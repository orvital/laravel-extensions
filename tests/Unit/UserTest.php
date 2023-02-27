<?php

namespace Orvital\Extensions\Tests\Unit;

use Orvital\Extensions\Tests\Models\User;
use Orvital\Extensions\Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_password_hashing(): void
    {
        $model = User::query()->create([
            'name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'verified_at' => now(),
            'password' => 'password',
            'remember_token' => fake()->sha256(),
        ]);

        $this->assertEquals(true, $model->checkAuthPassword('password'));
    }
}
