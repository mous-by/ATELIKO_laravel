@extends('layouts.app')

@section('title', 'Nouvelle affectation')
@section('page-title', 'Créer une affectation')

@push('styles')
<style>
.aff-create-page{min-height:calc(100vh - 150px);margin:-4px -2px 0;color:#24313d}.aff-create-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:24px;border-radius:8px;background:linear-gradient(135deg,rgba(19,121,104,.94),rgba(39,68,117,.92)),url('{{ asset('assets/images/atelier.jpg') }}') center/cover;color:#fff;box-shadow:0 18px 42px rgba(25,53,83,.18);margin-bottom:18px}.aff-create-hero h1{margin:0;font-size:clamp(1.8rem,4vw,3rem);font-weight:800;letter-spacing:0}.aff-create-hero p{max-width:840px;margin:8px 0 0;color:rgba(255,255,255,.9)}
.aff-kicker{display:block;margin-bottom:4px;color:#20c997;font-size:.76rem;font-weight:800;text-transform:uppercase;letter-spacing:0}.aff-workspace{display:grid;grid-template-columns:minmax(0,1fr) 370px;gap:18px}.aff-panel{background:#fff;border:1px solid #e4eaf0;border-radius:8px;box-shadow:0 10px 30px rgba(35,52,78,.07);padding:22px}.aff-title{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin-bottom:18px}.aff-title h2{margin:0;font-size:1.22rem;font-weight:800;letter-spacing:0}.aff-step{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:5px 11px;border-radius:999px;background:#eaf0fb;color:#274475;font-size:.8rem;font-weight:800;white-space:nowrap}
.aff-form-grid{display:grid;grid-template-columns:1.2fr .8fr .9fr;gap:14px;margin-bottom:18px}.aff-field span{display:block;margin-bottom:7px;font-weight:800}.aff-field b{color:#c94d3f}.aff-search-box{position:relative;margin-bottom:14px}.aff-search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6d7883;z-index:1}.aff-search-box input{padding-left:38px;min-height:44px}
.aff-client-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;max-height:450px;overflow:auto;padding-right:4px}.client-card-sel{display:grid;grid-template-columns:40px minmax(0,1fr);gap:10px;align-items:center;min-height:92px;border:1px solid #dce5eb;border-radius:8px;padding:14px;cursor:pointer;transition:.15s;background:#fff}.client-card-sel:hover,.client-card-sel.active{border-color:#14796a;box-shadow:0 10px 24px rgba(20,121,106,.12);transform:translateY(-1px)}.client-card-sel.active{background:#f3fbf8}.client-check{display:flex;width:36px;height:36px;align-items:center;justify-content:center;border-radius:50%;background:#eaf0fb;color:#274475;font-size:1.3rem}.client-card-sel.active .client-check{background:#14796a;color:#fff}.client-card-sel strong,.client-card-sel small{display:block;overflow-wrap:anywhere}.client-card-sel small{color:#6b7782}.client-badge{display:inline-flex;margin-top:6px;padding:3px 8px;border-radius:999px;background:#fff1c7;color:#6a4f12;font-size:.72rem;font-weight:800}
.aff-selected-clients{display:grid;gap:12px;margin-top:18px}.aff-client-models{border:1px solid #e0e7ee;border-radius:8px;background:#f8fbfa;padding:14px}.aff-client-models-head{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:10px}.aff-client-models-head strong{font-size:1rem}.aff-client-models-actions{display:flex;gap:8px;flex-wrap:wrap}.aff-model-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:10px}.aff-model-card{display:grid;grid-template-columns:54px minmax(0,1fr) 30px;gap:10px;align-items:center;padding:10px;border:1px solid #dce5eb;border-radius:8px;background:#fff;cursor:pointer}.aff-model-card.selected{border-color:#14796a;background:#f3fbf8}.aff-model-card img{width:54px;height:54px;border-radius:8px;object-fit:cover;background:#eef1f4}.aff-model-card strong,.aff-model-card small{display:block;overflow-wrap:anywhere}.aff-model-card small{color:#6b7782}.aff-model-check{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eaf0fb;color:#274475}.aff-model-card.selected .aff-model-check{background:#14796a;color:#fff}
.aff-summary-line,.aff-summary-total,.aff-selected-item{display:flex;justify-content:space-between;gap:12px;align-items:center}.aff-summary-line{padding:11px 0;border-bottom:1px solid #e1e8ed}.aff-summary-line span,.aff-summary-total span{color:#6a7580}.aff-summary-line strong,.aff-summary-total strong,.aff-selected-item strong{text-align:right}.aff-summary-total{margin:16px 0;padding:15px;border-radius:8px;background:#24313d;color:#fff}.aff-summary-total span{color:rgba(255,255,255,.72)}.aff-selected-list{display:grid;gap:8px;max-height:260px;overflow:auto;margin-bottom:16px}.aff-selected-list p{margin:0;color:#6b7782}.aff-selected-item{padding:10px;border-radius:8px;background:#fff;border:1px solid #e3e9ef}.aff-selected-item span{min-width:0;overflow-wrap:anywhere}
.aff-empty{min-height:190px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;text-align:center;color:#6b7782;border:1px dashed #cfd9e1;border-radius:8px;background:#fbfcfd;padding:24px}.aff-empty i{font-size:2.2rem;color:#14796a}.aff-empty strong{color:#2c3742}.aff-help{display:flex;gap:10px;align-items:flex-start;border:1px solid #cfe8df;background:#f2fbf7;color:#24594c;border-radius:8px;padding:12px;margin-bottom:14px}.aff-help i{font-size:1.4rem}.aff-confirm-btn{min-height:48px;font-weight:900}
html.dark-theme .aff-panel,html.dark-theme .client-card-sel,html.dark-theme .aff-client-models,html.dark-theme .aff-model-card,html.dark-theme .aff-selected-item{background:#242632;border-color:#3a3d50;color:#e4e5e6}html.dark-theme .aff-empty,html.dark-theme .aff-help{background:#242632;border-color:#3a3d50}html.dark-theme .aff-title h2,html.dark-theme .aff-summary-line strong,html.dark-theme .aff-empty strong{color:#e4e5e6}
@media(max-width:1200px){.aff-workspace{grid-template-columns:1fr}.aff-form-grid{grid-template-columns:1fr 1fr}}@media(max-width:768px){.aff-create-hero{flex-direction:column;padding:18px}.aff-panel{padding:18px}.aff-form-grid,.aff-client-grid,.aff-model-grid{grid-template-columns:1fr}.aff-create-page{margin:0}}
.aff-create-hero{background:linear-gradient(135deg,rgba(255,247,204,.98),rgba(255,255,255,.96)),url("{{ asset("assets/images/ateliko-icon-192.png") }}") right 22px center/96px no-repeat!important;color:#1f1f1f!important;border:1px solid rgba(214,185,21,.45);box-shadow:0 14px 34px rgba(31,31,31,.10)!important}.aff-create-hero h1{color:#171717!important}.aff-create-hero p{color:#424242!important}.aff-create-hero .aff-kicker{color:#b89700!important}.aff-create-hero .btn-light{background:#1f1f1f!important;color:#fff!important;border-color:#1f1f1f!important}.aff-create-hero .btn-light:hover{background:#d6b915!important;color:#171717!important;border-color:#d6b915!important}@media(max-width:768px){.aff-create-hero{background:linear-gradient(135deg,rgba(255,247,204,.98),rgba(255,255,255,.96))!important}}</style>
@endpush

@section('content')
<div class="aff-create-page">
    <div class="aff-create-hero">
        <div>
            <span class="aff-kicker">Nouvelle affectation</span>
            <h1>Donner le travail au tailleur</h1>
            <p>Touchez un client pour sélectionner automatiquement ses modèles. Vous pouvez choisir plusieurs clients, retirer un modèle, puis confirmer en une seule fois.</p>
        </div>
        <a href="{{ route('affectations.index') }}" class="btn btn-light"><i class="bx bx-arrow-back me-1"></i>Retour au suivi</a>
    </div>

    <form action="{{ route('affectations.store') }}" method="POST" id="formAffectation">
        @csrf
        <div id="selectedMesuresHidden"></div>
        <div class="aff-workspace">
            <section class="aff-panel">
                <div class="aff-title">
                    <div><span class="aff-kicker">Étape 1</span><h2>Choisir le tailleur</h2></div>
                    <span class="aff-step">Simple</span>
                </div>

                <div class="aff-form-grid">
                    <label class="aff-field">
                        <span>Tailleur <b>*</b></span>
                        <select name="tailleur_id" id="tailleurSelect" class="form-select" required>
                            <option value="">Choisir le tailleur</option>
                            @foreach($tailleurs as $tailleur)
                            <option value="{{ $tailleur->id }}">{{ $tailleur->prenom }} {{ $tailleur->nom }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="aff-field">
                        <span>Prix tailleur</span>
                        <input type="number" name="prix_tailleur" id="prixTailleur" class="form-control" min="0" step="500" placeholder="0">
                    </label>
                    <label class="aff-field">
                        <span>Date limite</span>
                        <input type="date" name="date_echeance" id="dateEcheance" class="form-control" min="{{ date('Y-m-d') }}">
                    </label>
                </div>

                <div class="aff-help">
                    <i class="bx bx-hand-up"></i>
                    <div><strong>Mode facile :</strong> cliquez sur le nom d'un client. Tous ses modèles non affectés sont cochés automatiquement.</div>
                </div>

                <div class="aff-title mb-2">
                    <div><span class="aff-kicker">Étape 2</span><h2>Choisir les clients</h2></div>
                </div>
                <label class="aff-search-box">
                    <i class="bx bx-search"></i>
                    <input type="text" id="clientSearch" class="form-control" placeholder="Nom ou téléphone du client">
                </label>

                <div class="aff-client-grid" id="clientList">
                    @foreach($clients as $client)
                    <div class="client-item"
                         data-search="{{ Str::lower($client->prenom.' '.$client->nom.' '.($client->contact??'').' '.$client->mesures->pluck('modele_nom')->join(' ').' '.$client->mesures->pluck('type_vetement')->join(' ')) }}"
                         data-id="{{ $client->id }}">
                        <div class="client-card-sel" id="client-card-{{ $client->id }}" role="button" tabindex="0" onclick="toggleClient('{{ $client->id }}')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleClient('{{ $client->id }}')}">
                            <span class="client-check"><i class="bx bx-plus"></i></span>
                            <span>
                                <strong>{{ $client->prenom }} {{ $client->nom }}</strong>
                                <small><i class="bx bx-phone me-1"></i>{{ $client->contact ?: 'Sans contact' }}</small>
                                <span class="client-badge">{{ $client->mesures->count() }} modèle(s)</span>
                            </span>
                        </div>
                    </div>
                    @endforeach
                    @if($clients->isEmpty())
                    <div class="aff-empty">
                        <i class="bx bx-user-x"></i>
                        <strong>Aucun client prêt</strong>
                        <span>Aucun client avec modèle non affecté.</span>
                    </div>
                    @endif
                </div>

                <div id="selectedClientsWrap" class="aff-selected-clients"></div>
            </section>

            <aside class="aff-panel">
                <div class="aff-title">
                    <div><span class="aff-kicker">Résumé</span><h2>À confirmer</h2></div>
                </div>
                <div class="aff-summary-line"><span>Tailleur</span><strong id="summaryTailleur">Non choisi</strong></div>
                <div class="aff-summary-line"><span>Clients</span><strong id="summaryClients">0</strong></div>
                <div class="aff-summary-line"><span>Modèles</span><strong id="summaryModels">0</strong></div>
                <div class="aff-summary-line"><span>Date limite</span><strong id="summaryDate">Non définie</strong></div>
                <div class="aff-summary-total"><span>Total tailleur</span><strong id="summaryPrice">0 FCFA</strong></div>
                <div class="aff-selected-list" id="summaryList">
                    <p>Choisissez un client pour préparer l'affectation.</p>
                </div>
                <button type="submit" class="btn btn-primary w-100 aff-confirm-btn" id="btnSubmit">
                    <i class="bx bx-check-circle me-1"></i>Affecter au tailleur
                </button>
                <a href="{{ route('affectations.index') }}" class="btn btn-outline-secondary w-100 mt-2">Annuler</a>
            </aside>
        </div>
    </form>
</div>

@php
$clientsForJs = $clients->map(function($c) {
    return [
        'id' => $c->id,
        'name' => trim($c->prenom.' '.$c->nom),
        'contact' => $c->contact,
        'mesures' => $c->mesures->map(function($m) {
            $photo = $m->photo_path ?: ($m->modeleReference?->photo_path);
            return [
                'id' => $m->id,
                'type' => $m->type_vetement ?: 'Modèle',
                'name' => $m->modele_nom ?: ($m->modeleReference?->nom ?: ($m->type_vetement ?: 'Modèle')),
                'price' => $m->prix,
                'photo' => $photo,
                'habitPhoto' => $m->habit_photo_path,
            ];
        })->values()->toArray(),
    ];
})->keyBy('id');
@endphp

@push('scripts')
<script>
const clientsData = @json($clientsForJs);
const storageBase = @json(rtrim(asset(''), '/'));
const fallbackModel = @json(asset('assets/images/model4.jpg'));
let selectedClients = new Set();
let selectedMesures = new Set();

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function mediaUrl(path) {
    return path ? `${storageBase}/${path}` : fallbackModel;
}

function money(value) {
    return `${Number(value || 0).toLocaleString('fr-FR')} FCFA`;
}

function notify(icon, title, text) {
    return Swal.fire({ icon, title, text, confirmButtonText: 'Compris' });
}

function toggleClient(clientId) {
    if (selectedClients.has(clientId)) {
        removeClient(clientId);
    } else {
        selectedClients.add(clientId);
        (clientsData[clientId]?.mesures || []).forEach(m => selectedMesures.add(m.id));
    }
    renderAll();
}

function removeClient(clientId) {
    selectedClients.delete(clientId);
    (clientsData[clientId]?.mesures || []).forEach(m => selectedMesures.delete(m.id));
}

function toggleMeasure(clientId, mesureId) {
    if (selectedMesures.has(mesureId)) selectedMesures.delete(mesureId);
    else selectedMesures.add(mesureId);

    const stillHasMeasure = (clientsData[clientId]?.mesures || []).some(m => selectedMesures.has(m.id));
    if (stillHasMeasure) selectedClients.add(clientId);
    else selectedClients.delete(clientId);
    renderAll();
}

function selectClientMeasures(clientId, shouldSelect) {
    if (shouldSelect) {
        selectedClients.add(clientId);
        (clientsData[clientId]?.mesures || []).forEach(m => selectedMesures.add(m.id));
    } else {
        removeClient(clientId);
    }
    renderAll();
}

function renderClientCards() {
    document.querySelectorAll('.client-card-sel').forEach(card => {
        const clientId = card.id.replace('client-card-', '');
        const active = selectedClients.has(clientId);
        card.classList.toggle('active', active);
        const icon = card.querySelector('.client-check i');
        if (icon) icon.className = active ? 'bx bx-check' : 'bx bx-plus';
    });
}

function renderSelectedClients() {
    const wrap = document.getElementById('selectedClientsWrap');
    const ids = Array.from(selectedClients);
    if (!ids.length) {
        wrap.innerHTML = '';
        return;
    }

    wrap.innerHTML = ids.map(clientId => {
        const client = clientsData[clientId];
        const rows = (client?.mesures || []).map(m => {
            const checked = selectedMesures.has(m.id);
            return `
                <div class="aff-model-card ${checked ? 'selected' : ''}" role="button" tabindex="0" onclick="toggleMeasure('${clientId}', '${m.id}')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleMeasure('${clientId}', '${m.id}')}">
                    <img src="${mediaUrl(m.photo || m.habitPhoto)}" alt="">
                    <span>
                        <strong>${escapeHtml(m.name)}</strong>
                        <small>${escapeHtml(m.type)}${m.price ? ' - ' + money(m.price) : ''}</small>
                    </span>
                    <span class="aff-model-check"><i class="bx ${checked ? 'bx-check' : 'bx-plus'}"></i></span>
                </div>`;
        }).join('');

        return `
            <div class="aff-client-models">
                <div class="aff-client-models-head">
                    <strong>${escapeHtml(client?.name || 'Client')}</strong>
                    <div class="aff-client-models-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectClientMeasures('${clientId}', true)">Tout cocher</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectClientMeasures('${clientId}', false)">Retirer</button>
                    </div>
                </div>
                <div class="aff-model-grid">${rows}</div>
            </div>`;
    }).join('');
}

function syncHiddenInputs() {
    const hidden = document.getElementById('selectedMesuresHidden');
    hidden.innerHTML = '';
    selectedMesures.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_mesures[]';
        input.value = id;
        hidden.appendChild(input);
    });
}

function updateSummary() {
    const tailleur = document.getElementById('tailleurSelect');
    document.getElementById('summaryTailleur').textContent = tailleur.value ? tailleur.options[tailleur.selectedIndex].text : 'Non choisi';
    document.getElementById('summaryClients').textContent = selectedClients.size;
    document.getElementById('summaryModels').textContent = selectedMesures.size;
    document.getElementById('summaryDate').textContent = document.getElementById('dateEcheance').value || 'Non définie';
    document.getElementById('summaryPrice').textContent = money(document.getElementById('prixTailleur').value);

    const selectedRows = [];
    selectedClients.forEach(clientId => {
        const client = clientsData[clientId];
        (client?.mesures || []).filter(m => selectedMesures.has(m.id)).forEach(m => {
            selectedRows.push(`<div class="aff-selected-item"><span>${escapeHtml(client.name)}<br><small class="text-muted">${escapeHtml(m.name)} - ${escapeHtml(m.type)}</small></span><strong>Choisi</strong></div>`);
        });
    });
    document.getElementById('summaryList').innerHTML = selectedRows.length ? selectedRows.join('') : '<p>Choisissez un client pour préparer l\'affectation.</p>';
}

function renderAll() {
    renderClientCards();
    renderSelectedClients();
    syncHiddenInputs();
    updateSummary();
}

document.getElementById('clientSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.client-item').forEach(el => {
        el.classList.toggle('d-none', q && !el.dataset.search.includes(q));
    });
});

['tailleurSelect', 'prixTailleur', 'dateEcheance'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateSummary);
    document.getElementById(id).addEventListener('change', updateSummary);
});

document.getElementById('formAffectation').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!document.getElementById('tailleurSelect').value) {
        return notify('warning', 'Tailleur manquant', 'Veuillez choisir le tailleur qui va faire le travail.');
    }
    if (selectedMesures.size === 0) {
        return notify('warning', 'Aucun modèle choisi', 'Touchez au moins un client. Ses modèles seront cochés automatiquement.');
    }

    const result = await Swal.fire({
        icon: 'question',
        title: 'Confirmer l\'affectation ?',
        html: `<b>${selectedMesures.size}</b> modèle(s) pour <b>${selectedClients.size}</b> client(s).`,
        showCancelButton: true,
        confirmButtonText: 'Oui, affecter',
        cancelButtonText: 'Annuler'
    });
    if (!result.isConfirmed) return;
    syncHiddenInputs();
    this.submit();
});

renderAll();
</script>
@endpush
@endsection
