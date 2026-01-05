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
use App\Contracts\SellerServiceInterface;
use App\Contracts\Services\CurrencyServiceInterface;
use App\Contracts\Services\Geo\GeoCollectorServiceInterface;
use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Contracts\Services\Geo\ResponseTemplateServiceInterface;
use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\Models\Product;
use App\Models\User;
use App\Services\BalanceService;
use App\Services\ChatService;
use App\Services\Currency\CurrencyService;
use App\Services\Feedback\CachedFeedbackService;
use App\Services\Feedback\FeedbackableMap;
use App\Services\Feedback\FeedbackService;
use App\Services\Geo\CachedReviewService;
use App\Services\Geo\GeoCollectorService;
use App\Services\Geo\LocationService;
use App\Services\Geo\ResponseTemplateService;
use App\Services\Geo\ReviewService;
use App\Services\NlpSearchPreprocessingService;
use App\Services\OrderService;
use App\Services\Product\CachedProductService;
use App\Services\Product\ProductService;
use App\Services\RecommendationService;
use App\Services\SellerService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
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

        $this->app->bind(function (Application $app): ProductServiceInterface {
            return new CachedProductService(
                $app->make(ProductService::class)
            );
        });

        $this->app->bind(function (Application $app): FeedbackServiceInterface {
            return new CachedFeedbackService(
                $app->make(FeedbackService::class)
            );
        });

        $this->app->bind(function (Application $app): ReviewServiceInterface {
            return new CachedReviewService(
                $app->make(ReviewService::class)
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

        $this->app->singleton(function (): GeoCollectorServiceInterface {
            return new GeoCollectorService(
                baseUrl: config('services.geo_collector.url'),
                timeout: config('services.geo_collector.timeout'),
            );
        });

        $this->app->singleton(function (): CurrencyServiceInterface {
            return new CurrencyService(
                baseUrl: config('services.currency_rates.url'),
                timeout: config('services.currency_rates.timeout'),
            );
        });

        $this->app->singleton(function (): FeedbackableMap {
            return new FeedbackableMap([
                'product' => Product::class,
                'seller' => User::class,
            ]);
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
