@extends('layouts.app')
@section('title', 'Reçu tailleur')
@section('page-title', 'Reçu tailleur')

@push('styles')
<style>
@media print { .no-print { display: none !important; } .topbar,.sidebar { display: none !important; } .main-content { margin: 0 !important; } }
.recu-box { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border: 2px solid #0d6efd; border-radius: 12px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-end mb-3 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2"><i class="bx bx-printer"></i> Imprimer</button>
    <a href="{{ route('paiements.index', ['tab'=>'tailleurs']) }}" class="btn btn-outline-secondary">Retour</a>
</div>

<div class="recu-box">
    <div class="text-center border-bottom pb-3 mb-3">
        <h3 class="fw-bold text-primary">🧵 ATELIKO</h3>
        <h5>REÇU DE PAIEMENT TAILLEUR</h5>
        <small class="text-muted">{{ now()->format('d/m/Y à H:i') }}</small>
    </div>
    <div class="mb-3">
        <p class="text-muted mb-0 small">Tailleur</p>
        <strong class="fs-5">{{ $tailleur->prenom }} {{ $tailleur->nom }}</strong>
    </div>
    <table class="table table-bordered">
        <tr><td>Total dû</td><td class="text-end fw-bold">{{ number_format($totalDu, 0, ',', ' ') }} FCFA</td></tr>
        <tr class="table-success"><td>Total payé</td><td class="text-end fw-bold text-success">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</td></tr>
        <tr class="{{ ($totalDu-$totalPaye)>0 ? 'table-danger' : 'table-success' }}">
            <td class="fw-bold">Restant</td>
            <td class="text-end fw-bold">{{ number_format(max(0,$totalDu-$totalPaye), 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>
    @if($paiements->isNotEmpty())
    <h6 class="mt-3">Historique des paiements</h6>
    <table class="table table-sm">
        <thead><tr><th>Date</th><th>Montant</th><th>Moyen</th></tr></thead>
        <tbody>
        @foreach($paiements as $p)
        <tr>
            <td>{{ $p->date_paiement?->format('d/m/Y') }}</td>
            <td>{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
            <td>{{ $p->moyen }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
    <div class="text-center mt-4 border-top pt-3">
        <p class="small text-muted">Signature : ________________</p>
    </div>
</div>
@endsection
