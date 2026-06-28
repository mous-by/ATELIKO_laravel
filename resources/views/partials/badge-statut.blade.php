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
    'PRET' => 'bg-success',
];
$labels = [
    'EN_ATTENTE' => 'En attente',
    'EN_COURS' => 'En cours',
    'TERMINE' => 'Client a récupéré',
    'VALIDE' => 'Récupéré',
    'ANNULE' => 'Annulé',
    'PLANIFIE' => 'Planifié',
    'CONFIRME' => 'Confirmé',
    'SOLDE' => 'Soldé',
    'PRET' => 'Habit prêt',
];
$cls = $classes[$statut] ?? 'bg-secondary';
$lbl = $labels[$statut] ?? $statut;
@endphp
<span class="badge {{ $cls }}">{{ $lbl }}</span>
