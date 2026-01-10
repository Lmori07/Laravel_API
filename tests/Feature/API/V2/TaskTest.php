<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// The trait must be used inside the file scope properly for Pest
uses(RefreshDatabase::class);

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('test_user_can_get_list_of_tasks', function () {
    // Arrange: create 5 fake tasks
    $user = User::factory()->create();
    $this->actingAs($user);
    $tasks = Task::factory()->count(5)->create([
        'user_id' => $user->id
    ]);

    // Act: Make a Get request
    $response = $this->getJson('/api/v2/tasks');

    // Assert
    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'completed'], // Added '*' to check all items in the array
        ],
    ]);
});

test('test_user_can_get_single_task', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->create([
        'user_id' => $user->id
    ]);

    // Act: Added a missing forward slash before the ID
    $response = $this->getJson('/api/v2/tasks/' . $task->id);

    // Assert
    $response->assertOk();
    $response->assertJsonStructure([
        'data' => ['id', 'name', 'completed'],
    ]);
});

// POST /task -> create a new task
test('user_can_create_a_new_task', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    //Act
    $response = $this->postJson('/api/v2/tasks', [
        'name' => 'New Task',
    ]);

    //Assert
    $response->assertCreated();
    $response->assertJsonStructure(['data' => ['id', 'name', 'completed']]);
    $this->assertDatabaseHas('tasks', ['name' => 'New Task']);
});

// PUT /task/{id} -> update a task
test('user_cannot_create_invalid_task', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    //Act
    $response = $this->postJson('/api/v2/tasks', [
        'name' => ''
    ]);

    //Assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

// PUT /task/{id} -> update a task v2
test('user_can_update_a_task', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->create([
        'user_id' => $user->id
    ]);

    //Act
    $response = $this->putJson('/api/v2/tasks/' . $task->id, [
        'name' => 'Updated Task',
    ]);

    //Assert
    $response->assertOk();
    $response->assertJsonFragment([
        'name' => 'Updated Task',
    ]);
});

test('user_cannot_update_task_with_invalid_data', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->create([
        'user_id' => $user->id
    ]);

    //Act
    $response = $this->putJson('/api/v2/tasks/' . $task->id, [
        'name' => '',
    ]);

    //Assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

// PATCH /task/{id} -> mark the task as completed or incomplete
test('user_can_toggle_task_completion', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'completed' => false,
    ]);

    //Act
    $response = $this->patchJson('/api/v2/tasks/' . $task->id . '/complete', [
        'completed' => true,
    ]);

    //Assert
    $response->assertOk();
    $response->assertJsonFragment([
        'completed' => true,
    ]);
});

test('user_cannot_toggle_task_completion_with_invalid_data', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->create([
        'user_id' => $user->id
    ]);

    //Act
    $response = $this->patchJson('/api/v2/tasks/' . $task->id . '/complete', [
        'completed' => 'invalid',
    ]);

    //Assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('completed');
});

// Delete /task/{id} -> delete a task
test('user_can_deletea_task', function () {

    //Arrange
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->create([
        'user_id' => $user->id
    ]);

    //Act
    $response = $this->deleteJson('/api/v2/tasks/' . $task->id);

    //Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,

    ]);
});
