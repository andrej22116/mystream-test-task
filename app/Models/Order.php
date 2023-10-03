<?php

namespace App\Models;

use App\Models\Stripe\PaymentIntent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Orchid\Screen\AsSource;

/**
 * @property-read null|User $user
 * @property-read Collection<Product> $products
 * @property-read Collection<PaymentIntent> $paymentIntents
 */
class Order extends Model
{
    use HasFactory;
    use AsSource;

    protected $fillable = [
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'order_products',
            'order_id',
            'product_id',
            'id',
            'id'
        );
    }

    public function paymentIntents(): BelongsToMany
    {
        return $this->belongsToMany(
            PaymentIntent::class,
            'stripe_order_payment_intent',
            'payment_intent_id',
            'order_id',
            'id',
            'id'
        );
    }
}
