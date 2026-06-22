<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Atelier;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Paiement;
use App\Models\Rendezvous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'SUPERADMIN' => $this->dashboardSuperAdmin(),
            'PROPRIETAIRE', 'SECRETAIRE' => $this->dashboardAtelier($user),
            'TAILLEUR' => $this->dashboardTailleur($user),
            default => response()->json(['message' => 'Rôle non reconnu'], 403),
        };
    }

    private function dashboardSuperAdmin(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'type' => 'SUPERADMIN',
            'nbAteliers' => Atelier::count(),
            'nbUtilisateurs' => Utilisateur::count(),
            'nbClients' => Client::count(),
            'totalPaiements' => Paiement::where('type_paiement', 'CLIENT')->sum('montant'),
        ]);
    }

    private function dashboardAtelier(Utilisateur $user): \Illuminate\Http\JsonResponse
    {
        $atelierId = $user->atelier_id;

        $nbClients = Client::where('atelier_id', $atelierId)->count();
        $nbMesures = Mesure::where('atelier_id', $atelierId)->count();
        $nbAffectations = Affectation::where('atelier_id', $atelierId)->count();
        $affectationsEnCours = Affectation::where('atelier_id', $atelierId)->where('statut', 'EN_COURS')->count();
        $affectationsTerminees = Affectation::where('atelier_id', $atelierId)->where('statut', 'TERMINE')->count();
        $totalEncaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->sum('montant');
        $totalDecaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'TAILLEUR')->sum('montant');
        $totalDuClients = Mesure::where('atelier_id', $atelierId)->sum('prix');
        $rdvsAVenir = Rendezvous::where('atelier_id', $atelierId)
            ->where('date_rdv', '>=', now())
            ->whereNotIn('statut', ['ANNULE', 'TERMINE'])
            ->count();
        $rdvsAujourdhui = Rendezvous::where('atelier_id', $atelierId)
            ->whereDate('date_rdv', now()->toDateString())
            ->count();

        $prochainRdvs = Rendezvous::with('client')
            ->where('atelier_id', $atelierId)
            ->where('date_rdv', '>=', now())
            ->whereNotIn('statut', ['ANNULE', 'TERMINE'])
            ->orderBy('date_rdv')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'dateRDV' => $r->date_rdv,
                'typeRendezVous' => $r->type_rendezvous,
                'statut' => $r->statut,
                'clientNom' => $r->client ? $r->client->prenom . ' ' . $r->client->nom : null,
            ]);

        return response()->json([
            'type' => $user->role,
            'nbClients' => $nbClients,
            'nbMesures' => $nbMesures,
            'nbAffectations' => $nbAffectations,
            'affectationsEnCours' => $affectationsEnCours,
            'affectationsTerminees' => $affectationsTerminees,
            'totalEncaisse' => $totalEncaisse,
            'totalDecaisse' => $totalDecaisse,
            'solde' => $totalEncaisse - $totalDecaisse,
            'totalDuClients' => $totalDuClients,
            'resteAEncaisser' => max(0, $totalDuClients - $totalEncaisse),
            'rdvsAVenir' => $rdvsAVenir,
            'rdvsAujourdhui' => $rdvsAujourdhui,
            'prochainRdvs' => $prochainRdvs,
        ]);
    }

    private function dashboardTailleur(Utilisateur $user): \Illuminate\Http\JsonResponse
    {
        $tailleurId = $user->id;

        $affectations = Affectation::where('tailleur_id', $tailleurId)->get();
        $enCours = $affectations->where('statut', 'EN_COURS')->count();
        $terminees = $affectations->where('statut', 'TERMINE')->count();
        $validees = $affectations->where('statut', 'VALIDE')->count();
        $totalDu = $affectations->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur');
        $totalPaye = Paiement::where('tailleur_id', $tailleurId)->where('type_paiement', 'TAILLEUR')->sum('montant');

        return response()->json([
            'type' => 'TAILLEUR',
            'nbAffectations' => $affectations->count(),
            'enCours' => $enCours,
            'terminees' => $terminees,
            'validees' => $validees,
            'totalDu' => $totalDu,
            'totalPaye' => $totalPaye,
            'totalRestant' => max(0, $totalDu - $totalPaye),
        ]);
    }

    public function statistiques(Request $request, $atelierId)
    {
        $dateDebut = $request->query('dateDebut', now()->startOfMonth()->toDateString());
        $dateFin = $request->query('dateFin', now()->endOfMonth()->toDateString());

        return response()->json([
            'atelierId' => $atelierId,
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
            'nbNouveauxClients' => Client::where('atelier_id', $atelierId)
                ->whereBetween('date_creation', [$dateDebut, $dateFin])->count(),
            'nbAffectations' => Affectation::where('atelier_id', $atelierId)
                ->whereBetween('date_creation', [$dateDebut, $dateFin])->count(),
            'totalEncaisse' => Paiement::where('atelier_id', $atelierId)
                ->where('type_paiement', 'CLIENT')
                ->whereBetween('date_paiement', [$dateDebut, $dateFin])->sum('montant'),
            'totalDecaisse' => Paiement::where('atelier_id', $atelierId)
                ->where('type_paiement', 'TAILLEUR')
                ->whereBetween('date_paiement', [$dateDebut, $dateFin])->sum('montant'),
        ]);
    }

    public function tailleurStatistiques($tailleurId)
    {
        $affectations = Affectation::where('tailleur_id', $tailleurId)->get();
        return response()->json([
            'total' => $affectations->count(),
            'parStatut' => $affectations->groupBy('statut')->map->count(),
            'totalGagne' => $affectations->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur'),
        ]);
    }

    public function health()
    {
        return response()->json([
            'status' => 'OK',
            'service' => 'ATELIKO Laravel API',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
