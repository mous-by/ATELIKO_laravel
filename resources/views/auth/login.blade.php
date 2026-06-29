<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ATELIKO — Connexion</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/ateliko-icon-32.png') }}?v=4">
    <link rel="shortcut icon" href="{{ asset('assets/images/ateliko-icon-32.png') }}?v=4">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/images/ateliko-icon-192.png') }}?v=4">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=4">
    <meta name="theme-color" content="#0d6efd">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --theme-accent: #0d6efd;
            --theme-soft:   rgba(13,110,253,.1);
            --theme-ring:   rgba(13,110,253,.25);
            --theme-dark:   #0a58ca;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }
        body { font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; color: #172033; }

        /* ══ CAROUSEL PLEIN ÉCRAN ══════════════════════════════════════════ */
        .bg-carousel { position: fixed; inset: 0; z-index: 0; }

        .bg-slide {
            position: absolute; inset: 0; opacity: 0; z-index: 1;
            transition: opacity 1.6s cubic-bezier(.4,0,.2,1); overflow: hidden;
        }
        .bg-slide.active { opacity: 1; z-index: 2; }

        .bg-slide img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
        .bg-slide.active img { animation: kenBurns 9s ease-in-out forwards; }
        @keyframes kenBurns {
            from { transform: scale(1)    translate(0, 0); }
            to   { transform: scale(1.08) translate(-1%, -.5%); }
        }

        /* Overlay dégradé sur chaque slide */
        .bg-slide::after {
            content: "";
            position: absolute; inset: 0;
            background:
                linear-gradient(180deg, rgba(10,18,35,.55) 0%, rgba(10,18,35,.2) 40%, rgba(10,18,35,.45) 75%, rgba(10,18,35,.72) 100%),
                linear-gradient(105deg, rgba(10,18,35,.35) 0%, transparent 55%);
        }

        /* Caption bas-gauche */
        .bg-caption {
            position: absolute;
            left: clamp(28px, 4vw, 64px);
            bottom: clamp(110px, 16vh, 180px);
            z-index: 10; max-width: min(420px, 42vw); pointer-events: none;
        }
        .bg-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--theme-accent); color: #fff;
            font-size: .72rem; font-weight: 700; padding: 5px 14px;
            border-radius: 999px; margin-bottom: 12px; letter-spacing: .3px;
            opacity: 0; transform: translateY(12px);
            transition: opacity .5s .35s, transform .5s .35s;
        }
        .bg-slide.active .bg-badge { opacity: 1; transform: translateY(0); }
        .bg-title {
            font-size: clamp(1.4rem, 2.2vw, 2rem); font-weight: 800; color: #fff;
            line-height: 1.22; margin-bottom: 10px;
            text-shadow: 0 3px 20px rgba(0,0,0,.5);
            opacity: 0; transform: translateY(16px);
            transition: opacity .55s .5s, transform .55s .5s;
        }
        .bg-slide.active .bg-title { opacity: 1; transform: translateY(0); }
        .bg-quote {
            font-size: clamp(.78rem, .9vw, .9rem); color: rgba(255,255,255,.8);
            font-style: italic; line-height: 1.6;
            opacity: 0; transform: translateY(12px);
            transition: opacity .55s .65s, transform .55s .65s;
        }
        .bg-slide.active .bg-quote { opacity: 1; transform: translateY(0); }

        /* Dots indicateurs */
        .bg-dots {
            position: fixed; left: clamp(28px, 4vw, 64px); bottom: clamp(72px, 9vh, 110px);
            z-index: 20; display: flex; align-items: center; gap: 8px;
        }
        .bg-dot {
            height: 5px; border-radius: 3px; background: rgba(255,255,255,.38);
            border: none; padding: 0; cursor: pointer; width: 5px;
            transition: width .4s ease, background .4s ease;
        }
        .bg-dot.active { width: 26px; background: #fff; }

        /* Barre de progression */
        .bg-progress { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 20; background: rgba(255,255,255,.15); }
        .bg-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--theme-accent), rgba(255,255,255,.7));
            width: 0%;
        }

        /* ══ LAYOUT PAGE ══════════════════════════════════════════════════ */
        .login-page {
            position: relative; z-index: 10; min-height: 100vh;
            display: flex; align-items: center; justify-content: flex-end;
            padding: clamp(16px,3vh,36px) clamp(16px,5vw,72px) clamp(60px,10vh,120px) clamp(16px,3vw,40px);
            overflow: auto;
        }

        /* ══ WAVE + BARRE MODULES (bas) ═══════════════════════════════════ */
        .module-wave {
            position: fixed; left: 0; right: 0; bottom: 0;
            height: clamp(72px, 11vh, 108px); z-index: 15; pointer-events: none;
        }
        .module-wave svg { position: absolute; inset: 0; width: 100%; height: 100%; overflow: visible; }
        .module-wave path { fill: var(--theme-accent); fill-opacity: .90; }
        .module-list {
            position: absolute;
            left: clamp(12px,2.5vw,48px); right: clamp(12px,2.5vw,48px); bottom: 10px;
            display: grid; grid-template-columns: repeat(12, minmax(0,1fr));
            gap: clamp(4px,.6vw,12px); align-items: center;
        }
        .module-node {
            display: inline-flex; flex-direction: column; align-items: center; gap: 3px;
            color: #fff; font-size: clamp(.52rem,.62vw,.74rem); font-weight: 700;
            text-align: center; text-shadow: 0 1px 4px rgba(0,0,0,.3); line-height: 1.2;
        }
        .module-node i { font-size: clamp(.72rem,.82vw,.96rem); }

        /* ══ CARTE DE LOGIN ═══════════════════════════════════════════════ */
        .login-card {
            width: 100%; max-width: 420px;
            padding: 32px 32px 26px;
            background: rgba(255,255,255,.93);
            backdrop-filter: blur(18px) saturate(1.4);
            -webkit-backdrop-filter: blur(18px) saturate(1.4);
            border: 1px solid rgba(255,255,255,.7);
            border-radius: 16px;
            box-shadow: 0 32px 80px rgba(10,18,35,.28), 0 0 0 1px rgba(255,255,255,.18) inset;
            position: relative; overflow: hidden;
        }
        .login-card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--theme-accent), #0dcaf0 60%, var(--theme-accent));
            border-radius: 16px 16px 0 0;
        }

        /* Brand */
        .brand-area { text-align: center; margin-bottom: 20px; }
        .brand-logo {
            width: 90px; height: 70px; margin: 0 auto 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-logo img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(15,23,42,.18)); }
        .brand-title { margin: 0; font-size: 1.9rem; font-weight: 800; color: #0f172a; letter-spacing: -.5px; line-height: 1; }
        .brand-title span:first-child { color: var(--theme-accent); }
        .brand-title span:last-child  { color: #0dcaf0; }
        .brand-subtitle { margin: 7px 0 0; color: #64748b; font-size: .78rem; font-weight: 700; letter-spacing: .2px; }

        /* Divider */
        .form-divider { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
        .form-divider::before, .form-divider::after { content: ""; flex: 1; height: 1px; background: #e5e7eb; }
        .form-divider span { font-size: .75rem; font-weight: 600; color: #94a3b8; white-space: nowrap; }

        /* Formulaire */
        .form-label { font-size: .84rem; font-weight: 700; color: #334155; margin-bottom: 7px; }
        .input-group { flex-wrap: nowrap; }
        .input-group-text {
            width: 44px; justify-content: center;
            color: var(--theme-accent); background: #f1f5f9;
            border-color: #dfe5ee; border-radius: 8px 0 0 8px;
        }
        .form-control {
            min-height: 46px; border-color: #dfe5ee;
            border-radius: 0 8px 8px 0; color: #0f172a;
            font-weight: 500; min-width: 0; background: #fff;
        }
        .form-control.no-right-radius { border-radius: 0; }
        .form-control::placeholder { font-size: .87rem; color: #94a3b8; }
        .form-control:focus { border-color: var(--theme-accent); box-shadow: 0 0 0 .2rem var(--theme-ring); }
        .password-toggle {
            width: 44px; border-color: #dfe5ee; border-left: 0;
            border-radius: 0 8px 8px 0; color: var(--theme-accent); background: #fff;
        }
        .password-toggle:hover, .password-toggle:focus {
            color: #fff; background: var(--theme-accent); border-color: var(--theme-accent);
        }
        .input-group:focus-within .input-group-text { border-color: var(--theme-accent); }
        .input-group:focus-within .password-toggle  { border-color: var(--theme-accent); }

        /* Remember me */
        .remember-row { display: flex; align-items: center; margin-bottom: 18px; }
        .remember-row .form-check-input:checked { background-color: var(--theme-accent); border-color: var(--theme-accent); }
        .remember-row .form-check-label { font-size: .83rem; color: #475569; cursor: pointer; }

        /* Bouton connexion */
        .login-button {
            min-height: 48px; border-radius: 8px; border: 0;
            background: linear-gradient(135deg, var(--theme-accent) 0%, var(--theme-dark) 100%);
            color: #fff; font-weight: 800; font-size: .95rem;
            box-shadow: 0 8px 24px var(--theme-ring);
            transition: opacity .2s, transform .1s, box-shadow .2s;
        }
        .login-button:hover  { opacity: .92; box-shadow: 0 12px 32px var(--theme-ring); color: #fff; }
        .login-button:active { transform: scale(.98); }
        .login-button:focus  { color: #fff; }
        .login-button:disabled { opacity: .7; cursor: not-allowed; }

        /* Footer carte */
        .login-footer { margin-top: 18px; text-align: center; color: #94a3b8; font-size: .78rem; }

        /* Widget caméra (modals) */
        .cam-video-box { position: relative; background: #000; border-radius: 8px; overflow: hidden; height: 200px; }
        .cam-video-box video, .cam-video-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .cam-shutter { position: absolute; inset: 0; background: rgba(255,255,255,.6); pointer-events: none; opacity: 0; transition: opacity .15s; }
        .cam-shutter.flash { opacity: 1; }

        /* ══ RESPONSIVE ═══════════════════════════════════════════════════ */
        @media (max-width: 600px) {
            html, body { height: auto; min-height: 100%; overflow-y: auto; overflow-x: hidden; }
            .login-page {
                align-items: flex-start; justify-content: center;
                padding: 20px 14px 148px; min-height: 100svh;
            }
            .login-card { padding: 22px 18px 20px; border-radius: 14px; max-width: 100%; width: 100%; }
            .brand-area { margin-bottom: 12px; }
            .brand-logo { width: 64px; height: 50px; margin-bottom: 6px; }
            .brand-title { font-size: 1.5rem; }
            .brand-subtitle { font-size: .7rem; }
            .bg-caption { display: none; }
            .bg-dots { display: none; }
            .module-wave { height: clamp(58px, 9vh, 72px); }
            .module-list { grid-template-columns: repeat(12, minmax(0,1fr)); bottom: 6px; gap: 2px; }
            .module-node { flex-direction: column; gap: 0; font-size: 0; }
            .module-node span { display: none; }
            .module-node i { font-size: .82rem; }
        }
        @media (max-width: 900px) {
            .module-node { font-size: .48rem; flex-direction: row; gap: 3px; }
            .module-node i { font-size: .68rem; }
        }
    </style>
</head>
<body>

{{-- ════ CAROUSEL PLEIN ÉCRAN ════ --}}
<div class="bg-carousel" id="bgCarousel" aria-hidden="true">

    <div class="bg-slide active" data-index="0">
        <img src="{{ asset('assets/images/jupe0.jpg') }}" alt="" loading="eager">
        <div class="bg-caption">
            <span class="bg-badge"><i class="bi bi-people me-1"></i>Clients</span>
            <h2 class="bg-title">Gérez tous vos clients en un seul endroit</h2>
            <p class="bg-quote">Profils complets, historiques de commandes et fidélisation simplifiée.</p>
        </div>
    </div>

    <div class="bg-slide" data-index="1">
        <img src="{{ asset('assets/images/model1.png') }}" alt="" loading="lazy">
        <div class="bg-caption">
            <span class="bg-badge"><i class="bi bi-palette me-1"></i>Modèles</span>
            <h2 class="bg-title">Votre catalogue de modèles, toujours à portée</h2>
            <p class="bg-quote">Créez, organisez et partagez vos collections avec élégance.</p>
        </div>
    </div>

    <div class="bg-slide" data-index="2">
        <img src="{{ asset('assets/images/jupe5.jpg') }}" alt="" loading="lazy">
        <div class="bg-caption">
            <span class="bg-badge"><i class="bi bi-rulers me-1"></i>Mesures</span>
            <h2 class="bg-title">Des mesures précises pour chaque client</h2>
            <p class="bg-quote">Enregistrez et retrouvez instantanément les mensurations de chaque client.</p>
        </div>
    </div>

    <div class="bg-slide" data-index="3">
        <img src="{{ asset('assets/images/model3.jpg') }}" alt="" loading="lazy">
        <div class="bg-caption">
            <span class="bg-badge"><i class="bi bi-calendar-check me-1"></i>Rendez-vous</span>
            <h2 class="bg-title">Planifiez et suivez vos rendez-vous</h2>
            <p class="bg-quote">Un agenda intelligent pour ne jamais manquer un client.</p>
        </div>
    </div>

    <div class="bg-slide" data-index="4">
        <img src="{{ asset('assets/images/jupe2.jpg') }}" alt="" loading="lazy">
        <div class="bg-caption">
            <span class="bg-badge"><i class="bi bi-cash-coin me-1"></i>Paiements</span>
            <h2 class="bg-title">Suivi complet de vos paiements</h2>
            <p class="bg-quote">Encaissements, avances et historiques en toute transparence.</p>
        </div>
    </div>

    <div class="bg-slide" data-index="5">
        <img src="{{ asset('assets/images/model5.jpg') }}" alt="" loading="lazy">
        <div class="bg-caption">
            <span class="bg-badge"><i class="bi bi-speedometer2 me-1"></i>Tableau de bord</span>
            <h2 class="bg-title">Votre atelier sous contrôle total</h2>
            <p class="bg-quote">Statistiques en temps réel, performance et vue d'ensemble de votre activité.</p>
        </div>
    </div>

</div>

{{-- Barre de progression --}}
<div class="bg-progress" aria-hidden="true">
    <div class="bg-progress-bar" id="bgProgressBar"></div>
</div>

{{-- Dots indicateurs --}}
<div class="bg-dots" aria-hidden="true">
    <button class="bg-dot active" data-slide="0"></button>
    <button class="bg-dot" data-slide="1"></button>
    <button class="bg-dot" data-slide="2"></button>
    <button class="bg-dot" data-slide="3"></button>
    <button class="bg-dot" data-slide="4"></button>
    <button class="bg-dot" data-slide="5"></button>
</div>

{{-- ════ WAVE + BARRE MODULES ════ --}}
<div class="module-wave" aria-hidden="true">
    <svg viewBox="0 0 900 100" preserveAspectRatio="none">
        <path d="M900 38 C790 52 755 88 630 76 C500 64 450 18 330 38 C205 60 175 90 0 68 L0 100 L900 100 Z" />
    </svg>
    <div class="module-list">
        <div class="module-node"><i class="bi bi-speedometer2"></i><span>Tableau de bord</span></div>
        <div class="module-node"><i class="bi bi-people"></i><span>Clients</span></div>
        <div class="module-node"><i class="bi bi-palette"></i><span>Modèles</span></div>
        <div class="module-node"><i class="bi bi-rulers"></i><span>Mesures</span></div>
        <div class="module-node"><i class="bi bi-person-check"></i><span>Affectations</span></div>
        <div class="module-node"><i class="bi bi-calendar-event"></i><span>Rendez-vous</span></div>
        <div class="module-node"><i class="bi bi-cash-coin"></i><span>Paiements</span></div>
        <div class="module-node"><i class="bi bi-bell"></i><span>Notifications</span></div>
        <div class="module-node"><i class="bi bi-award"></i><span>Abonnements</span></div>
        <div class="module-node"><i class="bi bi-people-fill"></i><span>Utilisateurs</span></div>
        <div class="module-node"><i class="bi bi-shop"></i><span>Atelier</span></div>
        <div class="module-node"><i class="bi bi-gear"></i><span>Paramètres</span></div>
    </div>
</div>

{{-- ════ CARTE DE LOGIN ════ --}}
<main class="login-page">
    <section class="login-card">

        <div class="brand-area">
            <div class="brand-logo">
                <img src="{{ asset('assets/images/logo_ateliko.png') }}" alt="Logo ATELIKO">
            </div>
            <h1 class="brand-title">
                <span>ATEL</span><span>IKO</span>
            </h1>
            <p class="brand-subtitle">Gestion d'Atelier de Couture</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-3 py-2 mb-3">
                <ul class="mb-0 list-unstyled small">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 py-2 small mb-3">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            </div>
        @endif

        <div class="form-divider"><span>CONNEXION</span></div>

        <form action="{{ route('login.post') }}" method="POST" id="loginForm">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="inputPhone">Numéro de téléphone</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input id="inputPhone" type="tel" name="telephone"
                           class="form-control @error('telephone') is-invalid @enderror"
                           placeholder="Ex : 74 74 56 69"
                           inputmode="tel" autocomplete="tel"
                           value="{{ old('telephone') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="inputPassword">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="inputPassword" type="password" name="password"
                           class="form-control no-right-radius"
                           placeholder="Mot de passe" autocomplete="current-password" required>
                    <button type="button" class="btn password-toggle" id="togglePassword"
                            aria-label="Afficher le mot de passe">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn login-button" id="loginButton">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    <span id="loginBtnText">Se connecter</span>
                    <span class="spinner-border spinner-border-sm ms-2 d-none" id="loginSpinner" role="status"></span>
                </button>
            </div>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} <strong style="color:var(--theme-accent);">ATEL</strong><strong style="color:#0dcaf0;">IKO</strong>
            &mdash; Tous droits réservés
        </div>
    </section>
</main>


{{-- ═════ MODAL : ABONNEMENT EXPIRÉ (PROPRIÉTAIRE) ═════ --}}
@if(session('login_blocked') === 'proprietaire')
@php
    $loginPlans   = session('login_blocked_plans', []);
    $loginPending = session('login_blocked_pending', false);
    $loginFailed  = session('login_blocked_failed');
@endphp
<div class="modal fade" id="modalLoginBlocked"
     data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bx bx-lock-alt me-2"></i>Abonnement expiré — Accès bloqué
                </h5>
            </div>
            <div class="modal-body">

                @if($loginPending)
                <div class="text-center py-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 p-4 mb-3" style="width:80px;height:80px">
                        <i class="bx bx-time-five text-warning" style="font-size:2.5rem"></i>
                    </span>
                    <h5 class="fw-bold mb-2">Paiement en cours de validation</h5>
                    <p class="text-muted">Votre preuve de paiement a été reçue et est en cours de vérification par l'administrateur.<br>
                    Vous pourrez vous connecter dès validation de votre paiement.</p>
                </div>
                @else

                @if($loginFailed)
                <div class="alert alert-danger py-2 mb-3">
                    <i class="bx bx-x-circle me-1"></i><strong>Paiement précédent rejeté.</strong>
                    Motif : {{ $loginFailed }}. Veuillez soumettre un nouveau paiement.
                </div>
                @else
                <div class="alert alert-danger py-2 mb-3">
                    <i class="bx bx-error-circle me-1"></i>
                    L'abonnement de votre atelier est expiré. Soumettez une preuve de paiement pour rétablir l'accès.
                </div>
                @endif

                <p class="text-muted small mb-3">
                    Effectuez votre paiement sur l'un des numéros ci-dessous, puis envoyez votre preuve (capture ou photo).
                    Votre accès sera rétabli après validation.
                </p>
                <div class="row g-2 mb-3">
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-bold small">Orange Money / Wave</div>
                            <div class="text-primary fw-bold">74 74 56 69</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-bold small">MobiCash</div>
                            <div class="text-primary fw-bold">67 20 57 36</div>
                        </div>
                    </div>
                </div>

                <form id="paymentLoginForm" action="{{ route('abonnement.paiement.blocked') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Plan <span class="text-danger">*</span></label>
                            <select name="plan_code" class="form-select" required>
                                <option value="">-- Choisir un plan --</option>
                                @foreach($loginPlans as $plan)
                                <option value="{{ $plan['code'] }}">{{ $plan['libelle'] }} — {{ number_format($plan['prix'], 0, ',', ' ') }} {{ $plan['devise'] }}/{{ $plan['duree_mois'] }}mois</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Mode de paiement <span class="text-danger">*</span></label>
                            <select name="mode_paiement" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <option value="ORANGE_MONEY">Orange Money</option>
                                <option value="WAVE">Wave</option>
                                <option value="MOBICASH">MobiCash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Référence de transaction</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="Ex: TXN-XXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Note (facultatif)</label>
                            <input type="text" name="owner_note" class="form-control" placeholder="Note sur le paiement">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Preuve de paiement <span class="text-danger">*</span></label>
                            <div class="camera-widget" id="camWidgetLogin">
                                <div class="btn-group w-100 mb-2" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary active cam-btn-file">
                                        <i class="bx bx-paperclip me-1"></i>Choisir un fichier
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary cam-btn-cam">
                                        <i class="bx bx-camera me-1"></i>Prendre une photo
                                    </button>
                                </div>
                                <div class="cam-file-zone">
                                    <input type="file" name="preuve" class="form-control" accept="image/*,.pdf" required>
                                    <div class="form-text">JPG, PNG ou PDF — max 5 Mo</div>
                                </div>
                                <div class="cam-live-zone" style="display:none">
                                    <div class="cam-video-box mb-2">
                                        <video class="cam-video" autoplay playsinline muted></video>
                                        <img class="cam-snap" style="display:none" alt="capture">
                                        <div class="cam-shutter"></div>
                                    </div>
                                    <canvas class="cam-canvas" style="display:none"></canvas>
                                    <div class="d-flex gap-2 mb-1">
                                        <button type="button" class="btn btn-primary btn-sm flex-fill cam-shoot">
                                            <i class="bx bx-camera me-1"></i>Capturer
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm cam-flip" title="Changer de caméra">
                                            <i class="bx bx-revision"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cam-redo" style="display:none">
                                            <i class="bx bx-refresh me-1"></i>Reprendre
                                        </button>
                                    </div>
                                    <div class="cam-msg small text-muted"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @endif

            </div>
            <div class="modal-footer justify-content-between border-0 pt-0">
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i>Réessayer la connexion
                </a>
                @if(!$loginPending)
                <button type="submit" form="paymentLoginForm" class="btn btn-danger">
                    <i class="bx bx-upload me-1"></i>Envoyer la preuve de paiement
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═════ MODAL : ACCÈS SUSPENDU (EMPLOYÉ) ═════ --}}
@if(session('login_blocked') === 'employee')
@php $blockedInfo = session('ateliko_blocked_login', []); @endphp
<div class="modal fade" id="modalLoginBlockedEmployee"
     data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-lock-alt me-2"></i>Accès suspendu</h5>
            </div>
            <div class="modal-body text-center py-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 p-4 mb-3" style="width:80px;height:80px">
                    <i class="bx bx-lock-alt text-danger" style="font-size:2.5rem"></i>
                </span>
                <h5 class="fw-bold mb-2">Accès temporairement suspendu</h5>
                <p class="text-muted">
                    L'abonnement de <strong>{{ $blockedInfo['atelier_nom'] ?? 'votre atelier' }}</strong> est expiré.<br>
                    Contactez le propriétaire de votre atelier pour renouveler l'abonnement.
                </p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── TOGGLE MOT DE PASSE ── */
    const pwdInput  = document.getElementById('inputPassword');
    const pwdToggle = document.getElementById('togglePassword');
    const pwdIcon   = pwdToggle.querySelector('i');
    pwdToggle.addEventListener('click', function () {
        const visible = pwdInput.type === 'text';
        pwdInput.type = visible ? 'password' : 'text';
        pwdIcon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    /* ── SPINNER SUBMIT ── */
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn  = document.getElementById('loginButton');
        const text = document.getElementById('loginBtnText');
        const spin = document.getElementById('loginSpinner');
        btn.disabled = true;
        text.textContent = 'Connexion…';
        spin.classList.remove('d-none');
    });

    /* ── CAROUSEL PLEIN ÉCRAN ── */
    const slides  = document.querySelectorAll('.bg-slide');
    const dotBtns = document.querySelectorAll('.bg-dot');
    const progBar = document.getElementById('bgProgressBar');
    const DURATION = 6000;
    let current = 0;
    let timer   = null;

    function goTo(idx) {
        slides[current].classList.remove('active');
        dotBtns[current].classList.remove('active');
        current = (idx + slides.length) % slides.length;
        slides[current].classList.add('active');
        dotBtns[current].classList.add('active');
        animateProgress();
    }

    function animateProgress() {
        progBar.style.transition = 'none';
        progBar.style.width = '0%';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            progBar.style.transition = `width ${DURATION}ms linear`;
            progBar.style.width = '100%';
        }));
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), DURATION);
    }

    dotBtns.forEach((btn, i) => btn.addEventListener('click', () => { goTo(i); startTimer(); }));

    /* Swipe mobile */
    let touchX = 0;
    document.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    document.addEventListener('touchend', e => {
        const diff = touchX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { goTo(diff > 0 ? current + 1 : current - 1); startTimer(); }
    });

    animateProgress();
    startTimer();

    /* ── MODALS BLOQUANTS ── */
    @if(session('login_blocked') === 'proprietaire')
    (function () { var el = document.getElementById('modalLoginBlocked'); if (el) new bootstrap.Modal(el).show(); })();
    @endif
    @if(session('login_blocked') === 'employee')
    (function () { var el = document.getElementById('modalLoginBlockedEmployee'); if (el) new bootstrap.Modal(el).show(); })();
    @endif
});

/* ── WIDGET CAMÉRA ── */
(function () {
    function initCameraWidget(w) {
        var fileZone  = w.querySelector('.cam-file-zone');
        var liveZone  = w.querySelector('.cam-live-zone');
        var fileInput = fileZone ? fileZone.querySelector('input[type=file]') : null;
        var video     = w.querySelector('.cam-video');
        var snap      = w.querySelector('.cam-snap');
        var canvas    = w.querySelector('.cam-canvas');
        var shootBtn  = w.querySelector('.cam-shoot');
        var redoBtn   = w.querySelector('.cam-redo');
        var msg       = w.querySelector('.cam-msg');
        var btnFile   = w.querySelector('.cam-btn-file');
        var btnCam    = w.querySelector('.cam-btn-cam');
        var shutter   = w.querySelector('.cam-shutter');
        var flipBtn   = w.querySelector('.cam-flip');
        var stream    = null;
        var facingMode = 'environment';

        if (!fileInput || !video) return;

        function stopStream() {
            if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
        }
        function startStream() {
            msg.textContent = 'Accès à la caméra…';
            navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 } } })
                .then(function (s) {
                    stream = s; video.srcObject = s;
                    video.style.display = 'block'; snap.style.display = 'none';
                    shootBtn.style.display = ''; redoBtn.style.display = 'none';
                    msg.textContent = '';
                })
                .catch(function (err) {
                    msg.textContent = '⚠ Caméra inaccessible : ' + (err.message || err.name);
                    msg.className = 'cam-msg small text-danger';
                });
        }

        if (flipBtn) flipBtn.addEventListener('click', function () {
            facingMode = facingMode === 'environment' ? 'user' : 'environment';
            stopStream(); startStream();
        });
        btnCam.addEventListener('click', function () {
            fileZone.style.display = 'none'; liveZone.style.display = 'block';
            btnCam.classList.add('active', 'btn-primary'); btnCam.classList.remove('btn-outline-primary');
            btnFile.classList.remove('active'); fileInput.required = false;
            startStream();
        });
        btnFile.addEventListener('click', function () {
            liveZone.style.display = 'none'; fileZone.style.display = 'block';
            btnFile.classList.add('active');
            btnCam.classList.remove('active', 'btn-primary'); btnCam.classList.add('btn-outline-primary');
            fileInput.required = true; stopStream();
        });
        shootBtn.addEventListener('click', function () {
            canvas.width = video.videoWidth || 640; canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0);
            if (shutter) { shutter.classList.add('flash'); setTimeout(function () { shutter.classList.remove('flash'); }, 200); }
            canvas.toBlob(function (blob) {
                var file = new File([blob], 'preuve_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                snap.src = URL.createObjectURL(blob);
                snap.style.display = 'block'; video.style.display = 'none';
                shootBtn.style.display = 'none'; redoBtn.style.display = '';
                msg.textContent = '✓ Photo capturée'; msg.className = 'cam-msg small text-success';
                try { var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; } catch (e) {}
                stopStream();
            }, 'image/jpeg', 0.92);
        });
        redoBtn.addEventListener('click', function () {
            snap.style.display = 'none'; shootBtn.style.display = ''; redoBtn.style.display = 'none'; msg.textContent = '';
            try { fileInput.files = new DataTransfer().files; } catch (e) {}
            startStream();
        });
        var modal = w.closest('.modal');
        if (modal) modal.addEventListener('hidden.bs.modal', function () { stopStream(); });
    }
    document.querySelectorAll('.camera-widget').forEach(initCameraWidget);
})();
</script>
</body>
</html>
