<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Akun admin & user awal sudah dibuat otomatis lewat migration
     * (0001_01_01_000000_create_users_table), jadi tidak perlu diulang di sini.
     */
    public function run(): void
    {
        $this->call(JenisCutiAturanSeeder::class);
    }
}
