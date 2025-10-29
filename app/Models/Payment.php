<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'gateway_response',
        'attempted_at',
        'paid_at',
        'failed_at',
        'failure_reason',
        'attempt_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'attempted_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
