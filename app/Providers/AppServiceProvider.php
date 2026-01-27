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
use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ResponseTemplateRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\SellerServiceInterface;
use App\Contracts\Services\Analytics\AnalyticsServiceInterface;
use App\Contracts\Services\CurrencyServiceInterface;
use App\Contracts\Services\Feedback\FeedbackableMapInterface;
use App\Contracts\Services\Geo\GeoCollectorServiceInterface;
use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Contracts\Services\Geo\ResponseTemplateServiceInterface;
use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\Contracts\UserPermissionServiceInterface;
use App\Models\Product;
use App\Models\User;
use App\Repositories\FeedbackRepository;
use App\Repositories\LocationRepository;
use App\Repositories\MessageRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ResponseTemplateRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\Analytics\AnalyticsService;
use App\Services\Analytics\CachedAnalyticsService;
use App\Services\BalanceService;
use App\Services\CachedRecommendationService;
use App\Services\ChatService;
use App\Services\Currency\CachedCurrencyService;
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
use App\Services\User\CachedUserPermissionService;
use App\Services\User\UserPermissionService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        OrderRepositoryInterface::class => OrderRepository::class,
        FeedbackRepositoryInterface::class => FeedbackRepository::class,
        ReviewRepositoryInterface::class => ReviewRepository::class,
        ResponseTemplateRepositoryInterface::class => ResponseTemplateRepository::class,
        LocationRepositoryInterface::class => LocationRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        MessageRepositoryInterface::class => MessageRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        TransactionRepositoryInterface::class => TransactionRepository::class,
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

        $this->app->bind(function (Application $app): UserPermissionServiceInterface {
            return new CachedUserPermissionService(
                $app->make(UserPermissionService::class)
            );
        });

        $this->app->bind(function (): RecommendationServiceInterface {
            return new CachedRecommendationService(
                new RecommendationService(
                    baseUrl: config('services.recommendation.url'),
                )
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
            return new CachedCurrencyService(
                new CurrencyService(
                    baseUrl: config('services.currency_rates.url'),
                    timeout: config('services.currency_rates.timeout'),
                )
            );
        });

        $this->app->singleton(function (Application $app): AnalyticsServiceInterface {
            return new CachedAnalyticsService(
                $app->make(AnalyticsService::class)
            );
        });

        $this->app->singleton(function (): FeedbackableMapInterface {
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

        $this->configureGates();
        $this->configureRateLimiting();
    }

    protected function configureGates(): void
    {
        Gate::define('viewApiDocs', function (?User $user): bool {
            if (! $this->app->isProduction()) {
                return true;
            }

            return $user instanceof User && in_array($user->email, [
                'test@example.com',
                'demo@example.com',
            ]);
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('orders', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('feedbacks', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('catalog', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('currency', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('activities', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
