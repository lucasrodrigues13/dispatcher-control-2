<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'type',
        'sent_at',
        'email',
        'message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
