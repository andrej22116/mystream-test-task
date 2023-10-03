<?php

namespace App\Models\Stripe;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static Builder whereCustomer(int|Customer $customer)
 * @method static Builder whereStripePaymentMethod(string $paymentMethodId)
 *
 * @property-read int $id
 * @property string $type
 * @property array $details
 *
 * @property-read Customer $customer
 */
class CustomerPaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'stripe_customer_payment_methods';

    protected $fillable = [
        'customer_id',
        'payment_method_id',
        'type',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function scopeWhereCustomer(Builder $query, int|Customer $customer): Builder
    {
        $customerId = is_int($customer) ? $customer : $customer->customer_id;
        return $query->where('customer_id', $customerId);
    }

    public function scopeWhereStripePaymentMethod(Builder $query, string $paymentMethodId): Builder
    {
        return $query->where('payment_method_id',$paymentMethodId);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(
            Customer::class,
            'customer_id',
            'id'
        );
    }

    public static function createNew(Customer $customer, \Stripe\PaymentIntent $paymentIntent): null|static
    {
        $paymentMethodId = $paymentIntent->payment_method;
        if (!$paymentMethodId) {
            return null;
        }

        $paymentMethodType = collect($paymentIntent->payment_method_types)->first();

        $paymentMethodDetails = $paymentMethodType
            ? $paymentIntent->payment_method_options[$paymentMethodType]
            : [];

        return static::create([
            'customer_id' => $customer->id,
            'payment_method_id' => $paymentMethodId,
            'type' => $paymentMethodType,
            'details' => $paymentMethodDetails,
        ]);
    }
}
