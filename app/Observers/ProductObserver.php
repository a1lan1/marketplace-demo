<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->clearCache();
    }

    public function updated(Product $product): void
    {
        $this->clearCache();
    }

    public function deleted(Product $product): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::tags(['products'])->flush();
    }
}
