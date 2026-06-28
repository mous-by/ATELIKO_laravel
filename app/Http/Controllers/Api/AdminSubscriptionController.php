<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use App\Models\AbonnementPlan;
use App\Models\Atelier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function plans(Request $request)
    {
        $this->checkSuperAdmin($request);

        return response()->json(AbonnementPlan::orderBy('duree_mois')->get()->map(fn ($p) => $this->formatPlan($p)));
    }

    public function storePlan(Request $request)
    {
        $this->checkSuperAdmin($request);
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:abonnement_plan,code',
            'libelle' => 'required|string|max:150',
            'duree_mois' => 'required_without:dureeMois|integer|min:1',
            'dureeMois' => 'nullable|integer|min:1',
            'prix' => 'required|numeric|min:0',
            'devise' => 'nullable|string|max:10',
            'actif' => 'nullable|boolean',
        ]);

        $plan = AbonnementPlan::create([
            'code' => strtoupper($data['code']),
            'libelle' => $data['libelle'],
            'duree_mois' => $data['duree_mois'] ?? $data['dureeMois'],
            'prix' => $data['prix'],
            'devise' => $data['devise'] ?? 'XOF',
            'actif' => $data['actif'] ?? true,
        ]);

        return response()->json($this->formatPlan($plan), 201);
    }

    public function updatePlan(Request $request, string $code)
    {
        $this->checkSuperAdmin($request);
        $plan = AbonnementPlan::where('code', $code)->firstOrFail();
        $data = $request->validate([
            'libelle' => 'nullable|string|max:150',
            'duree_mois' => 'nullable|integer|min:1',
            'dureeMois' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'devise' => 'nullable|string|max:10',
            'actif' => 'nullable|boolean',
        ]);

        $plan->fill(array_filter([
            'libelle' => $data['libelle'] ?? null,
            'duree_mois' => $data['duree_mois'] ?? $data['dureeMois'] ?? null,
            'prix' => $data['prix'] ?? null,
            'devise' => $data['devise'] ?? null,
        ], fn ($value) => $value !== null));

        if (array_key_exists('actif', $data)) {
            $plan->actif = (bool) $data['actif'];
        }
        $plan->save();

        return response()->json($this->formatPlan($plan));
    }

    public function destroyPlan(Request $request, string $code)
    {
        $this->checkSuperAdmin($request);
        AbonnementPlan::where('code', $code)->firstOrFail()->delete();

        return response()->json(['message' => 'Plan supprimé']);
    }

    public function ateliers(Request $request)
    {
        $this->checkSuperAdmin($request);

        return response()->json(
            Atelier::with(['abonnement.plan'])
                ->withCount(['utilisateurs', 'clients'])
                ->orderBy('nom')
                ->get()
                ->map(fn ($atelier) => [
                    'id' => $atelier->id,
                    'nom' => $atelier->nom,
                    'telephone' => $atelier->telephone,
                    'email' => $atelier->email,
                    'utilisateursCount' => $atelier->utilisateurs_count,
                    'clientsCount' => $atelier->clients_count,
                    'abonnement' => $this->formatAbonnement($atelier->abonnement),
                ])
        );
    }

    public function payments(Request $request)
    {
        $this->checkSuperAdmin($request);
        $status = $request->query('status');

        return response()->json(
            AbonnementPaiement::with(['abonnement.atelier', 'abonnement.plan', 'reviewer'])
                ->when($status, fn ($q) => $q->where('statut', strtoupper($status)))
                ->orderByDesc('created_at')
                ->limit(200)
                ->get()
                ->map(fn ($p) => $this->formatPayment($p))
        );
    }

    public function activateAtelier(Request $request, string $atelierId)
    {
        $this->checkSuperAdmin($request);
        $plan = AbonnementPlan::where('code', $request->input('planCode', 'MENSUEL'))->firstOrFail();
        $debut = $request->input('startAt') ? Carbon::parse($request->input('startAt')) : now();

        $abonnement = AbonnementAtelier::firstOrNew(['atelier_id' => $atelierId]);
        $abonnement->plan_id = $plan->id;
        $abonnement->statut = 'ACTIVE';
        $abonnement->date_debut = $debut;
        $abonnement->date_fin = $debut->copy()->addMonths($plan->duree_mois);
        $abonnement->save();

        return response()->json(['message' => 'Abonnement activé', 'abonnement' => $this->formatAbonnement($abonnement->load('plan'))]);
    }

    public function suspendAtelier(Request $request, string $atelierId)
    {
        $this->checkSuperAdmin($request);
        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->firstOrFail();
        $abonnement->update(['statut' => 'CANCELED']);

        return response()->json(['message' => 'Abonnement suspendu']);
    }

    public function updateAtelierDates(Request $request, string $atelierId)
    {
        $this->checkSuperAdmin($request);
        $data = $request->validate([
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:dateDebut',
        ]);

        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->firstOrFail();
        $abonnement->update([
            'date_debut' => Carbon::parse($data['dateDebut']),
            'date_fin' => Carbon::parse($data['dateFin']),
        ]);

        return response()->json(['message' => 'Dates mises à jour', 'abonnement' => $this->formatAbonnement($abonnement->fresh('plan'))]);
    }

    public function approvePayment(Request $request, int $paymentId)
    {
        $this->checkSuperAdmin($request);
        $payment = AbonnementPaiement::findOrFail($paymentId);

        if ($payment->statut !== 'PENDING') {
            return response()->json(['message' => 'Seul un paiement PENDING peut être approuvé.'], 400);
        }

        $payment->update([
            'statut' => 'PAID',
            'paid_at' => now(),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $abonnement = AbonnementAtelier::find($payment->abonnement_id);
        if ($abonnement) {
            $plan = AbonnementPlan::where('code', $payment->plan_code)->first();
            $months = $plan?->duree_mois ?? 1;
            $abonnement->statut = 'ACTIVE';
            $abonnement->plan_id = $plan?->id ?? $abonnement->plan_id;
            if (!$abonnement->date_fin || $abonnement->date_fin->isPast()) {
                $abonnement->date_debut = now();
                $abonnement->date_fin = now()->addMonths($months);
            } else {
                $abonnement->date_fin = $abonnement->date_fin->addMonths($months);
            }
            $abonnement->save();
        }

        return response()->json(['message' => 'Paiement validé', 'payment' => $this->formatPayment($payment->fresh())]);
    }

    public function rejectPayment(Request $request, int $paymentId)
    {
        $this->checkSuperAdmin($request);
        $payment = AbonnementPaiement::findOrFail($paymentId);

        if ($payment->statut !== 'PENDING') {
            return response()->json(['message' => 'Seul un paiement PENDING peut être rejeté.'], 400);
        }

        $payment->update([
            'statut' => 'FAILED',
            'review_note' => $request->input('reason'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => 'Paiement rejeté', 'payment' => $this->formatPayment($payment->fresh())]);
    }

    private function checkSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Accès réservé au superadmin');
    }

    private function formatPlan(AbonnementPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'code' => $plan->code,
            'libelle' => $plan->libelle,
            'duree_mois' => $plan->duree_mois,
            'dureeMois' => $plan->duree_mois,
            'prix' => $plan->prix,
            'devise' => $plan->devise,
            'actif' => $plan->actif,
        ];
    }

    private function formatAbonnement(?AbonnementAtelier $abonnement): ?array
    {
        if (!$abonnement) {
            return null;
        }

        return [
            'id' => $abonnement->id,
            'statut' => $abonnement->statut,
            'status' => $abonnement->statut,
            'dateDebut' => $abonnement->date_debut,
            'dateFin' => $abonnement->date_fin,
            'plan' => $abonnement->plan ? $this->formatPlan($abonnement->plan) : null,
        ];
    }

    private function formatPayment(AbonnementPaiement $payment): array
    {
        return [
            'id' => $payment->id,
            'reference' => $payment->reference,
            'montant' => $payment->montant,
            'devise' => $payment->devise,
            'planCode' => $payment->plan_code,
            'statut' => $payment->statut,
            'status' => $payment->statut,
            'modePaiement' => $payment->mode_paiement,
            'transactionRef' => $payment->transaction_ref,
            'preuveUrl' => $payment->preuve_url ? asset('storage/' . $payment->preuve_url) : null,
            'reviewNote' => $payment->review_note,
            'atelier' => $payment->abonnement?->atelier ? [
                'id' => $payment->abonnement->atelier->id,
                'nom' => $payment->abonnement->atelier->nom,
            ] : null,
            'createdAt' => $payment->created_at,
            'reviewedAt' => $payment->reviewed_at,
            'paidAt' => $payment->paid_at,
        ];
    }
}
