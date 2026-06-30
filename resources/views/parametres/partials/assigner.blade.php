@php
    $selected = $utilisateurs->firstWhere('id', request('utilisateur'));
    $groups = [
        'utilisateurs' => ['Utilisateurs', 'bx-user-cog', ['UTILISATEUR_VOIR','UTILISATEUR_CREER','UTILISATEUR_MODIFIER','UTILISATEUR_SUPPRIMER']],
        'clients' => ['Clients', 'bx-group', ['CLIENT_VOIR','CLIENT_CREER','CLIENT_MODIFIER','CLIENT_SUPPRIMER']],
        'modeles' => ['Modèles', 'bx-photo-album', ['MODELE_VOIR','MODELE_CREER','MODELE_MODIFIER','MODELE_SUPPRIMER']],
        'affectations' => ['Affectations', 'bx-user-check', ['AFFECTATION_VOIR','AFFECTATION_CREER','AFFECTATION_MODIFIER','AFFECTATION_SUPPRIMER']],
        'rendezvous' => ['Rendez-vous', 'bx-calendar', ['RENDEZ_VOUS_VOIR','RENDEZ_VOUS_CREER','RENDEZ_VOUS_MODIFIER','RENDEZ_VOUS_SUPPRIMER']],
        'paiements' => ['Paiements', 'bx-money', ['PAIEMENT_VOIR','PAIEMENT_CREER']],
        'rapports' => ['Rapports', 'bx-bar-chart-alt-2', ['RAPPORT_VOIR']],
    ];
    $totalPermissions = $permissions->count();
    $checkedCount = $selected?->isSuperAdmin() ? $totalPermissions : ($selected?->permissions->count() ?? 0);
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0 fw-bold"><i class="bx bx-user-check me-2"></i>Assignation de permissions</h5>
        <a href="{{ route('parametres.index',['section'=>'utilisateurs']) }}" class="btn btn-light px-4"><i class="bx bx-arrow-back me-2"></i>Retour</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('parametres.index') }}" id="selectUserPermissionForm">
            <input type="hidden" name="section" value="assigner">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Utilisateur</label>
                    <input type="text" id="userFilter" class="form-control mb-2" placeholder="Filtrer les utilisateurs...">
                    <select name="utilisateur" id="permission_user_id" class="form-select" required>
                        <option value="">Choisir un utilisateur</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}" @selected($selected?->id === $u->id)>{{ $u->prenom }} {{ $u->nom }} - {{ $u->role }} - {{ $u->atelier?->nom ?? 'Tous les ateliers' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-bold">Filtrer les permissions</label><input type="text" id="permissionFilter" class="form-control" placeholder="Ex: client, modifier..."></div>
            </div>
        </form>
        @if($selected)
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
            <div><h4 class="fw-bold mb-1">{{ $selected->prenom }} {{ $selected->nom }}</h4><div class="text-muted">{{ $selected->email }} - {{ $selected->role }} - {{ $selected->atelier?->nom ?? 'Tous les ateliers' }}</div></div>
            <span class="badge bg-light-primary text-primary fs-6 px-3 py-2" id="permissionCounter">{{ $checkedCount }} / {{ $totalPermissions }} cochées</span>
        </div>
        @endif
    </div>
</div>

