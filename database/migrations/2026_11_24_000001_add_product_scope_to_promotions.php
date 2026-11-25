<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table): void {
            $table->boolean('applies_to_all_products')->default(true);
        });

        Schema::create('product_promotion', function (Blueprint $table): void {
            $table->integer('promotion_id');
            $table->integer('product_id')->index();
            $table->primary(['promotion_id', 'product_id']);
            $table->foreign('promotion_id')->references('id')->on('promotions')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
        Schema::table('promotions', function (Blueprint $table): void {
            $table->dropColumn('applies_to_all_products');
        });
    }
};
