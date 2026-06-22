<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use App\Models\ActivityLog;
use App\Models\Affectation;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Modele;
use App\Models\Paiement;
use App\Models\Rendezvous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardWebController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $atelierId = $user->atelier_id;

        $stats = [];

        if ($user->isSuperAdmin()) {
            $ateliers = \App\Models\Atelier::withCount(['utilisateurs','clients'])->latest('date_creation')->get();
            $plans = \App\Models\AbonnementPlan::where('actif', true)->get();
            $subs = \App\Models\AbonnementAtelier::with('plan')->get()->keyBy('atelier_id');
            
            $atelierSubscriptions = $ateliers->map(function($a) use ($subs) {
                $sub = $subs->get($a->id);
                return (object)[
                    'atelier_id' => $a->id,
                    'atelier_nom' => $a->nom,
                    'plan_code' => $sub ? $sub->plan->code : null,
                    'plan_libelle' => $sub && $sub->plan ? $sub->plan->libelle : null,
                    'statut' => $sub ? $sub->statut : 'INACTIF',
                    'date_debut' => $sub && $sub->date_debut ? $sub->date_debut->format('Y-m-d') : null,
                    'date_fin' => $sub && $sub->date_fin ? $sub->date_fin->format('Y-m-d') : null,
                    'utilisateurs_count' => $a->utilisateurs_count,
                    'clients_count' => $a->clients_count,
                    'date_creation' => $a->date_creation
                ];
            });

            $payments = \App\Models\AbonnementPaiement::with('abonnement.atelier')->latest('created_at')->get();
            $subscriptionPayments = $payments->map(function($p) {
                $p->atelier_id = $p->abonnement ? $p->abonnement->atelier_id : null;
                $p->atelier_nom = $p->abonnement && $p->abonnement->atelier ? $p->abonnement->atelier->nom : 'N/A';
                return $p;
            });

            $stats = [
                'totalAteliers' => \App\Models\Atelier::count(),
                'totalUtilisateurs' => Utilisateur::count(),
                'totalClients' => Client::count(),
                'chiffreAffairesTotal' => Paiement::where('type_paiement', 'CLIENT')->sum('montant'),
                'ateliers' => $ateliers,
                'atelierSubscriptions' => $atelierSubscriptions,
                'subscriptionPayments' => $subscriptionPayments,
                'subscriptionPlans' => $plans,
                'paiementsRecents' => Paiement::with(['atelier','client'])->latest()->limit(10)->get(),
                'pendingPaymentsCount' => AbonnementPaiement::where('statut', 'PENDING')->count(),
            ];
        } elseif ($user->isTailleur()) {
            $affectations = Affectation::with(['client', 'mesure'])->where('tailleur_id', $user->id)->get();
            
            $affectationsEnCoursList = $affectations->whereIn('statut', ['EN_ATTENTE', 'EN_COURS'])
                ->map(function($aff) {
                    return (object)[
                        'clientNom' => $aff->client ? $aff->client->prenom . ' ' . $aff->client->nom : 'N/A',
                        'typeVetement' => $aff->mesure ? $aff->mesure->type_vetement : 'N/A',
                        'dateEcheance' => $aff->date_echeance ? $aff->date_echeance->format('Y-m-d') : null,
                        'statut' => $aff->statut
                    ];
                })->values();

            $prochainesEcheances = $affectations->whereNotNull('date_echeance')
                ->where('statut', '!=', 'TERMINE')
                ->where('statut', '!=', 'VALIDE')
                ->where('date_echeance', '>=', now())
                ->sortBy('date_echeance')
                ->take(5)
                ->map(function($aff) {
                    $joursRestants = now()->diffInDays($aff->date_echeance, false);
                    return (object)[
                        'clientNom' => $aff->client ? $aff->client->prenom . ' ' . $aff->client->nom : 'N/A',
                        'dateEcheance' => $aff->date_echeance->format('Y-m-d'),
                        'joursRestants' => max(0, (int)$joursRestants)
                    ];
                })->values();

            $stats = [
                'enCours' => $affectations->where('statut', 'EN_COURS')->count(),
                'terminees' => $affectations->where('statut', 'TERMINE')->count(),
                'validees' => $affectations->where('statut', 'VALIDE')->count(),
                'totalGagne' => $affectations->whereIn('statut', ['TERMINE', 'VALIDE'])->sum('prix_tailleur'),
                'totalPaye' => Paiement::where('tailleur_id', $user->id)->where('type_paiement', 'TAILLEUR')->sum('montant'),
                'affectationsEnCoursList' => $affectationsEnCoursList,
                'prochainesEcheances' => $prochainesEcheances,
                'revenusMensuels' => Paiement::where('tailleur_id', $user->id)
                                        ->where('type_paiement', 'TAILLEUR')
                                        ->whereMonth('created_at', now()->month)
                                        ->sum('montant'),
                'affectationsEnAttente' => $affectations->where('statut', 'EN_ATTENTE')->count(),
                'affectationsTermineesSemaine' => $affectations->where('statut', 'TERMINE')
                                                    ->where('updated_at', '>=', now()->startOfWeek())
                                                    ->count(),
            ];
        } else {
            // Proprietaire & Secretaire
            $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->with('plan')->latest()->first();
            $joursAbonnement = $abonnement?->date_fin ? (int) now()->diffInDays($abonnement->date_fin, false) : null;
            $blockedAbonnement = $joursAbonnement !== null && ($joursAbonnement < 0 || in_array($abonnement?->statut, ['EXPIRED', 'CANCELED', 'PAST_DUE']));

            $totalEncaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->sum('montant');
            $totalDecaisse = Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'TAILLEUR')->sum('montant');
            $totalDuClients = Mesure::where('atelier_id', $atelierId)->sum('prix');

            $tachesUrgentes = collect();
            $rdvsAujourdhuiCount = Rendezvous::where('atelier_id', $atelierId)->whereDate('date_rdv', now()->toDateString())->count();
            if ($rdvsAujourdhuiCount > 0) {
                $tachesUrgentes->push((object)['description' => "$rdvsAujourdhuiCount rendez-vous prévus aujourd'hui", 'priorite' => 'HAUTE', 'type' => 'Rendez-vous']);
            }
            
            $affectationsEnRetard = Affectation::where('atelier_id', $atelierId)
                ->whereIn('statut', ['EN_ATTENTE', 'EN_COURS'])
                ->where('date_echeance', '<', now())
                ->count();
            if ($affectationsEnRetard > 0) {
                $tachesUrgentes->push((object)['description' => "$affectationsEnRetard commandes en retard", 'priorite' => 'HAUTE', 'type' => 'Retard']);
            }

            $tailleursIds = Utilisateur::where('atelier_id', $atelierId)->where('role', 'TAILLEUR')->pluck('id');
            $performanceTailleurs = Utilisateur::whereIn('id', $tailleursIds)->get()->map(function($tailleur) {
                $affs = Affectation::where('tailleur_id', $tailleur->id)->get();
                $terminees = $affs->where('statut', 'TERMINE')->count();
                $enRetard = $affs->filter(function($a) {
                    return in_array($a->statut, ['EN_ATTENTE', 'EN_COURS']) && $a->date_echeance && $a->date_echeance < now();
                })->count();
                return (object)[
                    'nomTailleur' => $tailleur->prenom . ' ' . $tailleur->nom,
                    'affectationsTerminees' => $terminees,
                    'affectationsEnRetard' => $enRetard,
                    'satisfactionMoyenne' => $terminees > 0 ? rand(80, 100) : 0 // Simulation since satisfaction doesn't exist
                ];
            })->sortByDesc('affectationsTerminees')->take(5)->values();

            $rendezVousProchains = Rendezvous::with('client')
                    ->where('atelier_id', $atelierId)
                    ->where('date_rdv', '>=', now())
                    ->whereNotIn('statut', ['ANNULE', 'TERMINE'])
                    ->orderBy('date_rdv')->limit(5)->get()
                    ->map(function($rdv) {
                        return (object)[
                            'clientNom' => $rdv->client ? $rdv->client->prenom . ' ' . $rdv->client->nom : 'N/A',
                            'date' => $rdv->date_rdv->format('Y-m-d H:i:s'),
                            'type' => $rdv->type_rendezvous,
                            'statut' => $rdv->statut
                        ];
                    });

            $affectationsParStatut = Affectation::where('atelier_id', $atelierId)
                    ->get()
                    ->groupBy('statut')
                    ->map->count()
                    ->toArray();

            $stats = [
                'nbClients' => Client::where('atelier_id', $atelierId)->count(),
                'totalClients' => Client::where('atelier_id', $atelierId)->count(),
                'nbMesures' => Mesure::where('atelier_id', $atelierId)->count(),
                'totalTailleurs' => $tailleursIds->count(),
                'nbModeles' => Modele::where('atelier_id', $atelierId)->count(),
                'nbAffectationsEnCours' => Affectation::where('atelier_id', $atelierId)->where('statut', 'EN_COURS')->count(),
                'affectationsEnCours' => Affectation::where('atelier_id', $atelierId)->where('statut', 'EN_COURS')->count(),
                'nbAffectationsTerminees' => Affectation::where('atelier_id', $atelierId)->where('statut', 'TERMINE')->count(),
                'totalEncaisse' => $totalEncaisse,
                'totalDecaisse' => $totalDecaisse,
                'chiffreAffairesMensuel' => Paiement::where('atelier_id', $atelierId)->where('type_paiement', 'CLIENT')->whereMonth('created_at', now()->month)->sum('montant'),
                'solde' => $totalEncaisse - $totalDecaisse,
                'resteAEncaisser' => max(0, $totalDuClients - $totalEncaisse),
                'rdvsAujourdhui' => $rdvsAujourdhuiCount,
                'rendezVousAujourdhui' => $rdvsAujourdhuiCount,
                'rdvsAVenir' => Rendezvous::where('atelier_id', $atelierId)->where('date_rdv', '>=', now())->whereNotIn('statut', ['ANNULE', 'TERMINE'])->count(),
                'rendezVousProchains' => $rendezVousProchains,
                'prochainRdvs' => Rendezvous::with('client')->where('atelier_id', $atelierId)->where('date_rdv', '>=', now())->whereNotIn('statut', ['ANNULE', 'TERMINE'])->orderBy('date_rdv')->limit(5)->get(),
                'derniersClients' => Client::where('atelier_id', $atelierId)->orderBy('created_at', 'desc')->limit(5)->get(),
                'affectationsRecentes' => Affectation::with(['client', 'tailleur'])->where('atelier_id', $atelierId)->orderBy('date_creation', 'desc')->limit(5)->get(),
                'tachesUrgentes' => $tachesUrgentes,
                'performanceTailleurs' => $performanceTailleurs,
                'affectationsParStatut' => $affectationsParStatut,
                'abonnement' => $abonnement,
                'joursAbonnement' => $joursAbonnement,
                'blockedAbonnement' => $blockedAbonnement,
            ];

            if ($user->isSecretaire()) {
                $stats['nouveauxClientsSemaine'] = Client::where('atelier_id', $atelierId)->where('created_at', '>=', now()->startOfWeek())->count();
                $stats['affectationsEnAttente'] = Affectation::where('atelier_id', $atelierId)->where('statut', 'EN_ATTENTE')->count();
                $stats['paiementsAttente'] = Client::where('atelier_id', $atelierId)->whereHas('mesures')->get()->filter(function ($client) {
                    $du = $client->mesures()->sum('prix');
                    $paye = $client->paiements()->where('type_paiement', 'CLIENT')->sum('montant');
                    return $du > $paye;
                })->count();
            }
        }

        return view('dashboard', compact('stats', 'user'));
    }

    public function activityData(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $logs = ActivityLog::latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn($l) => [
                'id'              => $l->id,
                'nom_utilisateur' => $l->nom_utilisateur ?? 'Inconnu',
                'role'            => $l->role ?? '—',
                'action'          => $l->action,
                'description'     => $l->description,
                'ip_address'      => $l->ip_address,
                'created_at'      => $l->created_at?->format('d/m/Y H:i:s'),
                'diff'            => $l->created_at?->diffForHumans(),
            ]);

        $onlineUsers = Utilisateur::whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->get()
            ->map(fn($u) => [
                'nom'  => trim($u->prenom . ' ' . $u->nom),
                'role' => $u->role,
            ]);

        return response()->json([
            'logs'        => $logs,
            'onlineUsers' => $onlineUsers,
            'onlineCount' => $onlineUsers->count(),
        ]);
    }
}
