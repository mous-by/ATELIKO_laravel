<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $identifiant = $request->email;

        $utilisateur = Utilisateur::with(['atelier', 'permissions'])
            ->where('email', $identifiant)
            ->orWhere('telephone', $identifiant)
            ->first();

        if (!$utilisateur || !Hash::check($request->password, $utilisateur->mot_de_passe)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        if (!$utilisateur->actif) {
            return response()->json(['message' => 'Compte désactivé'], 401);
        }

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
            'photoPath' => $utilisateur->photo_path,
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
            'photoPath' => $utilisateur->photo_path,
            'actif' => $utilisateur->actif,
        ]);
    }
}
