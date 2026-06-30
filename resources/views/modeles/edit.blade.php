@extends('layouts.app')
@section('title', 'Modifier le modèle')
@section('page-title', 'Modifier le modèle')

@push('styles')
<style>
.media-card{border-radius:12px;overflow:hidden;background:#f0f0f0;position:relative;cursor:pointer;transition:.2s}
.media-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.15)}
.media-card img,.media-card video{width:100%;height:340px;object-fit:cover;display:block}
.media-empty{height:340px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#6c757d;gap:8px}
.media-empty i{font-size:3rem;color:#0d6efd}
.media-overlay{position:absolute;inset:0;background:rgba(0,0,0,.0);transition:.2s;display:flex;align-items:center;justify-content:center}
.media-card:hover .media-overlay{background:rgba(0,0,0,.35)}
.media-overlay-btn{opacity:0;transition:.2s;background:#fff;border:none;border-radius:999px;padding:8px 20px;font-weight:600;display:flex;align-items:center;gap:6px}
.media-card:hover .media-overlay-btn{opacity:1}
html.dark-theme .media-card{background:#2a2d3e}
html.dark-theme .media-overlay-btn{background:#1a1c2e;color:#e4e5e6}
</style>
@endpush

@section('content')
<div class="row justify-content-center g-4">

    {{-- ── Photo ── --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-image me-2 text-primary"></i>Photo
            </div>
            <div class="card-body">
                <form action="{{ route('modeles.update', $modele->id) }}" method="POST"
                      enctype="multipart/form-data" id="photoForm">
                    @csrf @method('PUT')

                    <div class="media-card mb-3" onclick="document.getElementById('photoInput').click()">
                        @if($modele->photo_url)
                            <img id="photoPreview" src="{{ $modele->photo_url }}" alt="Photo actuelle">
                        @else
                            <div class="media-empty" id="photoEmpty">
                                <i class="bx bx-image-add"></i>
                                <span class="small">Aucune photo</span>
                            </div>
                            <img id="photoPreview" src="" class="d-none" alt="">
                        @endif
                        <div class="media-overlay">
                            <button type="button" class="media-overlay-btn">
                                <i class="bx bx-camera"></i> Changer la photo
                            </button>
                        </div>
                    </div>

                    <input type="file" id="photoInput" name="photo" class="d-none" accept="image/*">

                    <div id="photoActions" class="d-none d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bx bx-save me-1"></i>Enregistrer
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="cancelPhoto()">
                            Annuler
                        </button>
                    </div>

                    @if($modele->photo_url && !request()->has('_photo_changed'))
                    <a href="{{ route('modeles.index') }}" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="bx bx-arrow-back me-1"></i>Retour à l'album
                    </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- ── Vidéo ── --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bx bx-video me-2 text-danger"></i>Vidéo
            </div>
            <div class="card-body">
                <form action="{{ route('modeles.update', $modele->id) }}" method="POST"
                      enctype="multipart/form-data" id="videoForm">
                    @csrf @method('PUT')

                    <div class="media-card mb-3" onclick="document.getElementById('videoInput').click()">
                        @if($modele->video_url)
                            <video id="videoPreview" src="{{ $modele->video_url }}" controls></video>
                        @else
                            <div class="media-empty" id="videoEmpty">
                                <i class="bx bx-video-plus"></i>
                                <span class="small">Aucune vidéo</span>
                            </div>
                            <video id="videoPreview" src="" class="d-none" controls></video>
                        @endif
                        <div class="media-overlay">
                            <button type="button" class="media-overlay-btn">
                                <i class="bx bx-video-plus"></i> Changer la vidéo
                            </button>
                        </div>
                    </div>

                    <input type="file" id="videoInput" name="video" class="d-none"
                           accept="video/mp4,video/quicktime,video/x-msvideo,video/webm">

                    <div id="videoActions" class="d-none d-flex gap-2">
                        <button type="submit" class="btn btn-danger flex-fill">
                            <i class="bx bx-save me-1"></i>Enregistrer
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="cancelVideo()">
                            Annuler
                        </button>
                    </div>

                    @if(!$modele->video_url)
                    <p class="text-muted small mt-2 mb-0">MP4, MOV, AVI — max 100 Mo</p>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Bouton retour centré si déjà des médias --}}
    <div class="col-12 text-center">
        <a href="{{ route('modeles.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-2"></i>Retour à l'album
        </a>
        <form action="{{ route('modeles.destroy', $modele->id) }}" method="POST" class="d-inline ms-2"
              data-confirm="Supprimer ce modèle ?"
              data-confirm-text="Cette action est irréversible."
              data-confirm-btn="Supprimer">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="bx bx-trash me-1"></i>Supprimer
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const origPhotoSrc = @json($modele->photo_url ?? '');
const origVideoSrc = @json($modele->video_url ?? '');

document.getElementById('photoInput').addEventListener('change', function(){
    const file = this.files[0];
    if (!file) return;
    const preview = document.getElementById('photoPreview');
    const empty   = document.getElementById('photoEmpty');
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
    if (empty) empty.classList.add('d-none');
    document.getElementById('photoActions').classList.remove('d-none');
    document.getElementById('photoActions').classList.add('d-flex');
});

document.getElementById('videoInput').addEventListener('change', function(){
    const file = this.files[0];
    if (!file) return;
    const preview = document.getElementById('videoPreview');
    const empty   = document.getElementById('videoEmpty');
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
    if (empty) empty.classList.add('d-none');
    document.getElementById('videoActions').classList.remove('d-none');
    document.getElementById('videoActions').classList.add('d-flex');
});

function cancelPhoto(){
    document.getElementById('photoInput').value = '';
    const preview = document.getElementById('photoPreview');
    const empty   = document.getElementById('photoEmpty');
    if (origPhotoSrc) { preview.src = origPhotoSrc; preview.classList.remove('d-none'); if(empty) empty.classList.add('d-none'); }
    else              { preview.classList.add('d-none'); if(empty) empty.classList.remove('d-none'); }
    document.getElementById('photoActions').classList.add('d-none');
    document.getElementById('photoActions').classList.remove('d-flex');
}

function cancelVideo(){
    document.getElementById('videoInput').value = '';
    const preview = document.getElementById('videoPreview');
    const empty   = document.getElementById('videoEmpty');
    if (origVideoSrc) { preview.src = origVideoSrc; preview.classList.remove('d-none'); if(empty) empty.classList.add('d-none'); }
    else              { preview.classList.add('d-none'); if(empty) empty.classList.remove('d-none'); }
    document.getElementById('videoActions').classList.add('d-none');
    document.getElementById('videoActions').classList.remove('d-flex');
}
</script>
@endpush
