<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\BalanceServiceInterface;
use App\Contracts\ChatServiceInterface;
use App\Contracts\FeedbackServiceInterface;
use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\Contracts\RecommendationServiceInterface;
use App\Contracts\Services\Geo\GeoCollectorServiceInterface;
use App\Services\BalanceService;
use App\Services\ChatService;
use App\Services\FeedbackService;
use App\Services\Geo\GeoCollectorService;
use App\Services\NlpSearchPreprocessingService;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\RecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(BalanceServiceInterface::class, BalanceService::class);
        $this->app->bind(ChatServiceInterface::class, ChatService::class);
        $this->app->bind(FeedbackServiceInterface::class, FeedbackService::class);

        $this->app->singleton(function (): GeoCollectorServiceInterface {
            return new GeoCollectorService(
                baseUrl: config('services.geo_collector.url'),
                timeout: config('services.geo_collector.timeout'),
            );
        });

        $this->app->bind(function (): RecommendationServiceInterface {
            return new RecommendationService(
                baseUrl: config('services.recommendation.url'),
            );
        });

        $this->app->bind(function (): NlpSearchPreprocessingServiceInterface {
            return new NlpSearchPreprocessingService(
                baseUrl: config('services.nlp_search_preprocessing.url'),
                timeout: config('services.nlp_search_preprocessing.timeout'),
            );
        });

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Date::use(CarbonImmutable::class);
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
