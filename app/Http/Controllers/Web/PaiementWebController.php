<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Client;
use App\Models\Paiement;
use App\Models\Rendezvous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaiementWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $atelierId = $user->atelier_id;
        $tab = $request->get('tab', 'clients');

        $clients = Client::where('atelier_id', $atelierId)
            ->with(['mesures', 'paiements'])
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'nom' => $c->prenom . ' ' . $c->nom,
                'contact' => $c->contact,
                'photo' => $c->photo,
                'montantTotal' => $c->mesures->sum('prix'),
                'montantPaye' => $c->paiements->where('type_paiement', 'CLIENT')->sum('montant'),
                'montantRestant' => max(0, $c->mesures->sum('prix') - $c->paiements->where('type_paiement', 'CLIENT')->sum('montant')),
                'nbMesuresSansLivraison' => $c->mesures->whereNull('date_livraison')->count(),
                'estSolde' => $c->mesures->sum('prix') > 0 && $c->paiements->where('type_paiement', 'CLIENT')->sum('montant') >= $c->mesures->sum('prix'),
            ]);

        $tailleurs = Utilisateur::where('atelier_id', $atelierId)->where('role', 'TAILLEUR')->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'nom' => $t->prenom . ' ' . $t->nom,
                'totalDu' => Affectation::where('tailleur_id', $t->id)->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur'),
                'totalPaye' => Paiement::where('tailleur_id', $t->id)->where('type_paiement', 'TAILLEUR')->sum('montant'),
            ]);

        $totalEncaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->sum('montant');
        $totalDecaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'TAILLEUR')->sum('montant');

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $mesuresMois = \App\Models\Mesure::where('atelier_id', $atelierId)
            ->whereBetween('created_at', [$monthStart, $monthEnd]);
        $synthese = [
            'encaissementsMois' => Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->whereBetween('date_paiement', [$monthStart, $monthEnd])->sum('montant'),
            'nombreModeles' => (clone $mesuresMois)->count(),
            'nombreSorties' => \App\Models\Mesure::where('atelier_id', $atelierId)->whereBetween('date_livraison', [$monthStart, $monthEnd])->count(),
            'montantModeles' => (clone $mesuresMois)->sum('prix'),
        ];

        return view('paiements.index', compact('clients', 'tailleurs', 'tab', 'totalEncaisse', 'totalDecaisse', 'synthese'));
    }

    public function storeClient(Request $request)
    {
        $request->validate(['client_id' => 'required|uuid', 'montant' => 'required|numeric|min:0.01']);
        $user = Auth::user();
        $client = Client::with(['mesures', 'paiements'])
            ->where('atelier_id', $user->atelier_id)
            ->findOrFail($request->client_id);

        $totalDu = (float) $client->mesures->sum('prix');
        $dejaPaye = (float) $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $resteAPayer = max(0, $totalDu - $dejaPaye);
        $montant = (float) $request->montant;

        if ($totalDu <= 0 || $resteAPayer <= 0) {
            return $this->paymentError($request, 'Ce client a déjà soldé. Aucun paiement en plus n’est autorisé.');
        }

        if ($montant > $resteAPayer) {
            return $this->paymentError(
                $request,
                'Montant trop élevé : il reste seulement ' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA à payer.'
            );
        }

        $paiement = Paiement::create([
            'id' => Str::uuid(),
            'montant' => $montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'CLIENT',
            'client_id' => $client->id,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        $totalPaye = $dejaPaye + $montant;
        if ($totalDu > 0 && $totalPaye >= $totalDu) {
            $this->activateReadyRendezvousForClient($client);
        }

        if ($request->expectsJson()) {
            $atelierNom = $user->atelier?->nom ?? 'Atelier';
            return response()->json([
                'message' => 'Paiement enregistré avec succès',
                'receipt' => [
                    'typeTicket' => 'PAIEMENT',
                    'autoWhatsApp' => true,
                    'statut' => 'Reçu client',
                    'reference' => 'PAY-' . strtoupper(substr($paiement->id, 0, 8)),
                    'dateFormatted' => now()->format('d/m/Y H:i'),
                    'beneficiaire' => trim(($client->prenom ?? '') . ' ' . ($client->nom ?? '')),
                    'contact' => $client->contact ?? '',
                    'moyenPaiement' => strtoupper($request->moyen ?? 'ESPECES'),
                    'montant' => $montant,
                    'totalDu' => (float) $totalDu,
                    'avancePaye' => (float) $totalPaye,
                    'resteAPayer' => (float) max(0, $totalDu - $totalPaye),
                    'atelierNom' => $atelierNom,
                    'messageMarketing' => 'Merci pour votre confiance en ' . $atelierNom . ' !',
                ],
            ]);
        }

        return redirect()->route('paiements.index', ['tab' => 'clients'])->with('success', 'Paiement client enregistré');
    }

    public function storeTailleur(Request $request)
    {
        $request->validate(['tailleur_id' => 'required|uuid', 'montant' => 'required|numeric|min:0.01']);
        $user = Auth::user();
        $tailleur = Utilisateur::where('atelier_id', $user->atelier_id)
            ->where('role', 'TAILLEUR')
            ->findOrFail($request->tailleur_id);

        $totalDu = (float) Affectation::where('tailleur_id', $tailleur->id)
            ->whereIn('statut', ['TERMINE', 'VALIDE'])
            ->sum('prix_tailleur');
        $dejaPaye = (float) Paiement::where('tailleur_id', $tailleur->id)
            ->where('type_paiement', 'TAILLEUR')
            ->sum('montant');
        $resteAPayer = max(0, $totalDu - $dejaPaye);
        $montant = (float) $request->montant;

        if ($totalDu <= 0 || $resteAPayer <= 0) {
            return $this->paymentError($request, 'Ce tailleur est déjà soldé. Aucun paiement en plus n’est autorisé.');
        }

        if ($montant > $resteAPayer) {
            return $this->paymentError(
                $request,
                'Montant trop élevé : il reste seulement ' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA à verser.'
            );
        }

        Paiement::create([
            'id' => Str::uuid(),
            'montant' => $montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'TAILLEUR',
            'tailleur_id' => $tailleur->id,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return redirect()->route('paiements.index', ['tab' => 'tailleurs'])->with('success', 'Paiement tailleur enregistré');
    }

    public function recuClient($clientId)
    {
        $client = Client::with(['mesures', 'paiements', 'atelier'])->findOrFail($clientId);
        $montantTotal = $client->mesures->sum('prix');
        $montantPaye  = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');

        if (request()->expectsJson()) {
            $atelierNom = $client->atelier?->nom ?? 'Atelier';
            return response()->json([
                'receipt' => [
                    'typeTicket'       => 'PAIEMENT',
                    'statut'           => 'Reçu client',
                    'reference'        => 'CLI-' . strtoupper(substr($clientId, 0, 8)),
                    'dateFormatted'    => now()->format('d/m/Y H:i'),
                    'beneficiaire'     => trim(($client->prenom ?? '') . ' ' . ($client->nom ?? '')),
                    'contact'          => $client->contact ?? '',
                    'nombreModeles'    => $client->mesures->count(),
                    'montant'          => (float) $montantPaye,
                    'totalDu'          => (float) $montantTotal,
                    'avancePaye'       => (float) $montantPaye,
                    'resteAPayer'      => (float) max(0, $montantTotal - $montantPaye),
                    'atelierNom'       => $atelierNom,
                    'messageMarketing' => 'Merci pour votre confiance chez ' . $atelierNom . ' !',
                ],
            ]);
        }

        return view('paiements.recu-client', compact('client', 'montantTotal', 'montantPaye'));
    }

    public function enregistrerSortie($clientId)
    {
        $user = Auth::user();
        // Sécurité : le client doit appartenir à cet atelier
        Client::where('atelier_id', $user->atelier_id)->findOrFail($clientId);

        $nb = \App\Models\Mesure::where('client_id', $clientId)
            ->where('atelier_id', $user->atelier_id)
            ->whereNull('date_livraison')
            ->update(['date_livraison' => now()]);

        $total = \App\Models\Mesure::where('atelier_id', $user->atelier_id)
            ->whereBetween('date_livraison', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return response()->json([
            'message' => $nb . ' habit(s) marqué(s) comme livré(s)',
            'nbLivres' => $nb,
            'nouvellesTotalSorties' => $total,
        ]);
    }

    public function recuTailleur($tailleurId)
    {
        $tailleur = Utilisateur::findOrFail($tailleurId);
        $paiements = Paiement::where('tailleur_id', $tailleurId)->where('type_paiement', 'TAILLEUR')->get();
        $totalDu = Affectation::where('tailleur_id', $tailleurId)->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur');
        $totalPaye = $paiements->sum('montant');

        return view('paiements.recu-tailleur', compact('tailleur', 'paiements', 'totalDu', 'totalPaye'));
    }

    private function paymentError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    private function activateReadyRendezvousForClient(Client $client): void
    {
        $hasReadyHabit = Affectation::where('client_id', $client->id)
            ->where('atelier_id', $client->atelier_id)
            ->whereIn('statut', ['TERMINE', 'VALIDE'])
            ->exists();

        if (!$hasReadyHabit) {
            return;
        }

        Rendezvous::where('client_id', $client->id)
            ->where('atelier_id', $client->atelier_id)
            ->whereIn('statut', ['PLANIFIE', 'CONFIRME'])
            ->get()
            ->each(function (Rendezvous $rdv) {
                $updates = ['statut' => 'PRET'];
                if (!$rdv->date_rdv || $rdv->date_rdv->lt(now())) {
                    $updates['date_rdv'] = now();
                }
                $rdv->update($updates);
            });
    }
}
