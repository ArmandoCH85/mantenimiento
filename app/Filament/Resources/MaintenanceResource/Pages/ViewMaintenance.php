<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewMaintenance extends ViewRecord
{
    protected static string $resource = MaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar')
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->icon('heroicon-o-trash'),
            Actions\Action::make('downloadAttachments')
                ->label('Descargar Adjuntos')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => !empty($this->getRecord()->adjuntos) && is_array($this->getRecord()->adjuntos))
                ->action(function () {
                    $record = $this->getRecord();
                    $adjuntos = $record->adjuntos;
                    
                    if (empty($adjuntos) || !is_array($adjuntos)) {
                        $this->notify('warning', 'No hay archivos adjuntos para descargar');
                        return;
                    }

                    // Si hay un solo archivo, descargarlo directamente
                    if (count($adjuntos) === 1) {
                        $filePath = $adjuntos[0];
                        if (Storage::disk('private')->exists($filePath)) {
                            return Storage::disk('private')->download($filePath);
                        }
                    }

                    // Si hay múltiples archivos, crear un ZIP
                    $zip = new \ZipArchive();
                    $zipFileName = 'mantenimiento_' . $record->id . '_adjuntos.zip';
                    $zipPath = storage_path('app/temp/' . $zipFileName);
                    
                    // Crear directorio temporal si no existe
                    if (!file_exists(dirname($zipPath))) {
                        mkdir(dirname($zipPath), 0755, true);
                    }

                    if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                        foreach ($adjuntos as $index => $filePath) {
                            if (Storage::disk('private')->exists($filePath)) {
                                $fileContent = Storage::disk('private')->get($filePath);
                                $fileName = basename($filePath);
                                $zip->addFromString($fileName, $fileContent);
                            }
                        }
                        $zip->close();

                        return response()->download($zipPath)->deleteFileAfterSend(true);
                    }

                    $this->notify('error', 'Error al crear el archivo ZIP');
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('🚗 Información del Vehículo')
                    ->description('Detalles del vehículo al que se realizó el mantenimiento')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('vehicle.placa')
                                    ->label('Placa')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('vehicle.marca')
                                    ->label('Marca'),
                                TextEntry::make('vehicle.modelo')
                                    ->label('Modelo'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('vehicle.anio')
                                    ->label('Año'),
                                TextEntry::make('vehicle.kilometraje_actual')
                                    ->label('Kilometraje Actual')
                                    ->formatStateUsing(fn ($state) => number_format($state ?? 0) . ' km'),
                                TextEntry::make('vehicle.propietario_actual')
                                    ->label('Propietario'),
                            ]),
                    ]),

                Section::make('🔧 Información del Mantenimiento')
                    ->description('Detalles del trabajo realizado')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('tipo_mantenimiento')
                                    ->label('Tipo de Mantenimiento')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'preventivo' => 'success',
                                        'correctivo' => 'warning',
                                        'emergencia' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('fecha_mantenimiento')
                                    ->label('Fecha del Mantenimiento')
                                    ->date('d/m/Y'),
                            ]),
                        TextEntry::make('trabajo_realizado')
                            ->label('Trabajo Realizado')
                            ->columnSpanFull(),
                        TextEntry::make('pieza_afectada')
                            ->label('Pieza/Sistema Afectado'),
                    ]),

                Section::make('🏪 Información del Taller y Costos')
                    ->description('Datos del taller y costos del servicio')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextEntry::make('nombre_taller')
                            ->label('Nombre del Taller'),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('precio_mantenimiento')
                                    ->label('Costo Total')
                                    ->money('PEN'),
                                TextEntry::make('kilometraje')
                                    ->label('Kilometraje al Mantenimiento')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state) . ' km' : 'No registrado'),
                                TextEntry::make('proximo_mantenimiento')
                                    ->label('Próximo Mantenimiento')
                                    ->date('d/m/Y')
                                    ->placeholder('No programado'),
                            ]),
                    ]),

                Section::make('⚡ Estado y Prioridad')
                    ->description('Estado actual y nivel de prioridad del mantenimiento')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'completado' => 'success',
                                        'en_proceso' => 'warning',
                                        'pendiente' => 'danger',
                                        'cancelado' => 'gray',
                                        default => 'gray',
                                    }),
                                TextEntry::make('nivel_prioridad')
                                    ->label('Nivel de Prioridad')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'baja' => 'gray',
                                        'media' => 'warning',
                                        'alta' => 'danger',
                                        'critica' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Section::make('📝 Observaciones y Adjuntos')
                    ->description('Información adicional y documentos del mantenimiento')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('comentarios')
                            ->label('Comentarios Adicionales')
                            ->placeholder('Sin comentarios adicionales')
                            ->columnSpanFull(),
                        TextEntry::make('adjuntos')
                        ->label('Archivos Adjuntos')
                        ->formatStateUsing(function ($state) {
                            if (empty($state)) {
                                return 'Sin archivos adjuntos';
                            }

                            // Convertir a array si es string
                            $files = is_array($state) ? $state : [$state];
                            
                            // Filtrar archivos vacíos
                            $files = array_filter($files, function($file) {
                                return !empty($file);
                            });

                            if (empty($files)) {
                                return 'Sin archivos adjuntos';
                            }

                            $links = [];
                            foreach ($files as $file) {
                                // Limpiar el nombre del archivo
                                $fileName = trim($file);
                                
                                // Construir la ruta completa
                                $filePath = 'mantenimientos/' . $fileName;
                                
                                // Verificar si el archivo existe en el disco private
                                if (Storage::disk('private')->exists($filePath)) {
                                    // Generar URL de descarga usando la ruta existente
                                    $url = route('maintenance.download-attachment', [
                                        'maintenance' => $this->getRecord()->id,
                                        'file' => base64_encode($filePath)
                                    ]);
                                    $links[] = "<a href='{$url}' target='_blank' class='text-blue-600 hover:text-blue-800 underline'>{$fileName}</a>";
                                } else {
                                    $links[] = "<span class='text-red-500'>{$fileName} (archivo no encontrado)</span>";
                                }
                            }

                            return implode('<br>', $links);
                        })
                        ->html()
                        ->columnSpanFull(),
                    ]),

                Section::make('📊 Información del Sistema')
                    ->description('Datos de registro y actualización')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return "Mantenimiento - {$record->vehicle->placa}";
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}