<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Skema final "users" -- sudah menggabungkan seluruh migration alter
     * yang tadinya terpisah (add_photo, add_pegawai_id, add_opd_role,
     * add_aktif, restructure_users_username_email_no_hp) supaya instalasi
     * baru langsung dapat skema akhir dalam satu langkah.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->nullable()->constrained('tb_pegawai_aktif')->nullOnDelete();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('no_hp', 30)->nullable();
            $table->string('photo')->nullable();
            $table->enum('role', ['admin', 'opd', 'user'])->default('user');
            $table->boolean('aktif')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Akun awal supaya aplikasi langsung bisa dipakai setelah migrate.
        DB::table('users')->insert([
            [
                'name' => 'Admin SICKEP',
                'username' => 'admin@klaten.go.id',
                'email' => 'admin@klaten.go.id',
                'role' => 'admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'User SICKEP',
                'username' => 'user@klaten.go.id',
                'email' => 'user@klaten.go.id',
                'role' => 'user',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
