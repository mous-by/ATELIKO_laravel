@extends('layouts.app')
@section('title', 'Paramètres')
@section('page-title', 'Paramètres')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Paramètres</div>
    <div class="ps-3"><nav><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item active">Configuration</li></ol></nav></div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-3">
        <div class="card h-100">
            <div class="card-header bg-primary"><h6 class="mb-0 text-center text-white">Menu Paramètres</h6></div>
            <div class="card-body p-0 param-menu"><div class="list-group list-group-flush">
                @php
                    $items = [
                        'ateliers' => ['Ateliers', 'bx bx-home-alt'],
                        'abonnement-tarifs' => ['Tarifs abonnement', 'bx bx-money'],
                        'utilisateurs' => ['Utilisateurs', 'bx bx-user'],
                        'assigner' => ['Assigner Permission', 'bx bx-user-pin'],
                        'liste' => ['Liste Permission', 'bx bx-list-ul'],
                    ];
                @endphp
                @foreach($items as $key => [$label, $icon])
                    @continue($key === 'abonnement-tarifs' && !$user->isSuperAdmin())
                    <button type="button" data-settings-url="{{ route('parametres.index', ['section' => $key]) }}" class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 py-3 {{ $section === $key ? 'active' : '' }}"><i class="{{ $icon }} fs-5"></i><span>{{ $label }}</span></button>
                @endforeach
            </div></div>
        </div>
    </div>

    <div class="col-12 col-lg-9">
        @if($section === 'ateliers')
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center"><h6 class="mb-0 text-white">Liste des Ateliers</h6>@if($user->isSuperAdmin())<button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#atelierCreate"><i class="bx bx-plus me-1"></i>Ajouter un atelier</button>@endif</div>
            <div class="card-body"><div class="table-responsive"><table class="table table-bordered table-hover align-middle"><thead class="table-light"><tr><th>Nom</th><th>Adresse</th><th>Email</th><th>Téléphone</th><th>Utilisateurs</th><th>Clients</th><th>Actions</th></tr></thead><tbody>
                @forelse($ateliers as $atelier)<tr><td class="fw-semibold">{{ $atelier->nom }}</td><td>{{ $atelier->adresse ?: '—' }}</td><td>{{ $atelier->email ?: '—' }}</td><td>{{ $atelier->telephone ?: '—' }}</td><td><span class="badge bg-light-primary text-primary">{{ $atelier->utilisateurs_count }}</span></td><td><span class="badge bg-light-success text-success">{{ $atelier->clients_count }}</span></td><td>@if($user->isSuperAdmin())<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#atelierEdit{{ $atelier->id }}"><i class="bx bx-edit"></i></button><form action="{{ route('parametres.ateliers.destroy', $atelier) }}" method="POST" class="d-inline" data-confirm="Supprimer l'atelier {{ $atelier->nom }} ?" data-confirm-text="Tous les utilisateurs et clients associés seront supprimés." data-confirm-btn="Supprimer">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button></form>@endif</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Aucun atelier</td></tr>@endforelse
            </tbody></table></div></div>
        </div>
        @elseif($section === 'abonnement-tarifs')
        <div class="card"><div class="card-header bg-primary d-flex justify-content-between align-items-center"><h6 class="mb-0 text-white">Configuration des tarifs d'abonnement</h6><span class="badge bg-light text-primary">{{ $subscriptionPlans->count() }} plan(s)</span></div><div class="card-body">
            <form action="{{ route('parametres.plans.store') }}" method="POST" class="border rounded p-3 mb-4">@csrf<h6 class="mb-3">Créer un plan d'abonnement</h6><div class="row g-2"><div class="col-md-2"><input name="code" class="form-control" placeholder="CODE" required></div><div class="col-md-3"><input name="libelle" class="form-control" placeholder="Libellé" required></div><div class="col-md-2"><input type="number" name="duree_mois" min="1" class="form-control" placeholder="Durée (mois)" required></div><div class="col-md-2"><input type="number" name="prix" min="1" class="form-control" placeholder="Prix" required></div><div class="col-md-1"><input name="devise" class="form-control" value="XOF" maxlength="10" required></div><div class="col-md-1 d-flex align-items-center"><div class="form-check form-switch"><input type="hidden" name="actif" value="0"><input class="form-check-input" type="checkbox" name="actif" value="1" checked title="Actif"></div></div><div class="col-md-1"><button class="btn btn-primary w-100">Créer</button></div></div></form>
            <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Code</th><th>Libellé</th><th>Durée (mois)</th><th>Montant</th><th>Devise</th><th>Actif</th><th style="width:170px">Action</th></tr></thead><tbody>
            @forelse($subscriptionPlans as $plan)<tr><td><strong>{{ $plan->code }}</strong></td><td><input form="planForm{{ $plan->id }}" name="libelle" class="form-control form-control-sm" value="{{ $plan->libelle }}" required></td><td><input form="planForm{{ $plan->id }}" type="number" name="duree_mois" min="1" class="form-control form-control-sm" value="{{ $plan->duree_mois }}" required></td><td><input form="planForm{{ $plan->id }}" type="number" name="prix" min="1" class="form-control form-control-sm" value="{{ $plan->prix }}" required></td><td><input form="planForm{{ $plan->id }}" name="devise" maxlength="10" class="form-control form-control-sm" value="{{ $plan->devise }}" required></td><td><div class="form-check form-switch m-0"><input form="planForm{{ $plan->id }}" type="hidden" name="actif" value="0"><input form="planForm{{ $plan->id }}" class="form-check-input" type="checkbox" name="actif" value="1" @checked($plan->actif)></div></td><td><div class="d-flex gap-2"><form id="planForm{{ $plan->id }}" action="{{ route('parametres.plans.update',$plan) }}" method="POST">@csrf @method('PUT')<button class="btn btn-sm btn-success">Enregistrer</button></form><form action="{{ route('parametres.plans.destroy',$plan) }}" method="POST" data-confirm="Supprimer le plan {{ $plan->code }} ?" data-confirm-text="Les abonnements associés à ce plan ne seront pas affectés." data-confirm-btn="Supprimer">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button></form></div></td></tr>@empty<tr><td colspan="7" class="text-muted text-center py-3">Aucun plan trouvé</td></tr>@endforelse
            </tbody></table></div>
        </div></div>
        @elseif($section === 'utilisateurs')
        @include('parametres.partials.utilisateurs')
        @elseif($section === 'assigner')
        @include('parametres.partials.assigner')
        @else
        @include('parametres.partials.liste-permissions')
        @endif
    </div>
</div>

@if($user->isSuperAdmin())
<div class="modal fade" id="atelierCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('parametres.ateliers.store') }}" method="POST">@csrf<div class="modal-header bg-primary text-white"><h5 class="modal-title">Ajouter un atelier</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('parametres.partials.atelier-form')</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div></form></div></div></div>
@foreach($ateliers as $atelier)
<div class="modal fade" id="atelierEdit{{ $atelier->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('parametres.ateliers.update', $atelier) }}" method="POST">@csrf @method('PUT')<div class="modal-header bg-primary text-white"><h5 class="modal-title">Modifier l'atelier</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('parametres.partials.atelier-form', ['editing' => $atelier])</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Mettre à jour</button></div></form></div></div></div>
@endforeach
@endif
@push('scripts')<script>document.querySelectorAll('[data-settings-url]').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('[data-settings-url]').forEach(item=>item.classList.remove('active'));button.classList.add('active');window.location.href=button.dataset.settingsUrl}))</script>@endpush
@endsection
