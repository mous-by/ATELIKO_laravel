@extends('layouts.app')
@section('title', 'Nouveau modèle')
@section('page-title', 'Ajouter un modèle')
@section('page-subtitle', 'Créez une fiche claire avec photo, prix et catégorie')

@push('styles')
<style>
    .model-create-shell{display:grid;grid-template-columns:minmax(280px,420px) 1fr;gap:18px;align-items:start}
    .model-photo-panel{position:sticky;top:88px}
    .model-photo-frame{position:relative;aspect-ratio:4/5;border-radius:8px;overflow:hidden;background:#eef3f8;border:1px dashed #b8c4d3;display:flex;align-items:center;justify-content:center}
    .model-photo-frame img,.model-photo-frame video{width:100%;height:100%;object-fit:cover;display:block}
    .model-photo-empty{text-align:center;color:#6c757d;padding:22px}
    .model-photo-empty i{font-size:3rem;color:#0d6efd;display:block;margin-bottom:8px}
    .model-preview-badge{position:absolute;left:12px;top:12px;background:rgba(13,110,253,.92);color:#fff;border-radius:999px;padding:6px 10px;font-size:.78rem;font-weight:700}
    .model-form-section{border-bottom:1px solid #edf0f4;padding-bottom:18px;margin-bottom:18px}
    .model-form-section:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0}
    .model-section-title{font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:8px}
    .model-section-title i{color:#0d6efd;font-size:1.2rem}
    .category-grid{display:grid;grid-template-columns:repeat(5,minmax(92px,1fr));gap:10px}
    .category-choice{border:1px solid #d9e2ef;background:#fff;border-radius:8px;padding:12px 8px;text-align:center;cursor:pointer;transition:.18s;min-height:76px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px}
    .category-choice i{font-size:1.45rem;color:#0d6efd}
    .category-choice span{font-weight:700;font-size:.82rem}
    .category-choice.active{border-color:#0d6efd;background:rgba(13,110,253,.08);box-shadow:0 0 0 3px rgba(13,110,253,.08)}
    .price-presets{display:flex;flex-wrap:wrap;gap:8px}
    .price-presets button{border-radius:999px}
    .model-help-strip{display:flex;gap:10px;align-items:flex-start;background:#f8fafc;border:1px solid #edf0f4;border-radius:8px;padding:12px;color:#5f6875}
    .model-help-strip i{font-size:1.4rem;color:#198754}
    .model-actions{position:sticky;bottom:0;background:rgba(255,255,255,.96);border-top:1px solid #edf0f4;margin:20px -20px -20px;padding:14px 20px;display:flex;justify-content:space-between;gap:10px;z-index:2}
    html.dark-theme .model-photo-frame{background:#2a2d3e;border-color:#3a3d50}
    html.dark-theme .model-form-section,.dark-theme .model-actions{border-color:#3a3d50}
    html.dark-theme .category-choice{background:#242632;border-color:#3a3d50;color:#e4e5e6}
    html.dark-theme .category-choice.active{background:rgba(13,110,253,.18);border-color:#0d6efd}
    html.dark-theme .model-help-strip{background:#2a2d3e;border-color:#3a3d50;color:#b8bdca}
    html.dark-theme .model-actions{background:rgba(36,38,50,.96)}
    @media(max-width:992px){.model-create-shell{grid-template-columns:1fr}.model-photo-panel{position:static}.category-grid{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:576px){.category-grid{grid-template-columns:repeat(2,1fr)}.model-actions{flex-direction:column-reverse}.model-actions .btn{width:100%}}
</style>
@endpush

@section('content')
@php
    $categoryMeta = [
        'ROBE' => ['label' => 'Robe', 'icon' => 'bx-female'],
        'JUPE' => ['label' => 'Jupe', 'icon' => 'bx-closet'],
        'HOMME' => ['label' => 'Homme', 'icon' => 'bx-male'],
        'ENFANT' => ['label' => 'Enfant', 'icon' => 'bx-happy-heart-eyes'],
        'AUTRE' => ['label' => 'Autre', 'icon' => 'bx-grid-alt'],
    ];
    $selectedCategory = old('categorie', $categories[0] ?? 'ROBE');
@endphp

<form action="{{ route('modeles.store') }}" method="POST" enctype="multipart/form-data" id="modelCreateForm">
    @csrf
    <div class="model-create-shell">
        <aside class="model-photo-panel">
            <div class="card">
                <div class="card-body">
                    <div class="model-photo-frame mb-3" id="modelPreviewFrame">
                        <span class="model-preview-badge" id="previewCategory">{{ $categoryMeta[$selectedCategory]['label'] ?? $selectedCategory }}</span>
                        <div class="model-photo-empty" id="modelPhotoEmpty">
                            <i class="bx bx-image-add"></i>
                            <strong>Ajoutez une photo du modèle</strong>
                            <div class="small mt-1">La photo aide l'équipe à retrouver rapidement le modèle.</div>
                        </div>
                        <img id="modelPhotoPreview" src="" alt="Aperçu du modèle" class="d-none">
                    </div>

                    <div class="camera-widget" id="modelCameraWidget">
                        <div class="btn-group w-100 mb-2" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary active cam-btn-file">
                                <i class="bx bx-paperclip me-1"></i>Choisir une photo
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary cam-btn-cam">
                                <i class="bx bx-camera me-1"></i>Prendre une photo
                            </button>
                        </div>
                        <div class="cam-file-zone">
                            <input type="file" name="photo" class="form-control" accept="image/*" id="photoInput">
                            <div class="form-text">JPG ou PNG, maximum 5 Mo.</div>
                        </div>
                        <div class="cam-live-zone" style="display:none">
                            <div class="cam-video-box mb-2">
                                <video class="cam-video" autoplay playsinline muted></video>
                                <img class="cam-snap" style="display:none" alt="capture">
                                <div class="cam-shutter"></div>
                            </div>
                            <canvas class="cam-canvas" style="display:none"></canvas>
                            <div class="d-flex gap-2 mb-1">
                                <button type="button" class="btn btn-primary btn-sm flex-fill cam-shoot">
                                    <i class="bx bx-camera me-1"></i>Capturer
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm cam-flip" title="Changer de caméra">
                                    <i class="bx bx-revision"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cam-redo" style="display:none">
                                    <i class="bx bx-refresh me-1"></i>Reprendre
                                </button>
                            </div>
                            <div class="cam-msg small text-muted"></div>
                        </div>
                    </div>

                    <div class="model-help-strip mt-3">
                        <i class="bx bx-bulb"></i>
                        <div class="small">Pour un album propre, prenez la photo en face, avec une bonne lumière et le vêtement bien visible.</div>
                    </div>
                </div>
            </div>
        </aside>

        <section class="card">
            <div class="card-body">
                <div class="model-form-section">
                    <div class="model-section-title"><i class="bx bx-edit"></i>Informations principales</div>
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label class="form-label">Nom du modèle <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control form-control-lg" value="{{ old('nom') }}" placeholder="Ex : Robe droite brodée" required maxlength="100">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Prix conseillé</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="prix" id="prixInput" class="form-control" value="{{ old('prix') }}" min="0" placeholder="0">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="model-form-section">
                    <div class="model-section-title"><i class="bx bx-category"></i>Catégorie</div>
                    <input type="hidden" name="categorie" id="categorieInput" value="{{ $selectedCategory }}" required>
                    <div class="category-grid">
                        @foreach($categories as $cat)
                        @php $meta = $categoryMeta[$cat] ?? ['label' => $cat, 'icon' => 'bx-grid-alt']; @endphp
                        <button type="button" class="category-choice {{ $selectedCategory === $cat ? 'active' : '' }}" data-category="{{ $cat }}" data-label="{{ $meta['label'] }}">
                            <i class="bx {{ $meta['icon'] }}"></i>
                            <span>{{ $meta['label'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="model-form-section">
                    <div class="model-section-title"><i class="bx bx-wallet"></i>Prix rapide</div>
                    <div class="price-presets mb-2">
                        @foreach([5000, 7500, 10000, 15000, 25000, 50000] as $amount)
                        <button type="button" class="btn btn-outline-primary btn-sm" data-price="{{ $amount }}">{{ number_format($amount, 0, ',', ' ') }}</button>
                        @endforeach
                    </div>
                    <div class="form-text">Touchez un montant pour remplir le prix automatiquement.</div>
                </div>

                <div class="model-form-section">
                    <div class="model-section-title"><i class="bx bx-detail"></i>Détails utiles</div>
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" maxlength="1000" placeholder="Couleur, tissu, coupe, occasion, remarques pour l'atelier...">{{ old('description') }}</textarea>
                </div>

                <div class="model-form-section">
                    <div class="model-section-title"><i class="bx bx-video"></i>Vidéo optionnelle</div>
                    <input type="file" name="video" class="form-control" accept="video/*" id="videoInput">
                    <div class="form-text">Ajoutez une courte vidéo seulement si elle aide à comprendre le modèle.</div>
                </div>

                <div class="model-actions">
                    <a href="{{ route('modeles.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bx bx-check-circle me-2"></i>Enregistrer le modèle
                    </button>
                </div>
            </div>
        </section>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const photoInput = document.getElementById('photoInput');
    const previewImg = document.getElementById('modelPhotoPreview');
    const emptyState = document.getElementById('modelPhotoEmpty');
    const categoryInput = document.getElementById('categorieInput');
    const previewCategory = document.getElementById('previewCategory');
    const priceInput = document.getElementById('prixInput');

    function renderPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = event => {
            previewImg.src = event.target.result;
            previewImg.classList.remove('d-none');
            emptyState.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }

    photoInput?.addEventListener('change', event => renderPreview(event.target.files[0]));

    document.querySelectorAll('.category-choice').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.category-choice').forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            categoryInput.value = button.dataset.category;
            previewCategory.textContent = button.dataset.label || button.dataset.category;
        });
    });

    document.querySelectorAll('[data-price]').forEach(button => {
        button.addEventListener('click', () => {
            priceInput.value = button.dataset.price;
            priceInput.focus();
        });
    });

    const cameraWidget = document.getElementById('modelCameraWidget');
    cameraWidget?.querySelector('.cam-shoot')?.addEventListener('click', () => {
        setTimeout(() => renderPreview(photoInput.files[0]), 350);
    });
})();
</script>
@endpush
