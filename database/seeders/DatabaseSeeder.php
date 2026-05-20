<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@printa.local',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        // Create Staff Users
        User::create([
            'name' => 'John Doe',
            'email' => 'staff@printa.local',
            'password' => Hash::make('password'),
            'role' => 'Staff',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@printa.local',
            'password' => Hash::make('password'),
            'role' => 'Staff',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@printa.local',
            'password' => Hash::make('password'),
            'role' => 'Staff',
            'is_active' => true,
        ]);

        // Create default settings
        Setting::set('company_name', 'Printa Signages & Stickers');
        Setting::set('company_email', 'hello@printa.local');
        Setting::set('company_phone', '+63 925 123 4567');
        Setting::set('company_address', '123 Business Street, Manila, Philippines');
        Setting::set('receipt_footer', 'Thank you for your business!');
        Setting::set('dark_mode', 'false');
        Setting::set('printer_name', 'Default Printer');
    }
}
