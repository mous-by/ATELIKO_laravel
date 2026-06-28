<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use App\Models\AbonnementPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AbonnementWebController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isProprietaire() || $user->isSecretaire(), 403);

        $atelierId = $user->atelier_id;
        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)
            ->with('plan')
            ->latest()
            ->first();

        $joursRestants = null;
        $blocked       = false;
        $expireBientot = false;

        if ($abonnement && $abonnement->date_fin) {
            $joursRestants = (int) now()->diffInDays($abonnement->date_fin, false);
            $blocked       = $joursRestants < 0 || in_array($abonnement->statut, ['EXPIRED', 'CANCELED', 'PAST_DUE']);
            $expireBientot = !$blocked && $joursRestants <= 7;
        }

        $plans    = AbonnementPlan::where('actif', true)->orderBy('duree_mois')->get();
        $paiements = $abonnement
            ? AbonnementPaiement::where('abonnement_id', $abonnement->id)->orderByDesc('created_at')->get()
            : collect();

        $moyensPaiement = [
            'ORANGE_MONEY' => ['label' => 'Orange Money', 'numero' => '74 74 56 69'],
            'WAVE'         => ['label' => 'Wave',         'numero' => '74 74 56 69'],
            'MOBICASH'     => ['label' => 'MobiCash',     'numero' => '67 20 57 36'],
        ];

        return view('abonnements.index', compact(
            'abonnement', 'joursRestants', 'blocked', 'expireBientot',
            'plans', 'paiements', 'moyensPaiement', 'user'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isProprietaire(), 403);

        $request->validate([
            'plan_code'     => 'required|string|exists:abonnement_plan,code',
            'mode_paiement' => 'required|in:ORANGE_MONEY,WAVE,MOBICASH',
            'preuve'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $plan      = AbonnementPlan::where('code', $request->plan_code)->where('actif', true)->firstOrFail();
        $atelierId = $user->atelier_id;

        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->first();
        if (!$abonnement) {
            $abonnement = AbonnementAtelier::create([
                'atelier_id' => $atelierId,
                'plan_id'    => $plan->id,
                'statut'     => 'EXPIRED',
                'date_debut' => now(),
                'date_fin'   => now(),
            ]);
        }

        $preuveUrl = $request->file('preuve')->store('subscription_receipts', 'public');
        $reference = 'SUB-' . strtoupper(substr(str_replace('-', '', $atelierId), 0, 8)) . '-' . strtoupper(Str::random(12));

        AbonnementPaiement::create([
            'abonnement_id'  => $abonnement->id,
            'reference'      => $reference,
            'montant'        => $plan->prix,
            'devise'         => $plan->devise,
            'plan_code'      => $plan->code,
            'statut'         => 'PENDING',
            'provider'       => 'MANUEL',
            'mode_paiement'  => $request->mode_paiement,
            'transaction_ref'=> $request->transaction_ref,
            'owner_note'     => $request->owner_note,
            'preuve_url'     => $preuveUrl,
        ]);

        return redirect()->route('abonnement.pending')
            ->with('success', 'Paiement soumis avec succès. En attente de validation par l\'administrateur.');
    }

    public function storeFromLogin(Request $request)
    {
        $blockedLogin = $request->session()->get('ateliko_blocked_login');
        if (!$blockedLogin || ($blockedLogin['role'] ?? '') !== 'PROPRIETAIRE') {
            return redirect()->route('login')->withErrors(['telephone' => 'Session invalide. Veuillez vous reconnecter.']);
        }

        $request->validate([
            'plan_code'     => 'required|string|exists:abonnement_plan,code',
            'mode_paiement' => 'required|in:ORANGE_MONEY,WAVE,MOBICASH',
            'preuve'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $atelierId = $blockedLogin['atelier_id'];
        $userId    = $blockedLogin['user_id'];
        $plan      = AbonnementPlan::where('code', $request->plan_code)->where('actif', true)->firstOrFail();

        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->first();
        if (!$abonnement) {
            $abonnement = AbonnementAtelier::create([
                'atelier_id' => $atelierId,
                'plan_id'    => $plan->id,
                'statut'     => 'EXPIRED',
                'date_debut' => now(),
                'date_fin'   => now(),
            ]);
        }

        $preuveUrl = $request->file('preuve')->store('subscription_receipts', 'public');
        $reference = 'SUB-' . strtoupper(substr(str_replace('-', '', $atelierId), 0, 8)) . '-' . strtoupper(\Illuminate\Support\Str::random(12));

        AbonnementPaiement::create([
            'abonnement_id'   => $abonnement->id,
            'reference'       => $reference,
            'montant'         => $plan->prix,
            'devise'          => $plan->devise,
            'plan_code'       => $plan->code,
            'statut'          => 'PENDING',
            'provider'        => 'MANUEL',
            'mode_paiement'   => $request->mode_paiement,
            'transaction_ref' => $request->transaction_ref,
            'owner_note'      => $request->owner_note,
            'preuve_url'      => $preuveUrl,
        ]);

        // Mettre à jour la session pour afficher l'état "en attente" au prochain essai
        $request->session()->put('ateliko_blocked_login.role', 'PROPRIETAIRE');
        $request->session()->flash('login_blocked', 'proprietaire');
        $request->session()->flash('login_blocked_pending', true);

        return redirect()->route('login')
            ->with('success', 'Paiement soumis avec succès. En attente de validation par l\'administrateur.');
    }

    public function pending()
    {
        $user      = Auth::user();
        $atelierId = $user->atelier_id;

        $abonnement = $atelierId
            ? AbonnementAtelier::where('atelier_id', $atelierId)->with('plan')->latest()->first()
            : null;

        $dernierPaiement = $abonnement
            ? AbonnementPaiement::where('abonnement_id', $abonnement->id)
                ->orderByDesc('created_at')->first()
            : null;

        return view('abonnements.pending', compact('user', 'abonnement', 'dernierPaiement'));
    }
}
