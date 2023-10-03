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
        Schema::create('stripe_customer_payment_methods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('stripe_customers')
                ->references('id')
                ->cascadeOnDelete();

            $table->string('payment_method_id', 1024);
            $table->string('type', 64);
            $table->json('details');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_customer_payment_methods');
    }
};
