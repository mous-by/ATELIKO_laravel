@extends('layouts.app')
@section('title', 'Modifier ' . $modele->nom)
@section('page-title', 'Modifier le modèle')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bx bx-edit me-2"></i>{{ $modele->nom }}
            </div>
            <div class="card-body">
                <form action="{{ route('modeles.update', $modele->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium">Nom *</label>
                            <input type="text" name="nom" class="form-control" value="{{ $modele->nom }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Catégorie *</label>
                            <select name="categorie" class="form-select" required>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $modele->categorie == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Prix (FCFA)</label>
                            <input type="number" name="prix" class="form-control" value="{{ $modele->prix }}" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $modele->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nouvelle photo</label>
                            @if($modele->photo_path)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $modele->photo_path) }}" style="height: 80px; object-fit: cover; border-radius: 6px;">
                                </div>
                            @endif
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nouvelle vidéo</label>
                            <input type="file" name="video" class="form-control" accept="video/*">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-save me-2"></i>Sauvegarder
                        </button>
                        <a href="{{ route('modeles.show', $modele->id) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
