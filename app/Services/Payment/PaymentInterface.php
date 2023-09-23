<?php

namespace App\Services\Payment;

interface PaymentInterface
{
    /**
     * The name of payment service
     * @return string
     */
    public function getName(): string;


}
