<?php

namespace App\Http\Requests\Stripe\Payment\Intent;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class CreateIntentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $model = Product::class;

        return [
            'itemId' => "required|exists:{$model},id"
        ];
    }

    public function getProduct(): Product
    {
        return Product::query()
            ->where('id', $this->input('itemId'))
            ->first();
    }
}
