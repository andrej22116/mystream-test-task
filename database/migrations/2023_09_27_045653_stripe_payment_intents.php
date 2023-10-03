<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stripe_payment_intents', function (Blueprint $table) {
            $table->id();

            $table->string('intent_id', 1024);
            $table->string('status', 64)->default(\App\Models\Stripe\PaymentIntentStatus::Created->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_payment_intents');
    }
};
