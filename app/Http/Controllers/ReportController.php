<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

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
        $this->authorizeView($document);

        return view('reports.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->authorizeManage($document);

        return view('reports.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorizeManage($document);

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
        $this->authorizeManage($document);

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

        // Notify the supervisor that a new report has been submitted.
        $notification = new \App\Notifications\SystemNotification(
            'New report submitted',
            auth()->user()->name . ' submitted a report: ' . $document->title,
            route('reports.show', $document->id),
            'document'
        );

        $supervisor = User::find($document->supervisor_id);

        if ($supervisor) {
            $supervisor->notify($notification);
        } else {
            // No supervisor assigned: fall back to notifying all admins so the
            // report is never silently lost.
            Log::warning("Report {$document->id} submitted but submitter " . auth()->id() . " has no supervisor_id; notified admins instead.");

            $admins = User::whereHas('role', function ($query) {
                $query->whereIn('name', ['Admin', 'Super Admin']);
            })->get();
            foreach ($admins as $admin) {
                $admin->notify($notification);
            }
        }

        return redirect()->back()->with('success', 'Report submitted to supervisor successfully.');
    }

    public function download(Document $document)
    {
        $this->authorizeView($document);

        return Storage::download($document->path, $document->original_name);
    }

    public function downloadSigned(Document $document)
    {
        $this->authorizeView($document);

        if ($document->status !== 'signed' || !$document->signed_path) {
            return redirect()->back()->with('error', 'Signed version is not available.');
        }

        return Storage::download($document->signed_path, 'signed_' . $document->original_name);
    }

    public function preview(Document $document)
    {
        $this->authorizeView($document);

        return response()->file(Storage::path($document->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
            'Cache-Control' => 'public, max-age=3600', // Cache for 1 hour to improve loading speed
            'Expires' => now()->addHour()->toRfc7231String()
        ]);
    }

    /**
     * Stream the ORIGINAL uploaded PDF inline (for previewing in an iframe).
     */
    public function viewPdf(Document $document)
    {
        $this->authorizeView($document);

        if (!$document->path || !Storage::exists($document->path)) {
            abort(404);
        }

        // ?download=1 streams as an attachment (used by the preview modal's
        // Download button, since reports.download is behind redirect.admin and
        // therefore unreachable by Admin/Super Admin).
        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response()->file(Storage::path($document->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $document->original_name . '"',
        ]);
    }

    /**
     * Stream the SIGNED PDF inline (for previewing in an iframe). ?download=1
     * forces an attachment download instead.
     */
    public function viewPdfSigned(Document $document)
    {
        $this->authorizeView($document);

        if (!$document->signed_path || !Storage::exists($document->signed_path)) {
            abort(404);
        }

        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response()->file(Storage::path($document->signed_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="signed_' . $document->original_name . '"',
        ]);
    }

    /**
     * A report may be previewed by an Admin / Super Admin, the report's owner,
     * or the direct supervisor of the report's owner.
     */
    protected function authorizeView(Document $document): void
    {
        $user = auth()->user();

        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isOwner = $document->user_id === $user->id;
        $isOwnersSupervisor = $document->user && $document->user->supervisor_id === $user->id;

        if (!$isAdmin && !$isOwner && !$isOwnersSupervisor) {
            abort(403);
        }
    }

    /**
     * Editing/deleting a report's content is limited to its owner or an
     * Admin / Super Admin (a supervisor may view/sign, but not alter/delete).
     */
    protected function authorizeManage(Document $document): void
    {
        $user = auth()->user();

        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isOwner = $document->user_id === $user->id;

        if (!$isAdmin && !$isOwner) {
            abort(403);
        }
    }

    public function showSignForm(Document $document)
    {
        $this->authorizeSigning($document);

        return view('reports.sign', compact('document'));
    }

    /**
     * Only an Admin / Super Admin, or the direct supervisor of the report's
     * owner, may sign an intern report.
     */
    protected function authorizeSigning(Document $document): void
    {
        $user = auth()->user();

        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isOwnersSupervisor = $document->user && $document->user->supervisor_id === $user->id;

        if (!$isAdmin && !$isOwnersSupervisor) {
            abort(403);
        }
    }

    public function sign(Request $request, Document $document)
    {
        $this->authorizeSigning($document);

        $request->validate([
            'signature_data' => 'required|string',
            'comments' => 'nullable|string|max:1000',
            'annotations' => 'nullable|string',
            'save_default' => 'nullable',
        ]);

        Log::info('Sign request received', [
            'document_id' => $document->id,
            'signature_data_length' => strlen($request->signature_data),
            'comments_length' => strlen($request->comments ?? ''),
            'annotations_length' => strlen($request->annotations ?? ''),
        ]);

        try {
            $comments = $request->comments;
            $annotations = json_decode($request->annotations ?? '[]', true) ?: [];

            // Optionally persist the drawn signature as this user's reusable
            // default so future reports can be signed with one click.
            if ($request->boolean('save_default') && !empty($request->signature_data)) {
                Signature::updateOrCreate(
                    ['user_id' => auth()->id()],
                    ['signature_data' => $request->signature_data]
                );
            }

            $signedPath = $this->stampSignature($document, $annotations, $comments);

            $document->update([
                'status' => 'signed',
                'signed_at' => now(),
                'comments' => $comments,
                'signed_path' => $signedPath,
            ]);

            $this->notifyOwnerSigned($document);

            Log::info('Document signed successfully', [
                'document_id' => $document->id,
                'signed_path' => $signedPath,
                'annotations_count' => count($annotations),
            ]);

            return redirect()->route('reports.show', $document->id)->with('success', 'Report signed successfully.');
        } catch (\Throwable $e) {
            Log::error('Sign error: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to sign the report. Please try again.');
        }
    }

    /**
     * One-click sign: stamp the supervisor's STORED default signature onto the
     * report automatically. Requires the user to have previously saved a
     * default signature (via the Sign page's "save as default" checkbox).
     */
    public function quickSign(Document $document)
    {
        $this->authorizeSigning($document);

        $signature = Signature::where('user_id', auth()->id())->first();

        if (!$signature || empty($signature->signature_data)) {
            return redirect()->back()->with('error', "Please create your signature first (open Sign and tick 'save as default').");
        }

        try {
            // Build a single signature annotation from the stored image and let
            // the shared stamping helper place it + an approval mark/date.
            $annotations = [[
                'type' => 'signature',
                'data' => $signature->signature_data,
                // Placed on the lower-right of the page (in canvas units; the
                // helper converts these to PDF coordinates just like sign()).
                'x' => 380,
                'y' => 520,
                'width' => 180,
                'height' => 70,
                'approved' => true,
            ]];

            $signedPath = $this->stampSignature($document, $annotations, null);

            $document->update([
                'status' => 'signed',
                'signed_at' => now(),
                'signed_path' => $signedPath,
            ]);

            $this->notifyOwnerSigned($document);

            Log::info('Document quick-signed successfully', [
                'document_id' => $document->id,
                'signed_path' => $signedPath,
            ]);

            return redirect()->back()->with('success', 'Report signed successfully.');
        } catch (\Throwable $e) {
            Log::error('Quick-sign error: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to sign the report. Please try again.');
        }
    }

    /**
     * Shared FPDI stamping logic used by both sign() and quickSign().
     *
     * Imports every page of the source PDF, stamps signature/text annotations
     * (and optional general comments) onto it, writes the result to
     * reports/signed/... and returns the stored (relative) path.
     *
     * @param  array<int, array<string, mixed>>  $annotations
     */
    private function stampSignature(Document $document, array $annotations, ?string $comments = null): string
    {
        // Make sure the destination directory exists (Laravel 11's `local`
        // disk root is storage/app/private, so this is relative to it).
        Storage::makeDirectory('reports/signed');

        $pdfPath = Storage::path($document->path);

        if (!is_file($pdfPath)) {
            throw new \RuntimeException("Source PDF not found at {$pdfPath}");
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfPath);

        // Convert annotation coordinates from canvas space (origin top-left)
        // to PDF space using the same scale factor the Sign page renders with.
        $processedAnnotations = [];
        foreach ($annotations as $annotation) {
            $annotation['pdf_x'] = ($annotation['x'] ?? 0) * 0.75;
            $annotation['pdf_y'] = (600 - ($annotation['y'] ?? 0)) * 0.75;
            $processedAnnotations[] = $annotation;
        }

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);

            foreach ($processedAnnotations as $annotation) {
                if (($annotation['type'] ?? null) === 'signature') {
                    $this->stampSignatureImage($pdf, $annotation);
                } elseif (($annotation['type'] ?? null) === 'text') {
                    $fontSize = intval($annotation['size'] ?? 12);
                    $pdf->SetFont($annotation['font'] ?? 'Arial', '', $fontSize);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($annotation['pdf_x'], $annotation['pdf_y']);
                    $pdf->MultiCell(0, $fontSize * 0.4, $annotation['text'] ?? '', 0, 'L');
                }
            }

            if ($pageNo === $pageCount && $comments) {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetXY(50, 280);
                $pdf->MultiCell(0, 5, 'Additional Comments: ' . $comments, 0, 'L');
            }
        }

        $signedFilename = 'signed_' . time() . '_' . $document->original_name;
        $signedPath = 'reports/signed/' . $signedFilename;

        Storage::put($signedPath, $pdf->Output('S'));

        return $signedPath;
    }

    /**
     * Decode a base64 PNG signature and stamp it (with an approval mark and
     * timestamp) onto the current PDF page.
     *
     * IMPORTANT: signature_pad produces a PNG WITH an alpha channel. FPDF cannot
     * parse alpha-channel PNGs and will attempt a multi-gigabyte allocation,
     * fataling the request. We therefore ALWAYS flatten the PNG onto a solid
     * white background via GD before handing it to FPDF, and never fall back to
     * the raw (transparent) PNG.
     *
     * @param  array<string, mixed>  $annotation
     */
    private function stampSignatureImage(Fpdi $pdf, array $annotation): void
    {
        $dataUri = $annotation['data'] ?? '';
        if (($commaPos = strpos($dataUri, ',')) !== false) {
            $dataUri = substr($dataUri, $commaPos + 1);
        }

        $binary = base64_decode($dataUri, true);
        if ($binary === false || $binary === '') {
            throw new \RuntimeException('Signature image could not be decoded from base64.');
        }

        if (!function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('GD extension is required to stamp signatures but is not available.');
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            throw new \RuntimeException('Signature image is not a valid PNG.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $canvas = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $w, $h, $white);
        // Preserve alpha while compositing the strokes onto the white canvas.
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $w, $h, $w, $h);

        ob_start();
        imagepng($canvas);
        $flattened = ob_get_clean();
        imagedestroy($src);
        imagedestroy($canvas);

        if ($flattened === false || $flattened === '') {
            throw new \RuntimeException('Failed to flatten signature PNG.');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'sig') . '.png';

        try {
            file_put_contents($tmpPath, $flattened);

            $imgW = isset($annotation['width']) ? $annotation['width'] * 0.75 : 40;
            $imgH = isset($annotation['height']) ? $annotation['height'] * 0.75 : 15;

            $pdf->Image($tmpPath, $annotation['pdf_x'], $annotation['pdf_y'], $imgW, $imgH, 'PNG');

            // Approval mark (the "chop") drawn as text, used by one-click sign.
            if (!empty($annotation['approved'])) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetTextColor(16, 122, 87);
                $pdf->SetXY($annotation['pdf_x'], $annotation['pdf_y'] - 5);
                $pdf->Cell(0, 4, 'APPROVED', 0, 1);
            }

            // Timestamp beneath the signature.
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->SetXY($annotation['pdf_x'], $annotation['pdf_y'] + $imgH);
            $pdf->Cell(0, 4, now()->format('M j, Y g:i A'), 0, 1);
        } finally {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

    /**
     * Notify the report's owner that their report has been signed.
     */
    private function notifyOwnerSigned(Document $document): void
    {
        $owner = $document->user;

        if (!$owner) {
            return;
        }

        $owner->notify(new \App\Notifications\SystemNotification(
            'Report signed',
            auth()->user()->name . ' signed your report: ' . $document->title,
            route('reports.show', $document->id),
            'document'
        ));
    }
}
