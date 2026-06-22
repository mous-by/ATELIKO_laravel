<?php

namespace App\Http\Middleware;

use App\Models\AbonnementAtelier;
use App\Models\AbonnementPaiement;
use Closure;
use Illuminate\Http\Request;

class CheckAtelierSubscription
{
    // Routes toujours accessibles (même abonnement expiré)
    private const ALLOWED_ROUTES = [
        'dashboard',
        'abonnement.index',
        'abonnement.paiement',
        'abonnement.pending',
        'logout',
        'profile',
        'profile.update',
        'profile.password',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $atelierId = $user->atelier_id;
        if (!$atelierId) {
            return $next($request);
        }

        $abonnement = AbonnementAtelier::where('atelier_id', $atelierId)->latest()->first();
        $isBlocked  = $this->isBlocked($abonnement);

        if (!$isBlocked) {
            return $next($request);
        }

        // Route exemptée → passage normal
        $currentRoute = $request->route()?->getName();
        if (in_array($currentRoute, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        // Tout autre route → rediriger vers le dashboard (le modal bloquant y est affiché)
        return redirect()->route('dashboard');
    }

    private function isBlocked(?AbonnementAtelier $abonnement): bool
    {
        if (!$abonnement) {
            return true;
        }
        if (in_array($abonnement->statut, ['EXPIRED', 'CANCELED', 'PAST_DUE'])) {
            return true;
        }
        if ($abonnement->date_fin && $abonnement->date_fin->isPast()) {
            return true;
        }
        return false;
    }
}
