<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed essential clinical & drug master dictionaries for prescriptions & autocomplete
        $this->call([
            ClinicalLookupSeeder::class,
            DrugLookupSeeder::class,
        ]);
    }
}
