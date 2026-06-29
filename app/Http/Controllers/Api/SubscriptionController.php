<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use App\Models\AbonnementPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function current(Request $request)
    {
        $user = $request->user();
        $abonnement = $user->atelier_id
            ? AbonnementAtelier::with('plan')->where('atelier_id', $user->atelier_id)->latest()->first()
            : null;

        return response()->json($this->formatAbonnement($abonnement));
    }

    public function plans()
    {
        return response()->json(AbonnementPlan::where('actif', true)->orderBy('duree_mois')->get()->map(fn ($p) => $this->formatPlan($p)));
    }

    public function payments(Request $request)
    {
        $abonnement = AbonnementAtelier::where('atelier_id', $request->user()->atelier_id)->latest()->first();

        if (!$abonnement) {
            return response()->json([]);
        }

        return response()->json(
            AbonnementPaiement::where('abonnement_id', $abonnement->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($p) => $this->formatPayment($p))
        );
    }

    public function submitManualPayment(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isProprietaire(), 403);

        $request->validate([
            'plan_code' => 'required|string|exists:abonnement_plan,code',
            'mode_paiement' => 'required|in:ORANGE_MONEY,WAVE,MOBICASH',
            'preuve' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $plan = AbonnementPlan::where('code', $request->plan_code)->where('actif', true)->firstOrFail();
        $abonnement = AbonnementAtelier::firstOrCreate(
            ['atelier_id' => $user->atelier_id],
            [
                'plan_id' => $plan->id,
                'statut' => 'EXPIRED',
                'date_debut' => now(),
                'date_fin' => now(),
            ]
        );

        $preuveUrl = $request->file('preuve')->store('subscription_receipts', 'public');
        $payment = AbonnementPaiement::create([
            'abonnement_id' => $abonnement->id,
            'reference' => 'SUB-' . strtoupper(substr(str_replace('-', '', $user->atelier_id), 0, 8)) . '-' . strtoupper(Str::random(12)),
            'montant' => $plan->prix,
            'devise' => $plan->devise,
            'plan_code' => $plan->code,
            'statut' => 'PENDING',
            'provider' => 'MANUEL',
            'mode_paiement' => $request->mode_paiement,
            'transaction_ref' => $request->transaction_ref,
            'owner_note' => $request->owner_note,
            'preuve_url' => $preuveUrl,
        ]);

        return response()->json([
            'message' => 'Paiement soumis avec succès. En attente de validation.',
            'payment' => $this->formatPayment($payment),
        ], 201);
    }

    private function formatAbonnement(?AbonnementAtelier $abonnement): array
    {
        if (!$abonnement) {
            return [
                'statut' => 'EXPIRED',
                'status' => 'EXPIRED',
                'isActive' => false,
                'joursRestants' => 0,
            ];
        }

        $blocked = in_array($abonnement->statut, ['EXPIRED', 'CANCELED', 'PAST_DUE'], true)
            || ($abonnement->date_fin && $abonnement->date_fin->isPast());
        $joursRestants = $abonnement->date_fin ? (int) now()->diffInDays($abonnement->date_fin, false) : null;

        return [
            'id' => $abonnement->id,
            'atelierId' => $abonnement->atelier_id,
            'statut' => $abonnement->statut,
            'status' => $abonnement->statut,
            'isActive' => !$blocked,
            'blocked' => $blocked,
            'dateDebut' => $abonnement->date_debut,
            'dateFin' => $abonnement->date_fin,
            'joursRestants' => $joursRestants,
            'plan' => $abonnement->plan ? $this->formatPlan($abonnement->plan) : null,
        ];
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
            'preuveUrl' => $payment->preuve_url ? asset($payment->preuve_url) : null,
            'reviewNote' => $payment->review_note,
            'createdAt' => $payment->created_at,
            'reviewedAt' => $payment->reviewed_at,
            'paidAt' => $payment->paid_at,
        ];
    }
}
