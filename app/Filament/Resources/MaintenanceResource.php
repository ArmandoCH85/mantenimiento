<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Mantenimientos';

    protected static ?string $modelLabel = 'Mantenimiento';

    protected static ?string $pluralModelLabel = 'Mantenimientos';

    protected static ?string $navigationGroup = 'Gestión de Mantenimiento';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // El formulario se manejará en el Wizard
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('vehicle.marca')
                    ->label('Vehículo')
                    ->formatStateUsing(fn ($record) => $record->vehicle->marca . ' ' . $record->vehicle->modelo)
                    ->searchable(['marca', 'modelo'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_mantenimiento')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo_mantenimiento')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'preventivo' => 'success',
                        'correctivo' => 'warning',
                        'emergencia' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('precio_mantenimiento')
                    ->label('Costo')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completado' => 'success',
                        'en_proceso' => 'warning',
                        'pendiente' => 'danger',
                        'cancelado' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'placa')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('tipo_mantenimiento')
                    ->label('Tipo de Mantenimiento')
                    ->options([
                        'preventivo' => 'Preventivo',
                        'correctivo' => 'Correctivo',
                        'emergencia' => 'Emergencia',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'completado' => 'Completado',
                        'en_proceso' => 'En Proceso',
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
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_mantenimiento', 'desc')
            ->emptyStateHeading('No hay mantenimientos registrados')
            ->emptyStateDescription('Comience registrando el primer mantenimiento de un vehículo.')
            ->emptyStateIcon('heroicon-o-wrench-screwdriver');
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'view' => Pages\ViewMaintenance::route('/{record}'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}