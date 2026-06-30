<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Mesure extends Model
{
    use HasUuids;

    protected $table = 'mesures';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'client_id', 'atelier_id', 'date_mesure', 'date_livraison', 'type_vetement', 'sexe',
        'prix', 'description', 'affecte', 'modele_reference_id', 'modele_nom',
        'photo_path', 'habit_photo_path', 'audio_description_path',
        // Communes
        'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse',
        'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
        // Jupe
        'longueur_jupe', 'ceinture',
        // Robe
        'longueur_poitrine_robe', 'longueur_taille_robe', 'longueur_fesse_robe',
        // Homme
        'longueur_pantalon', 'cuisse', 'corps',
    ];

    protected $casts = [
        'affecte'    => 'boolean',
        'date_mesure' => 'date',
        'prix'       => 'float',
    ];

    private const MESURE_FIELDS = [
        'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse',
        'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
        'longueur_jupe', 'ceinture',
        'longueur_poitrine_robe', 'longueur_taille_robe', 'longueur_fesse_robe',
        'longueur_pantalon', 'cuisse', 'corps',
    ];

    public function setAttribute($key, $value): mixed
    {
        if (in_array($key, self::MESURE_FIELDS, true)) {
            $value = $this->normalizeMesureValue($value);
        }
        return parent::setAttribute($key, $value);
    }

    private function normalizeMesureValue(mixed $val): ?string
    {
        if ($val === null || $val === '') return null;
        $val = str_replace(',', '.', trim((string) $val));
        if (str_contains($val, '|')) {
            $parts = array_values(array_filter(
                array_map(fn($p) => trim(str_replace(',', '.', $p)), explode('|', $val, 2)),
                fn($p) => $p !== ''
            ));
            return count($parts) === 2 ? $parts[0] . ' | ' . $parts[1] : ($parts[0] ?? null);
        }
        return $val !== '' ? $val : null;
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
        );
    }

    protected function habitPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->habit_photo_path ? Storage::disk('public')->url($this->habit_photo_path) : null,
        );
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function modeleReference()
    {
        return $this->belongsTo(Modele::class, 'modele_reference_id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'mesure_id');
    }

    public function rendezvous()
    {
        return $this->hasMany(Rendezvous::class, 'mesure_id');
    }
}
