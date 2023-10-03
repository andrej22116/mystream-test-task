<?php

namespace App\Models\Stripe;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * @property string $stripe_id
 * @property PaymentIntentStatus $status
 * @property-read  PaymentIntentError $error
 *
 * @method static Builder byPaymentIntentId(string $paymentIntentId) filter by payment intent id
 */
class PaymentIntent extends Model
{
    use HasFactory;

    protected $table = 'stripe_payment_intents';

    protected $fillable = [
        'intent_id',
        'status',
    ];

    protected $casts = [
        'status' => PaymentIntentStatus::class,
    ];

    public function scopeByPaymentIntentId(Builder $query, string $paymentIntentId): Builder
    {
        return $query->where('intent_id', $paymentIntentId);
    }

    public static function createNew(string|\Stripe\PaymentIntent $intent): static
    {
        return static::create([
            'intent_id' => is_string($intent) ? $intent : $intent->id,
        ]);
    }

    public function getOrder(): null|Order
    {
        return $this->orders?->first();
    }

    public function getSubscription(): null|Subscription
    {
        return $this->subscriptions?->first();
    }

    protected function orders(): BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            'stripe_order_payment_intent',
            'payment_intent_id',
            'order_id',
            'id',
            'id'
        );
    }

    protected function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            Subscription::class,
            'stripe_subscription_payment_intent',
            'payment_intent_id',
            'subscription_id',
            'id',
            'id'
        );
    }

    protected function error(): BelongsTo
    {
        return $this->belongsTo(
            PaymentIntentError::class,
            'payment_intent_id',
            'id'
        );
    }
}
