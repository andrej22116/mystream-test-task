<?php

namespace App\Orchid\Screens\Payment;

use App\Models\Order;
use App\Models\Product;
use App\Models\Stripe\PaymentIntent;
use App\Models\Stripe\PaymentIntentStatus;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PaymentListScreen extends Screen
{
    protected string $name = 'Payments';
    protected string $description = 'List of payments';

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'payments' => Order::query()
                ->with('products')
                ->with('paymentIntents')
                ->with('user')
                ->orderByDesc('id')
                ->paginate(20),
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('payments', [
                TD::make('Products')->render(function (Order $order) {
                    return $order->products->pluck('title')->implode('<br>');
                }),

                TD::make('Price')->render(function (Order $order) {
                    return '$' . $order->products->reduce(
                            fn(float $amount, Product $product) => $amount + $product->price,
                            0
                        );
                }),

                TD::make('User')->render(function (Order $order) {
                    return $order->user?->name ?? 'Guest';
                }),

                TD::make('created_at'),
                TD::make('updated_at'),

                TD::make('Status')->render(function (Order $order) {
                    return match ($order->paymentIntents?->first()?->status) {
                        PaymentIntentStatus::Created => '<div class="bg-info rounded text-white text-center p-1">Created</div>',

                        PaymentIntentStatus::Failed => '<div class="bg-danger rounded text-white text-center p-1">Failed</div>',

                        PaymentIntentStatus::Succeeded => '<div class="bg-success rounded text-white text-center p-1">Succeeded</div>',

                        PaymentIntentStatus::Processing => '<div class="bg-secondary rounded text-white text-center p-1">Processing</div>',

                        PaymentIntentStatus::Canceled => '<div class="bg-warning rounded text-white text-center p-1">Canceled</div>',

                        default => 'Unknown'
                    };
                })
            ])
        ];
    }
}
