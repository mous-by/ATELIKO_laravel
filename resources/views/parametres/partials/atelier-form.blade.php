@php
    $value = $editing ?? null;
    $currentPlanCode = $value?->abonnement?->plan?->code ?? null;
@endphp
<div class="mb-3"><label class="form-label">Nom de l'atelier *</label><input class="form-control" name="nom" value="{{ old('nom', $value?->nom) }}" required></div>
<div class="mb-3"><label class="form-label">Adresse</label><input class="form-control" name="adresse" value="{{ old('adresse', $value?->adresse) }}"></div>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $value?->email) }}"></div>
    <div class="col-md-6"><label class="form-label">Téléphone</label><input class="form-control" name="telephone" value="{{ old('telephone', $value?->telephone) }}"></div>
</div>
@if(Auth::user()->isSuperAdmin() && isset($subscriptionPlans) && $subscriptionPlans->isNotEmpty())
<div class="mb-0 mt-3">
    <label class="form-label fw-medium">
        Plan d'abonnement {{ $value ? '(modification)' : '(initial)' }}
    </label>
    <select name="plan_code" class="form-select">
        <option value="">-- Aucun plan (activer plus tard) --</option>
        @foreach($subscriptionPlans as $plan)
        <option value="{{ $plan->code }}" @selected(old('plan_code', $currentPlanCode) === $plan->code)>
            {{ $plan->libelle }} — {{ number_format($plan->prix, 0, ',', ' ') }} FCFA / {{ $plan->duree_mois }}mois
        </option>
        @endforeach
    </select>
    <div class="form-text">
        @if($value)
            Si vous changez ce plan, un nouvel abonnement actif sera appliqué à cet atelier.
        @else
            Ce plan sera activé automatiquement à la création de l'atelier.
        @endif
    </div>
</div>
@endif
