<?php

use App\Contracts\Repositories\ResponseTemplateRepositoryInterface;
use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use App\Services\Geo\ResponseTemplateService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->repository = Mockery::mock(ResponseTemplateRepositoryInterface::class);
    $this->service = new ResponseTemplateService($this->repository);
});

it('returns templates for user and caches result', function (): void {
    $user = User::factory()->make(['id' => 1]);
    $templates = new Collection([new ResponseTemplate]);

    $this->repository->shouldReceive('getForUser')
        ->once()
        ->with($user)
        ->andReturn($templates);

    $cacheSpy = Cache::spy();

    $result = $this->service->getTemplatesForUser($user);

    expect($result)->toHaveCount(1);
    $cacheSpy->shouldHaveReceived('tags')->with(['response_templates'])->once();
});

it('creates a new template', function (): void {
    $data = new ResponseTemplateData(
        id: null,
        sellerId: 1,
        title: 'Test Title',
        body: 'Test Body'
    );
    $template = new ResponseTemplate;

    $this->repository->shouldReceive('store')
        ->once()
        ->with($data)
        ->andReturn($template);

    $result = $this->service->storeTemplate($data);

    expect($result)->toBe($template);
});

it('deletes a template', function (): void {
    $template = new ResponseTemplate;

    $this->repository->shouldReceive('delete')
        ->once()
        ->with($template);

    $this->service->deleteTemplate($template);
});
