<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model
{
    public const TYPES = [
        'reclamo' => 'Reclamo',
        'queja'   => 'Queja',
    ];

    public const DOCUMENT_TYPES = ['DNI', 'CE', 'Pasaporte'];

    public const ITEM_TYPES = [
        'producto' => 'Producto',
        'servicio' => 'Servicio',
    ];

    // Plazo legal de respuesta al consumidor
    public const RESPONSE_DAYS = 15;

    protected $fillable = [
        'user_id', 'consumer_name', 'document_type', 'document_number', 'address', 'phone', 'email',
        'is_minor', 'guardian_name', 'item_type', 'order_reference', 'amount', 'item_description',
        'type', 'detail', 'consumer_request', 'status', 'response', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_minor' => 'boolean',
            'amount' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    /**
     * Asigna el token privado al crear y el número correlativo de hoja tras guardar
     */
    protected static function booted(): void
    {
        static::creating(fn (Complaint $complaint) => $complaint->token ??= Str::random(40));

        static::created(function (Complaint $complaint) {
            $complaint->forceFill([
                'code' => sprintf('LR-%s-%06d', $complaint->created_at->format('Y'), $complaint->id),
            ])->saveQuietly();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
