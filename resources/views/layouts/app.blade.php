<!DOCTYPE html>
<html lang="fr" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ATELIKO') - Gestion Atelier de Couture</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon-32x32.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon-32x32.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo-icon.png') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
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
            <li class="{{ request()->routeIs('clients.index', 'clients.show', 'clients.edit') ? 'mm-active' : '' }}"><a href="{{ route('clients.index') }}"><div class="parent-icon"><i class="bx bx-user"></i></div><div class="menu-title">Liste des clients</div></a></li>
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

        function stopStream() {
            if (stream) { stream.getTracks().forEach(function(t){ t.stop(); }); stream = null; }
        }

        function startStream() {
            msg.textContent = 'Accès à la caméra…';
            msg.className = 'cam-msg small text-muted';
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
</script>
@stack('scripts')
@yield('scripts')
</body>
</html>
