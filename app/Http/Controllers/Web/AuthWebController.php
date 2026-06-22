<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use App\Models\AbonnementPlan;
use App\Models\ActivityLog;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $rawPassword = (string) $request->input('password');
        $rawIdentifier = trim((string) $request->input('email'));
        $identifier = mb_strtolower($rawIdentifier);
        $phoneDigits = preg_replace('/\D+/', '', preg_replace('/^00/', '+', $rawIdentifier));

        // Les numéros maliens peuvent être saisis en local ou avec l'indicatif +223.
        $localPhone = str_starts_with($phoneDigits, '223') && strlen($phoneDigits) > 8
            ? substr($phoneDigits, 3)
            : $phoneDigits;
        $phoneCandidates = array_values(array_unique(array_filter([
            $rawIdentifier,
            $phoneDigits,
            $localPhone,
            $localPhone ? '+223' . $localPhone : null,
            $localPhone ? '223' . $localPhone : null,
        ])));

        $request->merge([
            'email' => $identifier,
            'password' => trim($rawPassword),
        ]);

        $request->validate([
            'email' => 'required|string|max:150',
            'password' => 'required',
        ]);

        $utilisateur = Utilisateur::where(function ($query) use ($identifier, $phoneCandidates) {
            $query->whereRaw('LOWER(email) = ?', [$identifier]);
            if ($phoneCandidates !== []) {
                $query->orWhereIn('telephone', $phoneCandidates);
            }
        })->first();
        $passwordMatches = $utilisateur
            && Hash::check($request->password, $utilisateur->mot_de_passe);

        if (!$passwordMatches) {
            Log::warning('Échec de connexion web', [
                'identifiant' => $identifier,
                'utilisateur_trouve' => (bool) $utilisateur,
                'longueur_mot_de_passe' => mb_strlen($request->password),
                'espaces_supprimes' => strlen($rawPassword) !== strlen($request->password),
                'adresse_ip' => $request->ip(),
            ]);

            return back()->withErrors(['email' => 'Identifiants incorrects'])->withInput();
        }

        if (!$utilisateur->actif) {
            return back()->withErrors(['email' => 'Compte désactivé. Contactez votre administrateur.'])->withInput();
        }

        // ── Vérification abonnement AVANT la connexion ──────────────────
        if (!$utilisateur->isSuperAdmin() && $utilisateur->atelier_id) {
            $abonnement = AbonnementAtelier::where('atelier_id', $utilisateur->atelier_id)->latest()->first();
            $isBlocked  = !$abonnement
                || in_array($abonnement->statut, ['EXPIRED', 'CANCELED', 'PAST_DUE'])
                || ($abonnement->date_fin && $abonnement->date_fin->isPast());

            if ($isBlocked) {
                // Stocker l'identité de l'utilisateur sans ouvrir de session complète
                $request->session()->put('ateliko_blocked_login', [
                    'user_id'    => $utilisateur->id,
                    'atelier_id' => $utilisateur->atelier_id,
                    'user_name'  => trim($utilisateur->prenom . ' ' . $utilisateur->nom),
                    'role'       => $utilisateur->role,
                    'atelier_nom'=> $utilisateur->atelier?->nom ?? 'votre atelier',
                ]);

                if ($utilisateur->isProprietaire()) {
                    $hasPending = $abonnement && AbonnementPaiement::where('abonnement_id', $abonnement->id)
                        ->where('statut', 'PENDING')->exists();
                    $failedPayment = $abonnement ? AbonnementPaiement::where('abonnement_id', $abonnement->id)
                        ->where('statut', 'FAILED')->orderByDesc('created_at')->first() : null;
                    $plans = AbonnementPlan::where('actif', true)->orderBy('duree_mois')->get()
                        ->map(fn($p) => ['code' => $p->code, 'libelle' => $p->libelle, 'prix' => $p->prix, 'duree_mois' => $p->duree_mois, 'devise' => $p->devise])
                        ->toArray();

                    $request->session()->flash('login_blocked', 'proprietaire');
                    $request->session()->flash('login_blocked_plans', $plans);
                    $request->session()->flash('login_blocked_pending', $hasPending);
                    $request->session()->flash('login_blocked_failed', $failedPayment?->review_note);
                } else {
                    $request->session()->flash('login_blocked', 'employee');
                }

                return back()->withInput(['email' => $request->input('email')]);
            }
        }
        // ────────────────────────────────────────────────────────────────

        Auth::guard('web')->login($utilisateur, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLog::create([
            'utilisateur_id'  => $utilisateur->id,
            'atelier_id'      => $utilisateur->atelier_id,
            'nom_utilisateur' => trim($utilisateur->prenom . ' ' . $utilisateur->nom),
            'role'            => $utilisateur->role,
            'action'          => 'LOGIN',
            'description'     => 'Connexion réussie',
            'ip_address'      => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::create([
                'utilisateur_id'  => $user->id,
                'atelier_id'      => $user->atelier_id,
                'nom_utilisateur' => trim($user->prenom . ' ' . $user->nom),
                'role'            => $user->role,
                'action'          => 'LOGOUT',
                'description'     => 'Déconnexion',
                'ip_address'      => $request->ip(),
            ]);
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