@if($selected)
<form method="POST" action="{{ route('utilisateurs.permissions.save',$selected->id) }}">@csrf
    <div class="card shadow-sm mb-4"><div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div><strong>Organisation :</strong> permissions triées par module puis par action, comme KalanNet.</div>
        @if($selected->isSuperAdmin())
            <span class="badge bg-success fs-6"><i class="bx bx-shield-quarter me-1"></i>Toutes les permissions sont garanties</span>
        @else
        <div class="d-flex align-items-center gap-3"><div class="form-check m-0"><input class="form-check-input" type="checkbox" id="select_all_permissions"><label class="form-check-label fw-bold" for="select_all_permissions">Tout cocher/décocher</label></div><button class="btn btn-primary shadow-sm"><i class="bx bx-save me-2"></i>Enregistrer</button></div>
        @endif
    </div></div>

    <div class="row g-3" id="permissionModules">
    @foreach($groups as $module => [$moduleLabel,$icon,$codes])
        @php $modulePermissions = $permissions->whereIn('code',$codes); @endphp
        @if($modulePermissions->isNotEmpty())
        <div class="col-12 permission-module" data-search="{{ strtolower($moduleLabel.' '.$modulePermissions->pluck('code')->join(' ').' '.$modulePermissions->pluck('description')->join(' ')) }}">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2"><div class="fw-bold"><i class="bx {{ $icon }} me-2"></i>{{ $moduleLabel }}<span class="badge bg-light text-primary ms-2">{{ $modulePermissions->count() }}</span></div>@unless($selected->isSuperAdmin())<div class="form-check m-0"><input class="form-check-input module-checkbox" type="checkbox" id="module_{{ $module }}" data-module="{{ $module }}"><label class="form-check-label" for="module_{{ $module }}">Tout le module</label></div>@endunless</div>
                <div class="card-body"><div class="row g-2">
                @foreach($modulePermissions as $permission)
                    @php $checked=$selected->isSuperAdmin() || $selected->permissions->contains('id',$permission->id); @endphp
                    <div class="col-md-6 col-xl-4 permission-item"><div class="form-check permission-check"><input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->code }}" id="perm_{{ $permission->id }}" data-module="{{ $module }}" @checked($checked) @disabled($selected->isSuperAdmin())><label class="form-check-label" for="perm_{{ $permission->id }}"><strong>{{ $permission->description ?: str_replace('_',' ',$permission->code) }}</strong><small class="d-block text-muted">{{ $permission->code }}</small></label></div></div>
                @endforeach
                </div></div>
            </div>
        </div>
        @endif
    @endforeach
    </div>
    @unless($selected->isSuperAdmin())<div class="text-end mt-4 mb-4"><button class="btn btn-primary px-5 shadow-sm"><i class="bx bx-save me-2"></i>Enregistrer</button></div>@endunless
</form>
@else
<div class="card shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="bx bx-user-check d-block mb-3" style="font-size:3rem"></i>Sélectionnez un utilisateur pour charger ses permissions cochées.</div></div>
@endif

<style>
.permission-check{border:1px solid #dee2e6;border-radius:8px;padding:12px 12px 12px 38px;min-height:70px;background:#fff}
.permission-check:has(.form-check-input:checked){background:#eef5ff;border-color:#0d6efd}
html.dark-theme .permission-check{background:#2a2d3e;border-color:#3a3d50;color:#e4e5e6}
html.dark-theme .permission-check:has(.form-check-input:checked){background:rgba(13,110,253,.15);border-color:#0d6efd}
html.dark-theme .permission-check .text-muted{color:#8a8d9e!important}
</style>
<script>document.addEventListener('DOMContentLoaded',()=>{const norm=v=>String(v||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');const user=document.getElementById('permission_user_id');document.getElementById('userFilter')?.addEventListener('input',e=>Array.from(user.options).forEach((o,i)=>{if(i)o.hidden=!norm(o.textContent).includes(norm(e.target.value))}));user?.addEventListener('change',()=>user.value&&document.getElementById('selectUserPermissionForm').submit());const boxes=[...document.querySelectorAll('.permission-checkbox')],all=document.getElementById('select_all_permissions'),modules=[...document.querySelectorAll('.module-checkbox')],counter=document.getElementById('permissionCounter');const sync=()=>{const n=boxes.filter(b=>b.checked).length;if(counter)counter.textContent=`${n} / ${boxes.length} cochées`;if(all){all.checked=n===boxes.length;all.indeterminate=n>0&&n<boxes.length}modules.forEach(m=>{const set=boxes.filter(b=>b.dataset.module===m.dataset.module),c=set.filter(b=>b.checked).length;m.checked=c===set.length;m.indeterminate=c>0&&c<set.length})};all?.addEventListener('change',()=>{boxes.forEach(b=>b.checked=all.checked);sync()});modules.forEach(m=>m.addEventListener('change',()=>{boxes.filter(b=>b.dataset.module===m.dataset.module).forEach(b=>b.checked=m.checked);sync()}));boxes.forEach(b=>b.addEventListener('change',sync));document.getElementById('permissionFilter')?.addEventListener('input',e=>document.querySelectorAll('.permission-module').forEach(m=>m.style.display=norm(m.dataset.search).includes(norm(e.target.value))?'':'none'));sync()});</script>
