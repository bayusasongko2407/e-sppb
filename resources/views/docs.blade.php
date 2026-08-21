<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi & API Reference — E-SPPB Enterprise</title>
    <meta name="description" content="Portal lengkap dokumentasi E-SPPB Enterprise: Panduan Pengguna per peran, OpenAPI Live Reference, Panduan Integrasi Mobile & QR, dan Context Prompt AI Studio.">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Stoplight Elements (OpenAPI UI) --}}
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
            --info: #3b82f6;
            --bg: #f8fafc;
            --sidebar-bg: #ffffff;
            --content-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --code-bg: #0d1117;
            --code-text: #e6edf3;
            --sidebar-width: 280px;
            --header-height: 60px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0d1117;
                --sidebar-bg: #161b22;
                --content-bg: #0d1117;
                --text-main: #e6edf3;
                --text-muted: #8b949e;
                --text-light: #6e7681;
                --border: #30363d;
                --border-light: #21262d;
                --code-bg: #161b22;
                --primary-light: #0c2039;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            line-height: 1.7;
            font-size: 15px;
        }

        /* ─── HEADER ─────────────────────────────────── */
        .header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--header-height);
            background: var(--sidebar-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            gap: 0.875rem;
            z-index: 200;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-main);
            flex-shrink: 0;
        }

        .header-logo .logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 800;
        }

        .header-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
            flex-shrink: 0;
        }

        /* HEADER TAB SWITCHER */
        .header-tabs {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: var(--border-light);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.2rem;
        }

        .header-tab {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.3125rem 0.875rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--text-muted);
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .header-tab:hover { color: var(--text-main); }
        .header-tab.active {
            background: var(--content-bg);
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            font-weight: 600;
        }
        .header-tab .tab-icon { font-size: 0.875rem; }

        .header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .btn-header {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid var(--border);
            color: var(--text-main);
            background: transparent;
            white-space: nowrap;
        }

        .btn-header:hover {
            background: var(--border-light);
        }

        .btn-header.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .btn-header.primary:hover {
            background: var(--primary-dark);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            color: var(--text-main);
        }

        /* ─── PANES ────────────────────────────────────── */
        .doc-pane {
            display: none;
            width: 100%;
        }
        .doc-pane.active {
            display: block;
        }

        /* PANE: OPENAPI ELEMENTS */
        #pane-api.doc-pane.active {
            display: flex;
            flex-direction: column;
            height: calc(100vh - var(--header-height));
            margin-top: var(--header-height);
            background: #ffffff;
        }
        @media (prefers-color-scheme: dark) {
            #pane-api.doc-pane.active {
                background: #161b22;
            }
        }
        #pane-api elements-api {
            flex: 1;
            height: 100%;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* PANE: MOBILE GUIDE & AI PROMPT */
        #pane-mobile.doc-pane.active,
        #pane-ai.doc-pane.active {
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
        }

        /* ─── LAYOUT ─────────────────────────────────── */
        .layout {
            display: flex;
            padding-top: var(--header-height);
            min-height: 100vh;
        }

        /* ─── SIDEBAR ─────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            overflow-y: auto;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            padding: 1.5rem 0 2rem;
            transition: transform 0.25s ease;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        .sidebar-section { margin-bottom: 0.25rem; }

        .sidebar-section-label {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-light);
            padding: 1rem 1.25rem 0.375rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1.25rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            text-decoration: none;
            border-left: 2px solid transparent;
            transition: all 0.15s ease;
            line-height: 1.4;
        }

        .sidebar-link:hover {
            color: var(--text-main);
            background: var(--border-light);
        }

        .sidebar-link.active {
            color: var(--primary);
            border-left-color: var(--primary);
            background: var(--primary-light);
            font-weight: 500;
        }

        .sidebar-link .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.5;
            flex-shrink: 0;
        }

        /* ─── MAIN CONTENT ─────────────────────────────────── */
        .main {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-width: 0;
        }

        .content {
            max-width: 860px;
            margin: 0 auto;
            padding: 2.5rem 2rem 6rem;
        }

        /* ─── SECTIONS ─────────────────────────────────── */
        .doc-section {
            scroll-margin-top: calc(var(--header-height) + 1.5rem);
            margin-bottom: 4rem;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.25;
            color: var(--text-main);
            margin-bottom: 1rem;
        }

        h2 {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.875rem;
            padding-bottom: 0.625rem;
            border-bottom: 1px solid var(--border);
        }

        h3 {
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 1.75rem 0 0.625rem;
        }

        h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-muted);
            margin: 1.25rem 0 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.8125rem;
        }

        p { color: var(--text-muted); margin-bottom: 0.875rem; }

        p:last-child { margin-bottom: 0; }

        /* ─── HERO ─────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--primary-light) 0%, rgba(99,102,241,0.08) 100%);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2.5rem;
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--content-bg);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }

        .badge.blue { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .badge.green { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .badge.purple { background: #f5f3ff; border-color: #ddd6fe; color: #5b21b6; }
        .badge.orange { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
        @media (prefers-color-scheme: dark) {
            .badge.blue { background: #1e3a5f; border-color: #1d4ed8; color: #93c5fd; }
            .badge.green { background: #14532d; border-color: #166534; color: #86efac; }
            .badge.purple { background: #2e1065; border-color: #5b21b6; color: #c4b5fd; }
            .badge.orange { background: #431407; border-color: #9a3412; color: #fdba74; }
        }

        /* ─── CALLOUTS ─────────────────────────────────── */
        .callout {
            display: flex;
            gap: 0.875rem;
            padding: 1rem 1.125rem;
            border-radius: 8px;
            margin: 1.25rem 0;
            border: 1px solid;
        }

        .callout-icon { font-size: 1.125rem; flex-shrink: 0; margin-top: 1px; }

        .callout-body { flex: 1; min-width: 0; }

        .callout-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .callout-text {
            font-size: 0.875rem;
            margin: 0;
            line-height: 1.6;
        }

        .callout.info { background: #eff6ff; border-color: #bfdbfe; }
        .callout.info .callout-title { color: #1e40af; }
        .callout.info .callout-text { color: #1e40af; }

        .callout.warning { background: #fffbeb; border-color: #fde68a; }
        .callout.warning .callout-title { color: #92400e; }
        .callout.warning .callout-text { color: #92400e; }

        .callout.success { background: #f0fdf4; border-color: #bbf7d0; }
        .callout.success .callout-title { color: #14532d; }
        .callout.success .callout-text { color: #14532d; }

        .callout.danger { background: #fef2f2; border-color: #fecaca; }
        .callout.danger .callout-title { color: #7f1d1d; }
        .callout.danger .callout-text { color: #7f1d1d; }

        @media (prefers-color-scheme: dark) {
            .callout.info { background: #0c1d35; border-color: #1d4ed8; }
            .callout.info .callout-title, .callout.info .callout-text { color: #93c5fd; }
            .callout.warning { background: #2d1b00; border-color: #d97706; }
            .callout.warning .callout-title, .callout.warning .callout-text { color: #fcd34d; }
            .callout.success { background: #052e16; border-color: #166534; }
            .callout.success .callout-title, .callout.success .callout-text { color: #86efac; }
            .callout.danger { background: #3b0000; border-color: #7f1d1d; }
            .callout.danger .callout-title, .callout.danger .callout-text { color: #fca5a5; }
        }

        /* ─── TABLES ─────────────────────────────────── */
        .table-wrapper {
            overflow-x: auto;
            margin: 1.25rem 0;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        thead {
            background: var(--border-light);
        }

        th {
            padding: 0.625rem 0.875rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: 0.625rem 0.875rem;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-muted);
            vertical-align: top;
        }

        tbody tr:last-child td { border-bottom: none; }

        tbody tr:hover { background: var(--border-light); }

        /* ─── STATUS BADGES ─────────────────────────────────── */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-gray { background: #f1f5f9; color: #64748b; }
        .status-yellow { background: #fef9c3; color: #854d0e; }
        .status-blue { background: #eff6ff; color: #1d4ed8; }
        .status-green { background: #f0fdf4; color: #166534; }
        .status-red { background: #fef2f2; color: #991b1b; }
        .status-darkgray { background: #e2e8f0; color: #374151; }

        @media (prefers-color-scheme: dark) {
            .status-gray { background: #1e293b; color: #94a3b8; }
            .status-yellow { background: #2d1b00; color: #fcd34d; }
            .status-blue { background: #1e3a5f; color: #93c5fd; }
            .status-green { background: #052e16; color: #86efac; }
            .status-red { background: #3b0000; color: #fca5a5; }
            .status-darkgray { background: #0f172a; color: #94a3b8; }
        }

        /* ─── ACCESS ICONS ─────────────────────────────────── */
        .check { color: #22c55e; font-weight: 700; }
        .cross { color: #ef4444; font-weight: 700; }
        .partial { color: #f59e0b; font-weight: 700; }

        /* ─── STEPS ─────────────────────────────────── */
        .steps { margin: 1rem 0; }

        .step {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.875rem;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 32px;
            bottom: -8px;
            width: 1px;
            background: var(--border);
        }

        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            font-size: 0.8125rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .step-body { flex: 1; padding-top: 0.375rem; }

        .step-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .step-desc {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* ─── ROLE CARDS ─────────────────────────────────── */
        .role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1rem;
            margin: 1.25rem 0;
        }

        .role-card {
            background: var(--content-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.125rem;
            transition: box-shadow 0.15s ease;
        }

        .role-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

        .role-card-header {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 0.75rem;
        }

        .role-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .role-name { font-weight: 600; font-size: 0.9375rem; color: var(--text-main); }

        .role-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.6875rem;
            color: var(--text-light);
            background: var(--code-bg);
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
        }

        .role-list { list-style: none; }

        .role-list li {
            font-size: 0.8125rem;
            color: var(--text-muted);
            padding: 0.2rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.375rem;
        }

        .role-list li::before {
            content: '→';
            color: var(--primary);
            font-size: 0.75rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ─── FLOW DIAGRAM ─────────────────────────────────── */
        .flow {
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8125rem;
            color: var(--text-muted);
            overflow-x: auto;
            line-height: 1.8;
            white-space: pre;
            margin: 1.25rem 0;
        }

        /* ─── CODE INLINE ─────────────────────────────────── */
        code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8125rem;
            background: var(--code-bg);
            border: 1px solid var(--border);
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            color: var(--primary-dark);
        }

        /* ─── TOC MOBILE ─────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 50;
        }

        /* ─── DIVIDER ─────────────────────────────────── */
        .doc-divider {
            height: 1px;
            background: var(--border);
            margin: 2.5rem 0;
        }

        /* ─── RESPONSIVE ─────────────────────────────────── */
        @media (max-width: 768px) {
            .hamburger { display: flex; }

            .sidebar {
                transform: translateX(-100%);
                z-index: 60;
                width: 280px;
            }

            .sidebar.open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0,0,0,0.15);
            }

            .sidebar-overlay.open { display: block; }

            .main { margin-left: 0; }

            .content { padding: 1.5rem 1rem 4rem; }

            h1 { font-size: 1.5rem; }

            .role-grid { grid-template-columns: 1fr; }

            .header-actions .btn-header.secondary { display: none; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Buka menu">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <a href="/docs" class="header-logo">
        <div class="logo-icon">E</div>
        <span>{{ \App\Models\AppSetting::get('app_custom_name', 'E-SPPB') }}</span>
    </a>
    <div class="header-divider"></div>

    <div class="header-tabs">
        <button class="header-tab active" onclick="switchDocTab('manual', this)" id="tab-btn-manual">
            <span class="tab-icon">📘</span> <span>Panduan Pengguna</span>
        </button>
        <button class="header-tab" onclick="switchDocTab('api', this)" id="tab-btn-api">
            <span class="tab-icon">⚡</span> <span>API Reference</span>
        </button>
        <button class="header-tab" onclick="switchDocTab('mobile', this)" id="tab-btn-mobile">
            <span class="tab-icon">📱</span> <span>Panduan Mobile</span>
        </button>
        <button class="header-tab" onclick="switchDocTab('ai', this)" id="tab-btn-ai">
            <span class="tab-icon">🤖</span> <span>AI Studio Prompt</span>
        </button>
    </div>

    <div class="header-actions">
        <a href="{{ route('docs.api.md') }}" target="_blank" class="btn-header" title="Download raw markdown context">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            api.md
        </a>
        <a href="{{ url('/verify/document') }}" class="btn-header">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verifikasi
        </a>
        <a href="/admin" class="btn-header primary">
            Masuk ke Aplikasi
        </a>
    </div>
</header>

<!-- PANE 1: USER MANUAL (Panduan Pengguna) -->
<div id="pane-manual" class="doc-pane active">

    <!-- SIDEBAR OVERLAY -->
    <div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div class="layout">

        <!-- SIDEBAR -->
        <nav class="sidebar" id="sidebar" aria-label="Navigasi dokumentasi">

        <div class="sidebar-section">
            <div class="sidebar-section-label">Memulai</div>
            <a href="#pengantar" class="sidebar-link active"><span class="dot"></span>Pengantar Sistem</a>
            <a href="#cara-login" class="sidebar-link"><span class="dot"></span>Cara Login</a>
            <a href="#navigasi" class="sidebar-link"><span class="dot"></span>Navigasi Aplikasi</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Peran Pengguna</div>
            <a href="#roles-overview" class="sidebar-link"><span class="dot"></span>Ringkasan Peran</a>
            <a href="#panduan-pemohon" class="sidebar-link"><span class="dot"></span>Panduan Pemohon</a>
            <a href="#panduan-approver" class="sidebar-link"><span class="dot"></span>Panduan Approver</a>
            <a href="#panduan-manager" class="sidebar-link"><span class="dot"></span>Panduan Manager</a>
            <a href="#panduan-gudang" class="sidebar-link"><span class="dot"></span>Panduan Gudang</a>
            <a href="#panduan-superadmin" class="sidebar-link"><span class="dot"></span>Panduan Super Admin</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Alur Dokumen</div>
            <a href="#siklus-sppb" class="sidebar-link"><span class="dot"></span>Siklus Hidup SPPB</a>
            <a href="#status-sppb" class="sidebar-link"><span class="dot"></span>Status SPPB</a>
            <a href="#status-surat-jalan" class="sidebar-link"><span class="dot"></span>Status Surat Jalan</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Aturan Bisnis</div>
            <a href="#aturan-sppb" class="sidebar-link"><span class="dot"></span>Aturan SPPB</a>
            <a href="#aturan-surat-jalan" class="sidebar-link"><span class="dot"></span>Aturan Surat Jalan</a>
            <a href="#aturan-workflow" class="sidebar-link"><span class="dot"></span>Aturan Alur Persetujuan</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Fitur Sistem</div>
            <a href="#notifikasi" class="sidebar-link"><span class="dot"></span>Sistem Notifikasi</a>
            <a href="#verifikasi-dokumen" class="sidebar-link"><span class="dot"></span>Verifikasi Dokumen</a>
            <a href="#delegasi" class="sidebar-link"><span class="dot"></span>Delegasi Wewenang</a>
            <a href="#laporan" class="sidebar-link"><span class="dot"></span>Laporan & Ekspor</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Pengaturan</div>
            <a href="#pengaturan-aplikasi" class="sidebar-link"><span class="dot"></span>Pengaturan Aplikasi</a>
            <a href="#pengaturan-notifikasi" class="sidebar-link"><span class="dot"></span>Pengaturan Notifikasi</a>
            <a href="#template-workflow" class="sidebar-link"><span class="dot"></span>Template Workflow</a>
            <a href="#hak-akses" class="sidebar-link"><span class="dot"></span>Hak Akses Dokumen</a>
        </div>

    </nav>

    <!-- MAIN CONTENT -->
    <main class="main">
        <div class="content">

            <!-- ────────────────────────────── -->
            <!-- PENGANTAR -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="pengantar">
                <div class="section-tag">📋 Dokumentasi</div>
                <h1>E-SPPB Enterprise</h1>
                <p>Manual lengkap penggunaan Sistem Permohonan Pengiriman Barang berbasis web untuk kebutuhan operasional logistik dan distribusi internal perusahaan.</p>

                <div class="hero">
                    <p style="color: var(--text-main); font-weight: 500; margin-bottom: 0.5rem;">Apa itu E-SPPB Enterprise?</p>
                    <p style="margin: 0;">E-SPPB Enterprise adalah sistem digitalisasi proses pengajuan, persetujuan bertingkat, dan penerbitan Surat Jalan (SAT) untuk pengeluaran barang dari gudang. Seluruh proses dilakukan secara elektronik dengan jejak audit penuh dan notifikasi real-time.</p>
                    <div class="hero-badges">
                        <span class="badge blue">Laravel 12</span>
                        <span class="badge purple">Filament v5</span>
                        <span class="badge green">PHP 8.3</span>
                        <span class="badge orange">Livewire v4</span>
                    </div>
                </div>

                <h3>Konsep Utama</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Istilah</th><th>Penjelasan</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>SPPB</strong></td><td>Surat Permohonan Pengiriman Barang — dokumen pengajuan utama yang dibuat oleh Pemohon.</td></tr>
                            <tr><td><strong>Surat Jalan (SAT)</strong></td><td>Dokumen resmi yang diterbitkan setelah SPPB disetujui, berisi data pengiriman, pengemudi, dan armada.</td></tr>
                            <tr><td><strong>Plant</strong></td><td>Unit bisnis atau lokasi pabrik tertinggi dalam hirarki organisasi aplikasi.</td></tr>
                            <tr><td><strong>Departemen</strong></td><td>Sub-unit di bawah Plant tempat pengguna dan dokumen dikelompokkan.</td></tr>
                            <tr><td><strong>Workflow</strong></td><td>Alur persetujuan bertingkat yang harus dilewati setiap SPPB sebelum disetujui.</td></tr>
                            <tr><td><strong>Approver</strong></td><td>Pengguna yang berwenang menyetujui atau menolak dokumen SPPB.</td></tr>
                            <tr><td><strong>BAT</strong></td><td>Bagian Aset Tetap — tim yang melakukan verifikasi teknis sebelum persetujuan akhir.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- CARA LOGIN -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="cara-login">
                <h2>Cara Login</h2>

                <div class="steps">
                    <div class="step">
                        <div class="step-num">1</div>
                        <div class="step-body">
                            <div class="step-title">Buka Alamat Aplikasi</div>
                            <p class="step-desc">Buka peramban (Chrome, Firefox, Edge) dan akses URL aplikasi yang diberikan oleh administrator sistem Anda.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <div class="step-body">
                            <div class="step-title">Masukkan Kredensial</div>
                            <p class="step-desc">Isi kolom <strong>Email</strong> dengan alamat email yang didaftarkan oleh admin, dan kolom <strong>Password</strong> dengan kata sandi Anda.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <div class="step-body">
                            <div class="step-title">Klik "Masuk"</div>
                            <p class="step-desc">Tekan tombol <strong>Masuk</strong>. Sistem akan memuat dashboard sesuai dengan peran Anda.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num">4</div>
                        <div class="step-body">
                            <div class="step-title">Ganti Password (Pertama Kali)</div>
                            <p class="step-desc">Jika ini login pertama Anda, segera ganti password melalui menu <strong>Profil Saya → Ubah Password</strong>.</p>
                        </div>
                    </div>
                </div>

                <div class="callout warning">
                    <div class="callout-icon">⚠️</div>
                    <div class="callout-body">
                        <div class="callout-title">Batas Percobaan Login</div>
                        <p class="callout-text">Sistem membatasi percobaan login yang gagal. Jika akun terkunci, hubungi Super Admin untuk melakukan reset.</p>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- NAVIGASI -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="navigasi">
                <h2>Navigasi Aplikasi</h2>
                <p>Sidebar kiri menampilkan menu sesuai peran Anda. Menu yang tidak relevan dengan peran Anda tidak akan tampil.</p>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Grup Menu</th><th>Menu</th><th>Pemohon</th><th>Approver</th><th>Manager</th><th>Gudang</th><th>Super Admin</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="3"><strong>Transaksi</strong></td>
                                <td>Dokumen SPPB</td>
                                <td class="check">✓</td><td class="check">✓</td><td class="check">✓</td><td class="check">✓</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Kotak Masuk Saya</td>
                                <td class="cross">✗</td><td class="check">✓</td><td class="check">✓</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Surat Jalan</td>
                                <td class="check">✓</td><td class="check">✓</td><td class="check">✓</td><td class="check">✓</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td rowspan="4"><strong>Referensi</strong></td>
                                <td>Daftar Barang</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Satuan</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Aset</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Lokasi</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td rowspan="5"><strong>Organisasi</strong></td>
                                <td>Plant & Departemen</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Pengguna & Jabatan</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Hak Akses Dokumen</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Template Workflow</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Delegasi Wewenang</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td><strong>Laporan</strong></td>
                                <td>Laporan Transaksi</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td rowspan="4"><strong>Pengaturan</strong></td>
                                <td>Pengaturan Aplikasi</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Pengaturan Notifikasi</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Kesehatan Sistem</td>
                                <td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td><td class="check">✓</td>
                            </tr>
                            <tr>
                                <td>Profil Saya</td>
                                <td class="check">✓</td><td class="check">✓</td><td class="check">✓</td><td class="check">✓</td><td class="check">✓</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- ROLES OVERVIEW -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="roles-overview">
                <h2>Ringkasan Peran Pengguna</h2>
                <p>Sistem memiliki 6 peran. Setiap pengguna hanya memiliki satu peran aktif yang menentukan seluruh akses dan tampilan menu.</p>

                <div class="role-grid">
                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#fef3c7;">🙋</div>
                            <div>
                                <div class="role-name">Pemohon</div>
                                <span class="role-code">Pemohon</span>
                            </div>
                        </div>
                        <ul class="role-list">
                            <li>Membuat & mengedit dokumen SPPB</li>
                            <li>Mengajukan persetujuan</li>
                            <li>Edit & re-submit SPPB yang ditolak</li>
                            <li>Membatalkan permohonan</li>
                            <li>Melihat status pengiriman</li>
                        </ul>
                    </div>

                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#dbeafe;">✋</div>
                            <div>
                                <div class="role-name">Approver</div>
                                <span class="role-code">approver</span>
                            </div>
                        </div>
                        <ul class="role-list">
                            <li>Menyetujui atau menolak SPPB</li>
                            <li>Melihat dokumen di Plant/Dept.</li>
                            <li>Eskalasi ke Manager (opsional)</li>
                            <li>Menerima notifikasi persetujuan</li>
                        </ul>
                    </div>

                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#dcfce7;">👔</div>
                            <div>
                                <div class="role-name">Manager</div>
                                <span class="role-code">manager</span>
                            </div>
                        </div>
                        <ul class="role-list">
                            <li>Persetujuan tingkat Manager Plant</li>
                            <li>Melihat laporan Plant/Dept.</li>
                            <li>Melihat dashboard KPI</li>
                        </ul>
                    </div>

                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#f3e8ff;">🔍</div>
                            <div>
                                <div class="role-name">Gudang</div>
                                <span class="role-code">gudang</span>
                            </div>
                        </div>
                        <ul class="role-list">
                            <li><strong>View-Only</strong> SPPB & Surat Jalan</li>
                            <li>Memantau status pengiriman</li>
                            <li>Menutup transaksi SPPB parsial</li>
                            <li>Tidak bisa membuat/edit dokumen</li>
                        </ul>
                    </div>

                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#ffe4e6;">🔑</div>
                            <div>
                                <div class="role-name">Super Admin</div>
                                <span class="role-code">super_admin</span>
                            </div>
                        </div>
                        <ul class="role-list">
                            <li>Akses penuh seluruh sistem</li>
                            <li>Manajemen pengguna & peran</li>
                            <li>Konfigurasi sistem & notifikasi</li>
                            <li>Template workflow & hak akses</li>
                        </ul>
                    </div>

                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#f1f5f9;">⚙️</div>
                            <div>
                                <div class="role-name">Admin</div>
                                <span class="role-code">admin</span>
                            </div>
                        </div>
                        <ul class="role-list">
                            <li>Akses sesuai Document Access</li>
                            <li>Manajemen data referensi</li>
                            <li>Tanpa akses pengaturan kritis</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PANDUAN PEMOHON -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="panduan-pemohon">
                <h2>🙋 Panduan Pemohon</h2>
                <p>Pemohon adalah pengguna yang mengajukan permohonan pengiriman barang melalui dokumen SPPB.</p>

                <h3>Membuat SPPB Baru</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Menu Dokumen SPPB</div><p class="step-desc">Klik menu <strong>Dokumen SPPB</strong> di sidebar grup Transaksi.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Klik "Buat SPPB"</div><p class="step-desc">Tekan tombol <strong>Buat SPPB</strong> di pojok kanan atas halaman daftar.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Isi Formulir Header</div><p class="step-desc">Isi <strong>Plant</strong>, <strong>Departemen</strong>, <strong>Lokasi Asal</strong>, <strong>Lokasi Tujuan</strong>, dan <strong>Keperluan</strong> pengiriman.</p></div></div>
                    <div class="step"><div class="step-num">4</div><div class="step-body"><div class="step-title">Tambahkan Rincian Barang</div><p class="step-desc">Klik <strong>"+ Tambah Barang"</strong> pada tabel Rincian. Isi nama barang, kuantitas, satuan, dan keterangan untuk setiap item.</p></div></div>
                    <div class="step"><div class="step-num">5</div><div class="step-body"><div class="step-title">Simpan sebagai Draft</div><p class="step-desc">Klik <strong>Simpan</strong>. Dokumen tersimpan sebagai <span class="status status-gray">Draft</span> dan dapat diedit kembali.</p></div></div>
                    <div class="step"><div class="step-num">6</div><div class="step-body"><div class="step-title">Ajukan Persetujuan</div><p class="step-desc">Setelah data lengkap dan diperiksa, klik tombol <strong>"Ajukan Persetujuan"</strong> dan konfirmasi dengan <strong>"Ya, Ajukan"</strong>.</p></div></div>
                </div>

                <div class="callout warning">
                    <div class="callout-icon">⚠️</div>
                    <div class="callout-body">
                        <div class="callout-title">Dokumen Tidak Dapat Diubah Setelah Diajukan</div>
                        <p class="callout-text">Setelah SPPB diajukan ke alur persetujuan, dokumen dikunci dan tidak dapat diedit sampai mendapat keputusan dari Approver.</p>
                    </div>
                </div>

                <h3>Mengedit & Mengajukan Ulang SPPB yang Ditolak</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka SPPB Berstatus Ditolak</div><p class="step-desc">Cari dokumen dengan status <span class="status status-red">Ditolak</span> di daftar SPPB Anda.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Klik Tombol Edit</div><p class="step-desc">Tekan tombol <strong>Edit</strong> untuk mengubah rincian barang atau data lainnya.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Perbaiki & Ajukan Ulang</div><p class="step-desc">Setelah diperbaiki, klik <strong>"Ajukan Persetujuan"</strong>. Sistem membuat alur persetujuan baru dari awal.</p></div></div>
                </div>

                <h3>Membatalkan Permohonan SPPB</h3>
                <div class="callout danger">
                    <div class="callout-icon">🚫</div>
                    <div class="callout-body">
                        <div class="callout-title">Pembatalan Bersifat Permanen</div>
                        <p class="callout-text">Dokumen yang dibatalkan berstatus <span class="status status-darkgray">Dibatalkan</span> dan tidak dapat diajukan kembali dalam kondisi apapun.</p>
                    </div>
                </div>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka SPPB Berstatus Ditolak</div><p class="step-desc">Pembatalan hanya dapat dilakukan pada dokumen berstatus <span class="status status-red">Ditolak</span>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Klik "Batalkan Permohonan"</div><p class="step-desc">Tekan tombol merah <strong>"Batalkan Permohonan"</strong> di halaman detail SPPB.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Isi Alasan & Konfirmasi</div><p class="step-desc">Isi alasan pembatalan (wajib, minimal 10 karakter) lalu tekan <strong>"Ya, Batalkan Dokumen"</strong>.</p></div></div>
                </div>

                <h3>Menutup Sisa Pengiriman (Selesaikan SPPB)</h3>
                <p>Jika SPPB sudah dikirim sebagian dan sisa kuantitas resmi tidak akan dikirim lagi:</p>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka SPPB Berstatus Proses Pengeluaran Barang</div><p class="step-desc">Cari dokumen berstatus <span class="status status-blue">Proses Pengeluaran Barang</span>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Klik "Selesaikan SPPB"</div><p class="step-desc">Tekan tombol hijau <strong>"Selesaikan SPPB"</strong> dengan ikon centang.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Isi Catatan & Konfirmasi</div><p class="step-desc">Isi catatan penutupan (opsional) lalu klik <strong>"Ya, Selesaikan SPPB"</strong>. Status berubah ke <span class="status status-green">Selesai</span>.</p></div></div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PANDUAN APPROVER -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="panduan-approver">
                <h2>✋ Panduan Approver / Verifikator</h2>
                <p>Approver berwenang meninjau dan mengambil keputusan terhadap SPPB yang diajukan dalam lingkup Plant dan Departemen mereka.</p>

                <h3>Menyetujui SPPB</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Kotak Masuk Saya</div><p class="step-desc">Klik menu <strong>Kotak Masuk Saya</strong> di sidebar. Daftar SPPB yang menunggu keputusan Anda akan ditampilkan.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Tinjau Dokumen</div><p class="step-desc">Klik dokumen SPPB dan periksa seluruh rincian: barang, kuantitas, lokasi asal/tujuan, keperluan, dan lampiran.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Klik "Setujui"</div><p class="step-desc">Tekan tombol hijau <strong>"Setujui"</strong>. Isi catatan (opsional). Aktifkan toggle <em>"Membutuhkan Persetujuan Manager Plant"</em> jika perlu eskalasi.</p></div></div>
                    <div class="step"><div class="step-num">4</div><div class="step-body"><div class="step-title">Konfirmasi</div><p class="step-desc">Tekan <strong>"Ya, Setujui"</strong>. Sistem mengirimkan notifikasi ke Pemohon dan ke Approver tahap berikutnya (jika ada).</p></div></div>
                </div>

                <h3>Menolak SPPB</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Kotak Masuk Saya & Pilih Dokumen</div><p class="step-desc">Sama seperti langkah persetujuan, buka dokumen dari <strong>Kotak Masuk Saya</strong>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Klik "Tolak"</div><p class="step-desc">Tekan tombol merah <strong>"Tolak"</strong>. Isi <strong>alasan penolakan</strong> dengan jelas (wajib diisi).</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Konfirmasi</div><p class="step-desc">Tekan <strong>"Ya, Tolak"</strong>. Status SPPB berubah ke <span class="status status-red">Ditolak</span> dan Pemohon menerima notifikasi beserta alasan.</p></div></div>
                </div>

                <div class="callout info">
                    <div class="callout-icon">ℹ️</div>
                    <div class="callout-body">
                        <div class="callout-title">Penolakan Tidak Final bagi Pemohon</div>
                        <p class="callout-text">Penolakan memberi kesempatan Pemohon untuk memperbaiki dan mengajukan ulang SPPB, atau membatalkannya secara permanen. Tidak ada status "Revisi Diperlukan" — hanya Setujui atau Tolak.</p>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PANDUAN MANAGER -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="panduan-manager">
                <h2>👔 Panduan Manager</h2>
                <p>Manager melakukan persetujuan tingkat akhir untuk dokumen yang di-eskalasi oleh Approver.</p>

                <h3>Menyetujui/Menolak SPPB Tingkat Manager</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Kotak Masuk Saya</div><p class="step-desc">Dokumen dengan status <span class="status status-yellow">Menunggu Persetujuan Manager</span> akan tampil di <strong>Kotak Masuk Saya</strong>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Tinjau Dokumen</div><p class="step-desc">Periksa rincian barang, histori persetujuan tahap sebelumnya, dan catatan dari Approver.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Pilih Tindakan</div><p class="step-desc"><strong>Setujui</strong> → Status berubah ke <span class="status status-green">Disetujui</span>. <strong>Tolak</strong> → Status berubah ke <span class="status status-red">Ditolak</span> dengan notifikasi ke Pemohon.</p></div></div>
                </div>

                <h3>Mengakses Laporan</h3>
                <p>Manager dapat mengakses menu <strong>Laporan → Laporan Transaksi</strong> untuk melihat seluruh data SPPB dan Surat Jalan di Plant mereka, serta mengunduh laporan dalam format <strong>Excel</strong> atau <strong>PDF</strong>.</p>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PANDUAN GUDANG -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="panduan-gudang">
                <h2>🔍 Panduan Gudang</h2>
                <p>Pengguna dengan peran <strong>Gudang</strong> adalah pengamat operasional yang memantau status pengiriman tanpa kemampuan mengubah data.</p>

                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Aksi</th><th>Dapat Dilakukan?</th></tr></thead>
                        <tbody>
                            <tr><td>Melihat daftar semua SPPB</td><td class="check">✓ Ya</td></tr>
                            <tr><td>Membuka detail dokumen SPPB</td><td class="check">✓ Ya</td></tr>
                            <tr><td>Melihat daftar semua Surat Jalan</td><td class="check">✓ Ya</td></tr>
                            <tr><td>Membuka detail Surat Jalan</td><td class="check">✓ Ya</td></tr>
                            <tr><td>Menekan tombol "Selesaikan SPPB"</td><td class="check">✓ Ya (khusus)</td></tr>
                            <tr><td>Membuat SPPB baru</td><td class="cross">✗ Tidak</td></tr>
                            <tr><td>Mengedit SPPB</td><td class="cross">✗ Tidak</td></tr>
                            <tr><td>Membuat / membatalkan Surat Jalan</td><td class="cross">✗ Tidak</td></tr>
                            <tr><td>Menyetujui / Menolak SPPB</td><td class="cross">✗ Tidak</td></tr>
                            <tr><td>Mengakses menu Pengaturan</td><td class="cross">✗ Tidak</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="callout success">
                    <div class="callout-icon">✅</div>
                    <div class="callout-body">
                        <div class="callout-title">Khusus: Menutup Transaksi SPPB Parsial</div>
                        <p class="callout-text">Pengguna Gudang dapat menekan tombol <strong>"Selesaikan SPPB"</strong> pada dokumen berstatus <em>Proses Pengeluaran Barang</em> jika sisa kuantitas barang resmi tidak akan dikirim lagi. Tindakan ini mengubah status menjadi <em>Selesai</em> secara permanen.</p>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PANDUAN SUPER ADMIN -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="panduan-superadmin">
                <h2>🔑 Panduan Super Admin</h2>
                <p>Super Admin memiliki kontrol penuh atas seluruh aspek sistem E-SPPB Enterprise.</p>

                <h3>Menambah Pengguna Baru</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Menu Pengguna</div><p class="step-desc">Navigasi ke <strong>Organisasi → Pengguna</strong> dan klik <strong>"Buat Pengguna"</strong>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Isi Data Pengguna</div><p class="step-desc">Isi nama, NIK, email, password, Plant, dan Departemen pengguna.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Tentukan Peran</div><p class="step-desc">Pilih salah satu peran: <code>Pemohon</code>, <code>approver</code>, <code>manager</code>, <code>gudang</code>, atau <code>admin</code>.</p></div></div>
                    <div class="step"><div class="step-num">4</div><div class="step-body"><div class="step-title">Simpan & Informasikan</div><p class="step-desc">Klik <strong>Simpan</strong>. Informasikan kredensial login kepada pengguna yang bersangkutan.</p></div></div>
                </div>

                <h3>Mengatur Template Workflow</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Template Workflow</div><p class="step-desc">Navigasi ke <strong>Organisasi → Template Workflow</strong>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Buat Template Baru</div><p class="step-desc">Pilih <strong>Plant</strong> dan <strong>Departemen</strong> yang akan menggunakan template ini.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Tambahkan Tahapan</div><p class="step-desc">Tambahkan satu atau lebih tahapan persetujuan. Tentukan jabatan Approver untuk setiap tahap.</p></div></div>
                    <div class="step"><div class="step-num">4</div><div class="step-body"><div class="step-title">Aktifkan Template</div><p class="step-desc">Pastikan template berstatus <strong>Aktif</strong> sebelum SPPB dari kombinasi Plant-Departemen tersebut dapat diajukan.</p></div></div>
                </div>

                <div class="callout warning">
                    <div class="callout-icon">⚠️</div>
                    <div class="callout-body">
                        <div class="callout-title">Template Workflow Wajib Ada</div>
                        <p class="callout-text">Setiap kombinasi Plant-Departemen <strong>wajib memiliki Template Workflow aktif</strong>. Jika belum ada, Pemohon tidak dapat mengajukan SPPB dan akan mendapat pesan error.</p>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- SIKLUS HIDUP SPPB -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="siklus-sppb">
                <h2>Siklus Hidup Dokumen SPPB</h2>
                <p>Berikut adalah alur lengkap perjalanan dokumen SPPB dari awal hingga selesai atau dibatalkan.</p>

                <div class="flow">┌─────────────────────────────────────────────────────────────────┐
│                    SIKLUS HIDUP DOKUMEN SPPB                    │
└─────────────────────────────────────────────────────────────────┘

  [DRAFT] ──(Pemohon: Ajukan)──▶ [MENUNGGU PERSETUJUAN]
     │                                     │
     │ Edit / Hapus                        ├──▶ [MENUNGGU VERIFIKASI BAT]
     │                                     │         │
     │                                     │    (BAT Selesai Verifikasi)
     │                                     │         │
     │                                     ▼         ▼
     │                          [MENUNGGU PERSETUJUAN MANAGER]
     │                                     │
     │                            (Manager: Setuju)
     │                                     │
     │                                     ▼
     │                               [DISETUJUI]
     │                                     │
     │                    ┌────────────────┼────────────────┐
     │                    ▼                                 ▼
     │        [PROSES PENGELUARAN BARANG]         [SELESAI]
     │        (Rilis Parsial)                (Rilis Penuh)
     │                    │
     │          ┌─────────┴──────────────────┐
     │          ▼                            ▼
     │       [SELESAI]              [SELESAI] ← "Selesaikan SPPB"
     │      (Rilis Sisa)           (Force Complete)
     │
     │
  (Approver/Manager: Tolak)
     ▼
  [DITOLAK]
     │
     ├──(Pemohon: Edit & Ajukan Ulang)──▶ [MENUNGGU PERSETUJUAN]
     │
     └──(Pemohon: Batalkan Permohonan)──▶ [DIBATALKAN] ★ TERMINAL</div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- STATUS SPPB -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="status-sppb">
                <h2>Status Dokumen SPPB</h2>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Status</th><th>Kode Internal</th><th>Keterangan</th><th>Dapat Diedit?</th><th>Status Akhir?</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><span class="status status-gray">Draft</span></td><td><code>DRAFT</code></td><td>Baru dibuat, belum diajukan. Pemohon dapat mengedit & menghapus.</td><td class="check">✓</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-yellow">Sedang Diproses</span></td><td><code>SUBMISSION_QUEUED</code></td><td>Pengajuan sedang diproses sistem (antrian). Berlangsung sangat singkat.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-yellow">Menunggu Persetujuan</span></td><td><code>WAITING_APPROVAL</code></td><td>Menunggu keputusan Approver pertama.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-yellow">Menunggu Verifikasi BAT</span></td><td><code>WAITING_VERIFICATION_BAT</code></td><td>Menunggu pemeriksaan teknis dari Tim BAT.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-blue">Proses Verifikasi BAT</span></td><td><code>PROCESS_VERIFICATION_BAT</code></td><td>Tim BAT sedang aktif memverifikasi dokumen.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-yellow">Menunggu Persetujuan Manager</span></td><td><code>WAITING_APPROVAL_MANAGER</code></td><td>Eskalasi ke Manager Plant, menunggu keputusan.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-green">Disetujui</span></td><td><code>APPROVED</code></td><td>Semua tahap persetujuan selesai. Siap diterbitkan Surat Jalan.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-blue">Proses Pengeluaran Barang</span></td><td><code>RELEASE_IN_PROGRESS</code></td><td>Sebagian barang sudah dikeluarkan, sisanya menunggu rilis.</td><td class="cross">✗</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-green">Selesai</span></td><td><code>COMPLETED</code></td><td>Semua barang dirilis 100% ATAU transaksi ditutup resmi.</td><td class="cross">✗</td><td class="check">✓</td></tr>
                            <tr><td><span class="status status-red">Ditolak</span></td><td><code>REJECTED</code></td><td>Ditolak Approver. Pemohon dapat edit & re-submit atau batalkan.</td><td class="check">✓</td><td class="cross">✗</td></tr>
                            <tr><td><span class="status status-darkgray">Dibatalkan</span></td><td><code>CANCELLED</code></td><td>Dibatalkan permanen oleh Pemohon. Tidak dapat dibuka kembali.</td><td class="cross">✗</td><td class="check">✓</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- STATUS SURAT JALAN -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="status-surat-jalan">
                <h2>Status Surat Jalan (Goods Release)</h2>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Status</th><th>Kode Internal</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><span class="status status-gray">Draft</span></td><td><code>DRAFT</code></td><td>Surat Jalan baru dibuat, belum diterbitkan untuk pengiriman.</td></tr>
                            <tr><td><span class="status status-blue">Dalam Pengiriman</span></td><td><code>RELEASED</code></td><td>Surat Jalan terbit, barang telah keluar dari gudang asal.</td></tr>
                            <tr><td><span class="status status-blue">Dalam Perjalanan</span></td><td><code>IN_TRANSIT</code></td><td>Barang sedang dalam perjalanan oleh ekspedisi/driver.</td></tr>
                            <tr><td><span class="status status-green">Sudah Diterima</span></td><td><code>DELIVERED</code></td><td>Barang telah tiba di tujuan dan dikonfirmasi penerima di lapangan via QR scan.</td></tr>
                            <tr><td><span class="status status-green">Diterima</span></td><td><code>RECEIVED</code></td><td>Status penerimaan resmi tercatat di sistem.</td></tr>
                            <tr><td><span class="status status-red">Dibatalkan</span></td><td><code>CANCELLED</code></td><td>Surat jalan dibatalkan petugas. Kuantitas dikembalikan ke sisa kuota SPPB.</td></tr>
                        </tbody>
                    </table>
                </div>

                <h3>Status Pengiriman per Item Barang</h3>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Status</th><th>Kode</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr><td><span class="status status-gray">Menunggu Pengiriman</span></td><td><code>PENDING</code></td><td>Belum ada rilis sama sekali untuk item ini.</td></tr>
                            <tr><td><span class="status status-yellow">Sebagian Dikirim</span></td><td><code>PARTIALLY_DELIVERED</code></td><td>Sebagian kuantitas sudah dirilis dalam Surat Jalan, sisanya belum.</td></tr>
                            <tr><td><span class="status status-green">Terkirim Penuh</span></td><td><code>DELIVERED</code></td><td>Kuantitas barang sudah dirilis 100%.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- ATURAN BISNIS -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="aturan-sppb">
                <h2>Aturan Bisnis SPPB</h2>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>#</th><th>Aturan</th><th>Detail</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td><strong>Satu Workflow Aktif</strong></td><td>Satu SPPB hanya boleh memiliki satu <code>WorkflowInstance</code> aktif pada satu waktu.</td></tr>
                            <tr><td>2</td><td><strong>Tidak Ada Revisi Paksa</strong></td><td>Approver hanya dapat <strong>Setujui</strong> atau <strong>Tolak</strong>. Tidak ada status "Perlu Revisi".</td></tr>
                            <tr><td>3</td><td><strong>Penolakan Bisa Diperbaiki</strong></td><td>Dokumen <span class="status status-red">Ditolak</span> memberikan 2 opsi Pemohon: Edit & Re-Submit, atau Batalkan Permanen.</td></tr>
                            <tr><td>4</td><td><strong>Pembatalan Permanen</strong></td><td>Dokumen <span class="status status-darkgray">Dibatalkan</span> adalah status terminal — tidak dapat dibuka kembali.</td></tr>
                            <tr><td>5</td><td><strong>Penomoran Otomatis</strong></td><td>Format: <code>SPPB/{PLANT}/{DEPT}/{TAHUN}/{BULAN}/{URUTAN}</code></td></tr>
                            <tr><td>6</td><td><strong>Penutupan Parsial</strong></td><td>Sisa kuota yang tidak dikirim dapat ditutup resmi via tombol "Selesaikan SPPB", menghasilkan log <code>FORCE_COMPLETED</code>.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-section" id="aturan-surat-jalan">
                <h2>Aturan Bisnis Surat Jalan</h2>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>#</th><th>Aturan</th><th>Detail</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td><strong>Tidak Boleh Melebihi Kuota</strong></td><td>Kuantitas rilis tidak boleh melebihi sisa kuantitas yang belum dirilis pada SPPB terkait.</td></tr>
                            <tr><td>2</td><td><strong>Verifikasi Keaslian SHA-256</strong></td><td>Setiap Surat Jalan memiliki hash SHA-256 unik yang dapat diverifikasi publik di <code>/verify/document/{hash}</code> tanpa login.</td></tr>
                            <tr><td>3</td><td><strong>Pembatalan Surat Jalan</strong></td><td>Pembatalan mengembalikan kuantitas terkait ke sisa kuota SPPB dan memperbarui status SPPB otomatis.</td></tr>
                            <tr><td>4</td><td><strong>Update Status Otomatis</strong></td><td>Status SPPB diperbarui otomatis saat Surat Jalan diterbitkan atau dibatalkan.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-section" id="aturan-workflow">
                <h2>Aturan Alur Persetujuan</h2>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>#</th><th>Aturan</th><th>Detail</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td><strong>Template Wajib Ada</strong></td><td>Setiap kombinasi Plant-Departemen wajib memiliki Template Workflow aktif sebelum SPPB dapat diajukan.</td></tr>
                            <tr><td>2</td><td><strong>Delegasi Wewenang</strong></td><td>Approver yang tidak hadir dapat mendelegasikan wewenangnya ke pengguna lain untuk periode tertentu.</td></tr>
                            <tr><td>3</td><td><strong>Resolusi Delegasi Rekursif</strong></td><td>Sistem menelusuri rantai delegasi secara otomatis (maks. 10 tingkat) untuk menemukan delegator yang valid.</td></tr>
                            <tr><td>4</td><td><strong>Eskalasi ke Manager</strong></td><td>Approver dapat mengaktifkan eskalasi ke Manager Plant saat menyetujui dokumen.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- NOTIFIKASI -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="notifikasi">
                <h2>Sistem Notifikasi</h2>
                <p>Notifikasi dikirim melalui 3 saluran secara bersamaan berdasarkan konfigurasi aktif.</p>

                <div class="role-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#fef3c7;">🔔</div>
                            <div class="role-name">Lonceng Web</div>
                        </div>
                        <ul class="role-list">
                            <li>Ikon lonceng pojok kanan atas</li>
                            <li>Polling otomatis tiap 30 detik</li>
                            <li>Klik → langsung ke dokumen</li>
                            <li>Tandai telah dibaca otomatis</li>
                        </ul>
                    </div>
                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#dcfce7;">💬</div>
                            <div class="role-name">WhatsApp</div>
                        </div>
                        <ul class="role-list">
                            <li>Meta Cloud API (Official)</li>
                            <li>Custom REST Gateway (wwebjs)</li>
                            <li>Pilih penyedia di Pengaturan</li>
                        </ul>
                    </div>
                    <div class="role-card">
                        <div class="role-card-header">
                            <div class="role-icon" style="background:#dbeafe;">📧</div>
                            <div class="role-name">Email</div>
                        </div>
                        <ul class="role-list">
                            <li>SMTP Server</li>
                            <li>Resend API</li>
                            <li>Konfigurasi di Pengaturan</li>
                        </ul>
                    </div>
                </div>

                <h3>Pemicu Notifikasi</h3>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Kejadian</th><th>Penerima</th></tr></thead>
                        <tbody>
                            <tr><td>SPPB baru diajukan</td><td>Approver tahap pertama</td></tr>
                            <tr><td>Approver Step 1 menyetujui</td><td>Approver Step 2 / Manager</td></tr>
                            <tr><td>SPPB disetujui penuh</td><td>Pemohon</td></tr>
                            <tr><td>SPPB ditolak</td><td>Pemohon (beserta alasan)</td></tr>
                            <tr><td>SPPB dibatalkan</td><td>Pemohon</td></tr>
                            <tr><td>Transaksi diselesaikan (Force Complete)</td><td>Pemohon</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- VERIFIKASI DOKUMEN -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="verifikasi-dokumen">
                <h2>Verifikasi Keaslian Dokumen</h2>
                <p>Setiap Surat Jalan yang diterbitkan dilengkapi <strong>QR Code Verifikasi</strong> yang dapat dipindai oleh siapa saja untuk membuktikan keaslian dokumen tanpa perlu login.</p>

                <div class="callout info">
                    <div class="callout-icon">🔒</div>
                    <div class="callout-body">
                        <div class="callout-title">URL Verifikasi Publik</div>
                        <p class="callout-text">Akses halaman verifikasi di: <code>{{ url('/verify/document/{hash-sha256}') }}</code><br>Hash unik SHA-256 tercantum di bagian bawah setiap dokumen Surat Jalan yang dicetak.</p>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- DELEGASI -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="delegasi">
                <h2>Delegasi Wewenang</h2>
                <p>Saat Approver atau Manager tidak dapat bertugas (cuti, dinas luar), wewenang persetujuan dapat didelegasikan ke pengguna lain untuk periode tertentu.</p>

                <h3>Cara Membuat Delegasi (Super Admin)</h3>
                <div class="steps">
                    <div class="step"><div class="step-num">1</div><div class="step-body"><div class="step-title">Buka Delegasi Wewenang</div><p class="step-desc">Navigasi ke <strong>Organisasi → Delegasi Wewenang</strong> dan klik <strong>"Buat Delegasi"</strong>.</p></div></div>
                    <div class="step"><div class="step-num">2</div><div class="step-body"><div class="step-title">Pilih Delegator & Delegate</div><p class="step-desc"><strong>Delegator</strong> = pengguna yang mendelegasikan wewenang. <strong>Delegate</strong> = pengguna yang menerima wewenang.</p></div></div>
                    <div class="step"><div class="step-num">3</div><div class="step-body"><div class="step-title">Tentukan Periode</div><p class="step-desc">Isi <strong>Tanggal Mulai</strong> dan <strong>Tanggal Akhir</strong> delegasi. Sistem akan otomatis menggunakan delegate dalam periode ini.</p></div></div>
                </div>

                <div class="callout info">
                    <div class="callout-icon">ℹ️</div>
                    <div class="callout-body">
                        <div class="callout-title">Resolusi Rantai Delegasi</div>
                        <p class="callout-text">Sistem secara otomatis menelusuri rantai delegasi hingga 10 tingkat kedalaman untuk menemukan penerima wewenang yang valid saat ini.</p>
                    </div>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- LAPORAN -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="laporan">
                <h2>Laporan & Ekspor</h2>
                <p>Menu <strong>Laporan → Laporan Transaksi</strong> tersedia untuk Manager dan Super Admin.</p>

                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Fitur</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Filter Periode</strong></td><td>Filter laporan berdasarkan rentang tanggal pengajuan atau penyelesaian.</td></tr>
                            <tr><td><strong>Filter Status</strong></td><td>Tampilkan hanya SPPB dengan status tertentu (misal: hanya yang Selesai).</td></tr>
                            <tr><td><strong>Filter Plant/Dept.</strong></td><td>Saring berdasarkan Plant dan Departemen tertentu.</td></tr>
                            <tr><td><strong>Ekspor Excel</strong></td><td>Unduh laporan dalam format <code>.xlsx</code> untuk diolah lebih lanjut.</td></tr>
                            <tr><td><strong>Ekspor PDF</strong></td><td>Unduh laporan dalam format <code>.pdf</code> siap cetak dengan kop perusahaan.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PENGATURAN APLIKASI -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="pengaturan-aplikasi">
                <h2>Pengaturan Aplikasi</h2>
                <p>Menu: <strong>Pengaturan → Pengaturan Aplikasi</strong> (hanya Super Admin)</p>

                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Tab</th><th>Konfigurasi yang Tersedia</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Identitas Perusahaan</strong></td><td>Nama resmi, NPWP, alamat kantor, nomor telepon, email perusahaan.</td></tr>
                            <tr><td><strong>Visual & Branding</strong></td><td>Nama aplikasi kustom, warna tema, logo dashboard (terang/gelap), logo PDF, favicon browser.</td></tr>
                            <tr><td><strong>Pengaturan Regional</strong></td><td>Zona waktu (WIB/WITA/WIT), format tanggal, simbol & kode mata uang, pemisah ribuan/desimal.</td></tr>
                            <tr><td><strong>Keamanan & Sesi</strong></td><td>Durasi sesi aktif (menit), batas percobaan login gagal, wajibkan password kuat.</td></tr>
                            <tr><td><strong>Kendali Operasional</strong></td><td>Mode Pemeliharaan (nonaktifkan akses non-admin), pesan pemeliharaan, bypass approval darurat.</td></tr>
                            <tr><td><strong>Label & Istilah</strong></td><td>Kustomisasi label Plant, Departemen, Pemohon, Approver, Barang, Satuan, dan status dokumen.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- PENGATURAN NOTIFIKASI -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="pengaturan-notifikasi">
                <h2>Pengaturan Notifikasi</h2>
                <p>Menu: <strong>Pengaturan → Pengaturan Notifikasi</strong> (hanya Super Admin)</p>

                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Tab</th><th>Konfigurasi yang Tersedia</th></tr></thead>
                        <tbody>
                            <tr><td><strong>WhatsApp</strong></td><td>Pilih penyedia (Meta Cloud API / Custom Gateway), masukkan API Key, nomor pengirim, kirim pesan uji coba.</td></tr>
                            <tr><td><strong>Email</strong></td><td>Pilih metode (SMTP / Resend API), konfigurasi host/port/user/pass atau API key, kirim email uji coba.</td></tr>
                            <tr><td><strong>Notifikasi In-App</strong></td><td>Aktifkan/nonaktifkan notifikasi lonceng, atur interval polling (detik).</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- TEMPLATE WORKFLOW -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="template-workflow">
                <h2>Template Workflow</h2>
                <p>Menu: <strong>Organisasi → Template Workflow</strong> (hanya Super Admin)</p>
                <p>Template Workflow mendefinisikan urutan tahapan persetujuan yang harus dilalui setiap SPPB dari kombinasi Plant-Departemen tertentu.</p>

                <div class="callout warning">
                    <div class="callout-icon">⚠️</div>
                    <div class="callout-body">
                        <div class="callout-title">Wajib Ada Sebelum SPPB Bisa Diajukan</div>
                        <p class="callout-text">Jika belum ada template aktif untuk kombinasi Plant-Departemen, Pemohon tidak dapat mengajukan SPPB dari departemen tersebut.</p>
                    </div>
                </div>

                <h3>Struktur Template</h3>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Komponen</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Plant</strong></td><td>Plant yang menggunakan template ini.</td></tr>
                            <tr><td><strong>Departemen</strong></td><td>Departemen spesifik (atau "Semua Departemen" untuk berlaku global).</td></tr>
                            <tr><td><strong>Tahapan (Steps)</strong></td><td>Urutan langkah persetujuan. Setiap step menentukan jabatan Approver yang berwenang.</td></tr>
                            <tr><td><strong>Status Aktif</strong></td><td>Template dapat diaktifkan atau dinonaktifkan tanpa dihapus.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-divider"></div>

            <!-- ────────────────────────────── -->
            <!-- HAK AKSES -->
            <!-- ────────────────────────────── -->
            <div class="doc-section" id="hak-akses">
                <h2>Hak Akses Dokumen</h2>
                <p>Menu: <strong>Organisasi → Hak Akses Dokumen</strong> (hanya Super Admin)</p>
                <p>Matriks Hak Akses Dokumen mengatur izin tambahan per pengguna, per modul, per Plant/Departemen — berlapis di atas peran Spatie.</p>

                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Kolom Izin</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr><td><code>can_view</code></td><td>Pengguna dapat melihat daftar dan detail dokumen modul tersebut.</td></tr>
                            <tr><td><code>can_create</code></td><td>Pengguna dapat membuat dokumen baru di modul tersebut.</td></tr>
                            <tr><td><code>can_edit</code></td><td>Pengguna dapat mengedit dokumen yang ada.</td></tr>
                            <tr><td><code>can_delete</code></td><td>Pengguna dapat menghapus dokumen.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="callout info">
                    <div class="callout-icon">ℹ️</div>
                    <div class="callout-body">
                        <div class="callout-title">Otorisasi Berlapis</div>
                        <p class="callout-text">Izin di Hak Akses Dokumen bekerja <strong>bersamaan</strong> dengan peran Spatie. Keduanya harus mengizinkan akses agar operasi dapat dilakukan.</p>
                    </div>
                </div>

                <div style="margin-top: 3rem; padding: 1.5rem; background: var(--border-light); border-radius: 8px; text-align: center;">
                    <p style="margin-bottom: 0.5rem; font-size: 0.875rem;">Butuh bantuan lebih lanjut?</p>
                    <p style="margin: 0; font-size: 0.8125rem; color: var(--text-light);">Hubungi Super Admin sistem di Plant Anda atau tim IT perusahaan.</p>
                    <div style="margin-top: 1rem; display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                        <a href="/admin" class="btn-header primary">Masuk ke Aplikasi</a>
                        <a href="{{ url('/verify/document') }}" class="btn-header">Verifikasi Dokumen</a>
                    </div>
                </div>
            </div>

        </div><!-- /content -->
    </main>

</div><!-- /layout -->
</div><!-- /#pane-manual -->

<!-- PANE 2: OPENAPI REFERENCE (Stoplight Elements) -->
<div id="pane-api" class="doc-pane">
    <div style="flex: 1; height: 100%; position: relative;">
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

<!-- PANE 3: PANDUAN MOBILE & INTEGRASI -->
<div id="pane-mobile" class="doc-pane">
    <div style="display: flex; max-width: 1280px; margin: 0 auto; width: 100%;">

        <!-- Sidebar Mobile Guide -->
        <nav class="sidebar" style="position: sticky; top: var(--header-height); height: calc(100vh - var(--header-height)); overflow-y: auto;">
            <div class="sidebar-section">
                <div class="sidebar-section-label">Pengantar Mobile</div>
                <a href="#mob-overview" class="sidebar-link active"><span class="dot"></span>Gambaran Umum</a>
                <a href="#mob-authentication" class="sidebar-link"><span class="dot"></span>Autentikasi</a>
                <a href="#mob-branding" class="sidebar-link"><span class="dot"></span>Logo & Branding API</a>
                <a href="#mob-errors" class="sidebar-link"><span class="dot"></span>Format Error</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Penerimaan Lapangan</div>
                <a href="#mob-receive-confirm" class="sidebar-link"><span class="dot"></span>Konfirmasi Terima</a>
                <a href="#mob-receive-show" class="sidebar-link"><span class="dot"></span>Detail Surat Jalan</a>
                <a href="#mob-flow" class="sidebar-link"><span class="dot"></span>Alur Scan QR</a>
                <a href="#mob-signature" class="sidebar-link"><span class="dot"></span>Canvas Tanda Tangan</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">State Machine</div>
                <a href="#mob-lifecycle" class="sidebar-link"><span class="dot"></span>Lifecycle Dokumen</a>
            </div>
        </nav>

        <!-- Content Mobile Guide -->
        <div class="content" style="max-width: 880px; padding: 2.5rem 2rem 6rem; flex: 1;">

            <!-- OVERVIEW -->
            <div class="doc-section" id="mob-overview">
                <h1>Panduan Mobile & Integrasi API</h1>
                <p>Panduan teknis bagi tim pengembang mobile app (Flutter / React Native / Kotlin / Swift / PWA) yang terhubung dengan backend E-SPPB Enterprise, difokuskan pada alur <strong>konfirmasi penerimaan barang di lapangan via QR Code</strong> dan <strong>sinkronisasi branding</strong>.</p>

                <div class="hero">
                    <h3 style="margin-top:0;">Base API URL</h3>
                    <div style="background: var(--code-bg); padding: 0.75rem 1rem; border-radius: 8px; font-family: 'JetBrains Mono', monospace; color: #a5d6ff; font-size: 0.9rem; word-break: break-all;">
                        {{ url('/api/v1') }}
                    </div>
                    <div class="hero-badges">
                        <span class="badge blue">Sanctum Token Auth</span>
                        <span class="badge green">Public QR Verification</span>
                        <span class="badge purple">Idempotent Confirmation</span>
                        <span class="badge orange">Visual Branding API</span>
                    </div>
                </div>

                <h3>Format Respons Standar</h3>
                <div style="background: var(--code-bg); border-radius: 8px; padding: 1.25rem; overflow-x: auto; margin: 1rem 0;">
                    <pre style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; color: var(--code-text);">{
  <span style="color:#79c0ff;">"success"</span>: <span style="color:#ff7b72;">true</span>,
  <span style="color:#79c0ff;">"message"</span>: <span style="color:#a5d6ff;">"Operasi berhasil dijalankan."</span>,
  <span style="color:#79c0ff;">"data"</span>: { ... },
  <span style="color:#79c0ff;">"already_confirmed"</span>: <span style="color:#ff7b72;">false</span>, <span style="color:#8b949e;">// Khusus endpoint penerimaan</span>
  <span style="color:#79c0ff;">"timestamp"</span>: <span style="color:#a5d6ff;">"2026-08-20T13:00:00+07:00"</span>
}</pre>
                </div>
            </div>

            <!-- AUTH -->
            <div class="doc-section" id="mob-authentication">
                <h2>Autentikasi Mobile</h2>
                <p>Aplikasi mobile menggunakan <strong>Laravel Sanctum Bearer Token</strong> untuk operasi terlindungi. Kirimkan token di header setiap request:</p>
                <div style="background: var(--code-bg); border-radius: 8px; padding: 1rem; font-family: 'JetBrains Mono', monospace; color: #a5d6ff; font-size: 0.85rem; margin-bottom: 1rem;">
                    Authorization: Bearer {token}
                </div>

                <div class="callout info">
                    <div class="callout-icon">ℹ️</div>
                    <div class="callout-body">
                        <div class="callout-title">Konfirmasi QR Publik vs Terautentikasi</div>
                        <p class="callout-text">Endpoint <strong>konfirmasi terima surat jalan</strong> dapat dipanggil <strong>tanpa token</strong> (oleh penerima umum via QR Code) maupun <strong>dengan token</strong> (jika user driver/staf gudang login, sistem otomatis mencatat <code>received_by_id</code>).</p>
                    </div>
                </div>
            </div>

            <!-- BRANDING API -->
            <div class="doc-section" id="mob-branding">
                <h2>Logo & Visual Branding API</h2>
                <p>Mobile App dapat menyesuaikan logo, favicon, warna tema utama, dan tinggi logo sesuai preferensi perusahaan secara dinamis melalui API Branding publik:</p>

                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Method</th><th>Endpoint</th><th>Akses</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge blue">GET</span></td><td><code>/api/v1/branding</code></td><td>Publik</td><td>Ambil konfigurasi nama aplikasi, warna tema, logo light/dark/login/pdf, dan favicon.</td></tr>
                            <tr><td><span class="badge purple">POST</span></td><td><code>/api/v1/settings/branding</code></td><td>Admin</td><td>Perbarui teks branding atau unggah berkas logo/favicon baru (multipart).</td></tr>
                            <tr><td><span class="badge red" style="background:#fee2e2;color:#991b1b;">DELETE</span></td><td><code>/api/v1/settings/branding/logos/{type}</code></td><td>Admin</td><td>Hapus logo spesifik (<code>light</code>, <code>dark</code>, <code>favicon</code>, <code>login</code>, <code>pdf</code>).</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RECEIVE CONFIRM -->
            <div class="doc-section" id="mob-receive-confirm">
                <h2>Konfirmasi Penerimaan Barang di Lapangan</h2>
                <p>Endpoint utama saat penerima di plant tujuan memindai QR Code Surat Jalan:</p>

                <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; background: var(--content-bg); margin: 1rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <span class="badge green" style="font-size:0.8rem; font-weight:700;">POST</span>
                        <code style="font-size: 0.95rem;">/api/v1/goods-releases/{uuid}/receive</code>
                    </div>
                    <p style="font-size: 0.875rem;">Alias: <code>/api/v1/goods-releases/{uuid}/confirm-receipt</code></p>

                    <h4>Payload JSON:</h4>
                    <div style="background: var(--code-bg); border-radius: 8px; padding: 1rem; overflow-x: auto;">
                        <pre style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--code-text);">{
  <span style="color:#79c0ff;">"recipient_name"</span>: <span style="color:#a5d6ff;">"Budi Santoso"</span>,       <span style="color:#8b949e;">// Wajib (string, max:150)</span>
  <span style="color:#79c0ff;">"recipient_signature"</span>: <span style="color:#a5d6ff;">"data:image/png;base64,..."</span>, <span style="color:#8b949e;">// Opsional (base64 image, max 5MB)</span>
  <span style="color:#79c0ff;">"receiving_notes"</span>: <span style="color:#a5d6ff;">"Diterima lengkap dan segel utuh"</span> <span style="color:#8b949e;">// Opsional (string, max:500)</span>
}</pre>
                    </div>
                </div>
            </div>

            <!-- MOBILE FLOW -->
            <div class="doc-section" id="mob-flow">
                <h2>Alur Konfirmasi via QR Code (Diagram)</h2>
                <div style="background: var(--code-bg); border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--code-text); line-height: 2;">
1. Pengguna membuka kamera / Scanner di Aplikasi Mobile
   │
   ▼
2. Scan QR Code pada fisik Surat Jalan
   ➔ Mendapatkan URL: https://.../verify/document/{hash} atau JSON / UUID Surat Jalan
   │
   ▼
3. Mobile App memanggil: GET /api/v1/goods-releases/{uuid}
   ➔ Periksa status: jika status DELIVERED ➔ tampilkan info penerimaan sebelumnya
   ➔ Jika status RELEASED ➔ buka Form Konfirmasi Penerimaan
   │
   ▼
4. Pengguna mengisi:
   • Nama Penerima (Wajib)
   • Tanda Tangan di Layar Sentuh / Canvas (Opsional)
   • Catatan Penerimaan (Opsional)
   │
   ▼
5. Mobile App mengirim: POST /api/v1/goods-releases/{uuid}/receive
   │
   ▼
6. Backend memproses:
   • Mengubah status Surat Jalan menjadi DELIVERED
   • Menghitung kuota pelepasan barang SPPB terkait (jika lunas ➔ COMPLETED)
   • Mengirim notifikasi WhatsApp otomatis ke Pemohon & Gudang Pengirim
   │
   ▼
7. Respons sukses: "Penerimaan barang berhasil dikonfirmasi."
                </div>
            </div>

            <!-- SIGNATURE -->
            <div class="doc-section" id="mob-signature">
                <h2>Panduan Tanda Tangan Canvas</h2>
                <p>Tanda tangan dikirim sebagai <code>data:image/png;base64,...</code>.</p>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Platform</th><th>Library Rekomendasi</th><th>Metode Export</th></tr></thead>
                        <tbody>
                            <tr><td>Flutter</td><td><code>syncfusion_flutter_signaturepad</code></td><td><code>toImage()</code> ➔ <code>base64Encode()</code></td></tr>
                            <tr><td>React Native</td><td><code>react-native-signature-canvas</code></td><td><code>onOK = (sig) => sig</code> (Base64)</td></tr>
                            <tr><td>Web / PWA</td><td><code>signature_pad</code></td><td><code>pad.toDataURL('image/png')</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </div>
</div>

<!-- PANE 4: MARKDOWN AI STUDIO PROMPT -->
<div id="pane-ai" class="doc-pane" style="background: var(--bg); overflow-y: auto;">
    <div style="max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 6rem; width: 100%;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Markdown Prompt Context — AI Studio</h1>
                <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Spesifikasi lengkap RESTful API, DTO, Type Definitions & Business Rules. Siap untuk disalin dan ditempelkan ke <strong>Google AI Studio</strong>, Claude 3.7, ChatGPT o3, atau Cursor IDE.</p>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('docs.api.md') }}" target="_blank" class="btn-header" style="background: var(--content-bg);">
                    ⬇️ Raw .md File
                </a>
                <button onclick="copyAllMarkdown(this)" class="btn-header primary" style="padding: 0.5rem 1.125rem; font-weight: 600; cursor: pointer;">
                    📋 Salin Semua Context untuk AI Studio
                </button>
            </div>
        </div>

        <div style="background: var(--code-bg); border: 1px solid var(--border); border-radius: 12px; margin: 0; overflow: hidden;">
            <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #8b949e; font-size: 0.75rem; font-weight: 700;">MARKDOWN PROMPT / SYSTEM CONTEXT</span>
                <span style="font-size: 0.75rem; color: #79c0ff; font-family: 'JetBrains Mono', monospace;">{{ route('docs.api.md') }}</span>
            </div>
            <div style="padding: 1.5rem 1.25rem; overflow-x: auto;">
                <pre id="raw-markdown-content" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; line-height: 1.8; color: var(--code-text); white-space: pre-wrap; word-break: break-word;">@include('docs-api-md')</pre>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        if (sidebar) sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('open');
    }

    // Tab switcher between User Manual, OpenAPI, Mobile Guide, and AI Prompt
    function switchDocTab(tabName, btn) {
        document.querySelectorAll('.doc-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.header-tab').forEach(t => t.classList.remove('active'));

        const targetPane = document.getElementById('pane-' + tabName);
        if (targetPane) {
            targetPane.classList.add('active');
        }

        if (btn) {
            btn.classList.add('active');
        } else {
            const matchingBtn = document.getElementById('tab-btn-' + tabName);
            if (matchingBtn) matchingBtn.classList.add('active');
        }

        // Sync URL query without full page reload
        const url = new URL(window.location);
        if (tabName === 'manual') {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', tabName);
        }
        window.history.replaceState({}, '', url);

        window.scrollTo({ top: 0, behavior: 'instant' });
    }

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

    // Active link tracking on scroll for manual
    const sections = document.querySelectorAll('#pane-manual .doc-section');
    const links = document.querySelectorAll('#pane-manual .sidebar-link');

    function updateActiveLink() {
        if (!document.getElementById('pane-manual').classList.contains('active')) return;
        let currentId = '';
        const scrollY = window.scrollY + 80;

        sections.forEach(section => {
            if (section.offsetTop <= scrollY) {
                currentId = section.id;
            }
        });

        links.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentId) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', updateActiveLink, { passive: true });

    links.forEach(link => {
        link.addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('open');
        });
    });

    // Handle initial tab on load from URL parameter or hash
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        const hash = window.location.hash.toLowerCase();

        if (tabParam === 'api' || hash === '#api' || hash.startsWith('#api-')) {
            switchDocTab('api');
        } else if (tabParam === 'mobile' || hash === '#mobile' || hash.startsWith('#mob-')) {
            switchDocTab('mobile');
        } else if (tabParam === 'ai' || tabParam === 'ai-prompt' || hash === '#ai' || hash === '#ai-prompt') {
            switchDocTab('ai');
        } else {
            switchDocTab('manual');
        }
        updateActiveLink();
    });

    // Intercept Stoplight fetch for CSRF
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
