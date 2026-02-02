<?php

declare(strict_types=1);

use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

it('displays wallet page with transactions and payout methods', function (): void {
    $user = User::factory()->create();
    Transaction::factory()->count(3)->create(['user_id' => $user->id]);
    PayoutMethod::factory()->count(2)->create(['user_id' => $user->id]);

    actingAs($user)
        ->get(route('wallet.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Wallet')
            ->has('balance', fn (AssertableInertia $page): AssertableInertia => $page
                ->has('amount')
                ->has('formatted')
                ->has('currency')
            )
            ->has('transactions.data', 3)
            ->has('payoutMethods', 2)
        );
});

it('paginates transactions', function (): void {
    $user = User::factory()->create();
    Transaction::factory()->count(25)->create(['user_id' => $user->id]);

    actingAs($user)
        ->get(route('wallet.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Wallet')
            ->has('transactions.data', 10)
            ->has('transactions.meta')
            ->has('transactions.links')
        );
});
