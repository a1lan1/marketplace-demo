<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImageAnalysisResult implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $productId,
        public array $analysisResults
    ) {}

    public function handle(): void
    {
        /** @var Product|null $product */
        $product = Product::find($this->productId);

        if (! $product) {
            Log::warning(sprintf('Product with ID %d not found for image analysis results.', $this->productId));

            return;
        }

        // Update product with analysis results
        $product->image_tags = $this->analysisResults['tags'] ?? [];
        $product->image_moderation_status = $this->analysisResults['moderation_status'] ?? 'pending';
        $product->save();

        Log::info(sprintf('Updating product %d with image analysis results: ', $product->id).json_encode($this->analysisResults));
    }
}
