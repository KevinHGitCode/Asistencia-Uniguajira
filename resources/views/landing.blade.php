@php
    $siteUrl = url('/');
    $ogImage = asset('images/fondo-uniguajira.png');
    $loginUrl = route('login');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AURA · Asistencia Uniguajira — Control de asistencia de la Universidad de La Guajira</title>
    <meta name="description" content="AURA (Asistencia Uniguajira) es el sistema de control de asistencia a eventos de la Universidad de La Guajira. Registro por código QR, estadísticas en tiempo real y reportes en PDF y Excel. Desarrollado por el Semillero SIIS2 de la sede Maicao.">
    <link rel="canonical" href="{{ $siteUrl }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Asistencia Uniguajira">
    <meta property="og:title" content="AURA · Asistencia Uniguajira — Universidad de La Guajira">
    <meta property="og:description" content="Control de asistencia a eventos por código QR, con estadísticas en tiempo real y reportes en PDF y Excel. Un producto del Semillero SIIS2 de la sede Maicao.">
    <meta property="og:url" content="{{ $siteUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="es_CO">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AURA · Asistencia Uniguajira — Universidad de La Guajira">
    <meta name="twitter:description" content="Control de asistencia a eventos por código QR, con estadísticas en tiempo real y reportes en PDF y Excel.">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}">

    {{-- Fuente Manrope (mismo proveedor que ya usa la app: bunny.net, con display=swap, no bloqueante) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">

    {{-- Datos estructurados: la app y la organización que la respalda.
         Se genera con json_encode para que Blade no interprete @context/@type/@graph. --}}
    <script type="application/ld+json">
@php
echo json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            // Quién es AURA. `alternateName` cubre las dos formas con que la
            // gente busca la marca, y `parentOrganization` ata el nombre
            // (que es una palabra común) a la universidad.
            '@type' => 'Organization',
            '@id' => $siteUrl.'/#organizacion',
            'name' => 'AURA — Asistencia Uniguajira',
            'alternateName' => ['AURA', 'Asistencia Uniguajira', 'AURA Uniguajira'],
            'url' => $siteUrl,
            'logo' => asset('images/aura_negro.png'),
            'parentOrganization' => [
                '@type' => 'CollegeOrUniversity',
                'name' => 'Universidad de La Guajira',
                'alternateName' => 'Uniguajira',
                'url' => 'https://uniguajira.edu.co',
            ],
            'areaServed' => [
                '@type' => 'AdministrativeArea',
                'name' => 'La Guajira, Colombia',
            ],
        ],
        [
            '@type' => 'WebApplication',
            '@id' => $siteUrl.'/#app',
            'name' => 'AURA — Asistencia Uniguajira',
            'url' => $siteUrl,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => 'es',
            'description' => 'Sistema de control de asistencia a eventos de la Universidad de La Guajira: registro por código QR, estadísticas en tiempo real y reportes en PDF y Excel.',
            'author' => [
                '@type' => 'Organization',
                'name' => 'Semillero SIIS2 — Universidad de La Guajira, sede Maicao',
            ],
            'publisher' => ['@id' => $siteUrl.'/#organizacion'],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@endphp
    </script>

    <style>
        :root {
            --ink: #16323a;          /* texto, títulos, íconos, superficie oscura */
            --green-deep: #2f7079;   /* relleno de CTA (texto blanco, contraste AA 5.6:1) */
            --green: #4d94a0;        /* acentos, íconos sobre claro */
            --green-light: #62a9b6;  /* hovers, blob decorativo */
            --coral: #cc5e50;        /* acento secundario, blob decorativo */
            --slate: #5c7880;        /* texto secundario */
            --mist: #a9c3c8;         /* trazos suaves, inactivo */
            --canvas: #f6f7fb;       /* fondo de página (igual que la página de acceso QR) */
            --paper: #ffffff;        /* tarjetas */
            --pebble: #eef3f3;       /* badges, fills sutiles */
            --hairline: #d5e2e2;     /* bordes, divisores */
            --shadow: rgba(77,148,160,0.05) 0 4px 5px 0, rgba(77,148,160,0.04) 0 8px 15px 0, rgba(77,148,160,0.08) 0 30px 50px 0;
            --shadow-sm: rgba(77,148,160,0.05) 0 4px 5px 0, rgba(77,148,160,0.05) 0 10px 20px 0;
            --radius-card: 16px;
            --radius-panel: 24px;
            --maxw: 1200px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Manrope', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            background: var(--canvas);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        img { max-width: 100%; display: block; }
        a { color: inherit; text-decoration: none; }
        ::selection { background: var(--green-light); color: #fff; }

        .wrap { max-width: var(--maxw); margin: 0 auto; padding: 0 24px; }

        h1, h2, h3 { letter-spacing: -0.02em; line-height: 1.08; }
        h1 { font-size: clamp(2.3rem, 6vw, 5rem); font-weight: 800; }
        h2 { font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 800; }
        h3 { font-size: clamp(1.25rem, 2vw, 1.6rem); font-weight: 700; }
        .lead { font-size: clamp(1.05rem, 1.6vw, 1.25rem); color: var(--slate); font-weight: 400; }

        /* ---- Botones ---- */
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            font-weight: 600; font-size: 1.0625rem; line-height: 1;
            padding: .8rem 1.5rem; border-radius: 8px; border: 1px solid transparent;
            cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
            white-space: nowrap;
        }
        .btn-primary { background: var(--green-deep); color: #fff; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background: #266069; transform: translateY(-1px); }
        .btn-dark { background: var(--ink); color: #fff; }
        .btn-dark:hover { background: #0e2429; transform: translateY(-1px); }
        .btn-ghost { color: var(--ink); font-weight: 600; }
        .btn-ghost:hover { color: var(--green-deep); }
        .btn-outline-light { color: #fff; border-color: rgba(255,255,255,.5); }
        .btn-outline-light:hover { background: rgba(255,255,255,.1); }
        .btn-lg { padding: 1rem 1.8rem; font-size: 1.125rem; }

        /* ---- Badge ---- */
        .badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--pebble); color: var(--green-deep);
            font-size: .8rem; font-weight: 600; letter-spacing: .01em;
            padding: .35rem .8rem; border-radius: 9999px;
        }

        /* ---- Nav ---- */
        header.nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(246,247,251,.85); backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--hairline);
        }
        .nav-inner { height: 64px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .nav-brand { display: flex; align-items: center; gap: .7rem; }
        .nav-brand img.aura { height: 26px; width: auto; }
        .nav-brand .sep { width: 1px; height: 24px; background: var(--hairline); }
        .nav-brand img.uni { height: 30px; width: auto; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { font-size: .95rem; font-weight: 500; color: var(--slate); }
        .nav-links a:hover { color: var(--ink); }
        .nav-cta { display: flex; align-items: center; gap: .8rem; }
        .nav-toggle { display: none; background: none; border: 0; cursor: pointer; padding: .4rem; color: var(--ink); }
        #nav-mobile { display: none; border-top: 1px solid var(--hairline); background: var(--canvas); }
        #nav-mobile a { display: block; padding: .9rem 0; font-weight: 500; color: var(--ink); border-bottom: 1px solid var(--hairline); }
        #nav-mobile .btn { width: 100%; justify-content: center; margin-top: 1rem; }

        /* ---- Secciones ---- */
        section { padding: clamp(48px, 8vw, 80px) 0; }
        .section-header { max-width: 640px; margin: 0 auto 3rem; text-align: center; }
        .section-header .lead { margin-top: 1rem; }

        /* ---- Hero ---- */
        .hero { padding-top: clamp(40px, 6vw, 72px); }
        .hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 64px; align-items: center; }
        .hero h1 { margin: 1.2rem 0; }
        .hero .lead { max-width: 34rem; }
        .hero-actions { display: flex; align-items: center; gap: 1rem; margin-top: 2rem; flex-wrap: wrap; }
        .hero-note { margin-top: 1.2rem; font-size: .9rem; color: var(--slate); display: flex; align-items: center; gap: .5rem; }

        /* ---- Blobs + tarjetas visuales ---- */
        .visual { position: relative; }
        .blob { position: absolute; border-radius: 50%; filter: blur(46px); opacity: .55; z-index: 0; pointer-events: none; }
        .blob-green { background: var(--green-light); }
        .blob-coral { background: var(--coral); }
        .card {
            position: relative; z-index: 1;
            background: var(--paper); border-radius: var(--radius-card);
            box-shadow: var(--shadow); border: 1px solid var(--hairline);
            overflow: hidden;
        }
        .card-pad { padding: 20px; }

        /* Mock de panel/estadísticas */
        .mock-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--hairline); }
        .mock-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--mist); }
        .mock-dots { display: flex; gap: 6px; }
        .mock-title { font-size: .8rem; font-weight: 600; color: var(--slate); }
        .tiles { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .tile { background: var(--pebble); border-radius: 10px; padding: 12px; }
        .tile .n { font-size: 1.4rem; font-weight: 800; color: var(--ink); }
        .tile .l { font-size: .68rem; color: var(--slate); text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }
        .bars { display: flex; align-items: flex-end; gap: 10px; height: 120px; padding-top: 8px; }
        .bar { flex: 1; border-radius: 6px 6px 0 0; background: linear-gradient(var(--green-light), var(--green)); }
        .bar.coral { background: linear-gradient(#e08a80, var(--coral)); }

        /* Mock QR */
        .qr { width: 118px; height: 118px; border-radius: 10px; padding: 8px; background: #fff; border: 1px solid var(--hairline); }
        .qr svg { width: 100%; height: 100%; }

        /* Lista de features en texto */
        .feature { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
        .feature + .feature { margin-top: clamp(56px, 8vw, 96px); }
        .feature .badge { margin-bottom: 1.2rem; }
        .feature h3 { margin-bottom: .9rem; }
        .feature p { color: var(--slate); font-size: 1.05rem; }
        .feature ul { list-style: none; margin-top: 1.2rem; display: grid; gap: .7rem; }
        .feature li { display: flex; align-items: flex-start; gap: .6rem; color: var(--ink); font-weight: 500; }
        .feature li svg { flex: none; margin-top: 3px; color: var(--green-deep); }

        /* ---- Trust strip ---- */
        .trust { padding: 40px 0; border-top: 1px solid var(--hairline); border-bottom: 1px solid var(--hairline); }
        .trust-inner { display: flex; align-items: center; justify-content: center; gap: 2rem; flex-wrap: wrap; text-align: center; }
        .trust p { font-size: .85rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--slate); }
        .trust img { height: 42px; width: auto; opacity: .8; filter: grayscale(1); }

        /* ---- Cómo funciona ---- */
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .step { text-align: center; }
        .step-n {
            width: 48px; height: 48px; margin: 0 auto 1.2rem; border-radius: 50%;
            background: var(--pebble); color: var(--green-deep);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.2rem;
        }
        .step h3 { margin-bottom: .6rem; }
        .step p { color: var(--slate); }

        /* ---- Institucional ---- */
        .inst { display: grid; grid-template-columns: .85fr 1.15fr; gap: 64px; align-items: center; }
        .inst-logo { display: flex; align-items: center; justify-content: center; background: var(--paper); border: 1px solid var(--hairline); border-radius: var(--radius-panel); padding: 40px; box-shadow: var(--shadow-sm); }
        .inst-logo img { max-height: 140px; width: auto; }
        .inst p { color: var(--slate); font-size: 1.08rem; margin-top: 1rem; }

        /* ---- CTA final ---- */
        .cta-final { padding-bottom: clamp(48px, 8vw, 80px); }
        .cta-box {
            background: var(--ink); color: #fff; border-radius: var(--radius-panel);
            padding: clamp(40px, 6vw, 72px); text-align: center; position: relative; overflow: hidden;
        }
        .cta-box h2 { color: #fff; }
        .cta-box p { color: rgba(255,255,255,.8); max-width: 32rem; margin: 1rem auto 2rem; }
        .cta-box .blob { opacity: .35; }

        /* ---- Footer ---- */
        footer.ft { background: var(--canvas); border-top: 1px solid var(--hairline); padding: 56px 0 32px; }
        .ft-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 40px; }
        .ft-brand img { height: 28px; width: auto; margin-bottom: 1rem; }
        .ft-brand p { color: var(--slate); font-size: .9rem; max-width: 22rem; }
        .ft-col h4 { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--slate); margin-bottom: 1rem; }
        .ft-col a { display: block; font-size: .92rem; font-weight: 500; color: var(--ink); padding: .35rem 0; }
        .ft-col a:hover { color: var(--green-deep); }
        .ft-bottom { margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--hairline); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; font-size: .85rem; color: var(--slate); }

        /* ---- Responsive ---- */
        @media (max-width: 900px) {
            .nav-links { display: none; }
            .nav-cta .btn-ghost { display: none; }
            .nav-toggle { display: inline-flex; }
            .hero-grid, .feature, .inst { grid-template-columns: 1fr; gap: 40px; }
            .feature.reverse .visual { order: -1; }
            .steps { grid-template-columns: 1fr; gap: 40px; }
            .ft-grid { grid-template-columns: 1fr 1fr; }
            .ft-brand { grid-column: 1 / -1; }
        }
        @media (max-width: 520px) {
            .tiles { grid-template-columns: 1fr 1fr; }
            .ft-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    {{-- ══════════════ NAV ══════════════ --}}
    <header class="nav">
        <div class="wrap nav-inner">
            <a href="{{ $siteUrl }}" class="nav-brand" aria-label="AURA — Asistencia Uniguajira">
                <img src="{{ asset('images/aura_negro.png') }}" alt="AURA" class="aura">
                <span class="sep"></span>
                <img src="{{ asset('images/Logo-uniguajira-de-retina1.webp') }}" alt="Universidad de La Guajira" class="uni">
            </a>

            <nav class="nav-links" aria-label="Principal">
                <a href="#caracteristicas">Características</a>
                <a href="#como-funciona">Cómo funciona</a>
                <a href="#institucional">El proyecto</a>
            </nav>

            <div class="nav-cta">
                <a href="{{ $loginUrl }}" class="btn btn-ghost">Iniciar sesión</a>
                <a href="{{ $loginUrl }}" class="btn btn-primary">Acceder</a>
                <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="nav-mobile">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
        <div class="wrap" id="nav-mobile">
            <a href="#caracteristicas">Características</a>
            <a href="#como-funciona">Cómo funciona</a>
            <a href="#institucional">El proyecto</a>
            <a href="{{ $loginUrl }}" class="btn btn-primary">Iniciar sesión</a>
        </div>
    </header>

    <main>
        {{-- ══════════════ HERO ══════════════ --}}
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <span class="badge">Universidad de La Guajira · Sede Maicao</span>
                    <h1>Control de asistencia,<br>simple y con evidencia</h1>
                    <p class="lead">
                        <strong>AURA — Asistencia Uniguajira</strong> es el sistema de control de asistencia a
                        eventos de la Universidad de La Guajira. Crea eventos, registra asistentes con un código QR
                        y obtén estadísticas y reportes al instante.
                    </p>
                    <div class="hero-actions">
                        <a href="{{ $loginUrl }}" class="btn btn-primary btn-lg">Iniciar sesión</a>
                        <a href="#caracteristicas" class="btn btn-ghost btn-lg">Conocer más →</a>
                    </div>
                    <p class="hero-note">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                        Los asistentes se registran por QR, sin necesidad de crear una cuenta.
                    </p>
                </div>

                <div class="visual" aria-hidden="true">
                    <span class="blob blob-green" style="width: 260px; height: 260px; top: -30px; right: -20px;"></span>
                    <span class="blob blob-coral" style="width: 200px; height: 200px; bottom: -30px; left: -10px;"></span>
                    <div class="card">
                        <div class="mock-head">
                            <span class="mock-title">Estadísticas del evento</span>
                            <span class="mock-dots"><span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span></span>
                        </div>
                        <div class="card-pad">
                            <div class="tiles">
                                <div class="tile"><div class="n">342</div><div class="l">Asistentes</div></div>
                                <div class="tile"><div class="n">18</div><div class="l">Programas</div></div>
                                <div class="tile"><div class="n">96%</div><div class="l">Registro QR</div></div>
                            </div>
                            <div class="bars">
                                <div class="bar" style="height: 55%"></div>
                                <div class="bar" style="height: 80%"></div>
                                <div class="bar coral" style="height: 100%"></div>
                                <div class="bar" style="height: 62%"></div>
                                <div class="bar" style="height: 88%"></div>
                                <div class="bar" style="height: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════ TRUST ══════════════ --}}
        <div class="trust">
            <div class="wrap trust-inner">
                <p>Un producto del Semillero SIIS2 · Universidad de La Guajira — Sede Maicao</p>
                <img src="{{ asset('images/SIIS2 Negro.png') }}" alt="Semillero SIIS2">
                <img src="{{ asset('images/logoUniguajira.png') }}" alt="Universidad de La Guajira">
            </div>
        </div>

        {{-- ══════════════ CARACTERÍSTICAS ══════════════ --}}
        <section id="caracteristicas">
            <div class="wrap">
                <div class="section-header">
                    <h2>Todo lo que necesitas para el control de asistencia</h2>
                    <p class="lead">Desde la creación del evento hasta el reporte oficial, en un solo lugar y pensado para el día a día de la Universidad de La Guajira.</p>
                </div>

                {{-- Feature 1: Eventos --}}
                <div class="feature">
                    <div>
                        <span class="badge">Eventos</span>
                        <h3>Crea y programa eventos en minutos</h3>
                        <p>Define título, fecha, lugar y dependencia responsable. Cada evento genera su propio enlace y código QR para el registro de asistencia.</p>
                        <ul>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Eventos por dependencia y sede</li>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Enlace público y QR automáticos</li>
                        </ul>
                    </div>
                    <div class="visual" aria-hidden="true">
                        <span class="blob blob-green" style="width: 240px; height: 240px; top: -20px; left: -20px;"></span>
                        <div class="card card-pad">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                                <div style="width:44px;height:44px;border-radius:10px;background:var(--pebble);display:flex;align-items:center;justify-content:center;color:var(--green-deep)">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                </div>
                                <div>
                                    <div style="font-weight:700">Semana de la Ingeniería 2026</div>
                                    <div style="font-size:.82rem;color:var(--slate)">24 de junio · 3:00 PM · Sede Maicao</div>
                                </div>
                            </div>
                            <div style="height:1px;background:var(--hairline);margin:6px 0 14px"></div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <span class="badge" style="background:#e7f3ef;color:var(--green-deep)">● En vivo</span>
                                <span class="badge">Bienestar Universitario</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Feature 2: QR --}}
                <div class="feature reverse">
                    <div class="visual" aria-hidden="true">
                        <span class="blob blob-coral" style="width: 230px; height: 230px; top: -20px; right: -10px;"></span>
                        <div class="card card-pad" style="display:flex;align-items:center;gap:20px;">
                            <div class="qr">
                                <svg viewBox="0 0 100 100" role="img" aria-label="Código QR de ejemplo">
                                    <rect width="100" height="100" fill="#fff"/>
                                    <g fill="#16323a">
                                        <rect x="6" y="6" width="26" height="26"/><rect x="12" y="12" width="14" height="14" fill="#fff"/><rect x="16" y="16" width="6" height="6" fill="#16323a"/>
                                        <rect x="68" y="6" width="26" height="26"/><rect x="74" y="12" width="14" height="14" fill="#fff"/><rect x="78" y="16" width="6" height="6" fill="#16323a"/>
                                        <rect x="6" y="68" width="26" height="26"/><rect x="12" y="74" width="14" height="14" fill="#fff"/><rect x="16" y="78" width="6" height="6" fill="#16323a"/>
                                        <rect x="40" y="6" width="6" height="6"/><rect x="52" y="6" width="6" height="12"/><rect x="40" y="18" width="6" height="6"/>
                                        <rect x="6" y="40" width="6" height="6"/><rect x="18" y="40" width="12" height="6"/><rect x="40" y="40" width="6" height="6"/><rect x="52" y="40" width="6" height="6"/><rect x="64" y="40" width="6" height="6"/><rect x="82" y="40" width="6" height="6"/>
                                        <rect x="40" y="52" width="6" height="6"/><rect x="58" y="52" width="6" height="6"/><rect x="74" y="52" width="6" height="6"/><rect x="88" y="52" width="6" height="6"/>
                                        <rect x="40" y="68" width="6" height="6"/><rect x="52" y="74" width="6" height="6"/><rect x="64" y="68" width="6" height="12"/><rect x="80" y="68" width="6" height="6"/><rect x="88" y="80" width="6" height="6"/>
                                        <rect x="40" y="88" width="12" height="6"/><rect x="64" y="88" width="6" height="6"/>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight:700;margin-bottom:4px">Escanea y regístrate</div>
                                <div style="font-size:.9rem;color:var(--slate)">Sin cuenta. Solo tus datos una vez y listo.</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge">Registro por QR</span>
                        <h3>Asistencia con un solo escaneo</h3>
                        <p>El asistente escanea el código, completa sus datos y queda registrado. No necesita crear una cuenta ni instalar nada, y tú ves la asistencia crecer en tiempo real.</p>
                        <ul>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Registro público, sin fricción</li>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Consentimiento de tratamiento de datos incluido</li>
                        </ul>
                    </div>
                </div>

                {{-- Feature 3: Estadísticas --}}
                <div class="feature">
                    <div>
                        <span class="badge">Estadísticas</span>
                        <h3>Estadísticas en tiempo real</h3>
                        <p>Visualiza la participación por programa, dependencia, estamento y sede. Las gráficas se actualizan mientras el evento transcurre, sin recargar la página.</p>
                        <ul>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Demografía de participación</li>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Comparación entre eventos</li>
                        </ul>
                    </div>
                    <div class="visual" aria-hidden="true">
                        <span class="blob blob-green" style="width: 240px; height: 240px; bottom: -20px; right: -10px;"></span>
                        <div class="card card-pad">
                            <div class="bars" style="height:150px">
                                <div class="bar" style="height: 40%"></div>
                                <div class="bar" style="height: 65%"></div>
                                <div class="bar" style="height: 52%"></div>
                                <div class="bar coral" style="height: 92%"></div>
                                <div class="bar" style="height: 70%"></div>
                                <div class="bar" style="height: 84%"></div>
                                <div class="bar" style="height: 48%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Feature 4: Reportes --}}
                <div class="feature reverse">
                    <div class="visual" aria-hidden="true">
                        <span class="blob blob-coral" style="width: 220px; height: 220px; top: -10px; left: -10px;"></span>
                        <div class="card card-pad">
                            <div style="display:flex;gap:10px;margin-bottom:14px">
                                <span class="badge" style="background:#fbe9e6;color:var(--coral)">PDF</span>
                                <span class="badge" style="background:#e7f3ef;color:var(--green-deep)">Excel</span>
                            </div>
                            @for ($i = 0; $i < 4; $i++)
                                <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--hairline)">
                                    <span style="width:26px;height:26px;border-radius:6px;background:var(--pebble)"></span>
                                    <span style="height:8px;border-radius:4px;background:var(--hairline);flex:1"></span>
                                    <span style="height:8px;width:40px;border-radius:4px;background:var(--mist)"></span>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div>
                        <span class="badge">Reportes</span>
                        <h3>Formatos PDF oficiales y exportación a Excel</h3>
                        <p>Descarga la lista de asistencia en el formato PDF oficial de cada dependencia, o exporta los datos a Excel para tus propios análisis e informes de acreditación.</p>
                        <ul>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> PDF con el formato de la dependencia</li>
                            <li><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Exportación a Excel</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════ CÓMO FUNCIONA ══════════════ --}}
        <section id="como-funciona" style="background: var(--paper);">
            <div class="wrap">
                <div class="section-header">
                    <h2>Cómo funciona</h2>
                    <p class="lead">Tres pasos, del evento al reporte.</p>
                </div>
                <div class="steps">
                    <div class="step">
                        <div class="step-n">1</div>
                        <h3>Crea el evento</h3>
                        <p>Registra el evento con su dependencia y fecha. El sistema genera el código QR automáticamente.</p>
                    </div>
                    <div class="step">
                        <div class="step-n">2</div>
                        <h3>Comparte el QR</h3>
                        <p>Muestra o imparte el código. Cada asistente se registra desde su celular en segundos.</p>
                    </div>
                    <div class="step">
                        <div class="step-n">3</div>
                        <h3>Consulta y reporta</h3>
                        <p>Sigue la asistencia en tiempo real y descarga el reporte oficial en PDF o Excel.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════ INSTITUCIONAL ══════════════ --}}
        <section id="institucional">
            <div class="wrap inst">
                <div class="inst-logo">
                    <img src="{{ asset('images/SIIS2 Colores.png') }}" alt="Semillero SIIS2 — Universidad de La Guajira">
                </div>
                <div>
                    <span class="badge">El proyecto</span>
                    <h2 style="margin-top:1rem">Hecho en la Universidad de La Guajira</h2>
                    <p><strong>AURA — Asistencia Uniguajira</strong> nace en el <strong>Semillero de Investigación SIIS2 de la sede Maicao</strong> de la Universidad de La Guajira, como respuesta al reto de dejar atrás las planillas de papel en el control de asistencia a eventos institucionales.</p>
                    <p>Hoy acompaña a las dependencias de la Universidad en el registro digital de asistencia, la generación de evidencia formal para procesos de calidad y acreditación, y el análisis de la participación de su comunidad educativa en toda La Guajira.</p>
                </div>
            </div>
        </section>

        {{-- ══════════════ CTA FINAL ══════════════ --}}
        <section class="cta-final">
            <div class="wrap">
                <div class="cta-box">
                    <span class="blob blob-green" style="width: 300px; height: 300px; top: -80px; left: -40px;"></span>
                    <span class="blob blob-coral" style="width: 240px; height: 240px; bottom: -80px; right: -30px;"></span>
                    <div style="position:relative;z-index:1">
                        <h2>Accede al sistema</h2>
                        <p>Ingresa con tu cuenta institucional para crear eventos, gestionar asistencias y consultar las estadísticas.</p>
                        <a href="{{ $loginUrl }}" class="btn btn-primary btn-lg">Iniciar sesión</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ══════════════ FOOTER ══════════════ --}}
    <footer class="ft">
        <div class="wrap">
            <div class="ft-grid">
                <div class="ft-brand">
                    <img src="{{ asset('images/aura_negro.png') }}" alt="AURA — Asistencia Uniguajira">
                    <p>Sistema de control de asistencia a eventos de la Universidad de La Guajira. Un producto del Semillero SIIS2 de la sede Maicao.</p>
                </div>
                <div class="ft-col">
                    <h4>Sistema</h4>
                    <a href="#caracteristicas">Características</a>
                    <a href="#como-funciona">Cómo funciona</a>
                    <a href="{{ $loginUrl }}">Iniciar sesión</a>
                </div>
                <div class="ft-col">
                    <h4>Institución</h4>
                    <a href="https://uniguajira.edu.co" target="_blank" rel="noopener">Universidad de La Guajira</a>
                    <a href="#institucional">Semillero SIIS2</a>
                    <a href="#">Tratamiento de datos</a>
                </div>
            </div>
            <div class="ft-bottom">
                <span>&copy; {{ date('Y') }} Universidad de La Guajira · Sede Maicao</span>
                <span>AURA — Asistencia Uniguajira</span>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var toggle = document.getElementById('nav-toggle');
            var menu = document.getElementById('nav-mobile');
            if (!toggle || !menu) return;
            toggle.addEventListener('click', function () {
                var open = menu.style.display === 'block';
                menu.style.display = open ? 'none' : 'block';
                toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
            });
            menu.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () {
                    menu.style.display = 'none';
                    toggle.setAttribute('aria-expanded', 'false');
                });
            });
        })();
    </script>
</body>
</html>
