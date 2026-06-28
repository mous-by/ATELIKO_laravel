<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
        'affecte' => 'boolean',
        'date_mesure' => 'date',
        'prix' => 'float',
        'epaule' => 'float', 'manche' => 'float', 'poitrine' => 'float',
        'taille' => 'float', 'longueur' => 'float', 'fesse' => 'float',
        'tour_manche' => 'float', 'longueur_poitrine' => 'float',
        'longueur_taille' => 'float', 'longueur_fesse' => 'float',
        'longueur_jupe' => 'float', 'ceinture' => 'float',
        'longueur_poitrine_robe' => 'float', 'longueur_taille_robe' => 'float',
        'longueur_fesse_robe' => 'float',
        'longueur_pantalon' => 'float', 'cuisse' => 'float', 'corps' => 'float',
    ];

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
