<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Atelier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AtelierController extends Controller
{
    public function index()
    {
        return response()->json(Atelier::all()->map(fn($a) => $this->format($a)));
    }

    public function store(Request $request)
    {
        $request->validate(['nom' => 'required|string']);

        $atelier = Atelier::create([
            'id' => Str::uuid(),
            'nom' => $request->nom,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
        ]);

        return response()->json($this->format($atelier), 201);
    }

    public function show($id)
    {
        return response()->json($this->format(Atelier::findOrFail($id)));
    }

    public function update(Request $request, $id)
    {
        $atelier = Atelier::findOrFail($id);
        $atelier->update($request->only(['nom', 'adresse', 'telephone', 'email']));
        return response()->json($this->format($atelier));
    }

    public function destroy($id)
    {
        Atelier::findOrFail($id)->delete();
        return response()->noContent();
    }

    private function format(Atelier $a): array
    {
        return [
            'id' => $a->id,
            'nom' => $a->nom,
            'adresse' => $a->adresse,
            'telephone' => $a->telephone,
            'email' => $a->email,
            'dateCreation' => $a->date_creation,
        ];
    }
}
