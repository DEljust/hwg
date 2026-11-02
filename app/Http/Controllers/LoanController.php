<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    // default cuma buku yang masih dipinjam, ?history=1 untuk semua riwayat
    public function index(Request $request)
    {
        return $request->user()->loans()
            ->with('book.category')
            ->when(! $request->boolean('history'), fn ($q) => $q->whereNull('returned_at'))
            ->latest('borrowed_at')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $user = $request->user();

        $alreadyBorrowed = $user->loans()
            ->where('book_id', $data['book_id'])
            ->whereNull('returned_at')
            ->exists();

        if ($alreadyBorrowed) {
            return response()->json(['message' => 'Buku ini masih kamu pinjam'], 422);
        }

        return DB::transaction(function () use ($user, $data) {
            // kurangi stok langsung di query biar aman kalau ada request bersamaan
            $updated = Book::where('id', $data['book_id'])
                ->where('stock', '>', 0)
                ->decrement('stock');

            if (! $updated) {
                return response()->json(['message' => 'Stok buku habis'], 422);
            }

            $loan = $user->loans()->create([
                'book_id' => $data['book_id'],
                'borrowed_at' => now(),
            ]);

            return response()->json($loan->load('book'), 201);
        });
    }

    public function return(Request $request, Loan $loan)
    {
        if ($loan->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan peminjaman kamu'], 403);
        }

        $returned = DB::transaction(function () use ($loan) {
            $updated = Loan::where('id', $loan->id)
                ->whereNull('returned_at')
                ->update(['returned_at' => now()]);

            if ($updated) {
                Book::where('id', $loan->book_id)->increment('stock');
            }

            return $updated;
        });

        if (! $returned) {
            return response()->json(['message' => 'Buku sudah dikembalikan'], 422);
        }

        return $loan->refresh()->load('book');
    }
}
