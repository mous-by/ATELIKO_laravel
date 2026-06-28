<!DOCTYPE html>
<html lang="fr" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ATELIKO') - Gestion Atelier de Couture</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/ateliko-icon-32.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('assets/images/ateliko-icon-32.png') }}?v=3">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/images/ateliko-icon-192.png') }}?v=3">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=3">
    <meta name="theme-color" content="#0d6efd">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet">
    <style>
        .stat-card{border:0;border-radius:10px;min-height:110px;box-shadow:0 2px 6px rgba(0,0,0,.08)}
        .stat-card .stat-value{font-size:1.75rem;font-weight:700}.stat-card .stat-label{font-size:.85rem;opacity:.9}.stat-card .stat-icon{font-size:2rem;opacity:.85}
        .avatar-img{width:40px;height:40px;border-radius:50%;object-fit:cover}.avatar-lg{width:80px;height:80px;border-radius:50%;object-fit:cover}
        .card{border:0;box-shadow:0 2px 6px rgba(0,0,0,.05)}.table>:not(caption)>*>*{padding:.8rem .75rem}
        .badge-en-attente{background:#ffc107;color:#000}.badge-en-cours,.badge-confirme{background:#0d6efd;color:#fff}.badge-termine,.badge-valide{background:#198754;color:#fff}.badge-annule{background:#dc3545;color:#fff}.badge-planifie{background:#6c757d;color:#fff}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:10}
        .page-breadcrumb .breadcrumb-title{font-size:20px;font-weight:600;border-right:1px solid #dee2e6}.param-menu .list-group-item{border-radius:0;color:#4c5258}.param-menu .list-group-item.active{color:#fff;background:#0d6efd}
        .form-label,label{font-weight:500}.modal-header.bg-primary,.card-header.bg-primary{background:#0d6efd!important}.table thead th{font-size:.78rem;text-transform:uppercase;letter-spacing:.02em;white-space:nowrap}.table tbody td{vertical-align:middle}
        .card-hover{transition:transform .2s,box-shadow .2s}.card-hover:hover{transform:translateY(-3px);box-shadow:0 .5rem 1rem rgba(0,0,0,.12)}
        @media(max-width:1024px){.wrapper.toggled .sidebar-overlay{display:block}}@media print{.sidebar-wrapper,header,.page-footer,.no-print{display:none!important}.page-wrapper{margin-left:0!important}}

        /* Scrollbar SimpleBar — thème clair (moins bleu) */
        .simplebar-scrollbar::before{background-color:rgba(0,0,0,.18)!important}
        .simplebar-track.simplebar-vertical{width:6px}
        .simplebar-track.simplebar-horizontal{height:6px}

        /* Hover menu param moins agressif */
        .param-menu .list-group-item-action:hover{background:rgba(13,110,253,.08)!important;color:#0d6efd!important}

        /* Widget caméra */
        .cam-video-box{position:relative;background:#000;border-radius:8px;overflow:hidden;height:200px}
        .cam-video-box video,.cam-video-box img{width:100%;height:100%;object-fit:cover;display:block}
        .cam-shutter{position:absolute;inset:0;background:rgba(255,255,255,.6);pointer-events:none;opacity:0;transition:opacity .15s}
        .cam-shutter.flash{opacity:1}
        html.dark-theme .cam-video-box{border:1px solid #3a3d50}

        /* Toggle thème */
        #btnTheme{background:none;border:none;cursor:pointer;padding:6px 10px;border-radius:8px;font-size:1.25rem;line-height:1;color:inherit;transition:background .2s}
        #btnTheme:hover{background:rgba(128,128,128,.15)}
        html.dark-theme #btnTheme .icon-light{display:none}
        html:not(.dark-theme) #btnTheme .icon-dark{display:none}

        /* Dark mode — corrections supplémentaires pour nos composants custom */
        html.dark-theme .stat-card{box-shadow:0 2px 8px rgba(0,0,0,.4)}
        html.dark-theme .card{box-shadow:0 2px 8px rgba(0,0,0,.3)}
        html.dark-theme .table-light{background:#2a2d3e!important;color:#e4e5e6}
        html.dark-theme .table-light th{background:#2a2d3e!important;color:#ced4da}
        html.dark-theme .modal-content{background:#242632;color:#e4e5e6}
        html.dark-theme .modal-header{border-color:#3a3d50}
        html.dark-theme .modal-footer{border-color:#3a3d50}
        html.dark-theme .form-control,html.dark-theme .form-select{background:#2a2d3e;border-color:#3a3d50;color:#e4e5e6}
        html.dark-theme .form-control::placeholder{color:#6c757d}
        html.dark-theme .form-control:focus,html.dark-theme .form-select:focus{background:#2a2d3e;border-color:#0d6efd;color:#e4e5e6}
        html.dark-theme .input-group-text{background:#2a2d3e;border-color:#3a3d50;color:#ced4da}
        html.dark-theme .nav-tabs .nav-link{color:#ced4da}
        html.dark-theme .nav-tabs .nav-link.active{background:#242632;border-color:#3a3d50 #3a3d50 #242632;color:#e4e5e6}
        html.dark-theme .list-group-item{background:#242632;border-color:#3a3d50;color:#e4e5e6}
        html.dark-theme .list-group-item-action:hover{background:#2a2d3e;color:#e4e5e6}
        html.dark-theme .dropdown-menu{background:#242632;border-color:#3a3d50}
        html.dark-theme .dropdown-item{color:#ced4da}
        html.dark-theme .dropdown-item:hover{background:#2a2d3e;color:#e4e5e6}
        html.dark-theme .dropdown-divider{border-color:#3a3d50}
        html.dark-theme .progress{background:#3a3d50}
        html.dark-theme .alert-success{background:#0f3a1f;border-color:#198754;color:#75b798}
        html.dark-theme .alert-danger{background:#3b1519;border-color:#dc3545;color:#ea868f}
        html.dark-theme .alert-info{background:#0d2d3e;border-color:#0dcaf0;color:#6edff6}
        html.dark-theme .badge.bg-light{background:#3a3d50!important;color:#e4e5e6!important}
        html.dark-theme .text-muted{color:#8a8d9e!important}
        html.dark-theme .border-bottom{border-color:#3a3d50!important}
        html.dark-theme .bg-light{background:#2a2d3e!important}
        html.dark-theme .border{border-color:#3a3d50!important}
        html.dark-theme code.bg-light{background:#3a3d50!important;color:#e4e5e6}

        /* Assistant d'installation */
        .install-hero{display:grid;grid-template-columns:72px 1fr;gap:16px;align-items:center}
        .install-icon{width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#0d6efd,#20c997);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.3rem;box-shadow:0 12px 28px rgba(13,110,253,.28)}
        .install-steps{display:grid;gap:10px;margin:18px 0 0;padding:0;list-style:none}
        .install-step{display:grid;grid-template-columns:34px 1fr;gap:10px;align-items:flex-start;padding:12px;border:1px solid rgba(13,110,253,.12);border-radius:8px;background:rgba(13,110,253,.04)}
        .install-step i{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fff;color:#0d6efd;font-size:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,.06)}
        .install-step strong{display:block;font-size:.92rem;margin-bottom:2px}
        .install-step span{display:block;font-size:.82rem;color:#6c757d;line-height:1.35}
        .install-note{border-radius:8px;background:#fff7e6;border:1px solid #ffe1a6;color:#6f4d00;padding:10px 12px;font-size:.85rem}
        .install-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end}
        .sidebar-install-box{padding:12px 14px;margin-top:10px;border-top:1px solid rgba(255,255,255,.12)}
        .sidebar-install-btn{width:100%;border:0;border-radius:12px;padding:10px 11px;display:flex;align-items:center;gap:10px;text-align:left;color:#fff;background:linear-gradient(135deg,#0d6efd,#19a974);box-shadow:0 10px 24px rgba(13,110,253,.28);transition:transform .18s,box-shadow .18s}
        .sidebar-install-btn:hover{transform:translateY(-1px);box-shadow:0 14px 30px rgba(13,110,253,.34);color:#fff}
        .sidebar-install-btn img{width:38px;height:38px;border-radius:10px;background:#fff;object-fit:contain;padding:3px;flex-shrink:0}
        .sidebar-install-btn strong{display:block;font-size:.86rem;line-height:1.1}
        .sidebar-install-btn span{display:block;font-size:.72rem;opacity:.86;line-height:1.2}
        .install-checklist{display:grid;gap:10px;margin-top:16px}
        .install-check{display:flex;gap:10px;align-items:flex-start;border:1px solid rgba(13,110,253,.12);border-radius:8px;padding:10px 12px;background:rgba(13,110,253,.04)}
        .install-check input{margin-top:4px}
        .install-check label{font-weight:700;margin:0}
        .install-check small{display:block;color:#6c757d;line-height:1.35}
        html.dark-theme .install-step{background:rgba(13,110,253,.08);border-color:#3a3d50}
        html.dark-theme .install-step i{background:#2a2d3e;box-shadow:none}
        html.dark-theme .install-step span{color:#9da1b2}
        html.dark-theme .install-note{background:#3a2d12;border-color:#6f520e;color:#ffd987}
        html.dark-theme .install-check{background:rgba(13,110,253,.08);border-color:#3a3d50}
        html.dark-theme .install-check small{color:#9da1b2}
        @media(display-mode:standalone){#sidebarInstallBox{display:none!important}}
        @media(max-width:576px){.install-hero{grid-template-columns:1fr;text-align:center}.install-icon{margin:auto}.install-actions>*{width:100%}}
    </style>
    {{-- Appliquer le thème AVANT le rendu pour éviter le flash --}}
    <script>
        (function(){
            if(localStorage.getItem('ateliko-theme')==='dark'){
                document.getElementById('htmlRoot').classList.add('dark-theme');
            }
        })();
    </script>
    @stack('styles')
</head>
<body>
@php
    $user = Auth::user();
    $subModalData = null;
    $subBlockedEmployee = null; // pour SECRETAIRE/TAILLEUR avec atelier bloqué
    $pendingPaymentsCount = 0;

    if ($user->isProprietaire()) {
        $abonnementModal = \App\Models\AbonnementAtelier::where('atelier_id', $user->atelier_id)
            ->with('plan')->latest()->first();
        $joursRestantsModal = $abonnementModal?->date_fin
            ? (int) now()->diffInDays($abonnementModal->date_fin, false)
            : null;
        $blockedModal = $joursRestantsModal !== null && ($joursRestantsModal < 0 || in_array($abonnementModal?->statut, ['EXPIRED','CANCELED','PAST_DUE']));
        $shouldShowModal = $joursRestantsModal !== null && $joursRestantsModal >= 0 && $joursRestantsModal <= 7 && !$blockedModal;

        if ($blockedModal || $shouldShowModal) {
            $plansModal = \App\Models\AbonnementPlan::where('actif', true)->get();
            // Vérifier s'il y a un paiement en attente
            $hasPendingPaymentModal = $abonnementModal && \App\Models\AbonnementPaiement::where('abonnement_id', $abonnementModal->id)->where('statut', 'PENDING')->exists();
            $dernierPaiementRejeté = $abonnementModal && \App\Models\AbonnementPaiement::where('abonnement_id', $abonnementModal->id)->where('statut', 'FAILED')->orderByDesc('created_at')->first();

            $subModalData = [
                'blocked'             => $blockedModal,
                'joursRestants'       => $joursRestantsModal,
                'hasPendingPayment'   => $hasPendingPaymentModal,
                'dernierPaiementRejeté' => $dernierPaiementRejeté,
                'message'             => $blockedModal
                    ? 'Votre abonnement est expiré. Veuillez renouveler pour continuer.'
                    : "Votre abonnement arrive à échéance dans {$joursRestantsModal} jour(s).",
                'plans'               => $plansModal,
                'modalKey'            => 'sub-modal-'.($user->atelier_id ?? 'none').'-'.($abonnementModal?->date_fin ? $abonnementModal->date_fin->format('Y-m-d') : 'none').'-'.($blockedModal ? 'blocked' : 'warn'),
            ];
        }
    } elseif ($user->isSecretaire() || $user->isTailleur()) {
        // Vérifier si l'atelier est bloqué → afficher modal "accès suspendu"
        if ($user->atelier_id) {
            $abonnementEmp = \App\Models\AbonnementAtelier::where('atelier_id', $user->atelier_id)->latest()->first();
            $empBlocked = !$abonnementEmp
                || in_array($abonnementEmp->statut, ['EXPIRED','CANCELED','PAST_DUE'])
                || ($abonnementEmp->date_fin && $abonnementEmp->date_fin->isPast());
            if ($empBlocked) {
                $subBlockedEmployee = [
                    'atelier_nom' => $user->atelier?->nom ?? 'votre atelier',
                    'statut'      => $abonnementEmp?->statut ?? 'INACTIF',
                ];
            }
        }
    } elseif ($user->isSuperAdmin()) {
        $pendingPaymentsCount = \App\Models\AbonnementPaiement::where('statut', 'PENDING')->count();
    }
@endphp
<div class="wrapper" id="appWrapper">
    <div class="sidebar-wrapper" data-simplebar="true">
        <div class="sidebar-header">
            <div class="branding d-flex align-items-center"><img src="{{ asset('assets/images/logo_ateliko.png') }}" class="logo-icon" alt="logo ATELIKO"><h6 class="logo-text mb-0 ms-2">ATELIKO</h6></div>
            <div class="toggle-icon ms-auto" id="sidebarClose" role="button" tabindex="0"><i class="bx bx-arrow-to-left"></i></div>
        </div>
        <ul class="metismenu" id="menu">
            <li class="{{ request()->routeIs('dashboard') ? 'mm-active' : '' }}"><a href="{{ route('dashboard') }}"><div class="parent-icon"><i class="bx bx-home-circle"></i></div><div class="menu-title">Tableau de bord</div></a></li>
            @if(!$user->isTailleur())
            <li class="{{ request()->routeIs('clients.create') ? 'mm-active' : '' }}"><a href="{{ route('clients.create') }}"><div class="parent-icon"><i class="bx bx-user-plus"></i></div><div class="menu-title">Ajouter un client</div></a></li>
            @endif
            <li class="{{ request()->routeIs('clients.index', 'clients.show', 'clients.edit') ? 'mm-active' : '' }}"><a href="{{ route('clients.index') }}"><div class="parent-icon"><i class="bx bx-user"></i></div><div class="menu-title">Liste des clients</div></a></li>
            @if(!$user->isTailleur())
            <li class="{{ request()->routeIs('modeles.*') ? 'mm-active' : '' }}"><a href="{{ route('modeles.index') }}"><div class="parent-icon"><i class="bx bx-photo-album"></i></div><div class="menu-title">Albums</div></a></li>
            @endif
            <li class="{{ request()->routeIs('affectations.*') ? 'mm-active' : '' }}"><a href="{{ route('affectations.index') }}"><div class="parent-icon"><i class="bx bx-user-check"></i></div><div class="menu-title">Affectations</div></a></li>
            @if(!$user->isTailleur())
            <li class="{{ request()->routeIs('rendezvous.*') ? 'mm-active' : '' }}"><a href="{{ route('rendezvous.index') }}"><div class="parent-icon"><i class="bx bx-calendar"></i></div><div class="menu-title">Rendez-vous</div></a></li>
            <li class="{{ request()->routeIs('paiements.*') ? 'mm-active' : '' }}"><a href="{{ route('paiements.index') }}"><div class="parent-icon"><i class="bx bx-wallet"></i></div><div class="menu-title">Paiements</div></a></li>
            @endif
            <li class="{{ request()->routeIs('documentation') ? 'mm-active' : '' }}"><a href="{{ route('documentation') }}"><div class="parent-icon"><i class="bx bx-book"></i></div><div class="menu-title">Documentation</div></a></li>
            @if($user->isProprietaire())
            <li class="{{ request()->routeIs('abonnement.*') ? 'mm-active' : '' }}">
                <a href="{{ route('abonnement.index') }}">
                    <div class="parent-icon"><i class="bx bx-badge-check"></i></div>
                    <div class="menu-title">Abonnement</div>
                </a>
            </li>
            @endif
            @if($user->isSuperAdmin())
            <li class="{{ request()->routeIs('admin.subscriptions.*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.subscriptions.index') }}">
                    <div class="parent-icon"><i class="bx bx-credit-card"></i></div>
                    <div class="menu-title">Abonnements</div>
                </a>
            </li>
            @endif
            @if($user->isSuperAdmin() || $user->isProprietaire())
            <li class="{{ request()->routeIs('parametres.*', 'utilisateurs.*') ? 'mm-active' : '' }}"><a href="{{ route('parametres.index') }}"><div class="parent-icon"><i class="bx bx-cog"></i></div><div class="menu-title">Paramètres</div></a></li>
            @endif
        </ul>
        <div class="sidebar-install-box" id="sidebarInstallBox">
            <button type="button" class="sidebar-install-btn" id="sidebarInstallBtn" data-bs-toggle="modal" data-bs-target="#modalInstallApp">
                <img src="{{ asset('assets/images/ateliko-icon-192.png') }}" alt="">
                <span><strong>Installer ATELIKO</strong><span>Raccourci et barre des tâches</span></span>
            </button>
        </div>
    </div>
    <header><div class="topbar d-flex align-items-center"><nav class="navbar navbar-expand">
        <div class="mobile-toggle-menu" id="sidebarToggle" role="button" tabindex="0"><i class="bx bx-menu"></i></div>
        <div class="top-menu ms-auto">
            <ul class="navbar-nav align-items-center">
                {{-- Toggle Dark / Light --}}
                <li class="nav-item">
                    <button id="btnTheme" title="Changer le thème" aria-label="Basculer thème sombre/clair">
                        <i class="bx bx-moon icon-dark"></i>
                        <i class="bx bx-sun icon-light"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="user-box dropdown">
            <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown">
                @if($user->photo_path)<img src="{{ asset('storage/' . $user->photo_path) }}" class="user-img" alt="Photo de profil">@else<img src="{{ asset('assets/images/default-user.jpg') }}" class="user-img" alt="Photo de profil">@endif
                <div class="user-info ps-3"><p class="user-name mb-0">{{ $user->prenom }} {{ $user->nom }}</p><p class="designattion mb-0">{{ $user->role }}</p></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bx bx-user"></i><span>Mon Profil</span></a></li>
                <li><div class="dropdown-divider mb-0"></div></li>
                <li><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="dropdown-item"><i class="bx bx-log-out-circle"></i><span>Déconnexion</span></button></form></li>
            </ul>
        </div>
    </nav></div></header>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="page-wrapper"><div class="page-content">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h5 class="mb-0">@yield('page-title', 'Tableau de bord')</h5>@hasSection('page-subtitle')<small class="text-muted">@yield('page-subtitle')</small>@endif</div><span class="badge bg-primary">{{ $user->atelier?->nom ?? 'ATELIKO' }}</span></div>
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bx bx-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bx bx-error-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if($errors->any())<div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        {{-- Alerte abonnement PROPRIETAIRE --}}
        @if($subModalData)
        <div class="alert {{ $subModalData['blocked'] ? 'alert-danger' : 'alert-warning' }} d-flex justify-content-between align-items-center mb-3" role="alert">
            <div><strong>{{ $subModalData['blocked'] ? 'Abonnement expiré.' : 'Alerte abonnement.' }}</strong> {{ $subModalData['message'] }}</div>
            <a href="{{ route('abonnement.index') }}" class="btn btn-sm btn-outline-dark ms-3 flex-shrink-0">Gérer l'abonnement</a>
        </div>
        @endif
        {{-- Alerte paiements en attente SUPERADMIN --}}
        @if($pendingPaymentsCount > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3" role="alert">
            <div><strong>Paiements en attente.</strong> {{ $pendingPaymentsCount }} paiement(s) d'abonnement nécessitent votre validation.</div>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-dark ms-3 flex-shrink-0">Valider</a>
        </div>
        @endif
        @yield('content')
    </div></div>
    <footer class="page-footer"><p class="mb-0">© {{ date('Y') }} ATELIKO</p></footer>
</div>

{{-- ===== MODAL ABONNEMENT PROPRIETAIRE ===== --}}
@if($subModalData)
<div class="modal fade" id="modalAbonnement"
     data-bs-backdrop="{{ $subModalData['blocked'] ? 'static' : 'true' }}"
     data-bs-keyboard="{{ $subModalData['blocked'] ? 'false' : 'true' }}"
     tabindex="-1" aria-labelledby="modalAbonnementLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header {{ $subModalData['blocked'] ? 'bg-danger text-white' : 'bg-warning' }}">
                <h5 class="modal-title" id="modalAbonnementLabel">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ $subModalData['blocked'] ? 'Abonnement expiré' : 'Abonnement bientôt expiré' }}
                </h5>
                @if(!$subModalData['blocked'])
                <button type="button" class="btn-close {{ $subModalData['blocked'] ? 'btn-close-white' : '' }}" data-bs-dismiss="modal" aria-label="Fermer"></button>
                @endif
            </div>
            <div class="modal-body">

                @if($subModalData['blocked'] && ($subModalData['hasPendingPayment'] ?? false))
                {{-- Paiement soumis, en attente de validation --}}
                <div class="text-center py-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 p-4 mb-3" style="width:80px;height:80px">
                        <i class="bx bx-time-five text-warning" style="font-size:2.5rem"></i>
                    </span>
                    <h5 class="fw-bold mb-2">Paiement en cours de validation</h5>
                    <p class="text-muted mb-4">Votre preuve de paiement a été reçue.<br>Un administrateur va la vérifier sous peu.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('abonnement.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-history me-1"></i>Voir l'historique
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">@csrf
                            <button class="btn btn-outline-secondary btn-sm"><i class="bx bx-log-out me-1"></i>Déconnexion</button>
                        </form>
                    </div>
                </div>

                @else
                {{-- Formulaire de paiement (abonnement expiré ou presque, ou paiement rejeté) --}}
                @if(isset($subModalData['dernierPaiementRejeté']) && $subModalData['dernierPaiementRejeté'])
                <div class="alert alert-danger py-2 mb-3">
                    <i class="bx bx-x-circle me-1"></i><strong>Paiement rejeté.</strong>
                    @if($subModalData['dernierPaiementRejeté']->review_note)
                        Motif : {{ $subModalData['dernierPaiementRejeté']->review_note }}
                    @endif
                    Veuillez soumettre un nouveau paiement.
                </div>
                @else
                <div class="alert {{ $subModalData['blocked'] ? 'alert-danger' : 'alert-warning' }} mb-3">
                    <i class="bx bx-info-circle me-2"></i>{{ $subModalData['message'] }}
                </div>
                @endif
                <p class="text-muted small mb-3">Envoyez votre preuve de paiement pour renouveler votre abonnement. Nos opérateurs la valideront rapidement.</p>
                <form id="paymentFormModal" action="{{ route('abonnement.paiement') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Plan <span class="text-danger">*</span></label>
                            <select name="plan_code" class="form-select" required>
                                <option value="">-- Choisir un plan --</option>
                                @foreach($subModalData['plans'] as $plan)
                                <option value="{{ $plan->code }}">{{ $plan->libelle }} — {{ number_format($plan->prix, 0, ',', ' ') }} FCFA/{{ $plan->duree_mois }}mois</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Mode de paiement <span class="text-danger">*</span></label>
                            <select name="mode_paiement" class="form-select" id="modalModePaiement" required>
                                <option value="">-- Choisir --</option>
                                <option value="ORANGE_MONEY">Orange Money</option>
                                <option value="WAVE">Wave</option>
                                <option value="MOBICASH">MobiCash</option>
                            </select>
                        </div>
                        <div class="col-12" id="modalNumeroDiv" style="display:none">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bx bx-phone me-2"></i>Numéro à utiliser : <strong id="modalNumeroAffiche"></strong>
                            </div>
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
                            <div class="camera-widget" id="camWidgetModal">
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
                                        <button type="button" class="btn btn-outline-warning btn-sm cam-flip" title="Changer de caméra (avant/arrière)">
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
                {{-- Actions séparées du formulaire pour éviter le nesting --}}
                <div class="mt-4 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bx bx-log-out me-1"></i>Déconnexion</button>
                    </form>
                    <div class="d-flex gap-2">
                        @if(!$subModalData['blocked'])
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Plus tard</button>
                        @endif
                        <button type="submit" form="paymentFormModal" class="btn btn-primary btn-sm"><i class="bx bx-upload me-1"></i>Envoyer la preuve</button>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endif

{{-- ===== MODAL ACCÈS SUSPENDU (SECRETAIRE / TAILLEUR) ===== --}}
@if($subBlockedEmployee)
<div class="modal fade" id="modalBlockedEmployee"
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
                <p class="text-muted">L'abonnement de <strong>{{ $subBlockedEmployee['atelier_nom'] }}</strong> est expiré ou suspendu.<br>Contactez le propriétaire de votre atelier pour rétablir l'accès.</p>
                <span class="badge bg-danger">{{ $subBlockedEmployee['statut'] }}</span>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button class="btn btn-outline-danger"><i class="bx bx-log-out me-1"></i>Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ===== MODAL PAIEMENTS EN ATTENTE SUPERADMIN ===== --}}
@if($pendingPaymentsCount > 0)
<div class="modal fade" id="modalPendingPayments" tabindex="-1" aria-labelledby="modalPendingLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalPendingLabel"><i class="bx bx-bell me-2"></i>Paiements en attente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3" style="font-size:3.5rem;color:#ffc107"><i class="bx bx-credit-card"></i></div>
                <h4 class="mb-2">{{ $pendingPaymentsCount }} paiement(s) en attente</h4>
                <p class="text-muted">Des paiements d'abonnement nécessitent votre validation.</p>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-warning btn-lg mt-2">
                    <i class="bx bx-check-circle me-2"></i>Valider maintenant
                </a>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Plus tard</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ===== MODAL PREMIÈRE CONNEXION — INSTALLATION APP ===== --}}
<div class="modal fade" id="modalInstallApp" tabindex="-1" aria-labelledby="modalInstallAppLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="install-hero w-100">
                    <div class="install-icon"><i class="bx bx-desktop"></i></div>
                    <div>
                        <h5 class="modal-title mb-1" id="modalInstallAppLabel">Installer ATELIKO sur cet ordinateur</h5>
                        <p class="text-muted mb-0">Un accès direct aide l'équipe à ouvrir l'application sans chercher le navigateur.</p>
                    </div>
                </div>
            </div>
            <div class="modal-body pt-3">
                <div class="install-note mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    Le navigateur peut installer l'application. L'épinglage à la barre des tâches et le démarrage automatique restent des actions Windows à confirmer par l'utilisateur.
                </div>
                <div id="installManualMsg" class="alert alert-warning py-2 px-3 mb-3 d-none" style="border-radius:10px;font-size:.86rem">
                    <i class="bx bx-info-circle me-1"></i>
                    Si le bouton natif ne s'affiche pas, utilisez l'icône d'installation dans la barre d'adresse du navigateur, puis suivez les cases ci-dessous.
                </div>
                <ul class="install-steps">
                    <li class="install-step">
                        <i class="bx bx-download"></i>
                        <div><strong>Installer l'application</strong><span>Cliquez sur “Installer ATELIKO” quand le bouton est disponible.</span></div>
                    </li>
                    <li class="install-step">
                        <i class="bx bx-link-alt"></i>
                        <div><strong>Créer un raccourci</strong><span>Après installation, ATELIKO apparaît dans le menu Démarrer et peut être placé sur le bureau.</span></div>
                    </li>
                    <li class="install-step">
                        <i class="bx bx-pin"></i>
                        <div><strong>Épingler pour les utilisateurs</strong><span>Ouvrez ATELIKO, puis clic droit sur son icône dans la barre des tâches et choisissez “Épingler”.</span></div>
                    </li>
                    <li class="install-step">
                        <i class="bx bx-power-off"></i>
                        <div><strong>Démarrer à la connexion</strong><span>Dans Windows : Paramètres &gt; Applications &gt; Démarrage, activez ATELIKO si l'option est proposée.</span></div>
                    </li>
                </ul>
                <div class="install-checklist">
                    <div class="install-check">
                        <input class="form-check-input install-task" type="checkbox" id="taskInstall">
                        <div><label for="taskInstall">Application installée</label><small>Utilisez le bouton Installer ATELIKO ou l'icône d'installation du navigateur.</small></div>
                    </div>
                    <div class="install-check">
                        <input class="form-check-input install-task" type="checkbox" id="taskShortcut">
                        <div><label for="taskShortcut">Raccourci créé</label><small>Vérifiez le bureau ou le menu Démarrer après installation.</small></div>
                    </div>
                    <div class="install-check">
                        <input class="form-check-input install-task" type="checkbox" id="taskPinned">
                        <div><label for="taskPinned">Épinglé à la barre des tâches</label><small>Ouvrez ATELIKO, clic droit sur son icône, puis Épingler.</small></div>
                    </div>
                    <div class="install-check">
                        <input class="form-check-input install-task" type="checkbox" id="taskStartup">
                        <div><label for="taskStartup">Démarrage automatique vérifié</label><small>Windows : Paramètres, Applications, Démarrage, puis activez ATELIKO si disponible.</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 install-actions">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="installLaterBtn">Plus tard</button>
                <button type="button" class="btn btn-light border" id="installDoneBtn"><i class="bx bx-check me-1"></i>Déjà fait</button>
                <button type="button" class="btn btn-primary" id="installAppBtn"><i class="bx bx-download me-1"></i>Installer ATELIKO</button>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
(()=>{
    // Sidebar toggle
    const wrapper = document.getElementById('appWrapper');
    const toggle = () => wrapper.classList.toggle('toggled');
    document.getElementById('sidebarToggle')?.addEventListener('click', toggle);
    document.getElementById('sidebarClose')?.addEventListener('click', toggle);
    document.getElementById('sidebarOverlay')?.addEventListener('click', () => wrapper.classList.remove('toggled'));

    // Auto-fermeture alertes
    setTimeout(() => document.querySelectorAll('.alert.show').forEach(a => bootstrap.Alert.getOrCreateInstance(a).close()), 5000);

    // Dark / Light mode toggle
    const html = document.getElementById('htmlRoot');
    const btn  = document.getElementById('btnTheme');
    const DARK = 'dark-theme';

    btn?.addEventListener('click', function () {
        const isDark = html.classList.toggle(DARK);
        localStorage.setItem('ateliko-theme', isDark ? 'dark' : 'light');
    });

    // Enregistrement PWA : nécessaire pour que le navigateur propose l'installation.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(err => console.warn('[ATELIKO] Service worker:', err));
        });
    }
})();
</script>
<script>
(()=>{
    const installModalEl = document.getElementById('modalInstallApp');
    const installBtn = document.getElementById('installAppBtn');
    const sidebarInstallBtn = document.getElementById('sidebarInstallBtn');
    const sidebarInstallBox = document.getElementById('sidebarInstallBox');
    const doneBtn = document.getElementById('installDoneBtn');
    const laterBtn = document.getElementById('installLaterBtn');
    const manualMsg = document.getElementById('installManualMsg');
    const userInstallKey = 'ateliko-install-assistant-v4-{{ $user->id }}';
    let deferredInstallPrompt = null;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const hasBlockingModal = !!document.getElementById('modalBlockedEmployee') || @json((bool) ($subModalData['blocked'] ?? false));
    const hideSidebarInstallButton = () => {
        if (sidebarInstallBox) sidebarInstallBox.style.display = 'none';
    };
    const markInstalled = () => {
        localStorage.setItem(userInstallKey, 'installed');
        hideSidebarInstallButton();
    };

    if (sidebarInstallBox && (isStandalone || localStorage.getItem(userInstallKey) === 'installed')) {
        hideSidebarInstallButton();
    }

    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        deferredInstallPrompt = event;
        manualMsg?.classList.add('d-none');
    });

    window.addEventListener('appinstalled', () => {
        markInstalled();
        if (installModalEl) {
            bootstrap.Modal.getInstance(installModalEl)?.hide();
        }
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) {
            manualMsg?.classList.remove('d-none');
            installBtn.innerHTML = "<i class=\"bx bx-check me-1\"></i>J'ai compris";
            return;
        }
        deferredInstallPrompt.prompt();
        const choice = await deferredInstallPrompt.userChoice;
        if (choice.outcome === 'accepted') markInstalled();
        deferredInstallPrompt = null;
    });

    sidebarInstallBtn?.addEventListener('click', () => {
        if (installModalEl && window.bootstrap?.Modal) {
            bootstrap.Modal.getOrCreateInstance(installModalEl).show();
        }
    });

    doneBtn?.addEventListener('click', () => {
        markInstalled();
        bootstrap.Modal.getOrCreateInstance(installModalEl).hide();
    });

    document.querySelectorAll('.install-task').forEach(input => {
        const key = userInstallKey + '-' + input.id;
        input.checked = localStorage.getItem(key) === '1';
        input.addEventListener('change', () => localStorage.setItem(key, input.checked ? '1' : '0'));
    });

    laterBtn?.addEventListener('click', () => {});

    function showInstallAssistant() {
        if (!installModalEl || isStandalone || hasBlockingModal) return;
        if (localStorage.getItem(userInstallKey) === 'done') return;
        if (document.querySelector('.modal.show')) return;
        bootstrap.Modal.getOrCreateInstance(installModalEl).show();
    }

    window.addEventListener('load', () => {
        // Le bouton permanent dans la sidebar reste la source fiable d'ouverture.
        document.querySelectorAll('.modal').forEach(modal => {
            if (modal !== installModalEl) {
                modal.addEventListener('hidden.bs.modal', () => setTimeout(showInstallAssistant, 250), { once: true });
            }
        });
    });
})();
</script>
{{-- ===== WIDGET CAMÉRA — JS PARTAGÉ ===== --}}
<script>
(function(){
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
        var stream    = null;

        if (!fileInput || !video) return;

        var facingMode = 'environment';
        var flipBtn = w.querySelector('.cam-flip');

        function stopStream() {
            if (stream) { stream.getTracks().forEach(function(t){ t.stop(); }); stream = null; }
        }

        function startStream() {
            msg.textContent = 'Accès à la caméra…';
            msg.className = 'cam-msg small text-muted';
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 } }
            }).then(function(s) {
                stream = s;
                video.srcObject = s;
                video.style.display = 'block';
                snap.style.display  = 'none';
                shootBtn.style.display = '';
                redoBtn.style.display  = 'none';
                msg.textContent = '';
            }).catch(function(err) {
                msg.textContent = '⚠ Caméra inaccessible : ' + (err.message || err.name);
                msg.className = 'cam-msg small text-danger';
            });
        }

        if (flipBtn) {
            flipBtn.addEventListener('click', function() {
                facingMode = facingMode === 'environment' ? 'user' : 'environment';
                stopStream();
                startStream();
            });
        }

        function resetWidget() {
            stopStream();
            if (snap) snap.style.display = 'none';
            if (video) video.style.display = 'block';
            if (shootBtn) shootBtn.style.display = '';
            if (redoBtn)  redoBtn.style.display  = 'none';
            if (msg) msg.textContent = '';
            try { fileInput.files = new DataTransfer().files; } catch(e){}
        }

        w._stopCamera = stopStream;
        w._resetWidget = resetWidget;

        btnCam.addEventListener('click', function() {
            fileZone.style.display = 'none';
            liveZone.style.display = 'block';
            btnCam.classList.add('active'); btnCam.classList.remove('btn-outline-primary'); btnCam.classList.add('btn-primary');
            btnFile.classList.remove('active');
            fileInput.required = false;
            startStream();
        });

        btnFile.addEventListener('click', function() {
            liveZone.style.display = 'none';
            fileZone.style.display = 'block';
            btnFile.classList.add('active');
            btnCam.classList.remove('active','btn-primary'); btnCam.classList.add('btn-outline-primary');
            fileInput.required = true;
            stopStream();
        });

        shootBtn.addEventListener('click', function() {
            canvas.width  = video.videoWidth  || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0);
            if (shutter) { shutter.classList.add('flash'); setTimeout(function(){ shutter.classList.remove('flash'); }, 200); }
            canvas.toBlob(function(blob) {
                var file = new File([blob], 'preuve_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                snap.src = URL.createObjectURL(blob);
                snap.style.display  = 'block';
                video.style.display = 'none';
                shootBtn.style.display = 'none';
                redoBtn.style.display  = '';
                msg.textContent = '✓ Photo capturée avec succès';
                msg.className = 'cam-msg small text-success';
                try { var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; } catch(e){}
                stopStream();
            }, 'image/jpeg', 0.92);
        });

        redoBtn.addEventListener('click', function() {
            snap.style.display = 'none';
            shootBtn.style.display = '';
            redoBtn.style.display  = 'none';
            msg.textContent = '';
            try { fileInput.files = new DataTransfer().files; } catch(e){}
            startStream();
        });
    }

    // Init tous les widgets présents dans la page
    document.querySelectorAll('.camera-widget').forEach(initCameraWidget);

    // Arrêt caméra quand le modal abonnement se ferme
    var modalEl = document.getElementById('modalAbonnement');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            var w = modalEl.querySelector('.camera-widget');
            if (w && w._stopCamera) { w._stopCamera(); }
            if (w && w._resetWidget) { w._resetWidget(); }
        });
    }
})();
</script>

@if($subModalData)
<script>
(function(){
    var isBlocked = @json($subModalData['blocked']);
    var modalKey  = @json($subModalData['modalKey']);

    // Si bloqué : afficher à CHAQUE chargement de page (pas de sessionStorage)
    // Si juste avertissement : afficher une seule fois par session
    var shouldShow = isBlocked
        ? true
        : (sessionStorage.getItem('__SUB_MODAL_KEY__') !== modalKey);

    if (shouldShow) {
        if (!isBlocked) { sessionStorage.setItem('__SUB_MODAL_KEY__', modalKey); }
        var el = document.getElementById('modalAbonnement');
        if (el) { new bootstrap.Modal(el).show(); }
    }

    var nums = { ORANGE_MONEY: '74 74 56 69', WAVE: '74 74 56 69', MOBICASH: '67 20 57 36' };
    var sel = document.getElementById('modalModePaiement');
    if (sel) {
        sel.addEventListener('change', function() {
            var div = document.getElementById('modalNumeroDiv');
            var num = document.getElementById('modalNumeroAffiche');
            if (this.value && nums[this.value]) {
                num.textContent = nums[this.value];
                div.style.display = 'block';
            } else {
                div.style.display = 'none';
            }
        });
    }
})();
</script>
@endif

@if($subBlockedEmployee)
<script>
(function(){
    var el = document.getElementById('modalBlockedEmployee');
    if (el) { new bootstrap.Modal(el).show(); }
})();
</script>
@endif

@if($pendingPaymentsCount > 0)
<script>
(function(){
    var adminKey = 'admin-pending-modal-{{ date("Y-m-d") }}';
    var alreadyShown = sessionStorage.getItem('__ADMIN_SUB_MODAL_KEY__') === adminKey;
    if (!alreadyShown) {
        sessionStorage.setItem('__ADMIN_SUB_MODAL_KEY__', adminKey);
        var el = document.getElementById('modalPendingPayments');
        if (el) { new bootstrap.Modal(el).show(); }
    }
})();
</script>
@endif

<script src="{{ asset('js/qrcode-generator.js') }}"></script>
<script src="{{ asset('js/html2canvas.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ===== ATELIKO — SweetAlert suit automatiquement le mode nuit =====
(function() {
    if (!window.Swal || window.Swal.__atelikoDarkPatched) return;

    var originalFire = window.Swal.fire.bind(window.Swal);

    function isDarkMode() {
        return document.documentElement.classList.contains('dark-theme')
            || document.body.classList.contains('dark-theme');
    }

    function darkOptions(opts) {
        if (!isDarkMode()) return opts;
        opts = Object.assign({}, opts || {});
        opts.background = opts.background || '#171717';
        opts.color = opts.color || '#e4e5e6';
        opts.customClass = Object.assign({}, opts.customClass || {}, {
            popup: ((opts.customClass && opts.customClass.popup) ? opts.customClass.popup + ' ' : '') + 'ateliko-swal-dark',
        });
        return opts;
    }

    window.Swal.fire = function() {
        var args = Array.prototype.slice.call(arguments);

        if (args.length === 1 && typeof args[0] === 'object') {
            return originalFire(darkOptions(args[0]));
        }

        return originalFire(darkOptions({
            title: args[0],
            text: args[1],
            icon: args[2],
        }));
    };

    window.Swal.__atelikoDarkPatched = true;
})();

// ===== ATELIKO — Système SweetAlert global =====
(function() {

    // --- Thème de base ---
    var BASE = {
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        reverseButtons: true,
        focusCancel: true,
        customClass: { confirmButton: 'btn', cancelButton: 'btn btn-secondary ms-2' },
        buttonsStyling: true,
    };

    // --- Toast succès / erreur / info ---
    window.swalSuccess = function(msg) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg,
            showConfirmButton: false, timer: 3500, timerProgressBar: true });
    };
    window.swalError = function(msg) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg,
            showConfirmButton: false, timer: 4000, timerProgressBar: true });
    };

    // --- Confirmation générique ---
    // options: { title, text, icon, confirmColor, confirmText, onConfirm }
    window.swalAsk = function(options) {
        return Swal.fire(Object.assign({}, BASE, {
            title: options.title || 'Confirmer ?',
            text:  options.text  || '',
            icon:  options.icon  || 'warning',
            confirmButtonColor: options.confirmColor || '#0d6efd',
            confirmButtonText:  options.confirmText  || 'Confirmer',
        })).then(function(r) {
            if (r.isConfirmed && options.onConfirm) options.onConfirm();
        });
    };

    // --- Intercepteur global : forms avec data-confirm ---
    // Usage : <form data-confirm="Supprimer ?" data-confirm-text="..." data-confirm-color="#dc3545" data-confirm-btn="Supprimer">
    document.addEventListener('submit', function(e) {
        var form = e.target;
        var msg = form.getAttribute('data-confirm');
        if (!msg) return;
        e.preventDefault();
        Swal.fire(Object.assign({}, BASE, {
            title: msg,
            text:  form.getAttribute('data-confirm-text') || '',
            icon:  form.getAttribute('data-confirm-icon') || 'warning',
            confirmButtonColor: form.getAttribute('data-confirm-color') || '#dc3545',
            confirmButtonText:  form.getAttribute('data-confirm-btn')   || 'Confirmer',
        })).then(function(r) {
            if (r.isConfirmed) { form.removeAttribute('data-confirm'); form.submit(); }
        });
    }, true); // capture phase pour attraper avant onsubmit

    // --- Intercepteur global : boutons avec data-swal-confirm ---
    // Usage : <button type="button" data-swal-confirm="..." data-swal-color="..." data-swal-btn="..." data-swal-text="...">
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-swal-confirm]');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        var form = btn.form || btn.closest('form');
        Swal.fire(Object.assign({}, BASE, {
            title: btn.getAttribute('data-swal-confirm'),
            text:  btn.getAttribute('data-swal-text')  || '',
            icon:  btn.getAttribute('data-swal-icon')  || 'warning',
            confirmButtonColor: btn.getAttribute('data-swal-color') || '#dc3545',
            confirmButtonText:  btn.getAttribute('data-swal-btn')   || 'Confirmer',
        })).then(function(r) {
            if (r.isConfirmed && form) form.submit();
        });
    });

})();

// ===== ATELIKO — Ticket thermique + WhatsApp =====
(function () {

    var WA_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>';

    function cleanPhone(v) {
        var d = String(v || '').replace(/[^\d+]/g, '');
        if (!d) return '';
        if (d.indexOf('00') === 0) d = d.slice(2);
        if (d.charAt(0) === '+') d = d.slice(1);
        if (d.indexOf('00223') === 0) d = d.slice(2);
        if (d.indexOf('2230') === 0 && d.length === 12) d = '223' + d.slice(4);
        if (d.charAt(0) === '0' && d.length === 9) d = d.slice(1);
        if (d.length === 8) return '223' + d;
        return d;
    }

    function fmtM(v) { return Number(v || 0).toLocaleString('fr-FR') + ' FCFA'; }

    function _row(label, value, extra) {
        return '<div style="display:table;width:100%;margin-bottom:4px' + (extra || '') + '">'
             + '<div style="display:table-cell;font-size:11px;padding-right:6px">' + label + '</div>'
             + '<div style="display:table-cell;text-align:right;font-size:11px;font-weight:700">' + value + '</div>'
             + '</div>';
    }

    function buildQrText(r) {
        return [
            'TICKET ' + (r.typeTicket || 'ATELIKO'),
            'Atelier: '      + (r.atelierNom   || ''),
            'Reference: '    + (r.reference    || ''),
            'Type: '         + (r.statut       || ''),
            'Beneficiaire: ' + (r.beneficiaire || ''),
            'Montant: '      + Math.round(r.montant || 0) + ' FCFA',
            'Date: '         + (r.dateFormatted || ''),
            'Contact: '      + (r.contact      || ''),
        ].join('\n');
    }

    function buildWaText(r) {
        var nom = r.atelierNom || 'ATELIKO';
        var isRDV = r.typeTicket === 'RDV';
        if (r.typeTicket === 'RDV_READY') {
            return [
                '*' + nom + '*',
                'Bonjour ' + (r.beneficiaire || '') + ',',
                r.readyMessage || 'Votre commande est prête. Vous pouvez passer la récupérer.',
                r.nomModele ? 'Habit : ' + r.nomModele : null,
                r.dateRdv ? 'Rendez-vous : ' + r.dateRdv : null,
                r.resteAPayer > 0 ? 'Reste à payer : ' + fmtM(r.resteAPayer) : null,
                '',
                'L’image du reçu est jointe à ce message.',
                r.messageMarketing || ('Merci pour votre confiance chez ' + nom + ' !')
            ].filter(function (l) { return l !== null && l !== undefined; }).join('\n');
        }
        var lines = isRDV ? [
            '*' + nom + '*',
            'Bonjour ' + (r.beneficiaire || '') + ',',
            'Votre rendez-vous est confirmé.',
            r.dateRdv          ? 'Date RDV : ' + r.dateRdv : null,
            r.type_rendezvous  ? 'Type : '    + r.type_rendezvous : null,
            '',
            r.messageMarketing || ('Nous vous attendons chez ' + nom + '. Merci !')
        ] : [
            '*' + nom + '*',
            r.statut || 'Reçu client',
            'Référence : '   + (r.reference    || ''),
            'Date : '        + (r.dateFormatted || ''),
            'Client : '      + (r.beneficiaire  || ''),
            r.nomModele ? 'Modèle : ' + r.nomModele : null,
            r.montant   ? 'Encaissé : ' + fmtM(r.montant) : null,
            'Total dû : '     + fmtM(r.totalDu),
            'Payé : '         + fmtM(r.avancePaye),
            'Reste à payer : '+ fmtM(r.resteAPayer),
            '',
            r.messageMarketing || ('Merci pour votre confiance chez ' + nom + ' !')
        ];
        return lines.filter(function (l) { return l !== null && l !== undefined; }).join('\n');
    }

    function buildTicketHtml(r, qrDataUrl) {
        var isRDV  = r.typeTicket === 'RDV' || r.typeTicket === 'RDV_READY';
        var atelier = r.atelierNom || 'ATELIKO';
        var DIV = '<div style="text-align:center;color:#777;margin:8px 0;font-size:11px">--------------------------------</div>';
        var s = '<div style="width:280px;background:#fff;font-family:Helvetica,Arial,sans-serif;padding:16px;color:#111;line-height:1.6;border-radius:8px;border:1px solid #d1d5db">';

        // Entête
        s += '<div style="text-align:center;font-size:15px;font-weight:900;text-transform:uppercase;color:#141414;margin-bottom:2px">' + atelier + '</div>';
        if (r.atelierAdresse)   s += '<div style="text-align:center;font-size:11px;color:#555;margin-top:2px">' + r.atelierAdresse + '</div>';
        if (r.atelierTelephone) s += '<div style="text-align:center;font-size:11px;color:#555;margin-top:2px">' + r.atelierTelephone + '</div>';

        // Badge statut — centré, largeur 54%, fond sombre
        s += '<div style="display:flex;justify-content:center;margin:10px 0">'
           + '<div style="width:54%;background:#232323;color:#fff;padding:5px 0;font-size:8px;font-weight:900;letter-spacing:1px;text-transform:uppercase;text-align:center">'
           + (r.statut || 'REÇU').toUpperCase() + '</div></div>';

        s += DIV;
        s += '<div style="font-size:11px;font-weight:900;color:#111;margin-bottom:4px;margin-top:2px">DÉTAILS DU TICKET</div>';

        s += _row('Référence',    r.reference    || '');
        s += _row('Date',         r.dateFormatted|| '');
        s += _row('Bénéficiaire', r.beneficiaire || '');
        s += _row('Contact',      r.contact      || '');
        if (!isRDV)      s += _row('Règlement', r.moyenPaiement || 'ESPECES');
        if (r.nomModele) s += _row('Modèle',    r.nomModele);
        if (r.nombreModeles) s += _row('Nb modèles', r.nombreModeles);

        if (isRDV) {
            s += '<div style="border:1.2px solid #2d2d2d;padding:10px 6px;margin:8px 0;text-align:center;background:#f5f5f5">';
            s += '<div style="font-size:10px;font-weight:900;text-transform:uppercase;color:#5f5f5f;letter-spacing:.5px;margin-bottom:4px">'
              + (r.resteAPayer > 0 ? 'Solde à régler avant récupération' : 'Habit prêt à récupérer') + '</div>';
            if (r.dateRdv)         s += '<div style="font-size:11px">Rendez-vous : ' + r.dateRdv + '</div>';
            if (r.resteAPayer > 0) s += '<div style="font-size:11px;font-weight:900;color:#dc3545">Solde : ' + fmtM(r.resteAPayer) + '</div>';
            if (r.type_rendezvous) s += '<div style="font-size:11px">Type : '     + r.type_rendezvous + '</div>';
            s += '</div>';
        } else {
            s += DIV;
            s += _row('Total dû',     fmtM(r.totalDu));
            s += _row('Avance payée', '<span style="color:#198754">' + fmtM(r.avancePaye) + '</span>');
            s += _row('Reste à payer','<span style="color:' + (r.resteAPayer > 0 ? '#dc3545' : '#198754') + ';font-weight:900">' + fmtM(r.resteAPayer) + '</span>', ';font-weight:700');
            s += '<div style="border:1.2px solid #2d2d2d;padding:10px 6px;text-align:center;margin:8px 0;background:#f5f5f5">';
            s += '<div style="font-size:10px;font-weight:900;text-transform:uppercase;color:#5f5f5f;letter-spacing:.5px;margin-bottom:3px">' + (r.typeTicket === 'COMMANDE' ? 'AVANCE REÇUE' : 'MONTANT ENCAISSÉ') + '</div>';
            s += '<div style="font-size:20px;font-weight:900;color:#111;margin-top:3px">' + fmtM(r.montant) + '</div>';
            s += '</div>';
        }

        // Vérification + QR dans un bloc bordé fond clair
        s += DIV;
        s += '<div style="font-size:11px;font-weight:900;color:#111;margin-bottom:4px">VÉRIFICATION</div>';
        s += '<div style="border:1px solid #b4b4b4;background:#fafafa;padding:8px 6px;text-align:center;margin-bottom:8px">';
        if (qrDataUrl) {
            s += '<div style="display:flex;justify-content:center;margin-bottom:4px"><img src="' + qrDataUrl + '" width="90" height="90" style="image-rendering:pixelated;display:block"></div>';
        } else {
            s += '<div style="width:90px;height:90px;background:#eee;margin:0 auto 4px;display:flex;align-items:center;justify-content:center"><span style="font-size:8px;color:#999">QR</span></div>';
        }
        s += '<div style="font-size:11px;color:#555">Scannez pour vérifier le reçu</div>';
        s += '</div>';

        s += DIV;
        s += '<div style="text-align:center;font-weight:900;color:#111;font-size:12px">Merci pour votre confiance.</div>';
        if (r.messageMarketing) s += '<div style="text-align:center;font-size:11px;color:#696969;margin-top:2px">' + r.messageMarketing + '</div>';
        s += '<div style="text-align:center;font-size:11px;color:#696969;margin-top:2px">' + atelier + '</div>';
        s += '</div>';
        return s;
    }

    // ── Générer QR code comme data URL — synchrone, même lib que React Native (qrcode-generator)
    function _genQrDataUrl(text) {
        if (!window.qrcode) return null;
        try {
            var qr = qrcode(0, 'M');  // type 0 = auto, M = correction moyenne
            qr.addData(String(text || ''));
            qr.make();
            return qr.createDataURL(3, 4); // cellSize=3px, margin=4px → GIF data URL
        } catch (e) {
            console.warn('[ATELIKO QR]', e);
            return null;
        }
    }

    function openClientWhatsApp(phone, text) {
        if (!phone) return false;
        var encoded = encodeURIComponent(text || '');
        var mobileDeepLink = 'whatsapp://send?phone=' + phone + '&text=' + encoded;
        var webLink = 'https://wa.me/' + phone + '?text=' + encoded;

        // Android/iOS préfèrent l'application WhatsApp. Desktop garde WhatsApp Web.
        if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '')) {
            window.location.href = mobileDeepLink;
            setTimeout(function () { window.open(webLink, '_blank'); }, 900);
        } else {
            window.open(webLink, '_blank');
        }
        return true;
    }

    // ── WhatsApp Business API configurée côté serveur ?
    var _waApiConfigured = @json(!empty(config('services.whatsapp.token')) && !empty(config('services.whatsapp.phone_number_id')));
    var _waCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ── Envoi WhatsApp universel — 4 niveaux de priorité :
    //    1. API Meta (100% automatique, serveur envoie l’image)
    //    2. Web Share API (mobile HTTPS — partage natif)
    //    3. Presse-papiers + WhatsApp Web (HTTPS bureau)
    //    4. Deeplink whatsapp:// + téléchargement image (HTTP mobile/bureau)
    window.receiptSendWhatsApp = async function (opts) {
        var phone    = cleanPhone(opts.contact || '');
        var fname    = 'recu-' + (opts.reference || 'ticket') + '.png';
        var waText   = opts.waText || '';
        var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');

        if (!phone) {
            Swal.fire({
                icon: 'warning',
                title: 'Contact manquant',
                html : 'Ajoutez le numéro WhatsApp du client dans sa fiche,<br>puis réessayez.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Niveau 1 : API WhatsApp Business (Meta) — 100% automatique
        //    Configurez WHATSAPP_API_TOKEN et WHATSAPP_PHONE_NUMBER_ID dans .env
        // ══════════════════════════════════════════════════════════════════
        if (_waApiConfigured) {
            Swal.fire({
                title: '⏳ Envoi en cours…',
                html : 'Envoi automatique via WhatsApp Business API.',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });
            try {
                var resp = await fetch('/whatsapp/send-receipt', {
                    method : 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _waCsrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ phone: opts.contact, image: opts.imgDataUrl || '', text: waText }),
                });
                var result = resp.ok ? await resp.json() : { success: false, error: 'http_' + resp.status };
                if (result.success) {
                    await Swal.fire({
                        icon : 'success',
                        title: '✅ Reçu envoyé !',
                        html : 'Le reçu a été envoyé automatiquement sur WhatsApp'
                             + (result.method === 'image' ? ' <b>avec l\'image</b>.' : ' (message texte).'),
                        timer: 4000, showConfirmButton: false
                    });
                    return;
                }
                console.warn('[WhatsApp API]', result.error || 'envoi échoué');
            } catch (e) {
                console.warn('[WhatsApp API]', e);
            }
            Swal.close();
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Niveau 2 : Web Share API (mobile HTTPS — partage avec image)
        // ══════════════════════════════════════════════════════════════════
        if (opts.imgFile && navigator.canShare && navigator.canShare({ files: [opts.imgFile] })) {
            try {
                await navigator.share({ files: [opts.imgFile], text: waText, title: 'Reçu ' + (opts.atelierNom || '') });
                return;
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Niveau 3 : Presse-papiers (HTTPS bureau — image copiée)
        // ══════════════════════════════════════════════════════════════════
        var copied = false;
        if (opts.imgFile && navigator.clipboard && window.ClipboardItem) {
            try {
                var cbBlob = new Blob([await opts.imgFile.arrayBuffer()], { type: 'image/png' });
                await navigator.clipboard.write([new ClipboardItem({ 'image/png': cbBlob })]);
                copied = true;
            } catch (_) {}
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Niveau 4 : Deeplink + téléchargement (HTTP mobile et bureau)
        //    whatsapp:// fonctionne sur mobile même sans HTTPS
        // ══════════════════════════════════════════════════════════════════
        var encoded    = encodeURIComponent(waText);
        var deepLink   = 'whatsapp://send?phone=' + phone + '&text=' + encoded;
        var webLink    = 'https://wa.me/' + phone + '?text=' + encoded;
        var downloaded = false;

        // Télécharger l’image automatiquement si pas dans le presse-papiers
        if (!copied && opts.imgDataUrl) {
            try {
                var dlA = document.createElement('a');
                dlA.href = opts.imgDataUrl;
                dlA.download = fname;
                document.body.appendChild(dlA);
                dlA.click();
                document.body.removeChild(dlA);
                downloaded = true;
            } catch (_) {}
        }

        // Ouvrir WhatsApp — deeplink sur mobile, WhatsApp Web sur bureau
        if (isMobile) {
            window.location.href = deepLink;
            // Fallback WhatsApp Web si l’app n’est pas installée
            setTimeout(function () {
                window.open(webLink, '_blank');
            }, 1500);
        } else {
            window.open(webLink, '_blank');
        }

        // Message d’instruction selon le contexte
        if (copied) {
            Swal.fire({
                icon : 'success',
                title: '📋 Image copiée',
                html : 'WhatsApp est ouvert avec le message pré-rempli.<br>'
                     + 'Collez l\'image avec <b>Ctrl+V</b> (ou maintenez appuyé sur mobile).',
                timer: 7000, showConfirmButton: false
            });
        } else if (downloaded) {
            Swal.fire({
                icon : 'info',
                title: '📥 Reçu téléchargé',
                html : 'WhatsApp est ouvert avec le message pré-rempli.<br>'
                     + 'Cliquez sur <b>📎 Joindre</b> pour envoyer l\'image du reçu.',
                timer: 7000, showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon : 'success',
                title: '✅ WhatsApp ouvert',
                html : 'La conversation est ouverte avec le message complet pré-rempli.',
                timer: 4000, showConfirmButton: false
            });
        }
    };

    window.showReceiptPopup = async function (receipt) {
        var phone  = cleanPhone(receipt.contact);
        var waText = buildWaText(receipt);

        // ── 1. QR synchrone
        var qrDataUrl = _genQrDataUrl(buildQrText(receipt));

        // ── 2. Ticket HTML hors-écran pour html2canvas
        var host = document.createElement('div');
        host.style.cssText = 'position:absolute;left:-9999px;top:0;pointer-events:none';
        host.innerHTML = buildTicketHtml(receipt, qrDataUrl);
        document.body.appendChild(host);

        // ── 3. Capture PNG
        var imgDataUrl = null;
        var imgFile    = null;
        if (window.html2canvas) {
            try {
                await new Promise(function (r) { setTimeout(r, 100); });
                var canvas = await html2canvas(host.firstElementChild, {
                    scale: 2, backgroundColor: '#ffffff',
                    useCORS: true, allowTaint: true, logging: false
                });
                imgDataUrl = canvas.toDataURL('image/png');
                var blob   = await (await fetch(imgDataUrl)).blob();
                imgFile    = new File([blob], 'recu-' + (receipt.reference || 'ticket') + '.png', { type: 'image/png' });
            } catch (e) { console.warn('[ATELIKO] html2canvas:', e); }
        }
        document.body.removeChild(host);

        if (receipt.autoWhatsApp) {
            await window.receiptSendWhatsApp({
                imgFile    : imgFile,
                imgDataUrl : imgDataUrl,
                waText     : waText,
                contact    : receipt.contact,
                reference  : receipt.reference,
                atelierNom : receipt.atelierNom,
            });
            return;
        }

        // ── 4. Popup SweetAlert avec aperçu + bouton WhatsApp
        var popHtml = '';
        if (imgDataUrl) {
            popHtml += '<img src="' + imgDataUrl + '" style="width:100%;max-width:280px;display:block;margin:0 auto 10px;box-shadow:0 2px 10px rgba(0,0,0,.15);border-radius:6px">';
            popHtml += '<a href="' + imgDataUrl + '" download="recu-' + (receipt.reference || 'ateliko') + '.png"'
                     + ' style="display:flex;align-items:center;justify-content:center;gap:6px;background:#0d6efd;color:#fff;border-radius:8px;padding:8px 12px;text-decoration:none;font-weight:600;font-size:13px;margin-bottom:6px">'
                     + '📥 Télécharger le reçu</a>';
        }

        await Swal.fire({
            title: '✅ ' + (receipt.statut || 'Enregistré') + ' !',
            html: popHtml || '<p style="color:#555">Reçu généré avec succès.</p>',
            showConfirmButton: !!(phone || imgFile),
            confirmButtonText: WA_SVG + ' &nbsp;Envoyer au client',
            confirmButtonColor: '#25D366',
            showCancelButton: true,
            cancelButtonText: 'Fermer',
            width: 380,
            preConfirm: async function () {
                await window.receiptSendWhatsApp({
                    imgFile    : imgFile,
                    imgDataUrl : imgDataUrl,
                    waText     : waText,
                    contact    : receipt.contact,
                    reference  : receipt.reference,
                    atelierNom : receipt.atelierNom,
                });
            }
        });
    };

})();
</script>
@stack('scripts')
@yield('scripts')
</body>
</html>
