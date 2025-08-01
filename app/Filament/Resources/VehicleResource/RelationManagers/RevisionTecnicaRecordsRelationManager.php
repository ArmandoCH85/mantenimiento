<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RevisionTecnicaRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisionTecnicaRecords';

    protected static ?string $title = 'Revisiones Técnicas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Certificado')
                    ->description('Datos principales de la revisión técnica')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('numero_certificado')
                                    ->label('Número de Certificado')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ej: RT-2024-123456')
                                    ->helperText('Número único del certificado de revisión técnica')
                                    ->prefixIcon('heroicon-o-hashtag'),
                                Forms\Components\TextInput::make('centro_revision')
                                    ->label('Centro de Revisión')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Nombre del centro autorizado')
                                    ->helperText('Centro de diagnóstico autorizado')
                                    ->prefixIcon('heroicon-o-building-office-2'),
                            ]),
                    ]),

                Forms\Components\Section::make('Fechas y Vigencia')
                    ->description('Período de vigencia de la revisión técnica')
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

                Forms\Components\Section::make('Resultado de la Revisión')
                    ->description('Resultado y observaciones de la inspección')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('resultado')
                                    ->label('Resultado')
                                    ->required()
                                    ->options([
                                        'aprobado' => 'Aprobado',
                                        'aprobado_con_observaciones' => 'Aprobado con observaciones',
                                        'rechazado' => 'Rechazado',
                                    ])
                                    ->native(false)
                                    ->live()
                                    ->prefixIcon('heroicon-o-check-circle'),
                                Forms\Components\TextInput::make('valor_pagado')
                                    ->label('Valor Pagado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('0.00')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Valor total pagado por la revisión')
                                    ->prefixIcon('heroicon-o-banknotes'),
                            ]),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Detalles adicionales sobre la revisión técnica...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => in_array($get('resultado'), ['aprobado_con_observaciones', 'rechazado']))
                            ->required(fn (Forms\Get $get) => $get('resultado') === 'rechazado')
                            ->helperText('Describa las observaciones o motivos del rechazo'),
                    ]),

                Forms\Components\Section::make('Estado del Documento')
                    ->description('Estado actual de la revisión técnica (calculado automáticamente)')
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
            ->recordTitleAttribute('numero_certificado')
            ->columns([
                Tables\Columns\TextColumn::make('numero_certificado')
                    ->label('Número de Certificado')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('centro_revision')
                    ->label('Centro de Revisión')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->limit(20),
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
                Tables\Columns\BadgeColumn::make('resultado')
                    ->label('Resultado')
                    ->colors([
                        'success' => 'aprobado',
                        'warning' => 'aprobado_con_observaciones',
                        'danger' => 'rechazado',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'aprobado',
                        'heroicon-o-exclamation-triangle' => 'aprobado_con_observaciones',
                        'heroicon-o-x-circle' => 'rechazado',
                    ]),
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
                Tables\Filters\SelectFilter::make('resultado')
                    ->label('Resultado')
                    ->options([
                        'aprobado' => 'Aprobado',
                        'aprobado_con_observaciones' => 'Aprobado con observaciones',
                        'rechazado' => 'Rechazado',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'vigente' => 'Vigente',
                        'proximo_a_vencer' => 'Próximo a vencer',
                        'vencido' => 'Vencido',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('vencimiento_proximo')
                    ->label('Vence en 30 días')
                    ->query(fn (Builder $query): Builder => $query->where('fecha_vencimiento', '<=', now()->addDays(30)))
                    ->toggle(),
                Tables\Filters\Filter::make('rechazados')
                    ->label('Solo rechazados')
                    ->query(fn (Builder $query): Builder => $query->where('resultado', 'rechazado'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Revisión Técnica')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Registrar Nueva Revisión Técnica')
                    ->modalDescription('Complete la información de la nueva revisión técnica para este vehículo.')
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
                    ->modalHeading('Editar Revisión Técnica')
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
            ->emptyStateHeading('No hay revisiones técnicas')
            ->emptyStateDescription('Comience registrando la primera revisión técnica para este vehículo.')
            ->emptyStateIcon('heroicon-o-document-check')
            ->defaultSort('fecha_vencimiento', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}