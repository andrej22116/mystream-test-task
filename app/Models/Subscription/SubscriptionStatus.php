<?php

namespace App\Models\Subscription;

enum SubscriptionStatus: string
{
    /** Subscription was created */
    case Created = 'created';

    /** Subscription was paid */
    case Active = 'active';

    /** Waiting for next payment */
    case Waiting = 'waiting';

    /** Subscription is paused */
    case Paused = 'paused';

    /** Subscription was canceled because not paid */
    case Canceled = 'canceled';

    /** Subscription was stopped */
    case Stopped = 'stopped';
}
