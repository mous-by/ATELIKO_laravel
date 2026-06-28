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
            'moyen' => 'nullable|in:ESPECES,MOBILE_MONEY,VIREMENT,CARTE',
        ]);

        $user = $request->user();
        $client = Client::with(['mesures', 'paiements'])
            ->where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)))
            ->findOrFail($request->clientId);

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
            'reference' => $request->reference,
            'date_paiement' => $request->datePaiement ?? now(),
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
        }])
            ->where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)))
            ->findOrFail($clientId);

        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = Paiement::where('client_id', $clientId)->where('type_paiement', 'CLIENT')->sum('montant');
        $montantRestant = max(0, $montantTotal - $montantPaye);

        return response()->json([
            'clientId' => $client->id,
            'clientNom' => $client->prenom . ' ' . $client->nom,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'contact' => $client->contact,
            'telephone' => $client->contact,
            'photo' => $client->photo,
            'montantTotal' => $montantTotal,
            'prixTotal' => $montantTotal,
            'totalDu' => $montantTotal,
            'montantPaye' => $montantPaye,
            'montantRestant' => $montantRestant,
            'resteAPayer' => $montantRestant,
            'nbMesures' => $client->mesures->count(),
            'statut' => $montantPaye >= $montantTotal && $montantTotal > 0 ? 'SOLDE' : 'EN_ATTENTE',
            'statutPaiement' => $montantTotal > 0 && $montantRestant <= 0 ? 'PAYE' : ($montantPaye > 0 ? 'PARTIEL' : 'EN_ATTENTE'),
            'paiements' => $client->paiements->map(fn($p) => $this->formatPaiement($p)),
        ]);
    }

    // ===== PAIEMENTS TAILLEURS =====

    public function createPaiementTailleur(Request $request)
    {
        $request->validate([
            'tailleurId' => 'required|uuid|exists:utilisateurs,id',
            'montant' => 'required|numeric|min:0.01',
            'moyen' => 'nullable|in:ESPECES,MOBILE_MONEY,VIREMENT,CARTE',
        ]);

        $user = $request->user();
        $tailleur = Utilisateur::where('atelier_id', $user->atelier_id)->where('role', 'TAILLEUR')->findOrFail($request->tailleurId);

        $paiement = Paiement::create([
            'id' => Str::uuid(),
            'montant' => $request->montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'reference' => $request->reference,
            'date_paiement' => $request->datePaiement ?? now(),
            'type_paiement' => 'TAILLEUR',
            'tailleur_id' => $tailleur->id,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return response()->json($paiement, 201);
    }

    public function getPaiementsTailleur(Request $request, $tailleurId)
    {
        $user = $request->user();
        $tailleur = Utilisateur::where('atelier_id', $user->atelier_id)->findOrFail($tailleurId);

        $paiementsQuery = Paiement::where('atelier_id', $user->atelier_id)
            ->where('tailleur_id', $tailleurId)
            ->where('type_paiement', 'TAILLEUR');

        if ($request->has('month') && $request->has('year')) {
            $paiementsQuery->whereMonth('date_paiement', $request->month)
                ->whereYear('date_paiement', $request->year);
        }

        $paiements = $paiementsQuery->get();

        $totalDu = \App\Models\Affectation::where('atelier_id', $user->atelier_id)
            ->where('tailleur_id', $tailleurId)
            ->whereIn('statut', ['TERMINE', 'VALIDE'])
            ->sum('prix_tailleur');
        $totalPaye = Paiement::where('atelier_id', $user->atelier_id)->where('tailleur_id', $tailleurId)->where('type_paiement', 'TAILLEUR')->sum('montant');
        $totalRestant = max(0, $totalDu - $totalPaye);

        return response()->json([
            'tailleurId' => $tailleur->id,
            'tailleurNom' => $tailleur->prenom . ' ' . $tailleur->nom,
            'nom' => $tailleur->nom,
            'prenom' => $tailleur->prenom,
            'contact' => $tailleur->telephone,
            'totalDu' => $totalDu,
            'montantTotal' => $totalDu,
            'totalPaye' => $totalPaye,
            'montantPaye' => $totalPaye,
            'totalRestant' => $totalRestant,
            'resteAPayer' => $totalRestant,
            'statut' => $totalPaye >= $totalDu && $totalDu > 0 ? 'SOLDE' : 'EN_ATTENTE',
            'statutPaiement' => $totalDu > 0 && $totalRestant <= 0 ? 'PAYE' : ($totalPaye > 0 ? 'PARTIEL' : 'EN_ATTENTE'),
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

    public function synthese(Request $request)
    {
        $atelierId = $request->query('atelierId', $request->user()->atelier_id);
        $now = now();

        $encaissementsMois = Paiement::where('atelier_id', $atelierId)
            ->where('type_paiement', 'CLIENT')
            ->whereMonth('date_paiement', $now->month)
            ->whereYear('date_paiement', $now->year)
            ->sum('montant');

        $nombreModeles = \App\Models\Mesure::where('atelier_id', $atelierId)->count();

        $nombreSorties = \App\Models\Mesure::where('atelier_id', $atelierId)
            ->whereMonth('date_livraison', $now->month)
            ->whereYear('date_livraison', $now->year)
            ->whereNotNull('date_livraison')
            ->count();

        $montantModeles = \App\Models\Mesure::where('atelier_id', $atelierId)->sum('prix');

        return response()->json([
            'encaissementsMois' => $encaissementsMois,
            'nombreModeles'     => $nombreModeles,
            'nombreSorties'     => $nombreSorties,
            'montantModeles'    => $montantModeles,
        ]);
    }

    public function enregistrerSortie(Request $request, $clientId)
    {
        $user = $request->user();
        Client::where('atelier_id', $user->atelier_id)->findOrFail($clientId);

        $nb = \App\Models\Mesure::where('client_id', $clientId)
            ->where('atelier_id', $user->atelier_id)
            ->whereNull('date_livraison')
            ->update(['date_livraison' => now()]);

        $totalSorties = \App\Models\Mesure::where('atelier_id', $user->atelier_id)
            ->whereMonth('date_livraison', now()->month)
            ->whereYear('date_livraison', now()->year)
            ->whereNotNull('date_livraison')
            ->count();

        return response()->json([
            'message'             => $nb . ' habit(s) marqué(s) comme livré(s)',
            'nbLivres'            => $nb,
            'nouvellesTotalSorties' => $totalSorties,
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
        $statutPaiement = $request->query('statutPaiement');

        $user = $request->user();
        $clients = Client::where('atelier_id', $atelierId)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)))
            ->where(fn($q) => $q->where('nom', 'like', "%$term%")->orWhere('prenom', 'like', "%$term%"))
            ->with(['mesures', 'paiements'])
            ->get()
            ->map(function ($c) {
                $montantTotal = $c->mesures->sum('prix');
                $montantPaye = $c->paiements->where('type_paiement', 'CLIENT')->sum('montant');
                $reste = max(0, $montantTotal - $montantPaye);
                $statutPaiement = $montantTotal > 0 && $reste <= 0 ? 'PAYE' : ($montantPaye > 0 ? 'PARTIEL' : 'EN_ATTENTE');

                return [
                    'clientId' => $c->id,
                    'id' => $c->id,
                    'clientNom' => $c->prenom . ' ' . $c->nom,
                    'nom' => $c->nom,
                    'prenom' => $c->prenom,
                    'contact' => $c->contact,
                    'telephone' => $c->contact,
                    'montantTotal' => $montantTotal,
                    'prixTotal' => $montantTotal,
                    'totalDu' => $montantTotal,
                    'montantPaye' => $montantPaye,
                    'montantRestant' => $reste,
                    'resteAPayer' => $reste,
                    'statutPaiement' => $statutPaiement,
                ];
            })
            ->when($statutPaiement, fn ($items) => $items->where('statutPaiement', $statutPaiement)->values());

        return response()->json($clients);
    }

    public function rechercheTailleurs(Request $request)
    {
        abort_if($request->user()->isTailleur(), 403);
        $atelierId = $request->query('atelierId', $request->user()->atelier_id);
        $term = $request->query('searchTerm', '');
        $statutPaiement = $request->query('statutPaiement');

        $tailleurs = Utilisateur::where('atelier_id', $atelierId)
            ->where('role', 'TAILLEUR')
            ->where(fn ($q) => $q->where('nom', 'like', "%$term%")->orWhere('prenom', 'like', "%$term%")->orWhere('telephone', 'like', "%$term%"))
            ->with(['atelier'])
            ->get()
            ->map(function ($t) use ($atelierId) {
                $totalDu = \App\Models\Affectation::where('atelier_id', $atelierId)->where('tailleur_id', $t->id)->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur');
                $totalPaye = Paiement::where('atelier_id', $atelierId)->where('tailleur_id', $t->id)->where('type_paiement', 'TAILLEUR')->sum('montant');
                $reste = max(0, $totalDu - $totalPaye);
                $statutPaiement = $totalDu > 0 && $reste <= 0 ? 'PAYE' : ($totalPaye > 0 ? 'PARTIEL' : 'EN_ATTENTE');

                return [
                    'tailleurId' => $t->id,
                    'id' => $t->id,
                    'tailleurNom' => trim($t->prenom . ' ' . $t->nom),
                    'nom' => $t->nom,
                    'prenom' => $t->prenom,
                    'contact' => $t->telephone,
                    'telephone' => $t->telephone,
                    'montantTotal' => $totalDu,
                    'totalDu' => $totalDu,
                    'montantPaye' => $totalPaye,
                    'totalPaye' => $totalPaye,
                    'montantRestant' => $reste,
                    'resteAPayer' => $reste,
                    'statutPaiement' => $statutPaiement,
                ];
            })
            ->when($statutPaiement, fn ($items) => $items->where('statutPaiement', $statutPaiement)->values());

        return response()->json($tailleurs);
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
            'clientId' => $client->id,
            'clientNom' => $client->nom,
            'clientPrenom' => $client->prenom,
            'beneficiaire' => trim($client->prenom . ' ' . $client->nom),
            'clientContact' => $client->contact,
            'montantTotal' => $montantTotal,
            'totalDu' => $montantTotal,
            'montant' => $montantPaye,
            'avance' => $montantPaye,
            'avancePaye' => $montantPaye,
            'montantRestant' => max(0, $montantTotal - $montantPaye),
            'resteAPayer' => max(0, $montantTotal - $montantPaye),
            'nbModeles' => $client->mesures->count(),
            'nombreModeles' => $client->mesures->count(),
            'date' => now()->toDateString(),
            'datePaiement' => now(),
            'dateFormatted' => now()->format('d/m/Y H:i'),
            'statut' => $montantPaye >= $montantTotal && $montantTotal > 0 ? 'SOLDE' : 'EN_ATTENTE',
            'atelierNom' => $client->atelier?->nom,
            'reference' => 'CLI-' . strtoupper(substr($client->id, 0, 8)),
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
            'id' => $p->id,
            'reference' => $p->reference,
            'clientNom' => $client ? $client->prenom . ' ' . $client->nom : null,
            'clientPrenom' => $client?->prenom,
            'clientContact' => $client?->contact,
            'montant' => $p->montant,
            'montantTotal' => $montantTotal,
            'totalDu' => $montantTotal,
            'avance' => $montantPaye,
            'avancePaye' => $montantPaye,
            'montantRestant' => max(0, $montantTotal - $montantPaye),
            'resteAPayer' => max(0, $montantTotal - $montantPaye),
            'nbModeles' => $client ? $client->mesures->count() : 0,
            'nombreModeles' => $client ? $client->mesures->count() : 0,
            'moyen' => $p->moyen,
            'moyenPaiement' => $p->moyen,
            'date' => $p->date_paiement,
            'datePaiement' => $p->date_paiement,
            'atelierNom' => $p->atelier?->nom,
        ];
    }

    private function buildRecuTailleur(Paiement $p): array
    {
        return [
            'paiementId' => $p->id,
            'id' => $p->id,
            'reference' => $p->reference,
            'tailleurNom' => $p->tailleur ? $p->tailleur->prenom . ' ' . $p->tailleur->nom : null,
            'tailleurPrenom' => $p->tailleur?->prenom,
            'montant' => $p->montant,
            'moyen' => $p->moyen,
            'moyenPaiement' => $p->moyen,
            'date' => $p->date_paiement,
            'datePaiement' => $p->date_paiement,
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
