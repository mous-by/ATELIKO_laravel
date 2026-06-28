<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AffectationWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Affectation::with(['client', 'mesure', 'tailleur'])
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

        $affectations = $query->orderBy('date_creation', 'desc')->paginate(20);
        $statuts = ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'VALIDE', 'ANNULE', 'EN_RETARD'];

        $tailleurs = $user->isTailleur()
            ? collect()
            : Utilisateur::where('atelier_id', $user->atelier_id)->where('role', 'TAILLEUR')->where('actif', true)->get();

        return view('affectations.index', compact('affectations', 'statuts', 'tailleurs'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_if($user->isTailleur(), 403);
        $atelierId = $user->atelier_id;

        $tailleurs = Utilisateur::where('atelier_id', $atelierId)
            ->where('role', 'TAILLEUR')->where('actif', true)->get();

        $clients = Client::where('atelier_id', $atelierId)
            ->with(['mesures' => fn($q) => $q->where('affecte', false)])
            ->get()
            ->filter(fn($c) => $c->mesures->isNotEmpty());

        return view('affectations.create', compact('tailleurs', 'clients'));
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $request->validate([
            'client_id'   => 'required|uuid|exists:clients,id',
            'mesure_ids'  => 'required|array|min:1',
            'mesure_ids.*'=> 'uuid|exists:mesures,id',
            'tailleur_id' => 'required|uuid|exists:utilisateurs,id',
        ]);

        $user = Auth::user();
        $count = 0;
        foreach ($request->mesure_ids as $mesureId) {
            Affectation::create([
                'id'           => Str::uuid(),
                'client_id'    => $request->client_id,
                'mesure_id'    => $mesureId,
                'tailleur_id'  => $request->tailleur_id,
                'atelier_id'   => $user->atelier_id,
                'createur_id'  => $user->id,
                'prix_tailleur'=> $request->prix_tailleur,
                'date_echeance'=> $request->date_echeance,
                'statut'       => 'EN_ATTENTE',
            ]);
            Mesure::where('id', $mesureId)->update(['affecte' => true]);
            $count++;
        }

        $msg = $count === 1 ? 'Affectation créée' : "{$count} affectations créées";
        return redirect()->route('affectations.index')->with('success', $msg);
    }

    public function updateStatut(Request $request, $id)
    {
        $request->validate(['statut' => 'required|in:EN_ATTENTE,EN_COURS,TERMINE,VALIDE,ANNULE']);
        $user = Auth::user();
        $affectation = Affectation::where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($q) => $q->where('tailleur_id', $user->id))
            ->findOrFail($id);

        $updates = ['statut' => $request->statut];
        if ($request->statut === 'EN_COURS') $updates['date_debut_reelle'] = now();
        if ($request->statut === 'TERMINE') $updates['date_fin_reelle'] = now();
        if ($request->statut === 'VALIDE') $updates['date_validation'] = now();
        if ($request->statut === 'ANNULE') Mesure::where('id', $affectation->mesure_id)->update(['affecte' => false]);

        $affectation->update($updates);
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
}
