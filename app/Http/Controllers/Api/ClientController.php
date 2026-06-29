<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Modele;
use App\Models\Paiement;
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
        $query = $this->clientQueryForUser($user)
            ->with([
                'mesures' => fn ($q) => $this->mesuresQueryForUser($q, $user),
                'paiements',
            ]);

        return response()->json($query->orderBy('created_at', 'desc')->get()->map(fn($c) => $this->formatClient($c)));
    }

    public function store(Request $request)
    {
        abort_if($request->user()->isTailleur(), 403);
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

            $mesure = null;
            if ($request->filled('prix') || $request->filled('typeVetement') || $request->filled('femme_type')) {
                $mesureData = $this->mapMobileRequestToMesure($request) + [
                    'id' => Str::uuid(),
                    'client_id' => $client->id,
                    'atelier_id' => $user->atelier_id,
                    'date_mesure' => $request->date_mesure ?? now()->toDateString(),
                    'affecte' => false,
                ];

                if ($request->hasFile('photo')) {
                    $mesureData['photo_path'] = $request->file('photo')->store('mesure_photo', 'public');
                } elseif ($request->filled('selectedModelId')) {
                    $modele = Modele::where('atelier_id', $user->atelier_id)->find($request->selectedModelId);
                    if ($modele) {
                        $mesureData['modele_reference_id'] = $modele->id;
                        $mesureData['modele_nom'] = $request->modeleNom ?: $modele->nom;
                        $mesureData['photo_path'] = $modele->photo_path;
                    }
                }

                if ($request->hasFile('habitPhoto')) {
                    $mesureData['habit_photo_path'] = $request->file('habitPhoto')->store('habit_photo', 'public');
                }

                $mesure = Mesure::create($mesureData);
            }

            $avance = (float) $request->input('avance', 0);
            if ($avance > 0) {
                Paiement::create([
                    'id' => Str::uuid(),
                    'montant' => $avance,
                    'moyen' => $request->input('avance_moyen', $request->input('moyen', 'ESPECES')),
                    'type_paiement' => 'CLIENT',
                    'client_id' => $client->id,
                    'atelier_id' => $user->atelier_id,
                    'note' => 'Avance à la création de la mesure',
                ]);
            }

            // Création automatique d'un RDV livraison dans 7 jours
            Rendezvous::create([
                'id' => Str::uuid(),
                'client_id' => $client->id,
                'atelier_id' => $user->atelier_id,
                'mesure_id' => $mesure?->id,
                'date_rdv' => now()->addDays(7),
                'type_rendezvous' => 'LIVRAISON',
                'notes' => 'Rendez-vous de livraison automatique',
                'statut' => 'PLANIFIE',
            ]);

            DB::commit();

            $client->load(['mesures', 'paiements', 'rendezvous']);
            $formattedClient = $this->formatClient($client);

            return response()->json([
                'status' => 'success',
                'message' => $mesure ? 'Client, mesure et rendez-vous créés avec succès' : 'Client créé avec succès',
                'clientId' => $client->id,
                'mesureId' => $mesure?->id,
                'client' => $formattedClient,
                'receipt' => $this->buildClientDueReceipt($client),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $client = $this->clientQueryForUser($user)
            ->with([
                'mesures' => fn ($q) => $this->mesuresQueryForUser($q, $user),
                'paiements',
                'rendezvous',
            ])
            ->findOrFail($id);
        return response()->json($this->formatClient($client));
    }

    public function update(Request $request, $id)
    {
        abort_if($request->user()->isTailleur(), 403);
        $client = Client::where('atelier_id', $request->user()->atelier_id)->findOrFail($id);

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

    public function destroy(Request $request, $id)
    {
        abort_if($request->user()->isTailleur(), 403);
        $client = Client::where('atelier_id', $request->user()->atelier_id)->findOrFail($id);
        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }
        $client->delete();
        return response()->json(['status' => 'success', 'message' => 'Client supprimé']);
    }

    // ===== MESURES =====

    public function getMesures(Request $request, $clientId)
    {
        $user = $request->user();
        $client = $this->clientQueryForUser($user)->findOrFail($clientId);
        $mesures = $this->mesuresQueryForUser($client->mesures(), $user)->with('modeleReference')->orderBy('created_at', 'desc')->get();
        return response()->json($mesures->map(fn($m) => $this->formatMesure($m)));
    }

    public function addMesure(Request $request, $clientId)
    {
        abort_if($request->user()->isTailleur(), 403);
        $user = $request->user();
        $client = Client::where('atelier_id', $user->atelier_id)->findOrFail($clientId);

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
        abort_if($request->user()->isTailleur(), 403);
        Client::where('atelier_id', $request->user()->atelier_id)->findOrFail($clientId);
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

    public function deleteMesure(Request $request, $clientId, $mesureId)
    {
        abort_if($request->user()->isTailleur(), 403);
        Client::where('atelier_id', $request->user()->atelier_id)->findOrFail($clientId);
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
        $results = $this->clientQueryForUser($user)
            ->withCount(['mesures' => fn ($q) => $this->mesuresQueryForUser($q, $user)])
            ->with(['paiements' => fn($q) => $q->where('type_paiement', 'CLIENT')])
            ->get()
            ->map(fn($c) => [
                'clientId' => $c->id,
                'clientNom' => $c->prenom . ' ' . $c->nom,
                'nbMesures' => $c->mesures_count,
                'totalPaye' => $c->paiements->sum('montant'),
                'totalDu' => $this->mesuresQueryForUser($c->mesures(), $user)->sum('prix'),
            ]);

        return response()->json($results);
    }

    private function formatClient(Client $c): array
    {
        $montantTotal = $c->mesures->sum('prix');
        $montantPaye = $c->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $montantRestant = max(0, $montantTotal - $montantPaye);

        $data = [
            'id' => $c->id,
            'nom' => $c->nom,
            'prenom' => $c->prenom,
            'fullName' => trim($c->prenom . ' ' . $c->nom),
            'contact' => $c->contact,
            'telephone' => $c->contact,
            'adresse' => $c->adresse,
            'email' => $c->email,
            'sexe' => $c->sexe,
            'photo' => $c->photo,
            'photoUrl' => $c->photo ? asset($c->photo) : null,
            'dateCreation' => $c->date_creation,
            'atelierId' => $c->atelier_id,
            'nbMesures' => $c->mesures->count(),
            'montantTotal' => $montantTotal,
            'prixTotal' => $montantTotal,
            'montantPaye' => $montantPaye,
            'montantRestant' => $montantRestant,
            'resteAPayer' => $montantRestant,
            'statutPaiement' => $montantTotal > 0 && $montantRestant <= 0 ? 'PAYE' : ($montantPaye > 0 ? 'PARTIEL' : 'EN_ATTENTE'),
        ];

        if ($c->relationLoaded('mesures')) {
            $data['mesures'] = $c->mesures->map(fn ($m) => $this->formatMesure($m))->values();
        }

        if ($c->relationLoaded('paiements')) {
            $data['paiements'] = $c->paiements->map(fn ($p) => [
                'id' => $p->id,
                'montant' => $p->montant,
                'moyen' => $p->moyen,
                'reference' => $p->reference,
                'datePaiement' => $p->date_paiement,
                'typePaiement' => $p->type_paiement,
                'note' => $p->note,
            ])->values();
        }

        if ($c->relationLoaded('rendezvous')) {
            $data['rendezvous'] = $c->rendezvous->map(fn ($r) => [
                'id' => $r->id,
                'dateRDV' => $r->date_rdv,
                'typeRendezVous' => $r->type_rendezvous,
                'statut' => $r->statut,
                'notes' => $r->notes,
                'mesureId' => $r->mesure_id,
            ])->values();
        }

        return $data;
    }

    private function clientQueryForUser($user)
    {
        return Client::where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)));
    }

    private function mesuresQueryForUser($query, $user)
    {
        return $user->isTailleur()
            ? $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id))
            : $query;
    }

    private function formatMesure(Mesure $m): array
    {
        return [
            'id' => $m->id,
            'clientId' => $m->client_id,
            'atelierId' => $m->atelier_id,
            'dateMesure' => $m->date_mesure,
            'dateLivraison' => $m->date_livraison,
            'typeVetement' => $m->type_vetement,
            'sexe' => $m->sexe,
            'prix' => $m->prix,
            'description' => $m->description,
            'affecte' => $m->affecte,
            'modeleReferenceId' => $m->modele_reference_id,
            'modeleNom' => $m->modele_nom,
            'photoPath' => $m->photo_path,
            'photoUrl' => $m->photo_url,
            'habitPhotoPath' => $m->habit_photo_path,
            'habitPhotoUrl' => $m->habit_photo_url,
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

    private function mapMobileRequestToMesure(Request $request): array
    {
        $sexe = $request->input('sexe', 'Femme');
        $type = strtoupper($request->input('typeVetement') ?: ($sexe === 'Homme' ? 'HOMME' : $request->input('femme_type', 'ROBE')));
        if ($type === 'ROBE' || $type === 'JUPE' || $type === 'HOMME') {
            $prefix = $type === 'HOMME' ? 'homme' : strtolower($type);
        } else {
            $type = $sexe === 'Homme' ? 'HOMME' : strtoupper($request->input('femme_type', 'ROBE'));
            $prefix = $type === 'HOMME' ? 'homme' : strtolower($type);
        }

        $value = fn (string $name) => blank($request->input($prefix . '_' . $name)) ? null : $request->input($prefix . '_' . $name);

        return [
            'type_vetement' => $type,
            'sexe' => $sexe,
            'prix' => blank($request->input('prix')) ? null : $request->input('prix'),
            'description' => $request->input('description'),
            'modele_reference_id' => $request->input('selectedModelId') ?: null,
            'modele_nom' => $request->input('modeleNom'),
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

    private function buildClientDueReceipt(Client $client): array
    {
        $client->loadMissing(['atelier', 'mesures', 'paiements']);
        $montantTotal = (float) $client->mesures->sum('prix');
        $montantPaye = (float) $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $reste = max(0, $montantTotal - $montantPaye);

        return [
            'typeTicket' => 'COMMANDE',
            'reference' => 'CLI-' . strtoupper(substr($client->id, 0, 8)),
            'date' => now()->toDateString(),
            'datePaiement' => now(),
            'dateFormatted' => now()->format('d/m/Y H:i'),
            'clientId' => $client->id,
            'clientNom' => $client->nom,
            'clientPrenom' => $client->prenom,
            'clientContact' => $client->contact,
            'beneficiaire' => trim($client->prenom . ' ' . $client->nom),
            'montant' => $montantPaye,
            'montantTotal' => $montantTotal,
            'totalDu' => $montantTotal,
            'avance' => $montantPaye,
            'avancePaye' => $montantPaye,
            'montantRestant' => $reste,
            'resteAPayer' => $reste,
            'nbModeles' => $client->mesures->count(),
            'nombreModeles' => $client->mesures->count(),
            'atelierNom' => $client->atelier?->nom,
            'statut' => $montantTotal > 0 && $reste <= 0 ? 'SOLDE' : 'EN_ATTENTE',
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
            'photoUrl' => $m->photo_url,
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
