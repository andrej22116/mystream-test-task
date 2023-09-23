<?php

namespace App\Http\Requests\Stripe\Hook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookRequest extends FormRequest
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
        return [];
    }

    /**
     * Make Stripe Event instance
     * @param string $webhookSecrete .ENV variable name or real secrete. If there is local environment, will be used local key by default if it's possible
     * @return Event
     * @throws SignatureVerificationException
     */
    public function getEvent(string $webhookSecrete): Event
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $this->header('stripe-signature', '');
        $webhookSecrete = $this->getSecrete($webhookSecrete);

        Log::debug('test-webhook', [
            'payload' => $payload,
            'sigHeader' => $sigHeader,
            'webhookSecrete' => $webhookSecrete,
        ]);

        return Webhook::constructEvent($payload, $sigHeader, $webhookSecrete);
    }

    protected function getSecrete(string $webhookSecrete): string
    {
        $key = app()->isProduction() ? $webhookSecrete : 'STRIPE_WEBHOOK_LOCAL_SECRETE';
        return env($key, $webhookSecrete);
    }
}
