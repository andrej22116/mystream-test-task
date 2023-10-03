<?php

namespace App\Models\Stripe;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;


/**
 * @property string $user_id
 * @property string $customer_id
 * @property-read User $user
 */
class Customer extends Model
{
    use HasFactory;

    protected $table = 'stripe_customers';

    protected \Stripe\Customer|null $stripeCustomerObj = null;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'customer_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    /**
     * Create new Stripe customer and associate in with local instance
     */
    public static function createNew(User $user): static
    {
        /** @var StripeClient $stripeClient */
        $stripeClient = app(StripeClient::class);

        try {
            $stripeCustomer = $stripeClient->customers->create([
                'name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe customer did not be created', [
                'exception' => $e,
            ]);

            throw new \Exception($e->getMessage());
        }

        $instance = parent::create([
            'user_id' => $user->id,
            'customer_id' => $stripeCustomer->id,
        ]);

        $instance->stripeCustomerObj = $stripeCustomer;

        return $instance;
    }

    /**
     * Get Stripe customer obj
     * @return \Stripe\Customer|null
     */
    public function getRawCustomer(): null|\Stripe\Customer
    {
        if ( $this->stripeCustomerObj ) {
            return $this->stripeCustomerObj;
        }

        /** @var StripeClient $stripeClient */
        $stripeClient = app(StripeClient::class);

        try {
            $this->stripeCustomerObj = $stripeClient->customers->retrieve($this->customer_id);
        } catch (ApiErrorException $e) {
            Log::warning("Customer {$this->customer_id} not exists in Stripe", [
                'exception' => $e,
            ]);
        }

        return $this->stripeCustomerObj;
    }
}
