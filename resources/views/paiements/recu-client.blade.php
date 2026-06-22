@extends('layouts.app')
@section('title', 'Reçu - ' . $client->prenom)
@section('page-title', 'Reçu de paiement client')

@push('styles')
<style>
@media print { .no-print { display: none !important; } .topbar,.sidebar { display: none !important; } .main-content { margin: 0 !important; } }
.recu-box { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border: 2px solid #6f42c1; border-radius: 12px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-end mb-3 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2"><i class="bx bx-printer"></i> Imprimer</button>
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Retour</a>
</div>

<div class="recu-box">
    <div class="text-center border-bottom pb-3 mb-3">
        <h3 class="fw-bold" style="color:#6f42c1">🧵 {{ $client->atelier?->nom ?? 'ATELIKO' }}</h3>
        <h5 class="mt-1">REÇU DE PAIEMENT CLIENT</h5>
        <small class="text-muted">Imprimé le {{ now()->format('d/m/Y à H:i') }}</small>
    </div>
    <div class="row mb-3">
        <div class="col-6"><p class="text-muted mb-0 small">Client</p><strong>{{ $client->prenom }} {{ $client->nom }}</strong></div>
        <div class="col-6 text-end"><p class="text-muted mb-0 small">Contact</p><strong>{{ $client->contact }}</strong></div>
    </div>
    <table class="table table-bordered">
        <tr><td>Nombre de commandes</td><td class="text-end fw-bold">{{ $client->mesures->count() }}</td></tr>
        <tr><td>Montant total</td><td class="text-end fw-bold">{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</td></tr>
        <tr class="table-success"><td>Montant payé</td><td class="text-end fw-bold text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</td></tr>
        <tr class="{{ ($montantTotal-$montantPaye)>0 ? 'table-danger' : 'table-success' }}">
            <td class="fw-bold">Restant à payer</td>
            <td class="text-end fw-bold">{{ number_format(max(0,$montantTotal-$montantPaye), 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>
    @if($montantPaye >= $montantTotal && $montantTotal > 0)
    <div class="text-center"><span class="badge bg-success fs-5 px-4 py-2">✓ SOLDÉ</span></div>
    @endif
    <div class="text-center mt-4 pt-3 border-top">
        <p class="small text-muted">Merci pour votre confiance | Signature : ________________</p>
    </div>
</div>
@endsection
