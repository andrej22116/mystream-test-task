<?php

namespace App\Orchid\Screens\Payment;

use App\Models\Stripe\PaymentIntentStatus;
use App\Models\Subscription;
use App\Models\Subscription\SubscriptionStatus;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class SubscribeListScreen extends Screen
{
    protected string $name = 'Payments';
    protected string $description = 'List of payments';

    public function __construct()
    {
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'payments' => Subscription::query()
                ->with('product')
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
                TD::make('Product')->render(function (Subscription $subscription) {
                    return $subscription->product->title;
                }),

                TD::make('Price')->render(function (Subscription $subscription) {
                    return '$' . $subscription->product->price;
                }),

                TD::make('User')->render(function (Subscription $subscription) {
                    return $subscription->user?->name;
                }),

                TD::make('last_payment')->usingComponent(DateTimeSplit::class),
                TD::make('next_payment')->usingComponent(DateTimeSplit::class),
                TD::make('created_at')->usingComponent(DateTimeSplit::class),
                TD::make('updated_at')->usingComponent(DateTimeSplit::class),

                TD::make('Payment')->render(function (Subscription $subscription) {
                    return match ($subscription->paymentIntents?->last()?->status) {
                        PaymentIntentStatus::Created => '<div class="bg-info rounded text-white text-center p-1">Created</div>',

                        PaymentIntentStatus::Failed => '<div class="bg-danger rounded text-white text-center p-1">Failed</div>',

                        PaymentIntentStatus::Succeeded => '<div class="bg-success rounded text-white text-center p-1">Succeeded</div>',

                        PaymentIntentStatus::Processing => '<div class="bg-secondary rounded text-white text-center p-1">Processing</div>',

                        PaymentIntentStatus::Canceled => '<div class="bg-warning rounded text-white text-center p-1">Canceled</div>',

                        default => 'Unknown'
                    };
                }),

                TD::make('Status')->render(function (Subscription $subscription) {
                    return match ($subscription->status) {
                        SubscriptionStatus::Created => '<div class="bg-info rounded text-white text-center p-1">Created</div>',

                        SubscriptionStatus::Canceled => '<div class="bg-danger rounded text-white text-center p-1">Stopped</div>',

                        SubscriptionStatus::Paused => '<div class="bg-warning rounded text-white text-center p-1">Stopped</div>',

                        SubscriptionStatus::Active => '<div class="bg-success rounded text-white text-center p-1">Active</div>',

                        SubscriptionStatus::Stopped => '<div class="bg-secondary rounded text-white text-center p-1">Canceled</div>',

                        default => 'Unknown'
                    };
                }),

                TD::make('')->render(function (Subscription $subscription) {
                    $btn = Button::make('Stop');
                    if ($subscription->status !== Subscription\SubscriptionStatus::Stopped) {
                        $btn->type(Color::DANGER)
                            ->method('stopSubscription', ['id' => $subscription->id]);
                    } else {
                        $btn->hidden();
                    }

                    return $btn;
                }),
            ])
        ];
    }

    /**
     * Stop subscription
     * @param Request $request
     * @return void
     */
    public function stopSubscription(Request $request): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::query()->where('id', $request->get('id'))->first();

        $subscription->stop();
    }
}
