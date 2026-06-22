@extends('layouts.app')
@section('title', 'Rendez-vous')
@section('page-title', 'Gestion des rendez-vous')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-2">
        <a href="{{ route('rendezvous.index', ['filter' => 'aujourd_hui']) }}"
           class="btn btn-sm {{ request('filter') == 'aujourd_hui' ? 'btn-primary' : 'btn-outline-primary' }}">
            Aujourd'hui
        </a>
        <a href="{{ route('rendezvous.index', ['filter' => 'a_venir']) }}"
           class="btn btn-sm {{ request('filter') == 'a_venir' ? 'btn-success' : 'btn-outline-success' }}">
            À venir
        </a>
        <a href="{{ route('rendezvous.index') }}"
           class="btn btn-sm {{ !request('filter') ? 'btn-secondary' : 'btn-outline-secondary' }}">
            Tous
        </a>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNouveauRdv">
        <i class="bx bx-calendar-plus me-2"></i>Nouveau RDV
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($rendezvous->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bx bx-calendar-x fs-1"></i>
                <h5 class="mt-3">Aucun rendez-vous</h5>
                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalNouveauRdv">
                    Ajouter un RDV
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Heure</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rendezvous as $rdv)
                        <tr class="{{ $rdv->date_rdv->isToday() ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $rdv->date_rdv->format('d/m/Y') }}</strong>
                                <br><small class="text-muted">{{ $rdv->date_rdv->format('H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $rdv->client?->prenom }} {{ $rdv->client?->nom }}</strong>
                                <br><small class="text-muted">{{ $rdv->client?->contact }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $rdv->type_rendezvous }}</span></td>
                            <td>@include('partials.badge-statut', ['statut' => $rdv->statut])</td>
                            <td><small class="text-muted">{{ Str::limit($rdv->notes, 40) }}</small></td>
                            <td>
                                @if($rdv->statut === 'PLANIFIE')
                                <form action="{{ route('rendezvous.statut', $rdv->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="CONFIRME">
                                    <button class="btn btn-sm btn-outline-primary">Confirmer</button>
                                </form>
                                @endif
                                @if(in_array($rdv->statut, ['PLANIFIE', 'CONFIRME']))
                                <form action="{{ route('rendezvous.statut', $rdv->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="TERMINE">
                                    <button class="btn btn-sm btn-outline-success ms-1">Terminer</button>
                                </form>
                                <form action="{{ route('rendezvous.statut', $rdv->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="ANNULE">
                                    <button class="btn btn-sm btn-outline-danger ms-1">Annuler</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-center">
                {{ $rendezvous->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Nouveau RDV -->
<div class="modal fade" id="modalNouveauRdv" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-calendar-plus me-2"></i>Nouveau rendez-vous</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rendezvous.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Client *</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Sélectionner un client --</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->prenom }} {{ $client->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Type de rendez-vous *</label>
                        <select name="type_rendezvous" class="form-select" required>
                            @foreach($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Date et heure *</label>
                        <input type="datetime-local" name="date_rdv" class="form-control" required
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check-circle me-1"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
