<?php

use App\Models\ResponseTemplate;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

it('allows a seller to get their templates', function (): void {
    $seller = User::factory()->withSellerRole()->create();
    ResponseTemplate::factory()->count(3)->create(['seller_id' => $seller->id]);
    actingAs($seller, 'sanctum');

    getJson(route('api.geo.response-templates.index'))
        ->assertOk()
        ->assertJsonCount(3);
});

it('prevents a non-seller from getting templates', function (): void {
    $user = User::factory()->create();
    actingAs($user, 'sanctum');

    getJson(route('api.geo.response-templates.index'))
        ->assertForbidden();
});

it('allows a seller to create a template', function (): void {
    $seller = User::factory()->withSellerRole()->create();
    actingAs($seller, 'sanctum');

    $data = [
        'title' => 'New Template',
        'body' => 'Template body',
    ];

    postJson(route('api.geo.response-templates.store'), $data)
        ->assertCreated()
        ->assertJsonFragment($data);
});

it('allows a seller to view their template', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);
    actingAs($seller, 'sanctum');

    getJson(route('api.geo.response-templates.show', $template))
        ->assertOk()
        ->assertJsonFragment(['title' => $template->title]);
});

it('prevents a seller from viewing other sellers template', function (): void {
    $seller1 = User::factory()->create();
    $seller2 = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller2->id]);
    actingAs($seller1, 'sanctum');

    getJson(route('api.geo.response-templates.show', $template))
        ->assertForbidden();
});

it('allows a seller to update their template', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);
    actingAs($seller, 'sanctum');

    $data = [
        'title' => 'Updated Title',
        'body' => 'Updated Body',
    ];

    putJson(route('api.geo.response-templates.update', $template), $data)
        ->assertOk()
        ->assertJsonFragment($data);
});

it('allows a seller to delete their template', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);
    actingAs($seller, 'sanctum');

    deleteJson(route('api.geo.response-templates.destroy', $template))
        ->assertNoContent();

    $this->assertDatabaseMissing('response_templates', ['id' => $template->id]);
});
