<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Task;

// The trait must be used inside the file scope properly for Pest
uses(RefreshDatabase::class);

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('test_user_cannot_view_task_owned_by_another_user', function () {

    // Arrange
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    // Act
    $task = Task::factory()->for($owner)->create();

    // Assert
    $this->actingAs($otherUser)
        ->getJson('/api/v2/tasks/' . $task->id)
        ->assertForbidden();
});

test('test_user_cannot_update_task_owned_by_another_user', function () {

    // Arrange
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    // Act
    $task = Task::factory()->for($owner)->create();
    $payload = [
        'name' => 'Unauthorized Update'
    ];

    // Assert
    $this->actingAs($otherUser)
        ->putJson('/api/v2/tasks/' . $task->id, $payload)
        ->assertForbidden();
});

test('test_user_cannot_delete_task_owned_by_another_user', function () {

    // Arrange
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    // Act & Assert
    $task = Task::factory()->for($owner)->create();
    $this->actingAs($otherUser)
        ->deleteJson('/api/v2/tasks/' . $task->id)
        ->assertForbidden();
});

test('test_user_cannot_complete_task_owned_by_another_user', function () {

    // Arrange
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    // Act
    $task = Task::factory()->for($owner)->create();
    $payload = [
        'completed' => true
    ];

    // Assert
    $this->actingAs($otherUser)
        ->patchJson('/api/v2/tasks/' . $task->id . '/complete', $payload)
        ->assertForbidden();
});