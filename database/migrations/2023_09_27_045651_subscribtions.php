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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id',)
                ->nullable()
                ->constrained('users')
                ->references('id')
                ->nullOnDelete();

            $table->foreignId('product_id',)
                ->nullable()
                ->constrained('products')
                ->references('id')
                ->cascadeOnDelete();

            $table->string('status', 64)
                ->default(\App\Models\Subscription\SubscriptionStatus::Created->value);

            $table->timestamp('last_payment')->nullable();
            $table->timestamp('next_payment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
