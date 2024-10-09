<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaccineCentersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() : void
    {
        // Prepopulate with 10-20 vaccine centers
        DB::table('vaccine_centers')->insert([
            ['center_name' => 'Center A', 'location' => 'Location A', 'daily_limit' => 100],
            ['center_name' => 'Center B', 'location' => 'Location B', 'daily_limit' => 150],
            ['center_name' => 'Center C', 'location' => 'Location C', 'daily_limit' => 120],
            ['center_name' => 'Center D', 'location' => 'Location D', 'daily_limit' => 80],
            ['center_name' => 'Center E', 'location' => 'Location E', 'daily_limit' => 200],
            ['center_name' => 'Center F', 'location' => 'Location F', 'daily_limit' => 130],
            ['center_name' => 'Center G', 'location' => 'Location G', 'daily_limit' => 90],
            ['center_name' => 'Center H', 'location' => 'Location H', 'daily_limit' => 170],
            ['center_name' => 'Center I', 'location' => 'Location I', 'daily_limit' => 110],
            ['center_name' => 'Center J', 'location' => 'Location J', 'daily_limit' => 95],
            ['center_name' => 'Center K', 'location' => 'Location K', 'daily_limit' => 180],
            ['center_name' => 'Center L', 'location' => 'Location L', 'daily_limit' => 105],
        ]);
    }
}
