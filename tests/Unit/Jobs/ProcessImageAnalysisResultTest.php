<?php

use App\Jobs\ProcessImageAnalysisResult;
use App\Models\Product;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\assertDatabaseCount;

it('can be dispatched', function (): void {
    Bus::fake();

    dispatch(new ProcessImageAnalysisResult(1, []));

    Bus::assertDispatched(ProcessImageAnalysisResult::class);
});

it('updates product with analysis results', function (): void {
    $product = Product::factory()->create();
    $analysisResults = [
        'tags' => ['tag1', 'tag2'],
        'moderation_status' => 'approved',
    ];

    $job = new ProcessImageAnalysisResult($product->id, $analysisResults);
    $job->handle();

    $product->refresh();

    expect($product->image_tags)->toBe(['tag1', 'tag2'])
        ->and($product->image_moderation_status)->toBe('approved');
});

it('does nothing if product not found', function (): void {
    $analysisResults = [
        'tags' => ['tag1', 'tag2'],
        'moderation_status' => 'approved',
    ];

    $job = new ProcessImageAnalysisResult(999, $analysisResults);
    $job->handle();

    assertDatabaseCount('products', 0);
});
