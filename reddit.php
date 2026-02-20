<?php
if (isset($_GET['fetch'])) {
    $sub = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['sub'] ?? 'popular');
    $sort = in_array($_GET['sort'] ?? 'hot', ['hot','new','top','rising']) ? $_GET['sort'] : 'hot';
    $url = $sub === 'popular'
        ? "https://www.reddit.com/r/popular/{$sort}.json?limit=25"
        : "https://www.reddit.com/r/{$sub}/{$sort}.json?limit=25";
    $ctx = stream_context_create(['http'=>['method'=>'GET','header'=>"User-Agent: Mozilla/5.0\r\n",'timeout'=>10]]);
    $data = @file_get_contents($url, false, $ctx);
    header('Content-Type: application/json');
    echo $data ?: '{"error":"no_data"}';
    exit;
}
if (isset($_GET['about'])) {
    $sub = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['sub'] ?? 'popular');
    $ctx = stream_context_create(['http'=>['method'=>'GET','header'=>"User-Agent: Mozilla/5.0\r\n",'timeout'=>8]]);
    $data = @file_get_contents("https://www.reddit.com/r/{$sub}/about.json", false, $ctx);
    header('Content-Type: application/json');
    echo $data ?: '{"error":"no_data"}';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reddit</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #dae0e6;
            --white: #ffffff;
            --surface: #f6f7f8;
            --text: #0f1a1c;
            --muted: #576f76;
            --border: #edeff1;
            --red: #ff4500;
            --orange: #ff6534;
            --blue: #0079d3;
            --green: #46d160;
            --header-h: 48px;
        }
        html { font-size: 14px; }
        body { font-family: 'IBM Plex Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── HEADER ── */
        header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 600;
            height: var(--header-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            padding: 0 20px; gap: 16px;
        }
        .reddit-logo {
            display: flex; align-items: center; gap: 8px;
            text-decoration: none; flex-shrink: 0;
        }
        .reddit-logo svg { width: 32px; height: 32px; }
        .reddit-wordmark {
            font-size: 18px; font-weight: 700; color: var(--red);
            font-style: italic; letter-spacing: -0.5px;
        }

        .search-container {
            flex: 1; max-width: 690px; margin: 0 auto;
            display: flex; align-items: center;
            border: 1px solid var(--border); border-radius: 20px;
            background: var(--surface); overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .search-container:focus-within {
            border-color: var(--blue);
            box-shadow: 0 0 0 1px var(--blue);
            background: var(--white);
        }
        .search-icon-wrap {
            display: flex; align-items: center; justify-content: center;
            width: 40px; height: 36px; flex-shrink: 0;
        }
        .search-icon-wrap svg { color: var(--muted); }
        #q {
            flex: 1; border: none; outline: none; background: none;
            font-family: 'IBM Plex Sans', sans-serif; font-size: 14px;
            color: var(--text); padding: 0 12px 0 0;
        }
        #q::placeholder { color: var(--muted); }
        .ask-btn {
            display: flex; align-items: center; gap: 6px;
            border: none; border-left: 1px solid var(--border);
            background: none; padding: 0 16px; height: 36px;
            font-family: 'IBM Plex Sans', sans-serif; font-size: 14px;
            color: var(--muted); cursor: pointer; white-space: nowrap;
            transition: background 0.15s;
        }
        .ask-btn:hover { background: var(--border); }

        .hd-right { margin-left: auto; display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .hd-icon-btn {
            width: 36px; height: 36px; border-radius: 50%; border: none;
            background: none; cursor: pointer; color: var(--muted);
            display: flex; align-items: center; justify-content: center;
            transition: background 0.15s;
        }
        .hd-icon-btn:hover { background: var(--border); }
        .app-btn {
            display: flex; align-items: center; gap: 6px;
            border: 1px solid var(--border); border-radius: 20px;
            background: none; padding: 6px 14px;
            font-family: 'IBM Plex Sans', sans-serif; font-size: 14px; font-weight: 500;
            color: var(--text); cursor: pointer; transition: background 0.15s;
            white-space: nowrap;
        }
        .app-btn:hover { background: var(--border); }
        .login-btn {
            background: var(--red); color: #fff; border: none;
            border-radius: 20px; padding: 7px 18px;
            font-family: 'IBM Plex Sans', sans-serif; font-size: 14px; font-weight: 700;
            cursor: pointer; white-space: nowrap; transition: opacity 0.2s;
        }
        .login-btn:hover { opacity: 0.88; }

        /* ── LAYOUT ── */
        .layout {
            display: flex;
            margin-top: var(--header-h);
            max-width: 1200px;
            margin-left: auto; margin-right: auto;
            padding: 20px 20px 40px;
            gap: 24px;
        }

        /* ── LEFT NAV ── */
        .left-nav {
            width: 240px; min-width: 240px;
            position: sticky; top: calc(var(--header-h) + 20px);
            max-height: calc(100vh - var(--header-h) - 40px);
            overflow-y: auto; scrollbar-width: none;
        }
        .left-nav::-webkit-scrollbar { display: none; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 2px;
            cursor: pointer; font-size: 14px; font-weight: 500;
            color: var(--text); transition: background 0.1s;
            text-decoration: none;
        }
        .nav-item:hover { background: var(--border); }
        .nav-item.active { background: var(--border); font-weight: 600; }
        .nav-item svg { color: var(--muted); flex-shrink: 0; }
        .nav-section {
            font-size: 10px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: 1px;
            padding: 12px 12px 4px;
        }
        .nav-divider { height: 1px; background: var(--border); margin: 8px 0; }

        /* ── MAIN ── */
        .main { flex: 1; min-width: 0; max-width: 740px; }

        /* TRENDING CAROUSEL */
        .trending-section {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .trending-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .trending-card {
            border-radius: 8px; overflow: hidden; position: relative;
            aspect-ratio: 16/10; background: #ccc; cursor: pointer;
        }
        .trending-card img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.3s;
        }
        .trending-card:hover img { transform: scale(1.04); }
        .trending-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.1) 60%);
            padding: 10px;
            display: flex; flex-direction: column; justify-content: flex-end;
        }
        .trending-title { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; margin-bottom: 4px; }
        .trending-sub { font-size: 11px; color: rgba(255,255,255,0.75); display: flex; align-items: center; gap: 4px; }

        /* SORT BAR */
        .sort-bar {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 10px 12px;
            display: flex; align-items: center; gap: 4px;
            margin-bottom: 16px;
        }
        .sort-item {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 12px; border-radius: 2px; border: none;
            background: none; font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px; font-weight: 700; color: var(--muted);
            cursor: pointer; transition: all 0.15s;
        }
        .sort-item:hover { background: var(--border); color: var(--blue); }
        .sort-item.active { color: var(--blue); background: #e8f0fe; }
        .sort-sep { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }
        .layout-btn {
            margin-left: auto; display: flex; align-items: center; gap: 4px;
        }
        .layout-btn button {
            width: 32px; height: 32px; border: 1px solid var(--border);
            border-radius: 2px; background: none; cursor: pointer; color: var(--muted);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .layout-btn button:hover, .layout-btn button.active { background: var(--border); color: var(--text); }

        /* FILTER CHIPS */
        .filter-bar {
            display: flex; gap: 8px; overflow-x: auto;
            padding-bottom: 4px; margin-bottom: 12px;
            scrollbar-width: none;
        }
        .filter-bar::-webkit-scrollbar { display: none; }
        .fchip {
            display: flex; align-items: center; gap: 6px;
            background: var(--white); border: 1px solid var(--border);
            border-radius: 20px; padding: 6px 14px;
            font-size: 13px; font-weight: 600; color: var(--text);
            cursor: pointer; white-space: nowrap; transition: all 0.15s;
            flex-shrink: 0;
        }
        .fchip:hover { border-color: var(--blue); color: var(--blue); }
        .fchip.active { background: var(--blue); border-color: var(--blue); color: #fff; }

        /* FEED */
        .feed { display: flex; flex-direction: column; gap: 10px; }

        /* POST CARD */
        .post {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            transition: border-color 0.15s;
            cursor: pointer;
            animation: fadeIn 0.3s ease both;
        }
        .post:hover { border-color: #818384; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

        .post-inner { display: flex; }

        /* VOTE */
        .vote-col {
            width: 40px; min-width: 40px;
            background: #f8f9fa;
            border-radius: 4px 0 0 4px;
            display: flex; flex-direction: column; align-items: center;
            padding: 8px 4px; gap: 2px;
        }
        .vote-up, .vote-down {
            background: none; border: none; cursor: pointer; padding: 4px;
            border-radius: 2px; color: var(--muted); font-size: 16px;
            transition: all 0.1s; line-height: 1;
        }
        .vote-up:hover { color: var(--red); background: #ffe8e0; }
        .vote-down:hover { color: var(--blue); background: #e8f0fe; }
        .vote-num {
            font-size: 12px; font-weight: 700; color: var(--text);
            line-height: 1;
        }

        /* CONTENT */
        .post-content { flex: 1; padding: 8px 8px 0; min-width: 0; }
        .post-header {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: var(--muted); margin-bottom: 6px;
            flex-wrap: wrap;
        }
        .post-sub {
            font-weight: 700; color: var(--text); font-size: 12px;
            display: flex; align-items: center; gap: 4px;
        }
        .post-sub:hover { text-decoration: underline; }
        .sub-icon {
            width: 18px; height: 18px; border-radius: 50%;
            background: var(--red); display: flex; align-items: center;
            justify-content: center; font-size: 10px; color: #fff; font-weight: 700;
            flex-shrink: 0;
        }
        .join-btn {
            background: var(--blue); color: #fff; border: none;
            border-radius: 20px; padding: 3px 12px;
            font-family: 'IBM Plex Sans', sans-serif; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: opacity 0.15s;
        }
        .join-btn:hover { opacity: 0.85; }
        .post-flair {
            background: #e8f0fe; color: var(--blue);
            border-radius: 2px; font-size: 12px; font-weight: 500;
            padding: 1px 6px;
        }
        .post-title {
            font-size: 18px; font-weight: 500; line-height: 1.3;
            color: var(--text); margin-bottom: 8px;
        }
        .post-img {
            width: 100%; max-height: 512px; object-fit: cover;
            border-radius: 4px; margin-bottom: 8px; display: block;
        }
        .post-text-preview {
            font-size: 14px; color: var(--muted); line-height: 1.5;
            margin-bottom: 8px;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }

        /* POST ACTIONS */
        .post-actions {
            display: flex; align-items: center; gap: 2px;
            padding: 4px 0 4px;
        }
        .act-btn {
            display: flex; align-items: center; gap: 5px;
            background: none; border: none; cursor: pointer; padding: 6px 8px;
            border-radius: 2px; font-family: 'IBM Plex Sans', sans-serif;
            font-size: 12px; font-weight: 700; color: var(--muted);
            transition: all 0.1s;
        }
        .act-btn:hover { background: var(--border); color: var(--text); }
        .awards { display: flex; align-items: center; gap: 3px; font-size: 12px; color: var(--muted); padding: 0 8px; margin-left: auto; }

        /* THUMB */
        .post-thumb {
            width: 96px; min-width: 96px; height: 72px;
            object-fit: cover; border-radius: 2px;
            margin: 8px 8px 8px 0; align-self: flex-start;
            border: 1px solid var(--border);
        }

        /* ── RIGHT SIDEBAR ── */
        .right-sidebar {
            width: 312px; min-width: 312px;
            position: sticky; top: calc(var(--header-h) + 20px);
            max-height: calc(100vh - var(--header-h) - 40px);
            overflow-y: auto; scrollbar-width: none;
            display: flex; flex-direction: column; gap: 16px;
        }
        .right-sidebar::-webkit-scrollbar { display: none; }

        .widget {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 4px; overflow: hidden;
        }
        .widget-header {
            padding: 10px 12px;
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: var(--text);
            border-bottom: 1px solid var(--border);
        }
        .widget-body { padding: 12px; }

        /* COMMUNITY LIST */
        .comm-item {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 0; cursor: pointer; transition: all 0.1s;
        }
        .comm-item:hover .comm-name { color: var(--blue); }
        .comm-icon {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--red); display: flex; align-items: center;
            justify-content: center; font-size: 14px; font-weight: 700; color: #fff;
            flex-shrink: 0; overflow: hidden;
        }
        .comm-icon img { width: 100%; height: 100%; object-fit: cover; }
        .comm-info { flex: 1; min-width: 0; }
        .comm-name { font-size: 14px; font-weight: 500; }
        .comm-members { font-size: 12px; color: var(--muted); }
        .comm-join {
            background: none; border: 1px solid var(--blue);
            color: var(--blue); border-radius: 20px; padding: 3px 12px;
            font-family: 'IBM Plex Sans', sans-serif; font-size: 12px; font-weight: 700;
            cursor: pointer; flex-shrink: 0; transition: all 0.15s;
        }
        .comm-join:hover { background: var(--blue); color: #fff; }
        .see-more {
            font-size: 14px; font-weight: 700; color: var(--blue);
            cursor: pointer; padding: 8px 0 0; display: block;
        }
        .see-more:hover { text-decoration: underline; }

        /* FOOTER WIDGET */
        .footer-links {
            display: flex; flex-wrap: wrap; gap: 6px 12px; padding: 12px;
            font-size: 12px; color: var(--muted);
        }
        .footer-links a { color: var(--muted); text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }

        /* LOADER */
        .loader { display: flex; justify-content: center; padding: 3rem; }
        .spinner { width: 36px; height: 36px; border: 3px solid var(--border); border-top-color: var(--red); border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to{transform:rotate(360deg)} }

        .empty { text-align: center; padding: 3rem; color: var(--muted); background: var(--white); border-radius: 4px; border: 1px solid var(--border); }

        @media(max-width:1100px) { .left-nav { display: none; } }
        @media(max-width:860px) { .right-sidebar { display: none; } }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a class="reddit-logo" href="#">
        <svg viewBox="0 0 20 20" fill="none">
            <circle cx="10" cy="10" r="10" fill="#FF4500"/>
            <path d="M16.67 10a1.46 1.46 0 00-2.47-1 7.12 7.12 0 00-3.85-1.23l.65-3.07 2.13.45a1 1 0 101.07-1 1 1 0 00-.96.68l-2.38-.5a.27.27 0 00-.32.2l-.73 3.44a7.14 7.14 0 00-3.89 1.23 1.46 1.46 0 10-1.61 2.39 2.84 2.84 0 000 .44c0 2.24 2.61 4.06 5.83 4.06s5.83-1.82 5.83-4.06a2.84 2.84 0 000-.44 1.46 1.46 0 00.61-1.59zM7.27 11a1 1 0 111 1 1 1 0 01-1-1zm5.57 2.65a3.54 3.54 0 01-2.84.59 3.54 3.54 0 01-2.84-.59.18.18 0 01.25-.25 3.16 3.16 0 002.59.47 3.16 3.16 0 002.59-.47.18.18 0 01.25.25zm-.22-1.65a1 1 0 111-1 1 1 0 01-1 1z" fill="white"/>
        </svg>
        <span class="reddit-wordmark">reddit</span>
    </a>

    <div class="search-container">
        <div class="search-icon-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        </div>
        <input type="text" id="q" placeholder="Encuentra lo que quieras" onkeydown="if(event.key==='Enter')buscar()">
        <button class="ask-btn" onclick="buscar()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FF4500"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            Preguntar
        </button>
    </div>

    <div class="hd-right">
        <button class="hd-icon-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0014.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/></svg>
        </button>
        <button class="app-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12" y2="18" stroke="white" stroke-width="2"/></svg>
            Obtener app
        </button>
        <button class="login-btn">Iniciar sesión</button>
        <button class="hd-icon-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
        </button>
    </div>
</header>

<div class="layout">

    <!-- LEFT NAV -->
    <div class="left-nav">
        <a class="nav-item" href="#" onclick="loadSub('popular')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            Principal
        </a>
        <a class="nav-item active" href="#" onclick="loadSub('popular')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
            Popular
        </a>
        <a class="nav-item" href="#">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            Noticias
        </a>
        <a class="nav-item" href="#">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg>
            Explorar
        </a>
        <div class="nav-divider"></div>
        <div class="nav-section">Recursos</div>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>Acerca de Reddit</a>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"/></svg>Anunciarse</a>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>Reddit Pro <span style="color:#ff6534;font-size:10px;font-weight:700;margin-left:4px">BETA</span></a>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg>Ayuda</a>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>Blog</a>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.36C18 2.51 15.49 0 12.36 0c-1.71 0-3.24.79-4.27 2.03L12 6.4l3.91-3.91C16.55 2.18 17.23 2 18 2c1.1 0 2 .9 2 2s-.9 2-2 2h-2.18l1.18-1.18-1.41-1.41-3 3L15 9l1.41-1.41-.73-.73c1.77.14 3.32 1.38 3.32 3.14V12h2V9c0-1.09-.56-2.04-1.41-2.62L20 4h.01L20 6zm-9 0H4C2.9 6 2 6.9 2 8v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-3.18L11 8.18V6zm1 11.5L8 15l1.41-1.41L12 16.17l5.59-5.59L19 12l-7 7z"/></svg>Empleo</a>
        <div class="nav-divider"></div>
        <a class="nav-item" href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>Comunidades</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <!-- FILTER CHIPS -->
        <div class="filter-bar">
            <button class="fchip active" onclick="loadSubChip('popular',this)">🌎 Popular</button>
            <button class="fchip" onclick="loadSubChip('technology',this)">💻 Technology</button>
            <button class="fchip" onclick="loadSubChip('programming',this)">👨‍💻 Programming</button>
            <button class="fchip" onclick="loadSubChip('worldnews',this)">📰 World News</button>
            <button class="fchip" onclick="loadSubChip('science',this)">🔬 Science</button>
            <button class="fchip" onclick="loadSubChip('gaming',this)">🎮 Gaming</button>
            <button class="fchip" onclick="loadSubChip('movies',this)">🎬 Movies</button>
            <button class="fchip" onclick="loadSubChip('music',this)">🎵 Music</button>
            <button class="fchip" onclick="loadSubChip('askreddit',this)">❓ AskReddit</button>
        </div>

        <!-- SORT BAR -->
        <div class="sort-bar">
            <button class="sort-item active" id="sort-hot" onclick="setSort('hot',this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67z"/></svg>
                Mejores
            </button>
            <button class="sort-item" id="sort-new" onclick="setSort('new',this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                Nuevos
            </button>
            <button class="sort-item" id="sort-top" onclick="setSort('top',this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>
                Populares
            </button>
            <div class="sort-sep"></div>
            <button class="sort-item" onclick="">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 18h6v-2H3v2zM3 6v2h18V6H3zm0 7h12v-2H3v2z"/></svg>
                En todo el mundo
            </button>
            <div class="layout-btn">
                <button class="active">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5v14h18V5H3zm4 2h10v10H7V7z"/></svg>
                </button>
                <button>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h2V3H3v2zm0 4h2V7H3v2zm0 4h2v-2H3v2zm4-8h14V3H7v2zm0 4h14V7H7v2zm0 4h14v-2H7v2zm-4 4h2v-2H3v2zm4 0h14v-2H7v2z"/></svg>
                </button>
            </div>
        </div>

        <!-- FEED -->
        <div class="feed" id="feed">
            <div class="loader"><div class="spinner"></div></div>
        </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="right-sidebar">
        <div class="widget">
            <div class="widget-header">Comunidades populares</div>
            <div class="widget-body" id="comm-list">
                <div class="loader"><div class="spinner"></div></div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-body">
                <div style="font-size:13px;color:var(--muted);line-height:1.8">
                    <strong style="color:var(--text)">Reddit</strong> es un lugar para la comunidad, la conversación y la conexión con millones de usuarios en todo el mundo.
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;flex-direction:column">
                    <button style="width:100%;padding:8px;background:var(--red);color:#fff;border:none;border-radius:20px;font-family:'IBM Plex Sans',sans-serif;font-size:14px;font-weight:700;cursor:pointer">Crear cuenta</button>
                    <button style="width:100%;padding:8px;background:none;border:1px solid var(--blue);color:var(--blue);border-radius:20px;font-family:'IBM Plex Sans',sans-serif;font-size:14px;font-weight:700;cursor:pointer">Iniciar sesión</button>
                </div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-body footer-links">
                <a href="#">Ayuda</a><a href="#">Acerca de</a><a href="#">Carreras</a>
                <a href="#">Prensa</a><a href="#">Blog</a><a href="#">Reglas</a>
                <a href="#">Privacidad</a><a href="#">Términos</a>
                <span>Reddit Inc © 2025. Todos los derechos reservados.</span>
            </div>
        </div>
    </div>
</div>

<script>
let currentSub = 'popular', currentSort = 'hot', allPosts = [];
const COLORS = ['#FF4500','#FF6534','#0079D3','#46D160','#FFB000','#7193FF','#FF585B','#00A6A6'];

function getColor(s) { let h=0; for(let c of s) h=c.charCodeAt(0)+((h<<5)-h); return COLORS[Math.abs(h)%COLORS.length]; }

function fmtNum(n) {
    if(!n) return '0';
    if(n>=1000000) return (n/1000000).toFixed(1)+'M';
    if(n>=1000) return (n/1000).toFixed(1)+'K';
    return n+'';
}

function timeAgo(ts) {
    const d=Math.floor(Date.now()/1000-ts);
    if(d<60) return 'hace '+d+'s';
    if(d<3600) return 'hace '+Math.floor(d/60)+'m';
    if(d<86400) return 'hace '+Math.floor(d/3600)+'h';
    return 'hace '+Math.floor(d/86400)+'d';
}

async function fetchPosts(sub, sort) {
    document.getElementById('feed').innerHTML = '<div class="loader"><div class="spinner"></div></div>';
    try {
        const res = await fetch(`reddit.php?fetch=1&sub=${sub}&sort=${sort}`);
        const data = await res.json();
        allPosts = data.data?.children?.map(c=>c.data) || [];
        renderPosts(allPosts);
    } catch(e) {
        document.getElementById('feed').innerHTML = '<div class="empty">Error al cargar. Intenta de nuevo.</div>';
    }
}

function renderPosts(posts) {
    if(!posts.length) { document.getElementById('feed').innerHTML='<div class="empty">No se encontraron posts.</div>'; return; }
    document.getElementById('feed').innerHTML = posts.map((p,i) => {
        const thumb = p.thumbnail && p.thumbnail.startsWith('http') ? p.thumbnail : null;
        const bigImg = p.url && /\.(jpg|jpeg|png|gif|webp)$/i.test(p.url) ? p.url : null;
        const awards = p.total_awards_received||0;
        const color = getColor(p.subreddit||'r');
        return `
        <div class="post" style="animation-delay:${i*0.03}s" onclick="window.open('https://reddit.com${p.permalink}','_blank')">
            <div class="post-inner">
                <div class="vote-col">
                    <button class="vote-up" onclick="event.stopPropagation()">▲</button>
                    <span class="vote-num">${fmtNum(p.score)}</span>
                    <button class="vote-down" onclick="event.stopPropagation()">▼</button>
                </div>
                <div class="post-content">
                    <div class="post-header">
                        <span class="post-sub">
                            <span class="sub-icon" style="background:${color}">${p.subreddit?.charAt(0)||'R'}</span>
                            r/${p.subreddit}
                        </span>
                        <span>•</span>
                        <span>hace ${timeAgo(p.created_utc).replace('hace ','')}</span>
                        <span>por u/${p.author}</span>
                        <button class="join-btn" onclick="event.stopPropagation()">Unirse</button>
                        <button style="background:none;border:none;cursor:pointer;color:var(--muted);margin-left:auto" onclick="event.stopPropagation()">•••</button>
                    </div>
                    ${p.link_flair_text?`<span class="post-flair">${p.link_flair_text}</span>`:''}
                    <div class="post-title">${p.title}</div>
                    ${bigImg?`<img class="post-img" src="${bigImg}" alt="" loading="lazy" onerror="this.style.display='none'">`:''}
                    ${p.selftext?`<div class="post-text-preview">${p.selftext}</div>`:''}
                </div>
                ${thumb&&!bigImg?`<img class="post-thumb" src="${thumb}" alt="" onerror="this.style.display='none'">`:''}
            </div>
            <div class="post-actions">
                <button class="act-btn" onclick="event.stopPropagation()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z"/></svg>
                    ${fmtNum(p.num_comments)} comentarios
                </button>
                <button class="act-btn" onclick="event.stopPropagation()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
                    Compartir
                </button>
                <button class="act-btn" onclick="event.stopPropagation()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-1.99.9-1.99 2L5 21l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg>
                    Guardar
                </button>
                ${awards>0?`<div class="awards">🏅 ${awards}</div>`:''}
            </div>
        </div>`;
    }).join('');
}

async function loadCommunities() {
    const subs = ['DestinyTheGame','anime','destiny2','FortNiteBR','dndnext','worldnews'];
    const results = await Promise.all(
        subs.map(s => fetch(`reddit.php?about=1&sub=${s}`).then(r=>r.json()).catch(()=>null))
    );
    const list = document.getElementById('comm-list');
    list.innerHTML = results.map(d => {
        if(!d?.data) return '';
        const s = d.data;
        const color = getColor(s.display_name||'r');
        return `<div class="comm-item">
            <div class="comm-icon" style="background:${color}">${s.display_name?.charAt(0)||'R'}</div>
            <div class="comm-info">
                <div class="comm-name">r/${s.display_name}</div>
                <div class="comm-members">${fmtNum(s.subscribers)} miembros</div>
            </div>
            <button class="comm-join" onclick="event.stopPropagation()">Unirse</button>
        </div>`;
    }).filter(Boolean).join('') + `<a class="see-more" href="#">Ver más</a>`;
}

function loadSub(sub) {
    currentSub = sub;
    fetchPosts(sub, currentSort);
}

function loadSubChip(sub, btn) {
    currentSub = sub;
    document.querySelectorAll('.fchip').forEach(c=>c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('q').value = '';
    fetchPosts(sub, currentSort);
}

function setSort(sort, btn) {
    currentSort = sort;
    document.querySelectorAll('.sort-item').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    fetchPosts(currentSub, sort);
}

document.getElementById('q').addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    if(!q) { renderPosts(allPosts); return; }
    renderPosts(allPosts.filter(p => p.title.toLowerCase().includes(q) || p.subreddit.toLowerCase().includes(q)));
});

function buscar() {
    const q = document.getElementById('q').value.trim().toLowerCase();
    if(!q) return;
    renderPosts(allPosts.filter(p => p.title.toLowerCase().includes(q) || p.subreddit.toLowerCase().includes(q)));
}

fetchPosts(currentSub, currentSort);
loadCommunities();
</script>
</body>
</html>