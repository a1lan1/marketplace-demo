<?php

use App\Models\ResponseTemplate;
use App\Models\User;
use App\Policies\ResponseTemplatePolicy;

it('allows a seller to view any templates', function (): void {
    $seller = User::factory()->withSellerRole()->create();
    $policy = new ResponseTemplatePolicy;

    expect($policy->viewAny($seller))->toBeTrue();
});

it('prevents a non-seller from viewing any templates', function (): void {
    $user = User::factory()->create();
    $policy = new ResponseTemplatePolicy;

    expect($policy->viewAny($user))->toBeFalse();
});

it('allows a seller to view their own template', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);
    $policy = new ResponseTemplatePolicy;

    expect($policy->view($seller, $template))->toBeTrue();
});

it('prevents a seller from viewing other sellers template', function (): void {
    $seller1 = User::factory()->create();
    $seller2 = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller2->id]);
    $policy = new ResponseTemplatePolicy;

    expect($policy->view($seller1, $template))->toBeFalse();
});

it('allows a seller to create a template', function (): void {
    $seller = User::factory()->withSellerRole()->create();
    $policy = new ResponseTemplatePolicy;

    expect($policy->create($seller))->toBeTrue();
});

it('prevents a non-seller from creating a template', function (): void {
    $user = User::factory()->create();
    $policy = new ResponseTemplatePolicy;

    expect($policy->create($user))->toBeFalse();
});

it('allows a seller to update their own template', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);
    $policy = new ResponseTemplatePolicy;

    expect($policy->update($seller, $template))->toBeTrue();
});

it('prevents a seller from updating other sellers template', function (): void {
    $seller1 = User::factory()->create();
    $seller2 = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller2->id]);
    $policy = new ResponseTemplatePolicy;

    expect($policy->update($seller1, $template))->toBeFalse();
});

it('allows a seller to delete their own template', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);
    $policy = new ResponseTemplatePolicy;

    expect($policy->delete($seller, $template))->toBeTrue();
});

it('prevents a seller from deleting other sellers template', function (): void {
    $seller1 = User::factory()->create();
    $seller2 = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller2->id]);
    $policy = new ResponseTemplatePolicy;

    expect($policy->delete($seller1, $template))->toBeFalse();
});
