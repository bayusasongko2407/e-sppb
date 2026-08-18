<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi API — E-SPPB Enterprise</title>
    <meta name="description" content="Dokumentasi lengkap API E-SPPB Enterprise: OpenAPI reference, panduan mobile, penerimaan barang, autentikasi.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Stoplight Elements (same as Scramble uses) --}}
    <script src="https://unpkg.com/@stoplight/elements@8.4.2/web-components.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements@8.4.2/styles.min.css">

    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --primary-light: #e0f2fe;
            --accent: #6366f1;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --header-bg: #ffffff;
            --content-bg: #ffffff;
            --sidebar-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --code-bg: #0d1117;
            --code-text: #e6edf3;
            --sidebar-width: 280px;
            --header-height: 56px;
            --tab-height: 45px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0d1117;
                --header-bg: #161b22;
                --content-bg: #0d1117;
                --sidebar-bg: #161b22;
                --text-main: #e6edf3;
                --text-muted: #8b949e;
                --text-light: #6e7681;
                --border: #30363d;
                --border-light: #21262d;
                --primary-light: #0c2039;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); }

        /* ── HEADER ──────────────────────────────────────────── */
        .app-header {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--header-height);
            background: var(--header-bg);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            padding: 0 1.25rem; gap: 1rem; z-index: 200;
        }
        .header-logo {
            display: flex; align-items: center; gap: 0.5rem;
            text-decoration: none; font-weight: 700; font-size: 0.9375rem;
            color: var(--text-main); white-space: nowrap;
        }
        .logo-icon {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 7px; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 0.8125rem; font-weight: 800;
        }
        .header-sep { width: 1px; height: 22px; background: var(--border); flex-shrink: 0; }
        .header-title { font-size: 0.875rem; color: var(--text-muted); font-weight: 500; }

        /* TAB SWITCHER in header */
        .header-tabs {
            display: flex; align-items: center; gap: 0.25rem;
            background: var(--border-light); border: 1px solid var(--border);
            border-radius: 8px; padding: 0.2rem;
        }
        .header-tab {
            display: flex; align-items: center; gap: 0.375rem;
            padding: 0.3125rem 0.875rem; border-radius: 6px;
            font-size: 0.8125rem; font-weight: 500; cursor: pointer;
            border: none; background: transparent; color: var(--text-muted);
            transition: all 0.15s ease; white-space: nowrap;
        }
        .header-tab:hover { color: var(--text-main); }
        .header-tab.active {
            background: var(--content-bg); color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .header-tab .tab-icon { font-size: 0.875rem; }

        .header-actions { margin-left: auto; display: flex; align-items: center; gap: 0.625rem; }
        .btn-sm {
            display: inline-flex; align-items: center; gap: 0.3125rem;
            padding: 0.3125rem 0.75rem; border-radius: 6px; font-size: 0.8125rem;
            font-weight: 500; text-decoration: none; transition: all 0.15s ease;
            border: 1px solid var(--border); color: var(--text-main); background: transparent;
            white-space: nowrap;
        }
        .btn-sm:hover { background: var(--border-light); }
        .btn-sm.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-sm.primary:hover { background: var(--primary-dark); }

        /* ── LAYOUT ──────────────────────────────────────────── */
        .page { padding-top: var(--header-height); height: 100vh; display: flex; flex-direction: column; }

        /* ── PANE: OPENAPI (Stoplight Elements) ──────────────── */
        .pane { display: none; flex: 1; overflow: hidden; }
        .pane.active { display: flex; flex-direction: column; }

        #pane-openapi elements-api {
            flex: 1;
            --font-sans: 'Inter', sans-serif;
        }
        #pane-openapi .elements-wrap {
            flex: 1; display: flex; flex-direction: column; overflow: hidden;
        }

        /* ── PANE: MOBILE GUIDE ──────────────────────────────── */
        #pane-guide {
            overflow-y: auto;
            background: var(--bg);
        }
        #pane-guide .guide-inner {
            display: flex; max-width: 1280px; margin: 0 auto; width: 100%;
        }

        /* Sidebar */
        .guide-sidebar {
            width: var(--sidebar-width); flex-shrink: 0;
            position: sticky; top: 0; align-self: flex-start;
            height: calc(100vh - var(--header-height));
            overflow-y: auto; padding: 1.5rem 0 2rem;
            border-right: 1px solid var(--border);
            background: var(--sidebar-bg);
            scrollbar-width: thin;
        }
        .gs-label {
            font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--text-light);
            padding: 0.875rem 1.25rem 0.375rem;
        }
        .gs-link {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 1.25rem; font-size: 0.875rem;
            color: var(--text-muted); text-decoration: none;
            border-left: 2px solid transparent; transition: all 0.15s;
        }
        .gs-link:hover { color: var(--text-main); background: var(--border-light); }
        .gs-link.active { color: var(--primary); border-left-color: var(--primary); background: var(--primary-light); font-weight: 500; }
        .gs-link .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; opacity: 0.5; flex-shrink: 0; }
        .method-tag {
            font-family: 'JetBrains Mono', monospace; font-size: 0.625rem;
            font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px;
        }
        .mt-post { background: #dcfce7; color: #166534; }
        .mt-get  { background: #dbeafe; color: #1d4ed8; }
        @media (prefers-color-scheme: dark) {
            .mt-post { background: #052e16; color: #86efac; }
            .mt-get  { background: #1e3a5f; color: #93c5fd; }
        }

        /* Guide content */
        .guide-content { flex: 1; min-width: 0; padding: 2.5rem 2rem 6rem; }
        .doc-section { scroll-margin-top: 1.5rem; margin-bottom: 3.5rem; }

        h1 { font-size: 1.875rem; font-weight: 700; margin-bottom: 0.75rem; }
        h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.875rem; padding-bottom: 0.625rem; border-bottom: 1px solid var(--border); }
        h3 { font-size: 0.9375rem; font-weight: 600; margin: 1.5rem 0 0.625rem; }
        p { color: var(--text-muted); margin-bottom: 0.875rem; line-height: 1.7; font-size: 0.9375rem; }
        code { font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; background: var(--border-light); border: 1px solid var(--border); padding: 0.125rem 0.375rem; border-radius: 4px; }
        .doc-divider { height: 1px; background: var(--border); margin: 2.5rem 0; }

        /* Endpoint card */
        .endpoint-card { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin: 1.5rem 0; }
        .endpoint-header {
            display: flex; align-items: center; gap: 0.875rem;
            padding: 0.875rem 1.125rem; background: var(--border-light); cursor: pointer;
        }
        .endpoint-header:hover { background: var(--border); }
        .ep-method {
            font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 700;
            padding: 0.2rem 0.5rem; border-radius: 5px; min-width: 46px; text-align: center;
        }
        .ep-POST { background: #dcfce7; color: #166534; }
        .ep-GET  { background: #dbeafe; color: #1d4ed8; }
        @media (prefers-color-scheme: dark) {
            .ep-POST { background: #052e16; color: #86efac; }
            .ep-GET  { background: #1e3a5f; color: #93c5fd; }
        }
        .ep-path { font-family: 'JetBrains Mono', monospace; font-size: 0.875rem; font-weight: 500; }
        .ep-desc { font-size: 0.8125rem; color: var(--text-muted); margin-left: auto; }
        .endpoint-body { padding: 1.25rem; }
        .endpoint-body.collapsed { display: none; }

        /* Params table */
        .params-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; margin: 0.75rem 0 1.25rem; }
        .params-table th { padding: 0.5rem 0.75rem; text-align: left; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-light); font-weight: 700; border-bottom: 1px solid var(--border); }
        .params-table td { padding: 0.625rem 0.75rem; border-bottom: 1px solid var(--border-light); vertical-align: top; }
        .params-table tbody tr:last-child td { border-bottom: none; }
        .pn { font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; font-weight: 500; }
        .pt { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--primary); }
        .pr { font-size: 0.6875rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px; background: #fef2f2; color: #991b1b; }
        .po { font-size: 0.6875rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px; background: var(--border-light); color: var(--text-muted); }
        @media (prefers-color-scheme: dark) {
            .pr { background: #3b0000; color: #fca5a5; }
        }

        /* Code blocks */
        .code-block { background: var(--code-bg); border-radius: 8px; overflow: hidden; margin: 1rem 0; }
        .code-hdr { display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .code-lang { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); }
        .copy-btn { font-size: 0.75rem; color: rgba(255,255,255,0.4); background: none; border: none; cursor: pointer; padding: 0.125rem 0.375rem; border-radius: 4px; transition: all 0.15s; }
        .copy-btn:hover { color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.08); }
        .code-body { padding: 1.125rem 1rem; overflow-x: auto; }
        pre { font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; line-height: 1.75; color: var(--code-text); white-space: pre; }
        .jk { color: #79c0ff; } .js { color: #a5d6ff; } .jn { color: #ffa657; }
        .jb { color: #ff7b72; } .jc { color: #8b949e; font-style: italic; }

        /* Response tabs */
        .resp-tabs { display: flex; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; margin: 1rem 0 0; }
        .resp-tab { flex: 1; padding: 0.45rem 0.75rem; font-size: 0.8rem; font-weight: 500; background: var(--border-light); border: none; cursor: pointer; color: var(--text-muted); border-right: 1px solid var(--border); transition: all 0.15s; }
        .resp-tab:last-child { border-right: none; }
        .resp-tab.active { background: var(--content-bg); color: var(--text-main); }
        .resp-pane { display: none; }
        .resp-pane.active { display: block; }

        /* Callouts */
        .callout { display: flex; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 8px; margin: 1rem 0; border: 1px solid; }
        .callout p { margin: 0; font-size: 0.875rem; }
        .callout.info { background: #eff6ff; border-color: #bfdbfe; }
        .callout.info p { color: #1e40af; }
        .callout.warning { background: #fffbeb; border-color: #fde68a; }
        .callout.warning p { color: #92400e; }
        @media (prefers-color-scheme: dark) {
            .callout.info { background: #0c1d35; border-color: #1d4ed8; }
            .callout.info p { color: #93c5fd; }
            .callout.warning { background: #2d1b00; border-color: #d97706; }
            .callout.warning p { color: #fcd34d; }
        }

        /* Base URL box */
        .base-url-box { display: flex; align-items: center; gap: 0.75rem; background: var(--code-bg); border-radius: 8px; padding: 0.75rem 1rem; margin: 0.875rem 0; }
        .bu-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); flex-shrink: 0; }
        .bu-val { font-family: 'JetBrains Mono', monospace; font-size: 0.875rem; color: #a5d6ff; word-break: break-all; }

        /* Status codes */
        .status-codes { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 0.75rem 0; }
        .sc { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.6rem; border-radius: 5px; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 600; border: 1px solid; }
        .sc-200 { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .sc-422 { background: #fff7ed; color: #9a3412; border-color: #fed7aa; }
        .sc-404 { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .sc-413 { background: #f5f3ff; color: #5b21b6; border-color: #ddd6fe; }
        @media (prefers-color-scheme: dark) {
            .sc-200 { background: #052e16; border-color: #166534; color: #86efac; }
            .sc-422 { background: #431407; border-color: #9a3412; color: #fdba74; }
            .sc-404 { background: #3b0000; border-color: #7f1d1d; color: #fca5a5; }
            .sc-413 { background: #2e1065; border-color: #5b21b6; color: #c4b5fd; }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .header-title { display: none; }
            .header-sep { display: none; }
            .guide-sidebar { display: none; }
            .guide-content { padding: 1.5rem 1rem 4rem; }
            h1 { font-size: 1.5rem; }
            .resp-tab { font-size: 0.75rem; padding: 0.4rem 0.5rem; }
            .header-tab { padding: 0.3rem 0.625rem; font-size: 0.75rem; }
        }

        /* Stoplight elements full height */
        #pane-openapi {
            background: #fff;
        }
        @media (prefers-color-scheme: dark) {
            #pane-openapi { background: #1a1d23; }
        }
        #pane-openapi elements-api {
            height: 100%;
        }
        #elements-container {
            height: 100%;
            overflow: hidden;
        }
    </style>
</head>
<body>

<!-- ── HEADER ──────────────────────────────────────────────────── -->
<header class="app-header">
    <a href="/docs" class="header-logo">
        <div class="logo-icon">E</div>
        E-SPPB
    </a>
    <div class="header-sep"></div>
    <span class="header-title">Dokumentasi API</span>

    <div class="header-tabs" style="margin-left: 1.25rem;">
        <button class="header-tab active" onclick="switchPane('openapi', this)" id="tab-openapi">
            <span class="tab-icon">⚡</span> API Reference
        </button>
        <button class="header-tab" onclick="switchPane('guide', this)" id="tab-guide">
            <span class="tab-icon">📱</span> Panduan Mobile
        </button>
        <button class="header-tab" onclick="switchPane('ai-prompt', this)" id="tab-ai-prompt">
            <span class="tab-icon">🤖</span> AI Studio Prompt (Markdown)
        </button>
    </div>

    <div class="header-actions">
        <a href="/docs" class="btn-sm">📋 User Manual</a>
        <a href="/admin" class="btn-sm primary">Masuk ke Aplikasi</a>
    </div>
</header>

<!-- ── PAGE ─────────────────────────────────────────────────────── -->
<div class="page">

    <!-- TAB 1: OpenAPI Reference (Stoplight Elements) -->
    <div class="pane active" id="pane-openapi">
        <div id="elements-container">
            <elements-api
                id="elements-docs"
                apiDescriptionUrl="{{ url('/docs/api.json') }}"
                router="hash"
                layout="responsive"
                hideTryIt="false"
                tryItCredentialsPolicy="include"
            ></elements-api>
        </div>
    </div>

    <!-- TAB 2: Mobile Guide -->
    <div class="pane" id="pane-guide">
        <div class="guide-inner">

            <!-- Sidebar -->
            <nav class="guide-sidebar">
                <div>
                    <div class="gs-label">Pengantar</div>
                    <a href="#overview" class="gs-link active"><span class="dot"></span>Gambaran Umum</a>
                    <a href="#authentication" class="gs-link"><span class="dot"></span>Autentikasi</a>
                    <a href="#errors" class="gs-link"><span class="dot"></span>Format Error</a>
                </div>
                <div>
                    <div class="gs-label">Penerimaan Barang</div>
                    <a href="#receive-confirm" class="gs-link"><span class="method-tag mt-post">POST</span>Konfirmasi Terima</a>
                    <a href="#receive-show" class="gs-link"><span class="method-tag mt-get">GET</span>Detail Surat Jalan</a>
                </div>
                <div>
                    <div class="gs-label">Autentikasi</div>
                    <a href="#auth-login" class="gs-link"><span class="method-tag mt-post">POST</span>Login</a>
                    <a href="#auth-me" class="gs-link"><span class="method-tag mt-get">GET</span>Profil Saya</a>
                </div>
                <div>
                    <div class="gs-label">Panduan Implementasi</div>
                    <a href="#mobile-flow" class="gs-link"><span class="dot"></span>Alur Konfirmasi QR</a>
                    <a href="#signature-guide" class="gs-link"><span class="dot"></span>Panduan Tanda Tangan</a>
                </div>
            </nav>

            <!-- Content -->
            <div class="guide-content">

                <!-- OVERVIEW -->
                <div class="doc-section" id="overview">
                    <h1>Panduan Mobile & API</h1>
                    <p>Panduan lengkap integrasi aplikasi mobile dengan sistem E-SPPB Enterprise, difokuskan pada alur <strong>konfirmasi penerimaan barang</strong> di lapangan via QR Code.</p>
                    <p>Untuk spesifikasi OpenAPI lengkap (seluruh endpoint, schema, TryIt), gunakan tab <strong>⚡ API Reference</strong> di sebelah kiri.</p>

                    <h3>Base URL</h3>
                    <div class="base-url-box">
                        <span class="bu-label">BASE</span>
                        <span class="bu-val">{{ url('/api/v1') }}</span>
                    </div>

                    <h3>Format Respons</h3>
                    <div class="code-block">
                        <div class="code-hdr"><span class="code-lang">JSON</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                        <div class="code-body"><pre>{
  <span class="jk">"success"</span>: <span class="jb">true</span>,
  <span class="jk">"message"</span>: <span class="js">"Pesan deskriptif hasil operasi"</span>,
  <span class="jk">"data"</span>: { <span class="jc">// Objek atau array data</span> },
  <span class="jk">"already_confirmed"</span>: <span class="jb">false</span> <span class="jc">// Khusus endpoint receive</span>
}</pre></div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- AUTH -->
                <div class="doc-section" id="authentication">
                    <h2>Autentikasi</h2>
                    <p>API menggunakan <strong>Laravel Sanctum Bearer Token</strong>. Sertakan token di header setiap request yang memerlukan autentikasi.</p>
                    <div class="code-block">
                        <div class="code-hdr"><span class="code-lang">HTTP Header</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                        <div class="code-body"><pre>Authorization: Bearer {token}</pre></div>
                    </div>
                    <div class="callout info">
                        <div>ℹ️</div>
                        <div><p>Endpoint <strong>konfirmasi penerimaan barang</strong> bersifat <strong>publik</strong> — dapat dipanggil tanpa token melalui QR scan. Jika token disertakan, ID pengguna akan dicatat sebagai <code>received_by_id</code>.</p></div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- ERRORS -->
                <div class="doc-section" id="errors">
                    <h2>Format Error</h2>
                    <h3>Error Validasi (422)</h3>
                    <div class="code-block">
                        <div class="code-hdr"><span class="code-lang">JSON</span></div>
                        <div class="code-body"><pre>{
  <span class="jk">"success"</span>: <span class="jb">false</span>,
  <span class="jk">"message"</span>: <span class="js">"The recipient name field is required."</span>,
  <span class="jk">"errors"</span>: {
    <span class="jk">"recipient_name"</span>: [<span class="js">"Nama penerima wajib diisi."</span>]
  }
}</pre></div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- RECEIVE CONFIRM -->
                <div class="doc-section" id="receive-confirm">
                    <h2>Konfirmasi Penerimaan Barang</h2>
                    <p>Endpoint utama untuk mobile app. Dipanggil setelah penerima memindai QR Code pada Surat Jalan dan mengisi form di aplikasi.</p>

                    <div class="endpoint-card">
                        <div class="endpoint-header" onclick="toggleEp(this)">
                            <span class="ep-method ep-POST">POST</span>
                            <span class="ep-path">/goods-releases/{uuid}/receive</span>
                            <span class="ep-desc">Konfirmasi terima barang</span>
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:auto;flex-shrink:0;transition:transform .2s"><path d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="endpoint-body">
                            <div class="callout info">
                                <div>🔓</div>
                                <div><p>Endpoint ini <strong>tidak memerlukan autentikasi</strong>. Dapat dipanggil langsung dari hasil pemindaian QR Code Surat Jalan cetak.</p></div>
                            </div>

                            <h3>Path Parameter</h3>
                            <table class="params-table">
                                <thead><tr><th>Parameter</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                                <tbody>
                                    <tr><td><span class="pn">uuid</span></td><td><span class="pt">string</span></td><td>UUID, nomor SJ, nomor manual, atau hash SHA-256 verifikasi.</td></tr>
                                </tbody>
                            </table>

                            <h3>Request Body</h3>
                            <table class="params-table">
                                <thead><tr><th>Field</th><th>Tipe</th><th>Status</th><th>Keterangan</th></tr></thead>
                                <tbody>
                                    <tr><td><span class="pn">recipient_name</span></td><td><span class="pt">string</span></td><td><span class="pr">WAJIB</span></td><td>Nama lengkap penerima. Maks. 255 karakter.</td></tr>
                                    <tr><td><span class="pn">recipient_signature</span></td><td><span class="pt">string</span></td><td><span class="po">OPSIONAL</span></td><td>Tanda tangan base64: <code>data:image/png;base64,...</code> Maks. 5 MB.</td></tr>
                                    <tr><td><span class="pn">receiving_notes</span></td><td><span class="pt">string</span></td><td><span class="po">OPSIONAL</span></td><td>Catatan kondisi barang. Maks. 1000 karakter.</td></tr>
                                    <tr><td><span class="pn">received_at</span></td><td><span class="pt">datetime</span></td><td><span class="po">OPSIONAL</span></td><td>Waktu ISO 8601. Default: sekarang (server time).</td></tr>
                                </tbody>
                            </table>

                            <h3>Status Kode</h3>
                            <div class="status-codes">
                                <span class="sc sc-200">200 OK</span>
                                <span class="sc sc-422">422 Validasi</span>
                                <span class="sc sc-404">404 Tidak Ada</span>
                                <span class="sc sc-413">413 Terlalu Besar</span>
                            </div>

                            <h3>Contoh Request</h3>
                            <div class="code-block">
                                <div class="code-hdr"><span class="code-lang">HTTP</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                                <div class="code-body"><pre>POST /api/v1/goods-releases/550e8400-e29b-41d4-a716-446655440000/receive
Content-Type: application/json

{
  <span class="jk">"recipient_name"</span>: <span class="js">"Budi Santoso"</span>,
  <span class="jk">"recipient_signature"</span>: <span class="js">"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA..."</span>,
  <span class="jk">"receiving_notes"</span>: <span class="js">"Diterima dalam kondisi baik, semua barang lengkap"</span>
}</pre></div>
                            </div>

                            <h3>Respons</h3>
                            <div class="resp-tabs">
                                <button class="resp-tab active" onclick="switchTab(this,'r200')">200 Berhasil</button>
                                <button class="resp-tab" onclick="switchTab(this,'r200i')">200 Sudah Dikonfirmasi</button>
                                <button class="resp-tab" onclick="switchTab(this,'r422')">422 Validasi</button>
                                <button class="resp-tab" onclick="switchTab(this,'r422c')">422 Dibatalkan</button>
                                <button class="resp-tab" onclick="switchTab(this,'r404')">404</button>
                            </div>

                            <div id="r200" class="resp-pane active">
                                <div class="code-block"><div class="code-hdr"><span class="code-lang">JSON 200</span></div><div class="code-body"><pre>{
  <span class="jk">"success"</span>: <span class="jb">true</span>,
  <span class="jk">"message"</span>: <span class="js">"Penerimaan barang berhasil dikonfirmasi."</span>,
  <span class="jk">"data"</span>: {
    <span class="jk">"uuid"</span>: <span class="js">"550e8400-e29b-41d4-a716-446655440000"</span>,
    <span class="jk">"release_number"</span>: <span class="js">"SJ-20260813-0042-1"</span>,
    <span class="jk">"status"</span>: <span class="js">"DELIVERED"</span>,
    <span class="jk">"recipient_name"</span>: <span class="js">"Budi Santoso"</span>,
    <span class="jk">"has_signature"</span>: <span class="jb">true</span>,
    <span class="jk">"recipient_signature"</span>: <span class="js">"data:image/png;base64,..."</span>,
    <span class="jk">"receiving_notes"</span>: <span class="js">"Diterima dalam kondisi baik"</span>,
    <span class="jk">"received_at"</span>: <span class="js">"2026-08-13T16:30:00+07:00"</span>,
    <span class="jk">"updated_at"</span>: <span class="js">"2026-08-13T16:30:05+07:00"</span>
  },
  <span class="jk">"already_confirmed"</span>: <span class="jb">false</span>
}</pre></div></div>
                            </div>
                            <div id="r200i" class="resp-pane">
                                <div class="code-block"><div class="code-hdr"><span class="code-lang">JSON 200 (Idempoten)</span></div><div class="code-body"><pre>{
  <span class="jk">"success"</span>: <span class="jb">true</span>,
  <span class="jk">"message"</span>: <span class="js">"Surat Jalan ini sudah pernah dikonfirmasi sebelumnya."</span>,
  <span class="jk">"data"</span>: { <span class="jc">// data konfirmasi pertama, tidak berubah</span> },
  <span class="jk">"already_confirmed"</span>: <span class="jb">true</span>
}</pre></div></div>
                                <div class="callout warning"><div>⚠️</div><div><p>Jika <code>already_confirmed: true</code>, data konfirmasi pertama dipertahankan. Nama/tanda tangan baru tidak menimpa data lama.</p></div></div>
                            </div>
                            <div id="r422" class="resp-pane">
                                <div class="code-block"><div class="code-hdr"><span class="code-lang">JSON 422</span></div><div class="code-body"><pre>{
  <span class="jk">"message"</span>: <span class="js">"The recipient name field is required."</span>,
  <span class="jk">"errors"</span>: { <span class="jk">"recipient_name"</span>: [<span class="js">"Nama penerima wajib diisi."</span>] }
}</pre></div></div>
                            </div>
                            <div id="r422c" class="resp-pane">
                                <div class="code-block"><div class="code-hdr"><span class="code-lang">JSON 422</span></div><div class="code-body"><pre>{
  <span class="jk">"success"</span>: <span class="jb">false</span>,
  <span class="jk">"message"</span>: <span class="js">"Surat Jalan yang dibatalkan tidak dapat dikonfirmasi penerimaannya."</span>
}</pre></div></div>
                            </div>
                            <div id="r404" class="resp-pane">
                                <div class="code-block"><div class="code-hdr"><span class="code-lang">JSON 404</span></div><div class="code-body"><pre>{
  <span class="jk">"message"</span>: <span class="js">"No query results for model [App\\Models\\GoodsRelease]."</span>
}</pre></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- SHOW -->
                <div class="doc-section" id="receive-show">
                    <h2>Detail Surat Jalan</h2>
                    <p>Ambil info lengkap Surat Jalan sebelum menampilkan form konfirmasi di mobile.</p>
                    <div class="endpoint-card">
                        <div class="endpoint-header" onclick="toggleEp(this)">
                            <span class="ep-method ep-GET">GET</span>
                            <span class="ep-path">/goods-releases/{uuid}</span>
                            <span class="ep-desc">Detail Surat Jalan</span>
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:auto;flex-shrink:0;transition:transform .2s"><path d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="endpoint-body">
                            <div class="code-block">
                                <div class="code-hdr"><span class="code-lang">HTTP</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                                <div class="code-body"><pre>GET /api/v1/goods-releases/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer {token}</pre></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- LOGIN -->
                <div class="doc-section" id="auth-login">
                    <h2>Login</h2>
                    <div class="endpoint-card">
                        <div class="endpoint-header" onclick="toggleEp(this)">
                            <span class="ep-method ep-POST">POST</span>
                            <span class="ep-path">/auth/login</span>
                            <span class="ep-desc">Dapatkan Bearer Token</span>
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:auto;flex-shrink:0;transition:transform .2s"><path d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="endpoint-body">
                            <table class="params-table">
                                <thead><tr><th>Field</th><th>Tipe</th><th>Status</th><th>Keterangan</th></tr></thead>
                                <tbody>
                                    <tr><td><span class="pn">email</span></td><td><span class="pt">string</span></td><td><span class="pr">WAJIB*</span></td><td>Email pengguna. *Salah satu <code>email</code>/<code>nik</code> wajib.</td></tr>
                                    <tr><td><span class="pn">nik</span></td><td><span class="pt">string</span></td><td><span class="pr">WAJIB*</span></td><td>NIK pengguna. *Salah satu <code>email</code>/<code>nik</code> wajib.</td></tr>
                                    <tr><td><span class="pn">password</span></td><td><span class="pt">string</span></td><td><span class="pr">WAJIB</span></td><td>Kata sandi pengguna.</td></tr>
                                    <tr><td><span class="pn">device_name</span></td><td><span class="pt">string</span></td><td><span class="po">OPSIONAL</span></td><td>Nama perangkat. Default: <code>mobile-app</code>.</td></tr>
                                </tbody>
                            </table>
                            <div class="code-block">
                                <div class="code-hdr"><span class="code-lang">HTTP</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                                <div class="code-body"><pre>POST /api/v1/auth/login
Content-Type: application/json

{
  <span class="jk">"email"</span>: <span class="js">"gudang@perusahaan.com"</span>,
  <span class="jk">"password"</span>: <span class="js">"password123"</span>,
  <span class="jk">"device_name"</span>: <span class="js">"mobile-android-v2"</span>
}</pre></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- AUTH ME -->
                <div class="doc-section" id="auth-me">
                    <h2>Profil Saya</h2>
                    <div class="endpoint-card">
                        <div class="endpoint-header" onclick="toggleEp(this)">
                            <span class="ep-method ep-GET">GET</span>
                            <span class="ep-path">/auth/me</span>
                            <span class="ep-desc">Data pengguna aktif</span>
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:auto;flex-shrink:0;transition:transform .2s"><path d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="endpoint-body">
                            <div class="code-block">
                                <div class="code-hdr"><span class="code-lang">HTTP</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                                <div class="code-body"><pre>GET /api/v1/auth/me
Authorization: Bearer {token}</pre></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- MOBILE FLOW -->
                <div class="doc-section" id="mobile-flow">
                    <h2>Alur Konfirmasi via QR Code</h2>
                    <p>Alur lengkap yang direkomendasikan untuk implementasi konfirmasi penerimaan barang di aplikasi mobile:</p>
                    <div class="code-block" style="background:var(--border-light);border:1px solid var(--border);">
                        <div class="code-body"><pre style="color:var(--text-muted);font-size:0.8rem;line-height:2.1;">1. User membuka aplikasi mobile
   │
   ▼
2. User memindai QR Code dari Surat Jalan fisik
   → QR berisi UUID / hash verifikasi Surat Jalan
   │
   ▼
3. App → GET /api/v1/goods-releases/{uuid}
   → Ambil detail barang, pengemudi, tanggal kirim
   → Cek received_at: null → belum dikonfirmasi
   │
   ├─ Sudah dikonfirmasi → tampilkan info konfirmasi sebelumnya
   │
   └─ Belum dikonfirmasi ▼
4. Tampilkan Form:
   ✏️  Nama Penerima (wajib)
   🖊️  Canvas Tanda Tangan (opsional)
   📝  Catatan (opsional)
   │
   ▼
5. User isi form, tekan "Konfirmasi Terima"
   │
   ▼
6. App: canvas.toDataURL('image/png') → base64
   App → POST /api/v1/goods-releases/{uuid}/receive
   │
   ▼
7. Periksa respons:
   ✅ already_confirmed: false → "Konfirmasi Berhasil!"
   ℹ️  already_confirmed: true  → "Sudah dikonfirmasi sebelumnya"
   ❌ status 422               → tampilkan pesan error ke user</pre></div>
                    </div>
                </div>

                <div class="doc-divider"></div>

                <!-- SIGNATURE GUIDE -->
                <div class="doc-section" id="signature-guide">
                    <h2>Panduan Implementasi Tanda Tangan</h2>
                    <p>Tanda tangan dikirim sebagai Base64 Data URL dari elemen <code>&lt;canvas&gt;</code>.</p>

                    <h3>Library yang Direkomendasikan</h3>
                    <table class="params-table">
                        <thead><tr><th>Platform</th><th>Library</th><th>Metode Export</th></tr></thead>
                        <tbody>
                            <tr><td>React Native</td><td><code>react-native-signature-canvas</code></td><td><code>ref.readSignature()</code> → Base64</td></tr>
                            <tr><td>Flutter</td><td><code>syncfusion_flutter_signaturepad</code></td><td><code>toImage()</code> → <code>base64Encode(bytes)</code></td></tr>
                            <tr><td>Web / PWA</td><td><code>signature_pad.js</code></td><td><code>pad.toDataURL('image/png')</code></td></tr>
                        </tbody>
                    </table>

                    <h3>Contoh JavaScript</h3>
                    <div class="code-block">
                        <div class="code-hdr"><span class="code-lang">JavaScript</span><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                        <div class="code-body"><pre>const canvas = document.getElementById('signature-canvas');
const signature = canvas.toDataURL('image/png');
<span class="jc">// → "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA..."</span>

const res = await fetch('/api/v1/goods-releases/{uuid}/receive', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    recipient_name: 'Budi Santoso',
    recipient_signature: signature,
    receiving_notes: 'Kondisi baik'
  })
});
const result = await res.json();
console.log(result.already_confirmed); <span class="jc">// false = baru dikonfirmasi</span></pre></div>
                    </div>

                    <div class="callout warning">
                        <div>⚠️</div>
                        <div><p>Ukuran tanda tangan maksimum <strong>5 MB</strong> dalam Base64. API mengembalikan <code>413</code> jika melebihi batas. Kurangi resolusi canvas sebelum export jika perlu.</p></div>
                    </div>
                </div>

            </div><!-- /guide-content -->
        </div><!-- /guide-inner -->
    </div><!-- /pane-guide -->

    <!-- TAB 3: Markdown AI Studio Prompt -->
    <div class="pane" id="pane-ai-prompt" style="background: var(--bg); overflow-y: auto;">
        <div style="max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 6rem; width: 100%;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Markdown Prompt Context — AI Studio</h1>
                    <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Format Markdown lengkap dan terstruktur. Siap untuk disalin dan ditempelkan (copy & paste) ke <strong>Google AI Studio</strong>, ChatGPT, Claude, atau Cursor untuk pembuatan / sinkronisasi aplikasi mobile & frontend.</p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('docs.api.md') }}" target="_blank" class="btn-sm" style="background: var(--content-bg);">
                        ⬇️ Raw .md File
                    </a>
                    <button onclick="copyAllMarkdown(this)" class="btn-sm primary" style="padding: 0.5rem 1.125rem; font-weight: 600; cursor: pointer;">
                        📋 Salin Semua Context untuk AI Studio
                    </button>
                </div>
            </div>

            <div class="code-block" style="background: var(--code-bg); border: 1px solid var(--border); border-radius: 12px; margin: 0;">
                <div class="code-hdr" style="padding: 0.75rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
                    <span class="code-lang" style="color: #8b949e; font-size: 0.75rem;">MARKDOWN PROMPT / SYSTEM CONTEXT</span>
                    <span style="font-size: 0.75rem; color: #79c0ff; font-family: 'JetBrains Mono', monospace;">{{ route('docs.api.md') }}</span>
                </div>
                <div class="code-body" style="padding: 1.5rem 1.25rem;">
                    <pre id="raw-markdown-content" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; line-height: 1.8; color: var(--code-text); white-space: pre-wrap; word-break: break-word;">@include('docs-api-md')</pre>
                </div>
            </div>
        </div>
    </div>

</div><!-- /page -->

<script>
    function copyAllMarkdown(btn) {
        const text = document.getElementById('raw-markdown-content').innerText;
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Tersalin ke Clipboard!';
            btn.style.background = '#22c55e';
            btn.style.borderColor = '#22c55e';
            setTimeout(() => {
                btn.innerHTML = original;
                btn.style.background = '';
                btn.style.borderColor = '';
            }, 3000);
        });
    }

    // ── Tab switcher ──────────────────────────────────────────
    function switchPane(name, btn) {
        document.querySelectorAll('.pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.header-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('pane-' + name).classList.add('active');
        btn.classList.add('active');

        // Restore active scroll on guide pane
        if (name === 'guide') updateActive();
    }

    // ── Endpoint accordion ────────────────────────────────────
    function toggleEp(header) {
        const body  = header.nextElementSibling;
        const arrow = header.querySelector('svg');
        body.classList.toggle('collapsed');
        arrow.style.transform = body.classList.contains('collapsed') ? 'rotate(-90deg)' : '';
    }

    // ── Response tabs ─────────────────────────────────────────
    function switchTab(btn, paneId) {
        const card = btn.closest('.endpoint-body');
        card.querySelectorAll('.resp-tab').forEach(t => t.classList.remove('active'));
        card.querySelectorAll('.resp-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(paneId)?.classList.add('active');
    }

    // ── Copy code ─────────────────────────────────────────────
    function copyCode(btn) {
        const pre = btn.closest('.code-block').querySelector('pre');
        navigator.clipboard.writeText(pre.innerText).then(() => {
            btn.textContent = 'Tersalin!';
            setTimeout(() => btn.textContent = 'Salin', 2000);
        });
    }

    // ── Active sidebar link on scroll ─────────────────────────
    const sections = document.querySelectorAll('.doc-section');
    const links    = document.querySelectorAll('.gs-link');
    function updateActive() {
        const pane = document.getElementById('pane-guide');
        let current = '';
        sections.forEach(s => {
            if (s.offsetTop - pane.scrollTop <= 100) current = s.id;
        });
        links.forEach(l => {
            l.classList.remove('active');
            if (l.getAttribute('href') === '#' + current) l.classList.add('active');
        });
    }
    document.getElementById('pane-guide').addEventListener('scroll', updateActive, { passive: true });
    links.forEach(l => l.addEventListener('click', e => {
        // Smooth scroll inside the pane
        const id = l.getAttribute('href').slice(1);
        const el = document.getElementById(id);
        if (el) {
            e.preventDefault();
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }));
    updateActive();

    // ── Intercept Stoplight fetch for CSRF (same as Scramble) ─
    const originalFetch = window.fetch;
    window.fetch = (url, options) => {
        const token = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN'))?.split('=')[1];
        if (token) {
            const headers = options?.headers || new Headers();
            if (headers instanceof Headers) headers.set('X-XSRF-TOKEN', decodeURIComponent(token));
            else if (Array.isArray(headers)) headers.push(['X-XSRF-TOKEN', decodeURIComponent(token)]);
            else headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
            return originalFetch(url, { ...options, headers });
        }
        return originalFetch(url, options);
    };
</script>
</body>
</html>
