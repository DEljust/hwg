<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_crud_category(): void
    {
        $id = $this->postJson('/api/categories', ['name' => 'Novel'])
            ->assertCreated()
            ->json('id');

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(1);

        $this->putJson("/api/categories/{$id}", ['name' => 'Fiksi'])
            ->assertOk()
            ->assertJsonPath('name', 'Fiksi');

        $this->deleteJson("/api/categories/{$id}")->assertNoContent();
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_name_must_be_unique(): void
    {
        Category::create(['name' => 'Novel']);

        $this->postJson('/api/categories', ['name' => 'Novel'])->assertUnprocessable();
    }
}
