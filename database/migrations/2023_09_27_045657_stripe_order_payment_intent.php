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
        Schema::create('stripe_order_payment_intent', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->unique()
                ->constrained('orders')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreignId('payment_intent_id')
                ->nullable()
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
        Schema::dropIfExists('stripe_order_payment_intent');
    }
};
