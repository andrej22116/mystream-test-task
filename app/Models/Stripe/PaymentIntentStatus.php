<?php

namespace App\Models\Stripe;

enum PaymentIntentStatus: string
{
    case Created = 'created';

    case Canceled = 'canceled';

    case Failed = 'failed';

    case Processing = 'processing';

    case Succeeded = 'succeeded';
}
