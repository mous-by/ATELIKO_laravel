<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Catalogue fonctionnel français utilisé par React et les applications mobiles.
        $permissions = [
            ['code'=>'UTILISATEUR_VOIR','description'=>'Voir les utilisateurs'], ['code'=>'UTILISATEUR_CREER','description'=>'Créer un utilisateur'], ['code'=>'UTILISATEUR_MODIFIER','description'=>'Modifier un utilisateur'], ['code'=>'UTILISATEUR_SUPPRIMER','description'=>'Supprimer un utilisateur'],
            ['code'=>'CLIENT_VOIR','description'=>'Voir les clients'], ['code'=>'CLIENT_CREER','description'=>'Créer un client'], ['code'=>'CLIENT_MODIFIER','description'=>'Modifier un client'], ['code'=>'CLIENT_SUPPRIMER','description'=>'Supprimer un client'],
            ['code'=>'MODELE_VOIR','description'=>'Voir les modèles/albums'], ['code'=>'MODELE_CREER','description'=>'Créer un modèle'], ['code'=>'MODELE_MODIFIER','description'=>'Modifier un modèle'], ['code'=>'MODELE_SUPPRIMER','description'=>'Supprimer un modèle'],
            ['code'=>'AFFECTATION_VOIR','description'=>'Voir les affectations'], ['code'=>'AFFECTATION_CREER','description'=>'Créer une affectation'], ['code'=>'AFFECTATION_MODIFIER','description'=>'Modifier une affectation'], ['code'=>'AFFECTATION_SUPPRIMER','description'=>'Supprimer une affectation'],
            ['code'=>'RENDEZ_VOUS_VOIR','description'=>'Voir les rendez-vous'], ['code'=>'RENDEZ_VOUS_CREER','description'=>'Créer un rendez-vous'], ['code'=>'RENDEZ_VOUS_MODIFIER','description'=>'Modifier un rendez-vous'], ['code'=>'RENDEZ_VOUS_SUPPRIMER','description'=>'Supprimer un rendez-vous'],
            ['code'=>'PAIEMENT_VOIR','description'=>'Voir les paiements'], ['code'=>'PAIEMENT_CREER','description'=>'Enregistrer un paiement'],
            ['code'=>'RAPPORT_VOIR','description'=>'Voir les rapports/statistiques'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['code' => $p['code']], ['description' => $p['description']]);
        }

        $canonicalCodes = collect($permissions)->pluck('code');
        $aliases = [
            'CREATE_CLIENT'=>['CLIENT_CREER'], 'EDIT_CLIENT'=>['CLIENT_MODIFIER'], 'DELETE_CLIENT'=>['CLIENT_SUPPRIMER'], 'VIEW_CLIENT'=>['CLIENT_VOIR'],
            'MANAGE_AFFECTATION'=>['AFFECTATION_CREER','AFFECTATION_MODIFIER','AFFECTATION_SUPPRIMER'], 'VIEW_AFFECTATION'=>['AFFECTATION_VOIR'],
            'MANAGE_PAYMENT'=>['PAIEMENT_CREER'], 'VIEW_PAYMENT'=>['PAIEMENT_VOIR'],
            'MANAGE_MODELE'=>['MODELE_VOIR','MODELE_CREER','MODELE_MODIFIER','MODELE_SUPPRIMER'],
            'MANAGE_UTILISATEUR'=>['UTILISATEUR_VOIR','UTILISATEUR_CREER','UTILISATEUR_MODIFIER','UTILISATEUR_SUPPRIMER'],
            'MANAGE_RENDEZVOUS'=>['RENDEZ_VOUS_VOIR','RENDEZ_VOUS_CREER','RENDEZ_VOUS_MODIFIER','RENDEZ_VOUS_SUPPRIMER'],
            'VIEW_RAPPORT'=>['RAPPORT_VOIR'],
        ];

        Utilisateur::with('permissions')->each(function (Utilisateur $user) use ($aliases, $canonicalCodes) {
            $codes = $user->permissions->pluck('code');
            foreach ($aliases as $oldCode => $newCodes) {
                if ($codes->contains($oldCode)) $codes = $codes->merge($newCodes);
            }
            $ids = Permission::whereIn('code', $codes->intersect($canonicalCodes))->pluck('id');
            $user->permissions()->sync($ids);
        });

        Permission::whereIn('code', array_keys($aliases))->delete();

        // SUPERADMIN — réutilise aussi l'ancien email mal orthographié pour
        // éviter un conflit sur le numéro de téléphone unique.
        $superadmin = Utilisateur::where('email', 'barrymoustpaha485@gmail.com')
            ->orWhere('email', 'barrymoustapha485@gmail.com')
            ->orWhere('telephone', '74745669')
            ->first() ?? new Utilisateur(['id' => Str::uuid()]);

        $superadmin->fill([
            'email'         => 'barrymoustpaha485@gmail.com',
            'prenom'        => 'Moustapha',
            'nom'           => 'BARRY',
            'telephone'     => '74745669',
            'mot_de_passe'  => Hash::make('superadmin123'),
            'role'          => 'SUPERADMIN',
            'actif'         => true,
        ])->save();

        $superadmin->permissions()->sync(Permission::pluck('id'));

        $this->command->info('');
        $this->command->info('✓ Initialisation OK');
        $this->command->info('  ' . Permission::count() . ' permissions uniques vérifiées');
        $this->command->info('  SUPERADMIN : barrymoustpaha485@gmail.com / superadmin123');
    }
}
