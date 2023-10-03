<?php

namespace App\Models\Stripe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentIntentError extends Model
{
    use HasFactory;

    protected $table = 'stripe_payment_intent_errors';

    protected $fillable = [
        'payment_intent_id',
        'location',
        'message',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function createNew(
        int|PaymentIntent $paymentIntent,
        string $location,
        string $message,
        array $details = []
    ): static {
        return static::create([
            'payment_intent_id' => is_int($paymentIntent) ? $paymentIntent : $paymentIntent->id,
            'location' => $location,
            'message' => $message,
            'details' => $details,
        ]);
    }
}
