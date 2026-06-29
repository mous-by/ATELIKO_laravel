@extends('layouts.app')

@section('title', 'Nouvelle mesure')
@section('page-title', 'Nouvelle commande')
@section('page-subtitle', 'Enregistrer un client et ses mesures')

@push('styles')
<link href="{{ asset('assets/css/mesure.css') }}" rel="stylesheet">
<style>
.tab-step-nav .nav-link { border-radius: 50px; padding: 0.5rem 1.2rem; font-size: .88rem; color: #6c757d; border: 2px solid #dee2e6; margin-right: .4rem; }
.tab-step-nav .nav-link.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
.tab-step-nav .nav-link.done { border-color: #198754; color: #198754; }
.client-picker-item { cursor: pointer; border-radius: 8px; transition: background .15s; }
.client-picker-item:hover, .client-picker-item.selected { background: #e8f0fe; }
.photo-preview-box { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #dee2e6; }
.habit-preview-box { width: 100%; max-height: 220px; object-fit: contain; border-radius: 8px; border: 2px dashed #dee2e6; }
.modele-card { cursor: pointer; border: 2px solid transparent; transition: border-color .15s; border-radius: 10px; }
.modele-card:hover { border-color: #0d6efd; }
.modele-card.selected { border-color: #0d6efd; background: #e8f0fe; }
.modele-img { width: 100%; height: 130px; object-fit: cover; border-radius: 8px 8px 0 0; }
.measure-input { font-size: .88rem; }
.section-badge { font-size: .75rem; background: #e8f0fe; color: #0d6efd; border-radius: 20px; padding: .2rem .7rem; }
</style>
@endpush

@section('content')
@php $user = Auth::user(); @endphp

<form action="{{ route('mesures.store') }}" method="POST" enctype="multipart/form-data" id="mesureForm">
@csrf

{{-- Tabs navigation --}}
<ul class="nav tab-step-nav mb-4" id="mesureTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabClient" id="tabClientLink"><i class="bx bx-user me-1"></i>Client</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPhoto" id="tabPhotoLink"><i class="bx bx-camera me-1"></i>Photo</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabMesures" id="tabMesuresLink"><i class="bx bx-ruler me-1"></i>Mesures</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabModele" id="tabModeleLink"><i class="bx bx-photo-album me-1"></i>Modèle & Prix</a></li>
</ul>

<div class="tab-content">

{{-- ===================== TAB 1 : CLIENT ===================== --}}
<div class="tab-pane fade show active" id="tabClient">
<div class="row g-4">

    {{-- Panneau gauche : choix nouveau/existant --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-user-circle text-primary me-2"></i>Type de client
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mb-4">
                    <button type="button" class="btn btn-primary" id="btnNouveauClient">
                        <i class="bx bx-user-plus me-2"></i>Nouveau client
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnClientExistant">
                        <i class="bx bx-search me-2"></i>Client existant
                    </button>
                </div>
                {{-- Aperçu client sélectionné --}}
                <div id="clientSelectPreview" class="d-none">
                    <div class="alert alert-success py-2 mb-0">
                        <i class="bx bx-check-circle me-1"></i>
                        <strong id="clientSelectName"></strong>
                        <br><small id="clientSelectContact" class="text-muted"></small>
                        <input type="hidden" name="client_id" id="clientIdHidden">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="btnDeselectClient">Changer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulaire nouveau client --}}
    <div class="col-lg-8" id="panelNouveauClient">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-user-plus text-success me-2"></i>Informations du nouveau client
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="prenom" class="form-control" id="inputPrenom" value="{{ old('prenom') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" id="inputNom" value="{{ old('nom') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Sexe</label>
                        <select name="sexe" class="form-select" id="inputSexe">
                            <option value="">-- Sélectionner --</option>
                            <option value="Femme" {{ old('sexe')=='Femme'?'selected':'' }}>Femme</option>
                            <option value="Homme" {{ old('sexe')=='Homme'?'selected':'' }}>Homme</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Téléphone</label>
                        <input type="text" name="contact" class="form-control" placeholder="77XXXXXXX" value="{{ old('contact') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Adresse</label>
                        <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Type femme</label>
                        <select name="femme_type" class="form-select" id="inputFemmeType">
                            <option value="">-- Sélectionner --</option>
                            <option value="Robe">Robe</option>
                            <option value="Jupe">Jupe</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panneau client existant (caché par défaut) --}}
    <div class="col-lg-8 d-none" id="panelClientExistant">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-search text-primary me-2"></i>Rechercher un client existant
            </div>
            <div class="card-body">
                <input type="text" id="clientSearch" class="form-control mb-3" placeholder="Rechercher par nom, prénom ou téléphone...">
                <div id="clientList" style="max-height: 300px; overflow-y: auto;">
                    @foreach($clients as $c)
                    <div class="client-picker-item d-flex align-items-center gap-3 p-2 mb-1"
                         data-id="{{ $c->id }}"
                         data-name="{{ $c->prenom }} {{ $c->nom }}"
                         data-contact="{{ $c->contact }}">
                        <div class="avatar-img bg-primary d-flex align-items-center justify-content-center text-white" style="min-width:38px;width:38px;height:38px;border-radius:50%;font-size:.9rem;">
                            {{ strtoupper(substr($c->prenom,0,1)) }}
                        </div>
                        <div>
                            <strong>{{ $c->prenom }} {{ $c->nom }}</strong>
                            <br><small class="text-muted">{{ $c->contact }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

<div class="d-flex justify-content-end mt-3">
    <button type="button" class="btn btn-primary" onclick="goToTab('tabPhoto')">
        Suivant <i class="bx bx-chevron-right ms-1"></i>
    </button>
</div>
</div>

{{-- ===================== TAB 2 : PHOTO ===================== --}}
<div class="tab-pane fade" id="tabPhoto">
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-user-circle text-primary me-2"></i>Photo du client
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <img id="clientPhotoPreview" src="{{ asset('assets/images/model4.jpg') }}"
                         class="photo-preview-box" alt="Aperçu">
                </div>
                <input type="file" name="photo" id="clientPhotoInput" class="form-control" accept="image/*">
                <small class="text-muted d-block mt-2">Photo de profil du client</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-shirt text-info me-2"></i>Photo de l'habit de référence
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <img id="habitPhotoPreview" src="{{ asset('assets/images/model4.jpg') }}"
                         class="habit-preview-box" alt="Aperçu habit" style="display:none;">
                    <div id="habitPhotoPlaceholder" class="habit-preview-box d-flex align-items-center justify-content-center text-muted" style="height:120px;">
                        <div><i class="bx bx-image-add fs-2"></i><br><small>Aucune photo</small></div>
                    </div>
                </div>
                <input type="file" name="habit_photo" id="habitPhotoInput" class="form-control" accept="image/*">
                <small class="text-muted d-block mt-2">Photo de l'habit à reproduire (optionnel)</small>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between mt-3">
    <button type="button" class="btn btn-outline-secondary" onclick="goToTab('tabClient')">
        <i class="bx bx-chevron-left me-1"></i>Précédent
    </button>
    <button type="button" class="btn btn-primary" onclick="goToTab('tabMesures')">
        Suivant <i class="bx bx-chevron-right ms-1"></i>
    </button>
</div>
</div>

{{-- ===================== TAB 3 : MESURES ===================== --}}
<div class="tab-pane fade" id="tabMesures">

{{-- Sélection type vêtement --}}
<div class="card mb-3">
    <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
        <span class="fw-medium">Type de vêtement :</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm vetement-btn active" data-type="Robe" id="btnRobe">
                <i class="bx bx-female me-1"></i>Femme Robe
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm vetement-btn" data-type="Jupe" id="btnJupe">
                <i class="bx bx-female-sign me-1"></i>Femme Jupe
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm vetement-btn" data-type="Homme" id="btnHomme">
                <i class="bx bx-male me-1"></i>Homme
            </button>
        </div>
        <input type="hidden" name="type_vetement" id="typeVetement" value="Robe">
    </div>
</div>

{{-- ROBE --}}
<div id="panelRobe" class="card">
    <div class="card-header bg-white fw-semibold">
        <span class="section-badge me-2">Femme Robe</span> Mesures (en cm)
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach([
                ['robe_epaule','Épaule'],['robe_manche','Manche'],['robe_poitrine','Poitrine'],
                ['robe_taille','Taille'],['robe_longueur','Longueur'],['robe_fesse','Fesse'],
                ['robe_tour_manche','Tour manche'],['robe_longueur_poitrine','Long. poitrine'],
                ['robe_longueur_taille','Long. taille'],['robe_longueur_fesse','Long. fesse'],
            ] as [$field,$label])
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label small fw-medium">{{ $label }}</label>
                <input type="number" step="0.5" name="{{ $field }}" class="form-control form-control-sm measure-input" placeholder="0" value="{{ old($field) }}">
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- JUPE --}}
<div id="panelJupe" class="card d-none">
    <div class="card-header bg-white fw-semibold">
        <span class="section-badge me-2">Femme Jupe</span> Mesures (en cm)
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach([
                ['jupe_epaule','Épaule'],['jupe_manche','Manche'],['jupe_poitrine','Poitrine'],
                ['jupe_taille','Taille'],['jupe_longueur','Longueur'],['jupe_longueur_jupe','Long. jupe'],
                ['jupe_ceinture','Ceinture'],['jupe_fesse','Fesse'],['jupe_tour_manche','Tour manche'],
                ['jupe_longueur_poitrine','Long. poitrine'],['jupe_longueur_taille','Long. taille'],
                ['jupe_longueur_fesse','Long. fesse'],
            ] as [$field,$label])
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label small fw-medium">{{ $label }}</label>
                <input type="number" step="0.5" name="{{ $field }}" class="form-control form-control-sm measure-input" placeholder="0" value="{{ old($field) }}">
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- HOMME --}}
<div id="panelHomme" class="card d-none">
    <div class="card-header bg-white fw-semibold">
        <span class="section-badge me-2">Homme</span> Mesures (en cm)
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach([
                ['homme_epaule','Épaule'],['homme_manche','Manche'],['homme_longueur','Longueur corps'],
                ['homme_longueur_pantalon','Long. pantalon'],['homme_ceinture','Ceinture'],
                ['homme_cuisse','Cuisse'],['homme_poitrine','Poitrine'],['homme_corps','Corps'],
                ['homme_tour_manche','Tour manche'],
            ] as [$field,$label])
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label small fw-medium">{{ $label }}</label>
                <input type="number" step="0.5" name="{{ $field }}" class="form-control form-control-sm measure-input" placeholder="0" value="{{ old($field) }}">
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-3">
    <button type="button" class="btn btn-outline-secondary" onclick="goToTab('tabPhoto')">
        <i class="bx bx-chevron-left me-1"></i>Précédent
    </button>
    <button type="button" class="btn btn-primary" onclick="goToTab('tabModele')">
        Suivant <i class="bx bx-chevron-right ms-1"></i>
    </button>
</div>
</div>

{{-- ===================== TAB 4 : MODÈLE & PRIX ===================== --}}
<div class="tab-pane fade" id="tabModele">
<div class="row g-4">

    {{-- Modèles --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bx bx-photo-album text-primary me-2"></i>Choisir un modèle</span>
                <span class="badge bg-light text-dark">{{ $modeles->count() }} modèle(s)</span>
            </div>
            <div class="card-body">
                {{-- Filtre catégorie --}}
                <div class="d-flex gap-2 mb-3 flex-wrap" id="categorieFilters">
                    <button type="button" class="btn btn-sm btn-outline-secondary cat-btn active" data-cat="all">Tous</button>
                    @foreach($categories as $cat)
                    <button type="button" class="btn btn-sm btn-outline-secondary cat-btn" data-cat="{{ $cat }}">{{ $cat }}</button>
                    @endforeach
                </div>
                {{-- Aucun modèle --}}
                <input type="hidden" name="modele_reference_id" id="modeleIdHidden" value="">
                <input type="hidden" name="modele_nom" id="modeleNomHidden" value="">
                <div class="row g-2" id="modeleGrid">
                    @forelse($modeles as $m)
                    <div class="col-6 col-md-4 col-xl-3 modele-item" data-cat="{{ $m->categorie }}">
                        <div class="modele-card card h-100" data-id="{{ $m->id }}" data-nom="{{ $m->nom }}" data-prix="{{ $m->prix }}" data-cat="{{ $m->categorie }}">
                            @if($m->photo_url)
                                <img src="{{ $m->photo_url }}" class="modele-img">
                            @else
                                <div class="modele-img bg-light d-flex align-items-center justify-content-center text-muted">
                                    <i class="bx bx-image fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body py-2 px-2">
                                <div class="fw-medium small text-truncate">{{ $m->nom }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $m->categorie }}</div>
                                @if($m->prix)
                                <div class="text-primary fw-bold small">{{ number_format($m->prix, 0, ',', ' ') }} F</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-4">
                        <i class="bx bx-palette fs-2"></i>
                        <p class="mt-2">Aucun modèle disponible.<br>
                        <a href="{{ route('modeles.create') }}" class="btn btn-sm btn-outline-primary mt-2">Ajouter un modèle</a></p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Prix & description --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-money text-success me-2"></i>Prix & Description
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Prix (FCFA)</label>
                    <input type="number" name="prix" id="inputPrix" class="form-control" placeholder="0" value="{{ old('prix') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description / Notes</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Détails supplémentaires...">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Date de mesure</label>
                    <input type="date" name="date_mesure" class="form-control" value="{{ old('date_mesure', date('Y-m-d')) }}">
                </div>
                {{-- Modèle sélectionné preview --}}
                <div id="modeleSelectedPreview" class="d-none">
                    <div class="alert alert-primary py-2">
                        <i class="bx bx-check-circle me-1"></i>
                        <strong id="modeleSelectedNom"></strong>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" id="btnDeselectModele">Retirer</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bouton soumettre --}}
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success btn-lg" id="btnSubmitMesure">
                <i class="bx bx-check-circle me-2"></i>Enregistrer la commande
                <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></span>
            </button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Annuler
            </a>
        </div>
    </div>

</div>
<div class="mt-3">
    <button type="button" class="btn btn-outline-secondary" onclick="goToTab('tabMesures')">
        <i class="bx bx-chevron-left me-1"></i>Précédent
    </button>
</div>
</div>

</div>{{-- tab-content --}}
</form>
@endsection

@push('scripts')
<script>
(function() {
    // --- Navigation entre onglets ---
    function goToTab(tabId) {
        const link = document.getElementById(tabId + 'Link');
        if (link) link.click();
    }
    window.goToTab = goToTab;

    // --- Sidebar : nouveau vs existant ---
    const btnNouveau = document.getElementById('btnNouveauClient');
    const btnExistant = document.getElementById('btnClientExistant');
    const panelNouveau = document.getElementById('panelNouveauClient');
    const panelExistant = document.getElementById('panelClientExistant');
    const clientSelectPreview = document.getElementById('clientSelectPreview');
    const clientIdHidden = document.getElementById('clientIdHidden');

    btnNouveau?.addEventListener('click', () => {
        panelNouveau.classList.remove('d-none');
        panelExistant.classList.add('d-none');
        clientSelectPreview.classList.add('d-none');
        clientIdHidden.value = '';
        btnNouveau.classList.add('btn-primary');
        btnNouveau.classList.remove('btn-outline-secondary');
        btnExistant.classList.add('btn-outline-secondary');
        btnExistant.classList.remove('btn-primary');
    });

    btnExistant?.addEventListener('click', () => {
        panelExistant.classList.remove('d-none');
        panelNouveau.classList.add('d-none');
        btnExistant.classList.add('btn-primary');
        btnExistant.classList.remove('btn-outline-secondary');
        btnNouveau.classList.add('btn-outline-secondary');
        btnNouveau.classList.remove('btn-primary');
    });

    // Sélection client existant
    document.querySelectorAll('.client-picker-item').forEach(item => {
        item.addEventListener('click', () => {
            clientIdHidden.value = item.dataset.id;
            document.getElementById('clientSelectName').textContent = item.dataset.name;
            document.getElementById('clientSelectContact').textContent = item.dataset.contact;
            clientSelectPreview.classList.remove('d-none');
            panelExistant.classList.add('d-none');
        });
    });

    document.getElementById('btnDeselectClient')?.addEventListener('click', () => {
        clientIdHidden.value = '';
        clientSelectPreview.classList.add('d-none');
        panelExistant.classList.remove('d-none');
    });

    // Recherche client existant
    document.getElementById('clientSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.client-picker-item').forEach(item => {
            const text = item.dataset.name.toLowerCase() + ' ' + (item.dataset.contact || '');
            item.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // --- Photos ---
    document.getElementById('clientPhotoInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => { document.getElementById('clientPhotoPreview').src = ev.target.result; };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('habitPhotoInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
                const img = document.getElementById('habitPhotoPreview');
                img.src = ev.target.result;
                img.style.display = 'block';
                document.getElementById('habitPhotoPlaceholder').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    // --- Type vêtement ---
    const vetBtns = document.querySelectorAll('.vetement-btn');
    const panels = { Robe: 'panelRobe', Jupe: 'panelJupe', Homme: 'panelHomme' };
    const typeInput = document.getElementById('typeVetement');

    vetBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            vetBtns.forEach(b => { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-outline-primary'); });
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-outline-primary');
            typeInput.value = btn.dataset.type;
            Object.entries(panels).forEach(([type, panelId]) => {
                document.getElementById(panelId)?.classList.toggle('d-none', type !== btn.dataset.type);
            });
            // Sync avec le sexe client
            if (btn.dataset.type === 'Homme') {
                document.getElementById('inputSexe').value = 'Homme';
            } else {
                document.getElementById('inputSexe').value = 'Femme';
                const ft = document.getElementById('inputFemmeType');
                if (ft) ft.value = (btn.dataset.type === 'Robe' ? 'Robe' : 'Jupe');
            }
        });
    });

    // Sync sexe → type vêtement
    document.getElementById('inputSexe')?.addEventListener('change', function() {
        if (this.value === 'Homme') {
            document.getElementById('btnHomme')?.click();
        } else if (this.value === 'Femme') {
            document.getElementById('btnRobe')?.click();
        }
    });

    // --- Sélection modèle ---
    const modeleCards = document.querySelectorAll('.modele-card');
    const modeleIdHidden = document.getElementById('modeleIdHidden');
    const modeleNomHidden = document.getElementById('modeleNomHidden');
    const modelePreview = document.getElementById('modeleSelectedPreview');
    const modeleNomSpan = document.getElementById('modeleSelectedNom');
    const inputPrix = document.getElementById('inputPrix');

    modeleCards.forEach(card => {
        card.addEventListener('click', () => {
            modeleCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            modeleIdHidden.value = card.dataset.id;
            modeleNomHidden.value = card.dataset.nom;
            modeleNomSpan.textContent = card.dataset.nom;
            modelePreview.classList.remove('d-none');
            // Pré-remplir le prix si non saisi
            if (!inputPrix.value && card.dataset.prix && card.dataset.prix !== '0') {
                inputPrix.value = card.dataset.prix;
            }
        });
    });

    document.getElementById('btnDeselectModele')?.addEventListener('click', () => {
        modeleCards.forEach(c => c.classList.remove('selected'));
        modeleIdHidden.value = '';
        modeleNomHidden.value = '';
        modelePreview.classList.add('d-none');
    });

    // Filtre catégorie modèles
    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.cat-btn').forEach(b => { b.classList.remove('active', 'btn-secondary'); b.classList.add('btn-outline-secondary'); });
            btn.classList.add('active', 'btn-secondary');
            btn.classList.remove('btn-outline-secondary');
            const cat = btn.dataset.cat;
            document.querySelectorAll('.modele-item').forEach(item => {
                item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
            });
        });
    });

    // --- Soumission du formulaire ---
    document.getElementById('mesureForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitMesure');
        btn.disabled = true;
        document.getElementById('submitSpinner').classList.remove('d-none');
    });
})();
</script>
@endpush
