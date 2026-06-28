<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mesure;
use App\Models\Modele;
use App\Models\Paiement;
use App\Models\Rendezvous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $this->clientQueryForUser($user)
            ->with(['mesures' => fn ($query) => $this->mesuresQueryForUser($query, $user)->orderByDesc('date_mesure')->orderByDesc('created_at')])
            ->orderBy('nom')
            ->orderBy('prenom');

        if ($request->has('search') && $request->search) {
            $term = $request->search;
            $query->where(fn ($q) => $q->where('nom', 'like', "%$term%")
                ->orWhere('prenom', 'like', "%$term%")
                ->orWhere('contact', 'like', "%$term%"));
        }

        $clients = $query->get();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $atelierId = Auth::user()->atelier_id;
        $modeles = Modele::where('atelier_id', $atelierId)->where('est_actif', true)->get();
        $clientsExistants = Client::where('atelier_id', $atelierId)
            ->with(['mesures' => fn ($query) => $query->orderByDesc('date_mesure')->orderByDesc('created_at')])
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();
        $syntheseMensuelle = $clientsExistants->flatMap->mesures
            ->filter(fn ($mesure) => $mesure->date_mesure)
            ->groupBy(fn ($mesure) => $mesure->date_mesure->format('Y-m'))
            ->map(fn ($mesures, $mois) => [
                'mois' => $mois,
                'libelle' => ucfirst(now()->createFromFormat('Y-m', $mois)->locale('fr')->translatedFormat('F Y')),
                'entrees' => $mesures->count(),
            ])->sortByDesc('mois')->values();

        return view('clients.create', compact('modeles', 'clientsExistants', 'syntheseMensuelle'));
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $request->validate([
            'existing_client_id' => 'nullable|uuid',
            'nom' => 'required_without:existing_client_id|nullable|string|max:100',
            'prenom' => 'required_without:existing_client_id|nullable|string|max:100',
            'contact' => 'nullable|string|max:20',
            'sexe' => 'required|string|in:Femme,Homme',
            'mesures_json' => 'required|json',
            'avance' => 'nullable|numeric|min:0',
            'avance_moyen' => 'nullable|in:ESPECES,MOBILE_MONEY,VIREMENT,CARTE',
            'model_photos.*' => 'nullable|image|max:10240',
            'habit_photos.*' => 'nullable|image|max:10240',
        ]);

        $vetements = collect(json_decode($request->mesures_json, true));

        if ($vetements->isEmpty()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Ajoutez au moins un vêtement.'], 422)
                : back()->withErrors(['vetements' => 'Ajoutez au moins un vêtement.']);
        }

        $totalNouvelleCommande = (float) $vetements->sum(fn ($v) => (float) ($v['prix'] ?? 0));
        $avanceDemandee = (float) ($request->avance ?? 0);
        if ($avanceDemandee > $totalNouvelleCommande) {
            $message = 'Montant trop élevé : l’avance ne peut pas dépasser le total de la commande ('
                . number_format($totalNouvelleCommande, 0, ',', ' ')
                . ' FCFA).';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withInput()->withErrors(['avance' => $message]);
        }

        $user = Auth::user();

        try {
            $client = DB::transaction(function () use ($request, $user, $vetements) {
                if ($request->filled('existing_client_id')) {
                    $client = Client::where('atelier_id', $user->atelier_id)
                        ->findOrFail($request->existing_client_id);
                } else {
                    $client = Client::create([
                        'id' => Str::uuid(),
                        'nom' => $request->nom,
                        'prenom' => $request->prenom,
                        'contact' => $request->contact,
                        'email' => $request->email,
                        'adresse' => $request->adresse,
                        'sexe' => $request->sexe,
                        'atelier_id' => $user->atelier_id,
                    ]);
                }

                // RDV livraison automatique
                Rendezvous::create([
                    'id' => Str::uuid(),
                    'client_id' => $client->id,
                    'atelier_id' => $user->atelier_id,
                    'date_rdv' => now()->addDays(7),
                    'type_rendezvous' => 'LIVRAISON',
                    'notes' => 'Rendez-vous de livraison automatique',
                    'statut' => 'PLANIFIE',
                ]);

                $modelPhotos = $request->file('model_photos', []);
                $habitPhotos = $request->file('habit_photos', []);
                foreach ($vetements as $index => $vetement) {
                    $type = strtoupper($vetement['typeVetement'] ?? '');
                    if (!in_array($type, ['ROBE', 'JUPE', 'HOMME'], true)) {
                        throw new \InvalidArgumentException("Type de vêtement invalide : $type");
                    }
                    $measureData = $this->mapVetementToMesure($vetement) + [
                        'id' => Str::uuid(),
                        'client_id' => $client->id,
                        'atelier_id' => $user->atelier_id,
                        'date_mesure' => now()->toDateString(),
                        'affecte' => false,
                    ];
                    if (isset($modelPhotos[$index])) {
                        $measureData['photo_path'] = $modelPhotos[$index]->store('mesure_photo', 'public');
                    }
                    if (isset($habitPhotos[$index])) {
                        $measureData['habit_photo_path'] = $habitPhotos[$index]->store('habit_photo', 'public');
                    }
                    Mesure::create($measureData);
                }

                if ($request->filled('avance') && (float) $request->avance > 0) {
                    Paiement::create([
                        'id' => Str::uuid(), 'montant' => $request->avance,
                        'moyen' => $request->avance_moyen ?: 'ESPECES', 'type_paiement' => 'CLIENT',
                        'client_id' => $client->id, 'atelier_id' => $user->atelier_id,
                        'note' => 'Avance à la création de la mesure',
                    ]);
                }

                return $client;
            });
        } catch (\Throwable $e) {
            \Log::error('Erreur enregistrement client/mesure: ' . $e->getMessage(), ['exception' => $e]);
            return $request->expectsJson()
                ? response()->json(['message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()], 500)
                : back()->withErrors(['general' => $e->getMessage()]);
        }

        $message = $request->filled('existing_client_id')
            ? 'Nouveaux vêtements ajoutés au client'
            : 'Client et vêtements créés avec succès';

        if ($request->expectsJson()) {
            $totalDu = collect($vetements)->sum(fn($v) => (float)($v['prix'] ?? 0));
            $avancePaye = ($request->filled('avance') && (float)$request->avance > 0) ? (float)$request->avance : 0;
            $atelierNom = $user->atelier?->nom ?? 'Atelier';
            return response()->json([
                'message' => $message,
                'clientId' => $client->id,
                'redirect' => route('clients.show', $client->id),
                'receipt' => [
                    'typeTicket' => 'COMMANDE',
                    'autoWhatsApp' => true,
                    'statut' => 'Reçu client',
                    'reference' => 'CMD-' . strtoupper(substr($client->id, 0, 8)),
                    'dateFormatted' => now()->format('d/m/Y H:i'),
                    'beneficiaire' => trim(($client->prenom ?? '') . ' ' . ($client->nom ?? '')),
                    'contact' => $client->contact ?? '',
                    'montant' => $avancePaye,
                    'totalDu' => $totalDu,
                    'avancePaye' => $avancePaye,
                    'resteAPayer' => max(0, $totalDu - $avancePaye),
                    'atelierNom' => $atelierNom,
                    'messageMarketing' => 'Merci pour votre confiance en ' . $atelierNom . ' !',
                ],
            ]);
        }

        return redirect()->route('clients.show', $client->id)->with('success', $message);
    }

    private function mapVetementToMesure(array $vetement): array
    {
        $type = strtoupper($vetement['typeVetement'] ?? '');
        $prefix = $type === 'HOMME' ? 'homme' : strtolower($type);
        // Convert empty strings to null to avoid MySQL decimal column rejection
        $value = fn (string $name) => blank($vetement[$prefix.'_'.$name] ?? null) ? null : $vetement[$prefix.'_'.$name];

        return [
            'type_vetement' => $type,
            'sexe' => $vetement['sexe'] ?? ($type === 'HOMME' ? 'Homme' : 'Femme'),
            'prix' => $vetement['prix'] ?: null,
            'description' => $vetement['description'] ?? null,
            'modele_reference_id' => $vetement['selectedModelId'] ?: null,
            'modele_nom' => $vetement['modeleNom'] ?? null,
            'photo_path' => $vetement['existing_photo_path'] ?? null,
            'habit_photo_path' => $vetement['existing_habit_photo_path'] ?? null,
            'epaule' => $value('epaule'),
            'manche' => $value('manche'),
            'poitrine' => $value('poitrine'),
            'taille' => $value('taille'),
            'longueur' => $value('longueur'),
            'fesse' => $value('fesse'),
            'tour_manche' => $value('tour_manche'),
            'longueur_poitrine' => $value('longueur_poitrine'),
            'longueur_taille' => $value('longueur_taille'),
            'longueur_fesse' => $value('longueur_fesse'),
            'longueur_jupe' => $value('longueur_jupe'),
            'ceinture' => $value('ceinture'),
            'longueur_pantalon' => $value('longueur_pantalon'),
            'cuisse' => $value('cuisse'),
            'corps' => $value('corps'),
        ];
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $client = Client::with([
            'mesures' => fn ($q) => $this->mesuresQueryForUser($q, $user)->orderByDesc('date_mesure')->orderByDesc('created_at'),
            'mesures.modeleReference',
            'affectations' => fn ($q) => $user->isTailleur() ? $q->where('tailleur_id', $user->id) : $q,
            'affectations.tailleur',
            'paiements',
            'rendezvous',
        ])->where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($q) => $q->whereHas('affectations', fn ($a) => $a->where('tailleur_id', $user->id)))
            ->findOrFail($id);

        if ($request->expectsJson()) {
            if ($user->isTailleur()) {
                $client->unsetRelation('paiements');
                $client->unsetRelation('rendezvous');
                $client->mesures->each(function ($mesure) {
                    $mesure->makeHidden(['prix']);
                    unset($mesure->prix);
                });
            }
            return response()->json(['client' => $client]);
        }

        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $montantRestant = max(0, $montantTotal - $montantPaye);

        $modeles = Modele::where('atelier_id', $user->atelier_id)->where('est_actif', true)->get();

        return view('clients.show', compact('client', 'montantTotal', 'montantPaye', 'montantRestant', 'modeles'));
    }

    public function edit($id)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        $client->fill($request->only(['nom', 'prenom', 'contact', 'sexe']));
        $client->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Client mis à jour']);
        }

        return redirect()->route('clients.show', $id)->with('success', 'Client mis à jour');
    }

    public function destroy(Request $request, $id)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }
        $client->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Client supprimé']);
        }

        return redirect()->route('clients.index')->with('success', 'Client supprimé');
    }

    public function ajouterMesure(Request $request, $clientId)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $user = Auth::user();
        $client = Client::where('atelier_id', $user->atelier_id)->findOrFail($clientId);

        $data = $request->except(['photo', 'habitPhoto', 'audio', '_token']);
        $data['id'] = Str::uuid();
        $data['client_id'] = $clientId;
        $data['atelier_id'] = $user->atelier_id;
        $data['date_mesure'] = $request->date_mesure ?? now()->toDateString();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('mesure_photo', 'public');
        }
        if ($request->hasFile('habitPhoto')) {
            $data['habit_photo_path'] = $request->file('habitPhoto')->store('habit_photo', 'public');
        }

        Mesure::create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Nouveau modèle ajouté avec succès']);
        }

        return redirect()->route('clients.show', $clientId)->with('success', 'Mesure ajoutée');
    }

    public function modifierMesure(Request $request, $clientId, $mesureId)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($clientId);
        $mesure = Mesure::where('client_id', $client->id)->findOrFail($mesureId);
        $data = $request->only([
            'sexe', 'type_vetement', 'prix', 'description', 'modele_nom',
            'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse',
            'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
            'longueur_jupe', 'ceinture', 'longueur_pantalon', 'cuisse', 'corps',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('mesure_photo', 'public');
        }
        if ($request->hasFile('habitPhoto')) {
            $data['habit_photo_path'] = $request->file('habitPhoto')->store('habit_photo', 'public');
        }

        $mesure->update($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Modèle modifié avec succès']);
        }

        return redirect()->route('clients.show', $clientId)->with('success', 'Mesure modifiée avec succès');
    }

    public function supprimerMesure($clientId, $mesureId)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $client = Client::where('atelier_id', Auth::user()->atelier_id)->findOrFail($clientId);
        $mesure = Mesure::where('client_id', $client->id)->findOrFail($mesureId);
        $mesure->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Modèle supprimé']);
        }

        return redirect()->route('clients.show', $clientId)->with('success', 'Mesure supprimée');
    }

    public function ajouterPaiement(Request $request, $clientId)
    {
        abort_if(Auth::user()->isTailleur(), 403);
        $request->validate(['montant' => 'required|numeric|min:0.01']);
        $user = Auth::user();
        $client = Client::with(['mesures', 'paiements'])
            ->where('atelier_id', $user->atelier_id)
            ->findOrFail($clientId);

        $totalDu = (float) $client->mesures->sum('prix');
        $dejaPaye = (float) $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $resteAPayer = max(0, $totalDu - $dejaPaye);
        $montant = (float) $request->montant;

        if ($totalDu <= 0 || $resteAPayer <= 0) {
            return redirect()->back()->withInput()->with('error', 'Ce client a déjà soldé. Aucun paiement en plus n’est autorisé.');
        }

        if ($montant > $resteAPayer) {
            return redirect()->back()->withInput()->with(
                'error',
                'Montant trop élevé : il reste seulement ' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA à payer.'
            );
        }

        Paiement::create([
            'id' => Str::uuid(),
            'montant' => $montant,
            'moyen' => $request->moyen ?? 'ESPECES',
            'type_paiement' => 'CLIENT',
            'client_id' => $client->id,
            'atelier_id' => $user->atelier_id,
            'note' => $request->note,
        ]);

        return redirect()->route('clients.show', $clientId)->with('success', 'Paiement enregistré');
    }

    public function recu($clientId)
    {
        $user = Auth::user();
        $client = $this->clientQueryForUser($user)
            ->with(['mesures' => fn ($q) => $this->mesuresQueryForUser($q, $user), 'paiements'])
            ->findOrFail($clientId);
        $montantTotal = $client->mesures->sum('prix');
        $montantPaye = $client->paiements->where('type_paiement', 'CLIENT')->sum('montant');
        $atelier = $client->atelier;

        return view('clients.recu', compact('client', 'montantTotal', 'montantPaye', 'atelier'));
    }

    private function clientQueryForUser($user)
    {
        return Client::where('atelier_id', $user->atelier_id)
            ->when($user->isTailleur(), fn ($query) => $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id)));
    }

    private function mesuresQueryForUser($query, $user)
    {
        return $user->isTailleur()
            ? $query->whereHas('affectations', fn ($q) => $q->where('tailleur_id', $user->id))
            : $query;
    }
}
