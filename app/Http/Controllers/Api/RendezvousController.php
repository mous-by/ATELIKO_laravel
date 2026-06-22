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
            'atelierId' => 'required|uuid|exists:ateliers,id',
            'dateRDV' => 'required|date',
            'typeRendezVous' => 'required|string',
        ]);

        $rdv = Rendezvous::create([
            'id' => Str::uuid(),
            'client_id' => $request->clientId,
            'atelier_id' => $request->atelierId ?? $request->user()->atelier_id,
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
        $rdv = Rendezvous::findOrFail($id);
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
        $rdv = Rendezvous::with('client')->findOrFail($id);
        return response()->json($this->format($rdv));
    }

    public function destroy($id)
    {
        Rendezvous::findOrFail($id)->delete();
        return response()->noContent();
    }

    public function clientsParAtelier($atelierId)
    {
        $clients = Client::where('atelier_id', $atelierId)->get()->map(fn($c) => [
            'id' => $c->id,
            'nom' => $c->nom,
            'prenom' => $c->prenom,
            'contact' => $c->contact,
        ]);
        return response()->json($clients);
    }

    public function aVenir($atelierId)
    {
        $rdvs = Rendezvous::with('client')
            ->where('atelier_id', $atelierId)
            ->where('date_rdv', '>=', now())
            ->whereNotIn('statut', ['ANNULE', 'TERMINE'])
            ->orderBy('date_rdv')
            ->get();
        return response()->json($rdvs->map(fn($r) => $this->format($r)));
    }

    public function aujourdhui($atelierId)
    {
        $rdvs = Rendezvous::with('client')
            ->where('atelier_id', $atelierId)
            ->whereDate('date_rdv', now()->toDateString())
            ->orderBy('date_rdv')
            ->get();
        return response()->json($rdvs->map(fn($r) => $this->format($r)));
    }

    public function confirmer($id)
    {
        $rdv = Rendezvous::findOrFail($id);
        $rdv->update(['statut' => 'CONFIRME']);
        return response()->json($this->format($rdv->load('client')));
    }

    public function annuler($id)
    {
        $rdv = Rendezvous::findOrFail($id);
        $rdv->update(['statut' => 'ANNULE']);
        return response()->json($this->format($rdv->load('client')));
    }

    public function terminer($id)
    {
        $rdv = Rendezvous::findOrFail($id);
        $rdv->update(['statut' => 'TERMINE']);
        return response()->json($this->format($rdv->load('client')));
    }

    public function clientDetails($clientId)
    {
        $client = Client::with(['mesures', 'rendezvous' => fn($q) => $q->orderBy('date_rdv')])->findOrFail($clientId);
        return response()->json([
            'id' => $client->id,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'contact' => $client->contact,
            'mesures' => $client->mesures->map(fn($m) => [
                'id' => $m->id,
                'typeVetement' => $m->type_vetement,
                'prix' => $m->prix,
            ]),
            'rendezvous' => $client->rendezvous->map(fn($r) => $this->format($r)),
        ]);
    }

    private function format(Rendezvous $r): array
    {
        return [
            'id' => $r->id,
            'dateRDV' => $r->date_rdv,
            'typeRendezVous' => $r->type_rendezvous,
            'notes' => $r->notes,
            'statut' => $r->statut,
            'atelierId' => $r->atelier_id,
            'mesureId' => $r->mesure_id,
            'client' => $r->relationLoaded('client') && $r->client ? [
                'id' => $r->client->id,
                'nom' => $r->client->nom,
                'prenom' => $r->client->prenom,
                'contact' => $r->client->contact,
            ] : null,
            'createdAt' => $r->created_at,
            'updatedAt' => $r->updated_at,
        ];
    }
}
