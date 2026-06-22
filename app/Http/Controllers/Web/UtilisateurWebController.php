<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atelier;
use App\Models\Permission;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UtilisateurWebController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Utilisateur::with(['atelier', 'permissions']);

        if (!$user->isSuperAdmin()) {
            $query->where('atelier_id', $user->atelier_id)
                ->where('role', '!=', 'SUPERADMIN');
        }

        $utilisateurs = $query->orderBy('created_at', 'desc')->get();
        $roles = ['PROPRIETAIRE', 'SECRETAIRE', 'TAILLEUR'];
        $ateliers = $user->isSuperAdmin() ? Atelier::orderBy('nom')->get() : collect();
        return view('utilisateurs.index', compact('utilisateurs', 'roles', 'ateliers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'prenom' => 'required|string|min:2|max:50',
            'nom' => 'required|string|min:2|max:50',
            'email' => 'required|email|unique:utilisateurs,email',
            'mot_de_passe' => 'required|min:6|confirmed',
            'role' => 'required|in:PROPRIETAIRE,SECRETAIRE,TAILLEUR',
        ];
        if ($user->isSuperAdmin()) {
            $rules['atelier_id'] = 'required|uuid|exists:ateliers,id';
        }
        $request->validate($rules);

        $atelierId = $user->isSuperAdmin() ? $request->atelier_id : $user->atelier_id;

        Utilisateur::create([
            'id'           => Str::uuid(),
            'prenom'       => $request->prenom,
            'nom'          => $request->nom,
            'email'        => $request->email,
            'telephone'    => $request->telephone,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role'         => $request->role,
            'actif'        => true,
            'atelier_id'   => $atelierId,
        ]);

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur créé avec succès');
    }

    public function update(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->update($request->only(['prenom', 'nom', 'telephone', 'role']));
        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur mis à jour');
    }

    public function destroy($id)
    {
        Utilisateur::findOrFail($id)->delete();
        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur supprimé');
    }

    public function toggleActivation($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->update(['actif' => !$utilisateur->actif]);
        $msg = $utilisateur->actif ? 'Utilisateur activé' : 'Utilisateur désactivé';
        return redirect()->back()->with('success', $msg);
    }

    public function permissions($id)
    {
        $utilisateur = Utilisateur::with('permissions')->findOrFail($id);
        $allPermissions = Permission::orderBy('code')->get();
        return view('utilisateurs.permissions', compact('utilisateur', 'allPermissions'));
    }

    public function savePermissions(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $permissionIds = $utilisateur->isSuperAdmin()
            ? Permission::pluck('id')
            : Permission::whereIn('code', $request->permissions ?? [])->pluck('id');
        $utilisateur->permissions()->sync($permissionIds);
        return redirect()->route('utilisateurs.permissions', $id)->with('success', 'Permissions mises à jour');
    }

    public function profile()
    {
        $user = Auth::user()->load('atelier');
        return view('utilisateurs.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'prenom' => 'required|string',
            'nom' => 'required|string',
        ]);

        $user->update($request->only(['prenom', 'nom', 'telephone']));

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('user_photo', 'public');
            $user->update(['photo_path' => $path]);
        }

        return redirect()->route('profile')->with('success', 'Profil mis à jour');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->mot_de_passe)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect']);
        }

        $user->update(['mot_de_passe' => Hash::make($request->new_password)]);
        return redirect()->route('profile')->with('success', 'Mot de passe modifié');
    }
}
