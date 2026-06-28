<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Utilisateur::with(['atelier', 'permissions']);

        if (!$user->isSuperAdmin()) {
            $query->where('atelier_id', $user->atelier_id);
        }

        return response()->json($query->get()->map(fn($u) => $this->formatUser($u)));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prenom' => 'required|string|min:2|max:50',
            'nom' => 'required|string|min:2|max:50',
            'email' => 'nullable|email|unique:utilisateurs,email',
            'telephone' => 'required|string|max:30|unique:utilisateurs,telephone',
            'mot_de_passe' => 'nullable|min:4',
            'motdepasse' => 'nullable|min:4',
            'role' => 'required|in:SUPERADMIN,PROPRIETAIRE,SECRETAIRE,TAILLEUR',
        ]);

        $user = $request->user();
        $password = $request->input('mot_de_passe', $request->input('motdepasse'));
        if (!$password) {
            return response()->json(['message' => 'Le mot de passe est obligatoire.'], 422);
        }
        $email = $request->email ?: $this->generatedEmail($request->telephone);

        $utilisateur = Utilisateur::create([
            'id' => Str::uuid(),
            'prenom' => $request->prenom,
            'nom' => $request->nom,
            'email' => $email,
            'telephone' => $request->telephone,
            'mot_de_passe' => Hash::make($password),
            'role' => $request->role,
            'actif' => true,
            'atelier_id' => $request->atelier_id ?? $user->atelier_id,
        ]);

        return response()->json($this->formatUser($utilisateur->load(['atelier', 'permissions'])), 201);
    }

    public function show(Request $request, $id)
    {
        $utilisateur = Utilisateur::with(['atelier', 'permissions'])->findOrFail($id);
        return response()->json($this->formatUser($utilisateur));
    }

    public function update(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        $request->validate([
            'telephone' => 'nullable|string|max:30|unique:utilisateurs,telephone,' . $id,
        ]);

        $utilisateur->update($request->only(['prenom', 'nom', 'telephone', 'role', 'atelier_id']));

        if ($request->has('email') && $request->email !== $utilisateur->email) {
            $request->validate(['email' => 'email|unique:utilisateurs,email,' . $id]);
            $utilisateur->email = $request->email;
            $utilisateur->save();
        }

        return response()->json($this->formatUser($utilisateur->fresh()->load(['atelier', 'permissions'])));
    }

    public function destroy($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }

    public function activate($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->update(['actif' => true]);
        return response()->json($this->formatUser($utilisateur->load(['atelier', 'permissions'])));
    }

    public function deactivate($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->update(['actif' => false]);
        return response()->json($this->formatUser($utilisateur->load(['atelier', 'permissions'])));
    }

    public function uploadPhoto(Request $request, $id)
    {
        $request->validate(['photo' => 'required|image|max:10240']);
        $utilisateur = Utilisateur::findOrFail($id);

        $path = $request->file('photo')->store('user_photo', 'public');
        $utilisateur->update(['photo_path' => $path]);

        return response()->json(['message' => 'Photo mise à jour', 'photoPath' => $path]);
    }

    public function deletePhoto($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        if ($utilisateur->photo_path) {
            Storage::disk('public')->delete($utilisateur->photo_path);
            $utilisateur->update(['photo_path' => null]);
        }
        return response()->json(['message' => 'Photo supprimée']);
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:4',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        $utilisateur = Utilisateur::findOrFail($id);

        if (!Hash::check($request->currentPassword, $utilisateur->mot_de_passe)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 400);
        }

        $utilisateur->update(['mot_de_passe' => Hash::make($request->newPassword)]);
        return response()->json(['message' => 'Mot de passe modifié avec succès']);
    }

    public function profile($id)
    {
        $utilisateur = Utilisateur::with('atelier')->findOrFail($id);
        return response()->json([
            'id' => $utilisateur->id,
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            'telephone' => $utilisateur->telephone,
            'role' => $utilisateur->role,
            'photoPath' => $utilisateur->photo_path,
            'atelier' => $utilisateur->atelier ? [
                'id' => $utilisateur->atelier->id,
                'nom' => $utilisateur->atelier->nom,
            ] : null,
        ]);
    }

    public function permissions($id)
    {
        $utilisateur = Utilisateur::with('permissions')->findOrFail($id);
        return response()->json($utilisateur->permissions->map(fn($p) => [
            'id' => $p->id,
            'code' => $p->code,
            'description' => $p->description,
        ]));
    }

    public function syncPermissions(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $permissionIds = Permission::whereIn('code', $request->permissions ?? [])->pluck('id');
        $utilisateur->permissions()->sync($permissionIds);
        return response()->json(['message' => 'Permissions mises à jour']);
    }

    private function formatUser(Utilisateur $u): array
    {
        return [
            'id' => $u->id,
            'prenom' => $u->prenom,
            'nom' => $u->nom,
            'email' => $u->email,
            'telephone' => $u->telephone,
            'role' => $u->role,
            'actif' => $u->actif,
            'photoPath' => $u->photo_path,
            'atelierId' => $u->atelier_id,
            'atelierNom' => $u->atelier?->nom,
            'permissions' => $u->relationLoaded('permissions')
                ? $u->permissions->map(fn($p) => ['id' => $p->id, 'code' => $p->code, 'description' => $p->description])
                : [],
        ];
    }

    private function generatedEmail(string $telephone): string
    {
        $digits = preg_replace('/\D+/', '', $telephone) ?: Str::random(8);

        return 'user-' . $digits . '-' . Str::lower(Str::random(6)) . '@ateliko.local';
    }
}
