@php
$classes = [
    'EN_ATTENTE' => 'bg-warning text-dark',
    'EN_COURS' => 'bg-primary',
    'TERMINE' => 'bg-success',
    'VALIDE' => 'bg-teal',
    'ANNULE' => 'bg-danger',
    'PLANIFIE' => 'bg-secondary',
    'CONFIRME' => 'bg-primary',
    'SOLDE' => 'bg-success',
];
$labels = [
    'EN_ATTENTE' => 'En attente',
    'EN_COURS' => 'En cours',
    'TERMINE' => 'Terminé',
    'VALIDE' => 'Validé',
    'ANNULE' => 'Annulé',
    'PLANIFIE' => 'Planifié',
    'CONFIRME' => 'Confirmé',
    'SOLDE' => 'Soldé',
];
$cls = $classes[$statut] ?? 'bg-secondary';
$lbl = $labels[$statut] ?? $statut;
@endphp
<span class="badge {{ $cls }}">{{ $lbl }}</span>
