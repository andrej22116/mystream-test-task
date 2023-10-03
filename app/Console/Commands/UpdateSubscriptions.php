<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\Stripe\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UpdateSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var Collection<Subscription> $subscriptions */
        $subscriptions = Subscription::query()
            ->where('status', Subscription\SubscriptionStatus::Active->value)
            ->where('next_payment', '<', Carbon::now())
            ->with('user')
            ->get();

        $paymentService = new PaymentService();

        $subscriptions->each(fn(Subscription $subscription) => $paymentService->requestNextSubscriptionPayment($subscription));
    }
}
