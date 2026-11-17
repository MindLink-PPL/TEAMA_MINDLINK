<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Counsellor;

class CounsellorSeeder extends Seeder
{
    public function run(): void
    {
        Counsellor::create(['name' => 'Alisa', 'specialization' => 'Student wellbeing']);
        Counsellor::create(['name' => 'Dr. Budi', 'specialization' => 'Academic & Career Stress']);
        Counsellor::create(['name' => 'Siti Rahma, M.Psi.', 'specialization' => 'Anxiety & Personal Counseling']);
    }
}