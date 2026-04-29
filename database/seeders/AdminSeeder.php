<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;     
use Illuminate\Support\Facades\Hash; 
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            DB::table('admins')->insert([
            'name'         => 'admin',
            'email'        => 'admin@gmail.com',
            'phone_number' => '1234567890', 
            'password'     => Hash::make('admin123'),
            'status'       => 'active',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);  
      }
}
