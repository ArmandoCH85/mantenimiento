{{-- Vista de login simple y limpia para Filament 3 --}}
<style>
/* Reset básico */
* {
    box-sizing: border-box;
}

/* Fondo simple */
body {
    background: #f8fafc !important;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    margin: 0;
    padding: 0;
}

/* Layout principal */
.fi-simple-layout {
    background: transparent !important;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

/* Contenedor del formulario */
.fi-simple-main {
    background: white !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid #e5e7eb !important;
    max-width: 400px;
    width: 100%;
    padding: 2rem !important;
}

/* Header del formulario */
.fi-simple-header {
    text-align: center !important;
    margin-bottom: 2rem !important;
}

.fi-logo {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    color: #1f2937 !important;
    margin-bottom: 0.5rem !important;
}

/* Campos del formulario */
.fi-fo-field-wrp {
    margin-bottom: 1.25rem !important;
}

.fi-fo-field-wrp-label {
    font-weight: 500 !important;
    color: #374151 !important;
    margin-bottom: 0.5rem !important;
    font-size: 0.875rem !important;
}

.fi-input {
    border-radius: 6px !important;
    border: 1px solid #d1d5db !important;
    background: white !important;
    transition: border-color 0.2s ease !important;
    padding: 0.75rem !important;
    font-size: 1rem !important;
    width: 100% !important;
}

.fi-input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    outline: none !important;
}

/* Checkbox */
.fi-checkbox-input {
    accent-color: #3b82f6 !important;
}

.fi-checkbox-label {
    color: #6b7280 !important;
    font-size: 0.875rem !important;
}

/* Botón de login */
.fi-btn-primary {
    background: #3b82f6 !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 0.75rem 1.5rem !important;
    font-weight: 500 !important;
    font-size: 1rem !important;
    transition: background-color 0.2s ease !important;
    width: 100% !important;
    color: white !important;
}

.fi-btn-primary:hover {
    background: #2563eb !important;
}

.fi-btn-primary:active {
    background: #1d4ed8 !important;
}

/* Enlaces */
a {
    color: #3b82f6 !important;
    text-decoration: none !important;
    font-weight: 500;
}

a:hover {
    color: #2563eb !important;
    text-decoration: underline !important;
}

/* Responsive */
@media (max-width: 640px) {
    .fi-simple-layout {
        padding: 1rem;
    }
    
    .fi-simple-main {
        padding: 1.5rem !important;
    }
}

/* Modo oscuro */
@media (prefers-color-scheme: dark) {
    body {
        background: #111827 !important;
    }
    
    .fi-simple-main {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }
    
    .fi-input {
        background: #374151 !important;
        border-color: #4b5563 !important;
        color: #f9fafb !important;
    }
    
    .fi-input:focus {
        border-color: #3b82f6 !important;
    }
    
    .fi-fo-field-wrp-label {
        color: #f9fafb !important;
    }
    
    .fi-logo {
        color: #f9fafb !important;
    }
    
    .fi-checkbox-label {
        color: #d1d5db !important;
    }
}
</style>