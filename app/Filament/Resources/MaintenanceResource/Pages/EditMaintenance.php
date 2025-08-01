<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditMaintenance extends EditRecord
{
    protected static string $resource = MaintenanceResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('🚗 Información del Vehículo')
                    ->description('Vehículo asociado al mantenimiento')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return Vehicle::all()->mapWithKeys(function ($vehicle) {
                                    return [$vehicle->id => $vehicle->placa . ' - ' . $vehicle->marca . ' ' . $vehicle->modelo . ' (' . $vehicle->anio . ')'];
                                });
                            })
                            ->helperText('Busque escribiendo la placa del vehículo')
                            ->prefixIcon('heroicon-o-magnifying-glass'),
                    ]),

                Forms\Components\Section::make('🔧 Información del Mantenimiento')
                    ->description('Detalles del trabajo realizado')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('tipo_mantenimiento')
                                    ->label('Tipo de Mantenimiento')
                                    ->required()
                                    ->options([
                                        'preventivo' => 'Preventivo',
                                        'correctivo' => 'Correctivo',
                                        'emergencia' => 'Emergencia',
                                    ])
                                    ->prefixIcon('heroicon-o-cog-6-tooth'),

                                Forms\Components\DatePicker::make('fecha_mantenimiento')
                                    ->label('Fecha del Mantenimiento')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now())
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),

                        Forms\Components\Textarea::make('trabajo_realizado')
                            ->label('Trabajo Realizado')
                            ->required()
                            ->rows(3),

                        Forms\Components\TextInput::make('pieza_afectada')
                            ->label('Pieza/Sistema Afectado')
                            ->required()
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-cog'),
                    ]),

                Forms\Components\Section::make('🏪 Información del Taller y Costos')
                    ->description('Datos del taller y costos del servicio')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_taller')
                            ->label('Nombre del Taller')
                            ->required()
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-building-storefront'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('precio_mantenimiento')
                                    ->label('Costo Total')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->prefixIcon('heroicon-o-currency-dollar'),

                                Forms\Components\TextInput::make('kilometraje')
                                    ->label('Kilometraje')
                                    ->numeric()
                                    ->suffix('km')
                                    ->minValue(0)
                                    ->prefixIcon('heroicon-o-chart-bar'),

                                Forms\Components\DatePicker::make('proximo_mantenimiento')
                                    ->label('Próximo Mantenimiento')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->minDate(now())
                                    ->prefixIcon('heroicon-o-forward'),
                            ]),
                    ]),

                Forms\Components\Section::make('⚡ Estado y Prioridad')
                    ->description('Estado actual y nivel de prioridad')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('estado')
                                    ->label('Estado del Mantenimiento')
                                    ->required()
                                    ->options([
                                        'completado' => 'Completado',
                                        'en_proceso' => 'En Proceso',
                                        'pendiente' => 'Pendiente',
                                        'cancelado' => 'Cancelado',
                                    ])
                                    ->prefixIcon('heroicon-o-check-circle'),

                                Forms\Components\Select::make('nivel_prioridad')
                                    ->label('Nivel de Prioridad')
                                    ->required()
                                    ->options([
                                        'baja' => 'Baja',
                                        'media' => 'Media',
                                        'alta' => 'Alta',
                                        'critica' => 'Crítica',
                                    ])
                                    ->prefixIcon('heroicon-o-exclamation-triangle'),
                            ]),
                    ]),

                Forms\Components\Section::make('📝 Observaciones y Adjuntos')
                    ->description('Información adicional y documentos del mantenimiento')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('comentarios')
                            ->label('Comentarios Adicionales')
                            ->rows(4),

                        Forms\Components\FileUpload::make('adjuntos')
                            ->label('Archivos Adjuntos')
                            ->multiple()
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->disk('private')
                            ->directory('mantenimientos')
                            ->visibility('private')
                            ->previewable()
                            ->reorderable()
                            ->appendFiles(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Ver')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->icon('heroicon-o-trash'),
        ];
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return "Editar Mantenimiento - {$record->vehicle->placa}";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Mantenimiento actualizado exitosamente';
    }
}