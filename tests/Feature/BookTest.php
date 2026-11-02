<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
        $this->category = Category::create(['name' => 'Novel']);
    }

    public function test_crud_book(): void
    {
        $id = $this->postJson('/api/books', [
            'category_id' => $this->category->id,
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'stock' => '3',
        ])->assertCreated()->assertJsonPath('stock', 3)->json('id');

        $this->getJson('/api/books')->assertOk()->assertJsonPath('total', 1);

        $this->putJson("/api/books/{$id}", ['stock' => 5])
            ->assertOk()
            ->assertJsonPath('stock', 5);

        $this->deleteJson("/api/books/{$id}")->assertNoContent();
        $this->assertDatabaseCount('books', 0);
    }

    public function test_category_with_books_cannot_be_deleted(): void
    {
        Book::create([
            'category_id' => $this->category->id,
            'title' => 'Bumi Manusia',
            'author' => 'Pramoedya Ananta Toer',
            'stock' => 1,
        ]);

        $this->deleteJson("/api/categories/{$this->category->id}")->assertUnprocessable();
    }
}
