<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\BalanceServiceInterface;
use App\Contracts\ChatServiceInterface;
use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\RecommendationServiceInterface;
use App\Contracts\SellerServiceInterface;
use App\Contracts\Services\Geo\GeoCollectorServiceInterface;
use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Contracts\Services\Geo\ResponseTemplateServiceInterface;
use App\Services\BalanceService;
use App\Services\ChatService;
use App\Services\Geo\GeoCollectorService;
use App\Services\Geo\LocationService;
use App\Services\Geo\ResponseTemplateService;
use App\Services\NlpSearchPreprocessingService;
use App\Services\OrderService;
use App\Services\RecommendationService;
use App\Services\SellerService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        OrderServiceInterface::class => OrderService::class,
        BalanceServiceInterface::class => BalanceService::class,
        ChatServiceInterface::class => ChatService::class,
        LocationServiceInterface::class => LocationService::class,
        SellerServiceInterface::class => SellerService::class,
        ResponseTemplateServiceInterface::class => ResponseTemplateService::class,
    ];

    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

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

        $this->app->singleton(function (): GeoCollectorServiceInterface {
            return new GeoCollectorService(
                baseUrl: config('services.geo_collector.url'),
                timeout: config('services.geo_collector.timeout'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Date::use(CarbonImmutable::class);
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());
    }
}
