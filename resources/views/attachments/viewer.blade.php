<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Lampiran: {{ $attachment->original_name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite / Tailwind CSS if needed, but since we want maximum flexibility and speed we use vanilla CSS -->
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --brand-color: #3b82f6;
            --brand-hover: #2563eb;
            --border-color: #334155;
        }

        /* Light mode support */
        @media (prefers-color-scheme: light) {
            :root {
                --bg-primary: #f8fafc;
                --bg-secondary: #ffffff;
                --text-primary: #0f172a;
                --text-secondary: #64748b;
                --brand-color: #3b82f6;
                --brand-hover: #2563eb;
                --border-color: #e2e8f0;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header / Navbar */
        .viewer-header {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 10;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-w: 0;
        }

        .file-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--brand-color);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        .file-details {
            min-w: 0;
        }

        .file-name {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-primary);
        }

        .file-size {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .viewer-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--brand-color);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--brand-hover);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        @media (prefers-color-scheme: light) {
            .btn-secondary:hover {
                background-color: rgba(0, 0, 0, 0.05);
            }
        }

        /* Viewer Container */
        .viewer-container {
            flex: 1;
            position: relative;
            width: 100%;
            height: calc(100vh - 64px);
            background-color: var(--bg-primary);
        }

        #file-viewer-mount {
            width: 100%;
            height: 100%;
        }

        /* Loading Skeleton */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background-color: var(--bg-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 5;
            transition: opacity 0.3s ease;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--border-color);
            border-top-color: var(--brand-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            margin-top: 16px;
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="viewer-header">
        <div class="file-info">
            <div class="file-icon">
                {{ $attachment->extension ?: 'FILE' }}
            </div>
            <div class="file-details">
                <h1 class="file-name" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</h1>
                <div class="file-size">{{ number_format($attachment->file_size / 1024, 1) }} KB</div>
            </div>
        </div>
        <div class="viewer-actions">
            <a href="{{ $downloadUrl }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Unduh
            </a>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>
    </header>

    <!-- Viewer -->
    <main class="viewer-container">
        <div id="loading-overlay" class="loading-overlay">
            <div class="spinner"></div>
            <p class="loading-text">Sedang menyiapkan dokumen...</p>
        </div>
        <div id="file-viewer-mount"></div>
    </main>

    <!-- Load File Viewer Library -->
    <script src="{{ asset('file-viewer/flyfish-file-viewer-web-full.iife.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('file-viewer-mount');
            const loadingOverlay = document.getElementById('loading-overlay');

            try {
                // Tentukan tema berdasarkan prefers-color-scheme browser
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = prefersDark ? 'dark' : 'light';

                // Gunakan base url path dari folder public
                const assetBaseUrl = "{{ asset('file-viewer') }}/";
                FlyfishFileViewerWebFull.setDefaultFullAssetBaseUrl(assetBaseUrl);

                // Mount file viewer
                const viewer = FlyfishFileViewerWebFull.mountViewer(container, {
                    url: "{{ $streamUrl }}",
                    name: "{{ $attachment->original_name }}",
                    type: "{{ $attachment->extension ?: 'pdf' }}",
                    options: {
                        theme: theme,
                        toolbar: true,
                        // non-editable watermark untuk audit keamanan
                        watermark: {
                            enabled: true,
                            text: "E-SPPB ENTERPRISE",
                            color: prefersDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.03)',
                            fontSize: 18
                        }
                    },
                    onEvent: (event) => {
                        console.log('FileViewer Event:', event.type);
                        if (event.type === 'load-complete') {
                            loadingOverlay.style.opacity = '0';
                            setTimeout(() => {
                                loadingOverlay.style.display = 'none';
                            }, 300);
                        }
                    },
                    onError: (err) => {
                        console.error('FileViewer Error:', err);
                        loadingOverlay.innerHTML = `
                            <p style="color: #ef4444; font-weight: 600;">Gagal memuat berkas lampiran.</p>
                            <p style="color: var(--text-secondary); font-size: 12px; margin-top: 8px;">Silakan klik tombol "Unduh" untuk membuka berkas secara manual.</p>
                        `;
                    }
                });

            } catch (error) {
                console.error('Inisialisasi FileViewer Gagal:', error);
                loadingOverlay.innerHTML = `
                    <p style="color: #ef4444; font-weight: 600;">Gagal menginisialisasi Penampil Berkas.</p>
                    <p style="color: var(--text-secondary); font-size: 12px; margin-top: 8px;">Silakan klik tombol "Unduh" untuk membuka berkas secara manual.</p>
                `;
            }
        });
    </script>
</body>
</html>
