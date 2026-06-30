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

        $modeles = $query->withCount('mesures')->paginate(16);
        $categories = ['ROBE', 'JUPE', 'HOMME', 'ENFANT', 'AUTRE'];
        return view('modeles.index', compact('modeles', 'categories'));
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'photos'   => 'required|array|min:1|max:30',
            'photos.*' => 'required|image|max:10240',
        ]);

        $user  = Auth::user();
        $count = 0;
        foreach ($request->file('photos') as $photo) {
            Modele::create([
                'id'             => Str::uuid(),
                'nom'            => '',
                'categorie'      => 'AUTRE',
                'est_actif'      => true,
                'atelier_id'     => $user->atelier_id,
                'date_creation'  => now(),
                'photo_path'     => $photo->store('model_photo', 'public'),
            ]);
            $count++;
        }

        return response()->json(['count' => $count]);
    }

    public function quickStoreVideos(Request $request)
    {
        $request->validate([
            'videos'   => 'required|array|min:1|max:10',
            'videos.*' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm|max:102400',
        ]);

        $user  = Auth::user();
        $count = 0;
        foreach ($request->file('videos') as $video) {
            Modele::create([
                'id'            => Str::uuid(),
                'nom'           => '',
                'categorie'     => 'AUTRE',
                'est_actif'     => true,
                'atelier_id'    => $user->atelier_id,
                'date_creation' => now(),
                'video_path'    => $video->store('model_video', 'public'),
            ]);
            $count++;
        }

        return response()->json(['count' => $count]);
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
