<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenances extends ListRecords
{
    protected static string $resource = MaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar Mantenimiento')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
        ];
    }

    public function getTitle(): string
    {
        return 'Mantenimientos';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí se pueden agregar widgets de estadísticas
        ];
    }
}