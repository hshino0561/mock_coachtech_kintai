<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'admin1',
            'email' => 'admin1@admin.com',
            'password' => Hash::make('pass'),
        ]);

        Admin::create([
            'name' => 'admin2',
            'email' => 'admin2@admin.com',
            'password' => Hash::make('pass'),
        ]);
    }
}
