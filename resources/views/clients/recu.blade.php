@extends('layouts.app')
@section('title', 'Reçu - ' . $client->prenom)
@section('page-title', 'Reçu client')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .main-content { margin: 0 !important; }
        .topbar { display: none !important; }
    }
    .recu-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 2rem;
        border: 2px solid #6f42c1;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-end mb-3 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2">
        <i class="bx bx-printer me-1"></i>Imprimer
    </button>
    <a href="{{ route('clients.show', $client->id) }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i>Retour
    </a>
</div>

<div class="recu-container">
    <!-- En-tête -->
    <div class="text-center border-bottom pb-3 mb-4">
        <h3 class="fw-bold text-purple" style="color: #6f42c1">🧵 {{ $atelier?->nom ?? 'ATELIKO' }}</h3>
        <p class="text-muted mb-0">{{ $atelier?->adresse }} | {{ $atelier?->telephone }}</p>
        <h5 class="mt-2 fw-bold">REÇU DE PAIEMENT</h5>
        <small class="text-muted">Date : {{ now()->format('d/m/Y') }}</small>
    </div>

    <!-- Infos client -->
    <div class="row mb-4">
        <div class="col-6">
            <p class="text-muted small mb-1">Client</p>
            <strong>{{ $client->prenom }} {{ $client->nom }}</strong>
        </div>
        <div class="col-6 text-end">
            <p class="text-muted small mb-1">Contact</p>
            <strong>{{ $client->contact ?? 'N/A' }}</strong>
        </div>
    </div>

    <!-- Détails -->
    <table class="table table-bordered">
        <tr>
            <td class="text-muted">Nombre de commandes</td>
            <td class="text-end fw-bold">{{ $client->mesures->count() }}</td>
        </tr>
        <tr>
            <td class="text-muted">Montant total</td>
            <td class="text-end fw-bold">{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td class="text-muted">Avance payée</td>
            <td class="text-end fw-bold text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr class="{{ ($montantTotal - $montantPaye) > 0 ? 'table-danger' : 'table-success' }}">
            <td class="fw-bold">Montant restant</td>
            <td class="text-end fw-bold">{{ number_format(max(0, $montantTotal - $montantPaye), 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    @if($montantPaye >= $montantTotal && $montantTotal > 0)
    <div class="text-center mt-3">
        <span class="badge bg-success px-4 py-2 fs-6">✓ SOLDÉ</span>
    </div>
    @endif

    <div class="text-center mt-4 pt-3 border-top">
        <p class="text-muted small">Merci pour votre confiance</p>
        <p class="text-muted small">Signature : ________________</p>
    </div>
</div>
@endsection
