<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use App\Models\AbonnementPlan;
use App\Models\Atelier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminSubscriptionWebController extends Controller
{
    private function checkSuperAdmin()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Accès réservé au superadmin');
        }
    }

    public function index(Request $request)
    {
        $this->checkSuperAdmin();
        $tab = $request->get('tab', 'paiements');

        $paiementsEnAttente = AbonnementPaiement::with(['abonnement.atelier', 'abonnement.plan'])
            ->where('statut', 'PENDING')
            ->orderByDesc('created_at')
            ->get();

        $paiementsHistorique = AbonnementPaiement::with(['abonnement.atelier', 'abonnement.plan', 'reviewer'])
            ->whereIn('statut', ['PAID', 'FAILED'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $ateliers = Atelier::with(['abonnement.plan'])
            ->withCount(['utilisateurs', 'clients'])
            ->orderBy('nom')
            ->get();

        $plans = AbonnementPlan::orderBy('duree_mois')->get();

        return view('abonnements.admin', compact(
            'tab', 'paiementsEnAttente', 'paiementsHistorique', 'ateliers', 'plans'
        ));
    }

    public function activateAtelierSubscription(Request $request, $atelierId)
    {
        $this->checkSuperAdmin();
        $planCode = $request->input('planCode', 'MENSUEL');
        $plan = AbonnementPlan::where('code', $planCode)->firstOrFail();

        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->first();
        if (!$abonnement) {
            $abonnement = new AbonnementAtelier();
            $abonnement->atelier_id = $atelierId;
        }

        $debut = $request->input('startAt') ? Carbon::parse($request->input('startAt')) : now();
        $abonnement->plan_id    = $plan->id;
        $abonnement->statut     = 'ACTIVE';
        $abonnement->date_debut = $debut;
        $abonnement->date_fin   = $debut->copy()->addMonths($plan->duree_mois);
        $abonnement->save();

        $message = "Abonnement activé — plan {$plan->libelle} appliqué.";
        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }
        return redirect()->route('admin.subscriptions.index', ['tab' => 'ateliers'])->with('success', $message);
    }

    public function suspendAtelierSubscription(Request $request, $atelierId)
    {
        $this->checkSuperAdmin();
        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->firstOrFail();
        $abonnement->statut = 'CANCELED';
        $abonnement->save();

        $message = 'Abonnement suspendu avec succès.';
        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }
        return redirect()->route('admin.subscriptions.index', ['tab' => 'ateliers'])->with('success', $message);
    }

    public function updateAtelierSubscriptionDates(Request $request, $atelierId)
    {
        $this->checkSuperAdmin();
        $request->validate([
            'dateDebut' => 'required|date',
            'dateFin'   => 'required|date|after:dateDebut',
        ]);

        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->firstOrFail();
        $abonnement->date_debut = Carbon::parse($request->input('dateDebut'));
        $abonnement->date_fin   = Carbon::parse($request->input('dateFin'));
        $abonnement->save();

        $message = 'Dates de l\'abonnement mises à jour.';
        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }
        return redirect()->route('admin.subscriptions.index', ['tab' => 'ateliers'])->with('success', $message);
    }

    public function approveSubscriptionPayment(Request $request, $paymentId)
    {
        $this->checkSuperAdmin();
        $payment = AbonnementPaiement::findOrFail($paymentId);

        if ($payment->statut !== 'PENDING') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Seul un paiement PENDING peut être approuvé.'], 400);
            }
            return redirect()->back()->with('error', 'Seul un paiement PENDING peut être approuvé.');
        }

        $payment->statut      = 'PAID';
        $payment->paid_at     = now();
        $payment->reviewed_by = Auth::id();
        $payment->reviewed_at = now();
        $payment->save();

        $abonnement = AbonnementAtelier::find($payment->abonnement_id);
        if ($abonnement) {
            $plan = AbonnementPlan::where('code', $payment->plan_code)->first();
            $mois = $plan ? $plan->duree_mois : 1;

            $abonnement->statut  = 'ACTIVE';
            $abonnement->plan_id = $plan ? $plan->id : $abonnement->plan_id;

            if (!$abonnement->date_fin || $abonnement->date_fin < now()) {
                $abonnement->date_debut = now();
                $abonnement->date_fin   = now()->addMonths($mois);
            } else {
                $abonnement->date_fin = $abonnement->date_fin->addMonths($mois);
            }
            $abonnement->save();
        }

        $message = 'Paiement validé et abonnement activé avec succès.';
        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }
        return redirect()->route('admin.subscriptions.index', ['tab' => 'paiements'])->with('success', $message);
    }

    public function rejectSubscriptionPayment(Request $request, $paymentId)
    {
        $this->checkSuperAdmin();
        $payment = AbonnementPaiement::findOrFail($paymentId);

        if ($payment->statut !== 'PENDING') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Seul un paiement PENDING peut être rejeté.'], 400);
            }
            return redirect()->back()->with('error', 'Seul un paiement PENDING peut être rejeté.');
        }

        $payment->statut      = 'FAILED';
        $payment->review_note = $request->input('reason');
        $payment->reviewed_by = Auth::id();
        $payment->reviewed_at = now();
        $payment->save();

        $message = 'Paiement rejeté.';
        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }
        return redirect()->route('admin.subscriptions.index', ['tab' => 'paiements'])->with('success', $message);
    }
}
