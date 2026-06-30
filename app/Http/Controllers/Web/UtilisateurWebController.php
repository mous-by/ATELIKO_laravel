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
        $roles = $this->rolesDisponiblesPour($user);
        $ateliers = $user->isSuperAdmin() ? Atelier::orderBy('nom')->get() : collect();
        return view('utilisateurs.index', compact('utilisateurs', 'roles', 'ateliers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $roles = $this->rolesDisponiblesPour($user);

        $rules = [
            'prenom'      => 'required|string|min:2|max:50',
            'nom'         => 'required|string|min:2|max:50',
            'email'       => 'required|email|unique:utilisateurs,email',
            'telephone'   => 'nullable|string|unique:utilisateurs,telephone',
            'mot_de_passe' => 'required|min:4|confirmed',
            'role'        => 'required|in:' . implode(',', $roles),
        ];
        if ($user->isSuperAdmin()) {
            $rules['atelier_id'] = 'required|uuid|exists:ateliers,id';
        }
        $request->validate($rules, $this->validationMessages(), $this->validationAttributes());

        $atelierId = $user->isSuperAdmin() ? $request->atelier_id : $user->atelier_id;

        $nouvelUtilisateur = Utilisateur::create([
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

        // Permissions par défaut selon le rôle
        $this->assignerPermissionsParDefaut($nouvelUtilisateur);

        return redirect()
            ->route('utilisateurs.permissions', $nouvelUtilisateur->id)
            ->with('success', 'Utilisateur créé. Vérifiez et ajustez ses permissions ci-dessous.');
    }

    public function update(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $user = Auth::user();
        $roles = $this->rolesDisponiblesPour($user);

        $rules = [
            'prenom'    => 'required|string|min:2|max:50',
            'nom'       => 'required|string|min:2|max:50',
            'email'     => 'required|email|unique:utilisateurs,email,' . $id,
            'telephone' => 'nullable|string|unique:utilisateurs,telephone,' . $id,
            'role'      => 'required|in:' . implode(',', $roles),
            'mot_de_passe' => 'nullable|min:4|confirmed',
        ];

        $request->validate($rules, $this->validationMessages(), $this->validationAttributes());

        $data = $request->only(['prenom', 'nom', 'email', 'telephone', 'role']);
        if ($request->filled('mot_de_passe')) {
            $data['mot_de_passe'] = Hash::make($request->mot_de_passe);
        }

        $utilisateur->update($data);
        return back()->with('success', 'Utilisateur mis à jour');
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

        // Rester sur la page d'origine (assigner ou standalone)
        $previous = url()->previous();
        $fallback = route('utilisateurs.permissions', $id);
        return redirect($previous ?: $fallback)->with('success', 'Permissions mises à jour');
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
        ], $this->validationMessages(), $this->validationAttributes());

        $user->update($request->only(['prenom', 'nom', 'telephone']));

        if ($request->hasFile('photo')) {
            if ($user->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo_path);
            }
            $path = $request->file('photo')->store('user_photo', 'public');
            $user->update(['photo_path' => $path]);
        }

        return redirect()->route('profile')->with('success', 'Profil mis à jour');
    }

    public function deletePhoto()
    {
        $user = Auth::user();
        if ($user->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo_path);
            $user->update(['photo_path' => null]);
        }
        return redirect()->route('profile')->with('success', 'Photo supprimée');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:4|confirmed',
        ], $this->validationMessages(), $this->validationAttributes());

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->mot_de_passe)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect']);
        }

        $user->update(['mot_de_passe' => Hash::make($request->new_password)]);
        return redirect()->route('profile')->with('success', 'Mot de passe modifié');
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
            'unique' => 'Cette valeur existe déjà pour :attribute.',
            'min' => 'Le champ :attribute doit contenir au moins :min caractères.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
            'in' => 'La valeur choisie pour :attribute est invalide.',
            'uuid' => 'La valeur choisie pour :attribute est invalide.',
            'exists' => 'La valeur choisie pour :attribute est introuvable.',
        ];
    }

    private function assignerPermissionsParDefaut(Utilisateur $utilisateur): void
    {
        $parRole = [
            'TAILLEUR' => [
                'AFFECTATION_VOIR', 'AFFECTATION_MODIFIER',
                'CLIENT_VOIR',
                'MODELE_VOIR',
                'RENDEZ_VOUS_VOIR',
            ],
            'SECRETAIRE' => [
                'CLIENT_CREER', 'CLIENT_MODIFIER', 'CLIENT_VOIR', 'CLIENT_SUPPRIMER',
                'AFFECTATION_CREER', 'AFFECTATION_MODIFIER', 'AFFECTATION_VOIR', 'AFFECTATION_SUPPRIMER',
                'RENDEZ_VOUS_CREER', 'RENDEZ_VOUS_MODIFIER', 'RENDEZ_VOUS_VOIR', 'RENDEZ_VOUS_SUPPRIMER',
                'MODELE_CREER', 'MODELE_MODIFIER', 'MODELE_VOIR',
                'PAIEMENT_CREER', 'PAIEMENT_VOIR',
                'RAPPORT_VOIR',
            ],
            'PROPRIETAIRE' => [], // récupère toutes les permissions
        ];

        $codes = $parRole[$utilisateur->role] ?? null;

        if ($codes === null) {
            return;
        }

        if ($utilisateur->role === 'PROPRIETAIRE') {
            $ids = Permission::pluck('id');
        } else {
            $ids = Permission::whereIn('code', $codes)->pluck('id');
        }

        $utilisateur->permissions()->sync($ids);
    }

    private function rolesDisponiblesPour(Utilisateur $user): array
    {
        return $user->isSuperAdmin()
            ? ['PROPRIETAIRE', 'SECRETAIRE', 'TAILLEUR']
            : ['SECRETAIRE', 'TAILLEUR'];
    }

    private function validationAttributes(): array
    {
        return [
            'prenom' => 'prénom',
            'nom' => 'nom',
            'email' => 'email',
            'telephone' => 'téléphone',
            'mot_de_passe' => 'mot de passe',
            'new_password' => 'nouveau mot de passe',
            'current_password' => 'mot de passe actuel',
            'role' => 'rôle',
            'atelier_id' => 'atelier',
        ];
    }
}
