<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'telephone' => 'required_without:email|string|max:30',
            'password' => 'required',
        ]);

        $telephone = trim((string) $request->input('telephone', $request->input('email')));
        $phoneCandidates = $this->phoneCandidates($telephone);
        $throttleKey = $this->loginThrottleKey($telephone, $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Trop de tentatives. Réessayez dans ' . ceil(RateLimiter::availableIn($throttleKey) / 60) . ' minute(s).',
            ], 429);
        }

        $utilisateur = Utilisateur::with(['atelier', 'permissions'])
            ->where(function ($query) use ($telephone, $phoneCandidates) {
                $query->whereIn('telephone', $phoneCandidates);

                if (filter_var($telephone, FILTER_VALIDATE_EMAIL)) {
                    $query->orWhere('email', $telephone);
                }
            })
            ->first();

        if (!$utilisateur || !Hash::check($request->password, $utilisateur->mot_de_passe)) {
            RateLimiter::hit($throttleKey, 120);

            $attemptsLeft = max(0, 5 - RateLimiter::attempts($throttleKey));
            $message = $attemptsLeft > 0
                ? 'Numéro ou mot de passe incorrect. Il reste ' . $attemptsLeft . ' tentative(s).'
                : 'Trop de tentatives. Réessayez dans 2 minute(s).';

            return response()->json(['message' => $message], 401);
        }

        if (!$utilisateur->actif) {
            return response()->json(['message' => 'Compte désactivé'], 401);
        }

        RateLimiter::clear($throttleKey);
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'userId' => $utilisateur->id,
            'id' => $utilisateur->id,
            'email' => $utilisateur->email,
            'prenom' => $utilisateur->prenom,
            'nom' => $utilisateur->nom,
            'role' => $utilisateur->role,
            'atelierId' => $utilisateur->atelier_id,
            'atelierName' => $utilisateur->atelier?->nom,
            'atelier' => $utilisateur->atelier ? [
                'id' => $utilisateur->atelier->id,
                'nom' => $utilisateur->atelier->nom,
            ] : null,
            'permissions' => $utilisateur->permissions->pluck('code'),
            'photoPath' => $utilisateur->photo_url,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me(Request $request)
    {
        $utilisateur = $request->user()->load(['atelier', 'permissions']);

        return response()->json([
            'id' => $utilisateur->id,
            'email' => $utilisateur->email,
            'prenom' => $utilisateur->prenom,
            'nom' => $utilisateur->nom,
            'role' => $utilisateur->role,
            'authorities' => [['authority' => 'ROLE_' . $utilisateur->role]],
            'permissions' => $utilisateur->permissions->pluck('code'),
            'atelierId' => $utilisateur->atelier_id,
            'atelierNom' => $utilisateur->atelier?->nom,
            'photoPath' => $utilisateur->photo_url,
            'actif' => $utilisateur->actif,
        ]);
    }

    private function phoneCandidates(string $telephone): array
    {
        $phoneDigits = preg_replace('/\D+/', '', preg_replace('/^00/', '+', $telephone));
        $localPhone = str_starts_with($phoneDigits, '223') && strlen($phoneDigits) > 8
            ? substr($phoneDigits, 3)
            : $phoneDigits;

        return array_values(array_unique(array_filter([
            $telephone,
            $phoneDigits,
            $localPhone,
            $localPhone ? '+223' . $localPhone : null,
            $localPhone ? '223' . $localPhone : null,
        ])));
    }

    private function loginThrottleKey(string $telephone, string $ip): string
    {
        $digits = preg_replace('/\D+/', '', $telephone) ?: $telephone;

        return Str::lower('api-login:' . $digits . '|' . $ip);
    }
}
