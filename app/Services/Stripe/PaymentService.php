<?php

namespace App\Services\Stripe;

use App\Models\Order;
use App\Models\Product;
use App\Models\Stripe\Customer;
use App\Models\Stripe\CustomerPaymentMethod;
use App\Models\Stripe\PaymentIntent as InternalPaymentIntent;
use App\Models\Stripe\PaymentIntentStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\PaymentIntent;

class PaymentService
{
    protected null|User $user;
    protected null|Customer $customer;

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $customer = $user?->customer;
        if ($user && !$customer) {
            $customer = Customer::createNew($user);
        }

        $this->user = $user;
        $this->customer = $customer;
    }

    /**
     * Create new payment intent which based on type of product
     * @param Product $product
     * @return PaymentIntent
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function newPayment(Product $product): PaymentIntent
    {
        return match ($product->payment) {
            Product\PaymentType::Once => $this->createOrder($product),
            Product\PaymentType::Monthly => $this->createSubscription($product),
        };
    }

    /**
     * Create new order payment intent
     * @param Product $product
     * @return PaymentIntent
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createOrder(Product $product): PaymentIntent
    {
        $stripeClient = stripe_client();
        $paymentIntent = $stripeClient->paymentIntents->create(
            array_filter([
                'amount' => $product->integer_price,
                'currency' => 'usd',
                'setup_future_usage' => 'on_session',
                'use_stripe_sdk' => true,
                'customer' => $this->customer?->customer_id,
                'metadata' => [
                    'payment' => $product->payment->value,
                ],
            ])
        );

        DB::transaction(function () use ($product, $paymentIntent) {
            /** @var Order $order */
            $order = Order::create([
                'user_id' => $this->user?->id
            ]);

            $internalPaymentIntent = InternalPaymentIntent::createNew($paymentIntent);

            $order->products()->attach($product);
            $order->paymentIntents()->attach($internalPaymentIntent);
        });

        return $paymentIntent;
    }

    /**
     * Create new subscription payment intent
     * @param Product $product
     * @return PaymentIntent
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createSubscription(Product $product): PaymentIntent
    {
        if (!$this->user || !$this->customer) {
            throw new \Exception('Subscription can\'t be created while user is not authorized');
        }

        $stripeClient = stripe_client();
        $paymentIntent = $stripeClient->paymentIntents->create(
            array_filter([
                'amount' => $product->integer_price,
                'currency' => 'usd',
                'setup_future_usage' => 'off_session',
                'use_stripe_sdk' => true,
                'automatic_payment_methods' => [
                    'enabled' => 'true',
                ],
                'customer' => $this->customer?->customer_id,
                'metadata' => [
                    'payment' => $product->payment->value,
                ],
            ])
        );

        DB::transaction(function () use ($product, $paymentIntent) {
            /** @var Subscription $subscription */
            $subscription = Subscription::create([
                'user_id' => $this->user?->id,
                'product_id' => $product->id,
            ]);

            $internalPaymentIntent = InternalPaymentIntent::createNew($paymentIntent);

            $subscription->paymentIntents()->attach($internalPaymentIntent);
        });

        return $paymentIntent;
    }

    public function processPaymentIntentEvent(Event $event): void
    {
        $paymentIntent = PaymentIntent::constructFrom($event->data->object);

        /** @var InternalPaymentIntent $internalPaymentIntent */
        $internalPaymentIntent = InternalPaymentIntent::byPaymentIntentId($paymentIntent->id)->first();
        if (!$internalPaymentIntent) {
            Log::error('Unknown payment intent ID', [
                'payment_intent' => $event->data->object
            ]);

            throw new \Exception('Unexpected PaymentIntent!');
        }

        switch ($event->type) {
            case 'payment_intent.canceled':
                $internalPaymentIntent->status = PaymentIntentStatus::Canceled;
                break;

            case 'payment_intent.payment_failed':
                $internalPaymentIntent->status = PaymentIntentStatus::Failed;
                break;

            case 'payment_intent.processing':
                $internalPaymentIntent->status = PaymentIntentStatus::Processing;
                break;

            case 'payment_intent.succeeded':
                $internalPaymentIntent->status = PaymentIntentStatus::Succeeded;
                break;

            default:
                Log::warning('Unknown payment intent event type', [
                    'event type' => $event->type
                ]);
        }

        $internalPaymentIntent->save();

        $this->processPaymentIntentUpdate($paymentIntent, $internalPaymentIntent);
    }

    /**
     * Find out the target of payment_intent and process it
     * @param PaymentIntent $paymentIntent
     * @param InternalPaymentIntent $internalPaymentIntent
     * @return void
     * @throws \Exception
     */
    public function processPaymentIntentUpdate(
        PaymentIntent $paymentIntent,
        InternalPaymentIntent $internalPaymentIntent
    ): void {
        $paymentType = $this->getPaymentType($internalPaymentIntent);

        match ($paymentType) {
            Product\PaymentType::Once => $this->processOrderPaymentIntentUpdate($paymentIntent, $internalPaymentIntent),
            Product\PaymentType::Monthly => $this->processSubscriptionPaymentIntentUpdate(
                $paymentIntent,
                $internalPaymentIntent
            ),
        };
    }

    /**
     * Process payment intent of order
     * @param PaymentIntent $paymentIntent
     * @param InternalPaymentIntent $internalPaymentIntent
     * @return void
     */
    public function processOrderPaymentIntentUpdate(
        PaymentIntent $paymentIntent,
        InternalPaymentIntent $internalPaymentIntent
    ): void {
        Log::info('Payment intent status of order was updated', [
            'internal_payment_intent' => $internalPaymentIntent
        ]);
    }

    /**
     * Process payment intent of subscription
     * @param PaymentIntent $paymentIntent
     * @param InternalPaymentIntent $internalPaymentIntent
     * @return void
     */
    public function processSubscriptionPaymentIntentUpdate(
        PaymentIntent $paymentIntent,
        InternalPaymentIntent $internalPaymentIntent
    ): void {
        Log::info('Payment intent status of subscription was updated', [
            'internal_payment_intent' => $internalPaymentIntent
        ]);

        $subscription = $internalPaymentIntent->getSubscription();
        if (!$subscription) {
            Log::error('Payment intent is not connected with subscription!', [
                'payment_intent' => $paymentIntent,
                'internal_payment_intent' => $internalPaymentIntent,
            ]);
            return;
        }

        if ($internalPaymentIntent->status !== PaymentIntentStatus::Succeeded) {
            $subscription->block();
            return;
        }

        $paymentMethodId = $paymentIntent->payment_method;
        if (!$paymentMethodId) {
            Log::error('Payment intent doesn\'t contain payment method ID!', [
                'payment_intent' => $paymentIntent,
                'internal_payment_intent' => $internalPaymentIntent,
            ]);
            return;
        }

        $customer = $subscription->user?->customer;
        if (!$customer) {
            Log::error('Undefined user or customer!', [
                'subscription' => $subscription,
                'user' => $subscription->user,
                'customer' => $subscription->user?->customer,
            ]);
            return;
        }

        /** @var null|CustomerPaymentMethod $paymentMethod */
        $paymentMethod = CustomerPaymentMethod::query()
            ->whereStripePaymentMethod($paymentMethodId)
            ->whereCustomer($customer)
            ->first();

        if (!$paymentMethod) {
            $paymentMethod = CustomerPaymentMethod::createNew($customer, $paymentIntent);
            $subscription->paymentMethods()->attach($paymentMethod);
        }

        $subscription->last_payment = new \DateTime();
        $subscription->next_payment = $this->calculateNextPaymentDate($subscription->last_payment);

        $subscription->activate();
    }

    public function requestNextSubscriptionPayment(Subscription $subscription): void
    {
        $customer = $subscription->user?->customer;
        /** @var CustomerPaymentMethod $paymentMethod */
        $paymentMethod = $subscription->paymentMethods->first();

        $stripeClient = stripe_client();

        try {
            $paymentIntent = $stripeClient->paymentIntents->create([
                'amount' => $subscription->product->integer_price,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'customer' => $customer->customer_id,
                'payment_method' => $paymentMethod->payment_method_id,
                'off_session' => true,
                'confirm' => true,
            ]);

            $internalPaymentIntent = InternalPaymentIntent::createNew($paymentIntent);

            $subscription->paymentIntents()->attach($internalPaymentIntent);
        } catch (\Stripe\Exception\CardException $e) {
            $subscription->block();
        }
    }

    /**
     * Try to find out or guess payment type
     * @param InternalPaymentIntent $paymentIntent
     * @return Product\PaymentType
     * @throws \Exception
     */
    protected function getPaymentType(InternalPaymentIntent $paymentIntent): Product\PaymentType
    {
        /** @var string $paymentType */
        $paymentType = $paymentIntent->metadata['payment'] ?? null;
        if ($paymentType) {
            return Product\PaymentType::from($paymentType);
        }

        $internalPaymentIntent = InternalPaymentIntent::query()
            ->select(['opi.order_id', 'spi.subscription_id'])
            ->leftJoin(
                'stripe_order_payment_intent as opi',
                'opi.payment_intent_id',
                '=',
                'stripe_payment_intents.id'
            )
            ->leftJoin(
                'stripe_subscription_payment_intent as spi',
                'spi.payment_intent_id',
                '=',
                'stripe_payment_intents.id'
            )
            ->where('stripe_payment_intents.id', $paymentIntent->id)
            ->first();

        if (!$internalPaymentIntent) {
            Log::error('Unexpected PaymentIntent!', [
                'payment_intent' => $paymentIntent
            ]);
            throw new \Exception('Unexpected PaymentIntent!');
        }

        if ($internalPaymentIntent?->order_id) {
            return Product\PaymentType::Once;
        } elseif ($internalPaymentIntent?->subscription_id) {
            return Product\PaymentType::Monthly;
        }

        return Product\PaymentType::Once;
    }

    protected function calculateNextPaymentDate(\DateTime $dateTime): \DateTime
    {
        $carbonDateTime = Carbon::create($dateTime);

        app()->isProduction()
            ? $carbonDateTime->addMonthNoOverflow()
            : $carbonDateTime->addRealMinutes(2);

        return $carbonDateTime->toDateTime();
    }
}
