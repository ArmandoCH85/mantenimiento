<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SoatRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'soatRecords';

    protected static ?string $title = 'Registros SOAT';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Póliza')
                    ->description('Datos principales del SOAT')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('numero_poliza')
                                    ->label('Número de Póliza')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ej: SOA-2024-123456')
                                    ->helperText('Número único de la póliza SOAT')
                                    ->prefixIcon('heroicon-o-hashtag'),
                                Forms\Components\Select::make('compania_aseguradora')
                                    ->label('Compañía Aseguradora')
                                    ->required()
                                    ->searchable()
                                    ->options([
                                        'Seguros Bolívar' => 'Seguros Bolívar',
                                        'SURA' => 'SURA',
                                        'Mapfre' => 'Mapfre',
                                        'AXA Colpatria' => 'AXA Colpatria',
                                        'Allianz' => 'Allianz',
                                        'Liberty Seguros' => 'Liberty Seguros',
                                        'Previsora Seguros' => 'Previsora Seguros',
                                        'Equidad Seguros' => 'Equidad Seguros',
                                        'Otro' => 'Otro',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-building-office'),
                            ]),
                    ]),

                Forms\Components\Section::make('Fechas y Vigencia')
                    ->description('Período de vigencia del SOAT')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_emision')
                                    ->label('Fecha de Emisión')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now())
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            // Calcular fecha de vencimiento (1 año después)
                                            $vencimiento = \Carbon\Carbon::parse($state)->addYear();
                                            $set('fecha_vencimiento', $vencimiento->format('Y-m-d'));
                                        }
                                    })
                                    ->prefixIcon('heroicon-o-calendar'),
                                Forms\Components\DatePicker::make('fecha_vencimiento')
                                    ->label('Fecha de Vencimiento')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->minDate(fn (Forms\Get $get) => $get('fecha_emision') ? \Carbon\Carbon::parse($get('fecha_emision'))->addDay() : now())
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->helperText('Se calcula automáticamente (1 año después de la emisión)'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Económica')
                    ->description('Valor pagado por el SOAT')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('valor_pagado')
                            ->label('Valor Pagado')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('0.00')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Valor total pagado por el SOAT')
                            ->prefixIcon('heroicon-o-banknotes'),
                    ]),

                Forms\Components\Section::make('Estado del Documento')
                    ->description('Estado actual del SOAT (calculado automáticamente)')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Placeholder::make('estado_info')
                            ->label('Estado')
                            ->content('El estado se calcula automáticamente basado en las fechas de vigencia')
                            ->helperText('Vigente: más de 30 días | Próximo a vencer: 30 días o menos | Vencido: fecha pasada'),
                    ])
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_poliza')
            ->columns([
                Tables\Columns\TextColumn::make('numero_poliza')
                    ->label('Número de Póliza')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('compania_aseguradora')
                    ->label('Aseguradora')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office')
                    ->limit(15),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color(fn ($record) => match (true) {
                        $record->fecha_vencimiento < now() => 'danger',
                        $record->fecha_vencimiento <= now()->addDays(30) => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('valor_pagado')
                    ->label('Valor')
                    ->money('PEN')
                    ->sortable()
                    ->icon('heroicon-o-banknotes'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'vigente',
                        'warning' => 'proximo_a_vencer',
                        'danger' => 'vencido',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'vigente',
                        'heroicon-o-exclamation-triangle' => 'proximo_a_vencer',
                        'heroicon-o-x-circle' => 'vencido',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'vigente' => 'Vigente',
                        'proximo_a_vencer' => 'Próximo a vencer',
                        'vencido' => 'Vencido',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('compania_aseguradora')
                    ->label('Aseguradora')
                    ->options([
                        'Seguros Bolívar' => 'Seguros Bolívar',
                        'SURA' => 'SURA',
                        'Mapfre' => 'Mapfre',
                        'AXA Colpatria' => 'AXA Colpatria',
                        'Allianz' => 'Allianz',
                        'Liberty Seguros' => 'Liberty Seguros',
                        'Previsora Seguros' => 'Previsora Seguros',
                        'Equidad Seguros' => 'Equidad Seguros',
                        'Otro' => 'Otro',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('vencimiento_proximo')
                    ->label('Vence en 30 días')
                    ->query(fn (Builder $query): Builder => $query->where('fecha_vencimiento', '<=', now()->addDays(30)))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar SOAT')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Registrar Nuevo SOAT')
                    ->modalDescription('Complete la información del nuevo registro SOAT para este vehículo.')
                    ->modalWidth('4xl')
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Registro SOAT')
                    ->modalWidth('4xl'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay registros SOAT')
            ->emptyStateDescription('Comience registrando el primer SOAT para este vehículo.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->defaultSort('fecha_vencimiento', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}