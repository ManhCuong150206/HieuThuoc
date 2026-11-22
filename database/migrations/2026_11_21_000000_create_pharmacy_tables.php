<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Match group10.sql (2026-11-21); existing imported tables are left intact.
    public function up(): void
    {
        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('username', 50)->unique('uq_accounts_username');
                $table->string('password', 255);
                $table->string('role', 10)->default('CUSTOMER');
                $table->boolean('is_active')->default(true);
                $table->date('created_at')->default(DB::raw('(CURRENT_DATE)'));
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('account_id')->unique('uq_users_account');
                $table->string('full_name', 150);
                $table->string('email', 254)->unique('uq_users_email');
                $table->string('phone', 20);
                $table->string('address', 255)->nullable();
                $table->foreign('account_id', 'fk_users_account')->references('id')->on('accounts');
            });
        }

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('name', 100)->unique('uq_categories_name');
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('category_id');
                $table->string('name', 200);
                $table->double('price');
                $table->string('brand', 100)->nullable();
                $table->string('composition', 500)->nullable();
                $table->string('used_for', 500)->nullable();
                $table->string('how_to_use', 500)->nullable();
                $table->string('contraindications', 500)->nullable();
                $table->string('side_effects', 500)->nullable();
                $table->string('image_url', 500)->nullable();
                $table->integer('quantity')->default(0);
                $table->date('expiry_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreign('category_id', 'fk_products_category')->references('id')->on('categories');
            });
        }

        if (! Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('user_id');
                $table->integer('product_id');
                $table->integer('quantity');
                $table->unique(['user_id', 'product_id'], 'uq_cart_items_user_product');
                $table->foreign('user_id', 'fk_cart_items_user')->references('id')->on('users');
                $table->foreign('product_id', 'fk_cart_items_product')->references('id')->on('products');
            });
        }

        if (! Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('name', 150);
                $table->string('description', 500)->nullable();
                $table->integer('discount_percent');
                $table->date('starts_at');
                $table->date('ends_at');
                $table->boolean('is_active')->default(true);
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('order_code', 30)->unique('uq_orders_order_code');
                $table->integer('user_id');
                $table->string('recipient_name', 150);
                $table->string('recipient_phone', 20);
                $table->string('shipping_address', 255);
                $table->integer('subtotal');
                $table->integer('promotion_id')->nullable();
                $table->integer('discount_amount')->default(0);
                $table->integer('shipping_fee')->default(0);
                $table->integer('total_amount');
                $table->string('payment_method', 20);
                $table->string('status', 20)->default('PENDING');
                $table->date('created_at')->default(DB::raw('(CURRENT_DATE)'));
                $table->foreign('user_id', 'fk_orders_user')->references('id')->on('users');
                $table->foreign('promotion_id', 'fk_orders_promotion')->references('id')->on('promotions');
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('order_id');
                $table->integer('product_id');
                $table->string('product_name', 200);
                $table->integer('unit_price');
                $table->integer('quantity');
                $table->foreign('order_id', 'fk_order_items_order')->references('id')->on('orders');
                $table->foreign('product_id', 'fk_order_items_product')->references('id')->on('products');
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('order_id');
                $table->string('method', 20);
                $table->integer('amount');
                $table->string('status', 20);
                $table->date('created_at')->default(DB::raw('(CURRENT_DATE)'));
                $table->date('paid_at')->nullable();
                $table->foreign('order_id', 'fk_payments_order')->references('id')->on('orders');
            });
        }

        if (! Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table): void {
                $table->integer('user_id');
                $table->integer('product_id');
                $table->date('created_at')->default(DB::raw('(CURRENT_DATE)'));
                $table->primary(['user_id', 'product_id']);
                $table->foreign('user_id', 'fk_favorites_user')->references('id')->on('users');
                $table->foreign('product_id', 'fk_favorites_product')->references('id')->on('products');
            });
        }

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('order_item_id')->unique('uq_reviews_order_item');
                $table->integer('rating');
                $table->string('comment', 500)->nullable();
                $table->string('status', 20);
                $table->date('created_at')->default(DB::raw('(CURRENT_DATE)'));
                $table->foreign('order_item_id', 'fk_reviews_order_item')->references('id')->on('order_items');
            });
        }

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('author_account_id');
                $table->string('title', 200);
                $table->string('content', 500);
                $table->string('cover_url', 500)->nullable();
                $table->string('status', 20);
                $table->date('published_at')->nullable();
                $table->foreign('author_account_id', 'fk_posts_author_account')->references('id')->on('accounts');
            });
        }
    }

    public function down(): void
    {
        // Tables may predate Laravel's migration history after an SQL import.
        throw new RuntimeException('Pharmacy baseline cannot be rolled back automatically because it may contain imported data.');
    }
};
