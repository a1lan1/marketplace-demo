<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Services\ProductService;
use App\Services\RecommendationService;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class ProductServiceIsolationTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_get_autocomplete_suggestions_preprocesses_query_and_returns_products(): void
    {
        // Arrange
        $searchQuery = '  Test Query  ';
        $processedQuery = 'test query';
        $limit = 5;

        $nlpSearchPreprocessingServiceMock = Mockery::mock(NlpSearchPreprocessingServiceInterface::class);
        $recommendationServiceMock = Mockery::mock(RecommendationService::class);

        $nlpSearchPreprocessingServiceMock
            ->shouldReceive('preprocessQuery')
            ->once()
            ->with($searchQuery)
            ->andReturn($processedQuery);

        // Mock the Product::search() method
        $mockedSearchBuilder = Mockery::mock();
        $mockedSearchBuilder->shouldReceive('take')->with($limit)->andReturnSelf();
        $mockedSearchBuilder->shouldReceive('get')->andReturn(collect([
            (object) ['id' => 1, 'name' => 'Test Product 1'],
            (object) ['id' => 2, 'name' => 'Test Product 2'],
        ]));

        // Temporarily replace the static `search` method on the Product model
        Mockery::mock('alias:App\Models\Product')
            ->shouldReceive('search')
            ->once()
            ->with($processedQuery)
            ->andReturn($mockedSearchBuilder);

        // Initialize Service AFTER mocking the alias
        $productService = new ProductService(
            $recommendationServiceMock,
            $nlpSearchPreprocessingServiceMock
        );

        // Act
        $result = $productService->getAutocompleteSuggestions($searchQuery, $limit);

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(2, $result->count());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
