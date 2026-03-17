<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSupervisor()) {
            $internIds = $user->subordinates()->where('is_intern', true)->pluck('id');
            $reports = Document::with('user')->where('type', 'Internship Report')->whereIn('user_id', $internIds)->latest()->get();
        } else {
            $reports = Document::where('type', 'Internship Report')->where('user_id', $user->id)->latest()->get();
        }

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filePath = $file->store('reports');

        Document::create([
            'title' => $request->title,
            'path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'type' => 'Internship Report',
            'user_id' => auth()->id(),
            'supervisor_id' => auth()->user()->supervisor_id,
            'status' => 'draft',
        ]);

        return redirect()->route('reports.index')->with('success', 'Report created successfully.');
    }

    public function show(Document $document)
    {
        return view('reports.show', compact('document'));
    }

    public function edit(Document $document)
    {
        return view('reports.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if ($request->hasFile('file')) {
            Storage::delete($document->path);
            $filePath = $request->file('file')->store('reports');
            $document->path = $filePath;
            $document->original_name = $request->file('file')->getClientOriginalName();
            $document->mime_type = $request->file('file')->getMimeType();
            $document->size = $request->file('file')->getSize();
        }

        $document->title = $request->title;
        $document->save();

        return redirect()->route('reports.index')->with('success', 'Report updated successfully.');
    }

    public function destroy(Document $document)
    {
        Storage::delete($document->path);
        $document->delete();

        return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
    }

    public function submit(Document $document)
    {
        // Only the owner can submit their own report
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        // Only draft, revised, or rejected reports can be submitted
        if (!in_array($document->status, ['draft', 'revised', 'rejected'])) {
            return redirect()->back()->with('error', 'Only draft or revised reports can be submitted.');
        }

        $document->update([
            'status' => 'pending',
            'supervisor_id' => auth()->user()->supervisor_id,
        ]);

        return redirect()->back()->with('success', 'Report submitted to supervisor successfully.');
    }

    public function download(Document $document)
    {
        return Storage::download($document->path, $document->original_name);
    }

    public function downloadSigned(Document $document)
    {
        if ($document->status !== 'signed' || !$document->signed_path) {
            return redirect()->back()->with('error', 'Signed version is not available.');
        }

        return Storage::download($document->signed_path, 'signed_' . $document->original_name);
    }

    public function preview(Document $document)
    {
        return response()->file(Storage::path($document->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
            'Cache-Control' => 'public, max-age=3600', // Cache for 1 hour to improve loading speed
            'Expires' => now()->addHour()->toRfc7231String()
        ]);
    }

    public function showSignForm(Document $document)
    {
        return view('reports.sign', compact('document'));
    }

    public function sign(Request $request, Document $document)
    {
        $request->validate([
            'signature_data' => 'required|string',
            'comments' => 'nullable|string|max:1000',
            'annotations' => 'nullable|string',
        ]);

        // Debug logging
        \Log::info('Sign request received', [
            'document_id' => $document->id,
            'signature_data_length' => strlen($request->signature_data),
            'comments_length' => strlen($request->comments ?? ''),
            'annotations_length' => strlen($request->annotations ?? ''),
        ]);

        try {
            // Get the data
            $signatureData = $request->signature_data;
            $comments = $request->comments;
            $annotations = json_decode($request->annotations ?? '[]', true);

            // Load the original PDF
            $pdfPath = Storage::path($document->path);
            $pdf = new Fpdi();

            // Get the number of pages
            $pageCount = $pdf->setSourceFile($pdfPath);

            // Process annotations and convert coordinates
            $processedAnnotations = [];
            foreach ($annotations as $annotation) {
                // Convert from canvas coordinates to PDF coordinates
                // PDF coordinates: (0,0) is bottom-left, canvas coordinates: (0,0) is top-left
                $annotation['pdf_x'] = $annotation['x'] * 0.75; // Scale factor for PDF
                $annotation['pdf_y'] = (600 - $annotation['y']) * 0.75; // Flip Y coordinate and scale
                $processedAnnotations[] = $annotation;
            }

            // Import each page
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);

                // Add annotations to this page (assuming single page for now)
                foreach ($processedAnnotations as $annotation) {
                    if ($annotation['type'] === 'signature') {
                        // For signature, we'll add a text marker since embedding images is complex
                        // In a production system, you'd decode the base64 and embed as image
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->SetTextColor(255, 0, 0); // Red color for signature marker
                        $pdf->SetXY($annotation['pdf_x'], $annotation['pdf_y']);
                        $pdf->Cell(0, 5, '[SIGNATURE: ' . auth()->user()->name . ']', 0, 1);
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetTextColor(128, 128, 128);
                        $pdf->SetXY($annotation['pdf_x'], $annotation['pdf_y'] + 5);
                        $pdf->Cell(0, 4, now()->format('M j, Y g:i A'), 0, 1);
                    } elseif ($annotation['type'] === 'text') {
                        // Add text comment with specified font and size
                        $fontSize = intval($annotation['size'] ?? 12);
                        $pdf->SetFont($annotation['font'] ?? 'Arial', '', $fontSize);
                        $pdf->SetTextColor(0, 0, 0); // Black text
                        $pdf->SetXY($annotation['pdf_x'], $annotation['pdf_y']);
                        $pdf->MultiCell(0, $fontSize * 0.4, $annotation['text'], 0, 'L');
                    }
                }

                // Add general comments to the last page if provided
                if ($pageNo === $pageCount && $comments) {
                    $pdf->SetFont('Arial', 'I', 10);
                    $pdf->SetTextColor(100, 100, 100);
                    $pdf->SetXY(50, 280);
                    $pdf->MultiCell(0, 5, 'Additional Comments: ' . $comments, 0, 'L');
                }
            }

            // Generate signed PDF filename
            $signedFilename = 'signed_' . time() . '_' . $document->original_name;
            $signedPath = 'reports/signed/' . $signedFilename;

            // Save the signed PDF
            Storage::put($signedPath, $pdf->Output('S'));

            // Update document record
            $document->update([
                'status' => 'signed',
                'signed_at' => now(),
                'comments' => $comments,
                'signed_path' => $signedPath,
            ]);

            \Log::info('Document signed successfully', [
                'document_id' => $document->id,
                'signed_path' => $signedPath,
                'annotations_count' => count($annotations)
            ]);

            return redirect()->route('reports.show', $document->id)->with('success', 'Report signed successfully.');

        } catch (\Exception $e) {
            \Log::error('Sign error: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to sign the report. Please try again.');
        }
    }
}
