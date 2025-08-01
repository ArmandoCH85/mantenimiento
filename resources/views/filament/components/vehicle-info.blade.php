<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-3">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Placa:</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded">{{ $vehicle->placa }}</span>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Marca:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ $vehicle->marca }}</span>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Modelo:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ $vehicle->modelo }}</span>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Año:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ $vehicle->anio }}</span>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Kilometraje:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ number_format($vehicle->kilometraje_actual ?? 0) }} km</span>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Propietario:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ $vehicle->propietario_actual }}</span>
            </div>
        </div>
    </div>
</div>