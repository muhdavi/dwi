<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Atur SEED_USER_PASSWORD di .env agar password awal tidak memakai default.
        $password = env('SEED_USER_PASSWORD', 'password');

        $users = [
            ['name' => 'Administrator', 'email' => 'admin@datawilayah.test'],
            ['name' => 'Davi', 'email' => 'davi@datawilayah.test'],
            ['name' => 'Operator', 'email' => 'operator@datawilayah.test'],
        ];

        foreach ($users as $user) {
            User::firstOrNew(['email' => $user['email']])
                ->forceFill([
                    'name' => $user['name'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ])
                ->save();
        }
    }
}
