<?php

use Database\Seeders\RoleSeeder;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());

    // Seed roles required for registration
    $this->seed(RoleSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'identitas' => '12345678',
        'kategori_pendaftar' => 'mahasiswa',
        'program_studi' => 'Teknik Informatika',
    ]);

    // User should be created in database
    expect(\App\Models\User::where('email', 'test@example.com')->exists())->toBeTrue();

    // Should redirect to dashboard after registration
    $response->assertRedirect(route('dashboard', absolute: false));
});