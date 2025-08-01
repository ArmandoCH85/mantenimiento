<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\Maintenance;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/maintenance/{maintenance}/download-attachment/{file}', function (Maintenance $maintenance, string $file) {
    $filePath = base64_decode($file);
    
    // Verificar que el archivo pertenece al mantenimiento
    if (!in_array($filePath, $maintenance->adjuntos ?? [])) {
        abort(404, 'Archivo no encontrado');
    }
    
    // Verificar que el archivo existe
    if (!Storage::disk('private')->exists($filePath)) {
        abort(404, 'Archivo no encontrado en el almacenamiento');
    }
    
    $fileName = basename($filePath);
    
    return Storage::disk('private')->download($filePath, $fileName);
})->name('maintenance.download-attachment');
