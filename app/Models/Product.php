<?php

namespace App\Models;

use App\Models\Product\PaymentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property float $price price in dollars
 * @property int $integer_price price in cents
 * @property PaymentType $payment
 */
class Product extends Model
{
    use HasFactory;
    use AsSource;

    protected $fillable = [
        'title',
        'description',
        'price',
        'payment',
    ];

    protected $casts = [
        'payment' => PaymentType::class,
    ];

    public function integerPrice(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => floor($attributes['price'] * 100),
            set: fn(int $value) => ['price' => round($value * 0.01, 2)]
        );
    }
}
