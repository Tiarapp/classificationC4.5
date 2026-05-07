<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@tiktok-classification.com',
        ]);

        // Pilihan seeder:
        // 1. RespondentKuesionerSeeder: 100 data random distribution
        // 2. BatchKuesionerSeeder: 300 data (3 batch @ 100 data per batch)
        
        // Uncomment salah satu seeder yang diinginkan:
        
        // Seeder lama (100 data random)
        // $this->call(RespondentKuesionerSeeder::class);
        
        // Seeder batch baru (300 data terdistribusi)
        $this->call(BatchKuesionerSeeder::class);
    }
}
