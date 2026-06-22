<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Paiement extends Model
{
    use HasUuids;

    protected $table = 'paiements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'montant', 'moyen', 'reference', 'date_paiement',
        'type_paiement', 'client_id', 'tailleur_id', 'atelier_id', 'note',
    ];

    protected $casts = [
        'montant' => 'float',
        'date_paiement' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($paiement) {
            if (empty($paiement->reference)) {
                $paiement->reference = 'PAY-' . strtoupper(Str::random(10));
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function tailleur()
    {
        return $this->belongsTo(Utilisateur::class, 'tailleur_id');
    }

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }
}
