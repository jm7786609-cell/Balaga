<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@pharmacy.com');
        $password = env('ADMIN_PASSWORD', 'admin123');
        $name = env('ADMIN_NAME', 'Super Admin');

        if (list($firstName, $lastName) = explode(' ', $name, 2) + [null, null]);

        if (User::where('email', $email)->exists()) {
            $this->command->info('Admin user already exists.');
            return;
        }

        User::factory()->create([
            'first_name'       => $firstName ?? 'Super',
            'middle_name'      => null,
            'last_name'        => $lastName ?? 'Admin',
            'email'            => $email,
            'password'         => Hash::make($password),
            'role'             => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created!');
        $this->command->info("Email:    {$email}");
        $this->command->info("Password: {$password}");
    }
}
