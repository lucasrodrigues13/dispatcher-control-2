<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoadPickupConfirmationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'status',
        'job_uuid',
        'confirmation_id',
        'created_by',
        'error_message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Load
     */
    public function loadRelation(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id', 'id');
    }

    /**
     * Relacionamento com LoadPickupConfirmation
     */
    public function confirmation(): BelongsTo
    {
        return $this->belongsTo(LoadPickupConfirmation::class, 'confirmation_id');
    }

    /**
     * Relacionamento com User (criador)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Verifica se há tentativa pendente para um load
     */
    public static function hasPendingAttempt(int $loadId): bool
    {
        return self::where('load_id', $loadId)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }

    /**
     * Obtém a tentativa pendente de um load
     */
    public static function getPendingAttempt(int $loadId): ?self
    {
        return self::where('load_id', $loadId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }
}
