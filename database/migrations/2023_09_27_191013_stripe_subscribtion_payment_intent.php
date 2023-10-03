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
        Schema::create('stripe_subscription_payment_intent', function (Blueprint $table) {
            $table->foreignId('subscription_id')
                ->constrained('subscriptions')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreignId('payment_intent_id')
                ->constrained('stripe_payment_intents')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_subscription_payment_intent');
    }
};
