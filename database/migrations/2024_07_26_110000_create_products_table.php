<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->comment('Seller ID')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->bigInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->json('image_tags')->nullable();
            $table->string('image_moderation_status')->nullable()->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
