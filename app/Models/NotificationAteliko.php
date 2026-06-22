<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NotificationAteliko extends Model
{
    use HasUuids;

    protected $table = 'notifications_ateliko';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'recipient_id', 'message', 'type', 'is_read',
        'related_entity_id', 'related_entity_type', 'date_creation',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'date_creation' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(Utilisateur::class, 'recipient_id');
    }
}
