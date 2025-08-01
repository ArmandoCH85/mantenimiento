<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';

    protected static ?string $title = 'Mantenimientos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Mantenimiento')
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
                                        'predictivo' => 'Predictivo',
                                        'emergencia' => 'Emergencia',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-cog-6-tooth'),
                                Forms\Components\TextInput::make('pieza_afectada')
                                    ->label('Pieza/Sistema Afectado')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ej: Motor, Frenos, Transmisión...')
                                    ->prefixIcon('heroicon-o-cog'),
                            ]),
                        Forms\Components\Textarea::make('trabajo_realizado')
                            ->label('Trabajo Realizado')
                            ->required()
                            ->placeholder('Describa detalladamente el trabajo realizado...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Incluya detalles específicos del mantenimiento realizado'),
                    ]),

                Forms\Components\Section::make('Información del Taller')
                    ->description('Datos del lugar donde se realizó el mantenimiento')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre_taller')
                                    ->label('Nombre del Taller')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Nombre del taller o mecánico')
                                    ->prefixIcon('heroicon-o-building-storefront'),
                                Forms\Components\DatePicker::make('fecha_mantenimiento')
                                    ->label('Fecha del Mantenimiento')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now())
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Técnica')
                    ->description('Datos técnicos del vehículo al momento del mantenimiento')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('kilometraje')
                                    ->label('Kilometraje')
                                    ->numeric()
                                    ->suffix('km')
                                    ->placeholder('0')
                                    ->minValue(0)
                                    ->helperText('Kilometraje del vehículo al momento del mantenimiento')
                                    ->prefixIcon('heroicon-o-map'),
                                Forms\Components\TextInput::make('proximo_mantenimiento')
                                    ->label('Próximo Mantenimiento (km)')
                                    ->numeric()
                                    ->suffix('km')
                                    ->placeholder('0')
                                    ->minValue(0)
                                    ->helperText('Kilometraje estimado para el próximo mantenimiento')
                                    ->prefixIcon('heroicon-o-forward'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Económica')
                    ->description('Costos del mantenimiento')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('precio_mantenimiento')
                            ->label('Precio del Mantenimiento')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('0.00')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Costo total del mantenimiento')
                            ->prefixIcon('heroicon-o-banknotes'),
                    ]),

                Forms\Components\Section::make('Estado y Prioridad')
                    ->description('Estado actual y nivel de prioridad')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('estado')
                                    ->label('Estado')
                                    ->required()
                                    ->options([
                                        'completado' => 'Completado',
                                        'en_progreso' => 'En progreso',
                                        'pendiente' => 'Pendiente',
                                        'cancelado' => 'Cancelado',
                                    ])
                                    ->default('completado')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-check-circle'),
                                Forms\Components\Select::make('nivel_prioridad')
                                    ->label('Nivel de Prioridad')
                                    ->options([
                                        'baja' => 'Baja',
                                        'media' => 'Media',
                                        'alta' => 'Alta',
                                        'critica' => 'Crítica',
                                    ])
                                    ->default('media')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-exclamation-triangle'),
                            ]),
                    ]),

                Forms\Components\Section::make('Observaciones y Adjuntos')
                    ->description('Comentarios adicionales y archivos adjuntos')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('comentarios')
                            ->label('Comentarios Adicionales')
                            ->placeholder('Observaciones, recomendaciones o notas adicionales...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('adjuntos')
                            ->label('Archivos Adjuntos')
                            ->multiple()
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->helperText('Suba fotos, facturas o documentos relacionados (máx. 5MB por archivo)')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('trabajo_realizado')
            ->columns([
                Tables\Columns\TextColumn::make('fecha_mantenimiento')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->weight('medium'),
                Tables\Columns\BadgeColumn::make('tipo_mantenimiento')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'preventivo',
                        'warning' => 'correctivo',
                        'info' => 'predictivo',
                        'danger' => 'emergencia',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'preventivo',
                        'heroicon-o-wrench-screwdriver' => 'correctivo',
                        'heroicon-o-chart-bar' => 'predictivo',
                        'heroicon-o-exclamation-triangle' => 'emergencia',
                    ]),
                Tables\Columns\TextColumn::make('trabajo_realizado')
                    ->label('Trabajo Realizado')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->trabajo_realizado),
                Tables\Columns\TextColumn::make('precio_mantenimiento')
                    ->label('Costo')
                    ->money('PEN')
                    ->sortable()
                    ->icon('heroicon-o-banknotes'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completado',
                        'warning' => 'en_progreso',
                        'gray' => 'pendiente',
                        'danger' => 'cancelado',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'completado',
                        'heroicon-o-clock' => 'en_progreso',
                        'heroicon-o-pause-circle' => 'pendiente',
                        'heroicon-o-x-circle' => 'cancelado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_mantenimiento')
                    ->label('Tipo')
                    ->options([
                        'preventivo' => 'Preventivo',
                        'correctivo' => 'Correctivo',
                        'predictivo' => 'Predictivo',
                        'emergencia' => 'Emergencia',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'completado' => 'Completado',
                        'en_progreso' => 'En progreso',
                        'pendiente' => 'Pendiente',
                        'cancelado' => 'Cancelado',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('nivel_prioridad')
                    ->label('Prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                        'critica' => 'Crítica',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('fecha_mantenimiento')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_mantenimiento', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_mantenimiento', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('mantenimientos_costosos')
                    ->label('Mantenimientos costosos (>S/1,500)')
                    ->query(fn (Builder $query): Builder => $query->where('precio_mantenimiento', '>', 1500))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Mantenimiento')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Registrar Nuevo Mantenimiento')
                    ->modalDescription('Complete la información del nuevo mantenimiento para este vehículo.')
                    ->modalWidth('5xl')
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Mantenimiento')
                    ->modalWidth('5xl'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay mantenimientos registrados')
            ->emptyStateDescription('Comience registrando el primer mantenimiento para este vehículo.')
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
            ->defaultSort('fecha_mantenimiento', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}