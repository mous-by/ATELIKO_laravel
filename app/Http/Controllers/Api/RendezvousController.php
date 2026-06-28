<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Rendezvous;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RendezvousController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'clientId' => 'required|uuid|exists:clients,id',
            'dateRDV' => 'required|date',
            'typeRendezVous' => 'required|string',
        ]);

        $user = $request->user();
        $atelierId = $request->input('atelierId', $user->atelier_id);
        abort_if($atelierId !== $user->atelier_id && !$user->isSuperAdmin(), 403);
        Client::where('atelier_id', $atelierId)->findOrFail($request->clientId);

        $rdv = Rendezvous::create([
            'id' => Str::uuid(),
            'client_id' => $request->clientId,
            'atelier_id' => $atelierId,
            'mesure_id' => $request->mesureId,
            'date_rdv' => $request->dateRDV,
            'type_rendezvous' => $request->typeRendezVous,
            'notes' => $request->notes,
            'statut' => 'PLANIFIE',
        ]);

        return response()->json($this->format($rdv->load('client')), 201);
    }

    public function update(Request $request, $id)
    {
        $rdv = $this->queryForUser($request)->findOrFail($id);
        $rdv->update([
            'date_rdv' => $request->dateRDV ?? $rdv->date_rdv,
            'type_rendezvous' => $request->typeRendezVous ?? $rdv->type_rendezvous,
            'notes' => $request->notes ?? $rdv->notes,
            'statut' => $request->statut ?? $rdv->statut,
        ]);
        return response()->json($this->format($rdv->load('client')));
    }

    public function show($id)
    {
        $rdv = $this->queryForUser(request())->with('client')->findOrFail($id);
        return response()->json($this->format($rdv));
    }

    public function destroy(Request $request, $id)
    {
        $this->queryForUser($request)->findOrFail($id)->delete();
        return response()->noContent();
    }

    public function clientsParAtelier(Request $request, $atelierId)
    {
        $user = $request->user();
        abort_if($atelierId !== $user->atelier_id && !$user->isSuperAdmin(), 403);

        $clients = Client::where('atelier_id', $atelierId)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)))
            ->get()
            ->map(fn($c) => [
            'id' => $c->id,
            'nom' => $c->nom,
            'prenom' => $c->prenom,
            'contact' => $c->contact,
        ]);
        return response()->json($clients);
    }

    public function tous(Request $request, $atelierId)
    {
        $rdvs = $this->queryForUser($request)
            ->with(['client.mesures', 'client.paiements'])
            ->where('atelier_id', $atelierId)
            ->orderBy('date_rdv', 'desc')
            ->get();
        return response()->json($rdvs->map(fn($r) => $this->format($r)));
    }

    public function aVenir(Request $request, $atelierId)
    {
        $rdvs = $this->queryForUser($request)
            ->with(['client.mesures', 'client.paiements'])
            ->where('atelier_id', $atelierId)
            ->where('date_rdv', '>=', now())
            ->whereNotIn('statut', ['ANNULE', 'TERMINE'])
            ->orderBy('date_rdv')
            ->get();
        return response()->json($rdvs->map(fn($r) => $this->format($r)));
    }

    public function aujourdhui($atelierId)
    {
        $rdvs = $this->queryForUser(request())
            ->with(['client.mesures', 'client.paiements'])
            ->where('atelier_id', $atelierId)
            ->whereDate('date_rdv', now()->toDateString())
            ->orderBy('date_rdv')
            ->get();
        return response()->json($rdvs->map(fn($r) => $this->format($r)));
    }

    public function confirmer(Request $request, $id)
    {
        $rdv = $this->queryForUser($request)->findOrFail($id);
        $rdv->update(['statut' => 'CONFIRME']);
        return response()->json($this->format($rdv->load('client')));
    }

    public function annuler(Request $request, $id)
    {
        $rdv = $this->queryForUser($request)->findOrFail($id);
        $rdv->update(['statut' => 'ANNULE']);
        return response()->json($this->format($rdv->load('client')));
    }

    public function pret(Request $request, $id)
    {
        $rdv = $this->queryForUser($request)
            ->with(['client.mesures', 'client.paiements'])
            ->findOrFail($id);
        $paiement = $this->resumePaiementClient($rdv->client);

        if (!$paiement['estSolde']) {
            return response()->json([
                'message' => 'Impossible de marquer prêt : le client doit encore payer ' . number_format($paiement['resteAPayer'], 0, ',', ' ') . ' FCFA.',
                'paiement' => $paiement,
            ], 422);
        }

        $rdv->update(['statut' => 'PRET']);

        return response()->json($this->format($rdv->fresh()->load(['client.mesures', 'client.paiements'])));
    }

    public function terminer(Request $request, $id)
    {
        $rdv = $this->queryForUser($request)
            ->with(['client.mesures', 'client.paiements'])
            ->findOrFail($id);
        $paiement = $this->resumePaiementClient($rdv->client);

        if (!$paiement['estSolde']) {
            return response()->json([
                'message' => 'Impossible de terminer : le client doit encore payer ' . number_format($paiement['resteAPayer'], 0, ',', ' ') . ' FCFA.',
                'paiement' => $paiement,
            ], 422);
        }

        $rdv->update(['statut' => 'TERMINE']);
        return response()->json($this->format($rdv->fresh()->load(['client.mesures', 'client.paiements'])));
    }

    public function clientDetails(Request $request, $clientId)
    {
        $user = $request->user();
        $client = Client::with([
            'mesures' => fn ($q) => $user->isTailleur()
                ? $q->whereHas('affectations', fn ($a) => $a->where('tailleur_id', $user->id))
                : $q,
            'paiements',
            'rendezvous' => fn($q) => $q->orderBy('date_rdv'),
        ])
            ->where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)))
            ->findOrFail($clientId);
        $paiement = $this->resumePaiementClient($client);

        return response()->json([
            'id' => $client->id,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'contact' => $client->contact,
            'email' => $client->email,
            'paiement' => $paiement,
            'mesures' => $client->mesures->map(fn($m) => [
                'id' => $m->id,
                'typeVetement' => $m->type_vetement,
                'modeleNom' => $m->modele_nom,
                'description' => $m->description,
                'prix' => $m->prix,
                'dateMesure' => $m->date_mesure,
                'dateLivraison' => $m->date_livraison,
            ]),
            'rendezvous' => $client->rendezvous->map(fn($r) => $this->format($r)),
        ]);
    }

    private function format(Rendezvous $r): array
    {
        $paiement = $r->relationLoaded('client') ? $this->resumePaiementClient($r->client) : null;

        return [
            'id' => $r->id,
            'dateRDV' => $r->date_rdv,
            'date' => $r->date_rdv,
            'typeRendezVous' => $r->type_rendezvous,
            'type' => $r->type_rendezvous,
            'notes' => $r->notes,
            'statut' => $r->statut,
            'atelierId' => $r->atelier_id,
            'mesureId' => $r->mesure_id,
            'clientId' => $r->client_id,
            'client' => $r->relationLoaded('client') && $r->client ? [
                'id' => $r->client->id,
                'nom' => $r->client->nom,
                'prenom' => $r->client->prenom,
                'contact' => $r->client->contact,
            ] : null,
            'clientNomComplet' => $r->relationLoaded('client') && $r->client ? trim($r->client->prenom . ' ' . $r->client->nom) : null,
            'clientNom' => $r->relationLoaded('client') && $r->client ? $r->client->nom : null,
            'clientPrenom' => $r->relationLoaded('client') && $r->client ? $r->client->prenom : null,
            'clientContact' => $r->relationLoaded('client') && $r->client ? $r->client->contact : null,
            'paiement' => $paiement,
            'peutMarquerPret' => $paiement ? $paiement['estSolde'] : false,
            'createdAt' => $r->created_at,
            'updatedAt' => $r->updated_at,
        ];
    }

    private function queryForUser(Request $request)
    {
        $user = $request->user();

        return Rendezvous::where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('client.affectations', fn ($q) => $q->where('tailleur_id', $user->id)));
    }

    private function resumePaiementClient(?Client $client): array
    {
        if (!$client) {
            return ['totalDu' => 0.0, 'montantPaye' => 0.0, 'resteAPayer' => 0.0, 'estSolde' => false];
        }

        $totalDu = $client->relationLoaded('mesures')
            ? (float) $client->mesures->sum('prix')
            : (float) $client->mesures()->sum('prix');
        $montantPaye = $client->relationLoaded('paiements')
            ? (float) $client->paiements->where('type_paiement', 'CLIENT')->sum('montant')
            : (float) $client->paiements()->where('type_paiement', 'CLIENT')->sum('montant');
        $resteAPayer = max(0, $totalDu - $montantPaye);

        return [
            'totalDu' => $totalDu,
            'montantPaye' => $montantPaye,
            'resteAPayer' => $resteAPayer,
            'estSolde' => $totalDu > 0 && $resteAPayer <= 0,
        ];
    }
}
