<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Modele extends Model
{
    use HasUuids;

    protected $table = 'modeles';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom', 'description', 'prix', 'photo_path', 'video_path',
        'categorie', 'est_actif', 'atelier_id', 'date_creation', 'date_modification',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'prix' => 'float',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
        );
    }

    protected function videoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->video_path ? Storage::disk('public')->url($this->video_path) : null,
        );
    }

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function mesures()
    {
        return $this->hasMany(Mesure::class, 'modele_reference_id');
    }
}
