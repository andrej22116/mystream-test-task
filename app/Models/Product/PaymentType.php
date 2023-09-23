<?php

namespace App\Models\Product;

enum PaymentType: string
{
    case Once = 'once';
    case Monthly = 'monthly';
}
