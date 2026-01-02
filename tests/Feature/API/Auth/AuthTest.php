<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// The trait must be used inside the file scope properly for Pest
uses(RefreshDatabase::class);

test('test user can login and receive token', function () {

    $user = User::factory()->create();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['token', 'user']);

});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('user can register and receive token', function () {
    $payload = [
        'name' => 'New user',
        'email' => $email = 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertCreated();
    $response->assertJsonStructure(['token', 'user']);
    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});

test('user cannot register with invalid credentials', function () {
    $payload = [
        'name' => '',
        'email' => 'wrong-email',
        'password' => 'short',
        'password_confirmation' => 'short',
    ];

    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});
