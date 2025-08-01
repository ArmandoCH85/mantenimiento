<?php

namespace App\Console\Commands;

use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use App\Models\AlertSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateDocumentStates extends Command
{
    protected $signature = 'vehicles:update-states';
    protected $description = 'Actualiza los estados de SOAT y Revisión Técnica basado en las fechas de vencimiento';

    public function handle(): int
    {
        $this->info('Actualizando estados de documentos...');

        // Obtener configuración de alertas
        $alertSettings = AlertSettings::whereNull('user_id')->first();
        $diasAnticipacionSoat = $alertSettings?->dias_anticipacion_soat ?? 30;
        $diasAnticipacionRevision = $alertSettings?->dias_anticipacion_revision_tecnica ?? 30;

        $hoy = Carbon::today();

        // Actualizar estados de SOAT
        $soatsActualizados = 0;
        SoatRecord::chunk(100, function ($soats) use ($hoy, $diasAnticipacionSoat, &$soatsActualizados) {
            foreach ($soats as $soat) {
                $nuevoEstado = $this->calcularEstado($soat->fecha_vencimiento, $hoy, $diasAnticipacionSoat);
                
                if ($soat->estado !== $nuevoEstado) {
                    $soat->estado = $nuevoEstado;
                    $soat->save();
                    $soatsActualizados++;
                }
            }
        });

        // Actualizar estados de Revisión Técnica
        $revisionesActualizadas = 0;
        RevisionTecnicaRecord::chunk(100, function ($revisiones) use ($hoy, $diasAnticipacionRevision, &$revisionesActualizadas) {
            foreach ($revisiones as $revision) {
                $nuevoEstado = $this->calcularEstado($revision->fecha_vencimiento, $hoy, $diasAnticipacionRevision);
                
                if ($revision->estado !== $nuevoEstado) {
                    $revision->estado = $nuevoEstado;
                    $revision->save();
                    $revisionesActualizadas++;
                }
            }
        });

        $this->info("✅ SOAT actualizados: {$soatsActualizados}");
        $this->info("✅ Revisiones técnicas actualizadas: {$revisionesActualizadas}");
        $this->info('Estados actualizados correctamente.');

        return Command::SUCCESS;
    }

    private function calcularEstado($fechaVencimiento, $hoy, $diasAnticipacion): string
    {
        $fechaVencimiento = Carbon::parse($fechaVencimiento);
        $fechaAlerta = $hoy->copy()->addDays($diasAnticipacion);

        if ($fechaVencimiento < $hoy) {
            return 'vencido';
        } elseif ($fechaVencimiento <= $fechaAlerta) {
            return 'proximo_a_vencer';
        } else {
            return 'vigente';
        }
    }
}