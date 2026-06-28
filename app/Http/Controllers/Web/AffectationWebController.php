<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Rendezvous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AffectationWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Affectation::with(['client', 'mesure.modeleReference', 'tailleur'])
            ->where('atelier_id', $user->atelier_id);

        if ($user->isTailleur()) {
            $query->where('tailleur_id', $user->id);
        } elseif ($request->filled('tailleur_id')) {
            $query->where('tailleur_id', $request->tailleur_id);
        }

        if ($request->filled('statut')) {
            if ($request->statut === 'EN_RETARD') {
                $query->whereNotIn('statut', ['TERMINE', 'VALIDE', 'ANNULE'])
                      ->where('date_echeance', '<', now());
            } else {
                $query->where('statut', $request->statut);
            }
        }

        $baseStatsQuery = Affectation::where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($q) => $q->where('tailleur_id', $user->id));
        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'enAttente' => (clone $baseStatsQuery)->where('statut', 'EN_ATTENTE')->count(),
            'enCours' => (clone $baseStatsQuery)->where('statut', 'EN_COURS')->count(),
            'terminees' => (clone $baseStatsQuery)->whereIn('statut', ['TERMINE', 'VALIDE'])->count(),
            'retard' => (clone $baseStatsQuery)
                ->whereNotIn('statut', ['TERMINE', 'VALIDE', 'ANNULE'])
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        $affectations = $query->orderBy('date_creation', 'desc')->paginate(20);
        $statuts = ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'VALIDE', 'ANNULE', 'EN_RETARD'];

        $tailleurs = $user->isTailleur()
            ? collect()
            : Utilisateur::where('atelier_id', $user->atelier_id)->where('role', 'TAILLEUR')->where('actif', true)->get();

        return view('affectations.index', compact('affectations', 'statuts', 'tailleurs', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_if($user->isTailleur(), 403);
        $atelierId = $user->atelier_id;

        $tailleurs = Utilisateur::where('atelier_id', $atelierId)
            ->where('role', 'TAILLEUR')->where('actif', true)->get();

        $clients = Client::where('atelier_id', $atelierId)
            ->with(['mesures' => fn($q) => $q->where('affecte', false)->with('modeleReference')])
            ->get()
            ->filter(fn($c) => $c->mesures->isNotEmpty());

        return view('affectations.create', compact('tailleurs', 'clients'));
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $request->validate([
            'selected_mesures' => 'required|array|min:1',
            'selected_mesures.*' => 'uuid|exists:mesures,id',
            'tailleur_id' => 'required|uuid|exists:utilisateurs,id',
        ]);

        $user = Auth::user();
        $mesures = Mesure::where('atelier_id', $user->atelier_id)
            ->whereIn('id', $request->selected_mesures)
            ->where('affecte', false)
            ->get();

        if ($mesures->count() !== count($request->selected_mesures)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Certaines mesures sont déjà affectées ou introuvables. Veuillez recommencer.');
        }

        $count = 0;
        foreach ($mesures as $mesure) {
            Affectation::create([
                'id'           => Str::uuid(),
                'client_id'    => $mesure->client_id,
                'mesure_id'    => $mesure->id,
                'tailleur_id'  => $request->tailleur_id,
                'atelier_id'   => $user->atelier_id,
                'createur_id'  => $user->id,
                'prix_tailleur'=> $request->prix_tailleur,
                'date_echeance'=> $request->date_echeance,
                'statut'       => 'EN_ATTENTE',
            ]);
            $mesure->update(['affecte' => true]);
            $count++;
        }

        $clientCount = $mesures->pluck('client_id')->unique()->count();
        $msg = $count === 1
            ? '1 modèle affecté au tailleur'
            : "{$count} modèles affectés au tailleur pour {$clientCount} client(s)";
        return redirect()->route('affectations.index')->with('success', $msg);
    }

    public function updateStatut(Request $request, $id)
    {
        $request->validate(['statut' => 'required|in:EN_ATTENTE,EN_COURS,TERMINE,VALIDE,ANNULE']);
        $user = Auth::user();
        $affectation = Affectation::with([
                'client.mesures',
                'client.paiements' => fn ($q) => $q->where('type_paiement', 'CLIENT'),
                'mesure.modeleReference',
            ])
            ->where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($q) => $q->where('tailleur_id', $user->id))
            ->findOrFail($id);

        $updates = ['statut' => $request->statut];
        if ($request->statut === 'EN_COURS') $updates['date_debut_reelle'] = now();
        if ($request->statut === 'TERMINE') $updates['date_fin_reelle'] = now();
        if ($request->statut === 'VALIDE') $updates['date_validation'] = now();
        if ($request->statut === 'ANNULE') Mesure::where('id', $affectation->mesure_id)->update(['affecte' => false]);

        $affectation->update($updates);

        if ($request->expectsJson() && $request->statut === 'TERMINE') {
            $affectation->refresh()->loadMissing([
                'client.mesures',
                'client.paiements',
                'mesure.modeleReference',
            ]);

            $paiement = $this->resumePaiementClient($affectation->client);
            $rdv = $this->findOrMarkReadyRendezvous($affectation, $paiement['estSolde']);

            return response()->json([
                'message' => $paiement['estSolde']
                    ? 'Habit terminé. Rendez-vous de récupération prêt.'
                    : 'Habit terminé. Le rendez-vous reste en attente du paiement du solde.',
                'receipt' => $this->buildReadyReceipt($affectation, $rdv, $user),
            ]);
        }

        return redirect()->back()->with('success', 'Statut mis à jour');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        abort_if($user->isTailleur(), 403);
        $affectation = Affectation::where('atelier_id', $user->atelier_id)->findOrFail($id);
        Mesure::where('id', $affectation->mesure_id)->update(['affecte' => false]);
        $affectation->delete();
        return redirect()->route('affectations.index')->with('success', 'Affectation supprimée');
    }

    private function findOrMarkReadyRendezvous(Affectation $affectation, bool $isSolde): ?Rendezvous
    {
        $query = Rendezvous::where('atelier_id', $affectation->atelier_id)
            ->where('client_id', $affectation->client_id)
            ->whereIn('statut', ['PLANIFIE', 'CONFIRME', 'PRET'])
            ->orderByRaw("CASE WHEN mesure_id = ? THEN 0 ELSE 1 END", [$affectation->mesure_id])
            ->orderBy('date_rdv');

        $rdv = $query->first();

        if ($rdv && $isSolde && $rdv->statut !== 'PRET') {
            $updates = ['statut' => 'PRET'];
            if ($rdv->date_rdv && $rdv->date_rdv->lt(now())) {
                $updates['date_rdv'] = now();
            }
            $rdv->update($updates);
            $rdv->refresh();
        }

        return $rdv;
    }

    private function buildReadyReceipt(Affectation $affectation, ?Rendezvous $rdv, $user): array
    {
        $client = $affectation->client;
        $mesure = $affectation->mesure;
        $paiement = $this->resumePaiementClient($client);
        $isSolde = $paiement['estSolde'];
        $atelierNom = $user->atelier?->nom ?? 'Atelier';
        $prenom = $client->prenom ?? '';
        $nomModele = $mesure?->modele_nom
            ?: ($mesure?->modeleReference?->nom ?: ($mesure?->type_vetement ?: 'Habit client'));

        return [
            'typeTicket'       => 'RDV_READY',
            'autoWhatsApp'     => true,
            'statut'           => $isSolde ? 'Habit prêt à récupérer' : 'Habit prêt - solde à régler',
            'reference'        => $rdv ? 'RDV-' . strtoupper(substr($rdv->id, 0, 8)) : 'AFF-' . strtoupper(substr($affectation->id, 0, 8)),
            'dateFormatted'    => now()->format('d/m/Y H:i'),
            'beneficiaire'     => trim($prenom . ' ' . ($client->nom ?? '')),
            'contact'          => $client->contact ?? '',
            'dateRdv'          => $isSolde
                ? ($rdv?->date_rdv ? $rdv->date_rdv->format('d/m/Y H:i') : ($affectation->date_echeance?->format('d/m/Y H:i')))
                : 'Après règlement du solde',
            'type_rendezvous'  => $rdv?->type_rendezvous ?: 'LIVRAISON',
            'nomModele'        => $nomModele,
            'montant'          => 0,
            'totalDu'          => $paiement['totalDu'],
            'avancePaye'       => $paiement['montantPaye'],
            'resteAPayer'      => $paiement['resteAPayer'],
            'atelierNom'       => $atelierNom,
            'readyMessage'     => $isSolde
                ? 'Votre commande est prête. Vous pouvez passer la récupérer chez ' . $atelierNom . '.'
                : 'Votre habit est prêt, mais le rendez-vous de récupération sera effectif après règlement du solde de '
                    . number_format($paiement['resteAPayer'], 0, ',', ' ') . ' FCFA.',
            'messageMarketing' => $isSolde
                ? 'Bonjour ' . $prenom . ', votre habit est prêt. Passez chez ' . $atelierNom . ' pour le récupérer. Merci !'
                : 'Bonjour ' . $prenom . ', votre habit est prêt. Merci de régler le solde avant la récupération chez ' . $atelierNom . '.',
        ];
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
