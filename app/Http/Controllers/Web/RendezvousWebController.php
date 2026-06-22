<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Rendezvous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RendezvousWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Rendezvous::with('client')
            ->where('atelier_id', $user->atelier_id)
            ->orderBy('date_rdv');

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->has('filter')) {
            match ($request->filter) {
                'aujourd_hui' => $query->whereDate('date_rdv', now()->toDateString()),
                'a_venir' => $query->where('date_rdv', '>=', now())->whereNotIn('statut', ['ANNULE', 'TERMINE']),
                default => null,
            };
        }

        $rendezvous = $query->paginate(20);
        $clients = Client::where('atelier_id', $user->atelier_id)->orderBy('nom')->get();
        $statuts = ['PLANIFIE', 'CONFIRME', 'ANNULE', 'TERMINE'];
        $types = ['LIVRAISON', 'RETOUCHE', 'ESSAYAGE', 'AUTRE'];

        return view('rendezvous.index', compact('rendezvous', 'clients', 'statuts', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'date_rdv' => 'required|date',
            'type_rendezvous' => 'required|string',
        ]);

        $user = Auth::user();
        Rendezvous::create([
            'id' => Str::uuid(),
            'client_id' => $request->client_id,
            'atelier_id' => $user->atelier_id,
            'date_rdv' => $request->date_rdv,
            'type_rendezvous' => $request->type_rendezvous,
            'notes' => $request->notes,
            'statut' => 'PLANIFIE',
        ]);

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous créé');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $rdv = Rendezvous::where('atelier_id', $user->atelier_id)->findOrFail($id);
        $rdv->update([
            'date_rdv' => $request->date_rdv ?? $rdv->date_rdv,
            'type_rendezvous' => $request->type_rendezvous ?? $rdv->type_rendezvous,
            'notes' => $request->notes,
            'statut' => $request->statut ?? $rdv->statut,
        ]);
        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous mis à jour');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        Rendezvous::where('atelier_id', $user->atelier_id)->findOrFail($id)->delete();
        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous supprimé');
    }

    public function changerStatut(Request $request, $id)
    {
        $request->validate(['statut' => 'required|in:PLANIFIE,CONFIRME,ANNULE,TERMINE']);
        $user = Auth::user();
        Rendezvous::where('atelier_id', $user->atelier_id)->findOrFail($id)->update(['statut' => $request->statut]);
        return redirect()->back()->with('success', 'Statut mis à jour');
    }
}
