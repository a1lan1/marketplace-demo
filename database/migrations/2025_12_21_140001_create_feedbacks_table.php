<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('feedbackable');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('sentiment')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();

            // Prevent duplicate reviews from same user for same entity
            $table->unique(['user_id', 'feedbackable_id', 'feedbackable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
