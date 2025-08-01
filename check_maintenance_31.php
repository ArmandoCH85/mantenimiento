<?php

use App\Models\Maintenance;
use Illuminate\Support\Facades\Storage;

$maintenance = Maintenance::find(31);

if (!$maintenance) {
    echo "Mantenimiento 31 no encontrado\n";
    exit;
}

echo "Mantenimiento 31:\n";
echo "Adjuntos: " . json_encode($maintenance->adjuntos) . "\n";

$adjuntos = is_array($maintenance->adjuntos) ? $maintenance->adjuntos : [$maintenance->adjuntos];

foreach ($adjuntos as $file) {
    if (empty($file)) continue;
    
    $fileName = trim($file);
    $filePath = str_starts_with($fileName, 'mantenimientos/') ? $fileName : 'mantenimientos/' . $fileName;
    
    echo "Archivo: {$fileName}\n";
    echo "Ruta completa: {$filePath}\n";
    echo "Existe: " . (Storage::disk('private')->exists($filePath) ? 'SI' : 'NO') . "\n";
    echo "---\n";
}