@extends('layouts.app')
@section('title', 'Gestion Abonnements')
@section('page-title', 'Gestion des abonnements')
@section('page-subtitle', 'Validation des paiements et suivi des ateliers')

@section('content')

{{-- Onglets --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'paiements' ? 'active' : '' }}"
           href="{{ route('admin.subscriptions.index', ['tab' => 'paiements']) }}">
            <i class="bx bx-credit-card me-1"></i>Paiements
            @if($paiementsEnAttente->count() > 0)
            <span class="badge bg-danger ms-1">{{ $paiementsEnAttente->count() }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'ateliers' ? 'active' : '' }}"
           href="{{ route('admin.subscriptions.index', ['tab' => 'ateliers']) }}">
            <i class="bx bx-building me-1"></i>Ateliers
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'historique' ? 'active' : '' }}"
           href="{{ route('admin.subscriptions.index', ['tab' => 'historique']) }}">
            <i class="bx bx-history me-1"></i>Historique
        </a>
    </li>
</ul>

{{-- ============================================================
     TAB : PAIEMENTS EN ATTENTE
     ============================================================ --}}
@if($tab === 'paiements')

@if($paiementsEnAttente->isEmpty())
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bx bx-check-circle fs-1 d-block mb-2 text-success"></i>
        Aucun paiement en attente de validation
    </div>
</div>
@else
<div class="row g-3">
    @foreach($paiementsEnAttente as $p)
    @php $atelier = $p->abonnement?->atelier; @endphp
    <div class="col-12">
        <div class="card border-start border-warning border-3">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-4">
                        <div class="fw-bold fs-6">{{ $atelier?->nom ?? '—' }}</div>
                        <code class="small text-muted">{{ $p->reference }}</code>
                        <div class="mt-1">
                            <span class="badge bg-light text-dark border me-1">{{ $p->plan_code }}</span>
                            <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $p->mode_paiement) }}</span>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="fs-5 fw-bold text-primary">{{ number_format($p->montant, 0, ',', ' ') }}</div>
                        <small class="text-muted">{{ $p->devise }}</small>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="small text-muted">Soumis le</div>
                        <div class="fw-semibold">{{ $p->created_at?->format('d/m/Y') }}</div>
                        <div class="small text-muted">{{ $p->created_at?->diffForHumans() }}</div>
                    </div>
                    <div class="col-md-2">
                        @if($p->preuve_url)
                        <a href="{{ asset('storage/' . $p->preuve_url) }}" target="_blank"
                           class="btn btn-sm btn-outline-info w-100">
                            <i class="bx bx-file me-1"></i>Voir preuve
                        </a>
                        @endif
                        @if($p->transaction_ref)
                        <div class="small text-muted mt-1">Réf: {{ $p->transaction_ref }}</div>
                        @endif
                        @if($p->owner_note)
                        <div class="small text-muted mt-1 fst-italic">"{{ Str::limit($p->owner_note, 40) }}"</div>
                        @endif
                    </div>
                    <div class="col-md-2 d-flex gap-2 justify-content-end">
                        <form action="{{ route('admin.subscriptions.payments.approve', $p->id) }}" method="POST"
                              data-confirm="Approuver ce paiement et activer l'abonnement ?"
                              data-confirm-text="L'accès de l'atelier sera rétabli immédiatement."
                              data-confirm-icon="question"
                              data-confirm-color="#198754"
                              data-confirm-btn="Approuver et activer">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bx bx-check me-1"></i>Valider
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm"
                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">
                            <i class="bx bx-x me-1"></i>Rejeter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal rejet --}}
        <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.subscriptions.payments.reject', $p->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="bx bx-x-circle me-2"></i>Rejeter le paiement</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Atelier : <strong>{{ $atelier?->nom }}</strong></p>
                            <p>Plan : <strong>{{ $p->plan_code }}</strong> — {{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</p>
                            <div class="mb-3">
                                <label class="form-label">Raison du rejet *</label>
                                <textarea name="reason" class="form-control" rows="3" required
                                          placeholder="Expliquez pourquoi le paiement est rejeté..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ============================================================
     TAB : ATELIERS
     ============================================================ --}}
