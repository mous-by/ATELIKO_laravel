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
                        @php
                            $clientRdv = $rdv->client;
                            $totalDu = $clientRdv ? (float) $clientRdv->mesures->sum('prix') : 0;
                            $montantPaye = $clientRdv ? (float) $clientRdv->paiements->where('type_paiement', 'CLIENT')->sum('montant') : 0;
                            $resteAPayer = max(0, $totalDu - $montantPaye);
                            $clientSolde = $totalDu > 0 && $resteAPayer <= 0;
                        @endphp
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
                                <div class="d-flex flex-wrap gap-1">
                                @if($rdv->statut === 'PLANIFIE')
                                <form action="{{ route('rendezvous.statut', $rdv->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="CONFIRME">
                                    <button class="btn btn-sm btn-outline-primary">RDV confirmé</button>
                                </form>
                                @endif
                                @if(in_array($rdv->statut, ['PLANIFIE', 'CONFIRME']))
                                {{-- Habit terminé avant la date de RDV : on informe le client, même s'il reste un solde. --}}
                                <button class="btn btn-sm btn-success btn-rdv-pret"
                                    data-id="{{ $rdv->id }}"
                                    data-url="{{ route('rendezvous.pret', $rdv->id) }}"
                                    title="Habit terminé — notifier le client par WhatsApp">
                                    <i class="bx bx-check-shield me-1"></i>Habit prêt
                                </button>
                                @if($resteAPayer > 0)
                                    <small class="text-danger fw-semibold align-self-center">
                                        Reste {{ number_format($resteAPayer, 0, ',', ' ') }} FCFA
                                    </small>
                                @endif
                                <form action="{{ route('rendezvous.statut', $rdv->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="ANNULE">
                                    <button class="btn btn-sm btn-outline-danger">Annuler</button>
                                </form>
                                @endif
                                @if($rdv->statut === 'PRET')
                                <form action="{{ route('rendezvous.statut', $rdv->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="TERMINE">
                                    <button class="btn btn-sm btn-outline-success">Client a récupéré</button>
                                </form>
                                @endif
                                </div>
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
            <form id="formNouveauRdv" action="{{ route('rendezvous.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Client *</label>
                        <select name="client_id" id="rdvClientId" class="form-select" required>
                            <option value="">-- Sélectionner un client --</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->prenom }} {{ $client->nom }}{{ $client->contact ? ' — '.$client->contact : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mesures du client (chargées dynamiquement) --}}
                    <div class="mb-3" id="rdvMesureWrap" style="display:none">
                        <label class="form-label fw-medium">Mesure concernée</label>
                        <select name="mesure_id" id="rdvMesureId" class="form-select">
                            <option value="">-- Aucune / Générale --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Type de rendez-vous *</label>
                        <div class="d-flex flex-wrap gap-2" id="rdvTypeChips">
                            @foreach($types as $type)
                            <input type="radio" name="type_rendezvous" id="rdvType_{{ $type }}" value="{{ $type }}" class="d-none" {{ $loop->first ? 'checked' : '' }}>
                            <label for="rdvType_{{ $type }}" class="btn btn-sm btn-outline-primary rdv-type-chip {{ $loop->first ? 'active' : '' }}">
                                {{ $type }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Date et heure *</label>
                        <input type="datetime-local" name="date_rdv" class="form-control" required
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnRdvSubmit">
                        <i class="bx bx-check-circle me-1"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
$clientsWithMesures = $clients->mapWithKeys(fn($c) => [
    $c->id => $c->mesures->map(fn($m) => [
        'id'    => $m->id,
        'label' => ($m->type_vetement ?? 'N/A') . ($m->prix ? ' — ' . number_format($m->prix, 0) . ' FCFA' : ''),
    ])->values()->toArray()
]);
@endphp

@push('scripts')
<script>
// ── Données mesures par client
const rdvClientsData = @json($clientsWithMesures);

// ── Chargement mesures lors du changement de client
document.getElementById('rdvClientId').addEventListener('change', function() {
    const clientId = this.value;
    const wrap = document.getElementById('rdvMesureWrap');
    const sel  = document.getElementById('rdvMesureId');
    sel.innerHTML = '<option value="">-- Aucune / Générale --</option>';
    if (clientId && rdvClientsData[clientId]?.length) {
        rdvClientsData[clientId].forEach(m => {
            sel.innerHTML += `<option value="${m.id}">${m.label}</option>`;
        });
        wrap.style.display = '';
    } else {
        wrap.style.display = 'none';
    }
});

// ── Chips type rendez-vous
document.querySelectorAll('.rdv-type-chip').forEach(lbl => {
    lbl.addEventListener('click', function() {
        document.querySelectorAll('.rdv-type-chip').forEach(l => l.classList.remove('active', 'btn-primary'));
        document.querySelectorAll('.rdv-type-chip').forEach(l => { l.classList.add('btn-outline-primary'); l.classList.remove('btn-primary'); });
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
    });
});

// ── Création nouveau RDV (AJAX)
document.getElementById('formNouveauRdv').addEventListener('submit', async function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnRdvSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';
    try {
        var resp = await fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        var json = await readJsonResponse(resp);
        if (!resp.ok) throw new Error(json.message || 'Erreur lors de l\'enregistrement');
        bootstrap.Modal.getInstance(document.getElementById('modalNouveauRdv'))?.hide();
        if (json.receipt && window.showReceiptPopup) {
            await window.showReceiptPopup(json.receipt);
        }
        window.location.reload();
    } catch (err) {
        swalError(err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-check-circle me-1"></i>Créer';
    }
});

// ── Boutons "Prêt à récupérer"
document.querySelectorAll('.btn-rdv-pret').forEach(function(btn) {
    btn.addEventListener('click', async function() {
        var url = this.dataset.url;
        var self = this;
        self.disabled = true;
        self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            var resp = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            });
            var json = await readJsonResponse(resp);
            if (!resp.ok) throw new Error(json.message || 'Erreur');

            if (json.receipt && window.showReceiptPopup) {
                // showReceiptPopup génère le ticket thermique + QR et propose l'envoi WhatsApp
                await window.showReceiptPopup(json.receipt);
            }
            window.location.reload();
        } catch (err) {
            swalError(err.message || 'Une erreur est survenue.');
            self.disabled = false;
            self.innerHTML = '<i class="bx bx-check-shield me-1"></i>Habit prêt';
        }
    });
});

async function readJsonResponse(resp) {
    const text = await resp.text();

    try {
        return text ? JSON.parse(text) : {};
    } catch (e) {
        throw new Error(resp.ok ? 'Réponse serveur invalide.' : 'Erreur serveur. Veuillez réessayer ou vérifier les journaux.');
    }
}
</script>
@endpush
