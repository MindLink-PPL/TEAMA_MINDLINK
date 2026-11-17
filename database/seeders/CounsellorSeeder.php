<?php

namespace Database\Seeders;

use App\Models\Counsellor;
use Illuminate\Database\Seeder;

class CounsellorSeeder extends Seeder
{
    public function run(): void
    {
        Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );
        Counsellor::firstOrCreate(
            ['name' => 'Dr. Budi'],
            ['specialization' => 'Academic & Career Stress']
        );
        Counsellor::firstOrCreate(
            ['name' => 'Siti Rahma, M.Psi.'],
            ['specialization' => 'Anxiety & Personal Counseling']
        );
    }
}