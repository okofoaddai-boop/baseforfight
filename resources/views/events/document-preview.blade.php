<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->title }} - {{ $documentName }}</title>
    <style>
        html, body {
            margin: 0;
            width: 100%;
            height: 100%;
            background: #f4f6f2;
        }

        .viewer {
            width: 100%;
            height: 100vh;
            display: block;
            border: 0;
            background: #f4f6f2;
        }

        .fallback {
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 24px;
            color: #2d3a2e;
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
        }

        .pdf-shell {
            min-height: 100vh;
            padding: 12px;
            box-sizing: border-box;
            background: #f4f6f2;
        }

        .pdf-pages {
            display: grid;
            gap: 12px;
        }

        .pdf-page {
            display: block;
            width: 100%;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #c8d4c2;
            box-shadow: 0 8px 20px rgba(45, 58, 46, 0.08);
        }
    </style>
</head>
<body>
    <div class="pdf-shell">
        <div id="pdf-pages" class="pdf-pages"></div>
        <div id="pdf-fallback" class="fallback" hidden>
            <p>Die PDF-Vorschau konnte nicht geladen werden.</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function () {
            var pdfDataUri = @json($pdfDataUri);
            var pagesContainer = document.getElementById('pdf-pages');
            var fallback = document.getElementById('pdf-fallback');

            function showFallback() {
                if (fallback) {
                    fallback.hidden = false;
                }
            }

            function base64ToUint8Array(base64) {
                var binary = atob(base64);
                var length = binary.length;
                var bytes = new Uint8Array(length);

                for (var index = 0; index < length; index += 1) {
                    bytes[index] = binary.charCodeAt(index);
                }

                return bytes;
            }

            if (!window.pdfjsLib || !pagesContainer || !pdfDataUri) {
                showFallback();
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            var dataIndex = pdfDataUri.indexOf(',');
            if (dataIndex === -1) {
                showFallback();
                return;
            }

            var pdfBytes;
            try {
                pdfBytes = base64ToUint8Array(pdfDataUri.slice(dataIndex + 1));
            } catch (error) {
                showFallback();
                return;
            }

            pdfjsLib.getDocument({ data: pdfBytes }).promise.then(function (pdf) {
                var renderPage = function (pageNumber) {
                    return pdf.getPage(pageNumber).then(function (page) {
                        var viewport = page.getViewport({ scale: 1.5 });
                        var canvas = document.createElement('canvas');
                        var context = canvas.getContext('2d');

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        canvas.className = 'pdf-page';

                        pagesContainer.appendChild(canvas);

                        return page.render({ canvasContext: context, viewport: viewport }).promise;
                    });
                };

                var queue = Promise.resolve();
                for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
                    queue = queue.then(renderPage.bind(null, pageNumber));
                }

                return queue;
            }).catch(function () {
                showFallback();
            });
        }());
    </script>
</body>
</html>