@php
    // $url = inline PDF URL. Download URL reuses the same route with ?download=1
    // (reports.download is behind redirect.admin, so admins can't use it).
    $downloadUrl = $url . (str_contains($url, '?') ? '&' : '?') . 'download=1';
    $frameId = 'pdfPreviewFrame_' . uniqid();
@endphp

<div class="w-full" x-data>
    <div class="mb-3 flex items-center justify-end gap-2">
        <button type="button"
                onclick="(function(){var f=document.getElementById('{{ $frameId }}');try{f.contentWindow.focus();f.contentWindow.print();}catch(e){window.open('{{ $url }}','_blank');}})()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
            Print
        </button>
        <a href="{{ $downloadUrl }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Download
        </a>
    </div>

    <iframe id="{{ $frameId }}" src="{{ $url }}" class="w-full rounded-lg border border-gray-200 dark:border-white/10"
            style="height: 78vh; border-width: 1px;" loading="lazy"></iframe>
</div>
