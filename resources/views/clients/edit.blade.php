@extends('layouts.app')

@section('title', 'Modifier ' . $client->prenom)
@section('page-title', 'Modifier le client')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bx bx-edit me-2"></i>Modifier : {{ $client->prenom }} {{ $client->nom }}
            </div>
            <div class="card-body">
                <form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" value="{{ $client->prenom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nom *</label>
                            <input type="text" name="nom" class="form-control" value="{{ $client->nom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Sexe</label>
                            <select name="sexe" class="form-select">
                                <option value="">-- Sélectionner --</option>
                                <option value="Femme" {{ $client->sexe == 'Femme' ? 'selected' : '' }}>Femme</option>
                                <option value="Homme" {{ $client->sexe == 'Homme' ? 'selected' : '' }}>Homme</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Contact</label>
                            <input type="text" name="contact" class="form-control" value="{{ $client->contact }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="{{ $client->adresse }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $client->email }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Nouvelle photo</label>
                            @if($client->photo_url)
                                <div class="mb-2">
                                    <img src="{{ $client->photo_url }}" style="height: 60px; border-radius: 50%;">
                                    <small class="text-muted ms-2">Photo actuelle</small>
                                </div>
                            @endif
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-save me-2"></i>Sauvegarder
                        </button>
                        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
