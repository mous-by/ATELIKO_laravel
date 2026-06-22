<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Affectation extends Model
{
    use HasUuids;

    protected $table = 'affectations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'client_id', 'mesure_id', 'tailleur_id', 'atelier_id', 'createur_id',
        'prix_tailleur', 'statut', 'date_creation', 'date_echeance',
        'date_debut_reelle', 'date_fin_reelle', 'date_validation',
    ];

    protected $casts = [
        'prix_tailleur' => 'float',
        'date_creation' => 'datetime',
        'date_echeance' => 'datetime',
        'date_debut_reelle' => 'datetime',
        'date_fin_reelle' => 'datetime',
        'date_validation' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function mesure()
    {
        return $this->belongsTo(Mesure::class, 'mesure_id');
    }

    public function tailleur()
    {
        return $this->belongsTo(Utilisateur::class, 'tailleur_id');
    }

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'createur_id');
    }
}
