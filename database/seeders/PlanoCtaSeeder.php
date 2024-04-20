<?php

namespace Database\Seeders;

use App\Models\PlanoCta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanoCtaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        PlanoCta::factory(1)->create();
    }
}
