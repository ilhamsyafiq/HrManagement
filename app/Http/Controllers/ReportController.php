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
            // PDF only: reports must be previewable inline and signable (FPDI),
            // both of which require PDF. Word docs can be neither.
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ], [
            'file.mimes' => 'The report must be a PDF file (so it can be previewed and signed).',
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
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'file.mimes' => 'The report must be a PDF file (so it can be previewed and signed).',
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

        // Use Symfony's BinaryFileResponse (fread loop) rather than
        // Storage::download(), which relies on fpassthru() — disabled in the
        // production PHP-FPM (RunCloud default disable_functions).
        return response()->download(Storage::path($document->path), $document->original_name);
    }

    public function downloadSigned(Document $document)
    {
        $this->authorizeView($document);

        if ($document->status !== 'signed' || !$document->signed_path) {
            return redirect()->back()->with('error', 'Signed version is not available.');
        }

        return response()->download(Storage::path($document->signed_path), 'signed_' . $document->original_name);
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

        // Serve the real mime type. Non-PDFs (legacy Word uploads) can't render in
        // the PDF viewer, so the UI links them to download instead of this route.
        return response()->file(Storage::path($document->path), [
            'Content-Type' => $document->mime_type ?: 'application/pdf',
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

        // Preload the signer's saved signature so they can just place it
        // (no need to redraw) — or draw a new one on the page.
        $storedSignature = Signature::where('user_id', auth()->id())->value('signature_data');

        return view('reports.sign', compact('document', 'storedSignature'));
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
            'page' => 'required|integer|min:1',
            'pos_x' => 'required|numeric|between:0,1',   // fraction across the page
            'pos_y' => 'required|numeric|between:0,1',   // fraction down the page
            'width_frac' => 'nullable|numeric|between:0.05,0.6',
            'comments' => 'nullable|string|max:1000',
            'save_default' => 'nullable',
        ]);

        try {
            // Optionally persist the drawn signature as this user's reusable
            // default so future reports only need placing (no redraw).
            if ($request->boolean('save_default') && !empty($request->signature_data)) {
                Signature::updateOrCreate(
                    ['user_id' => auth()->id()],
                    ['signature_data' => $request->signature_data]
                );
            }

            $signedPath = $this->stampSignature($document, $request->signature_data, [
                'page' => (int) $request->page,
                'fx' => (float) $request->pos_x,
                'fy' => (float) $request->pos_y,
                'wFrac' => (float) ($request->width_frac ?: 0.28),
            ], $request->comments);

            $document->update([
                'status' => 'signed',
                'signed_at' => now(),
                'comments' => $request->comments,
                'signed_path' => $signedPath,
            ]);

            $this->notifyOwnerSigned($document);

            return redirect()->route('reports.show', $document->id)->with('success', 'Report signed successfully.');
        } catch (\Throwable $e) {
            Log::error('Sign error: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to sign the report: ' . $e->getMessage());
        }
    }

    /**
     * Signing now requires choosing WHERE the signature goes (each document has
     * a different signature spot), so this just routes to the placement page.
     * The signer's saved signature is preloaded there for quick placing.
     */
    public function quickSign(Document $document)
    {
        $this->authorizeSigning($document);

        return redirect()->route('reports.sign.form', $document->id);
    }

    /**
     * Re-import every page of the source PDF and stamp the signature image at
     * the chosen location (fractional page coordinates -> mm) on the chosen
     * page, with a small date beneath it. Optional comments go on the last page.
     * Returns the stored (relative) path.
     *
     * @param  array{page:int,fx:float,fy:float,wFrac:float}  $placement
     */
    private function stampSignature(Document $document, string $signatureImage, array $placement, ?string $comments): string
    {
        // Laravel 11's `local` disk root is storage/app/private.
        Storage::makeDirectory('reports/signed');

        $pdfPath = Storage::path($document->path);
        if (!is_file($pdfPath)) {
            throw new \RuntimeException("Source PDF not found at {$pdfPath}");
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfPath);

        $sigTmp = $this->flattenSignatureToTempPng($signatureImage);
        if (!$sigTmp) {
            throw new \RuntimeException('Signature image could not be processed.');
        }

        $targetPage = min(max(1, $placement['page']), $pageCount);

        try {
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                if ($pageNo === $targetPage) {
                    $this->placeSignature($pdf, (float) $size['width'], (float) $size['height'], $sigTmp, $placement);
                }

                if ($pageNo === $pageCount && $comments) {
                    $pdf->SetXY(15, (float) $size['height'] - 18);
                    $pdf->SetFont('Arial', 'I', 9);
                    $pdf->SetTextColor(90, 90, 90);
                    $pdf->MultiCell((float) $size['width'] - 30, 4, $this->latin1('Comments: ' . $comments), 0, 'L');
                }
            }

            $signedPath = 'reports/signed/signed_' . time() . '_' . $document->original_name;
            Storage::put($signedPath, $pdf->Output('S'));

            return $signedPath;
        } finally {
            @unlink($sigTmp);
            $base = substr($sigTmp, 0, -4); // the tempnam() base without .png
            if (is_file($base)) {
                @unlink($base);
            }
        }
    }

    /**
     * Stamp the signature image centred on the chosen point (fractional page
     * coords -> mm), keeping it fully on-page.
     *
     * @param  array{page:int,fx:float,fy:float,wFrac:float}  $placement
     */
    private function placeSignature(Fpdi $pdf, float $pageW, float $pageH, string $sigPath, array $placement): void
    {
        $wMm = max(20.0, min($placement['wFrac'] * $pageW, $pageW * 0.5));

        $dims = @getimagesize($sigPath) ?: [200, 80];
        $ratio = ($dims[0] > 0) ? ($dims[1] / $dims[0]) : 0.4;
        $hMm = $wMm * $ratio;

        // The click point is the CENTRE of the signature; clamp on-page.
        $x = $placement['fx'] * $pageW - $wMm / 2;
        $y = $placement['fy'] * $pageH - $hMm / 2;
        $x = max(2.0, min($x, $pageW - $wMm - 2));
        $y = max(2.0, min($y, $pageH - $hMm - 6));

        $pdf->Image($sigPath, $x, $y, $wMm, $hMm, 'PNG');
    }

    /**
     * Decode a base64 (signature_pad) PNG and flatten it onto a solid white
     * background via GD — FPDF cannot parse alpha-channel PNGs. Returns the temp
     * file path (with a .png suffix), or null if it can't be decoded.
     */
    private function flattenSignatureToTempPng(string $dataUri): ?string
    {
        if (($commaPos = strpos($dataUri, ',')) !== false) {
            $dataUri = substr($dataUri, $commaPos + 1);
        }

        $binary = base64_decode($dataUri, true);
        if ($binary === false || $binary === '' || !function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $canvas = imagecreatetruecolor($w, $h);
        imagefilledrectangle($canvas, 0, 0, $w, $h, imagecolorallocate($canvas, 255, 255, 255));
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $w, $h, $w, $h);

        ob_start();
        imagepng($canvas);
        $flattened = ob_get_clean();
        imagedestroy($src);
        imagedestroy($canvas);

        if ($flattened === false || $flattened === '') {
            return null;
        }

        $path = tempnam(sys_get_temp_dir(), 'sig') . '.png';
        file_put_contents($path, $flattened);

        return $path;
    }

    /**
     * FPDF core fonts are Latin-1 only; transliterate UTF-8 so names/comments
     * don't render as mojibake.
     */
    private function latin1(string $text): string
    {
        return @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text) ?: $text;
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
