@extends('layouts.app')
@section('title', 'Permissions - ' . $utilisateur->prenom)
@section('page-title', 'Permissions de ' . $utilisateur->prenom . ' ' . $utilisateur->nom)

@push('styles')
<style>
html.dark-theme .perm-card-header      { background: #2a2d3e !important; color: #e4e5e6; border-color: #3a3d50; }
html.dark-theme .perm-card-header .btn-outline-primary   { color: #6ea8fe; border-color: #6ea8fe; }
html.dark-theme .perm-card-header .btn-outline-secondary { color: #adb5bd; border-color: #6c757d; }
html.dark-theme .form-check.border     { border-color: #3a3d50 !important; background: rgba(255,255,255,.03); }
html.dark-theme .perm-code             { background: rgba(13,110,253,.18) !important; color: #a8c7fa !important; }
html.dark-theme .form-check-label .text-muted { color: #8a9bb0 !important; }
</style>
@endpush

@section('content')
@php
$modules = [
    'Clients'      => ['CLIENT_VOIR','CLIENT_CREER','CLIENT_MODIFIER','CLIENT_SUPPRIMER'],
    'Modèles'      => ['MODELE_VOIR','MODELE_CREER','MODELE_MODIFIER','MODELE_SUPPRIMER'],
    'Affectations' => ['AFFECTATION_VOIR','AFFECTATION_CREER','AFFECTATION_MODIFIER','AFFECTATION_SUPPRIMER'],
    'Rendez-vous'  => ['RENDEZ_VOUS_VOIR','RENDEZ_VOUS_CREER','RENDEZ_VOUS_MODIFIER','RENDEZ_VOUS_SUPPRIMER'],
    'Paiements'    => ['PAIEMENT_VOIR','PAIEMENT_CREER'],
    'Utilisateurs' => ['UTILISATEUR_VOIR','UTILISATEUR_CREER','UTILISATEUR_MODIFIER','UTILISATEUR_SUPPRIMER'],
    'Rapports'     => ['RAPPORT_VOIR'],
];

// Index des codes de permission disponibles pour un accès rapide
$permissionsByCode = $allPermissions->keyBy('code');
// Codes déjà attribués à cet utilisateur
$userPermCodes = $utilisateur->permissions->pluck('code')->toArray();
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10">

        <div class="card mb-3">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-key me-2"></i>
                    <strong>{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</strong>
                    <span class="badge bg-white text-info ms-2">{{ $utilisateur->role }}</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="btnToutCocher">
                        <i class="bx bx-check-square me-1"></i>Tout cocher
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnToutDecocher">
                        <i class="bx bx-square me-1"></i>Tout décocher
                    </button>
                </div>
            </div>
        </div>

        <form action="{{ route('utilisateurs.permissions.save', $utilisateur->id) }}" method="POST" id="formPermissions">
            @csrf

            @if($allPermissions->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="bx bx-lock fs-2"></i>
                    <p class="mt-2 mb-0">Aucune permission configurée dans le système</p>
                </div>
            </div>
            @else

            @foreach($modules as $moduleNom => $codes)
            @php
                // Ne rendre le module que si au moins une permission existe en base
                $modulePerms = collect($codes)->filter(fn($c) => $permissionsByCode->has($c));
            @endphp
            @if($modulePerms->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header perm-card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        <i class="bx bx-folder-open me-2 text-primary"></i>{{ $moduleNom }}
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-cocher-module"
                                data-module="{{ $moduleNom }}">
                            <i class="bx bx-check-square me-1"></i>Tout cocher
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-decocher-module"
                                data-module="{{ $moduleNom }}">
                            <i class="bx bx-square me-1"></i>Tout décocher
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($modulePerms as $code)
                        @php $perm = $permissionsByCode->get($code); @endphp
                        <div class="col-md-6 col-lg-3">
                            <div class="form-check border rounded p-2 h-100">
                                <input class="form-check-input perm-checkbox perm-module-{{ Str::slug($moduleNom) }}"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $perm->code }}"
                                       id="perm_{{ $perm->id }}"
                                       data-module="{{ $moduleNom }}"
                                       {{ in_array($perm->code, $userPermCodes) ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="perm_{{ $perm->id }}">
                                    <code class="perm-code bg-light px-1 rounded small d-block mb-1">{{ $perm->code }}</code>
                                    @if($perm->description)
                                        <small class="text-muted">{{ $perm->description }}</small>
                                    @endif
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @endforeach

            @endif

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-info text-white">
                    <i class="bx bx-save me-2"></i>Enregistrer les permissions
                </button>
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Retour
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Tout cocher global
    document.getElementById('btnToutCocher')?.addEventListener('click', function () {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    });

    // Tout décocher global
    document.getElementById('btnToutDecocher')?.addEventListener('click', function () {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    });

    // Tout cocher par module
    document.querySelectorAll('.btn-cocher-module').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const module = this.getAttribute('data-module');
            document.querySelectorAll('.perm-checkbox[data-module="' + module + '"]')
                .forEach(cb => cb.checked = true);
        });
    });

    // Tout décocher par module
    document.querySelectorAll('.btn-decocher-module').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const module = this.getAttribute('data-module');
            document.querySelectorAll('.perm-checkbox[data-module="' + module + '"]')
                .forEach(cb => cb.checked = false);
        });
    });
})();
</script>
@endpush
