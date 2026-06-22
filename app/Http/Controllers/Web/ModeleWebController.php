<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Modele;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModeleWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Modele::where('atelier_id', $user->atelier_id)->orderBy('date_creation', 'desc');

        if ($request->has('categorie') && $request->categorie) {
            $query->where('categorie', $request->categorie);
        }
        if ($request->has('search') && $request->search) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $modeles = $query->paginate(16);
        $categories = ['ROBE', 'JUPE', 'HOMME', 'ENFANT', 'AUTRE'];
        return view('modeles.index', compact('modeles', 'categories'));
    }

    public function create()
    {
        $categories = ['ROBE', 'JUPE', 'HOMME', 'ENFANT', 'AUTRE'];
        return view('modeles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'categorie' => 'required|in:ROBE,JUPE,HOMME,ENFANT,AUTRE',
        ]);

        $user = Auth::user();
        $data = [
            'id' => Str::uuid(),
            'nom' => $request->nom,
            'description' => $request->description,
            'prix' => $request->prix,
            'categorie' => $request->categorie,
            'est_actif' => true,
            'atelier_id' => $user->atelier_id,
            'date_creation' => now(),
        ];

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('model_photo', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('model_video', 'public');
        }

        Modele::create($data);
        return redirect()->route('modeles.index')->with('success', 'Modèle créé avec succès');
    }

    public function show($id)
    {
        $modele = Modele::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        return view('modeles.show', compact('modele'));
    }

    public function edit($id)
    {
        $modele = Modele::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        $categories = ['ROBE', 'JUPE', 'HOMME', 'ENFANT', 'AUTRE'];
        return view('modeles.edit', compact('modele', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $modele = Modele::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);

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
        return redirect()->route('modeles.show', $id)->with('success', 'Modèle mis à jour');
    }

    public function destroy($id)
    {
        $modele = Modele::where('atelier_id', Auth::user()->atelier_id)->findOrFail($id);
        if ($modele->photo_path) Storage::disk('public')->delete($modele->photo_path);
        if ($modele->video_path) Storage::disk('public')->delete($modele->video_path);
        $modele->delete();
        return redirect()->route('modeles.index')->with('success', 'Modèle supprimé');
    }
}
