<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    public function index(Request $request)
    {
        $this->checkSuperAdmin($request);

        return response()->json(Permission::orderBy('code')->get());
    }

    public function store(Request $request)
    {
        $this->checkSuperAdmin($request);
        $data = $request->validate([
            'code' => 'required|string|max:100|unique:permissions,code',
            'description' => 'nullable|string|max:255',
        ]);

        return response()->json(Permission::create($data), 201);
    }

    public function update(Request $request, string $id)
    {
        $this->checkSuperAdmin($request);
        $permission = Permission::findOrFail($id);
        $data = $request->validate([
            'code' => 'required|string|max:100|unique:permissions,code,' . $permission->id,
            'description' => 'nullable|string|max:255',
        ]);
        $permission->update($data);

        return response()->json($permission);
    }

    public function destroy(Request $request, string $id)
    {
        $this->checkSuperAdmin($request);
        Permission::findOrFail($id)->delete();

        return response()->json(['message' => 'Permission supprimée']);
    }

    public function userPermissions(Request $request, string $userId)
    {
        $this->checkSuperAdmin($request);
        $user = Utilisateur::with('permissions')->findOrFail($userId);

        return response()->json($user->permissions);
    }

    public function syncUserPermissions(Request $request, string $userId)
    {
        $this->checkSuperAdmin($request);
        $user = Utilisateur::findOrFail($userId);
        $ids = collect($request->all())->filter()->values()->all();
        $user->permissions()->sync($ids);

        return response()->json(['message' => 'Permissions mises à jour']);
    }

    private function checkSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Accès réservé au superadmin');
    }
}
