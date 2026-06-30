@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="row g-4">
    {{-- ── Carte gauche : photo + infos ── --}}
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body pt-4 pb-3">

                {{-- Zone photo cliquable --}}
                <form action="{{ route('profile.photo.upload') }}" method="POST"
                      enctype="multipart/form-data" id="photoForm">
                    @csrf

                    <div class="position-relative d-inline-block mb-1" style="cursor:pointer"
                         onclick="document.getElementById('photoInput').click()">

                        {{-- Aperçu / photo actuelle --}}
                        <img id="photoPreview"
                             src="{{ $user->photo_url ?? '' }}"
                             class="rounded-circle @if(!$user->photo_url) d-none @endif"
                             style="width:100px;height:100px;object-fit:cover;border:3px solid #e0e0e0;">

                        {{-- Initiales (si pas de photo) --}}
                        <div id="photoInitials"
                             class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto @if($user->photo_url) d-none @endif"
                             style="width:100px;height:100px;font-size:2rem;">
                            {{ strtoupper(substr($user->prenom, 0, 1)) }}
                        </div>

                        {{-- Badge caméra --}}
                        <span class="position-absolute bottom-0 end-0 bg-primary rounded-circle d-flex align-items-center justify-content-center"
                              style="width:28px;height:28px;border:2px solid #fff;">
                            <i class="bx bx-camera text-white" style="font-size:.85rem"></i>
                        </span>
                    </div>

                    <input type="file" id="photoInput" name="photo" class="d-none" accept="image/*">

                    {{-- Boutons visibles après sélection --}}
                    <div id="photoActions" class="d-none mt-2 d-flex justify-content-center gap-2">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bx bx-upload me-1"></i>Enregistrer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="cancelPhoto()">Annuler</button>
                    </div>
                </form>

                {{-- Nom / rôle / infos --}}
                <h5 class="fw-bold mt-2 mb-0">{{ $user->prenom }} {{ $user->nom }}</h5>
                <p class="mb-1"><span class="badge bg-primary">{{ $user->role }}</span></p>
                <p class="text-muted small mb-1"><i class="bx bx-envelope me-1"></i>{{ $user->email }}</p>
                @if($user->telephone)
                <p class="text-muted small mb-1"><i class="bx bx-phone me-1"></i>{{ $user->telephone }}</p>
                @endif
                @if($user->atelier)
                <p class="text-muted small mb-2"><i class="bx bx-building me-1"></i>{{ $user->atelier->nom }}</p>
                @endif

                {{-- Supprimer photo --}}
                @if($user->photo_url)
                <form action="{{ route('profile.photo.delete') }}" method="POST"
                      onsubmit="return confirm('Supprimer la photo de profil ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bx bx-trash me-1"></i>Supprimer la photo
                    </button>
                </form>
                @endif

            </div>
        </div>
    </div>

    {{-- ── Colonne droite ── --}}
    <div class="col-lg-8">
        {{-- Modifier profil --}}
        <div class="card mb-3">
            <div class="card-header bg-white">
                <i class="bx bx-user-circle me-2 text-primary"></i>Modifier mes informations
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="prenom" class="form-control"
                                   value="{{ $user->prenom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control"
                                   value="{{ $user->nom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="{{ $user->telephone }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="bx bx-save me-2"></i>Mettre à jour
                    </button>
                </form>
            </div>
        </div>

        {{-- Changer mot de passe --}}
        <div class="card">
            <div class="card-header bg-white">
                <i class="bx bx-lock me-2 text-warning"></i>Changer le mot de passe
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="new_password" class="form-control"
                                   minlength="4" placeholder="Ex : 1234" required>
                            <div class="form-text">Minimum 4 caractères.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirmer</label>
                            <input type="password" name="new_password_confirmation"
                                   class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning mt-3">
                        <i class="bx bx-lock me-2"></i>Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const originalSrc = @json($user->photo_url ?? '');
const hasPhoto    = @json((bool) $user->photo_url);

document.getElementById('photoInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('photoPreview');
        preview.src = e.target.result;
        preview.classList.remove('d-none');
        document.getElementById('photoInitials').classList.add('d-none');
        document.getElementById('photoActions').classList.remove('d-none');
        document.getElementById('photoActions').classList.add('d-flex');
    };
    reader.readAsDataURL(file);
});

function cancelPhoto() {
    document.getElementById('photoInput').value = '';
    const preview  = document.getElementById('photoPreview');
    const initials = document.getElementById('photoInitials');
    const actions  = document.getElementById('photoActions');

    if (hasPhoto) {
        preview.src = originalSrc;
        preview.classList.remove('d-none');
        initials.classList.add('d-none');
    } else {
        preview.classList.add('d-none');
        initials.classList.remove('d-none');
    }

    actions.classList.add('d-none');
    actions.classList.remove('d-flex');
}
</script>
@endpush
@endsection
