<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'dmpluspower@digitalmediaplus.info'],
            [
                'name' => 'DmplusPower',
                'password' => Hash::make('dmplus123#'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: dmpluspower@digitalmediaplus.info');
        $this->command->info('Password: dmplus123#');
        $this->command->info('Role: super_admin');
    }
}
