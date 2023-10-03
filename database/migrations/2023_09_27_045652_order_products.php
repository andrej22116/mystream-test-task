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
        Schema::create('order_products', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->constrained('orders')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->references('id')
                ->cascadeOnDelete();

            $table->integer('amount')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
