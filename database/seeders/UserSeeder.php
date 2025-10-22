<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $itRole = Role::where('name', 'it')->first();
        $seoerRole = Role::where('name', 'seoer')->first();
        $partnerRole = Role::where('name', 'partner')->first();
        $assistantRole = Role::where('name', 'assistant')->first();

        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@bl555.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole ? $adminRole->id : null,
            'is_active' => true,
        ]);

        // Create IT user
        User::create([
            'name' => 'IT Support',
            'email' => 'it@bl555.com',
            'password' => Hash::make('it123'),
            'role_id' => $itRole ? $itRole->id : null,
            'is_active' => true,
        ]);

        // Create Seoer user
        User::create([
            'name' => 'SEO Specialist',
            'email' => 'seoer@bl555.com',
            'password' => Hash::make('seoer123'),
            'role_id' => $seoerRole ? $seoerRole->id : null,
            'is_active' => true,
        ]);

        // Create Partner user
        User::create([
            'name' => 'Partner',
            'email' => 'partner@bl555.com',
            'password' => Hash::make('partner123'),
            'role_id' => $partnerRole ? $partnerRole->id : null,
            'is_active' => true,
        ]);

        // Create Assistant user
        User::create([
            'name' => 'Assistant',
            'email' => 'assistant@bl555.com',
            'password' => Hash::make('assistant123'),
            'role_id' => $assistantRole ? $assistantRole->id : null,
            'is_active' => true,
        ]);
    }
}
