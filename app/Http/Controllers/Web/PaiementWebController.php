<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Client;
use App\Models\Paiement;
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
            'encaissementsMois' => Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->whereBetween('created_at', [$monthStart, $monthEnd])->sum('montant'),
            'nombreModeles' => (clone $mesuresMois)->count(),
            'nombreSorties' => Affectation::where('atelier_id', $atelierId)->whereIn('statut', ['TERMINE','VALIDE'])->whereBetween('date_fin_reelle', [$monthStart, $monthEnd])->count(),
            'montantModeles' => (clone $mesuresMois)->sum('prix'),
        ];

        return view('paiements.index', compact('clients', 'tailleurs', 'tab', 'totalEncaisse', 'totalDecaisse', 'synthese'));
    }

    public function storeClient(Request $request)
    {
        $request->validate(['client_id' => 'required|uuid', 'montant' => 'required|numeric|min:0.01']);
        $user = Auth::user();

        Paiement::create([
            'id' => Str::uuid(),
            'montant' => $request->montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'CLIENT',
            'client_id' => $request->client_id,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return redirect()->route('paiements.index', ['tab' => 'clients'])->with('success', 'Paiement client enregistré');
    }

    public function storeTailleur(Request $request)
    {
        $request->validate(['tailleur_id' => 'required|uuid', 'montant' => 'required|numeric|min:0.01']);
        $user = Auth::user();

        Paiement::create([
            'id' => Str::uuid(),
            'montant' => $request->montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'TAILLEUR',
            'tailleur_id' => $request->tailleur_id,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return redirect()->route('paiements.index', ['tab' => 'tailleurs'])->with('success', 'Paiement tailleur enregistré');
    }

    public function recuClient($clientId)
    {
        $client = Client::with(['mesures', 'paiements', 'atelier'])->findOrFail($clientId);
        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');

        return view('paiements.recu-client', compact('client', 'montantTotal', 'montantPaye'));
    }

    public function recuTailleur($tailleurId)
    {
        $tailleur = Utilisateur::findOrFail($tailleurId);
        $paiements = Paiement::where('tailleur_id', $tailleurId)->where('type_paiement', 'TAILLEUR')->get();
        $totalDu = Affectation::where('tailleur_id', $tailleurId)->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur');
        $totalPaye = $paiements->sum('montant');

        return view('paiements.recu-tailleur', compact('tailleur', 'paiements', 'totalDu', 'totalPaye'));
    }
}
