<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sign Report: ') . $document->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
                    <span class="text-sm font-medium text-red-700">{{ session('error') }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ route('reports.show', $document->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Report
                </a>
            </div>

            <form action="{{ route('reports.sign', $document->id) }}" method="POST" id="signForm">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- LEFT: signature + submit --}}
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Step 1 · Your signature</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">This is what gets stamped on the document.</p>

                            {{-- Current signature preview --}}
                            <div class="mb-3">
                                <div id="sig-preview-wrap" class="border border-gray-200 dark:border-gray-600 rounded-lg bg-white p-2 flex items-center justify-center" style="min-height:70px;">
                                    <img id="sig-preview" src="" alt="" class="max-h-16 hidden">
                                    <span id="sig-empty" class="text-xs text-gray-400">No signature yet — draw one below.</span>
                                </div>
                            </div>

                            {{-- Draw pad --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-indigo-700 dark:text-indigo-300">Draw {{ $storedSignature ? 'a new' : 'your' }} signature</label>
                                    <button type="button" id="clear-signature" class="text-xs text-red-600 hover:text-red-800">Clear</button>
                                </div>
                                <div class="border-2 border-indigo-300 rounded-xl bg-white p-1 flex justify-center">
                                    <canvas id="signature-canvas" width="300" height="90" class="rounded-lg" style="cursor: crosshair; touch-action: none; max-width:100%;"></canvas>
                                </div>
                                <label class="flex items-start gap-2 mt-3 cursor-pointer">
                                    <input type="checkbox" id="save_default" name="save_default" value="1" checked class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Save as my signature (skip drawing next time)</span>
                                </label>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Step 3 · Comments (optional)</h3>
                            <textarea name="comments" rows="3" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-xl text-sm" placeholder="Any note for the intern..."></textarea>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Sign &amp; Submit
                        </button>

                        {{-- Hidden fields sent to the server --}}
                        <input type="hidden" name="signature_data" id="signature_data">
                        <input type="hidden" name="page" id="pos_page" value="1">
                        <input type="hidden" name="pos_x" id="pos_x">
                        <input type="hidden" name="pos_y" id="pos_y">
                        <input type="hidden" name="width_frac" id="width_frac" value="0.28">
                    </div>

                    {{-- RIGHT: document + placement --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Step 2 · Click where the signature should go</h3>
                                <div class="flex items-center gap-2 text-sm">
                                    <button type="button" id="prev-page" class="px-2 py-1 rounded border border-gray-200 dark:border-gray-600 disabled:opacity-40">‹</button>
                                    <span class="text-gray-500 dark:text-gray-400">Page <span id="page-num">1</span> / <span id="page-count">?</span></span>
                                    <button type="button" id="next-page" class="px-2 py-1 rounded border border-gray-200 dark:border-gray-600 disabled:opacity-40">›</button>
                                </div>
                            </div>
                            <p id="place-hint" class="text-xs text-amber-600 mb-2">Draw/confirm your signature, then click on the document to place it.</p>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-100 dark:bg-gray-900" style="max-height:75vh; overflow:auto;">
                                <div id="pdf-wrap" style="position:relative; display:inline-block; max-width:100%;">
                                    <canvas id="pdf-canvas" style="display:block; cursor: crosshair; max-width:100%; height:auto;"></canvas>
                                    <img id="sig-marker" src="" alt="" style="position:absolute; display:none; pointer-events:none; transform:translate(-50%,-50%); opacity:0.9;">
                                </div>
                                <div id="pdf-loading" class="p-10 text-center text-gray-500 text-sm">Loading document…</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }

            const storedSignature = @json($storedSignature);
            let currentSignature = storedSignature || '';
            let placement = null; // { page, fx, fy }
            const widthFrac = 0.28;

            const preview = document.getElementById('sig-preview');
            const previewEmpty = document.getElementById('sig-empty');
            const marker = document.getElementById('sig-marker');
            const canvas = document.getElementById('pdf-canvas');
            const wrap = document.getElementById('pdf-wrap');

            function setSignature(dataUrl) {
                currentSignature = dataUrl || '';
                document.getElementById('signature_data').value = currentSignature;
                if (currentSignature) {
                    preview.src = currentSignature; preview.classList.remove('hidden'); previewEmpty.classList.add('hidden');
                    marker.src = currentSignature;
                } else {
                    preview.classList.add('hidden'); previewEmpty.classList.remove('hidden');
                }
            }
            setSignature(currentSignature);

            // Signature pad
            const sigCanvas = document.getElementById('signature-canvas');
            let signaturePad = null;
            if (typeof SignaturePad !== 'undefined') {
                signaturePad = new SignaturePad(sigCanvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: '#111827' });
                signaturePad.addEventListener('endStroke', function () {
                    setSignature(signaturePad.toDataURL());
                    updateMarker();
                });
            }
            document.getElementById('clear-signature').addEventListener('click', function () {
                if (signaturePad) signaturePad.clear();
                setSignature(storedSignature || '');
                updateMarker();
            });

            // ---- PDF rendering + placement ----
            let pdfDoc = null, pageNum = 1, rendering = false;

            function renderPage(num) {
                if (!pdfDoc) return;
                rendering = true;
                pdfDoc.getPage(num).then(function (page) {
                    // Fit width to the container (max ~900px), min scale 0.5
                    const container = wrap.parentElement.clientWidth || 800;
                    const unscaled = page.getViewport({ scale: 1 });
                    let scale = Math.min(1.6, Math.max(0.5, (container - 8) / unscaled.width));
                    const viewport = page.getViewport({ scale: scale });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const task = page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });
                    task.promise.then(function () {
                        rendering = false;
                        document.getElementById('pdf-loading').style.display = 'none';
                        document.getElementById('page-num').textContent = num;
                        document.getElementById('pos_page').value = num;
                        document.getElementById('prev-page').disabled = (num <= 1);
                        document.getElementById('next-page').disabled = (num >= pdfDoc.numPages);
                        updateMarker();
                    });
                });
            }

            function updateMarker() {
                if (placement && placement.page === pageNum && currentSignature) {
                    marker.style.display = 'block';
                    marker.style.left = (placement.fx * canvas.clientWidth) + 'px';
                    marker.style.top = (placement.fy * canvas.clientHeight) + 'px';
                    marker.style.width = (widthFrac * canvas.clientWidth) + 'px';
                } else {
                    marker.style.display = 'none';
                }
            }

            canvas.addEventListener('click', function (e) {
                if (!currentSignature) { alert('Draw or confirm your signature first.'); return; }
                const rect = canvas.getBoundingClientRect();
                placement = {
                    page: pageNum,
                    fx: (e.clientX - rect.left) / rect.width,
                    fy: (e.clientY - rect.top) / rect.height,
                };
                document.getElementById('pos_x').value = placement.fx.toFixed(4);
                document.getElementById('pos_y').value = placement.fy.toFixed(4);
                document.getElementById('pos_page').value = placement.page;
                document.getElementById('place-hint').textContent = 'Signature placed on page ' + pageNum + '. Click again to move it.';
                document.getElementById('place-hint').className = 'text-xs text-emerald-600 mb-2';
                updateMarker();
            });

            document.getElementById('prev-page').addEventListener('click', function () {
                if (pageNum > 1) { pageNum--; renderPage(pageNum); }
            });
            document.getElementById('next-page').addEventListener('click', function () {
                if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); }
            });

            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.getDocument('{{ route('reports.preview', $document->id) }}').promise.then(function (pdf) {
                    pdfDoc = pdf;
                    document.getElementById('page-count').textContent = pdf.numPages;
                    renderPage(pageNum);
                }).catch(function (err) {
                    document.getElementById('pdf-loading').textContent = 'Could not load the document preview.';
                    console.error(err);
                });
            }

            // Validate on submit
            document.getElementById('signForm').addEventListener('submit', function (e) {
                if (!currentSignature) { e.preventDefault(); alert('Please draw or confirm your signature.'); return; }
                if (!placement) { e.preventDefault(); alert('Please click on the document to place your signature.'); return; }
                document.getElementById('signature_data').value = currentSignature;
            });
        });
    </script>
</x-app-layout>
