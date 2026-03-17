<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sign Report: ') . $document->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-4" role="alert">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-red-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium text-red-700">{{ session('error') }}</span>
                    </div>
                </div>
            @endif
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <div class="p-6">
                    <div class="mb-6">
                        <a href="{{ route('reports.show', $document->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                            </svg>
                            Back to Report Details
                        </a>
                    </div>

                    <div class="space-y-6">
                        <!-- Signing Panel - Top Section -->
                        <div class="rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50/50 border-b border-gray-100 px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M15.98 1.804a1 1 0 00-1.96 0l-.24 1.192a1 1 0 01-.784.784l-1.192.24a1 1 0 000 1.96l1.192.24a1 1 0 01.784.785l.24 1.192a1 1 0 001.96 0l.24-1.192a1 1 0 01.784-.785l1.192-.24a1 1 0 000-1.96l-1.192-.24a1 1 0 01-.784-.784l-.24-1.192zM3.196 12.814l-.24 1.192a1 1 0 01-.784.785l-1.192.24a1 1 0 000 1.96l1.192.24a1 1 0 01.784.785l.24 1.192a1 1 0 001.96 0l.24-1.192a1 1 0 01.784-.785l1.192-.24a1 1 0 000-1.96l-1.192-.24a1 1 0 01-.784-.785l-.24-1.192a1 1 0 00-1.96 0z" />
                                        <path d="M10.229 1.993A.75.75 0 009.47 2.53l-6.835 16a.75.75 0 001.357.634l1.72-4.027 4.143 4.143a.75.75 0 001.06-1.06l-4.143-4.143 4.027-1.72a.75.75 0 00-.634-1.357L6.42 12.98l3.81-8.918.001-.002z" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Step 1: Create Your Signature</h3>
                                </div>
                            </div>
                            <div class="p-5">
                                <!-- Instructions -->
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 mb-4">
                                    <h4 class="text-sm font-medium text-gray-800 mb-2">How to sign:</h4>
                                    <ol class="text-sm text-gray-700 list-decimal list-inside space-y-1">
                                        <li>Sign in the bordered box below using your mouse or finger</li>
                                        <li>Click "Add Signature" to place it on the PDF below</li>
                                        <li>Add comments directly on the PDF if needed</li>
                                        <li>Click "Sign & Submit" when done</li>
                                    </ol>
                                </div>

                                <!-- Signature Canvas -->
                                <div class="mb-4 flex justify-center">
                                    <div class="text-center">
                                        <label class="block text-sm font-semibold text-indigo-700 mb-2">Sign Here First</label>
                                        <div class="border-2 border-indigo-300 rounded-xl bg-white p-2 shadow-sm inline-block">
                                            <canvas id="signature-canvas" width="250" height="80" class="border border-gray-200 rounded-lg" style="cursor: crosshair; border: 1px solid #d1d5db;"></canvas>
                                        </div>
                                        <div class="mt-3 flex space-x-2 justify-center">
                                            <button type="button" id="clear-signature" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.519.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5z" clip-rule="evenodd" />
                                                </svg>
                                                Clear
                                            </button>
                                            <button type="button" id="undo-signature" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M7.793 2.232a.75.75 0 01-.025 1.06L3.622 7.25h10.003a5.375 5.375 0 010 10.75H10.75a.75.75 0 010-1.5h2.875a3.875 3.875 0 000-7.75H3.622l4.146 3.957a.75.75 0 01-1.036 1.085l-5.5-5.25a.75.75 0 010-1.085l5.5-5.25a.75.75 0 011.06.025z" clip-rule="evenodd" />
                                                </svg>
                                                Undo
                                            </button>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Use mouse or finger to sign above</p>
                                        @error('signature_data')
                                            <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PDF Editor - Bottom Section -->
                        <div class="rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50/50 border-b border-gray-100 px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Step 2: Edit Document</h3>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <button type="button" id="signature-tool" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                        </svg>
                                        Add Signature
                                    </button>
                                    <button type="button" id="text-tool" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 3.98 1 5.344v5.311c0 1.365.993 2.59 2.43 2.82 1.616.26 3.27.411 4.942.448l2.496 2.496a.75.75 0 001.06-1.06l-1.57-1.57c1.727-.079 3.422-.252 5.072-.532C16.907 13.245 18 12.02 18 10.656V5.344c0-1.365-.993-2.59-2.43-2.82A41.732 41.732 0 0010 2z" clip-rule="evenodd" />
                                        </svg>
                                        Add Comment
                                    </button>
                                    <button type="button" id="clear-annotations" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.519.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5z" clip-rule="evenodd" />
                                        </svg>
                                        Clear All
                                    </button>
                                </div>
                                <div id="pdf-container" class="border border-gray-200 rounded-xl bg-gray-50" style="height: 600px; position: relative; overflow: auto;">
                                    <div id="pdf-loading" class="flex items-center justify-center h-full text-gray-500">
                                        <div class="text-center">
                                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mx-auto mb-4"></div>
                                            <p>Loading PDF...</p>
                                        </div>
                                    </div>
                                    <canvas id="pdf-canvas" style="max-width: 100%; height: auto; display: none;"></canvas>
                                    <div id="annotation-layer" style="position: absolute; top: 0; left: 0; pointer-events: none; width: 100%; height: 100%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Form and Actions -->
                        <div class="rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50/50 border-b border-gray-100 px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Step 3: Submit</h3>
                                </div>
                            </div>
                            <div class="p-5">
                                <form action="{{ route('reports.sign', $document->id) }}" method="POST" id="signForm">
                                    @csrf
                                    @method('POST')

                                    <!-- Comments -->
                                    <div class="mb-4">
                                        <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Additional Comments (optional)</label>
                                        <textarea id="comments" name="comments" rows="3" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="General comments about the document..."></textarea>
                                        @error('comments')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Hidden signature data -->
                                    <input type="hidden" id="signature-data" name="signature_data">

                                    <!-- Action Buttons -->
                                    <div class="flex space-x-4 justify-center">
                                        <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                            </svg>
                                            Sign & Submit Document
                                        </button>
                                        <a href="{{ route('reports.show', $document->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm text-center">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                            </svg>
                                            Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" defer></script>
    <script>
        // Initialize PDF.js worker as soon as possible
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        } else {
            // Fallback if PDF.js loads after this script
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof pdfjsLib !== 'undefined') {
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                }
            });
        }

        try {
            if (typeof SignaturePad === 'undefined') {
                throw new Error('SignaturePad library not loaded');
            }
            if (typeof pdfjsLib === 'undefined') {
                throw new Error('PDF.js library not loaded');
            }
        } catch (e) {
            console.error(e);
            alert('Error loading libraries: ' + e.message + '. Please refresh the page.');
            return;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pdfCanvas = document.getElementById('pdf-canvas');
            const annotationLayer = document.getElementById('annotation-layer');
            const signatureCanvas = document.getElementById('signature-canvas');

            // Initialize signature pad
            const signaturePad = new SignaturePad(signatureCanvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)',
                velocityFilterWeight: 0.7,
                minWidth: 0.5,
                maxWidth: 2.5,
                throttle: 16,
                minDistance: 5,
                dotSize: 1.5,
                penPressure: true
            });

            const signatureDataInput = document.getElementById('signature-data');

            let pdfDoc = null;
            let pageNum = 1;
            let pageRendering = false;
            let pageNumPending = null;
            let scale = 0.75;
            let annotations = [];
            let currentTool = null;
            let isDrawing = false;
            let signatureMode = false;

            // Load PDF
            function loadPDF(url) {
                console.log('Loading PDF from:', url);
                pdfjsLib.getDocument(url).promise.then(function(pdf) {
                    console.log('PDF loaded successfully');
                    pdfDoc = pdf;
                    renderPage(pageNum);
                }).catch(function(error) {
                    console.error('Error loading PDF:', error);
                    alert('Error loading PDF. Please try again.');
                });
            }

            // Render page
            function renderPage(num) {
                pageRendering = true;

                pdfDoc.getPage(num).then(function(page) {
                    const viewport = page.getViewport({scale: scale});
                    pdfCanvas.height = viewport.height;
                    pdfCanvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: pdfCanvas.getContext('2d'),
                        viewport: viewport
                    };

                    const renderTask = page.render(renderContext);

                    renderTask.promise.then(function() {
                        pageRendering = false;
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                        // Hide loading and show PDF
                        document.getElementById('pdf-loading').style.display = 'none';
                        pdfCanvas.style.display = 'block';
                        renderAnnotations();
                    });
                });
            }

            // Render annotations
            function renderAnnotations() {
                annotationLayer.innerHTML = '';
                annotations.forEach(function(annotation, index) {
                    const element = document.createElement('div');
                    element.style.position = 'absolute';
                    element.style.left = annotation.x + 'px';
                    element.style.top = annotation.y + 'px';
                    element.style.pointerEvents = 'auto';
                    element.style.cursor = 'move';
                    element.dataset.index = index;

                    if (annotation.type === 'signature') {
                        element.innerHTML = '<img src="' + annotation.data + '" style="max-width: 200px; max-height: 100px;">';
                    } else if (annotation.type === 'text') {
                        element.innerHTML = '<div style="font-family: ' + annotation.font + '; font-size: ' + annotation.size + 'px; white-space: pre-wrap; max-width: 300px; word-wrap: break-word; color: #000; background: transparent; border: none; padding: 0; margin: 0;">' + annotation.text + '</div>';
                    }

                    // Make draggable
                    let isDragging = false;
                    let startX, startY;

                    element.addEventListener('mousedown', function(e) {
                        isDragging = true;
                        startX = e.clientX - annotation.x;
                        startY = e.clientY - annotation.y;
                    });

                    document.addEventListener('mousemove', function(e) {
                        if (isDragging) {
                            annotation.x = e.clientX - startX;
                            annotation.y = e.clientY - startY;
                            element.style.left = annotation.x + 'px';
                            element.style.top = annotation.y + 'px';
                        }
                    });

                    document.addEventListener('mouseup', function() {
                        isDragging = false;
                    });

                    annotationLayer.appendChild(element);
                });
            }

            // Detect font from canvas
            function detectFont(context, x, y) {
                // Simple font detection - in a real implementation, you'd use more sophisticated OCR
                // For now, return a default font
                return 'Arial';
            }

            // PDF canvas click handler
            pdfCanvas.addEventListener('click', function(e) {
                console.log('PDF clicked, currentTool:', currentTool, 'signatureMode:', signatureMode);
                if (currentTool === 'signature' && signatureMode) {
                    const rect = pdfCanvas.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    annotations.push({
                        type: 'signature',
                        x: x,
                        y: y,
                        data: signaturePad.toDataURL()
                    });
                    renderAnnotations();
                    signatureMode = false;
                    currentTool = null;
                    document.getElementById('signature-tool').classList.remove('bg-blue-700');
                    document.getElementById('signature-tool').classList.add('bg-blue-500');
                } else if (currentTool === 'text') {
                    console.log('Opening text dialog');
                    const rect = pdfCanvas.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    // Create a custom dialog for text input with font and size options
                    showTextDialog(x, y);
                } else {
                    console.log('No active tool or tool not recognized');
                }
            });

            // Show text input dialog with font and size options
            function showTextDialog(x, y) {
                // Create modal overlay
                const modal = document.createElement('div');
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.width = '100%';
                modal.style.height = '100%';
                modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.style.zIndex = '1000';

                // Create dialog content
                const dialog = document.createElement('div');
                dialog.style.backgroundColor = 'white';
                dialog.style.padding = '20px';
                dialog.style.borderRadius = '8px';
                dialog.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                dialog.style.minWidth = '300px';

                dialog.innerHTML = `
                    <h3 style="margin-top: 0; margin-bottom: 15px; color: #374151;">Add Comment</h3>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Text:</label>
                        <textarea id="comment-text" rows="3" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-family: Arial; resize: vertical;" placeholder="Enter your comment..."></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Font:</label>
                        <select id="comment-font" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Helvetica">Helvetica</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Size:</label>
                        <select id="comment-size" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                            <option value="12">Small (12px)</option>
                            <option value="14" selected>Medium (14px)</option>
                            <option value="16">Large (16px)</option>
                            <option value="18">Extra Large (18px)</option>
                            <option value="20">XX Large (20px)</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button id="cancel-comment" style="padding: 8px 16px; background-color: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button id="add-comment" style="padding: 8px 16px; background-color: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Comment</button>
                    </div>
                `;

                modal.appendChild(dialog);
                document.body.appendChild(modal);

                // Focus on text area
                setTimeout(() => {
                    document.getElementById('comment-text').focus();
                }, 100);

                // Handle cancel
                document.getElementById('cancel-comment').addEventListener('click', function() {
                    document.body.removeChild(modal);
                    currentTool = null;
                });

                // Handle add comment
                document.getElementById('add-comment').addEventListener('click', function() {
                    const text = document.getElementById('comment-text').value.trim();
                    const font = document.getElementById('comment-font').value;
                    const size = document.getElementById('comment-size').value;

                    if (text) {
                        annotations.push({
                            type: 'text',
                            x: x,
                            y: y,
                            text: text,
                            font: font,
                            size: size
                        });
                        renderAnnotations();
                    }

                    document.body.removeChild(modal);
                    currentTool = null;
                });

                // Handle escape key
                document.addEventListener('keydown', function escHandler(e) {
                    if (e.key === 'Escape') {
                        document.body.removeChild(modal);
                        currentTool = null;
                        document.removeEventListener('keydown', escHandler);
                    }
                });
            }

            // Tool buttons
            document.getElementById('signature-tool').addEventListener('click', function() {
                if (!signaturePad.isEmpty()) {
                    currentTool = 'signature';
                    signatureMode = true;
                    this.classList.remove('bg-blue-500');
                    this.classList.add('bg-blue-700');
                    document.getElementById('text-tool').classList.remove('bg-green-700');
                    document.getElementById('text-tool').classList.add('bg-green-500');
                } else {
                    alert('Please sign in the signature box first.');
                }
            });

            document.getElementById('text-tool').addEventListener('click', function() {
                console.log('Text tool clicked');
                currentTool = 'text';
                signatureMode = false;
                // Don't keep text tool highlighted - it should only highlight when actively adding text
                document.getElementById('signature-tool').classList.remove('bg-blue-700');
                document.getElementById('signature-tool').classList.add('bg-blue-500');
                console.log('Current tool set to:', currentTool);
            });

            document.getElementById('clear-annotations').addEventListener('click', function() {
                annotations = [];
                renderAnnotations();
                currentTool = null;
                signatureMode = false;
                document.getElementById('signature-tool').classList.remove('bg-blue-700');
                document.getElementById('signature-tool').classList.add('bg-blue-500');
                document.getElementById('text-tool').classList.remove('bg-green-700');
                document.getElementById('text-tool').classList.add('bg-green-500');
            });

            // Signature canvas controls
            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
                signatureDataInput.value = '';
            });

            document.getElementById('undo-signature').addEventListener('click', function() {
                const data = signaturePad.toData();
                if (data) {
                    data.pop();
                    signaturePad.fromData(data);
                }
            });

            signaturePad.addEventListener('endStroke', function() {
                signatureDataInput.value = signaturePad.toDataURL();
            });

            // Form submission
            document.getElementById('signForm').addEventListener('submit', function(e) {
                console.log('Form submission started');
                signatureDataInput.value = signaturePad.toDataURL();
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Please provide your signature before submitting.');
                    return false;
                }

                console.log('Signature check passed, annotations:', annotations);

                // Add annotations data to form
                const annotationsInput = document.createElement('input');
                annotationsInput.type = 'hidden';
                annotationsInput.name = 'annotations';
                annotationsInput.value = JSON.stringify(annotations);
                this.appendChild(annotationsInput);

                console.log('Form data prepared, submitting...');
                // Don't prevent default - let the form submit normally
            });

            // Load the PDF
            loadPDF('{{ route("reports.preview", $document->id) }}');
        });
    </script>
</x-app-layout>
