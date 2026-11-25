<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->string('admin_reply', 500)->nullable();
        });

        Schema::create('post_comments', function (Blueprint $table): void {
            $table->id();
            $table->integer('post_id');
            $table->integer('account_id');
            $table->string('body', 500);
            $table->string('admin_reply', 500)->nullable();
            $table->timestamps();
            $table->index(['post_id', 'id']);
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_comments');

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('admin_reply');
        });
    }
};
