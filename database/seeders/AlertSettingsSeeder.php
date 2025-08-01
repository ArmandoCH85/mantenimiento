<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('alert_settings')->insert([
            [
                'dias_anticipacion_soat' => 30,
                'dias_anticipacion_revision_tecnica' => 30,
                'user_id' => null, // Configuración global
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}