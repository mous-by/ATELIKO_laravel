@extends('layouts.app')
@section('title', 'Accès suspendu')
@section('page-title', 'Accès suspendu')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        @if($user->isProprietaire() && $dernierPaiement?->statut === 'PENDING')
        {{-- PROPRIETAIRE : paiement soumis, en attente de validation --}}
        <div class="card border-0 shadow-sm text-center py-5 px-4">
            <div class="mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 p-4" style="width:90px;height:90px">
                    <i class="bx bx-time-five text-warning" style="font-size:2.8rem"></i>
                </span>
            </div>
            <h4 class="fw-bold mb-2">Paiement en cours de validation</h4>
            <p class="text-muted mb-4">
                Votre preuve de paiement a bien été reçue.<br>
                Un administrateur va la vérifier et activer votre accès sous peu.
            </p>

            {{-- Détail du paiement soumis --}}
            <div class="card bg-light border-0 rounded-3 mb-4 text-start">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Référence</span>
                        <code class="small">{{ $dernierPaiement->reference }}</code>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Plan</span>
                        <span class="fw-semibold">{{ $dernierPaiement->plan_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Montant</span>
                        <span class="fw-semibold">{{ number_format($dernierPaiement->montant, 0, ',', ' ') }} {{ $dernierPaiement->devise }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Mode</span>
                        <span>{{ str_replace('_', ' ', $dernierPaiement->mode_paiement) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Soumis le</span>
                        <span>{{ $dernierPaiement->created_at?->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="alert alert-info py-2 small mb-4">
                <i class="bx bx-info-circle me-1"></i>
                Si votre paiement est rejeté, vous pourrez en soumettre un nouveau.
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('abonnement.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-history me-1"></i>Voir l'historique
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-secondary"><i class="bx bx-log-out me-1"></i>Déconnexion</button>
                </form>
            </div>
        </div>

        @elseif($user->isProprietaire() && $dernierPaiement?->statut === 'FAILED')
        {{-- PROPRIETAIRE : paiement rejeté --}}
        <div class="card border-0 shadow-sm text-center py-5 px-4">
            <div class="mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 p-4" style="width:90px;height:90px">
                    <i class="bx bx-x-circle text-danger" style="font-size:2.8rem"></i>
                </span>
            </div>
            <h4 class="fw-bold mb-2">Paiement rejeté</h4>
            <p class="text-muted mb-3">
                Votre dernier paiement a été rejeté par l'administrateur.
            </p>
            @if($dernierPaiement->review_note)
            <div class="alert alert-danger py-2 small mb-4">
                <i class="bx bx-error-circle me-1"></i><strong>Motif :</strong> {{ $dernierPaiement->review_note }}
            </div>
            @endif
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('abonnement.index') }}" class="btn btn-primary">
                    <i class="bx bx-refresh me-1"></i>Soumettre un nouveau paiement
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-secondary"><i class="bx bx-log-out me-1"></i>Déconnexion</button>
                </form>
            </div>
        </div>

        @else
        {{-- SECRETAIRE / TAILLEUR (ou propriétaire sans paiement) --}}
        <div class="card border-0 shadow-sm text-center py-5 px-4">
            <div class="mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 p-4" style="width:90px;height:90px">
                    <i class="bx bx-lock-alt text-danger" style="font-size:2.8rem"></i>
                </span>
            </div>
            <h4 class="fw-bold mb-2">Accès suspendu</h4>
            <p class="text-muted mb-4">
                @if($abonnement)
                    L'abonnement de <strong>{{ $user->atelier?->nom ?? 'votre atelier' }}</strong> est expiré ou suspendu.
                @else
                    Aucun abonnement actif n'est associé à votre atelier.
                @endif
                <br>
                @if(!$user->isProprietaire())
                    Contactez le propriétaire de votre atelier pour renouveler l'abonnement.
                @else
                    Soumettez une preuve de paiement pour rétablir l'accès.
                @endif
            </p>

            @if($abonnement)
            <div class="card bg-light border-0 rounded-3 mb-4 text-start">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Plan</span>
                        <span class="fw-semibold">{{ $abonnement->plan?->libelle ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Statut</span>
                        <span class="badge bg-danger">{{ $abonnement->statut }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Expiré le</span>
                        <span class="text-danger fw-semibold">{{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="d-flex gap-2 justify-content-center">
                @if($user->isProprietaire())
                <a href="{{ route('abonnement.index') }}" class="btn btn-primary">
                    <i class="bx bx-credit-card me-1"></i>Renouveler l'abonnement
                </a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-secondary"><i class="bx bx-log-out me-1"></i>Déconnexion</button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
