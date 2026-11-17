<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('users')->insertOrIgnore([
            [
                'id'         => 1,
                'name'       => 'Demo Student',
                'email'      => 'student@telkomuniversity.ac.id',
                'password'   => Hash::make('password123'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'name'       => 'Admin MindLink',
                'email'      => 'admin@telkomuniversity.ac.id',
                'password'   => Hash::make('pass1234'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}