<?php

use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use App\Services\Geo\ResponseTemplateService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('returns templates for user and caches result', function (): void {
    $user = User::factory()->create();
    ResponseTemplate::factory()->count(5)->create(['seller_id' => $user->id]);

    $service = new ResponseTemplateService;

    $cacheSpy = Cache::spy();

    $result = $service->getTemplatesForUser($user);

    expect($result)->toHaveCount(5);
    $cacheSpy->shouldHaveReceived('tags')->with(['response_templates'])->once();
});

it('creates a new template', function (): void {
    $user = User::factory()->create();
    $data = new ResponseTemplateData(
        id: null,
        sellerId: $user->id,
        title: 'Test Title',
        body: 'Test Body'
    );

    $service = new ResponseTemplateService;
    $service->storeTemplate($data);

    assertDatabaseHas('response_templates', [
        'seller_id' => $user->id,
        'title' => 'Test Title',
        'body' => 'Test Body',
    ]);
});

it('updates an existing template', function (): void {
    $template = ResponseTemplate::factory()->create();
    $data = new ResponseTemplateData(
        id: $template->id,
        sellerId: $template->seller_id,
        title: 'Updated Title',
        body: 'Updated Body'
    );

    $service = new ResponseTemplateService;
    $service->storeTemplate($data);

    assertDatabaseHas('response_templates', [
        'id' => $template->id,
        'title' => 'Updated Title',
        'body' => 'Updated Body',
    ]);
});

it('deletes a template', function (): void {
    $template = ResponseTemplate::factory()->create();
    $service = new ResponseTemplateService;

    $service->deleteTemplate($template);

    assertDatabaseMissing('response_templates', ['id' => $template->id]);
});
