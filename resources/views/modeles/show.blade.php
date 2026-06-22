@extends('layouts.app')
@section('title', $modele->nom)
@section('page-title', $modele->nom)

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        @if($modele->photo_path)
            <img src="{{ asset('storage/' . $modele->photo_path) }}" class="img-fluid rounded shadow" alt="{{ $modele->nom }}">
        @else
            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
                <i class="bx bx-image fs-1"></i>
            </div>
        @endif
        @if($modele->video_path)
            <video class="w-100 mt-3 rounded" controls>
                <source src="{{ asset('storage/' . $modele->video_path) }}">
            </video>
        @endif
    </div>
    <div class="col-md-7">
        <div class="d-flex gap-2 mb-3">
            <span class="badge bg-primary fs-6">{{ $modele->categorie }}</span>
            @if(!$modele->est_actif)
                <span class="badge bg-secondary">Inactif</span>
            @endif
        </div>
        <h3 class="fw-bold">{{ $modele->nom }}</h3>
        @if($modele->prix)
            <h4 class="text-primary">{{ number_format($modele->prix, 0, ',', ' ') }} FCFA</h4>
        @endif
        @if($modele->description)
            <p class="text-muted mt-3">{{ $modele->description }}</p>
        @endif
        <p class="text-muted small mt-3">Créé le {{ $modele->date_creation?->format('d/m/Y') }}</p>
        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('modeles.edit', $modele->id) }}" class="btn btn-warning">
                <i class="bx bx-edit me-1"></i>Modifier
            </a>
            <a href="{{ route('modeles.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Retour
            </a>
            <form action="{{ route('modeles.destroy', $modele->id) }}" method="POST"
                  data-confirm="Supprimer ce modèle ?"
                  data-confirm-text="Cette action est irréversible."
                  data-confirm-btn="Supprimer">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i>Supprimer</button>
            </form>
        </div>
    </div>
</div>
@endsection
