@extends('layouts.app')

@section('title', 'Affectations')
@section('page-title', 'Gestion des affectations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-primary fs-6">{{ $affectations->total() }} affectation(s)</span>
    </div>
    @if(!Auth::user()->isTailleur())
    <a href="{{ route('affectations.create') }}" class="btn btn-primary">
        <i class="bx bx-plus-circle me-2"></i>Nouvelle affectation
    </a>
    @endif
</div>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="statut" class="form-select form-select-sm" style="width: auto;">
                <option value="">Tous les statuts</option>
                @foreach($statuts as $s)
                <option value="{{ $s }}" {{ request('statut') == $s ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $s) }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filtrer</button>
            @if(request('statut'))
                <a href="{{ route('affectations.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($affectations->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bx bx-user-check fs-1"></i>
                <h5 class="mt-3">Aucune affectation</h5>
                @if(!Auth::user()->isTailleur())
                <a href="{{ route('affectations.create') }}" class="btn btn-primary mt-2">Créer la première</a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Tailleur</th>
                            <th>Statut</th>
                            <th>Échéance</th>
                            <th>Prix tailleur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($affectations as $aff)
                        <tr>
                            <td>
                                <strong>{{ $aff->client?->prenom }} {{ $aff->client?->nom }}</strong>
                                <br><small class="text-muted">{{ $aff->client?->contact }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $aff->mesure?->type_vetement ?? '—' }}</span></td>
                            <td>{{ $aff->tailleur?->prenom }} {{ $aff->tailleur?->nom }}</td>
                            <td>@include('partials.badge-statut', ['statut' => $aff->statut])</td>
                            <td>
                                @if($aff->date_echeance)
                                    <span class="{{ $aff->date_echeance < now() && !in_array($aff->statut, ['TERMINE', 'VALIDE', 'ANNULE']) ? 'text-danger' : '' }}">
                                        {{ $aff->date_echeance->format('d/m/Y') }}
                                    </span>
                                @else —
                                @endif
                            </td>
                            <td>{{ $aff->prix_tailleur ? number_format($aff->prix_tailleur, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($aff->statut === 'EN_ATTENTE')
                                            <li>
                                                <form action="{{ route('affectations.statut', $aff->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="statut" value="EN_COURS">
                                                    <button class="dropdown-item text-primary">▶ Commencer</button>
                                                </form>
                                            </li>
                                        @endif
                                        @if($aff->statut === 'EN_COURS')
                                            <li>
                                                <form action="{{ route('affectations.statut', $aff->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="statut" value="TERMINE">
                                                    <button class="dropdown-item text-success">✓ Terminer</button>
                                                </form>
                                            </li>
                                        @endif
                                        @if($aff->statut === 'TERMINE' && !Auth::user()->isTailleur())
                                            <li>
                                                <form action="{{ route('affectations.statut', $aff->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="statut" value="VALIDE">
                                                    <button class="dropdown-item text-info">★ Valider</button>
                                                </form>
                                            </li>
                                        @endif
                                        @if(!in_array($aff->statut, ['VALIDE', 'ANNULE']) && !Auth::user()->isTailleur())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('affectations.destroy', $aff->id) }}" method="POST"
                                                      data-confirm="Supprimer cette affectation ?"
                                                      data-confirm-text="Cette action est irréversible."
                                                      data-confirm-btn="Supprimer">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i>Supprimer</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center p-3">
                {{ $affectations->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
