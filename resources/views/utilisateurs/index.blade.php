@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <span class="badge bg-primary fs-6">{{ $utilisateurs->count() }} utilisateur(s)</span>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNouvelUtilisateur">
        <i class="bx bx-user-plus me-2"></i>Nouvel utilisateur
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($utilisateurs as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($u->photo_path)
                                    <img src="{{ asset('storage/' . $u->photo_path) }}" class="avatar-img" alt="">
                                @else
                                    <div class="avatar-img bg-primary d-flex align-items-center justify-content-center text-white">
                                        {{ strtoupper(substr($u->prenom, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $u->prenom }} {{ $u->nom }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php $roleColors = ['SUPERADMIN'=>'danger','PROPRIETAIRE'=>'primary','SECRETAIRE'=>'info','TAILLEUR'=>'success']; @endphp
                            <span class="badge bg-{{ $roleColors[$u->role] ?? 'secondary' }}">{{ $u->role }}</span>
                        </td>
                        <td><small>{{ $u->email }}</small></td>
                        <td><small>{{ $u->telephone ?? '—' }}</small></td>
                        <td>
                            @if($u->actif)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('utilisateurs.permissions', $u->id) }}" class="btn btn-sm btn-outline-info" title="Permissions">
                                    <i class="bx bx-key"></i>
                                </a>
                                <form action="{{ route('utilisateurs.activation', $u->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $u->actif ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $u->actif ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $u->actif ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                @if(Auth::user()->id !== $u->id)
                                <form action="{{ route('utilisateurs.destroy', $u->id) }}" method="POST"
                                      data-confirm="Supprimer cet utilisateur ?"
                                      data-confirm-text="Cette action est irréversible."
                                      data-confirm-btn="Supprimer">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Aucun utilisateur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nouvel utilisateur -->
<div class="modal fade" id="modalNouvelUtilisateur" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-user-plus me-2"></i>Nouvel utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('utilisateurs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Nom *</label>
                            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="77XXXXXXX">
                        </div>
                        @if(Auth::user()->isSuperAdmin())
                        <div class="col-12">
                            <label class="form-label fw-medium">Atelier *</label>
                            <select name="atelier_id" class="form-select" required>
                                <option value="">-- Choisir un atelier --</option>
                                @foreach($ateliers as $atelier)
                                <option value="{{ $atelier->id }}" @selected(old('atelier_id') === $atelier->id)>{{ $atelier->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-6">
                            <label class="form-label fw-medium">Rôle *</label>
                            <select name="role" class="form-select" required>
                                @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Mot de passe *</label>
                            <input type="password" name="mot_de_passe" class="form-control" minlength="4" placeholder="Ex : 1234" required>
                            <div class="form-text">Minimum 4 caractères. Exemple simple accepté : 1234.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Confirmer le mot de passe *</label>
                            <input type="password" name="mot_de_passe_confirmation" class="form-control" minlength="4" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check-circle me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@if($errors->any())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('modalNouvelUtilisateur');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
});
</script>
@endpush
@endif
