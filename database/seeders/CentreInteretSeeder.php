<?php

namespace Database\Seeders;

use App\Models\CentreInteret;
use Illuminate\Database\Seeder;

class CentreInteretSeeder extends Seeder
{
    public function run(): void
    {
        $centres = [
            ['nom' => 'Musculation', 'icone' => 'dumbbell'],
            ['nom' => 'Cardio', 'icone' => 'heart-pulse'],
            ['nom' => 'CrossFit', 'icone' => 'flame'],
            ['nom' => 'Yoga', 'icone' => 'lotus'],
            ['nom' => 'Boxe', 'icone' => 'boxing-glove'],
            ['nom' => 'Natation', 'icone' => 'waves'],
            ['nom' => 'Course à pied', 'icone' => 'running'],
            ['nom' => 'Danse', 'icone' => 'music'],
            ['nom' => 'Football', 'icone' => 'football'],
            ['nom' => 'Basketball', 'icone' => 'basketball'],
        ];

        foreach ($centres as $centre) {
            CentreInteret::firstOrCreate(['nom' => $centre['nom']], $centre);
        }
    }
}
