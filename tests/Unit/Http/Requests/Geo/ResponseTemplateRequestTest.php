<?php

use App\DTO\Geo\ResponseTemplateData;
use App\Http\Requests\Geo\ResponseTemplateRequest;
use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;

it('has correct validation rules', function (): void {
    $request = new ResponseTemplateRequest;

    expect($request->rules())->toBe([
        'title' => ['required', 'string', 'max:255'],
        'body' => ['required', 'string'],
    ]);
});

it('creates a dto for creating a template', function (): void {
    $user = User::factory()->create();
    $data = [
        'title' => 'Test Title',
        'body' => 'Test Body',
    ];

    $request = new ResponseTemplateRequest([], $data);
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make($data, $request->rules());
    $validator->validate();

    $request->setValidator($validator);

    $dto = $request->toDto();

    expect($dto)->toBeInstanceOf(ResponseTemplateData::class)
        ->and($dto->title)->toBe('Test Title')
        ->and($dto->body)->toBe('Test Body')
        ->and($dto->sellerId)->toBe($user->id)
        ->and($dto->id)->toBeNull();
});

it('creates a dto for updating a template', function (): void {
    $template = ResponseTemplate::factory()->create();
    $user = $template->seller;
    $data = [
        'title' => 'Updated Title',
        'body' => 'Updated Body',
    ];

    $request = new ResponseTemplateRequest([], $data);
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(function () use ($template): Route {
        $route = new Route('PUT', 'test/{response_template}', fn (): null => null);
        $route->bind(new Request);
        $route->setParameter('response_template', $template);

        return $route;
    });

    $validator = Validator::make($data, $request->rules());
    $validator->validate();

    $request->setValidator($validator);

    $dto = $request->toDto();

    expect($dto)->toBeInstanceOf(ResponseTemplateData::class)
        ->and($dto->title)->toBe('Updated Title')
        ->and($dto->body)->toBe('Updated Body')
        ->and($dto->sellerId)->toBe($template->seller_id)
        ->and($dto->id)->toBe($template->id);
});
