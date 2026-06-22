@extends('layouts.app')
@section('title', 'Paiements')
@section('page-title', 'Gestion des paiements')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3"><div class="breadcrumb-title pe-3">Paiements</div><div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item active">Gestion des Paiements</li></ol></div></div>
<div class="card mb-4"><div class="card-body py-3 px-4"><div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3"><div><div class="text-uppercase text-muted small mb-1">Synthèse (Mois en cours)</div><div class="h6 mb-0">{{ now()->translatedFormat('F Y') }}</div></div><div class="d-flex flex-wrap gap-2">
@foreach([['ENCAISSEMENTS DU MOIS',$synthese['encaissementsMois'].' FCFA'],['Nombre model',$synthese['nombreModeles']],['Sortie',$synthese['nombreSorties']],['Montant total des models',$synthese['montantModeles'].' FCFA']] as [$label,$value])<div class="bg-light border rounded p-3 text-center" style="min-width:150px"><div class="text-uppercase text-muted small">{{ $label }}</div><div class="h5 mb-0">{{ is_numeric($value) ? number_format($value,0,',',' ') : $value }}</div></div>@endforeach
</div></div></div></div>

<!-- Onglets -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button type="button" class="nav-link {{ $tab == 'clients' ? 'active' : '' }}"
           onclick="window.location.href='{{ route('paiements.index', ['tab' => 'clients']) }}'">
            <i class="bx bx-user me-2"></i>Paiements Clients
        </button>
    </li>
    <li class="nav-item">
        <button type="button" class="nav-link {{ $tab == 'tailleurs' ? 'active' : '' }}"
           onclick="window.location.href='{{ route('paiements.index', ['tab' => 'tailleurs']) }}'">
            <i class="bx bx-cut me-2"></i>Paiements Tailleurs
        </button>
    </li>
</ul>

@if($tab == 'clients')
<!-- TABLEAU CLIENTS -->
<div class="card">
    <div class="card-header bg-white">
        <i class="bx bx-group text-primary me-2"></i>Paiements clients
        <button class="btn btn-sm btn-success float-end" data-bs-toggle="modal" data-bs-target="#modalPaiementClient">
            <i class="bx bx-plus"></i> Enregistrer
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Client</th><th>Contact</th><th>Total dû</th><th>Payé</th><th>Restant</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($clients as $c)
                    <tr>
                        <td><strong>{{ $c['nom'] }}</strong></td>
                        <td><small>{{ $c['contact'] }}</small></td>
                        <td>{{ number_format($c['montantTotal'], 0, ',', ' ') }} FCFA</td>
                        <td class="text-success">{{ number_format($c['montantPaye'], 0, ',', ' ') }} FCFA</td>
                        <td class="{{ $c['montantRestant'] > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($c['montantRestant'], 0, ',', ' ') }} FCFA
                        </td>
                        <td>
                            @if($c['montantRestant'] <= 0 && $c['montantTotal'] > 0)
                                <span class="badge bg-success">Soldé</span>
                            @else
                                <span class="badge bg-warning text-dark">En attente</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('paiements.recu.client', $c['id']) }}" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-receipt"></i> Reçu
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@else
<!-- TABLEAU TAILLEURS -->
<div class="card">
    <div class="card-header bg-white">
        <i class="bx bx-cut text-primary me-2"></i>Paiements tailleurs
        <button class="btn btn-sm btn-success float-end" data-bs-toggle="modal" data-bs-target="#modalPaiementTailleur">
            <i class="bx bx-plus"></i> Enregistrer
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Tailleur</th><th>Total dû</th><th>Payé</th><th>Restant</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($tailleurs as $t)
                    <tr>
                        <td><strong>{{ $t['nom'] }}</strong></td>
                        <td>{{ number_format($t['totalDu'], 0, ',', ' ') }} FCFA</td>
                        <td class="text-success">{{ number_format($t['totalPaye'], 0, ',', ' ') }} FCFA</td>
                        <td class="{{ ($t['totalDu'] - $t['totalPaye']) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format(max(0, $t['totalDu'] - $t['totalPaye']), 0, ',', ' ') }} FCFA
                        </td>
                        <td>
                            <a href="{{ route('paiements.recu.tailleur', $t['id']) }}" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-receipt"></i> Reçu
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Modal paiement client -->
<div class="modal fade" id="modalPaiementClient" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-money me-2"></i>Paiement client</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('paiements.clients.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Client *</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($clients as $c)
                            <option value="{{ $c['id'] }}">{{ $c['nom'] }} — Restant: {{ number_format($c['montantRestant'], 0) }} FCFA</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Moyen de paiement</label>
                        <select name="moyen" class="form-select">
                            <option value="ESPECES">Espèces</option>
                            <option value="MOBILE_MONEY">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal paiement tailleur -->
<div class="modal fade" id="modalPaiementTailleur" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-money me-2"></i>Paiement tailleur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('paiements.tailleurs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tailleur *</label>
                        <select name="tailleur_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($tailleurs as $t)
                            <option value="{{ $t['id'] }}">{{ $t['nom'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Moyen de paiement</label>
                        <select name="moyen" class="form-select">
                            <option value="ESPECES">Espèces</option>
                            <option value="MOBILE_MONEY">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
