<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Modele;
use App\Models\Rendezvous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Client::with(['mesures', 'paiements'])
            ->where('atelier_id', $user->atelier_id);

        if ($user->isTailleur()) {
            $query->whereHas('mesures.affectations', fn($q) => $q->where('tailleur_id', $user->id));
        }

        return response()->json($query->orderBy('created_at', 'desc')->get()->map(fn($c) => $this->formatClient($c)));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'contact' => 'nullable|string|max:20',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('user_photo', 'public');
            }

            $client = Client::create([
                'id' => Str::uuid(),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'contact' => $request->contact,
                'adresse' => $request->adresse,
                'email' => $request->email,
                'sexe' => $request->sexe,
                'photo' => $photoPath,
                'atelier_id' => $user->atelier_id,
            ]);

            // Création automatique d'un RDV livraison dans 7 jours
            Rendezvous::create([
                'id' => Str::uuid(),
                'client_id' => $client->id,
                'atelier_id' => $user->atelier_id,
                'date_rdv' => now()->addDays(7),
                'type_rendezvous' => 'LIVRAISON',
                'notes' => 'Rendez-vous de livraison automatique',
                'statut' => 'PLANIFIE',
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Client créé avec succès',
                'clientId' => $client->id,
                'client' => $this->formatClient($client),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $client = Client::with(['mesures', 'paiements', 'rendezvous'])->findOrFail($id);
        return response()->json($this->formatClient($client));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        if ($request->hasFile('photo')) {
            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }
            $client->photo = $request->file('photo')->store('user_photo', 'public');
        }

        $client->fill($request->only(['nom', 'prenom', 'contact', 'adresse', 'email', 'sexe']));
        $client->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Client mis à jour',
            'clientId' => $client->id,
        ]);
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }
        $client->delete();
        return response()->json(['status' => 'success', 'message' => 'Client supprimé']);
    }

    // ===== MESURES =====

    public function getMesures($clientId)
    {
        $client = Client::findOrFail($clientId);
        $mesures = $client->mesures()->with('modeleReference')->orderBy('created_at', 'desc')->get();
        return response()->json($mesures->map(fn($m) => $this->formatMesure($m)));
    }

    public function addMesure(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
        $user = $request->user();

        $data = $request->except(['photo', 'habitPhoto', 'audio']);
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
        if ($request->hasFile('audio')) {
            $data['audio_description_path'] = $request->file('audio')->store('audio', 'public');
        }

        $mesure = Mesure::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Mesure ajoutée',
            'mesureId' => $mesure->id,
            'mesure' => $this->formatMesure($mesure),
        ], 201);
    }

    public function updateMesure(Request $request, $clientId, $mesureId)
    {
        $mesure = Mesure::where('client_id', $clientId)->findOrFail($mesureId);

        $data = $request->except(['photo', 'habitPhoto', 'audio']);

        if ($request->hasFile('photo')) {
            if ($mesure->photo_path) Storage::disk('public')->delete($mesure->photo_path);
            $data['photo_path'] = $request->file('photo')->store('mesure_photo', 'public');
        }
        if ($request->hasFile('habitPhoto')) {
            if ($mesure->habit_photo_path) Storage::disk('public')->delete($mesure->habit_photo_path);
            $data['habit_photo_path'] = $request->file('habitPhoto')->store('habit_photo', 'public');
        }

        $mesure->update($data);

        return response()->json(['status' => 'success', 'message' => 'Mesure mise à jour', 'mesureId' => $mesure->id]);
    }

    public function deleteMesure($clientId, $mesureId)
    {
        $mesure = Mesure::where('client_id', $clientId)->findOrFail($mesureId);
        $mesure->delete();
        return response()->json(['status' => 'success', 'message' => 'Mesure supprimée']);
    }

    // ===== MODÈLES =====

    public function getModelesByAtelier(Request $request, $atelierId)
    {
        $query = Modele::where('atelier_id', $atelierId)->where('est_actif', true);
        if ($request->has('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        return response()->json($query->get()->map(fn($m) => $this->formatModeleList($m)));
    }

    public function getModeleDetail($modeleId, $atelierId)
    {
        $modele = Modele::where('atelier_id', $atelierId)->findOrFail($modeleId);
        return response()->json($this->formatModeleDetail($modele));
    }

    public function syntheseMensuelle(Request $request)
    {
        $user = $request->user();
        $results = Client::where('atelier_id', $user->atelier_id)
            ->withCount('mesures')
            ->with(['paiements' => fn($q) => $q->where('type_paiement', 'CLIENT')])
            ->get()
            ->map(fn($c) => [
                'clientId' => $c->id,
                'clientNom' => $c->prenom . ' ' . $c->nom,
                'nbMesures' => $c->mesures_count,
                'totalPaye' => $c->paiements->sum('montant'),
                'totalDu' => $c->mesures()->sum('prix'),
            ]);

        return response()->json($results);
    }

    private function formatClient(Client $c): array
    {
        $montantTotal = $c->mesures->sum('prix');
        $montantPaye = $c->paiements->where('type_paiement', 'CLIENT')->sum('montant');

        return [
            'id' => $c->id,
            'nom' => $c->nom,
            'prenom' => $c->prenom,
            'contact' => $c->contact,
            'adresse' => $c->adresse,
            'email' => $c->email,
            'sexe' => $c->sexe,
            'photo' => $c->photo,
            'photoUrl' => $c->photo ? asset('storage/' . $c->photo) : null,
            'dateCreation' => $c->date_creation,
            'atelierId' => $c->atelier_id,
            'nbMesures' => $c->mesures->count(),
            'montantTotal' => $montantTotal,
            'montantPaye' => $montantPaye,
            'montantRestant' => max(0, $montantTotal - $montantPaye),
        ];
    }

    private function formatMesure(Mesure $m): array
    {
        return [
            'id' => $m->id,
            'clientId' => $m->client_id,
            'atelierId' => $m->atelier_id,
            'dateMesure' => $m->date_mesure,
            'typeVetement' => $m->type_vetement,
            'sexe' => $m->sexe,
            'prix' => $m->prix,
            'description' => $m->description,
            'affecte' => $m->affecte,
            'modeleReferenceId' => $m->modele_reference_id,
            'modeleNom' => $m->modele_nom,
            'photoPath' => $m->photo_path,
            'photoUrl' => $m->photo_path ? asset('storage/' . $m->photo_path) : null,
            'habitPhotoPath' => $m->habit_photo_path,
            'audioDescriptionPath' => $m->audio_description_path,
            'epaule' => $m->epaule, 'manche' => $m->manche,
            'poitrine' => $m->poitrine, 'taille' => $m->taille,
            'longueur' => $m->longueur, 'fesse' => $m->fesse,
            'tourManche' => $m->tour_manche,
            'longueurPoitrine' => $m->longueur_poitrine,
            'longueurTaille' => $m->longueur_taille,
            'longueurFesse' => $m->longueur_fesse,
            'longueurJupe' => $m->longueur_jupe,
            'ceinture' => $m->ceinture,
            'longueurPoitrineRobe' => $m->longueur_poitrine_robe,
            'longueurTailleRobe' => $m->longueur_taille_robe,
            'longueurFesseRobe' => $m->longueur_fesse_robe,
            'longueurPantalon' => $m->longueur_pantalon,
            'cuisse' => $m->cuisse,
            'corps' => $m->corps,
        ];
    }

    private function formatModeleList(Modele $m): array
    {
        return [
            'id' => $m->id,
            'nom' => $m->nom,
            'categorie' => $m->categorie,
            'prix' => $m->prix,
            'photoPath' => $m->photo_path,
            'photoUrl' => $m->photo_path ? asset('storage/' . $m->photo_path) : null,
            'estActif' => $m->est_actif,
        ];
    }

    private function formatModeleDetail(Modele $m): array
    {
        return array_merge($this->formatModeleList($m), [
            'description' => $m->description,
            'videoPath' => $m->video_path,
            'dateCreation' => $m->date_creation,
        ]);
    }
}
