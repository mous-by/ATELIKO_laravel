<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Client extends Model
{
    use HasUuids;

    protected $table = 'clients';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom', 'prenom', 'contact', 'adresse', 'email',
        'photo', 'sexe', 'atelier_id', 'date_creation',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
    ];

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function mesures()
    {
        return $this->hasMany(Mesure::class, 'client_id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'client_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'client_id');
    }

    public function rendezvous()
    {
        return $this->hasMany(Rendezvous::class, 'client_id');
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo ? Storage::disk('public')->url($this->photo) : null,
        );
    }

    public function getMontantTotalAttribute(): float
    {
        return $this->mesures()->sum('prix');
    }

    public function getMontantPayeAttribute(): float
    {
        return $this->paiements()->where('type_paiement', 'CLIENT')->sum('montant');
    }

    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->montant_total - $this->montant_paye);
    }
}
