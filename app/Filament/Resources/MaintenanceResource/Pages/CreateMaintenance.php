<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenance extends CreateRecord
{
    protected static string $resource = MaintenanceResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make($this->getSteps())
                    ->skippable()
                    ->persistStepInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Selección de Vehículo')
                ->description('Seleccione el vehículo para el mantenimiento')
                ->icon('heroicon-o-truck')
                ->schema([
                    Forms\Components\Section::make('🚗 Selección de Vehículo')
                        ->description('Seleccione el vehículo al que se realizará el mantenimiento')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Forms\Components\Select::make('vehicle_id')
                                ->label('Vehículo')
                                ->required()
                                ->relationship('vehicle', 'placa')
                                ->searchable(['placa', 'marca', 'modelo'])
                                ->preload()
                                ->live()
                                ->helperText('Busque por placa, marca o modelo')
                                ->prefixIcon('heroicon-o-magnifying-glass')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->placa} - {$record->marca} {$record->modelo}"),
                        ]),

                    Forms\Components\Section::make('📋 Información del Vehículo')
                        ->description('Detalles del vehículo seleccionado')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Placeholder::make('vehicle_info')
                                ->label('')
                                ->content(function (Forms\Get $get) {
                                    $vehicleId = $get('vehicle_id');
                                    if (!$vehicleId) {
                                        return 'Seleccione un vehículo para ver su información';
                                    }
                                    
                                    $vehicle = Vehicle::find($vehicleId);
                                    if (!$vehicle) {
                                        return 'Vehículo no encontrado';
                                    }
                                    
                                    return view('filament.components.vehicle-info', compact('vehicle'));
                                })
                                ->visible(fn (Forms\Get $get) => $get('vehicle_id'))
                                ->columnSpanFull(),
                        ])
                        ->visible(fn (Forms\Get $get) => $get('vehicle_id')),
                ]),

            Step::make('Información del Mantenimiento')
                ->description('Detalles del trabajo realizado')
                ->icon('heroicon-o-wrench-screwdriver')
                ->schema([
                    Forms\Components\Section::make('🔧 Información del Mantenimiento')
                        ->description('Detalles del trabajo realizado en el vehículo')
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
                                        ->helperText('Seleccione el tipo de mantenimiento realizado')
                                        ->prefixIcon('heroicon-o-cog-6-tooth'),

                                    Forms\Components\DatePicker::make('fecha_mantenimiento')
                                        ->label('Fecha del Mantenimiento')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->maxDate(now())
                                        ->helperText('Fecha en que se realizó el mantenimiento')
                                        ->prefixIcon('heroicon-o-calendar'),
                                ]),

                            Forms\Components\Textarea::make('trabajo_realizado')
                                ->label('Trabajo Realizado')
                                ->required()
                                ->rows(3)
                                ->helperText('Describa detalladamente el trabajo realizado'),

                            Forms\Components\TextInput::make('pieza_afectada')
                                ->label('Pieza/Sistema Afectado')
                                ->required()
                                ->maxLength(100)
                                ->helperText('Especifique la pieza o sistema trabajado')
                                ->prefixIcon('heroicon-o-cog'),
                        ]),
                ]),

            Step::make('Taller, Costos y Estado')
                ->description('Información del taller, costos y estado del mantenimiento')
                ->icon('heroicon-o-currency-dollar')
                ->schema([
                    Forms\Components\Section::make('🏪 Información del Taller')
                        ->description('Datos del taller que realizó el trabajo')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            Forms\Components\TextInput::make('nombre_taller')
                                ->label('Nombre del Taller')
                                ->required()
                                ->maxLength(100)
                                ->helperText('Nombre del taller o mecánico')
                                ->prefixIcon('heroicon-o-building-storefront'),
                        ]),

                    Forms\Components\Section::make('💰 Información Económica')
                        ->description('Costos del mantenimiento')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('precio_mantenimiento')
                                        ->label('Costo Total')
                                        ->required()
                                        ->numeric()
                                        ->prefix('S/')
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->helperText('Costo total del mantenimiento en soles')
                                        ->prefixIcon('heroicon-o-currency-dollar'),

                                    Forms\Components\TextInput::make('kilometraje')
                                        ->label('Kilometraje')
                                        ->numeric()
                                        ->suffix('km')
                                        ->minValue(0)
                                        ->helperText('Kilometraje del vehículo al momento del mantenimiento')
                                        ->prefixIcon('heroicon-o-chart-bar'),
                                ]),

                            Forms\Components\DatePicker::make('proximo_mantenimiento')
                                ->label('Próximo Mantenimiento')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->minDate(now())
                                ->helperText('Fecha estimada para el próximo mantenimiento')
                                ->prefixIcon('heroicon-o-forward'),
                        ]),

                    Forms\Components\Section::make('⚡ Estado y Prioridad')
                        ->description('Configure el estado y prioridad del mantenimiento')
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
                                        ->default('completado')
                                        ->helperText('Estado actual del mantenimiento')
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
                                        ->default('media')
                                        ->helperText('Nivel de prioridad del mantenimiento')
                                        ->prefixIcon('heroicon-o-exclamation-triangle'),
                                ]),
                        ]),
                ]),

            Step::make('Observaciones y Adjuntos')
                ->description('Comentarios adicionales y archivos adjuntos')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Section::make('📝 Observaciones y Adjuntos')
                        ->description('Información adicional y documentos del mantenimiento')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Textarea::make('comentarios')
                                ->label('Comentarios Adicionales')
                                ->rows(4)
                                ->helperText('Observaciones, recomendaciones o notas adicionales'),

                            Forms\Components\FileUpload::make('adjuntos')
                                ->label('Archivos Adjuntos')
                                ->multiple()
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->maxSize(5120) // 5MB
                                ->helperText('Suba fotos, facturas o documentos relacionados (máx. 5MB por archivo)')
                                ->disk('private')
                                ->directory('mantenimientos')
                                ->visibility('private')
                                ->previewable()
                                ->reorderable()
                                ->appendFiles(),
                        ]),
                ]),
        ];
    }

    public function getTitle(): string
    {
        return 'Registrar Nuevo Mantenimiento';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Mantenimiento registrado exitosamente';
    }
}