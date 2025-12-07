<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'balance' => fake()->randomFloat(2, 10000, 100000),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'remember_token' => Str::random(10),
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have an avatar.
     */
    public function withAvatar(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->addMediaFromUrl('https://picsum.photos/500')
                ->toMediaCollection('user.avatar');
        });
    }

    /**
     * Indicate that the user should have a base set of roles.
     */
    public function withBaseRoles(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole([
                RoleEnum::USER->value,
                RoleEnum::BUYER->value,
                RoleEnum::SELLER->value,
            ]);
        });
    }

    /**
     * Indicate that the user should have an admin role.
     */
    public function withAdminRole(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(RoleEnum::ADMIN->value);
        });
    }

    /**
     * Indicate that the user should have a manager role.
     */
    public function withManagerRole(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(RoleEnum::MANAGER->value);
        });
    }

    /**
     * Indicate that the user should have a buyer role.
     */
    public function withBuyerRole(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(RoleEnum::BUYER->value);
        });
    }

    /**
     * Indicate that the user should have a seller role.
     */
    public function withSellerRole(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(RoleEnum::SELLER->value);
        });
    }

}
