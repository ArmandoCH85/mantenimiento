<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use App\Models\Maintenance;
use Carbon\Carbon;

class ReportesTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear vehículos de prueba
        $vehicles = [
            [
                'placa' => 'ABC123',
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'anio' => 2020,
                'numero_motor' => 'TOY2020001',
                'numero_chasis' => 'CHASIS001',
                'color' => 'Blanco',
                'tipo_vehiculo' => 'Sedán',
                'fecha_compra' => '2020-01-15',
                'propietario_actual' => 'Juan Pérez',
                'kilometraje_actual' => 45000,
            ],
            [
                'placa' => 'DEF456',
                'marca' => 'Chevrolet',
                'modelo' => 'Spark',
                'anio' => 2019,
                'numero_motor' => 'CHV2019001',
                'numero_chasis' => 'CHASIS002',
                'color' => 'Rojo',
                'tipo_vehiculo' => 'Hatchback',
                'fecha_compra' => '2019-03-20',
                'propietario_actual' => 'María García',
                'kilometraje_actual' => 62000,
            ],
            [
                'placa' => 'GHI789',
                'marca' => 'Nissan',
                'modelo' => 'Sentra',
                'anio' => 2021,
                'numero_motor' => 'NIS2021001',
                'numero_chasis' => 'CHASIS003',
                'color' => 'Azul',
                'tipo_vehiculo' => 'Sedán',
                'fecha_compra' => '2021-06-10',
                'propietario_actual' => 'Carlos López',
                'kilometraje_actual' => 28000,
            ],
            [
                'placa' => 'JKL012',
                'marca' => 'Renault',
                'modelo' => 'Logan',
                'anio' => 2018,
                'numero_motor' => 'REN2018001',
                'numero_chasis' => 'CHASIS004',
                'color' => 'Gris',
                'tipo_vehiculo' => 'Sedán',
                'fecha_compra' => '2018-09-05',
                'propietario_actual' => 'Ana Rodríguez',
                'kilometraje_actual' => 78000,
            ],
            [
                'placa' => 'MNO345',
                'marca' => 'Hyundai',
                'modelo' => 'Accent',
                'anio' => 2022,
                'numero_motor' => 'HYU2022001',
                'numero_chasis' => 'CHASIS005',
                'color' => 'Negro',
                'tipo_vehiculo' => 'Sedán',
                'fecha_compra' => '2022-02-14',
                'propietario_actual' => 'Luis Martínez',
                'kilometraje_actual' => 15000,
            ],
        ];

        $createdVehicles = [];
        foreach ($vehicles as $vehicleData) {
            $createdVehicles[] = Vehicle::create($vehicleData);
        }

        // Compañías aseguradoras disponibles
        $companias = [
            'Seguros Bolívar',
            'SURA',
            'Mapfre',
            'AXA Colpatria',
            'Allianz',
            'Liberty Seguros',
            'Previsora Seguros',
            'Equidad Seguros',
        ];

        // Centros de revisión técnica
        $centros = [
            'CDA Bogotá',
            'Tecnicentro',
            'Revisión Técnica Nacional',
            'CDA Colombia',
            'Centro de Diagnóstico Automotor',
        ];

        // Crear registros de SOAT
        foreach ($createdVehicles as $vehicle) {
            // SOAT vencido (para reporte de documentos vencidos)
            SoatRecord::create([
                'vehicle_id' => $vehicle->id,
                'fecha_emision' => Carbon::now()->subYear()->subDays(10),
                'fecha_vencimiento' => Carbon::now()->subDays(rand(5, 30)), // Vencido hace 5-30 días
                'numero_poliza' => 'SOAT' . str_pad($vehicle->id * 1000 + 1, 8, '0', STR_PAD_LEFT),
                'compania_aseguradora' => $companias[array_rand($companias)],
                'valor_pagado' => rand(450000, 650000),
                'estado' => 'vencido',
            ]);

            // SOAT próximo a vencer (para reporte de próximos vencimientos)
            SoatRecord::create([
                'vehicle_id' => $vehicle->id,
                'fecha_emision' => Carbon::now()->subDays(rand(300, 350)),
                'fecha_vencimiento' => Carbon::now()->addDays(rand(1, 25)), // Vence en 1-25 días
                'numero_poliza' => 'SOAT' . str_pad($vehicle->id * 1000 + 2, 8, '0', STR_PAD_LEFT),
                'compania_aseguradora' => $companias[array_rand($companias)],
                'valor_pagado' => rand(450000, 650000),
                'estado' => 'vigente',
            ]);

            // SOAT vigente (para completar datos)
            SoatRecord::create([
                'vehicle_id' => $vehicle->id,
                'fecha_emision' => Carbon::now()->subDays(rand(30, 60)),
                'fecha_vencimiento' => Carbon::now()->addDays(rand(300, 365)),
                'numero_poliza' => 'SOAT' . str_pad($vehicle->id * 1000 + 3, 8, '0', STR_PAD_LEFT),
                'compania_aseguradora' => $companias[array_rand($companias)],
                'valor_pagado' => rand(450000, 650000),
                'estado' => 'vigente',
            ]);
        }

        // Crear registros de Revisión Técnica
        foreach ($createdVehicles as $vehicle) {
            // Revisión técnica vencida
            RevisionTecnicaRecord::create([
                'vehicle_id' => $vehicle->id,
                'fecha_emision' => Carbon::now()->subYear()->subDays(15),
                'fecha_vencimiento' => Carbon::now()->subDays(rand(10, 45)),
                'numero_certificado' => 'RT' . str_pad($vehicle->id * 2000 + 1, 8, '0', STR_PAD_LEFT),
                'centro_revision' => $centros[array_rand($centros)],
                'resultado' => 'aprobado',
                'observaciones' => 'Vehículo en buen estado',
                'valor_pagado' => rand(180000, 250000),
                'estado' => 'vencido',
            ]);

            // Revisión técnica próxima a vencer
            RevisionTecnicaRecord::create([
                'vehicle_id' => $vehicle->id,
                'fecha_emision' => Carbon::now()->subDays(rand(300, 350)),
                'fecha_vencimiento' => Carbon::now()->addDays(rand(5, 20)),
                'numero_certificado' => 'RT' . str_pad($vehicle->id * 2000 + 2, 8, '0', STR_PAD_LEFT),
                'centro_revision' => $centros[array_rand($centros)],
                'resultado' => 'aprobado',
                'observaciones' => 'Revisión satisfactoria',
                'valor_pagado' => rand(180000, 250000),
                'estado' => 'vigente',
            ]);
        }

        // Crear mantenimientos (para reporte de gastos por vehículo)
        $tiposMantenimiento = [
            'Cambio de aceite',
            'Cambio de filtros',
            'Revisión de frenos',
            'Alineación y balanceo',
            'Cambio de llantas',
            'Revisión de motor',
            'Cambio de batería',
            'Reparación de transmisión',
            'Cambio de amortiguadores',
            'Mantenimiento preventivo',
        ];

        $talleres = [
            'Taller Central',
            'AutoServicio Express',
            'Mecánica Especializada',
            'Taller del Norte',
            'Servicio Automotriz Integral',
        ];

        foreach ($createdVehicles as $vehicle) {
            // Crear varios mantenimientos para el mes actual
            $numMantenimientos = rand(2, 5);
            
            for ($i = 0; $i < $numMantenimientos; $i++) {
                Maintenance::create([
                    'vehicle_id' => $vehicle->id,
                    'trabajo_realizado' => $tiposMantenimiento[array_rand($tiposMantenimiento)],
                    'pieza_afectada' => 'Motor, Frenos, Transmisión',
                    'comentarios' => 'Mantenimiento realizado según especificaciones del fabricante',
                    'precio_mantenimiento' => rand(50000, 500000),
                    'nombre_taller' => $talleres[array_rand($talleres)],
                    'fecha_mantenimiento' => Carbon::now()->subDays(rand(1, 30)), // Último mes
                    'kilometraje' => max(1000, $vehicle->kilometraje_actual - rand(100, 2000)),
                    'tipo_mantenimiento' => rand(0, 1) ? 'preventivo' : 'correctivo',
                    'estado' => 'completado',
                    'proximo_mantenimiento' => Carbon::now()->addDays(rand(30, 90)),
                    'nivel_prioridad' => ['baja', 'media', 'alta'][rand(0, 2)],
                    'adjuntos' => null,
                ]);
            }

            // Crear mantenimientos de meses anteriores
            $numMantenimientosAnteriores = rand(3, 8);
            
            for ($i = 0; $i < $numMantenimientosAnteriores; $i++) {
                Maintenance::create([
                    'vehicle_id' => $vehicle->id,
                    'trabajo_realizado' => $tiposMantenimiento[array_rand($tiposMantenimiento)],
                    'pieza_afectada' => 'Motor, Frenos, Transmisión',
                    'comentarios' => 'Mantenimiento histórico',
                    'precio_mantenimiento' => rand(50000, 500000),
                    'nombre_taller' => $talleres[array_rand($talleres)],
                    'fecha_mantenimiento' => Carbon::now()->subDays(rand(31, 365)), // Meses anteriores
                    'kilometraje' => max(500, $vehicle->kilometraje_actual - rand(1000, 10000)),
                    'tipo_mantenimiento' => rand(0, 1) ? 'preventivo' : 'correctivo',
                    'estado' => 'completado',
                    'proximo_mantenimiento' => Carbon::now()->addDays(rand(30, 90)),
                    'nivel_prioridad' => ['baja', 'media', 'alta'][rand(0, 2)],
                    'adjuntos' => null,
                ]);
            }
        }

        $this->command->info('✅ Datos de prueba creados exitosamente:');
        $this->command->info('   - ' . count($createdVehicles) . ' vehículos');
        $this->command->info('   - ' . (count($createdVehicles) * 3) . ' registros de SOAT');
        $this->command->info('   - ' . (count($createdVehicles) * 2) . ' registros de Revisión Técnica');
        $this->command->info('   - Múltiples registros de mantenimiento');
        $this->command->info('');
        $this->command->info('📊 Los reportes ahora tendrán datos para mostrar:');
        $this->command->info('   - Documentos vencidos (SOAT y Revisión Técnica)');
        $this->command->info('   - Próximos vencimientos (en los próximos 30 días)');
        $this->command->info('   - Gastos por vehículo (mes actual)');
    }
}
