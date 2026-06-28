@extends('layouts.app')
@section('title', 'Paiements')
@section('page-title', 'Gestion des paiements')

@push('styles')
<style>
.client-pay-card{border-radius:12px;border:1px solid #e0e0e0;transition:box-shadow .2s,transform .2s;background:#fff}
.client-pay-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.1);transform:translateY(-3px)}
.montant-restant{font-size:1.45rem;font-weight:800;line-height:1.1}
.progress-pay{height:8px;border-radius:4px;background:#e9ecef}
.badge-solde{background:#198754;color:#fff;font-size:.75rem;padding:4px 10px;border-radius:20px}
.badge-attente{background:#ffc107;color:#000;font-size:.75rem;padding:4px 10px;border-radius:20px}
.badge-vide{background:#6c757d;color:#fff;font-size:.75rem;padding:4px 10px;border-radius:20px}
.btn-moyen{border:2px solid #dee2e6;border-radius:8px;padding:8px 12px;font-size:13px;cursor:pointer;background:#fff;transition:all .2s}
.btn-moyen.selected{border-color:#198754;background:#d1e7dd;color:#0a3622;font-weight:600}
</style>
@endpush

@section('content')

{{-- Synthèse --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#198754,#20c997)">
            <div class="card-body py-3 text-center">
                <i class="bx bx-trending-up fs-4 mb-1 d-block"></i>
                <div class="small">Encaissements du mois</div>
                <div class="fw-bold fs-6 mt-1">{{ number_format($synthese['encaissementsMois'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#0d6efd,#6ea8fe)">
            <div class="card-body py-3 text-center">
                <i class="bx bx-closet fs-4 mb-1 d-block"></i>
                <div class="small">Modèles enregistrés</div>
                <div class="fw-bold fs-6 mt-1">{{ $synthese['nombreModeles'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#0dcaf0,#6edff6)">
            <div class="card-body py-3 text-center">
                <i class="bx bx-export fs-4 mb-1 d-block"></i>
                <div class="small">Sorties ce mois</div>
                <div class="fw-bold fs-6 mt-1" id="sortieDuMois">{{ $synthese['nombreSorties'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 h-100" style="background:linear-gradient(135deg,#ffc107,#ffda6a)">
            <div class="card-body py-3 text-center">
                <i class="bx bx-money fs-4 mb-1 d-block"></i>
                <div class="small">Total valeur modèles</div>
                <div class="fw-bold fs-6 mt-1">{{ number_format($synthese['montantModeles'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
</div>

{{-- Onglets --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button type="button" class="nav-link {{ $tab=='clients'?'active':'' }}"
            onclick="window.location.href='{{ route('paiements.index',['tab'=>'clients']) }}'">
            <i class="bx bx-user me-2"></i>Clients
        </button>
    </li>
    <li class="nav-item">
        <button type="button" class="nav-link {{ $tab=='tailleurs'?'active':'' }}"
            onclick="window.location.href='{{ route('paiements.index',['tab'=>'tailleurs']) }}'">
            <i class="bx bx-cut me-2"></i>Tailleurs
        </button>
    </li>
</ul>

@if($tab == 'clients')

{{-- Barre recherche + bouton + chips filtres --}}
<div class="d-flex gap-2 mb-2 align-items-center flex-wrap">
    <input type="text" id="searchClient" class="form-control" placeholder="🔍 Rechercher un client par nom ou contact...">
    <button class="btn btn-success text-nowrap" onclick="openPayModal()">
        <i class="bx bx-plus me-1"></i>Paiement
    </button>
</div>
<div class="d-flex gap-2 mb-3 flex-wrap">
    <button class="btn btn-sm btn-secondary filter-chip active" data-filter="tous">Tous</button>
    <button class="btn btn-sm btn-outline-warning filter-chip" data-filter="a_regler">À régler</button>
    <button class="btn btn-sm btn-outline-success filter-chip" data-filter="solde">✓ Soldés</button>
    <button class="btn btn-sm btn-outline-secondary filter-chip" data-filter="vide">Sans commande</button>
</div>

{{-- Grille de cartes clients --}}
<div class="row g-3" id="clientCards">
    @forelse($clients as $c)
    @php
        $pct    = $c['montantTotal'] > 0 ? min(100, round(($c['montantPaye']/$c['montantTotal'])*100)) : 0;
        $solde  = $c['montantRestant'] <= 0 && $c['montantTotal'] > 0;
        $vide   = $c['montantTotal'] <= 0;
    @endphp
    <div class="col-12 col-md-6 col-xl-4 client-card-wrap"
         data-search="{{ Str::lower($c['nom'].' '.($c['contact']??'')) }}"
         data-solde="{{ $solde ? '1' : '0' }}"
         data-vide="{{ $vide ? '1' : '0' }}">
        <div class="card client-pay-card h-100">
            <div class="card-body d-flex flex-column">

                {{-- En-tête --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold fs-6">{{ $c['nom'] }}</div>
                        <small class="text-muted">
                            <i class="bx bx-phone me-1"></i>{{ $c['contact'] ?: '—' }}
                        </small>
                    </div>
                    @if($vide)
                        <span class="badge-vide">Aucune commande</span>
                    @elseif($solde)
                        <span class="badge-solde">✓ Soldé</span>
                    @else
                        <span class="badge-attente">À régler</span>
                    @endif
                </div>

                {{-- Barre progression --}}
                @if(!$vide)
                <div class="progress progress-pay mb-1">
                    <div class="progress-bar {{ $solde?'bg-success':'bg-warning' }}" style="width:{{ $pct }}%"></div>
                </div>
                <small class="text-muted">{{ $pct }}% réglé</small>
                @endif

                {{-- Montants --}}
                <div class="mt-auto pt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Total commandes</span>
                        <span class="fw-semibold">{{ number_format($c['montantTotal'],0,',',' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Déjà payé</span>
                        <span class="fw-semibold text-success">{{ number_format($c['montantPaye'],0,',',' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="fw-bold small">Reste à payer</span>
                        <span class="montant-restant {{ $c['montantRestant']>0?'text-danger':'text-success' }}">
                            {{ number_format($c['montantRestant'],0,',',' ') }}&nbsp;<small style="font-size:13px;font-weight:600">FCFA</small>
                        </span>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if(!$solde && !$vide)
                    <button class="btn btn-success btn-sm flex-fill btn-payer"
                        data-id="{{ $c['id'] }}"
                        data-nom="{{ $c['nom'] }}"
                        data-total="{{ $c['montantTotal'] }}"
                        data-paye="{{ $c['montantPaye'] }}"
                        data-restant="{{ $c['montantRestant'] }}">
                        <i class="bx bx-money me-1"></i>Payer
                    </button>
                    @endif
                    @if($c['estSolde'] && $c['nbMesuresSansLivraison'] > 0)
                    <button class="btn btn-warning btn-sm flex-fill btn-livrer"
                        data-id="{{ $c['id'] }}"
                        data-nom="{{ $c['nom'] }}"
                        data-url="{{ route('paiements.clients.sortie', $c['id']) }}"
                        title="Marquer les habits comme livrés au client">
                        <i class="bx bx-package me-1"></i>Livré
                    </button>
                    @endif
                    <button class="btn btn-outline-primary btn-sm flex-fill btn-voir-recu"
                        data-url="{{ route('paiements.recu.client', $c['id']) }}"
                        title="Voir et partager le reçu">
                        <i class="bx bx-receipt me-1"></i>Reçu
                    </button>
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bx bx-user-x fs-1 d-block mb-2"></i>
        Aucun client avec des commandes.
    </div>
    @endforelse
</div>

@else

{{-- TAB TAILLEURS --}}
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPaiementTailleur">
        <i class="bx bx-plus me-1"></i>Nouveau paiement tailleur
    </button>
</div>
<div class="row g-3">
    @forelse($tailleurs as $t)
    @php
        $restT = max(0,$t['totalDu']-$t['totalPaye']);
        $pctT  = $t['totalDu']>0 ? min(100,round(($t['totalPaye']/$t['totalDu'])*100)) : 0;
        $soldeT= $restT<=0 && $t['totalDu']>0;
        $videT = $t['totalDu']<=0;
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card client-pay-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="fw-bold fs-6">{{ $t['nom'] }}</div>
                    @if($videT)
                        <span class="badge-vide">Aucune affectation</span>
                    @elseif($soldeT)
                        <span class="badge-solde">✓ Soldé</span>
                    @else
                        <span class="badge-attente">À payer</span>
                    @endif
                </div>
                @if(!$videT)
                <div class="progress progress-pay mb-1">
                    <div class="progress-bar {{ $soldeT?'bg-success':'bg-info' }}" style="width:{{ $pctT }}%"></div>
                </div>
                <small class="text-muted">{{ $pctT }}% payé</small>
                @endif
                <div class="mt-auto pt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Total à payer</span>
                        <span class="fw-semibold">{{ number_format($t['totalDu'],0,',',' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Déjà versé</span>
                        <span class="fw-semibold text-success">{{ number_format($t['totalPaye'],0,',',' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="fw-bold small">Reste à verser</span>
                        <span class="montant-restant {{ $restT>0?'text-danger':'text-success' }}">
                            {{ number_format($restT,0,',',' ') }}&nbsp;<small style="font-size:13px;font-weight:600">FCFA</small>
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    @if(!$soldeT && !$videT)
                    <button class="btn btn-primary btn-sm flex-fill"
                        data-bs-toggle="modal" data-bs-target="#modalPaiementTailleur"
                        onclick="document.querySelector('[name=tailleur_id]').value='{{ $t['id'] }}'">
                        <i class="bx bx-money me-1"></i>Payer
                    </button>
                    @endif
                    <a href="{{ route('paiements.recu.tailleur',$t['id']) }}"
                       class="btn btn-outline-primary btn-sm flex-fill">
                        <i class="bx bx-receipt me-1"></i>Reçu
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bx bx-user-x fs-1 d-block mb-2"></i>Aucun tailleur trouvé.
    </div>
    @endforelse
</div>
@endif

{{-- ===== MODAL PAIEMENT CLIENT ===== --}}
<div class="modal fade" id="modalPaiementClient" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:#198754">
                <h5 class="modal-title"><i class="bx bx-money-withdraw me-2"></i>Enregistrer un paiement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPaiementClient" action="{{ route('paiements.clients.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Sélection client --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
                        <select name="client_id" id="payClientId" class="form-select" required>
                            <option value="">-- Choisir un client --</option>
                            @foreach($clients as $c)
                            <option value="{{ $c['id'] }}"
                                data-nom="{{ $c['nom'] }}"
                                data-total="{{ $c['montantTotal'] }}"
                                data-paye="{{ $c['montantPaye'] }}"
                                data-restant="{{ $c['montantRestant'] }}">
                                {{ $c['nom'] }}{{ $c['contact'] ? ' — '.$c['contact'] : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Résumé client (visible après sélection) --}}
                    <div id="clientSummary" class="d-none mb-3 rounded-3 p-3" style="background:#f0fdf4;border:1.5px solid #86efac">
                        <div class="row text-center g-0 mb-2">
                            <div class="col-4 border-end">
                                <div class="small text-muted">Total dû</div>
                                <div class="fw-bold" id="sumTotal">—</div>
                            </div>
                            <div class="col-4 border-end">
                                <div class="small text-muted">Payé</div>
                                <div class="fw-bold text-success" id="sumPaye">—</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Reste</div>
                                <div class="fw-bold text-danger" id="sumRestant">—</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm w-100" id="btnToutPayer">
                            ✓ Payer le solde total restant
                        </button>
                    </div>

                    {{-- Montant --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Montant encaissé (FCFA) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="montant" id="payMontant"
                                   class="form-control form-control-lg fw-bold"
                                   min="1" required placeholder="0"
                                   style="font-size:1.4rem">
                            <span class="input-group-text fw-bold">FCFA</span>
                        </div>
                        <div id="montantApresPay" class="small text-muted mt-1 d-none">
                            Après ce paiement, il restera : <strong id="nouveauRestant" class="text-danger"></strong>
                        </div>
                    </div>

                    {{-- Mode de paiement --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode de paiement</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <input type="radio" name="moyen" id="m1" value="ESPECES" class="d-none" checked>
                            <label for="m1" class="btn-moyen selected">💵 Espèces</label>
                            <input type="radio" name="moyen" id="m2" value="MOBILE_MONEY" class="d-none">
                            <label for="m2" class="btn-moyen">📱 Mobile Money</label>
                            <input type="radio" name="moyen" id="m3" value="VIREMENT" class="d-none">
                            <label for="m3" class="btn-moyen">🏦 Virement</label>
                        </div>
                    </div>

                    {{-- Note --}}
                    <div>
                        <label class="form-label small text-muted">Note (optionnel)</label>
                        <input type="text" name="note" class="form-control form-control-sm"
                               placeholder="Ex : 2ème versement, acompte...">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold" id="btnPaiementClientSubmit">
                        <i class="bx bx-check-circle me-1"></i>Enregistrer le paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL PAIEMENT TAILLEUR ===== --}}
<div class="modal fade" id="modalPaiementTailleur" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-money me-2"></i>Paiement tailleur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('paiements.tailleurs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tailleur <span class="text-danger">*</span></label>
                        <select name="tailleur_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($tailleurs as $t)
                            <option value="{{ $t['id'] }}">
                                {{ $t['nom'] }}
                                — Reste : {{ number_format(max(0,$t['totalDu']-$t['totalPaye']),0,',',' ') }} FCFA
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Montant versé (FCFA) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="montant" class="form-control form-control-lg fw-bold"
                                   min="1" required placeholder="0" style="font-size:1.3rem">
                            <span class="input-group-text fw-bold">FCFA</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode de paiement</label>
                        <select name="moyen" class="form-select">
                            <option value="ESPECES">💵 Espèces</option>
                            <option value="MOBILE_MONEY">📱 Mobile Money</option>
                            <option value="VIREMENT">🏦 Virement</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted">Note (optionnel)</label>
                        <input type="text" name="note" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bx bx-check-circle me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
// ── Chips filtres statut paiement
let activeFilter = 'tous';
document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.filter-chip').forEach(c => {
            c.classList.remove('active');
            c.className = c.className.replace(/btn-(secondary|warning|success|dark)(\s|$)/, 'btn-outline-$1 ').trim();
        });
        this.classList.add('active');
        this.className = this.className.replace('btn-outline-', 'btn-');
        activeFilter = this.dataset.filter;
        applyFilters();
    });
});

function applyFilters() {
    const q = (document.getElementById('searchClient')?.value || '').toLowerCase().trim();
    document.querySelectorAll('.client-card-wrap').forEach(el => {
        const solde  = el.dataset.solde === '1';
        const vide   = el.dataset.vide === '1';
        let show = true;
        if (activeFilter === 'a_regler') show = !solde && !vide;
        else if (activeFilter === 'solde')   show = solde;
        else if (activeFilter === 'vide')    show = vide;
        if (show && q) show = el.dataset.search.includes(q);
        el.classList.toggle('d-none', !show);
    });
}

// ── Recherche client
const searchInput = document.getElementById('searchClient');
if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
}

// ── Délégation des boutons "Payer" sur les cartes
document.querySelectorAll('.btn-payer').forEach(function(btn) {
    btn.addEventListener('click', function() {
        openPayModal(
            this.dataset.id,
            this.dataset.nom,
            parseFloat(this.dataset.total || 0),
            parseFloat(this.dataset.paye  || 0),
            parseFloat(this.dataset.restant || 0)
        );
    });
});

// ── Ouvrir modal paiement (depuis bouton Payer d'une carte)
function openPayModal(clientId, nom, total, paye, restant) {
    const sel = document.getElementById('payClientId');
    if (clientId) {
        sel.value = clientId;
        _updateSummary(total, paye, restant);
    } else {
        sel.value = '';
        document.getElementById('clientSummary').classList.add('d-none');
        document.getElementById('payMontant').value = '';
        document.getElementById('montantApresPay').classList.add('d-none');
    }
    new bootstrap.Modal(document.getElementById('modalPaiementClient')).show();
}

// ── Mise à jour résumé lors du changement de client dans le select
document.getElementById('payClientId').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!this.value) {
        document.getElementById('clientSummary').classList.add('d-none');
        document.getElementById('montantApresPay').classList.add('d-none');
        return;
    }
    _updateSummary(
        parseFloat(opt.dataset.total   || 0),
        parseFloat(opt.dataset.paye    || 0),
        parseFloat(opt.dataset.restant || 0)
    );
});

function fmtF(v) { return Number(v||0).toLocaleString('fr-FR') + ' FCFA'; }

let _currentRestant = 0;
function _updateSummary(total, paye, restant) {
    _currentRestant = restant;
    document.getElementById('sumTotal').textContent   = fmtF(total);
    document.getElementById('sumPaye').textContent    = fmtF(paye);
    document.getElementById('sumRestant').textContent = fmtF(restant);
    document.getElementById('clientSummary').classList.remove('d-none');
    document.getElementById('btnToutPayer').onclick = () => {
        document.getElementById('payMontant').value = Math.round(restant);
        document.getElementById('payMontant').dispatchEvent(new Event('input'));
    };
}

// ── Calcul du nouveau restant en temps réel
document.getElementById('payMontant').addEventListener('input', function() {
    if (!_currentRestant) return;
    const val = parseFloat(this.value || 0);
    if (val > 0) {
        const apres = Math.max(0, _currentRestant - val);
        document.getElementById('nouveauRestant').textContent = fmtF(apres);
        document.getElementById('montantApresPay').classList.remove('d-none');
    } else {
        document.getElementById('montantApresPay').classList.add('d-none');
    }
});

// ── Boutons radio moyen de paiement visuels
document.querySelectorAll('.btn-moyen').forEach(lbl => {
    lbl.addEventListener('click', function() {
        document.querySelectorAll('.btn-moyen').forEach(l => l.classList.remove('selected'));
        this.classList.add('selected');
    });
});

// ── Bouton "Reçu" : ticket thermique via showReceiptPopup
document.querySelectorAll('.btn-voir-recu').forEach(function(btn) {
    btn.addEventListener('click', async function() {
        var url = this.dataset.url;
        var self = this;
        self.disabled = true;
        try {
            var resp = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            var json = await resp.json();
            if (json.receipt && window.showReceiptPopup) {
                await window.showReceiptPopup(json.receipt);
            }
        } catch (err) {
            swalError('Impossible de charger le reçu.');
        } finally {
            self.disabled = false;
        }
    });
});

// ── Bouton "Livré" : marquer les habits comme livrés + incrémenter Sortie du mois
document.querySelectorAll('.btn-livrer').forEach(function(btn) {
    btn.addEventListener('click', async function() {
        var url    = this.dataset.url;
        var nom    = this.dataset.nom;
        var self   = this;

        var confirm = await Swal.fire({
            title: 'Confirmer la livraison',
            html: '<p>Marquer tous les habits <strong>' + nom + '</strong> comme <strong>livrés</strong> ?</p>'
                + '<p class="text-muted small">Cela incrémentera les "Sorties du mois".</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '📦 Oui, livré !',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#ffc107',
        });
        if (!confirm.isConfirmed) return;

        self.disabled = true;
        self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            var resp  = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            });
            var json = await resp.json();
            if (!resp.ok) throw new Error(json.message || 'Erreur');

            // Mise à jour visuelle du compteur "Sorties du mois" sans rechargement
            var sortieBadge = document.getElementById('sortieDuMois');
            if (sortieBadge && json.nouvellesTotalSorties !== undefined) {
                sortieBadge.textContent = json.nouvellesTotalSorties;
            }

            await Swal.fire({
                icon: 'success',
                title: '📦 Livré !',
                text: json.message,
                timer: 2000,
                showConfirmButton: false
            });
            window.location.reload();
        } catch (err) {
            swalError(err.message || 'Une erreur est survenue.');
            self.disabled = false;
            self.innerHTML = '<i class="bx bx-package me-1"></i>Livré';
        }
    });
});

// ── Soumission AJAX paiement client
document.getElementById('formPaiementClient').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnPaiementClientSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';
    try {
        const resp = await fetch(this.action, {
            method: 'POST', body: new FormData(this),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        if (!resp.ok) throw new Error(json.message || 'Erreur lors de l\'enregistrement');
        bootstrap.Modal.getInstance(document.getElementById('modalPaiementClient'))?.hide();
        if (json.receipt && window.showReceiptPopup) await window.showReceiptPopup(json.receipt);
        window.location.reload();
    } catch (err) {
        swalError(err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-check-circle me-1"></i>Enregistrer le paiement';
    }
});

</script>
@endpush
