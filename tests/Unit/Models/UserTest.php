<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Panel;
use Mockery;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->manager = User::factory()->withManagerRole()->create();
    $this->buyer = User::factory()->withBuyerRole()->create();
    $this->seller = User::factory()->withSellerRole()->create();
    $this->plainUser = User::factory()->create();
});

it('determines if a user is an admin', function (): void {
    expect($this->admin->isAdmin())->toBeTrue();
    expect($this->manager->isAdmin())->toBeFalse();
    expect($this->plainUser->isAdmin())->toBeFalse();
});

it('determines if a user is a manager', function (): void {
    expect($this->manager->isManager())->toBeTrue();
    expect($this->admin->isManager())->toBeFalse();
    expect($this->plainUser->isManager())->toBeFalse();
});

it('determines if a user is an admin or manager', function (): void {
    expect($this->admin->isAdminOrManager())->toBeTrue();
    expect($this->manager->isAdminOrManager())->toBeTrue();
    expect($this->buyer->isAdminOrManager())->toBeFalse();
    expect($this->plainUser->isAdminOrManager())->toBeFalse();
});

it('determines if a user is a buyer', function (): void {
    expect($this->buyer->isBuyer())->toBeTrue();
    expect($this->seller->isBuyer())->toBeFalse();
    expect($this->plainUser->isBuyer())->toBeFalse();
});

it('determines if a user is a seller', function (): void {
    expect($this->seller->isSeller())->toBeTrue();
    expect($this->buyer->isSeller())->toBeFalse();
    expect($this->plainUser->isSeller())->toBeFalse();
});

it('allows admin or manager to access filament panel', function (): void {
    $panelMock = Mockery::mock(Panel::class);
    expect($this->admin->canAccessPanel($panelMock))->toBeTrue();
    expect($this->manager->canAccessPanel($panelMock))->toBeTrue();
    expect($this->plainUser->canAccessPanel($panelMock))->toBeFalse();
});
