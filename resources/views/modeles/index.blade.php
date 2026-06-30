@extends('layouts.app')
@section('title','Albums')
@section('page-title','Albums')

@push('styles')
<style>
.modele-card{transition:.25s;overflow:hidden}
.modele-card:hover{transform:translateY(-3px);box-shadow:0 .5rem 1rem rgba(0,0,0,.15)}
.modele-photo-container{position:relative;overflow:hidden;height:280px;background:#f0f0f0}
.modele-photo{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.modele-card:hover .modele-photo{transform:scale(1.03)}
.modele-actions{position:absolute;top:8px;right:8px;opacity:0;transform:translateY(-6px);transition:.25s;display:flex;gap:5px}
.modele-photo-container:hover .modele-actions{opacity:1;transform:none}
@media(max-width:768px){.modele-photo-container{height:220px}.modele-actions{opacity:1;transform:none}}

/* ── Zone upload rapide ── */
.quick-drop{border:2px dashed #b8c4d3;border-radius:12px;padding:28px 20px;text-align:center;cursor:pointer;transition:.2s;background:#f8fafc}
.quick-drop:hover,.quick-drop.drag-over{border-color:#0d6efd;background:rgba(13,110,253,.06)}
.quick-drop i{font-size:2.4rem;color:#0d6efd}
.quick-thumb-grid{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.quick-thumb{position:relative;width:90px;height:90px;border-radius:8px;overflow:hidden;background:#eee;flex-shrink:0}
.quick-thumb img{width:100%;height:100%;object-fit:cover}
.quick-thumb-remove{position:absolute;top:3px;right:3px;background:rgba(0,0,0,.55);border:none;border-radius:50%;width:20px;height:20px;color:#fff;font-size:12px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center}
html.dark-theme .quick-drop{background:#2a2d3e;border-color:#3a3d50}
html.dark-theme .quick-drop:hover,html.dark-theme .quick-drop.drag-over{background:rgba(13,110,253,.12);border-color:#0d6efd}
</style>
@endpush

@section('content')

{{-- ── En-tête ── --}}
<div class="page-breadcrumb d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="breadcrumb-title pe-3">Albums</div>
    <div class="ps-3">
        <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Gestion des modèles</li>
        </ol>
    </div>
</div>

{{-- ── Zones upload rapide ── --}}
<div class="row g-3 mb-4">

    {{-- Photos --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body p-3">
                <label class="quick-drop w-100 mb-0" id="quickDrop" for="quickInput">
                    <i class="bx bx-images d-block mb-1"></i>
                    <div class="fw-semibold">Photos</div>
                    <div class="text-muted small mt-1">Glissez ou cliquez — plusieurs à la fois</div>
                    <input type="file" id="quickInput" multiple accept="image/*" class="d-none">
                </label>
                <div id="quickPreviewArea" class="d-none mt-3">
                    <div class="quick-thumb-grid" id="quickThumbGrid"></div>
                    <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                        <button id="quickSubmitBtn" class="btn btn-primary btn-sm">
                            <i class="bx bx-upload me-1"></i>Enregistrer <span id="quickCount">0</span> photo(s)
                        </button>
                        <button id="quickCancelBtn" type="button" class="btn btn-outline-secondary btn-sm">Annuler</button>
                        <span id="quickSpinner" class="d-none text-muted small">
                            <span class="spinner-border spinner-border-sm me-1"></span>Envoi…
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vidéos --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body p-3">
                <label class="quick-drop w-100 mb-0" id="quickVideoDrop" for="quickVideoInput">
                    <i class="bx bx-video d-block mb-1"></i>
                    <div class="fw-semibold">Vidéos</div>
                    <div class="text-muted small mt-1">Glissez ou cliquez — MP4, MOV, AVI (max 100 Mo chacune)</div>
                    <input type="file" id="quickVideoInput" multiple accept="video/mp4,video/quicktime,video/x-msvideo,video/webm" class="d-none">
                </label>
                <div id="quickVideoPreviewArea" class="d-none mt-3">
                    <div class="quick-thumb-grid" id="quickVideoThumbGrid"></div>
                    <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                        <button id="quickVideoSubmitBtn" class="btn btn-danger btn-sm">
                            <i class="bx bx-upload me-1"></i>Enregistrer <span id="quickVideoCount">0</span> vidéo(s)
                        </button>
                        <button id="quickVideoCancelBtn" type="button" class="btn btn-outline-secondary btn-sm">Annuler</button>
                        <span id="quickVideoSpinner" class="d-none text-muted small">
                            <span class="spinner-border spinner-border-sm me-1"></span>Envoi…
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Grille modèles ── --}}
<div class="row" id="modelesGrid">
    @forelse($modeles as $modele)
    @php
        $nb = $modele->mesures_count ?? 0;
        if ($nb === 0)     { $stars = 0; }
        elseif ($nb <= 2)  { $stars = 1; }
        elseif ($nb <= 5)  { $stars = 2; }
        elseif ($nb <= 10) { $stars = 3; }
        elseif ($nb <= 20) { $stars = 4; }
        else               { $stars = 5; }
    @endphp
    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card modele-card radius-10">
            <div class="card-body p-0">
                <div class="modele-photo-container">
                    @if($modele->video_url)
                        <video src="{{ $modele->video_url }}" class="modele-photo" controls></video>
                    @elseif($modele->photo_url)
                        <img src="{{ $modele->photo_url }}" class="modele-photo" alt="{{ $modele->nom }}">
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center">
                            <i class="bx bx-t-shirt display-1 text-muted"></i>
                        </div>
                    @endif
                    <div class="modele-actions">
                        <a href="{{ route('modeles.edit', $modele->id) }}" class="btn btn-sm btn-light" title="Modifier">
                            <i class="bx bx-edit"></i>
                        </a>
                        <form action="{{ route('modeles.destroy', $modele->id) }}" method="POST"
                              data-confirm="Supprimer ce modèle ?"
                              data-confirm-text="Cette action est irréversible."
                              data-confirm-btn="Supprimer">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light" title="Supprimer">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Badge étoiles sur la photo --}}
                    @if($nb > 0)
                    <div class="position-absolute bottom-0 start-0 p-2">
                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill"
                              style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px)"
                              title="{{ $nb }} client(s) ont choisi ce modèle">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bx bxs-star" style="font-size:.8rem;color:{{ $i <= $stars ? '#ffc107' : 'rgba(255,255,255,.25)' }}"></i>
                            @endfor
                        </span>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="bx bx-image-add bx-lg text-muted mb-3"></i>
        <h5 class="text-muted">Aucun modèle — glissez vos premières photos ci-dessus</h5>
    </div>
    @endforelse
</div>

<div class="d-flex justify-content-center">{{ $modeles->withQueryString()->links() }}</div>

@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function makeUploadZone({ inputId, dropId, previewAreaId, thumbGridId, countId, submitId, cancelId, spinnerId, fileKey, accept, uploadUrl, isVideo }) {
    const input     = document.getElementById(inputId);
    const drop      = document.getElementById(dropId);
    const preview   = document.getElementById(previewAreaId);
    const thumbGrid = document.getElementById(thumbGridId);
    const countEl   = document.getElementById(countId);
    const submitBtn = document.getElementById(submitId);
    const cancelBtn = document.getElementById(cancelId);
    const spinner   = document.getElementById(spinnerId);
    let files = [];

    function thumb(file, i) {
        const div = document.createElement('div');
        div.className = 'quick-thumb';
        if (isVideo) {
            const v = document.createElement('video');
            v.src = URL.createObjectURL(file);
            v.style.cssText = 'width:100%;height:100%;object-fit:cover';
            div.appendChild(v);
        } else {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            div.appendChild(img);
        }
        const btn = document.createElement('button');
        btn.className = 'quick-thumb-remove';
        btn.innerHTML = '&times;';
        btn.onclick = () => { files.splice(i, 1); render(); };
        div.appendChild(btn);
        return div;
    }

    function render() {
        thumbGrid.innerHTML = '';
        files.forEach((f, i) => thumbGrid.appendChild(thumb(f, i)));
        countEl.textContent = files.length;
        preview.classList.toggle('d-none', files.length === 0);
    }

    function add(newFiles) {
        Array.from(newFiles).forEach(f => {
            if (!files.find(x => x.name === f.name && x.size === f.size)) files.push(f);
        });
        render();
    }

    input.addEventListener('change', e => { add(e.target.files); input.value = ''; });
    drop.addEventListener('dragover',  e => { e.preventDefault(); drop.classList.add('drag-over'); });
    drop.addEventListener('dragleave', () => drop.classList.remove('drag-over'));
    drop.addEventListener('drop', e => { e.preventDefault(); drop.classList.remove('drag-over'); add(e.dataTransfer.files); });
    cancelBtn.addEventListener('click', () => { files = []; render(); });

    submitBtn.addEventListener('click', async () => {
        if (!files.length) return;
        submitBtn.disabled = cancelBtn.disabled = true;
        spinner.classList.remove('d-none');
        const fd = new FormData();
        files.forEach(f => fd.append(fileKey, f));
        fd.append('_token', csrf);
        try {
            const res = await fetch(uploadUrl, { method: 'POST', body: fd });
            if (!res.ok) throw new Error();
            files = []; render();
            window.location.reload();
        } catch {
            alert('Erreur lors de l\'envoi. Réessayez.');
            submitBtn.disabled = cancelBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
}

makeUploadZone({
    inputId: 'quickInput', dropId: 'quickDrop',
    previewAreaId: 'quickPreviewArea', thumbGridId: 'quickThumbGrid',
    countId: 'quickCount', submitId: 'quickSubmitBtn',
    cancelId: 'quickCancelBtn', spinnerId: 'quickSpinner',
    fileKey: 'photos[]', accept: 'image/*',
    uploadUrl: @json(route('modeles.quick-store')),
    isVideo: false,
});

makeUploadZone({
    inputId: 'quickVideoInput', dropId: 'quickVideoDrop',
    previewAreaId: 'quickVideoPreviewArea', thumbGridId: 'quickVideoThumbGrid',
    countId: 'quickVideoCount', submitId: 'quickVideoSubmitBtn',
    cancelId: 'quickVideoCancelBtn', spinnerId: 'quickVideoSpinner',
    fileKey: 'videos[]', accept: 'video/*',
    uploadUrl: @json(route('modeles.quick-store-videos')),
    isVideo: true,
});
</script>
@endpush
