@extends('layouts.app')

@section('title', $client->prenom . ' ' . $client->nom)
@section('page-title', $client->prenom . ' ' . $client->nom)
@section('page-subtitle', 'Fiche client')

@section('content')
@php $isTailleur = Auth::user()->isTailleur(); @endphp
<div class="row g-4">
    <!-- Informations client -->
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body pt-4">
                @if($client->photo_url)
                    <img src="{{ $client->photo_url }}" class="rounded-circle mb-3"
                         style="width: 100px; height: 100px; object-fit: cover;" alt="">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width: 100px; height: 100px; font-size: 2rem;">
                        {{ strtoupper(substr($client->prenom, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold">{{ $client->prenom }} {{ $client->nom }}</h5>
                <p class="text-muted mb-1"><i class="bx bx-phone me-1"></i>{{ $client->contact ?? 'N/A' }}</p>
                @if($client->email)
                    <p class="text-muted mb-1"><i class="bx bx-envelope me-1"></i>{{ $client->email }}</p>
                @endif
                @if($client->adresse)
                    <p class="text-muted mb-1"><i class="bx bx-map me-1"></i>{{ $client->adresse }}</p>
                @endif
                @if($client->sexe)
                    <span class="badge bg-light text-dark">{{ $client->sexe }}</span>
                @endif
            </div>
            <div class="card-footer bg-transparent">
                @if(!$isTailleur)
                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-outline-primary btn-sm me-1">
                    <i class="bx bx-edit"></i> Modifier
                </a>
                @endif
                <button class="btn btn-outline-info btn-sm" onclick="window.print()">
                    <i class="bx bx-printer"></i> Imprimer
                </button>
            </div>
        </div>

        @if(!$isTailleur)
        <!-- Résumé paiements -->
        <div class="card mt-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-wallet me-2 text-success"></i>Résumé financier
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total commande :</span>
                    <strong>{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payé :</span>
                    <strong class="text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Restant :</span>
                    <strong class="{{ $montantRestant > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($montantRestant, 0, ',', ' ') }} FCFA
                    </strong>
                </div>
                @if($montantTotal > 0)
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ min(100, ($montantPaye/$montantTotal)*100) }}%"></div>
                </div>
                @endif

                @if($montantRestant > 0)
                <!-- Ajouter paiement -->
                <button class="btn btn-success w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#modalPaiement">
                    <i class="bx bx-plus-circle me-1"></i>Enregistrer un paiement
                </button>
                @else
                <span class="badge bg-success w-100 py-2">Soldé ✓</span>
                @endif

                <button type="button" id="btnVoirRecu" class="btn btn-outline-secondary w-100 btn-sm mt-2"
                    data-url="{{ route('paiements.recu.client', $client->id) }}">
                    <i class="bx bx-receipt me-1"></i>Voir le reçu
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Détails -->
    <div class="col-lg-8">
        <!-- Onglets -->
        <ul class="nav nav-tabs mb-3" id="clientTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#mesures">
                    <i class="bx bx-ruler me-1"></i>Mesures ({{ $client->mesures->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#affectations">
                    <i class="bx bx-user-check me-1"></i>Affectations ({{ $client->affectations->count() }})
                </a>
            </li>
            @if(!$isTailleur)
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#paiements">
                    <i class="bx bx-money me-1"></i>Paiements ({{ $client->paiements->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#rendezvous">
                    <i class="bx bx-calendar me-1"></i>RDV ({{ $client->rendezvous->count() }})
                </a>
            </li>
            @endif
        </ul>

        <div class="tab-content">
            <!-- MESURES -->
            <div class="tab-pane fade show active" id="mesures">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between">
                        <span><i class="bx bx-ruler text-primary me-2"></i>Mesures</span>
                        @if(!$isTailleur)
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalMesure">
                            <i class="bx bx-plus"></i> Ajouter
                        </button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if($client->mesures->isEmpty())
                            <div class="text-center py-4 text-muted">Aucune mesure enregistrée</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Modèle</th>
                                            @if(!$isTailleur)<th>Prix</th>@endif
                                            <th>Statut</th>
                                            @if(!$isTailleur)<th>Actions</th>@endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($client->mesures as $mesure)
                                        <tr>
                                            <td>{{ $mesure->date_mesure ? $mesure->date_mesure->format('d/m/Y') : '—' }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $mesure->type_vetement ?? '—' }}</span></td>
                                            <td>{{ $mesure->modele_nom ?? '—' }}</td>
                                            @if(!$isTailleur)<td>{{ $mesure->prix ? number_format($mesure->prix, 0, ',', ' ') . ' FCFA' : '—' }}</td>@endif
                                            <td>
                                                @if($mesure->affecte)
                                                    <span class="badge bg-info">Affecté</span>
                                                @else
                                                    <span class="badge bg-light text-dark">Disponible</span>
                                                @endif
                                            </td>
                                            @if(!$isTailleur)
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-mesure" title="Modifier"
                                                        data-url="{{ route('clients.mesures.update', [$client->id, $mesure->id]) }}"
                                                        data-mesure="{{ json_encode($mesure->only(['type_vetement','sexe','prix','modele_nom','description','epaule','manche','poitrine','taille','longueur','fesse','tour_manche','longueur_pantalon','ceinture','cuisse','longueur_jupe','corps','longueur_poitrine','longueur_taille','longueur_fesse'])) }}">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <form action="{{ route('clients.mesures.destroy', [$client->id, $mesure->id]) }}" method="POST"
                                                          data-confirm="Supprimer cette mesure ?"
                                                          data-confirm-text="Cette action est irréversible."
                                                          data-confirm-btn="Supprimer">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- AFFECTATIONS -->
            <div class="tab-pane fade" id="affectations">
                <div class="card">
                    <div class="card-body p-0">
                        @if($client->affectations->isEmpty())
                            <div class="text-center py-4 text-muted">Aucune affectation</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr><th>Tailleur</th><th>Statut</th><th>Échéance</th>@if(!$isTailleur)<th>Prix tailleur</th>@endif</tr>
                                    </thead>
                                    <tbody>
                                        @foreach($client->affectations as $aff)
                                        <tr>
                                            <td>{{ $aff->tailleur?->prenom }} {{ $aff->tailleur?->nom }}</td>
                                            <td>@include('partials.badge-statut', ['statut' => $aff->statut])</td>
                                            <td>{{ $aff->date_echeance ? $aff->date_echeance->format('d/m/Y') : '—' }}</td>
                                            @if(!$isTailleur)<td>{{ $aff->prix_tailleur ? number_format($aff->prix_tailleur, 0, ',', ' ') . ' FCFA' : '—' }}</td>@endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(!$isTailleur)
            <!-- PAIEMENTS -->
            <div class="tab-pane fade" id="paiements">
                <div class="card">
                    <div class="card-body p-0">
                        @if($client->paiements->isEmpty())
                            <div class="text-center py-4 text-muted">Aucun paiement</div>
                        @else
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr><th>Date</th><th>Montant</th><th>Moyen</th><th>Référence</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($client->paiements->where('type_paiement', 'CLIENT') as $p)
                                        <tr>
                                            <td>{{ $p->date_paiement?->format('d/m/Y') }}</td>
                                            <td><strong>{{ number_format($p->montant, 0, ',', ' ') }} FCFA</strong></td>
                                            <td><span class="badge bg-light text-dark">{{ $p->moyen }}</span></td>
                                            <td><small class="text-muted">{{ $p->reference }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RENDEZ-VOUS -->
            <div class="tab-pane fade" id="rendezvous">
                <div class="card">
                    <div class="card-body p-0">
                        @if($client->rendezvous->isEmpty())
                            <div class="text-center py-4 text-muted">Aucun rendez-vous</div>
                        @else
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr><th>Date</th><th>Type</th><th>Statut</th><th>Notes</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($client->rendezvous as $rdv)
                                        <tr>
                                            <td>{{ $rdv->date_rdv?->format('d/m/Y H:i') }}</td>
                                            <td>{{ $rdv->type_rendezvous }}</td>
                                            <td>@include('partials.badge-statut', ['statut' => $rdv->statut])</td>
                                            <td><small>{{ $rdv->notes }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(!$isTailleur)
<!-- Modal Paiement -->
<div class="modal fade" id="modalPaiement" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-money me-2"></i>Enregistrer un paiement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('clients.paiements.store', $client->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Montant (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" name="montant" class="form-control" min="1"
                               max="{{ $montantRestant }}" placeholder="Montant à payer" required>
                        <div class="form-text">Restant dû : {{ number_format($montantRestant, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Moyen de paiement</label>
                        <select name="moyen" class="form-select">
                            <option value="ESPECES">Espèces</option>
                            <option value="MOBILE_MONEY">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note (optionnel)</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(!$isTailleur)
<!-- Modal Modifier Mesure -->
<div class="modal fade" id="modalModifierMesure" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la mesure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formModifierMesure" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Type de vêtement</label>
                            <select name="type_vetement" class="form-select">
                                <option value="ROBE">Robe</option>
                                <option value="JUPE">Jupe</option>
                                <option value="HOMME">Homme</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prix (FCFA)</label>
                            <input type="number" name="prix" class="form-control" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modèle</label>
                            <input type="text" name="modele_nom" class="form-control">
                        </div>
                        <div class="col-12"><h6 class="text-primary border-bottom pb-1 mb-0">Mesures (cm)</h6></div>
                        @foreach(['epaule'=>'Épaule','manche'=>'Manche','poitrine'=>'Poitrine','taille'=>'Taille','longueur'=>'Longueur','fesse'=>'Fesse','tour_manche'=>'Tour manche','longueur_jupe'=>'Lg Jupe','ceinture'=>'Ceinture','longueur_pantalon'=>'Lg Pantalon','cuisse'=>'Cuisse','corps'=>'Corps'] as $mField => $mLabel)
                        <div class="col-md-3 col-6">
                            <label class="form-label small">{{ $mLabel }}</label>
                            <input type="number" name="{{ $mField }}" class="form-control form-control-sm" step="0.5" min="0">
                        </div>
                        @endforeach
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i>Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Mesure -->
<div class="modal fade" id="modalMesure" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-ruler me-2"></i>Ajouter une mesure</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('clients.mesures.store', $client->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Type de vêtement</label>
                            <select name="type_vetement" class="form-select" id="typeVetement">
                                <option value="ROBE">Robe</option>
                                <option value="JUPE">Jupe</option>
                                <option value="HOMME">Homme</option>
                                <option value="ENFANT">Enfant</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prix (FCFA)</label>
                            <input type="number" name="prix" class="form-control" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="date_mesure" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Mesures communes -->
                        <div class="col-12"><h6 class="text-primary border-bottom pb-1">Mesures</h6></div>
                        @foreach([
                            'epaule' => 'Épaule', 'manche' => 'Manche', 'poitrine' => 'Poitrine',
                            'taille' => 'Taille', 'longueur' => 'Longueur', 'fesse' => 'Fesse',
                            'tour_manche' => 'Tour manche', 'longueur_pantalon' => 'Lg Pantalon',
                            'ceinture' => 'Ceinture', 'cuisse' => 'Cuisse'
                        ] as $field => $label)
                        <div class="col-md-3 col-6">
                            <label class="form-label small">{{ $label }} (cm)</label>
                            <input type="number" name="{{ $field }}" class="form-control form-control-sm" step="0.5" min="0">
                        </div>
                        @endforeach

                        <div class="col-12">
                            <label class="form-label">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Photo modèle</label>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                        </div>
                        @if($modeles->isNotEmpty())
                        <div class="col-md-6">
                            <label class="form-label">Modèle de référence</label>
                            <select name="modele_reference_id" class="form-select form-select-sm">
                                <option value="">-- Aucun --</option>
                                @foreach($modeles as $modele)
                                <option value="{{ $modele->id }}">{{ $modele->nom }} ({{ $modele->categorie }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-plus-circle me-1"></i>Ajouter la mesure
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Voir le reçu (popup thermique)
document.getElementById('btnVoirRecu')?.addEventListener('click', async function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    try {
        var r = await fetch(btn.dataset.url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        var json = await r.json();
        if (json.receipt && window.showReceiptPopup) window.showReceiptPopup(json.receipt);
    } catch(e) { console.error(e); }
    btn.disabled = false;
    btn.innerHTML = '<i class="bx bx-receipt me-1"></i>Voir le reçu';
});

// ── Modifier mesure
document.querySelectorAll('.btn-edit-mesure').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var mesure = JSON.parse(this.dataset.mesure);
        var form   = document.getElementById('formModifierMesure');
        form.action = this.dataset.url;
        var fields = ['type_vetement','prix','modele_nom','description',
                      'epaule','manche','poitrine','taille','longueur','fesse',
                      'tour_manche','longueur_jupe','ceinture','longueur_pantalon',
                      'cuisse','corps','longueur_poitrine','longueur_taille','longueur_fesse'];
        fields.forEach(function(f) {
            var el = form.querySelector('[name="' + f + '"]');
            if (el) el.value = mesure[f] !== null && mesure[f] !== undefined ? mesure[f] : '';
        });
        new bootstrap.Modal(document.getElementById('modalModifierMesure')).show();
    });
});
</script>
@endpush
