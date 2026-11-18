<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        DB::table('categories')->insert(['id' => 1, 'name' => 'Danh mục kiểm thử']);
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->default(1)->constrained('categories');
            $table->string('name', 200);
            $table->double('price');
            $table->string('brand')->nullable();
            $table->string('composition', 500)->nullable();
            $table->string('used_for', 500)->nullable();
            $table->string('how_to_use', 500)->nullable();
            $table->string('contraindications', 500)->nullable();
            $table->string('side_effects', 500)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->integer('quantity')->default(0);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }
}
