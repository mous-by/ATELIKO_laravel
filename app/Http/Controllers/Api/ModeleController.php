<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modele;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModeleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'categorie' => 'required|in:ROBE,JUPE,HOMME,ENFANT,AUTRE',
            'atelier_id' => 'required|uuid|exists:ateliers,id',
        ]);

        $data = [
            'id' => Str::uuid(),
            'nom' => $request->nom,
            'description' => $request->description,
            'prix' => $request->prix,
            'categorie' => $request->categorie,
            'est_actif' => true,
            'atelier_id' => $request->atelier_id,
            'date_creation' => now(),
        ];

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('model_photo', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('model_video', 'public');
        }

        $modele = Modele::create($data);
        return response()->json($this->format($modele), 201);
    }

    public function indexByAtelier($atelierId)
    {
        $modeles = Modele::where('atelier_id', $atelierId)
            ->orderBy('date_creation', 'desc')
            ->get();
        return response()->json($modeles->map(fn($m) => $this->format($m)));
    }

    public function showByAtelier($id, $atelierId)
    {
        $modele = Modele::where('atelier_id', $atelierId)->findOrFail($id);
        return response()->json($this->format($modele, true));
    }

    public function updateByAtelier(Request $request, $id, $atelierId)
    {
        $modele = Modele::where('atelier_id', $atelierId)->findOrFail($id);

        $modele->fill($request->only(['nom', 'description', 'prix', 'categorie']));
        $modele->date_modification = now();

        if ($request->hasFile('photo')) {
            if ($modele->photo_path) Storage::disk('public')->delete($modele->photo_path);
            $modele->photo_path = $request->file('photo')->store('model_photo', 'public');
        }
        if ($request->hasFile('video')) {
            if ($modele->video_path) Storage::disk('public')->delete($modele->video_path);
            $modele->video_path = $request->file('video')->store('model_video', 'public');
        }

        $modele->save();
        return response()->json($this->format($modele, true));
    }

    public function destroyByAtelier($id, $atelierId)
    {
        $modele = Modele::where('atelier_id', $atelierId)->findOrFail($id);
        if ($modele->photo_path) Storage::disk('public')->delete($modele->photo_path);
        if ($modele->video_path) Storage::disk('public')->delete($modele->video_path);
        $modele->delete();
        return response()->json(['message' => 'Modèle supprimé']);
    }

    public function uploadPhoto(Request $request, $id, $atelierId)
    {
        $request->validate(['photo' => 'required|image|max:10240']);
        $modele = Modele::where('atelier_id', $atelierId)->findOrFail($id);
        if ($modele->photo_path) Storage::disk('public')->delete($modele->photo_path);
        $modele->update(['photo_path' => $request->file('photo')->store('model_photo', 'public')]);
        return response()->json('Photo mise à jour');
    }

    public function deletePhoto($id, $atelierId)
    {
        $modele = Modele::where('atelier_id', $atelierId)->findOrFail($id);
        if ($modele->photo_path) {
            Storage::disk('public')->delete($modele->photo_path);
            $modele->update(['photo_path' => null]);
        }
        return response()->json('Photo supprimée');
    }

    public function activate($id, $atelierId)
    {
        Modele::where('atelier_id', $atelierId)->findOrFail($id)->update(['est_actif' => true]);
        return response()->noContent();
    }

    public function deactivate($id, $atelierId)
    {
        Modele::where('atelier_id', $atelierId)->findOrFail($id)->update(['est_actif' => false]);
        return response()->noContent();
    }

    public function search(Request $request, $atelierId)
    {
        $q = $request->q ?? '';
        $modeles = Modele::where('atelier_id', $atelierId)
            ->where('nom', 'like', "%$q%")
            ->get();
        return response()->json($modeles->map(fn($m) => $this->format($m)));
    }

    public function byCategorie($atelierId, $categorie)
    {
        $modeles = Modele::where('atelier_id', $atelierId)
            ->where('categorie', $categorie)
            ->get();
        return response()->json($modeles->map(fn($m) => $this->format($m)));
    }

    public function count($atelierId)
    {
        return response()->json(Modele::where('atelier_id', $atelierId)->count());
    }

    public function servePhoto($filename)
    {
        $path = storage_path('app/public/model_photo/' . $filename);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    }

    public function serveVideo($filename)
    {
        $path = storage_path('app/public/model_video/' . $filename);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    }

    private function format(Modele $m, bool $full = false): array
    {
        $data = [
            'id' => $m->id,
            'nom' => $m->nom,
            'categorie' => $m->categorie,
            'prix' => $m->prix,
            'photoPath' => $m->photo_path,
            'photoUrl' => $m->photo_path ? asset($m->photo_path) : null,
            'estActif' => $m->est_actif,
            'atelierId' => $m->atelier_id,
            'dateCreation' => $m->date_creation,
        ];

        if ($full) {
            $data['description'] = $m->description;
            $data['videoPath'] = $m->video_path;
            $data['dateModification'] = $m->date_modification;
        }

        return $data;
    }
}
