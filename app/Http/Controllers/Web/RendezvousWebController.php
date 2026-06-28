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
        $query = Rendezvous::with([
            'client.mesures',
            'client.paiements' => fn ($q) => $q->where('type_paiement', 'CLIENT'),
        ])
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
        $clients = Client::where('atelier_id', $user->atelier_id)->with('mesures')->orderBy('nom')->get();
        $statuts = ['PLANIFIE', 'CONFIRME', 'PRET', 'ANNULE', 'TERMINE'];
        $types = ['LIVRAISON', 'RETOUCHE', 'ESSAYAGE', 'AUTRE'];

        return view('rendezvous.index', compact('rendezvous', 'clients', 'statuts', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'       => 'required|uuid|exists:clients,id',
            'date_rdv'        => 'required|date',
            'type_rendezvous' => 'required|string',
            'mesure_id'       => 'nullable|uuid|exists:mesures,id',
        ]);

        $user = Auth::user();
        $rdv = Rendezvous::create([
            'id'              => Str::uuid(),
            'client_id'       => $request->client_id,
            'atelier_id'      => $user->atelier_id,
            'date_rdv'        => $request->date_rdv,
            'type_rendezvous' => $request->type_rendezvous,
            'notes'           => $request->notes,
            'mesure_id'       => $request->mesure_id ?: null,
            'statut'          => 'PLANIFIE',
        ]);

        if ($request->expectsJson()) {
            $client = Client::findOrFail($request->client_id);
            $atelierNom = $user->atelier?->nom ?? 'Atelier';
            $dateRdv = \Carbon\Carbon::parse($request->date_rdv)->format('d/m/Y H:i');
            return response()->json([
                'message' => 'Rendez-vous créé',
                'receipt' => [
                    'typeTicket' => 'RDV',
                    'statut' => 'Rendez-vous planifié',
                    'reference' => 'RDV-' . strtoupper(substr($rdv->id, 0, 8)),
                    'dateFormatted' => now()->format('d/m/Y H:i'),
                    'beneficiaire' => trim(($client->prenom ?? '') . ' ' . ($client->nom ?? '')),
                    'contact' => $client->contact ?? '',
                    'dateRdv' => $dateRdv,
                    'type_rendezvous' => $request->type_rendezvous,
                    'montant' => 0,
                    'totalDu' => 0,
                    'avancePaye' => 0,
                    'resteAPayer' => 0,
                    'atelierNom' => $atelierNom,
                    'messageMarketing' => 'Nous vous attendons chez ' . $atelierNom . '. Merci !',
                ],
            ]);
        }

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

    public function marquerPret(Request $request, $id)
    {
        $user = Auth::user();
        $rdv = Rendezvous::with([
            'client.mesures',
            'client.paiements' => fn ($q) => $q->where('type_paiement', 'CLIENT'),
        ])->where('atelier_id', $user->atelier_id)->findOrFail($id);

        $paiement = $this->resumePaiementClient($rdv->client);
        if (!$paiement['estSolde']) {
            $message = 'Impossible de marquer prêt : le client doit encore payer '
                . number_format($paiement['resteAPayer'], 0, ',', ' ')
                . ' FCFA avant la récupération.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'paiement' => $paiement], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $rdv->update(['statut' => 'PRET']);

        if ($request->expectsJson()) {
            $client = $rdv->client;
            $atelierNom = $user->atelier?->nom ?? 'Atelier';
            $dateRdv = $rdv->date_rdv ? \Carbon\Carbon::parse($rdv->date_rdv)->format('d/m/Y H:i') : null;
            $prenom = $client->prenom ?? '';
            return response()->json([
                'message' => 'Habit marqué comme prêt',
                'receipt' => [
                    'typeTicket'       => 'RDV_READY',
                    'statut'           => 'Habit prêt à récupérer',
                    'reference'        => 'RDV-' . strtoupper(substr($rdv->id, 0, 8)),
                    'dateFormatted'    => now()->format('d/m/Y H:i'),
                    'beneficiaire'     => trim($prenom . ' ' . ($client->nom ?? '')),
                    'contact'          => $client->contact ?? '',
                    'dateRdv'          => $dateRdv,
                    'type_rendezvous'  => $rdv->type_rendezvous,
                    'montant'          => $paiement['montantPaye'],
                    'totalDu'          => $paiement['totalDu'],
                    'avancePaye'       => $paiement['montantPaye'],
                    'resteAPayer'      => $paiement['resteAPayer'],
                    'atelierNom'       => $atelierNom,
                    'messageMarketing' => 'Bonjour ' . $prenom . ', votre habit est prêt. Passez chez ' . $atelierNom . ' pour le récupérer. Merci !',
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Habit marqué comme prêt à récupérer');
    }

    public function changerStatut(Request $request, $id)
    {
        $request->validate(['statut' => 'required|in:PLANIFIE,CONFIRME,ANNULE,TERMINE,PRET']);
        $user = Auth::user();
        $rdv  = Rendezvous::with(['client.mesures', 'client.paiements'])
            ->where('atelier_id', $user->atelier_id)
            ->findOrFail($id);

        // Blocage : on ne peut pas marquer PRET/TERMINE si le client n'a pas soldé
        if (in_array($request->statut, ['PRET', 'TERMINE'], true) && $rdv->client) {
            $paiement = $this->resumePaiementClient($rdv->client);
            if (!$paiement['estSolde']) {
                $resteAPayer = number_format($paiement['resteAPayer'], 0, ',', ' ');
                return redirect()->back()->with(
                    'error',
                    'Impossible : ' . ($rdv->client->prenom ?? '') . ' doit encore payer ' . $resteAPayer . ' FCFA avant la récupération.'
                );
            }
        }

        $rdv->update(['statut' => $request->statut]);
        return redirect()->back()->with('success', 'Statut mis à jour');
    }

    private function resumePaiementClient(?Client $client): array
    {
        if (!$client) {
            return [
                'totalDu' => 0.0,
                'montantPaye' => 0.0,
                'resteAPayer' => 0.0,
                'estSolde' => false,
            ];
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
