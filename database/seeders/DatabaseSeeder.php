<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Justin',
            'email' => 'justSMBOelot@gmail.com',
        ]);

        $novel = Category::create(['name' => 'Novel']);
        $novel->books()->createMany([
            ['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'stock' => 3],
            ['title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer', 'stock' => 2],
        ]);

        $sains = Category::create(['name' => 'Sains']);
        $sains->books()->create(['title' => 'Kosmos', 'author' => 'Carl Sagan', 'stock' => 1]);
    }
}
