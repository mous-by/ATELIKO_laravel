@extends('layouts.app')
@section('title', 'Documentation')
@section('page-title', 'Documentation')
@section('content')
<div class="row"><div class="col-12"><div class="card"><div class="card-header"><h4 class="card-title">Documentation de l'Application ATELIKO</h4><p class="card-title-desc mb-0">Guide complet d'utilisation de votre atelier de couture</p></div><div class="card-body">
<div class="alert alert-primary"><h5>À propos d'ATELIKO</h5><p class="mb-0">ATELIKO centralise les clients, mesures, modèles, affectations, rendez-vous, paiements, utilisateurs et permissions de l'atelier.</p></div>
@php $sections = [
['Connexion à l’application', ['Renseignez votre email ou téléphone et votre mot de passe.','Utilisez « Se souvenir de moi » uniquement sur votre appareil personnel.','Après connexion, le tableau de bord correspondant à votre rôle est affiché.']],
['Navigation', ['Le menu latéral donne accès aux modules autorisés par votre rôle.','Le menu Paramètres regroupe ateliers, utilisateurs et permissions.','Le menu du profil permet de modifier vos informations et de vous déconnecter.']],
['Gestion des clients et mesures', ['Ajouter un client depuis « Ajouter un client ».','Compléter ses informations, vêtements, prix, avances et mesures.','Consulter, modifier ou supprimer depuis la liste des clients.']],
['Albums et modèles', ['Créer un modèle avec photo, vidéo, catégorie, description et prix.','Filtrer les albums par nom ou catégorie.','Associer les modèles aux vêtements des clients.']],
['Affectations', ['Sélectionner le client, le vêtement et le tailleur.','Renseigner l’échéance et le prix du tailleur.','Suivre les statuts EN_ATTENTE, EN_COURS, TERMINE, VALIDE ou ANNULE.']],
['Rendez-vous', ['Créer ou modifier un rendez-vous client.','Suivre les rendez-vous à venir et leurs statuts.','Confirmer, terminer ou annuler un rendez-vous.']],
['Paiements', ['Basculer entre paiements clients et tailleurs.','Consulter le dû, le payé, le restant et l’historique.','Enregistrer et imprimer les reçus.']],
['Paramètres et permissions', ['Créer et modifier les ateliers en tant que SuperAdmin.','Créer, activer ou désactiver les utilisateurs.','Attribuer les permissions depuis le sous-menu Assigner Permission.']],
]; @endphp
<h2 class="mt-4 mb-3">Guide d'utilisation complet</h2>
@foreach($sections as [$title,$steps])<section class="mb-4"><h3 class="h5 text-primary">{{ $loop->iteration }}. {{ $title }}</h3><ol>@foreach($steps as $step)<li class="mb-1">{{ $step }}</li>@endforeach</ol></section>@endforeach
<div class="alert alert-warning mb-0"><strong>Conseil :</strong> vérifiez toujours les informations avant validation et effectuez régulièrement une sauvegarde.</div>
</div></div></div></div>
@endsection
