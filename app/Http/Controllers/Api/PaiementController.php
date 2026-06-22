<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Paiement;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    // ===== PAIEMENTS CLIENTS =====

    public function createPaiementClient(Request $request)
    {
        $request->validate([
            'clientId' => 'required|uuid|exists:clients,id',
            'montant' => 'required|numeric|min:0.01',
            'moyen' => 'in:ESPECES,MOBILE_MONEY',
        ]);

        $user = $request->user();
        $client = Client::with(['mesures', 'paiements'])->findOrFail($request->clientId);

        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $restant = max(0, $montantTotal - $montantPaye);

        if ($request->montant > $restant && $restant > 0) {
            return response()->json(['message' => 'Le montant dépasse le restant dû'], 400);
        }

        $paiement = Paiement::create([
            'id' => Str::uuid(),
            'montant' => $request->montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'CLIENT',
            'client_id' => $request->clientId,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return response()->json($paiement, 201);
    }

    public function getPaiementsClient(Request $request, $clientId)
    {
        $user = $request->user();
        $client = Client::with(['mesures', 'paiements' => function ($q) use ($request) {
            $q->where('type_paiement', 'CLIENT');
            if ($request->has('month') && $request->has('year')) {
                $q->whereMonth('date_paiement', $request->month)
                    ->whereYear('date_paiement', $request->year);
            }
        }])->findOrFail($clientId);

        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = Paiement::where('client_id', $clientId)->where('type_paiement', 'CLIENT')->sum('montant');

        return response()->json([
            'clientId' => $client->id,
            'clientNom' => $client->prenom . ' ' . $client->nom,
            'contact' => $client->contact,
            'photo' => $client->photo,
            'montantTotal' => $montantTotal,
            'montantPaye' => $montantPaye,
            'montantRestant' => max(0, $montantTotal - $montantPaye),
            'nbMesures' => $client->mesures->count(),
            'statut' => $montantPaye >= $montantTotal && $montantTotal > 0 ? 'SOLDE' : 'EN_ATTENTE',
            'paiements' => $client->paiements->map(fn($p) => $this->formatPaiement($p)),
        ]);
    }

    // ===== PAIEMENTS TAILLEURS =====

    public function createPaiementTailleur(Request $request)
    {
        $request->validate([
            'tailleurId' => 'required|uuid|exists:utilisateurs,id',
            'montant' => 'required|numeric|min:0.01',
            'moyen' => 'in:ESPECES,MOBILE_MONEY',
        ]);

        $user = $request->user();

        $paiement = Paiement::create([
            'id' => Str::uuid(),
            'montant' => $request->montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'TAILLEUR',
            'tailleur_id' => $request->tailleurId,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return response()->json($paiement, 201);
    }

    public function getPaiementsTailleur(Request $request, $tailleurId)
    {
        $tailleur = Utilisateur::findOrFail($tailleurId);

        $paiementsQuery = Paiement::where('tailleur_id', $tailleurId)->where('type_paiement', 'TAILLEUR');

        if ($request->has('month') && $request->has('year')) {
            $paiementsQuery->whereMonth('date_paiement', $request->month)
                ->whereYear('date_paiement', $request->year);
        }

        $paiements = $paiementsQuery->get();

        $totalDu = \App\Models\Affectation::where('tailleur_id', $tailleurId)
            ->whereIn('statut', ['TERMINE', 'VALIDE'])
            ->sum('prix_tailleur');
        $totalPaye = Paiement::where('tailleur_id', $tailleurId)->where('type_paiement', 'TAILLEUR')->sum('montant');

        return response()->json([
            'tailleurId' => $tailleur->id,
            'tailleurNom' => $tailleur->prenom . ' ' . $tailleur->nom,
            'totalDu' => $totalDu,
            'totalPaye' => $totalPaye,
            'totalRestant' => max(0, $totalDu - $totalPaye),
            'statut' => $totalPaye >= $totalDu && $totalDu > 0 ? 'SOLDE' : 'EN_ATTENTE',
            'paiements' => $paiements->map(fn($p) => $this->formatPaiement($p)),
        ]);
    }

    // ===== STATISTIQUES =====

    public function statistiques(Request $request)
    {
        $atelierId = $request->query('atelierId', $request->user()->atelier_id);

        $totalEncaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->sum('montant');
        $totalDecaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'TAILLEUR')->sum('montant');
        $totalDuClients = \App\Models\Mesure::where('atelier_id', $atelierId)->sum('prix');
        $totalDuTailleurs = \App\Models\Affectation::where('atelier_id', $atelierId)
            ->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur');

        return response()->json([
            'totalEncaisse' => $totalEncaisse,
            'totalDecaisse' => $totalDecaisse,
            'solde' => $totalEncaisse - $totalDecaisse,
            'resteAEncaisser' => max(0, $totalDuClients - $totalEncaisse),
            'resteADecaisser' => max(0, $totalDuTailleurs - $totalDecaisse),
        ]);
    }

    public function recouvrementMensuel(Request $request)
    {
        $atelierId = $request->query('atelierId', $request->user()->atelier_id);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $clients = Client::where('atelier_id', $atelierId)
            ->with(['mesures', 'paiements' => fn($q) => $q->where('type_paiement', 'CLIENT')])
            ->get()
            ->filter(fn($c) => $c->mesures->isNotEmpty())
            ->map(fn($c) => [
                'clientId' => $c->id,
                'clientNom' => $c->prenom . ' ' . $c->nom,
                'montantTotal' => $c->mesures->sum('prix'),
                'montantPaye' => $c->paiements->sum('montant'),
                'restant' => max(0, $c->mesures->sum('prix') - $c->paiements->sum('montant')),
                'statut' => $c->paiements->sum('montant') >= $c->mesures->sum('prix') && $c->mesures->sum('prix') > 0
                    ? 'SOLDE' : 'EN_ATTENTE',
            ]);

        return response()->json($clients->values());
    }

    public function rechercheClients(Request $request)
    {
        $atelierId = $request->query('atelierId', $request->user()->atelier_id);
        $term = $request->query('searchTerm', '');

        $clients = Client::where('atelier_id', $atelierId)
            ->where(fn($q) => $q->where('nom', 'like', "%$term%")->orWhere('prenom', 'like', "%$term%"))
            ->with(['mesures', 'paiements'])
            ->get()
            ->map(fn($c) => [
                'clientId' => $c->id,
                'clientNom' => $c->prenom . ' ' . $c->nom,
                'contact' => $c->contact,
                'montantTotal' => $c->mesures->sum('prix'),
                'montantPaye' => $c->paiements->where('type_paiement', 'CLIENT')->sum('montant'),
                'montantRestant' => max(0, $c->mesures->sum('prix') - $c->paiements->where('type_paiement', 'CLIENT')->sum('montant')),
            ]);

        return response()->json($clients);
    }

    // ===== REÇUS =====

    public function recuClient($paiementId, Request $request)
    {
        $atelierId = $request->query('atelierId');
        $paiement = Paiement::with(['client.mesures', 'atelier'])->findOrFail($paiementId);

        return response()->json($this->buildRecu($paiement));
    }

    public function recuClientDu($clientId, Request $request)
    {
        $atelierId = $request->query('atelierId');
        $client = Client::with(['mesures', 'paiements'])->findOrFail($clientId);

        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');

        return response()->json([
            'clientNom' => $client->prenom . ' ' . $client->nom,
            'clientContact' => $client->contact,
            'montantTotal' => $montantTotal,
            'avance' => $montantPaye,
            'montantRestant' => max(0, $montantTotal - $montantPaye),
            'nbModeles' => $client->mesures->count(),
            'date' => now()->toDateString(),
            'statut' => $montantPaye >= $montantTotal && $montantTotal > 0 ? 'SOLDE' : 'EN_ATTENTE',
        ]);
    }

    public function recuTailleur($paiementId, Request $request)
    {
        $paiement = Paiement::with(['tailleur', 'atelier'])->findOrFail($paiementId);
        return response()->json($this->buildRecuTailleur($paiement));
    }

    public function imprimerRecu(Request $request)
    {
        return response()->json($request->all());
    }

    private function buildRecu(Paiement $p): array
    {
        $client = $p->client;
        $montantTotal = $client ? $client->mesures->sum('prix') : 0;
        $montantPaye = $client ? Paiement::where('client_id', $client->id)->where('type_paiement', 'CLIENT')->sum('montant') : 0;

        return [
            'paiementId' => $p->id,
            'reference' => $p->reference,
            'clientNom' => $client ? $client->prenom . ' ' . $client->nom : null,
            'clientContact' => $client?->contact,
            'montant' => $p->montant,
            'montantTotal' => $montantTotal,
            'avance' => $montantPaye,
            'montantRestant' => max(0, $montantTotal - $montantPaye),
            'nbModeles' => $client ? $client->mesures->count() : 0,
            'moyen' => $p->moyen,
            'date' => $p->date_paiement,
            'atelierNom' => $p->atelier?->nom,
        ];
    }

    private function buildRecuTailleur(Paiement $p): array
    {
        return [
            'paiementId' => $p->id,
            'reference' => $p->reference,
            'tailleurNom' => $p->tailleur ? $p->tailleur->prenom . ' ' . $p->tailleur->nom : null,
            'montant' => $p->montant,
            'moyen' => $p->moyen,
            'date' => $p->date_paiement,
            'atelierNom' => $p->atelier?->nom,
        ];
    }

    private function formatPaiement(Paiement $p): array
    {
        return [
            'id' => $p->id,
            'montant' => $p->montant,
            'moyen' => $p->moyen,
            'reference' => $p->reference,
            'datePaiement' => $p->date_paiement,
            'typePaiement' => $p->type_paiement,
            'note' => $p->note,
        ];
    }
}
