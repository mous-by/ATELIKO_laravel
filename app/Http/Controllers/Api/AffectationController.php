<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffectationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $atelierId = $request->query('atelierId', $user->atelier_id);
        $role = $user->role;

        $query = Affectation::with(['client', 'mesure', 'tailleur', 'createur'])
            ->where('atelier_id', $atelierId);

        if ($role === 'TAILLEUR') {
            $query->where('tailleur_id', $user->id);
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('tailleurId') && $role !== 'TAILLEUR') {
            $query->where('tailleur_id', $request->tailleurId);
        }

        $affectations = $query->orderBy('date_creation', 'desc')->get();

        return response()->json([
            'data' => $affectations->map(fn($a) => $this->format($a)),
            'message' => 'Affectations récupérées',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'clientId' => 'required|uuid|exists:clients,id',
            'mesureId' => 'required|uuid|exists:mesures,id',
            'tailleurId' => 'required|uuid|exists:utilisateurs,id',
            'atelierId' => 'required|uuid|exists:ateliers,id',
        ]);

        $user = $request->user();

        $affectation = Affectation::create([
            'id' => Str::uuid(),
            'client_id' => $request->clientId,
            'mesure_id' => $request->mesureId,
            'tailleur_id' => $request->tailleurId,
            'atelier_id' => $request->atelierId ?? $user->atelier_id,
            'createur_id' => $user->id,
            'prix_tailleur' => $request->prixTailleur,
            'date_echeance' => $request->dateEcheance,
            'statut' => 'EN_ATTENTE',
        ]);

        // Marquer la mesure comme affectée
        Mesure::where('id', $request->mesureId)->update(['affecte' => true]);

        return response()->json([
            'data' => $this->format($affectation->load(['client', 'mesure', 'tailleur'])),
            'message' => 'Affectation créée',
        ], 201);
    }

    public function updateStatut(Request $request, $affectationId)
    {
        $request->validate(['statut' => 'required|in:EN_ATTENTE,EN_COURS,TERMINE,VALIDE,ANNULE']);

        $affectation = Affectation::findOrFail($affectationId);
        $user = $request->user();
        $newStatut = $request->statut;

        $updates = ['statut' => $newStatut];

        if ($newStatut === 'EN_COURS') {
            $updates['date_debut_reelle'] = now();
        } elseif ($newStatut === 'TERMINE') {
            $updates['date_fin_reelle'] = now();
        } elseif ($newStatut === 'VALIDE') {
            $updates['date_validation'] = now();
        } elseif ($newStatut === 'ANNULE') {
            // Libérer la mesure
            Mesure::where('id', $affectation->mesure_id)->update(['affecte' => false]);
        }

        $affectation->update($updates);

        return response()->json([
            'data' => $this->format($affectation->fresh()->load(['client', 'mesure', 'tailleur'])),
            'message' => 'Statut mis à jour',
        ]);
    }

    public function destroy(Request $request, $affectationId)
    {
        $affectation = Affectation::findOrFail($affectationId);
        // Libérer la mesure
        Mesure::where('id', $affectation->mesure_id)->update(['affecte' => false]);
        $affectation->delete();

        return response()->json(['data' => null, 'message' => 'Affectation supprimée']);
    }

    public function formulaireData(Request $request)
    {
        $atelierId = $request->query('atelierId', $request->user()->atelier_id);

        $tailleurs = Utilisateur::where('atelier_id', $atelierId)
            ->where('role', 'TAILLEUR')
            ->where('actif', true)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'prenom' => $t->prenom,
                'nom' => $t->nom,
                'email' => $t->email,
            ]);

        $clients = Client::where('atelier_id', $atelierId)
            ->with(['mesures' => fn($q) => $q->where('affecte', false)])
            ->get()
            ->filter(fn($c) => $c->mesures->isNotEmpty())
            ->map(fn($c) => [
                'id' => $c->id,
                'prenom' => $c->prenom,
                'nom' => $c->nom,
                'contact' => $c->contact,
                'mesures' => $c->mesures->map(fn($m) => [
                    'id' => $m->id,
                    'typeVetement' => $m->type_vetement,
                    'prix' => $m->prix,
                    'dateMesure' => $m->date_mesure,
                ]),
            ]);

        return response()->json([
            'data' => [
                'tailleurs' => $tailleurs,
                'clients' => $clients->values(),
            ],
            'message' => 'Données formulaire récupérées',
        ]);
    }

    private function format(Affectation $a): array
    {
        return [
            'id' => $a->id,
            'statut' => $a->statut,
            'prixTailleur' => $a->prix_tailleur,
            'dateCreation' => $a->date_creation,
            'dateEcheance' => $a->date_echeance,
            'dateDebutReelle' => $a->date_debut_reelle,
            'dateFinReelle' => $a->date_fin_reelle,
            'dateValidation' => $a->date_validation,
            'atelierId' => $a->atelier_id,
            'client' => $a->relationLoaded('client') && $a->client ? [
                'id' => $a->client->id,
                'nom' => $a->client->nom,
                'prenom' => $a->client->prenom,
                'contact' => $a->client->contact,
            ] : null,
            'mesure' => $a->relationLoaded('mesure') && $a->mesure ? [
                'id' => $a->mesure->id,
                'typeVetement' => $a->mesure->type_vetement,
                'prix' => $a->mesure->prix,
                'sexe' => $a->mesure->sexe,
            ] : null,
            'tailleur' => $a->relationLoaded('tailleur') && $a->tailleur ? [
                'id' => $a->tailleur->id,
                'nom' => $a->tailleur->nom,
                'prenom' => $a->tailleur->prenom,
            ] : null,
        ];
    }
}
