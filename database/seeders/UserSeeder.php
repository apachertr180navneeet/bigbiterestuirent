<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => "Project",
            'last_name' => "Admin",
            'full_name' => "Project Admin",
            'slug' => "project-admin",
            'email' => 'projectadmin@mailinator.com',
            'password' => Hash::make('123456'),
            'phone' => '8000000000',
            'role' => 'admin',
            'address' => '115 Pitt Street, Sydney NSW, Australia',
            'area' => '115 Pitt St',
            'city' => 'Sydney',
            'state' => 'NSW',
            'country' => 'Australia',
            'country_code' => '1',
            'zipcode' => '2000',
            'latitude' => '-33.8664701',
            'longitude' => '151.2081952',
            'status' => 'active',
        ]);
    }
}
