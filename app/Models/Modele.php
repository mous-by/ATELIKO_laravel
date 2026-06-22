<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function mesures()
    {
        return $this->hasMany(Mesure::class, 'modele_reference_id');
    }
}
