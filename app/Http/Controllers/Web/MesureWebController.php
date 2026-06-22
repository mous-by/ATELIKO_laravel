<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Modele;
use App\Models\Rendezvous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MesureWebController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $modeles = Modele::where('atelier_id', $user->atelier_id)
            ->where('est_actif', true)
            ->orderBy('nom')
            ->get();

        $categories = $modeles->pluck('categorie')->unique()->filter()->sort()->values();

        $clients = Client::where('atelier_id', $user->atelier_id)
            ->orderBy('prenom')
            ->get(['id', 'prenom', 'nom', 'contact']);

        return view('mesures.create', compact('modeles', 'categories', 'clients'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Si un client existant est sélectionné
        if ($request->filled('client_id')) {
            $client = Client::where('atelier_id', $user->atelier_id)
                ->findOrFail($request->client_id);
        } else {
            // Nouveau client
            $request->validate([
                'prenom' => 'required|string|max:100',
                'nom' => 'required|string|max:100',
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('user_photo', 'public');
            }

            $client = Client::create([
                'id' => Str::uuid(),
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'contact' => $request->contact,
                'adresse' => $request->adresse,
                'email' => $request->email,
                'sexe' => $request->sexe,
                'photo' => $photoPath,
                'atelier_id' => $user->atelier_id,
            ]);

            // RDV livraison automatique
            Rendezvous::create([
                'id' => Str::uuid(),
                'client_id' => $client->id,
                'atelier_id' => $user->atelier_id,
                'date_rdv' => now()->addDays(7),
                'type_rendezvous' => 'LIVRAISON',
                'notes' => 'Rendez-vous de livraison automatique',
                'statut' => 'PLANIFIE',
            ]);
        }

        // Créer la mesure
        $typeVetement = $request->type_vetement ?? 'Robe';
        $sexe = in_array($typeVetement, ['Robe', 'Jupe']) ? 'Femme' : 'Homme';

        $mesureData = [
            'id' => Str::uuid(),
            'client_id' => $client->id,
            'atelier_id' => $user->atelier_id,
            'type_vetement' => $typeVetement,
            'sexe' => $request->sexe ?? $sexe,
            'prix' => $request->prix,
            'description' => $request->description,
            'date_mesure' => $request->date_mesure ?? now()->toDateString(),
            'modele_reference_id' => $request->modele_reference_id ?: null,
            'modele_nom' => $request->modele_nom ?: null,
        ];

        // Champs mesures selon le type
        if ($typeVetement === 'Robe') {
            $mesureData += $this->extraireChamps($request, 'robe_', [
                'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse',
                'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
            ]);
        } elseif ($typeVetement === 'Jupe') {
            $mesureData += $this->extraireChamps($request, 'jupe_', [
                'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'longueur_jupe',
                'ceinture', 'fesse', 'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
            ]);
        } else {
            $mesureData += $this->extraireChamps($request, 'homme_', [
                'epaule', 'manche', 'longueur', 'longueur_pantalon',
                'ceinture', 'cuisse', 'poitrine', 'corps', 'tour_manche',
            ]);
        }

        if ($request->hasFile('habit_photo')) {
            $mesureData['habit_photo_path'] = $request->file('habit_photo')->store('habit_photo', 'public');
        }

        Mesure::create($mesureData);

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Commande enregistrée avec succès pour ' . $client->prenom . ' ' . $client->nom);
    }

    private function extraireChamps(Request $request, string $prefix, array $champs): array
    {
        $data = [];
        foreach ($champs as $champ) {
            $key = $prefix . $champ;
            if ($request->filled($key)) {
                // Mapper les champs préfixés vers les colonnes de la table
                $colonne = $this->mapperColonne($champ, $prefix);
                $data[$colonne] = $request->input($key);
            }
        }
        return $data;
    }

    private function mapperColonne(string $champ, string $prefix): string
    {
        // Les colonnes de la table mesures n'ont pas de préfixe
        // sauf longueur_poitrine_robe, longueur_taille_robe, longueur_fesse_robe pour robe
        if ($prefix === 'robe_' && in_array($champ, ['longueur_poitrine', 'longueur_taille', 'longueur_fesse'])) {
            return $champ . '_robe';
        }
        if ($prefix === 'homme_') {
            return $champ; // longueur_pantalon, cuisse, corps sont dans la table
        }
        return $champ;
    }
}
