@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body pt-4">
                @if($user->photo_url)
                    <img src="{{ $user->photo_url }}" class="rounded-circle mb-2"
                         style="width: 100px; height: 100px; object-fit: cover;">
                    <div class="mb-3">
                        <form action="{{ route('profile.photo.delete') }}" method="POST"
                              onsubmit="return confirm('Supprimer la photo de profil ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bx bx-trash me-1"></i>Supprimer la photo
                            </button>
                        </form>
                    </div>
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width: 100px; height: 100px; font-size: 2rem;">
                        {{ strtoupper(substr($user->prenom, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold">{{ $user->prenom }} {{ $user->nom }}</h5>
                <p class="badge bg-primary">{{ $user->role }}</p>
                <p class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $user->email }}</p>
                @if($user->telephone)
                <p class="text-muted"><i class="bx bx-phone me-1"></i>{{ $user->telephone }}</p>
                @endif
                @if($user->atelier)
                <p class="text-muted"><i class="bx bx-building me-1"></i>{{ $user->atelier->nom }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Modifier profil -->
        <div class="card mb-3">
            <div class="card-header bg-white"><i class="bx bx-user-circle me-2 text-primary"></i>Modifier mes informations</div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" value="{{ $user->prenom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" value="{{ $user->nom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ $user->telephone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nouvelle photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="bx bx-save me-2"></i>Mettre à jour
                    </button>
                </form>
            </div>
        </div>

        <!-- Changer mot de passe -->
        <div class="card">
            <div class="card-header bg-white"><i class="bx bx-lock me-2 text-warning"></i>Changer le mot de passe</div>
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
                            <input type="password" name="new_password" class="form-control" minlength="4" placeholder="Ex : 1234" required>
                            <div class="form-text">Minimum 4 caractères.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirmer</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
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
@endsection
