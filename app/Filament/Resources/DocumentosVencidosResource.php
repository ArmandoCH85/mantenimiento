<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentosVencidosResource\Pages;
use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DocumentosVencidosResource extends Resource
{
    protected static ?string $model = SoatRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Documentos Vencidos';

    protected static ?string $modelLabel = 'Documento Vencido';

    protected static ?string $pluralModelLabel = 'Documentos Vencidos';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return SoatRecord::query()
            ->where('fecha_vencimiento', '<', now())
            ->with('vehicle');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.marca')
                    ->label('Marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.modelo')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('tipo_documento')
                    ->label('Tipo Documento')
                    ->getStateUsing(fn () => 'SOAT')
                    ->color('info'),
                Tables\Columns\TextColumn::make('numero_poliza')
                    ->label('Número')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Fecha Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dias_vencido')
                    ->label('Días Vencido')
                    ->badge()
                    ->color('danger')
                    ->getStateUsing(fn ($record) => (int) now()->diffInDays($record->fecha_vencimiento))
                    ->formatStateUsing(fn ($state) => $state . ' días')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('compania_aseguradora')
                    ->label('Compañía')
                    ->searchable(),
                Tables\Columns\TextColumn::make('valor_pagado')
                    ->label('Valor Pagado')
                    ->money('PEN')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'placa')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('compania_aseguradora')
                    ->label('Compañía Aseguradora')
                    ->options([
                        'Seguros Bolívar' => 'Seguros Bolívar',
                        'SURA' => 'SURA',
                        'Mapfre' => 'Mapfre',
                        'AXA Colpatria' => 'AXA Colpatria',
                        'Allianz' => 'Allianz',
                        'Liberty Seguros' => 'Liberty Seguros',
                        'Previsora Seguros' => 'Previsora Seguros',
                        'Equidad Seguros' => 'Equidad Seguros',
                    ]),
            ])
            ->actions([
                // No necesitamos acciones de edición para reportes
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Acciones de exportación se manejarán a nivel de página
                ]),
            ])
            ->defaultSort('fecha_vencimiento', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getDocumentosVencidosQuery(): Builder
    {
        return SoatRecord::query()
            ->where('fecha_vencimiento', '<', now())
            ->with('vehicle')
            ->orderBy('fecha_vencimiento', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentosVencidos::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // No se pueden crear reportes
    }
}