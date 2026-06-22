<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Atelier extends Model
{
    use HasUuids;

    protected $table = 'ateliers';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom', 'adresse', 'telephone', 'email', 'date_creation',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
    ];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'atelier_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'atelier_id');
    }

    public function modeles()
    {
        return $this->hasMany(Modele::class, 'atelier_id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'atelier_id');
    }

    public function mesures()
    {
        return $this->hasMany(Mesure::class, 'atelier_id');
    }

    public function rendezvous()
    {
        return $this->hasMany(Rendezvous::class, 'atelier_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'atelier_id');
    }

    public function abonnements()
    {
        return $this->hasMany(AbonnementAtelier::class, 'atelier_id');
    }

    public function abonnement()
    {
        return $this->hasOne(AbonnementAtelier::class, 'atelier_id')->latest();
    }
}
