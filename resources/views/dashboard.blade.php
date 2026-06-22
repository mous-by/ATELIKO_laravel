@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
@php 
    $user = Auth::user(); 
@endphp

<style>
    /* Modern Dashboard Styling */
    .dashboard-wrapper {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modern-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #ffffff;
        overflow: hidden;
    }

    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .modern-card .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .stat-card-gradient {
        color: #fff;
        position: relative;
        z-index: 1;
    }

    .stat-card-gradient::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
        z-index: -1;
    }

    .bg-gradient-primary { background: linear-gradient(135deg, #4e54c8, #8f94fb); }
    .bg-gradient-success { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f2994a, #f2c94c); }
    .bg-gradient-info { background: linear-gradient(135deg, #2f80ed, #56ccf2); }
    .bg-gradient-danger { background: linear-gradient(135deg, #eb3349, #f45c43); }
    .bg-gradient-dark { background: linear-gradient(135deg, #232526, #414345); }

    .stat-icon-large {
        font-size: 3rem;
        opacity: 0.8;
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        transition: transform 0.3s ease;
    }

    .modern-card:hover .stat-icon-large {
        transform: translateY(-50%) scale(1.1);
        opacity: 1;
    }

    .stat-value-large {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        font-weight: 500;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .list-group-item-modern {
        border: none;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 1.5rem;
        transition: background-color 0.2s ease;
    }

    .list-group-item-modern:last-child {
        border-bottom: none;
    }

    .list-group-item-modern:hover {
        background-color: #f8f9fa;
    }

    .avatar-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.2rem;
        color: white;
        background: linear-gradient(135deg, #a8c0ff, #3f2b96);
    }

    .badge-modern {
        padding: 0.5em 0.8em;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    /* Custom scrollbar for lists */
    .scrollable-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .scrollable-list::-webkit-scrollbar {
        width: 6px;
    }
    .scrollable-list::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 4px;
    }
    .scrollable-list::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 4px;
    }
    .scrollable-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
</style>

<div class="dashboard-wrapper pb-4">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 fw-bold">Accueil</div>
        <div class="ps-3">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><i class="bx bx-home-alt text-primary"></i></li>
                <li class="breadcrumb-item active" aria-current="page">Tableau de bord interactif</li>
            </ol>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- Dashboard SUPERADMIN --}}
    {{-- ========================================== --}}
    @if($user->isSuperAdmin())

    {{-- Alerte paiements abonnement en attente --}}
    @if(($stats['pendingPaymentsCount'] ?? 0) > 0)
    <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4">
        <div><i class="bx bx-bell-ring me-2 fs-5"></i><strong>{{ $stats['pendingPaymentsCount'] }}</strong> paiement(s) d'abonnement en attente de validation</div>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-warning">
            <i class="bx bx-check-circle me-1"></i>Valider maintenant
            <span class="badge bg-white text-warning ms-1">{{ $stats['pendingPaymentsCount'] }}</span>
        </a>
    </div>
    @endif

    <div class="row g-4 mb-4">
        @foreach([
            ['totalAteliers', 'Ateliers', 'bx-store-alt', 'bg-gradient-primary'],
            ['totalUtilisateurs', 'Utilisateurs', 'bx-user-circle', 'bg-gradient-success'],
            ['totalClients', 'Clients', 'bx-group', 'bg-gradient-warning']
        ] as [$key, $label, $icon, $bg])
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card-gradient {{ $bg }} h-100 p-4">
                <div class="stat-value-large">{{ $stats[$key] }}</div>
                <div class="stat-label">{{ $label }}</div>
                <i class="bx {{ $icon }} stat-icon-large"></i>
            </div>
        </div>
        @endforeach
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.subscriptions.index') }}" class="text-decoration-none">
            <div class="modern-card stat-card-gradient bg-gradient-info h-100 p-4" style="cursor:pointer">
                <div class="stat-value-large">{{ $stats['pendingPaymentsCount'] ?? 0 }}</div>
                <div class="stat-label">Paiements en attente</div>
                <i class="bx bx-credit-card stat-icon-large"></i>
            </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fs-5"><i class="bx bx-buildings text-primary me-2"></i>Gestion des abonnements</h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="window.location.reload()">
                        <i class="bx bx-refresh me-1"></i>Rafraîchir
                    </button>
                </div>
                <div class="card-body p-0 scrollable-list">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Atelier</th>
                                    <th>Plan</th>
                                    <th>Statut</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th style="width: 280px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['atelierSubscriptions'] as $sub)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $sub->atelier_nom }}</td>
                                    <td>{{ $sub->plan_libelle ?: '—' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($sub->statut) {
                                                'ACTIVE' => 'bg-success',
                                                'CANCELED' => 'bg-secondary',
                                                default => 'bg-warning text-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $sub->statut }}</span>
                                    </td>
                                    <td>{{ $sub->date_debut ? \Carbon\Carbon::parse($sub->date_debut)->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $sub->date_fin ? \Carbon\Carbon::parse($sub->date_fin)->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                                            <select class="form-select form-select-sm select-plan" style="min-width: 150px" data-atelier-id="{{ $sub->atelier_id }}">
                                                @foreach($stats['subscriptionPlans'] as $plan)
                                                    <option value="{{ $plan->code }}" {{ $sub->plan_code == $plan->code ? 'selected' : '' }}>
                                                        {{ $plan->libelle }} ({{ $plan->duree_mois }}m)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-success" onclick="onActivateSubscription('{{ $sub->atelier_id }}')" title="Activer le plan">
                                                <i class="bx bx-check-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="onSuspendSubscription('{{ $sub->atelier_id }}')" title="Suspendre l'abonnement">
                                                <i class="bx bx-pause-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" onclick="onEditSubscriptionDates('{{ $sub->atelier_id }}', '{{ $sub->date_debut }}', '{{ $sub->date_fin }}')" title="Modifier les dates">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Aucune donnée abonnement</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fs-5"><i class="bx bx-credit-card text-success me-2"></i>Paiements abonnement (manuel)</h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="window.location.reload()">
                        <i class="bx bx-refresh me-1"></i>Rafraîchir
                    </button>
                </div>
                <div class="card-body p-0 scrollable-list">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">N°</th>
                                    <th>Atelier</th>
                                    <th>Référence</th>
                                    <th>Réf. transfert</th>
                                    <th>Plan</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Provider</th>
                                    <th>Mode</th>
                                    <th>Preuve</th>
                                    <th>Créé le</th>
                                    <th style="width: 180px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['subscriptionPayments'] as $index => $p)
                                <tr>
                                    <td class="ps-4">{{ $index + 1 }}</td>
                                    <td>{{ $p->atelier_nom }}</td>
                                    <td>{{ $p->reference }}</td>
                                    <td>{{ $p->transaction_ref ?: '—' }}</td>
                                    <td>{{ $p->plan_code ?: '—' }}</td>
                                    <td>{{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</td>
                                    <td>
                                        @php
                                            $statutUpper = strtoupper($p->statut);
                                            $pBadge = match($statutUpper) {
                                                'PENDING' => 'bg-warning text-dark',
                                                'PAID' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $pBadge }}">{{ $statutUpper }}</span>
                                    </td>
                                    <td>{{ $p->provider }}</td>
                                    <td>{{ $p->mode_paiement ?: '—' }}</td>
                                    <td>
                                        @if($p->preuve_url)
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="showProof('{{ asset('storage/' . $p->preuve_url) }}', '{{ $p->reference }}', {{ $p->id }}, {{ $statutUpper === 'PENDING' ? 'true' : 'false' }})"
                                                title="Voir la preuve">
                                                <i class="bx bx-search-alt"></i> Voir
                                            </button>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-success" {{ $statutUpper !== 'PENDING' ? 'disabled' : '' }} onclick="onApprovePayment({{ $p->id }})" title="Valider paiement">
                                                <i class="bx bx-check-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" {{ $statutUpper !== 'PENDING' ? 'disabled' : '' }} onclick="onRejectPayment({{ $p->id }})" title="Rejeter paiement">
                                                <i class="bx bx-x-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Aucun paiement</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- Activité en temps réel (SUPERADMIN) --}}
    {{-- ========================================== --}}
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card modern-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fs-5"><i class="bx bx-history text-primary me-2"></i>Activité récente</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-pill" id="activityStatus">
                            <i class="bx bx-wifi me-1"></i>En direct
                        </span>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill" id="btnPauseActivity">
                            <i class="bx bx-pause"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height:420px;overflow-y:auto">
                    <table class="table table-hover table-sm align-middle mb-0" id="activityTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">Utilisateur</th>
                                <th>Rôle</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP</th>
                                <th>Quand</th>
                            </tr>
                        </thead>
                        <tbody id="activityBody">
                            <tr><td colspan="6" class="text-center text-muted py-4">Chargement…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card modern-card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fs-5"><i class="bx bx-user-check text-success me-2"></i>Connectés maintenant</h5>
                </div>
                <div class="card-body p-0">
                    <div id="onlineUsers" class="p-3">
                        <div class="text-muted small text-center py-3">Chargement…</div>
                    </div>
                </div>
                <div class="card-footer text-muted small text-center py-2">
                    <span id="onlineCount">0</span> utilisateur(s) actif(s) (5 min)
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- Dashboard TAILLEUR --}}
    {{-- ========================================== --}}
    @elseif($user->isTailleur())
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card-gradient bg-gradient-primary h-100 p-4">
                <div class="stat-value-large">{{ $stats['enCours'] ?? 0 }}</div>
                <div class="stat-label">En cours</div>
                <i class="bx bx-cut stat-icon-large"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card-gradient bg-gradient-success h-100 p-4">
                <div class="stat-value-large">{{ $stats['terminees'] ?? 0 }}</div>
                <div class="stat-label">Terminées</div>
                <i class="bx bx-check-double stat-icon-large"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card-gradient bg-gradient-warning h-100 p-4">
                <div class="stat-value-large">{{ $stats['affectationsTermineesSemaine'] ?? 0 }}</div>
                <div class="stat-label">Terminées (7j)</div>
                <i class="bx bx-calendar-check stat-icon-large"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card-gradient bg-gradient-info h-100 p-4">
                <div class="stat-value-large">{{ number_format($stats['revenusMensuels'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label">Revenus (FCFA)</div>
                <i class="bx bx-wallet stat-icon-large"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="modern-card h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h5 class="mb-0"><i class="bx bx-list-check me-2 text-primary"></i>Affectations en cours</h5>
                </div>
                <div class="card-body p-0 scrollable-list">
                    @if(count($stats['affectationsEnCoursList'] ?? []) > 0)
                        <div class="list-group list-group-flush px-2 pb-2">
                            @foreach($stats['affectationsEnCoursList'] as $aff)
                            <div class="list-group-item-modern rounded mb-2 mx-2 border shadow-sm">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-gradient-dark">
                                            {{ strtoupper(substr($aff->clientNom, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $aff->clientNom }}</h6>
                                            <span class="text-muted small"><i class="bx bx-closet me-1"></i>{{ $aff->typeVetement }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="mb-1">
                                            @if($aff->statut == 'EN_COURS')
                                                <span class="badge badge-modern bg-primary-subtle text-primary border border-primary-subtle">EN COURS</span>
                                            @else
                                                <span class="badge badge-modern bg-warning-subtle text-warning border border-warning-subtle">EN ATTENTE</span>
                                            @endif
                                        </div>
                                        <small class="text-muted"><i class="bx bx-calendar me-1"></i>{{ $aff->dateEcheance ? \Carbon\Carbon::parse($aff->dateEcheance)->format('d/m/Y') : '—' }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center p-5 text-muted">
                            <i class="bx bx-coffee fs-1 mb-3 opacity-50"></i>
                            <p>Aucune affectation en cours. Bon repos !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="modern-card h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h5 class="mb-0"><i class="bx bx-bell me-2 text-danger"></i>Prochaines échéances</h5>
                </div>
                <div class="card-body p-0">
                    @if(count($stats['prochainesEcheances'] ?? []) > 0)
                        <div class="list-group list-group-flush px-3 pb-3">
                            @foreach($stats['prochainesEcheances'] as $echeance)
                            @php
                                $joursRestants = $echeance->joursRestants;
                                $badgeColor = $joursRestants <= 2 ? 'danger' : ($joursRestants <= 5 ? 'warning' : 'info');
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded bg-white shadow-sm transition-hover">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">{{ $echeance->clientNom }}</h6>
                                    <small class="text-muted"><i class="bx bx-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($echeance->dateEcheance)->format('d/m/Y') }}</small>
                                </div>
                                <span class="badge bg-{{ $badgeColor }} rounded-pill px-3 py-2 shadow-sm">
                                    {{ $joursRestants == 0 ? "Aujourd'hui" : ($joursRestants == 1 ? "Demain" : "$joursRestants jours") }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center p-5 text-muted">
                            <i class="bx bx-check-shield fs-1 mb-3 opacity-50 text-success"></i>
                            <p>Aucune échéance proche !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- Dashboard SECRETAIRE / PROPRIETAIRE --}}
    {{-- ========================================== --}}
    @else
    <div class="row g-4 mb-4">
        @if($user->isSecretaire())
            {{-- Vue Secrétaire --}}
            @foreach([
                ['rdvsAujourdhui', "RDV Aujourd'hui", 'bx-calendar', 'bg-gradient-primary'],
                ['nouveauxClientsSemaine', 'Nouveaux clients', 'bx-user-plus', 'bg-gradient-success'],
                ['affectationsEnAttente', 'Commandes en attente', 'bx-clipboard', 'bg-gradient-warning'],
                ['paiementsAttente', 'Paiements en attente', 'bx-credit-card', 'bg-gradient-danger'],
            ] as [$key, $label, $icon, $bg])
            <div class="col-xl-3 col-md-6">
                <div class="modern-card stat-card-gradient {{ $bg }} h-100 p-4">
                    <div class="stat-value-large">{{ $stats[$key] ?? 0 }}</div>
                    <div class="stat-label">{{ $label }}</div>
                    <i class="bx {{ $icon }} stat-icon-large"></i>
                </div>
            </div>
            @endforeach
        @else
            {{-- Vue Propriétaire --}}
            <div class="col-xl-3 col-md-6">
                <div class="modern-card stat-card-gradient bg-gradient-primary h-100 p-4">
                    <div class="stat-value-large">{{ number_format($stats['chiffreAffairesMensuel'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="stat-label">C.A. Mensuel (FCFA)</div>
                    <i class="bx bx-money stat-icon-large"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="modern-card stat-card-gradient bg-gradient-success h-100 p-4">
                    <div class="stat-value-large">{{ $stats['affectationsEnCours'] ?? 0 }}</div>
                    <div class="stat-label">Commandes en cours</div>
                    <i class="bx bx-layer stat-icon-large"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="modern-card stat-card-gradient bg-gradient-warning h-100 p-4">
                    <div class="stat-value-large">{{ $stats['totalClients'] ?? 0 }}</div>
                    <div class="stat-label">Clients</div>
                    <i class="bx bx-group stat-icon-large"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="modern-card stat-card-gradient bg-gradient-info h-100 p-4">
                    <div class="stat-value-large">{{ $stats['totalTailleurs'] ?? 0 }}</div>
                    <div class="stat-label">Tailleurs</div>
                    <i class="bx bx-user stat-icon-large"></i>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-4">
        {{-- Colonne Principale --}}
        <div class="col-lg-8">
            {{-- Performance Tailleurs (Propriétaire uniquement) --}}
            @if(!$user->isSecretaire())
            <div class="modern-card mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-dark"><i class="bx bx-star text-warning me-2"></i>Performance des tailleurs</h5>
                </div>
                <div class="card-body">
                    @if(count($stats['performanceTailleurs'] ?? []) > 0)
                        <div class="row g-3">
                            @foreach($stats['performanceTailleurs'] as $perf)
                            <div class="col-md-6 col-xl-4">
                                <div class="p-3 border rounded shadow-sm bg-white position-relative overflow-hidden h-100">
                                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-10" style="z-index: 0; display: none;"></div>
                                    <div class="d-flex justify-content-between align-items-start mb-2 relative z-1">
                                        <h6 class="mb-0 fw-bold text-truncate pe-2">{{ $perf->nomTailleur }}</h6>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">{{ $perf->satisfactionMoyenne }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small relative z-1">
                                        <span><i class="bx bx-check text-success"></i> {{ $perf->affectationsTerminees }} terminées</span>
                                        @if($perf->affectationsEnRetard > 0)
                                            <span class="text-danger"><i class="bx bx-error"></i> {{ $perf->affectationsEnRetard }} retards</span>
                                        @else
                                            <span class="text-success"><i class="bx bx-check-shield"></i> 0 retard</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Aucune donnée de performance disponible.</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Rendez-vous prochains --}}
            <div class="modern-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark mb-0"><i class="bx bx-calendar-event text-info me-2"></i>Prochains Rendez-vous</h5>
                    <a href="{{ route('rendezvous.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-sm">Tout voir</a>
                </div>
                <div class="card-body p-0 scrollable-list">
                    @if(count($stats['rendezVousProchains'] ?? []) > 0)
                        <div class="list-group list-group-flush px-3 pb-3">
                            @foreach($stats['rendezVousProchains'] as $rdv)
                            @php
                                $statusColor = match($rdv->statut) {
                                    'PLANIFIE' => 'warning',
                                    'CONFIRME' => 'success',
                                    'ANNULE' => 'danger',
                                    'TERMINE' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-2 p-3 border rounded shadow-sm hover-bg-light transition-all">
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="bg-light rounded p-2 text-center" style="min-width: 60px;">
                                        <div class="fw-bold text-primary fs-5 lh-1">{{ \Carbon\Carbon::parse($rdv->date)->format('d') }}</div>
                                        <div class="small text-muted fw-bold text-uppercase" style="font-size: 10px;">{{ \Carbon\Carbon::parse($rdv->date)->translatedFormat('M') }}</div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ $rdv->clientNom }}</h6>
                                        <small class="text-muted"><i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($rdv->date)->format('H:i') }} - {{ $rdv->type }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle rounded-pill px-3">{{ $rdv->statut }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-calendar-x fs-1 mb-3 opacity-50"></i>
                            <p class="mb-0">Aucun rendez-vous à venir</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne Latérale --}}
        <div class="col-lg-4">

            {{-- Card Abonnement (Propriétaire uniquement) --}}
            @if($user->isProprietaire())
            @php
                $abo = $stats['abonnement'] ?? null;
                $joursAbo = $stats['joursAbonnement'] ?? null;
                $blockedAbo = $stats['blockedAbonnement'] ?? false;
                $aboStatutColor = ['ACTIVE'=>'success','EXPIRED'=>'danger','CANCELED'=>'secondary','PAST_DUE'=>'warning'][$abo?->statut ?? ''] ?? 'secondary';
            @endphp
            <div class="modern-card mb-4 {{ $blockedAbo ? 'border border-danger border-2' : ($joursAbo !== null && $joursAbo <= 7 ? 'border border-warning border-2' : '') }}">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark mb-0"><i class="bx bx-badge-check text-primary me-2"></i>Abonnement</h5>
                    <a href="{{ route('abonnement.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Gérer</a>
                </div>
                <div class="card-body">
                    @if($abo)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">{{ $abo->plan?->libelle ?? '—' }}</span>
                        <span class="badge bg-{{ $aboStatutColor }}">{{ $abo->statut }}</span>
                    </div>
                    <div class="small text-muted mb-2">
                        Expire le : <strong class="{{ $blockedAbo ? 'text-danger' : '' }}">{{ $abo->date_fin?->format('d/m/Y') ?? '—' }}</strong>
                    </div>
                    @if($joursAbo !== null)
                    @php
                        $pctAbo = $abo->plan ? min(100, max(0, (int)(($joursAbo / ($abo->plan->duree_mois * 30)) * 100))) : 0;
                        $barAbo = $pctAbo > 30 ? 'bg-success' : ($pctAbo > 10 ? 'bg-warning' : 'bg-danger');
                    @endphp
                    <div class="progress mb-2" style="height:6px">
                        <div class="progress-bar {{ $barAbo }}" style="width:{{ $pctAbo }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Jours restants</span>
                        <strong class="{{ $joursAbo <= 7 ? 'text-danger' : 'text-success' }}">{{ max(0, $joursAbo) }} j</strong>
                    </div>
                    @endif
                    @if($blockedAbo)
                    <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small">
                        <i class="bx bx-block me-1"></i>Accès restreint — renouvelez votre abonnement
                    </div>
                    @elseif($joursAbo !== null && $joursAbo <= 7)
                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                        <i class="bx bx-error-circle me-1"></i>Expire bientôt — pensez à renouveler
                    </div>
                    @endif
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="bx bx-credit-card fs-2 d-block mb-1"></i>
                        <small>Aucun abonnement actif</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Chart pour Statut des commandes (Propriétaire uniquement) --}}
            @if(!$user->isSecretaire())
            <div class="modern-card mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-dark"><i class="bx bx-pie-chart-alt-2 text-primary me-2"></i>Statut des commandes</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px;">
                    @if(!empty($stats['affectationsParStatut']))
                        <canvas id="statusChart" width="100%" height="100%"></canvas>
                    @else
                        <p class="text-muted text-center">Aucune donnée disponible</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Tâches Urgentes --}}
            <div class="modern-card h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h5 class="card-title fw-bold text-dark"><i class="bx bx-error-circle text-danger me-2"></i>Tâches Urgentes</h5>
                </div>
                <div class="card-body p-3">
                    @if(count($stats['tachesUrgentes'] ?? []) > 0)
                        @foreach($stats['tachesUrgentes'] as $tache)
                        @php
                            $pColor = match($tache->priorite) {
                                'HAUTE' => 'danger',
                                'MOYENNE' => 'warning',
                                'BASSE' => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="alert alert-{{ $pColor }} border-0 shadow-sm d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-error text-{{ $pColor }} fs-4"></i>
                                <span class="fw-medium text-dark">{{ $tache->description }}</span>
                            </div>
                            <span class="badge bg-white text-{{ $pColor }} border">{{ $tache->type }}</span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-check-shield fs-1 text-success mb-3 opacity-50"></i>
                            <p class="mb-0">Tout est sous contrôle !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Script pour Chart.js --}}
@if(!$user->isSuperAdmin() && !$user->isTailleur() && !$user->isSecretaire() && !empty($stats['affectationsParStatut']))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('statusChart').getContext('2d');
        const rawData = @json($stats['affectationsParStatut']);
        
        const labels = Object.keys(rawData);
        const values = Object.values(rawData);
        
        // Define appealing colors matching React Dashboard
        const colorMap = {
            'EN_ATTENTE': '#ffc107',
            'EN_COURS': '#0d6efd',
            'TERMINE': '#198754',
            'VALIDE': '#0dcaf0',
            'ANNULE': '#dc3545'
        };
        
        const backgroundColors = labels.map(label => colorMap[label] || '#6c757d');

        if(labels.length > 0) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: backgroundColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    },
                    cutout: '70%',
                    layout: { padding: 10 }
                }
            });
        }
    });
</script>
@endif

{{-- Modal prévisualisation preuve de paiement (SUPERADMIN) --}}
@if($user->isSuperAdmin())
<div class="modal fade" id="modalProofPreview" tabindex="-1" aria-labelledby="modalProofLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProofLabel">
                    <i class="bx bx-file-find me-2 text-primary"></i>
                    Prévisualisation — <span id="proofRefLabel" class="text-muted fw-normal"></span>
                </h5>
                <div class="ms-auto d-flex gap-2 align-items-center me-3">
                    <a id="proofOpenLink" href="#" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-link-external me-1"></i>Ouvrir
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0 bg-dark" style="min-height:500px">
                <div id="proofLoading" class="d-flex align-items-center justify-content-center" style="min-height:400px">
                    <div class="spinner-border text-light"></div>
                </div>
                <img id="proofImage" src="" alt="Preuve de paiement"
                    style="display:none;max-width:100%;max-height:75vh;object-fit:contain;margin:auto;display:none"
                    class="d-block mx-auto">
                <iframe id="proofPdf" src="" style="display:none;width:100%;height:75vh;border:0"></iframe>
            </div>
            <div class="modal-footer justify-content-between" id="proofActions">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <div class="d-flex gap-2" id="proofPendingActions" style="display:none!important">
                    <button type="button" class="btn btn-success" id="proofApproveBtn">
                        <i class="bx bx-check-circle me-1"></i>Valider ce paiement
                    </button>
                    <button type="button" class="btn btn-danger" id="proofRejectBtn">
                        <i class="bx bx-x-circle me-1"></i>Rejeter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@parent
<script>
    // AJAX Setup for CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var _currentProofPaymentId = null;

    function showProof(url, reference, paymentId, isPending) {
        _currentProofPaymentId = paymentId;

        // Mettre à jour l'en-tête
        document.getElementById('proofRefLabel').textContent = reference;
        document.getElementById('proofOpenLink').href = url;

        // Réinitialiser l'affichage
        var img    = document.getElementById('proofImage');
        var pdf    = document.getElementById('proofPdf');
        var loader = document.getElementById('proofLoading');
        img.style.display = 'none';
        pdf.style.display = 'none';
        loader.style.display = 'flex';

        // Boutons valider/rejeter
        var pendingActions = document.getElementById('proofPendingActions');
        pendingActions.style.display = isPending ? '' : 'none';
        pendingActions.style.setProperty('display', isPending ? 'flex' : 'none', 'important');
        if (isPending) {
            document.getElementById('proofApproveBtn').onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('modalProofPreview')).hide();
                onApprovePayment(paymentId);
            };
            document.getElementById('proofRejectBtn').onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('modalProofPreview')).hide();
                onRejectPayment(paymentId);
            };
        }

        // Ouvrir le modal
        var modal = new bootstrap.Modal(document.getElementById('modalProofPreview'));
        modal.show();

        // Détecter image ou PDF
        var isPdf = url.toLowerCase().includes('.pdf') || url.toLowerCase().includes('pdf');
        if (isPdf) {
            pdf.src = url;
            pdf.onload = function() { loader.style.display = 'none'; pdf.style.display = 'block'; };
            pdf.style.display = 'block';
            loader.style.display = 'none';
        } else {
            img.src = '';
            img.onload = function() { loader.style.display = 'none'; img.style.display = 'block'; };
            img.onerror = function() {
                loader.style.display = 'none';
                loader.innerHTML = '<div class="text-danger text-center p-4"><i class="bx bx-error-circle fs-1"></i><br>Impossible de charger l\'image</div>';
            };
            img.src = url;
        }
    }

    var SWAL_BASE = {
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        reverseButtons: true,
        focusCancel: true,
    };

    function swalDone(msg) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg,
            showConfirmButton: false, timer: 3500, timerProgressBar: true });
    }
    function swalFail(err) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error',
            title: err.responseJSON?.message || 'Une erreur est survenue',
            showConfirmButton: false, timer: 4000, timerProgressBar: true });
    }

    function onActivateSubscription(atelierId) {
        const select = document.querySelector(`.select-plan[data-atelier-id="${atelierId}"]`);
        const planCode = select ? select.value : 'MENSUEL';

        Swal.fire(Object.assign({}, SWAL_BASE, {
            title: 'Activer l\'abonnement ?',
            html: `Activer le plan <strong>${planCode}</strong> pour cet atelier ?`,
            icon: 'question',
            confirmButtonColor: '#0d6efd',
            confirmButtonText: '<i class="bx bx-check-circle"></i> Activer',
        })).then((result) => {
            if (result.isConfirmed) {
                $.post(`{{ url('/admin/subscriptions/ateliers') }}/${atelierId}/activate`, { planCode })
                .done(res => { swalDone(res.message); setTimeout(() => location.reload(), 1200); })
                .fail(swalFail);
            }
        });
    }

    function onSuspendSubscription(atelierId) {
        Swal.fire(Object.assign({}, SWAL_BASE, {
            title: 'Suspendre l\'abonnement ?',
            text: "L'atelier perdra immédiatement ses accès.",
            icon: 'warning',
            confirmButtonColor: '#e67e22',
            confirmButtonText: '<i class="bx bx-pause-circle"></i> Suspendre',
        })).then((result) => {
            if (result.isConfirmed) {
                $.post(`{{ url('/admin/subscriptions/ateliers') }}/${atelierId}/suspend`)
                .done(res => { swalDone(res.message); setTimeout(() => location.reload(), 1200); })
                .fail(swalFail);
            }
        });
    }

    function onEditSubscriptionDates(atelierId, currentDebut, currentFin) {
        Swal.fire(Object.assign({}, SWAL_BASE, {
            title: 'Modifier les dates',
            html: `<label class="swal2-label">Date de début</label>
                   <input type="date" id="swal-input1" class="swal2-input" value="${currentDebut ? currentDebut.substring(0,10) : ''}">
                   <label class="swal2-label mt-2">Date de fin</label>
                   <input type="date" id="swal-input2" class="swal2-input" value="${currentFin ? currentFin.substring(0,10) : ''}">`,
            icon: 'info',
            focusConfirm: false,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Enregistrer',
            preConfirm: () => {
                const dateDebut = document.getElementById('swal-input1').value;
                const dateFin   = document.getElementById('swal-input2').value;
                if (!dateDebut || !dateFin) { Swal.showValidationMessage('Les deux dates sont requises'); return false; }
                return { dateDebut, dateFin };
            }
        })).then((result) => {
            if (result.isConfirmed) {
                $.ajax({ url: `{{ url('/admin/subscriptions/ateliers') }}/${atelierId}/dates`, type: 'PUT', data: result.value })
                .done(res => { swalDone(res.message); setTimeout(() => location.reload(), 1200); })
                .fail(swalFail);
            }
        });
    }

    function onApprovePayment(paymentId) {
        Swal.fire(Object.assign({}, SWAL_BASE, {
            title: 'Approuver ce paiement et activer l\'abonnement ?',
            text: "L'accès de l'atelier sera rétabli immédiatement après validation.",
            icon: 'question',
            confirmButtonColor: '#198754',
            confirmButtonText: '<i class="bx bx-check-shield"></i> Approuver et activer',
            cancelButtonText: 'Annuler',
            focusCancel: true,
        })).then((result) => {
            if (result.isConfirmed) {
                $.post(`{{ url('/admin/subscriptions/payments') }}/${paymentId}/approve`)
                .done(res => { swalDone(res.message); setTimeout(() => location.reload(), 1200); })
                .fail(swalFail);
            }
        });
    }

    function onRejectPayment(paymentId) {
        Swal.fire(Object.assign({}, SWAL_BASE, {
            title: 'Rejeter ce paiement',
            input: 'textarea',
            inputLabel: 'Motif du rejet (obligatoire)',
            inputPlaceholder: 'Expliquez pourquoi ce paiement est rejeté...',
            inputAttributes: { rows: 3 },
            icon: 'warning',
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="bx bx-x-circle"></i> Rejeter',
            preConfirm: (reason) => {
                if (!reason || !reason.trim()) { Swal.showValidationMessage('Le motif est obligatoire'); return false; }
                return reason;
            }
        })).then((result) => {
            if (result.isConfirmed) {
                $.post(`{{ url('/admin/subscriptions/payments') }}/${paymentId}/reject`, { reason: result.value })
                .done(res => { swalDone(res.message); setTimeout(() => location.reload(), 1200); })
                .fail(swalFail);
            }
        });
    }

    @if($user->isSuperAdmin())
    // ===== Tableau d'activité temps réel =====
    var activityPaused = false;
    var activityTimer  = null;

    var actionBadge = {
        'LOGIN':   '<span class="badge bg-success">Connexion</span>',
        'LOGOUT':  '<span class="badge bg-secondary">Déconnexion</span>',
        'CREATE':  '<span class="badge bg-primary">Création</span>',
        'UPDATE':  '<span class="badge bg-info text-dark">Modification</span>',
        'DELETE':  '<span class="badge bg-danger">Suppression</span>',
        'PAYMENT': '<span class="badge bg-warning text-dark">Paiement</span>',
    };

    var roleBadge = {
        'SUPERADMIN':  'bg-dark',
        'PROPRIETAIRE':'bg-primary',
        'SECRETAIRE':  'bg-info text-dark',
        'TAILLEUR':    'bg-secondary',
    };

    function fetchActivity() {
        if (activityPaused) return;
        $.getJSON('{{ route("dashboard.activity") }}', function(data) {
            // Logs
            var tbody = document.getElementById('activityBody');
            if (!tbody) return;
            if (!data.logs || data.logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Aucune activité enregistrée</td></tr>';
            } else {
                tbody.innerHTML = data.logs.map(function(l) {
                    var badge = actionBadge[l.action] || '<span class="badge bg-light text-dark">' + l.action + '</span>';
                    var rc = roleBadge[l.role] || 'bg-light text-dark';
                    return '<tr>' +
                        '<td class="ps-3 fw-medium">' + (l.nom_utilisateur || '—') + '</td>' +
                        '<td><span class="badge ' + rc + '">' + (l.role || '—') + '</span></td>' +
                        '<td>' + badge + '</td>' +
                        '<td class="text-muted small">' + (l.description || '—') + '</td>' +
                        '<td class="text-muted small">' + (l.ip_address || '—') + '</td>' +
                        '<td class="text-muted small" title="' + (l.created_at || '') + '">' + (l.diff || '') + '</td>' +
                        '</tr>';
                }).join('');
            }

            // Utilisateurs en ligne
            var onlineDiv = document.getElementById('onlineUsers');
            document.getElementById('onlineCount').textContent = data.onlineCount || 0;
            if (!data.onlineUsers || data.onlineUsers.length === 0) {
                onlineDiv.innerHTML = '<div class="text-muted small text-center py-3">Aucun utilisateur actif</div>';
            } else {
                onlineDiv.innerHTML = data.onlineUsers.map(function(u) {
                    var rc = roleBadge[u.role] || 'bg-light text-dark';
                    return '<div class="d-flex align-items-center gap-2 py-2 border-bottom">' +
                        '<span class="rounded-circle bg-success" style="width:10px;height:10px;flex-shrink:0"></span>' +
                        '<span class="fw-medium small">' + u.nom + '</span>' +
                        '<span class="badge ' + rc + ' ms-auto">' + u.role + '</span>' +
                        '</div>';
                }).join('');
            }
        }).fail(function() {
            document.getElementById('activityStatus').innerHTML = '<i class="bx bx-wifi-off me-1"></i>Hors ligne';
            document.getElementById('activityStatus').className = 'badge bg-danger rounded-pill';
        });
    }

    function startActivityPolling() {
        fetchActivity();
        activityTimer = setInterval(fetchActivity, 15000);
    }

    document.getElementById('btnPauseActivity').addEventListener('click', function() {
        activityPaused = !activityPaused;
        var statusEl = document.getElementById('activityStatus');
        if (activityPaused) {
            this.innerHTML = '<i class="bx bx-play"></i>';
            statusEl.innerHTML = '<i class="bx bx-pause me-1"></i>En pause';
            statusEl.className = 'badge bg-warning text-dark rounded-pill';
        } else {
            this.innerHTML = '<i class="bx bx-pause"></i>';
            statusEl.innerHTML = '<i class="bx bx-wifi me-1"></i>En direct';
            statusEl.className = 'badge bg-success rounded-pill';
            fetchActivity();
        }
    });

    startActivityPolling();
    @endif
</script>
@endsection
