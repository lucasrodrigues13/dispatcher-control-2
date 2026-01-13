<?php
// app/Models/Subscription.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'blocked_at',
        'payment_method',
        'payment_gateway_id',
        'stripe_payment_id',  // Adicionado
        'stripe_subscription_id',
        'stripe_status',
        'stripe_current_period_start',
        'stripe_current_period_end',
        'amount',
        'billing_cycle_day',
        'due_date',           // Adicionado
        'payment_terms',      // Adicionado
        'invoice_notes',      // Adicionado
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'blocked_at' => 'datetime',
        'due_date' => 'datetime',        // Adicionado
        'stripe_current_period_start' => 'datetime',
        'stripe_current_period_end' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function billingNotifications()
    {
        return $this->hasMany(BillingNotification::class);
    }

    public function subscriptionItems()
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    // Verifica se é plano unlimited (carrier)
    public function isUnlimited()
    {
        return $this->plan && $this->plan->slug === 'carrier-unlimited';
    }

    // Verifica se está em trial
    public function isOnTrial()
    {
        return $this->status === 'trial' &&
               $this->trial_ends_at &&
               now()->isBefore($this->trial_ends_at);
    }

    // Verifica se está ativa
    public function isActive()
    {
        // Se é plano unlimited, sempre ativo
        if ($this->isUnlimited()) {
            return true;
        }

        // Se está em trial e ainda não expirou
        if ($this->isOnTrial()) {
            return true;
        }

        // Se está ativa e não bloqueada
        return $this->status === 'active' && !$this->isBlocked();
    }

    // Verifica se está bloqueada
    public function isBlocked()
    {
        // Planos unlimited nunca são bloqueados
        if ($this->isUnlimited()) {
            return false;
        }

        return $this->status === 'blocked' ||
               ($this->blocked_at && now()->isAfter($this->blocked_at));
    }

    // Verifica se trial expirou
    // public function trialExpired()
    // {
    //     // Planos unlimited não têm trial
    //     if ($this->isUnlimited()) {
    //         return false;
    //     }

    //     return $this->trial_ends_at && now()->isAfter($this->trial_ends_at);
    // }

    // NOVO: Verifica se está vencida (overdue)
    public function isOverdue()
    {
        // Planos unlimited nunca vencem
        if ($this->isUnlimited()) {
            return false;
        }

        // Se tem data de vencimento e já passou
        if ($this->due_date && now()->isAfter($this->due_date)) {
            return $this->status !== 'paid' && $this->status !== 'active';
        }

        // Se tem data de expiração e já passou
        if ($this->expires_at && now()->isAfter($this->expires_at)) {
            return $this->status !== 'paid' && $this->status !== 'active';
        }

        return false;
    }

    // NOVO: Calcula dias até o vencimento
    public function daysUntilDue()
    {
        // Planos unlimited não vencem
        if ($this->isUnlimited()) {
            return null;
        }

        if (!$this->due_date) {
            return null;
        }

        $days = now()->diffInDays($this->due_date, false);
        return $days;
    }

    // NOVO: Verifica se vence em breve (próximos 7 dias)
    public function isDueSoon($days = 7)
    {
        // Planos unlimited não vencem
        if ($this->isUnlimited()) {
            return false;
        }

        if (!$this->due_date) {
            return false;
        }

        return now()->diffInDays($this->due_date, false) <= $days &&
               now()->diffInDays($this->due_date, false) >= 0;
    }

    // NOVO: Accessor para formatar payment_terms
    public function getPaymentTermsFormattedAttribute()
    {
        // Planos unlimited são gratuitos
        if ($this->isUnlimited()) {
            return 'Unlimited Access';
        }

        return match($this->payment_terms) {
            'net_15' => 'Net 15 days',
            'net_30' => 'Net 30 days',
            'net_45' => 'Net 45 days',
            'net_60' => 'Net 60 days',
            'due_on_receipt' => 'Due on Receipt',
            'custom' => 'Custom Terms',
            default => $this->payment_terms ?? 'Not specified'
        };
    }

    // Calcula próxima data de cobrança
    public function getNextBillingDate()
    {
        // Planos unlimited não têm cobrança
        if ($this->isUnlimited()) {
            return null;
        }

        $today = now();
        $nextBilling = $today->copy()->day($this->billing_cycle_day);

        if ($nextBilling->isPast()) {
            $nextBilling->addMonth();
        }

        return $nextBilling;
    }

    // NOVO: Scopes para consultas úteis
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'active')
              ->orWhereHas('plan', function($planQuery) {
                  $planQuery->where('slug', 'carrier-unlimited');
              });
        });
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked')
                    ->whereDoesntHave('plan', function($planQuery) {
                        $planQuery->where('slug', 'carrier-unlimited');
                    });
    }

    public function scopeOverdue($query)
    {
        return $query->where(function($q) {
            $q->where('due_date', '<', now())
              ->orWhere('expires_at', '<', now());
        })->whereNotIn('status', ['paid', 'active'])
          ->whereDoesntHave('plan', function($planQuery) {
              $planQuery->where('slug', 'carrier-unlimited');
          });
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
                    ->whereNotIn('status', ['paid', 'cancelled'])
                    ->whereDoesntHave('plan', function($planQuery) {
                        $planQuery->where('slug', 'carrier-unlimited');
                    });
    }

    public function scopeUnlimited($query)
    {
        return $query->whereHas('plan', function($planQuery) {
            $planQuery->where('slug', 'carrier-unlimited');
        });
    }
}
