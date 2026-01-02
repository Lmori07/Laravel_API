<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

// The trait must be used inside the file scope properly for Pest
uses(RefreshDatabase::class);

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('test user can get list of tasks', function () {
    // Arrange: create 5 fake tasks
    Task::factory()->count(5)->create();

    // Act: Make a Get request
    $response = $this->getJson('/api/v1/tasks');

    // Assert
    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'completed'], // Added '*' to check all items in the array
        ],
    ]);
});

test('test user can get single task', function () {
    // Arrange
    $task = Task::factory()->create();

    // Act: Added a missing forward slash before the ID
    $response = $this->getJson('/api/v1/tasks/'.$task->id);

    // Assert
    $response->assertOk();
    $response->assertJsonStructure([
        'data' => ['id', 'name', 'completed'],
    ]);
});

// POST /task -> create a new task
test('user can create a new task', function () {

    $response = $this->postJson('/api/v1/tasks', [
        'name' => 'New Task',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure(['data' => ['id', 'name', 'completed']]);
    $this->assertDatabaseHas('tasks', ['name' => 'New Task']);
});

// PUT /task/{id} -> update a task
test('user cannot create invalid task', function () {
    $response = $this->postJson('/api/v1/tasks', [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

// PUT /task/{id} -> update a task v2
test('user can update a task', function () {

    $task = Task::factory()->create();

    $response = $this->putJson('/api/v1/tasks/'.$task->id, [
        'name' => 'Updated Task',
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'name' => 'Updated Task',
    ]);
});

test('user cannot update task with invalid data', function () {

    $task = Task::factory()->create();

    $response = $this->putJson('/api/v1/tasks/'.$task->id, [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

// PATCH /task/{id} -> mark the task as completed or incomplete
test('user can toggle task completion', function () {

    $task = Task::factory()->create([
        'completed' => false,
    ]);

    $response = $this->patchJson('/api/v1/tasks/'.$task->id.'/complete', [
        'completed' => true,
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'completed' => true,
    ]);
});

test('user cannot toggle task completion with invalid data', function () {

    $task = Task::factory()->create();

    $response = $this->patchJson('/api/v1/tasks/'.$task->id.'/complete', [
        'completed' => 'invalid',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('completed');
});

// Delete /task/{id} -> delete a task
test('user can delete a task', function () {

    $task = Task::factory()->create();

    $response = $this->deleteJson('/api/v1/tasks/'.$task->id);
    $response->assertNoContent();
    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,

    ]);
});
