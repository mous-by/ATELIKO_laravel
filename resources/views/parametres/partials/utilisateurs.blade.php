<div class="card">
    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white">Liste des utilisateurs</h6>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#userCreate">
            <i class="bx bx-user-plus me-1"></i>Ajouter
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Atelier</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($utilisateurs as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $u->photo_url ?? asset('assets/images/default-user.jpg') }}" class="avatar-img" alt="">
                                <strong>{{ $u->prenom }} {{ $u->nom }}</strong>
                            </div>
                        </td>
                        <td><span class="badge bg-primary">{{ $u->role }}</span></td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->telephone ?: '—' }}</td>
                        <td>{{ $u->atelier?->nom ?: '—' }}</td>
                        <td><span class="badge {{ $u->actif ? 'bg-success' : 'bg-secondary' }}">{{ $u->actif ? 'Actif' : 'Inactif' }}</span></td>
                        <td>
                            <a class="btn btn-sm btn-outline-info" href="{{ route('parametres.index', ['section'=>'assigner','utilisateur'=>$u->id]) }}">
                                <i class="bx bx-key"></i>
                            </a>
                            <form action="{{ route('utilisateurs.activation',$u->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning" title="{{ $u->actif ? 'Désactiver' : 'Activer' }}">
                                    <i class="bx bx-power-off"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun utilisateur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($errors->any())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('userCreate');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
});
</script>
@endpush
@endif

<div class="modal fade" id="userCreate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('utilisateurs.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Prénom *</label>
                            <input name="prenom" class="form-control" value="{{ old('prenom') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Nom *</label>
                            <input name="nom" class="form-control" value="{{ old('nom') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Téléphone</label>
                            <input name="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="Ex : 77000000">
                        </div>
                        @if($user->isSuperAdmin())
                        <div class="col-sm-6">
                            <label class="form-label">Atelier *</label>
                            <select name="atelier_id" class="form-select" required>
                                <option value="">Choisir un atelier</option>
                                @foreach($ateliers as $atelier)
                                <option value="{{ $atelier->id }}" @selected(old('atelier_id') === $atelier->id)>{{ $atelier->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-sm-6">
                            <label class="form-label">Rôle *</label>
                            <select name="role" class="form-select" required>
                                @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Mot de passe *</label>
                            <input type="password" name="mot_de_passe" class="form-control" minlength="4" placeholder="Ex : 1234" required>
                            <div class="form-text">Minimum 4 caractères. Exemple simple accepté : 1234.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Confirmation *</label>
                            <input type="password" name="mot_de_passe_confirmation" class="form-control" minlength="4" placeholder="Répéter le même mot de passe" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary"><i class="bx bx-check-circle me-1"></i>Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