@elseif($tab === 'ateliers')

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bx bx-building me-2 text-primary"></i>Abonnements par atelier</span>
        <span class="badge bg-primary">{{ $ateliers->count() }} atelier(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Atelier</th>
                        <th>Plan</th>
                        <th>Statut</th>
                        <th>Date fin</th>
                        <th>Jours restants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ateliers as $atelier)
                    @php
                        $abo = $atelier->abonnement;
                        $sc = ['ACTIVE'=>'success','EXPIRED'=>'danger','CANCELED'=>'secondary','PAST_DUE'=>'warning'][$abo?->statut ?? ''] ?? 'light';
                        $jours = $abo?->date_fin ? (int)now()->diffInDays($abo->date_fin, false) : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $atelier->nom }}</div>
                            <small class="text-muted">{{ $atelier->clients_count }} clients · {{ $atelier->utilisateurs_count }} utilisateurs</small>
                        </td>
                        <td>
                            @if($abo?->plan)
                            <span class="badge bg-light text-dark border">{{ $abo->plan->libelle }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($abo)
                            <span class="badge bg-{{ $sc }}">{{ $abo->statut }}</span>
                            @else
                            <span class="badge bg-light text-dark border">Aucun</span>
                            @endif
                        </td>
                        <td>
                            @if($abo?->date_fin)
                            <span class="{{ $jours !== null && $jours < 0 ? 'text-danger fw-bold' : '' }}">
                                {{ $abo->date_fin->format('d/m/Y') }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($jours !== null)
                            <span class="badge {{ $jours < 0 ? 'bg-danger' : ($jours <= 7 ? 'bg-warning text-dark' : 'bg-success') }}">
                                {{ max(0, $jours) }} j
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                {{-- Activer --}}
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                        data-bs-target="#activateModal{{ $atelier->id }}">
                                    <i class="bx bx-play me-1"></i>Activer
                                </button>
                                {{-- Suspendre --}}
                                @if($abo?->statut === 'ACTIVE')
                                <form action="{{ route('admin.subscriptions.suspend', $atelier->id) }}" method="POST"
                                          data-confirm="Suspendre l'abonnement de {{ $atelier->nom }} ?"
                                          data-confirm-text="L'atelier perdra immédiatement ses accès."
                                          data-confirm-color="#e67e22"
                                          data-confirm-btn="Suspendre">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="bx bx-pause me-1"></i>Suspendre
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal activation --}}
                    <div class="modal fade" id="activateModal{{ $atelier->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.subscriptions.activate', $atelier->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title"><i class="bx bx-check-circle me-2"></i>Activer l'abonnement</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Atelier : <strong>{{ $atelier->nom }}</strong></p>
                                        <div class="mb-3">
                                            <label class="form-label">Plan *</label>
                                            <select name="planCode" class="form-select" required>
                                                @foreach($plans as $plan)
                                                <option value="{{ $plan->code }}" {{ ($abo?->plan?->code === $plan->code) ? 'selected' : '' }}>
                                                    {{ $plan->libelle }} — {{ $plan->duree_mois }} mois ({{ number_format($plan->prix, 0, ',', ' ') }} {{ $plan->devise }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Date de début</label>
                                            <input type="date" name="startAt" class="form-control"
                                                   value="{{ now()->format('Y-m-d') }}">
                                            <div class="form-text">Laissez vide pour démarrer maintenant</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-success">Activer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Aucun atelier enregistré</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================================
     TAB : HISTORIQUE
     ============================================================ --}}
@else

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bx bx-history me-2 text-primary"></i>Historique des paiements traités</span>
        <span class="badge bg-primary">{{ $paiementsHistorique->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if($paiementsHistorique->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bx bx-time fs-1 d-block mb-2"></i>
            Aucun paiement traité
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Référence</th>
                        <th>Atelier</th>
                        <th>Plan</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paiementsHistorique as $p)
                    @php $pc = ['PAID'=>'success','FAILED'=>'danger'][$p->statut] ?? 'secondary'; @endphp
                    <tr>
                        <td><code class="small">{{ $p->reference }}</code></td>
                        <td><strong>{{ $p->abonnement?->atelier?->nom ?? '—' }}</strong></td>
                        <td><span class="badge bg-light text-dark border">{{ $p->plan_code }}</span></td>
                        <td class="fw-semibold">{{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</td>
                        <td>
                            <span class="badge bg-{{ $pc }}">{{ $p->statut }}</span>
                            @if($p->statut === 'FAILED' && $p->review_note)
                            <div class="small text-danger">{{ Str::limit($p->review_note, 40) }}</div>
                            @endif
                        </td>
                        <td><small>{{ $p->reviewer?->prenom }} {{ $p->reviewer?->nom }}</small></td>
                        <td><small class="text-muted">{{ $p->updated_at?->format('d/m/Y H:i') }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endif
@endsection
