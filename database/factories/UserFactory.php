<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'username' => fake()->unique()->userName(),
            'nis' => fake()->unique()->numerify('##########'),
            'identity_type_id' => \App\Models\IdentityType::firstOrCreate(['id' => 1], ['name' => 'KTP', 'label' => 'KTP'])->id,
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => \App\Models\Role::firstOrCreate(['id' => 1], ['name' => 'Superadmin'])->id,
            'language' => 'id',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
