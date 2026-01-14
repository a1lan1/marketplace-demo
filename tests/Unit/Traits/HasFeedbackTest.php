<?php

declare(strict_types=1);

use App\Models\Feedback;
use App\Traits\HasFeedback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('test_models', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });
});

it('returns morph many for feedbacks relation', function (): void {
    $model = new class extends Model
    {
        use HasFeedback;
    };

    expect($model->feedbacks())->toBeInstanceOf(MorphMany::class);
});

it('calculates average rating correctly', function (): void {
    $model = new class extends Model
    {
        use HasFeedback;

        protected $table = 'test_models';
    };
    $model->save();

    Feedback::factory()->create(['feedbackable_id' => $model->id, 'feedbackable_type' => get_class($model), 'rating' => 4]);
    Feedback::factory()->create(['feedbackable_id' => $model->id, 'feedbackable_type' => get_class($model), 'rating' => 5]);

    expect($model->averageRating())->toBe(4.5);
});

it('returns correct reviews count', function (): void {
    $model = new class extends Model
    {
        use HasFeedback;

        protected $table = 'test_models';
    };
    $model->save();

    Feedback::factory()->count(3)->create(['feedbackable_id' => $model->id, 'feedbackable_type' => get_class($model)]);

    expect($model->reviewsCount())->toBe(3);
});
