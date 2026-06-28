@extends('layouts.app')
@section('title', 'Mon Abonnement')
@section('page-title', 'Gestion de l\'abonnement')
@section('page-subtitle', 'Statut et paiement de votre abonnement ATELIKO')

@section('content')

{{-- Alerte expiration --}}
@if($blocked)
<div class="alert alert-danger d-flex align-items-center gap-3 mb-4">
    <i class="bx bx-block fs-3"></i>
    <div>
        <strong>Abonnement expiré ou suspendu.</strong>
        Soumettez un paiement pour réactiver l'accès complet.
    </div>
</div>
@elseif($expireBientot)
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="bx bx-error-circle fs-3"></i>
    <div>
        <strong>Abonnement expire dans {{ $joursRestants }} jour(s) !</strong>
        Renouvelez maintenant pour éviter toute interruption.
    </div>
</div>
@endif

<div class="row g-4">

    {{-- Colonne gauche : statut + formulaire --}}
    <div class="col-lg-5">

        {{-- Statut actuel --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                <i class="bx bx-badge-check fs-5"></i>
                <span class="fw-semibold">Statut de l'abonnement</span>
            </div>
            <div class="card-body">
                @if($abonnement)
                @php
                    $statutColors = [
                        'ACTIVE'   => 'success',
                        'EXPIRED'  => 'danger',
                        'CANCELED' => 'secondary',
                        'PAST_DUE' => 'warning',
                    ];
                    $sc = $statutColors[$abonnement->statut] ?? 'secondary';
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fs-5 fw-bold">{{ $abonnement->plan?->libelle ?? '—' }}</div>
                        <small class="text-muted">{{ $abonnement->plan?->duree_mois }} mois · {{ number_format($abonnement->plan?->prix, 0, ',', ' ') }} {{ $abonnement->plan?->devise }}</small>
                    </div>
                    <span class="badge bg-{{ $sc }} fs-6 px-3 py-2">{{ $abonnement->statut }}</span>
                </div>
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="small text-muted">Début</div>
                            <div class="fw-semibold">{{ $abonnement->date_debut?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="small text-muted">Fin</div>
                            <div class="fw-semibold {{ $blocked ? 'text-danger' : '' }}">
                                {{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                @if($joursRestants !== null)
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Jours restants</span>
                        <strong class="{{ $joursRestants <= 7 ? 'text-danger' : 'text-success' }}">
                            {{ max(0, $joursRestants) }} j
                        </strong>
                    </div>
                    @php
                        $totalJours = $abonnement->plan?->duree_mois * 30 ?: 30;
                        $pct = $totalJours > 0 ? min(100, max(0, (int)(($joursRestants / $totalJours) * 100))) : 0;
                        $barColor = $pct > 30 ? 'bg-success' : ($pct > 10 ? 'bg-warning' : 'bg-danger');
                    @endphp
                    <div class="progress" style="height:8px">
                        <div class="progress-bar {{ $barColor }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endif
                @else
                <div class="text-center text-muted py-3">
                    <i class="bx bx-credit-card fs-2 d-block mb-2"></i>
                    Aucun abonnement actif.<br>Soumettez un premier paiement.
                </div>
                @endif
            </div>
        </div>

        {{-- Formulaire paiement --}}
        <div class="card">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bx bx-send text-primary fs-5"></i>
                <span class="fw-semibold">Soumettre un paiement</span>
            </div>
            <div class="card-body">
                <form action="{{ route('abonnement.paiement') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Plan d'abonnement *</label>
                        <select name="plan_code" class="form-select" required id="selectPlan">
                            <option value="">-- Choisir un plan --</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->code }}"
                                    data-prix="{{ $plan->prix }}"
                                    data-devise="{{ $plan->devise }}"
                                    data-mois="{{ $plan->duree_mois }}"
                                    {{ old('plan_code') === $plan->code ? 'selected' : '' }}>
                                {{ $plan->libelle }} — {{ $plan->duree_mois }} mois — {{ number_format($plan->prix, 0, ',', ' ') }} {{ $plan->devise }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Moyen de paiement *</label>
                        <select name="mode_paiement" class="form-select" required id="selectMoyen">
                            <option value="">-- Choisir --</option>
                            @foreach($moyensPaiement as $code => $infos)
                            <option value="{{ $code }}" data-numero="{{ $infos['numero'] }}" {{ old('mode_paiement') === $code ? 'selected' : '' }}>
                                {{ $infos['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Numéro à créditer --}}
                    <div id="infoNumero" class="alert alert-info py-2 d-none mb-3">
                        <i class="bx bx-phone me-2"></i>
                        Envoyez le montant au numéro : <strong id="numeroPaiement"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Référence transaction</label>
                        <input type="text" name="transaction_ref" class="form-control"
                               placeholder="N° transaction mobile money" value="{{ old('transaction_ref') }}">
                        <div class="form-text">Le numéro reçu par SMS après paiement</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note / Message</label>
                        <textarea name="owner_note" class="form-control" rows="2"
                                  placeholder="Message pour l'administrateur (optionnel)">{{ old('owner_note') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Preuve de paiement <span class="text-danger">*</span> <small class="text-muted">(photo, PDF — max 5 Mo)</small></label>
                        <div class="camera-widget" id="camWidgetPage">
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

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-send me-2"></i>Soumettre le paiement
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Colonne droite : historique paiements --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-history text-primary fs-5"></i>
                    <span class="fw-semibold">Historique des paiements</span>
                </div>
                <span class="badge bg-primary">{{ $paiements->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($paiements->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                    Aucun paiement enregistré
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Plan</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paiements as $p)
                            @php
                                $pc = ['PENDING'=>'warning','PAID'=>'success','FAILED'=>'danger'][$p->statut] ?? 'secondary';
                            @endphp
                            <tr>
                                <td><code class="small">{{ $p->reference }}</code></td>
                                <td><span class="badge bg-light text-dark border">{{ $p->plan_code }}</span></td>
                                <td class="fw-semibold">{{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</td>
                                <td><small>{{ str_replace('_', ' ', $p->mode_paiement) }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $pc }}">{{ $p->statut }}</span>
                                    @if($p->statut === 'FAILED' && $p->review_note)
                                    <div class="small text-danger mt-1">{{ $p->review_note }}</div>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $p->created_at?->format('d/m/Y') }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const selectMoyen = document.getElementById('selectMoyen');
    const infoBox     = document.getElementById('infoNumero');
    const numeroPaiement = document.getElementById('numeroPaiement');

    selectMoyen?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const num = opt.getAttribute('data-numero');
        if (num) {
            numeroPaiement.textContent = num;
            infoBox.classList.remove('d-none');
        } else {
            infoBox.classList.add('d-none');
        }
    });

    // Trigger on load if value already set
    if (selectMoyen?.value) selectMoyen.dispatchEvent(new Event('change'));
})();
</script>
@endpush
