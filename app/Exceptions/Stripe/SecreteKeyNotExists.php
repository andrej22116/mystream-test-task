<?php

namespace App\Exceptions\Stripe;

use Exception;

class SecreteKeyNotExists extends Exception
{
    protected $message = 'Stripe secrete key is not exists. Please, define the STRIPE_API_PRIVATE_KEY environment variable.';
}
