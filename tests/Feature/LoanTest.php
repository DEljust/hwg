<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        $this->book = Book::create([
            'category_id' => Category::create(['name' => 'Novel'])->id,
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'stock' => 1,
        ]);
    }

    public function test_borrow_and_return_book(): void
    {
        $loanId = $this->postJson('/api/loans', ['book_id' => $this->book->id])
            ->assertCreated()
            ->json('id');

        $this->assertSame(0, $this->book->fresh()->stock);
        $this->getJson('/api/loans')->assertOk()->assertJsonCount(1);

        $this->postJson("/api/loans/{$loanId}/return")->assertOk();

        $this->assertSame(1, $this->book->fresh()->stock);
        $this->getJson('/api/loans')->assertJsonCount(0);
        $this->getJson('/api/loans?history=1')->assertJsonCount(1);
    }

    public function test_cannot_borrow_when_out_of_stock(): void
    {
        $this->book->update(['stock' => 0]);

        $this->postJson('/api/loans', ['book_id' => $this->book->id])->assertUnprocessable();
    }

    public function test_cannot_borrow_same_book_twice(): void
    {
        $this->book->update(['stock' => 5]);

        $this->postJson('/api/loans', ['book_id' => $this->book->id])->assertCreated();
        $this->postJson('/api/loans', ['book_id' => $this->book->id])->assertUnprocessable();
    }

    public function test_cannot_return_twice(): void
    {
        $loanId = $this->postJson('/api/loans', ['book_id' => $this->book->id])->json('id');

        $this->postJson("/api/loans/{$loanId}/return")->assertOk();
        $this->postJson("/api/loans/{$loanId}/return")->assertUnprocessable();
        $this->assertSame(1, $this->book->fresh()->stock);
    }

    public function test_cannot_return_other_users_loan(): void
    {
        $loanId = $this->postJson('/api/loans', ['book_id' => $this->book->id])->json('id');

        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/loans/{$loanId}/return")->assertForbidden();
    }

    public function test_book_with_active_loan_cannot_be_deleted(): void
    {
        $this->postJson('/api/loans', ['book_id' => $this->book->id]);

        $this->deleteJson("/api/books/{$this->book->id}")->assertUnprocessable();
    }
}
