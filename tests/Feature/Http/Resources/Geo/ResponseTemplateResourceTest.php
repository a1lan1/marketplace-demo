<?php

use App\Http\Resources\Geo\ResponseTemplateResource;
use App\Models\ResponseTemplate;
use Illuminate\Http\Request;

it('formats response template data correctly', function (): void {
    $template = ResponseTemplate::factory()->create();

    $resource = new ResponseTemplateResource($template);
    $request = new Request;
    $result = $resource->toArray($request);

    expect($result['id'])->toBe($template->id)
        ->and($result['title'])->toBe($template->title)
        ->and($result['body'])->toBe($template->body);
});
