@extends('layouts.app')

@section('title', 'Nouvelle affectation')
@section('page-title', 'Créer une affectation')

@push('styles')
<style>
.mesure-item{border:1.5px solid #e0e7ef;border-radius:10px;padding:10px 14px;cursor:pointer;transition:.18s;background:#fff;display:flex;align-items:center;gap:10px}
.mesure-item:hover{border-color:#0d6efd;background:#f0f5ff}
.mesure-item.selected{border-color:#0d6efd;background:#e8f0fe}
.mesure-item .check-icon{width:22px;height:22px;border-radius:50%;border:2px solid #c4cdd8;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:.18s}
.mesure-item.selected .check-icon{border-color:#0d6efd;background:#0d6efd;color:#fff}
.mesure-badge{font-size:.72rem;padding:2px 8px;border-radius:999px}
.client-card-sel{border:2px solid #dee2e6;border-radius:12px;padding:12px;cursor:pointer;transition:.18s;background:#fff}
.client-card-sel:hover{border-color:#0d6efd;background:#f8f9ff}
.client-card-sel.active{border-color:#0d6efd;background:#e8f0fe}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-xl-9">

<div class="card">
    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
        <i class="bx bx-user-check fs-5"></i>
        <span class="fw-semibold">Assigner une commande à un tailleur</span>
    </div>
    <div class="card-body">
        <form action="{{ route('affectations.store') }}" method="POST" id="formAffectation">
            @csrf

            {{-- Tailleur + date --}}
            <div class="row g-3 mb-4">
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
                    <input type="number" name="prix_tailleur" class="form-control" min="0" placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="form-control" min="{{ date('Y-m-d') }}">
                </div>
            </div>

            <hr class="my-3">

            {{-- Recherche client --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Rechercher un client</label>
                <input type="text" id="clientSearch" class="form-control" placeholder="🔍 Nom ou contact...">
            </div>

            {{-- Sélection client --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Client <span class="text-danger">*</span></label>
                <input type="hidden" name="client_id" id="selectedClientId" required>
                <div class="row g-2" id="clientList">
                    @foreach($clients as $client)
                    <div class="col-12 col-sm-6 col-lg-4 client-item"
                         data-search="{{ Str::lower($client->prenom.' '.$client->nom.' '.($client->contact??'')) }}"
                         data-id="{{ $client->id }}">
                        <div class="client-card-sel" onclick="selectClient('{{ $client->id }}', this)">
                            <div class="fw-semibold">{{ $client->prenom }} {{ $client->nom }}</div>
                            @if($client->contact)
                            <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $client->contact }}</small>
                            @endif
                            <div class="mt-1">
                                <span class="badge bg-light text-dark">{{ $client->mesures->count() }} mesure(s)</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($clients->isEmpty())
                    <div class="col-12 text-center py-3 text-muted">
                        <i class="bx bx-user-x fs-2 d-block mb-1"></i>
                        Aucun client avec des mesures non affectées.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Mesures du client sélectionné --}}
            <div id="mesuresSection" class="d-none mb-4">
                <hr class="my-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-medium mb-0">
                        Mesures à affecter <span class="text-danger">*</span>
                        <span class="badge bg-primary ms-2" id="mesuresCount">0</span>
                    </label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAll(true)">Tout</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAll(false)">Aucun</button>
                    </div>
                </div>
                <div id="mesuresList" class="d-flex flex-column gap-2"></div>
                <div id="mesuresHidden"></div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="bx bx-check-circle me-2"></i>
                    Créer <span id="btnCount"></span>
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
                'label' => ($m->type_vetement ?? 'N/A') . ($m->prix ? ' — ' . number_format($m->prix, 0) . ' FCFA' : ''),
            ];
        })->values()->toArray(),
    ];
})->keyBy('id');
@endphp

@push('scripts')
<script>
const clientsData = @json($clientsForJs);
let selectedMesures = new Set();

function selectClient(id, el) {
    document.querySelectorAll('.client-card-sel').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('selectedClientId').value = id;
    loadMesures(id);
}

function loadMesures(clientId) {
    const client = clientsData[clientId];
    const section = document.getElementById('mesuresSection');
    const list = document.getElementById('mesuresList');
    const hidden = document.getElementById('mesuresHidden');
    selectedMesures = new Set();
    hidden.innerHTML = '';
    list.innerHTML = '';

    if (!client || !client.mesures.length) {
        section.classList.add('d-none');
        return;
    }

    client.mesures.forEach(m => {
        const item = document.createElement('div');
        item.className = 'mesure-item';
        item.dataset.id = m.id;
        item.innerHTML = `
            <div class="check-icon"><i class="bx bx-check" style="font-size:.9rem"></i></div>
            <div>
                <div class="fw-semibold small">${m.label}</div>
            </div>`;
        item.addEventListener('click', () => toggleMesure(m.id, item));
        list.appendChild(item);
    });

    section.classList.remove('d-none');
    updateCount();
}

function toggleMesure(id, el) {
    if (selectedMesures.has(id)) {
        selectedMesures.delete(id);
        el.classList.remove('selected');
    } else {
        selectedMesures.add(id);
        el.classList.add('selected');
    }
    updateCount();
    syncHiddenInputs();
}

function toggleAll(select) {
    document.querySelectorAll('#mesuresList .mesure-item').forEach(item => {
        const id = item.dataset.id;
        if (select) { selectedMesures.add(id); item.classList.add('selected'); }
        else { selectedMesures.delete(id); item.classList.remove('selected'); }
    });
    updateCount();
    syncHiddenInputs();
}

function syncHiddenInputs() {
    const hidden = document.getElementById('mesuresHidden');
    hidden.innerHTML = '';
    selectedMesures.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'mesure_ids[]';
        inp.value = id;
        hidden.appendChild(inp);
    });
}

function updateCount() {
    const n = selectedMesures.size;
    document.getElementById('mesuresCount').textContent = n;
    document.getElementById('btnCount').textContent = n > 1 ? `(${n} affectations)` : '';
}

// Recherche client
document.getElementById('clientSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.client-item').forEach(el => {
        el.classList.toggle('d-none', q && !el.dataset.search.includes(q));
    });
});

// Validation avant soumission
document.getElementById('formAffectation').addEventListener('submit', function(e) {
    if (!document.getElementById('selectedClientId').value) {
        e.preventDefault();
        return alert('Veuillez sélectionner un client.');
    }
    if (selectedMesures.size === 0) {
        e.preventDefault();
        return alert('Veuillez sélectionner au moins une mesure.');
    }
});
</script>
@endpush
@endsection
