@extends('layouts.app')

@section('title', 'Nouvelle affectation')
@section('page-title', 'Créer une affectation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bx bx-user-check me-2"></i>Assigner une commande à un tailleur
            </div>
            <div class="card-body">
                <form action="{{ route('affectations.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select" id="selectClient" required>
                                <option value="">-- Sélectionner un client --</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->prenom }} {{ $client->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Mesure disponible <span class="text-danger">*</span></label>
                            <select name="mesure_id" class="form-select" id="selectMesure" required disabled>
                                <option value="">-- Sélectionner d'abord un client --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tailleur <span class="text-danger">*</span></label>
                            <select name="tailleur_id" class="form-select" required>
                                <option value="">-- Sélectionner un tailleur --</option>
                                @foreach($tailleurs as $tailleur)
                                <option value="{{ $tailleur->id }}">{{ $tailleur->prenom }} {{ $tailleur->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Prix tailleur (FCFA)</label>
                            <input type="number" name="prix_tailleur" class="form-control" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Date d'échéance</label>
                            <input type="date" name="date_echeance" class="form-control"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <hr class="my-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check-circle me-2"></i>Créer l'affectation
                        </button>
                        <a href="{{ route('affectations.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
$clientsForJs = $clients->map(function($c) {
    return [
        'id' => $c->id,
        'mesures' => $c->mesures->map(function($m) {
            return [
                'id'    => $m->id,
                'label' => ($m->type_vetement ?? 'N/A') . ' — ' . ($m->prix ? number_format($m->prix, 0) . ' FCFA' : 'Prix N/A'),
            ];
        })->values()->toArray(),
    ];
})->keyBy('id');
@endphp

@push('scripts')
<script>
const clientsData = @json($clientsForJs);

document.getElementById('selectClient').addEventListener('change', function() {
    const clientId = this.value;
    const selectMesure = document.getElementById('selectMesure');
    selectMesure.innerHTML = '<option value="">-- Sélectionner une mesure --</option>';

    if (clientId && clientsData[clientId]) {
        const mesures = clientsData[clientId].mesures;
        if (mesures.length === 0) {
            selectMesure.innerHTML = '<option value="">Aucune mesure disponible</option>';
        } else {
            mesures.forEach(m => {
                selectMesure.innerHTML += `<option value="${m.id}">${m.label}</option>`;
            });
            selectMesure.disabled = false;
        }
    } else {
        selectMesure.disabled = true;
    }
});
</script>
@endpush
@endsection
