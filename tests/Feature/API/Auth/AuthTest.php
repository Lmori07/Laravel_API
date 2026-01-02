<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// The trait must be used inside the file scope properly for Pest
uses(RefreshDatabase::class);

test('test user can login and receive token', function () {

    $user = User::factory()->create();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['token', 'user']);

});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});
