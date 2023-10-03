<?php

namespace App\Models;

use App\Models\Stripe\CustomerPaymentMethod;
use App\Models\Stripe\PaymentIntent;
use App\Models\Subscription\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Orchid\Screen\AsSource;

/**
 * @property-read int $id
 * @property-read int $product_id
 * @property SubscriptionStatus $status,
 * @property \DateTime $next_payment
 * @property \DateTime $last_payment
 *
 * @property-read User $user
 * @property-read Product $product
 * @property-read Collection<PaymentIntent> paymentIntents
 * @property-read Collection<CustomerPaymentMethod> paymentMethods
 */
class Subscription extends Model
{
    use HasFactory;
    use AsSource;

    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'next_payment',
        'last_payment',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'next_payment' => 'datetime',
        'last_payment' => 'datetime',
    ];

    /**
     * Make subscription active
     * @param bool $save
     * @return void
     */
    public function activate(bool $save = true): void
    {
        $this->status = SubscriptionStatus::Active;
        if ($save) {
            $this->save();
        }
    }

    /**
     * Make subscription paused or canceled
     * @param bool $save
     * @return void
     */
    public function block(bool $save = true): void
    {
        $this->status = $this->status === SubscriptionStatus::Active
            ? SubscriptionStatus::Paused
            : SubscriptionStatus::Canceled;
        if ($save) {
            $this->save();
        }
    }

    /**
     * Make subscription stopped
     * @param bool $save
     * @return void
     */
    public function stop(bool $save = true): void
    {
        $this->status = SubscriptionStatus::Stopped;
        if ($save) {
            $this->save();
        }
    }

    public function user(): HasOne
    {
        return $this->hasOne(
            User::class,
            'id',
            'user_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'id'
        );
    }

    public function paymentIntents(): BelongsToMany
    {
        return $this->belongsToMany(
            PaymentIntent::class,
            'stripe_subscription_payment_intent',
            'subscription_id',
            'payment_intent_id',
            'id',
            'id'
        );
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomerPaymentMethod::class,
            'stripe_subscription_payment_method',
            'subscription_id',
            'payment_method_id',
            'id',
            'id'
        );
    }
}
