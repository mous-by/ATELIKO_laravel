<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Modele;
use App\Models\Paiement;
use App\Models\Rendezvous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Client::where('atelier_id', $user->atelier_id)
            ->with(['mesures' => fn ($query) => $query->orderByDesc('date_mesure')->orderByDesc('created_at')])
            ->orderBy('nom')
            ->orderBy('prenom');

        if ($request->has('search') && $request->search) {
            $term = $request->search;
            $query->where(fn ($q) => $q->where('nom', 'like', "%$term%")
                ->orWhere('prenom', 'like', "%$term%")
                ->orWhere('contact', 'like', "%$term%"));
        }

        $clients = $query->get();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $atelierId = Auth::user()->atelier_id;
        $modeles = Modele::where('atelier_id', $atelierId)->where('est_actif', true)->get();
        $clientsExistants = Client::where('atelier_id', $atelierId)
            ->with(['mesures' => fn ($query) => $query->orderByDesc('date_mesure')->orderByDesc('created_at')])
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();
        $syntheseMensuelle = $clientsExistants->flatMap->mesures
            ->filter(fn ($mesure) => $mesure->date_mesure)
            ->groupBy(fn ($mesure) => $mesure->date_mesure->format('Y-m'))
            ->map(fn ($mesures, $mois) => [
                'mois' => $mois,
                'libelle' => ucfirst(now()->createFromFormat('Y-m', $mois)->locale('fr')->translatedFormat('F Y')),
                'entrees' => $mesures->count(),
            ])->sortByDesc('mois')->values();

        return view('clients.create', compact('modeles', 'clientsExistants', 'syntheseMensuelle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'existing_client_id' => 'nullable|uuid',
            'nom' => 'required_without:existing_client_id|nullable|string|max:100',
            'prenom' => 'required_without:existing_client_id|nullable|string|max:100',
            'contact' => 'nullable|string|max:20',
            'sexe' => 'required|string|in:Femme,Homme',
            'mesures_json' => 'required|json',
            'avance' => 'nullable|numeric|min:0',
            'avance_moyen' => 'nullable|in:ESPECES,MOBILE_MONEY,VIREMENT,CARTE',
            'model_photos.*' => 'nullable|image|max:10240',
            'habit_photos.*' => 'nullable|image|max:10240',
        ]);

        $vetements = collect(json_decode($request->mesures_json, true));
        abort_if($vetements->isEmpty(), 422, 'Ajoutez au moins un vêtement.');

        $user = Auth::user();
        $client = DB::transaction(function () use ($request, $user, $vetements) {
            if ($request->filled('existing_client_id')) {
                $client = Client::where('atelier_id', $user->atelier_id)
                    ->findOrFail($request->existing_client_id);
            } else {
                $client = Client::create([
                    'id' => Str::uuid(),
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'contact' => $request->contact,
                    'sexe' => $request->sexe,
                    'atelier_id' => $user->atelier_id,
                ]);
            }

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

            $modelPhotos = $request->file('model_photos', []);
            $habitPhotos = $request->file('habit_photos', []);
            foreach ($vetements as $index => $vetement) {
                $type = strtoupper($vetement['typeVetement'] ?? '');
                abort_unless(in_array($type, ['ROBE', 'JUPE', 'HOMME'], true), 422, 'Type de vêtement invalide.');
                $requiredFields = [
                    'ROBE' => ['epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse'],
                    'JUPE' => ['epaule', 'manche', 'poitrine', 'taille', 'longueur', 'longueur_jupe', 'ceinture', 'fesse'],
                    'HOMME' => ['epaule', 'manche', 'longueur', 'longueur_pantalon', 'ceinture', 'cuisse'],
                ][$type];
                $prefix = strtolower($type);
                abort_if(collect($requiredFields)->contains(fn ($field) => blank($vetement[$prefix.'_'.$field] ?? null)), 422, 'Des mesures obligatoires sont manquantes.');
                $measureData = $this->mapVetementToMesure($vetement) + [
                    'id' => Str::uuid(),
                    'client_id' => $client->id,
                    'atelier_id' => $user->atelier_id,
                    'date_mesure' => now()->toDateString(),
                    'affecte' => false,
                ];
                if (isset($modelPhotos[$index])) {
                    $measureData['photo_path'] = $modelPhotos[$index]->store('mesure_photo', 'public');
                }
                if (isset($habitPhotos[$index])) {
                    $measureData['habit_photo_path'] = $habitPhotos[$index]->store('habit_photo', 'public');
                }
                Mesure::create($measureData);
            }

            if ($request->filled('avance') && (float) $request->avance > 0) {
                Paiement::create([
                    'id' => Str::uuid(), 'montant' => $request->avance,
                    'moyen' => $request->avance_moyen ?: 'ESPECES', 'type_paiement' => 'CLIENT',
                    'client_id' => $client->id, 'atelier_id' => $user->atelier_id,
                    'note' => 'Avance à la création de la mesure',
                ]);
            }

            return $client;
        });

        $message = $request->filled('existing_client_id')
            ? 'Nouveaux vêtements ajoutés au client'
            : 'Client et vêtements créés avec succès';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'clientId' => $client->id,
                'redirect' => route('clients.show', $client->id),
            ]);
        }

        return redirect()->route('clients.show', $client->id)->with('success', $message);
    }

    private function mapVetementToMesure(array $vetement): array
    {
        $type = strtoupper($vetement['typeVetement'] ?? '');
        $prefix = $type === 'HOMME' ? 'homme' : strtolower($type);
        $value = fn (string $name) => $vetement[$prefix.'_'.$name] ?? null;

        return [
            'type_vetement' => $type,
            'sexe' => $vetement['sexe'] ?? ($type === 'HOMME' ? 'Homme' : 'Femme'),
            'prix' => $vetement['prix'] ?: null,
            'description' => $vetement['description'] ?? null,
            'modele_reference_id' => $vetement['selectedModelId'] ?: null,
            'modele_nom' => $vetement['modeleNom'] ?? null,
            'photo_path' => $vetement['existing_photo_path'] ?? null,
            'habit_photo_path' => $vetement['existing_habit_photo_path'] ?? null,
            'epaule' => $value('epaule'),
            'manche' => $value('manche'),
            'poitrine' => $value('poitrine'),
            'taille' => $value('taille'),
            'longueur' => $value('longueur'),
            'fesse' => $value('fesse'),
            'tour_manche' => $value('tour_manche'),
            'longueur_poitrine' => $value('longueur_poitrine'),
            'longueur_taille' => $value('longueur_taille'),
            'longueur_fesse' => $value('longueur_fesse'),
            'longueur_jupe' => $value('longueur_jupe'),
            'ceinture' => $value('ceinture'),
            'longueur_pantalon' => $value('longueur_pantalon'),
            'cuisse' => $value('cuisse'),
            'corps' => $value('corps'),
        ];
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $client = Client::with([
            'mesures' => fn ($q) => $q->orderByDesc('date_mesure')->orderByDesc('created_at'),
            'mesures.modeleReference',
            'affectations.tailleur',
            'paiements',
            'rendezvous',
        ])->where('atelier_id', $user->atelier_id)->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json(['client' => $client]);
        }

        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $montantRestant = max(0, $montantTotal - $montantPaye);

        $modeles = Modele::where('atelier_id', $user->atelier_id)->where('est_actif', true)->get();

        return view('clients.show', compact('client', 'montantTotal', 'montantPaye', 'montantRestant', 'modeles'));
    }

    public function edit($id)
    {
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        $client->fill($request->only(['nom', 'prenom', 'contact', 'sexe']));
        $client->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Client mis à jour']);
        }

        return redirect()->route('clients.show', $id)->with('success', 'Client mis à jour');
    }

    public function destroy(Request $request, $id)
    {
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }
        $client->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Client supprimé']);
        }

        return redirect()->route('clients.index')->with('success', 'Client supprimé');
    }

    public function ajouterMesure(Request $request, $clientId)
    {
        $user = Auth::user();
        $client = Client::where('atelier_id', $user->atelier_id)->findOrFail($clientId);

        $data = $request->except(['photo', 'habitPhoto', 'audio', '_token']);
        $data['id'] = Str::uuid();
        $data['client_id'] = $clientId;
        $data['atelier_id'] = $user->atelier_id;
        $data['date_mesure'] = $request->date_mesure ?? now()->toDateString();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('mesure_photo', 'public');
        }
        if ($request->hasFile('habitPhoto')) {
            $data['habit_photo_path'] = $request->file('habitPhoto')->store('habit_photo', 'public');
        }

        Mesure::create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Nouveau modèle ajouté avec succès']);
        }

        return redirect()->route('clients.show', $clientId)->with('success', 'Mesure ajoutée');
    }

    public function modifierMesure(Request $request, $clientId, $mesureId)
    {
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($clientId);
        $mesure = Mesure::where('client_id', $client->id)->findOrFail($mesureId);
        $data = $request->only([
            'sexe', 'type_vetement', 'prix', 'description', 'modele_nom',
            'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse',
            'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
            'longueur_jupe', 'ceinture', 'longueur_pantalon', 'cuisse', 'corps',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('mesure_photo', 'public');
        }
        if ($request->hasFile('habitPhoto')) {
            $data['habit_photo_path'] = $request->file('habitPhoto')->store('habit_photo', 'public');
        }

        $mesure->update($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Modèle modifié avec succès']);
        }

        return redirect()->route('clients.index')->with('success', 'Modèle modifié avec succès');
    }

    public function supprimerMesure($clientId, $mesureId)
    {
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($clientId);
        $mesure = Mesure::where('client_id', $client->id)->findOrFail($mesureId);
        $mesure->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Modèle supprimé']);
        }

        return redirect()->route('clients.show', $clientId)->with('success', 'Mesure supprimée');
    }

    public function ajouterPaiement(Request $request, $clientId)
    {
        $request->validate(['montant' => 'required|numeric|min:0.01']);
        $user = Auth::user();

        Paiement::create([
            'id' => Str::uuid(),
            'montant' => $request->montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'CLIENT',
            'client_id' => $clientId,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return redirect()->route('clients.show', $clientId)->with('success', 'Paiement enregistré');
    }

    public function recu($clientId)
    {
        $client = Client::with(['mesures', 'paiements'])->findOrFail($clientId);
        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $atelier = $client->atelier;

        return view('clients.recu', compact('client', 'montantTotal', 'montantPaye', 'atelier'));
    }
}
