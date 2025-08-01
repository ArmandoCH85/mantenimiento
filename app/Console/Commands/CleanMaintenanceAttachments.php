<?php

namespace App\Console\Commands;

use App\Models\Maintenance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanMaintenanceAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:clean-attachments 
                            {--dry-run : Solo mostrar qué se haría sin hacer cambios}
                            {--remove-orphans : Eliminar referencias a archivos que no existen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia archivos adjuntos huérfanos y detecta problemas con los adjuntos de mantenimiento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Analizando archivos adjuntos de mantenimientos...');
        
        $dryRun = $this->option('dry-run');
        $removeOrphans = $this->option('remove-orphans');
        
        // Obtener todos los mantenimientos con adjuntos
        $maintenances = Maintenance::whereNotNull('adjuntos')
            ->where('adjuntos', '!=', '')
            ->where('adjuntos', '!=', '[]')
            ->get();
            
        $this->info("📊 Encontrados {$maintenances->count()} mantenimientos con adjuntos registrados");
        
        $missingFiles = [];
        $validFiles = [];
        $updatedMaintenances = 0;
        
        foreach ($maintenances as $maintenance) {
            $adjuntos = is_array($maintenance->adjuntos) ? $maintenance->adjuntos : [$maintenance->adjuntos];
            $adjuntos = array_filter($adjuntos, function($file) {
                return !empty($file);
            });
            
            if (empty($adjuntos)) {
                continue;
            }
            
            $validAdjuntos = [];
            $maintenanceMissingFiles = [];
            
            foreach ($adjuntos as $file) {
                $fileName = trim($file);
                
                // Determinar la ruta del archivo
                $filePath = $fileName;
                if (!str_starts_with($fileName, 'mantenimientos/')) {
                    $filePath = 'mantenimientos/' . $fileName;
                }
                
                if (Storage::disk('private')->exists($filePath)) {
                    $validAdjuntos[] = $fileName;
                    $validFiles[] = [
                        'maintenance_id' => $maintenance->id,
                        'file' => $fileName,
                        'path' => $filePath
                    ];
                } else {
                    $maintenanceMissingFiles[] = $fileName;
                    $missingFiles[] = [
                        'maintenance_id' => $maintenance->id,
                        'file' => $fileName,
                        'path' => $filePath
                    ];
                }
            }
            
            // Si hay archivos faltantes y se debe limpiar
            if (!empty($maintenanceMissingFiles) && $removeOrphans) {
                if ($dryRun) {
                    $this->warn("🔄 [DRY RUN] Mantenimiento #{$maintenance->id}: Eliminaría " . count($maintenanceMissingFiles) . " referencia(s) de archivo(s) faltante(s)");
                } else {
                    $maintenance->update(['adjuntos' => $validAdjuntos]);
                    $updatedMaintenances++;
                    $this->info("✅ Mantenimiento #{$maintenance->id}: Eliminadas " . count($maintenanceMissingFiles) . " referencia(s) de archivo(s) faltante(s)");
                }
            }
            
            // Mostrar información del mantenimiento
            if (!empty($maintenanceMissingFiles)) {
                $this->error("❌ Mantenimiento #{$maintenance->id}: " . count($maintenanceMissingFiles) . " archivo(s) faltante(s):");
                foreach ($maintenanceMissingFiles as $missingFile) {
                    $this->line("   - {$missingFile}");
                }
            }
        }
        
        // Resumen
        $this->newLine();
        $this->info('📈 RESUMEN:');
        $this->info("✅ Archivos válidos: " . count($validFiles));
        $this->error("❌ Archivos faltantes: " . count($missingFiles));
        
        if ($removeOrphans && !$dryRun) {
            $this->info("🔄 Mantenimientos actualizados: {$updatedMaintenances}");
        }
        
        // Mostrar archivos físicos huérfanos (archivos en storage sin referencia en BD)
        $this->newLine();
        $this->info('🔍 Verificando archivos físicos huérfanos...');
        
        $physicalFiles = [];
        if (Storage::disk('private')->exists('mantenimientos')) {
            $physicalFiles = Storage::disk('private')->files('mantenimientos');
        }
        
        $referencedFiles = array_map(function($file) {
            return $file['path'];
        }, $validFiles);
        
        $orphanFiles = array_diff($physicalFiles, $referencedFiles);
        
        if (!empty($orphanFiles)) {
            $this->warn("🗂️ Archivos físicos sin referencia en BD (" . count($orphanFiles) . "):");
            foreach ($orphanFiles as $orphanFile) {
                $this->line("   - {$orphanFile}");
            }
        } else {
            $this->info("✅ No se encontraron archivos físicos huérfanos");
        }
        
        // Sugerencias
        $this->newLine();
        if (!empty($missingFiles)) {
            $this->warn('💡 SUGERENCIAS:');
            $this->line('   - Ejecuta con --remove-orphans para limpiar referencias a archivos faltantes');
            $this->line('   - Usa --dry-run para ver qué cambios se harían sin aplicarlos');
            $this->line('   - Verifica si los archivos fueron movidos o eliminados manualmente');
        }
        
        if (!empty($orphanFiles)) {
            $this->line('   - Considera eliminar archivos físicos huérfanos si no son necesarios');
        }
        
        return Command::SUCCESS;
    }
}