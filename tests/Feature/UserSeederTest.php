<?php

use App\Models\User;
use Database\Seeders\UserSeeder;

test('seeded users can log in and open the dashboard without registering', function () {
    $this->seed(UserSeeder::class);
    $this->seed(UserSeeder::class);

    $this->post('/login', ['email' => 'admin@datawilayah.test', 'password' => 'password']);

    $this->assertAuthenticated();
    $this->get('/dashboard')->assertOk();
    expect(User::count())->toBe(3);
});

test('registration is disabled', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [])->assertStatus(404);
    $this->get('/')->assertOk()->assertDontSee('Daftar');
});
