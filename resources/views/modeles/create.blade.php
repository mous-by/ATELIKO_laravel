@extends('layouts.app')
@section('title', 'Nouveau modèle')
@section('page-title', 'Ajouter un modèle')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bx bx-photo-album me-2"></i>Nouveau modèle
            </div>
            <div class="card-body">
                <form action="{{ route('modeles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium">Nom du modèle *</label>
                            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Catégorie *</label>
                            <select name="categorie" class="form-select" required>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Prix (FCFA)</label>
                            <input type="number" name="prix" class="form-control" value="{{ old('prix') }}" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" id="photoInput">
                            <div id="photoPreview" class="mt-2 d-none">
                                <img id="previewImg" src="" class="rounded" style="max-height: 150px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Vidéo (optionnel)</label>
                            <input type="file" name="video" class="form-control" accept="video/*">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check-circle me-2"></i>Enregistrer
                        </button>
                        <a href="{{ route('modeles.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('photoInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
            document.getElementById('previewImg').src = ev.target.result;
            document.getElementById('photoPreview').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
