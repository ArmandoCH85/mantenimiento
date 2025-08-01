# Sistema de Mantenimiento de Vehículos

## Metadatos del Proyecto
- **Versión**: 2.0 (2025-07-31)
- **Framework**: Laravel 12
- **Admin Panel**: Filament 3
- **Base de Datos**: MySQL/PostgreSQL
- **Prioridad**: ALTA y MEDIA implementadas

---

## Resumen Ejecutivo

Sistema web integral para gestión de mantenimiento vehicular con control automatizado de documentos obligatorios (SOAT, Revisión Técnica) y registro histórico de servicios realizados.

### Objetivos Principales
1. Control automático de vencimientos de documentos
2. Registro detallado de mantenimientos
3. Sistema de alertas configurables
4. Reportes y análisis de costos
5. Historial completo por vehículo

---

## Requerimientos Funcionales

### RF-001: Gestión de Vehículos
**Prioridad:** ALTA
**Descripción:** El sistema debe permitir el registro, modificación, consulta y eliminación lógica de vehículos.

#### RF-001.1: Registro de Vehículos
- **Actor:** Usuario/Administrador
- **Precondición:** Usuario autenticado con permisos
- **Flujo Principal:**
  1. Usuario accede al formulario de registro de vehículo
  2. Sistema presenta campos obligatorios y opcionales
  3. Usuario completa información del vehículo
  4. Sistema valida unicidad de placa y formato
  5. Sistema guarda el vehículo en base de datos
  6. Sistema confirma registro exitoso

**Criterios de Aceptación:**
- [x] Placa debe ser única en el sistema
- [x] Validación de formato de placa según normativa
- [x] Campos obligatorios: placa, marca, modelo
- [x] Año no puede ser mayor al actual
- [x] Kilometraje debe ser numérico positivo

#### RF-001.2: Consulta de Vehículos
- **Funcionalidades:**
  - Listado paginado de vehículos
  - Búsqueda por placa, marca, modelo
  - Filtros por tipo, año, propietario
  - Ordenamiento por cualquier columna
  - Vista detalle con historial completo

#### RF-001.3: Modificación de Vehículos
- **Restricciones:**
  - Placa no modificable después del registro
  - Kilometraje solo puede incrementarse
  - Historial de cambios registrado

#### RF-001.4: Eliminación de Vehículos
- **Comportamiento:**
  - Eliminación lógica (soft delete)
  - Verificar que no tenga documentos vigentes
  - Mantener historial de mantenimientos

---

### RF-002: Gestión de SOAT
**Prioridad:** ALTA
**Descripción:** Control completo del Seguro Obligatorio de Accidentes de Tránsito con cálculo automático de estados.

#### RF-002.1: Registro de SOAT
- **Campos Obligatorios:**
  - Vehículo asociado
  - Fecha de emisión
  - Fecha de vencimiento
- **Campos Opcionales:**
  - Número de póliza
  - Compañía aseguradora
  - Valor pagado

**Validaciones:**
- Fecha vencimiento > fecha emisión
- Fecha emisión ≤ fecha actual
- Solo un SOAT vigente por vehículo

#### RF-002.2: Cálculo Automático de Estados
**Estados del Sistema:**
```
VIGENTE: días_restantes > días_configurados_alerta
PRÓXIMO_A_VENCER: 0 < días_restantes ≤ días_configurados_alerta  
VENCIDO: días_restantes ≤ 0
```

**Implementación:**
- Campo calculado en base de datos
- Actualización automática vía triggers o scheduler
- Visible en listados y dashboard

#### RF-002.3: Historial de SOAT
- Registro cronológico por vehículo
- Visualización de todos los SOAT anteriores
- Filtros por período y estado
- Exportación de datos

---

### RF-003: Gestión de Revisión Técnica
**Prioridad:** ALTA
**Descripción:** Control de revisiones técnicas vehiculares con misma lógica de estados que SOAT.

#### RF-003.1: Registro de Revisión Técnica
- **Campos Específicos:**
  - Número de certificado
  - Centro de revisión
  - Resultado (aprobado/rechazado)
  - Observaciones
  - Valor pagado

#### RF-003.2: Estados y Alertas
- Misma lógica de cálculo que SOAT
- Configuración independiente de días de alerta
- Integración con sistema de notificaciones

#### RF-003.3: Reportes de Revisión Técnica
- Vehículos pendientes de revisión
- Centros de revisión más utilizados
- Histórico de aprobaciones/rechazos

---

### RF-004: Sistema de Alertas Configurables
**Prioridad:** ALTA
**Descripción:** Sistema flexible de alertas para documentos próximos a vencer.

#### RF-004.1: Configuración de Alertas
- **Niveles de Configuración:**
  - Global (para todo el sistema)
  - Por usuario (opcional)
  - Por tipo de documento

**Parámetros Configurables:**
- Días de anticipación para SOAT
- Días de anticipación para Revisión Técnica
- Habilitación de notificaciones email
- Frecuencia de verificación

#### RF-004.2: Tipos de Alertas
**Dashboard:**
- Contadores de documentos por estado
- Lista de próximos vencimientos
- Indicadores visuales (badges, colores)

**Notificaciones:**
- Email automático diario
- Resumen semanal de vencimientos
- Alertas críticas inmediatas

#### RF-004.3: Gestión de Notificaciones
- Configuración de destinatarios
- Templates personalizables
- Log de notificaciones enviadas
- Opciones de suscripción/desuscripción

---

### RF-005: Gestión de Mantenimientos
**Prioridad:** ALTA
**Descripción:** Registro completo y detallado de todos los mantenimientos realizados a los vehículos.

#### RF-005.1: Registro de Mantenimientos
**Información Básica:**
- Vehículo (referencia, no duplicar datos)
- Fecha del mantenimiento
- Trabajo realizado (descripción detallada)
- Pieza afectada
- Precio del mantenimiento
- Taller que realizó el trabajo

**Información Extendida:**
- Kilometraje al momento del servicio
- Tipo: preventivo, correctivo, emergencia
- Estado: completado, pendiente, en proceso
- Nivel de prioridad: baja, media, alta, crítica
- Comentarios adicionales
- Próximo mantenimiento sugerido
- Adjuntos (facturas, fotos, documentos)

#### RF-005.2: Validaciones de Mantenimientos
- Fecha no futura
- Precio positivo
- Kilometraje ≥ último registrado
- Vehículo debe existir y estar activo
- Campos obligatorios completos

#### RF-005.3: Historial de Mantenimientos
**Visualización:**
- Cronología por vehículo
- Línea de tiempo interactiva
- Agrupación por tipo de mantenimiento
- Resumen de costos acumulados

**Filtros Avanzados:**
- Por rango de fechas
- Por taller
- Por tipo de mantenimiento
- Por pieza afectada
- Por rango de precios
- Por estado

#### RF-005.4: Programación de Mantenimientos
- Registro de próximos mantenimientos
- Alertas de mantenimientos programados
- Seguimiento de mantenimientos pendientes
- Recordatorios automáticos

---

### RF-006: Dashboard y Reportes
**Prioridad:** MEDIA
**Descripción:** Panel de control centralizado con métricas y reportes del sistema.

#### RF-006.1: Dashboard Principal
**Widgets Obligatorios:**
- Resumen de vehículos (total, activos)
- Estados de documentos (contadores por estado)
- Alertas críticas (vencidos hoy/esta semana)
- Próximos mantenimientos (15 días)
- Gastos del mes actual

**Widgets Adicionales:**
- Gráfico de gastos mensuales
- Top 5 talleres más utilizados
- Distribución por tipo de mantenimiento
- Eficiencia preventivo vs correctivo

#### RF-006.2: Reportes del Sistema
**Reporte de Gastos:**
- Por período (mensual, anual)
- Por vehículo individual
- Por taller
- Por tipo de mantenimiento
- Comparativas y tendencias

**Reporte de Vencimientos:**
- Documentos próximos a vencer (30, 60, 90 días)
- Histórico de renovaciones
- Proyección de gastos en documentos

**Reporte de Eficiencia:**
- Ratio preventivo vs correctivo
- Tiempo promedio entre mantenimientos
- Análisis de costos por kilómetro
- Ranking de vehículos por costo

#### RF-006.3: Exportación de Datos
- Formatos: PDF, Excel, CSV
- Reportes programados
- Envío automático por email
- Configuración de destinatarios

---

### RF-007: Seguridad y Usuarios
**Prioridad:** MEDIA
**Descripción:** Sistema de autenticación, autorización y auditoría.

#### RF-007.1: Gestión de Usuarios
**Roles del Sistema:**
- **Super Administrador:** Acceso completo
- **Administrador:** Gestión operativa completa
- **Usuario:** Consulta y registro básico
- **Solo Lectura:** Únicamente consultas

**Permisos por Módulo:**
- Vehículos: crear, leer, actualizar, eliminar
- Documentos: crear, leer, actualizar
- Mantenimientos: crear, leer, actualizar, eliminar
- Configuraciones: leer, actualizar (solo admin)
- Reportes: generar, exportar

#### RF-007.2: Auditoría del Sistema
- Log de todas las operaciones críticas
- Registro de cambios en datos importantes
- Trazabilidad de acciones por usuario
- Backup automático de información

---

### RF-008: Integraciones y APIs
**Prioridad:** BAJA
**Descripción:** Funcionalidades de integración con sistemas externos.

#### RF-008.1: API REST
- Endpoints para consulta de vehículos
- Integración con sistemas de talleres
- Webhook para notificaciones externas
- Autenticación via tokens

#### RF-008.2: Importación/Exportación Masiva
- Importación de vehículos via Excel/CSV
- Exportación completa de datos
- Validación de datos importados
- Reporte de errores en importación

---

### RF-009: Configuración del Sistema
**Prioridad:** MEDIA
**Descripción:** Configuraciones generales y personalización del sistema.

#### RF-009.1: Configuraciones Generales
- Días de alerta por tipo de documento
- Configuración de email (SMTP)
- Timezone del sistema
- Moneda y formato de números
- Idioma de la interfaz

#### RF-009.2: Personalización
- Logo de la empresa
- Colores del tema
- Campos adicionales por entidad
- Templates de reportes
- Formatos de exportación

---

### RF-010: Mantenimiento del Sistema
**Prioridad:** BAJA
**Descripción:** Funcionalidades para mantenimiento y administración del sistema.

#### RF-010.1: Comandos de Mantenimiento
```bash
# Verificación de documentos vencidos
php artisan vehicles:check-expired

# Envío de alertas
php artisan vehicles:send-alerts

# Limpieza de datos antiguos
php artisan vehicles:cleanup

# Backup de base de datos
php artisan backup:run
```

#### RF-010.2: Monitoreo del Sistema
- Estado de servicios críticos
- Métricas de performance
- Logs de errores
- Estadísticas de uso

**Criterios de Aceptación Generales:**
- [ ] Todos los RF-001 a RF-005 implementados (ALTA prioridad)
- [ ] Dashboard funcional con widgets básicos
- [ ] Sistema de usuarios y permisos operativo
- [ ] Reportes básicos exportables
- [ ] Comandos automáticos programados
- [ ] Backup automático configurado
- [ ] Documentación de usuario completa

---

## Entidades del Sistema

### 1. ENTIDAD: Vehículo

#### Campos Obligatorios
| Campo | Tipo | Descripción | Validación |
|-------|------|-------------|------------|
| `placa` | string(10) | Identificador único | Formato local, único |
| `marca` | string(50) | Marca del fabricante | Requerido |
| `modelo` | string(50) | Modelo específico | Requerido |
| `anio` | year | Año de fabricación | ≤ año actual |

#### Campos Opcionales
| Campo | Tipo | Descripción | Valor Default |
|-------|------|-------------|---------------|
| `numero_motor` | string(50) | Identificación motor | null |
| `numero_chasis` | string(50) | Identificación chasis | null |
| `color` | string(30) | Color del vehículo | null |
| `tipo_vehiculo` | string(30) | Categoría (auto, camión, etc.) | null |
| `fecha_compra` | date | Fecha de adquisición | null |
| `propietario_actual` | string(100) | Nombre del propietario | null |
| `kilometraje_actual` | int unsigned | Kilometraje registrado | 0 |

### 2. ENTIDAD: Registro SOAT

#### Campos del Sistema
| Campo | Tipo | Descripción | Obligatorio |
|-------|------|-------------|-------------|
| `vehicle_id` | bigint | Referencia a vehículo | SÍ |
| `fecha_emision` | date | Fecha de emisión | SÍ |
| `fecha_vencimiento` | date | Fecha de vencimiento | SÍ |
| `numero_poliza` | string(30) | Número de póliza | NO |
| `compania_aseguradora` | string(100) | Empresa aseguradora | NO |
| `valor_pagado` | decimal(10,2) | Monto pagado | NO |
| `estado` | enum | CALCULADO AUTOMÁTICAMENTE | - |

#### Estados Automáticos SOAT
```
VIGENTE: fecha_vencimiento > (fecha_actual + dias_anticipo)
PRÓXIMO_A_VENCER: fecha_actual < fecha_vencimiento ≤ (fecha_actual + dias_anticipo)
VENCIDO: fecha_vencimiento < fecha_actual
```

### 3. ENTIDAD: Revisión Técnica

#### Campos del Sistema
| Campo | Tipo | Descripción | Obligatorio |
|-------|------|-------------|-------------|
| `vehicle_id` | bigint | Referencia a vehículo | SÍ |
| `fecha_emision` | date | Fecha de emisión | SÍ |
| `fecha_vencimiento` | date | Fecha de vencimiento | SÍ |
| `numero_certificado` | string(30) | Número de certificado | NO |
| `centro_revision` | string(100) | Centro de revisión | NO |
| `resultado` | enum | aprobado/rechazado | NO |
| `observaciones` | text | Comentarios | NO |
| `valor_pagado` | decimal(10,2) | Costo de revisión | NO |
| `estado` | enum | CALCULADO AUTOMÁTICAMENTE | - |

### 4. ENTIDAD: Mantenimiento

#### Campos Obligatorios
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `vehicle_id` | bigint | Referencia a vehículo |
| `trabajo_realizado` | text | Descripción del trabajo |
| `pieza_afectada` | string(100) | Componente reparado |
| `precio_mantenimiento` | decimal(10,2) | Costo total |
| `nombre_taller` | string(100) | Taller que realizó el trabajo |
| `fecha_mantenimiento` | date | Fecha del servicio |

#### Campos Adicionales
| Campo | Tipo | Opciones | Default |
|-------|------|----------|---------|
| `comentarios` | text | Observaciones adicionales | null |
| `kilometraje` | int unsigned | Km al momento del servicio | null |
| `tipo_mantenimiento` | enum | preventivo/correctivo/emergencia | null |
| `estado` | enum | completado/pendiente/en_proceso | completado |
| `proximo_mantenimiento` | date | Fecha sugerida próximo servicio | null |
| `nivel_prioridad` | enum | baja/media/alta/critica | media |
| `adjuntos` | json | Array de URLs documentos | null |

---

## Reglas de Negocio

### RN-001: Unicidad de Vehículos
- Cada placa debe ser única en el sistema
- Validación de formato según normativa local

### RN-002: Estados de Documentos (CRÍTICA)
**Cálculo automático via campos generados:**
```sql
CASE
    WHEN fecha_vencimiento < CURDATE() THEN 'vencido'
    WHEN fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL dias_anticipo DAY) THEN 'proximo_a_vencer'
    ELSE 'vigente'
END
```

### RN-003: Validaciones Temporales
- `fecha_vencimiento` > `fecha_emision`
- `fecha_emision` ≤ fecha actual
- Para mantenimientos: `fecha_mantenimiento` ≤ fecha actual

### RN-004: Consistencia de Kilometraje
- Nuevo kilometraje ≥ último kilometraje registrado
- Validación en modelo y formulario

### RN-005: Integridad Referencial
- Todos los registros deben referenciar vehículos existentes
- Soft delete para mantener historial

---

## Estructura de Base de Datos

### Tabla: vehicles
```sql
CREATE TABLE vehicles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio YEAR,
    numero_motor VARCHAR(50),
    numero_chasis VARCHAR(50),
    color VARCHAR(30),
    tipo_vehiculo VARCHAR(30),
    fecha_compra DATE,
    propietario_actual VARCHAR(100),
    kilometraje_actual INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_placa (placa),
    INDEX idx_marca_modelo (marca, modelo)
);
```

### Tabla: soat_records
```sql
CREATE TABLE soat_records (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id BIGINT NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    numero_poliza VARCHAR(30),
    compania_aseguradora VARCHAR(100),
    valor_pagado DECIMAL(10,2),
    
    -- Campo calculado para estado automático
    estado ENUM('vigente', 'proximo_a_vencer', 'vencido') GENERATED ALWAYS AS (
        CASE
            WHEN fecha_vencimiento < CURDATE() THEN 'vencido'
            WHEN fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL (
                SELECT dias_anticipacion_soat FROM alert_settings LIMIT 1
            ) DAY) THEN 'proximo_a_vencer'
            ELSE 'vigente'
        END
    ) STORED,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle_estado (vehicle_id, estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
);
```

### Tabla: revision_tecnica_records
```sql
CREATE TABLE revision_tecnica_records (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id BIGINT NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    numero_certificado VARCHAR(30),
    centro_revision VARCHAR(100),
    resultado ENUM('aprobado', 'rechazado'),
    observaciones TEXT,
    valor_pagado DECIMAL(10,2),
    
    -- Campo calculado para estado automático
    estado ENUM('vigente', 'proximo_a_vencer', 'vencido') GENERATED ALWAYS AS (
        CASE
            WHEN fecha_vencimiento < CURDATE() THEN 'vencido'
            WHEN fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL (
                SELECT dias_anticipacion_revision_tecnica FROM alert_settings LIMIT 1
            ) DAY) THEN 'proximo_a_vencer'
            ELSE 'vigente'
        END
    ) STORED,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle_estado (vehicle_id, estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
);
```

### Tabla: maintenances
```sql
CREATE TABLE maintenances (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id BIGINT NOT NULL,
    trabajo_realizado TEXT NOT NULL,
    pieza_afectada VARCHAR(100) NOT NULL,
    comentarios TEXT,
    precio_mantenimiento DECIMAL(10,2) NOT NULL,
    nombre_taller VARCHAR(100) NOT NULL,
    fecha_mantenimiento DATE NOT NULL,
    kilometraje INT UNSIGNED,
    tipo_mantenimiento ENUM('preventivo', 'correctivo', 'emergencia'),
    estado ENUM('completado', 'pendiente', 'en_proceso') DEFAULT 'completado',
    proximo_mantenimiento DATE,
    nivel_prioridad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    adjuntos JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle_fecha (vehicle_id, fecha_mantenimiento),
    INDEX idx_taller (nombre_taller),
    INDEX idx_tipo_estado (tipo_mantenimiento, estado),
    INDEX idx_proximo_mantenimiento (proximo_mantenimiento)
);
```

### Tabla: alert_settings
```sql
CREATE TABLE alert_settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dias_anticipacion_soat INT NOT NULL DEFAULT 30,
    dias_anticipacion_revision_tecnica INT NOT NULL DEFAULT 30,
    user_id BIGINT NULL, -- NULL = configuración global
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_user_settings (user_id)
);
```

---

## Recursos Filament

### VehicleResource
**Campos de Formulario:**
```php
Forms\Components\TextInput::make('placa')->required()->unique(),
Forms\Components\TextInput::make('marca')->required(),
Forms\Components\TextInput::make('modelo')->required(),
Forms\Components\TextInput::make('anio')->numeric(),
Forms\Components\TextInput::make('numero_motor'),
Forms\Components\TextInput::make('numero_chasis'),
Forms\Components\TextInput::make('color'),
Forms\Components\Select::make('tipo_vehiculo')->options([...]),
Forms\Components\DatePicker::make('fecha_compra'),
Forms\Components\TextInput::make('propietario_actual'),
Forms\Components\TextInput::make('kilometraje_actual')->numeric()
```

**Columnas de Tabla:**
- Placa (searchable, sortable)
- Marca/Modelo
- Año
- Propietario
- Estado SOAT (badge)
- Estado Revisión Técnica (badge)
- Acciones

### SoatRecordResource
**Relaciones:**
- `belongsTo(Vehicle::class)`

**Filtros:**
- Por estado (vigente, próximo a vencer, vencido)
- Por vehículo
- Por rango de fechas

### RevisionTecnicaRecordResource
**Funcionalidades:**
- Igual estructura que SOAT
- Campo adicional: resultado (aprobado/rechazado)
- Filtro por centro de revisión

### MaintenanceResource
**Campos Especiales:**
```php
Forms\Components\Select::make('vehicle_id')->relationship('vehicle', 'placa'),
Forms\Components\Textarea::make('trabajo_realizado')->required(),
Forms\Components\TextInput::make('pieza_afectada')->required(),
Forms\Components\TextInput::make('precio_mantenimiento')->numeric()->required(),
Forms\Components\Select::make('tipo_mantenimiento')->options([...]),
Forms\Components\Select::make('estado')->options([...]),
Forms\Components\Repeater::make('adjuntos')->schema([...])
```

---

## Dashboard y Widgets

### Widget: AlertasDocumentosWidget
**Métricas mostradas:**
```php
Stat::make('SOAT Vencidos', $soatVencidos)->color('danger'),
Stat::make('SOAT Próximos a Vencer', $soatProximos)->color('warning'),
Stat::make('Revisión Técnica Vencida', $rtVencidas)->color('danger'),
Stat::make('Revisión Técnica Próxima', $rtProximas)->color('warning')
```

### Widget: GastosMantenimientoWidget
**Tipo:** ChartWidget (LineChart)
**Datos:** Gastos mensuales últimos 12 meses
**Filtros:** Por vehículo, por taller, por tipo

### Widget: ProximosMantenimientosWidget
**Tipo:** TableWidget
**Datos:** Mantenimientos programados próximos 30 días
**Columnas:** Vehículo, Fecha, Taller, Prioridad

---

## Commands Artisan

### CheckExpiredDocumentsCommand
```php
php artisan vehicles:check-expired --type=soat
php artisan vehicles:check-expired --type=revision_tecnica
php artisan vehicles:check-expired --all
```

### SendAlertsCommand
```php
php artisan vehicles:send-alerts --email
php artisan vehicles:send-alerts --dashboard-only
```

### UpdateDocumentStatesCommand
```php
php artisan vehicles:update-states
# Ejecuta recálculo de estados (backup del campo calculado)
```

---

## Programación de Tareas

### Schedule Configuration
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Verificación diaria de documentos vencidos
    $schedule->command('vehicles:check-expired --all')
             ->daily()
             ->at('06:00');
    
    // Envío de alertas diarias
    $schedule->command('vehicles:send-alerts --email')
             ->daily()
             ->at('08:00');
    
    // Actualización de estados cada hora
    $schedule->command('vehicles:update-states')
             ->hourly();
    
    // Backup de BD cada día
    $schedule->command('backup:run')
             ->daily()
             ->at('02:00');
}
```

---

## Validaciones del Sistema

### Validadores Laravel

#### VehicleRequest
```php
public function rules(): array
{
    return [
        'placa' => ['required', 'string', 'max:10', 'unique:vehicles,placa'],
        'marca' => ['required', 'string', 'max:50'],
        'modelo' => ['required', 'string', 'max:50'],
        'anio' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
        'kilometraje_actual' => ['nullable', 'integer', 'min:0']
    ];
}
```

#### MaintenanceRequest
```php
public function rules(): array
{
    return [
        'vehicle_id' => ['required', 'exists:vehicles,id'],
        'trabajo_realizado' => ['required', 'string'],
        'pieza_afectada' => ['required', 'string', 'max:100'],
        'precio_mantenimiento' => ['required', 'numeric', 'min:0'],
        'fecha_mantenimiento' => ['required', 'date', 'before_or_equal:today'],
        'kilometraje' => ['nullable', 'integer', 'gte:' . $this->getLastKilometraje()]
    ];
}
```

---

## Reportes del Sistema

### 1. Reporte de Gastos por Período
**Parámetros:**
- Fecha inicio/fin
- Vehículo específico (opcional)
- Tipo de mantenimiento (opcional)

**Datos incluidos:**
- Total gastado por vehículo
- Promedio por mantenimiento
- Taller más costoso/económico
- Gráfico de tendencia

### 2. Reporte de Vencimientos
**Próximos 90 días:**
- SOAT por vencer
- Revisiones técnicas por vencer
- Agrupado por mes
- Exportable a PDF/Excel

### 3. Reporte de Eficiencia
**Métricas:**
- Ratio mantenimiento preventivo vs correctivo
- Tiempo promedio entre mantenimientos
- Vehículos con más/menos mantenimientos
- Análisis de costos por kilometraje

---

## Cronograma de Implementación

### Fase 1: Base (Semanas 1-2)
- [ ] Configuración Laravel 12 + Filament 3
- [ ] Migraciones y modelos base
- [ ] Autenticación y roles
- [ ] Seeders con datos de prueba

### Fase 2: Recursos Core (Semanas 3-4)
- [ ] VehicleResource completo
- [ ] SoatRecordResource con estados automáticos
- [ ] RevisionTecnicaRecordResource
- [ ] Validaciones principales

### Fase 3: Mantenimientos (Semanas 5-6)
- [ ] MaintenanceResource avanzado
- [ ] Sistema de alertas configurables
- [ ] AlertSettingResource
- [ ] Commands básicos

### Fase 4: Dashboard y Reportes (Semanas 7-8)
- [ ] Widgets del dashboard
- [ ] Reportes exportables
- [ ] Commands automáticos
- [ ] Scheduler configuration
- [ ] Testing y documentación

---

## Configuración de Entorno

### Requisitos del Sistema
```env
PHP_VERSION=8.2+
LARAVEL_VERSION=12.x
FILAMENT_VERSION=3.x
MYSQL_VERSION=8.0+
NODE_VERSION=18+
```

### Variables de Entorno Críticas
```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=vehicle_maintenance
DB_USERNAME=root
DB_PASSWORD=

# Configuración de alertas
ALERT_DAYS_SOAT=30
ALERT_DAYS_REVISION=30
MAIL_ALERTS_ENABLED=true

# Backup
BACKUP_ENABLED=true
BACKUP_SCHEDULE=daily
```

---

## Métricas de Éxito

### KPIs del Sistema
1. **Disponibilidad**: 99.5% uptime
2. **Performance**: Carga de página < 2 segundos
3. **Precisión de alertas**: 100% documentos detectados
4. **Adopción**: 90% vehículos con datos completos
5. **Reportes**: Generación < 30 segundos

### Criterios de Aceptación
- [ ] Registro completo de vehículos funcional
- [ ] Estados automáticos de documentos operativos
- [ ] Sistema de alertas enviando notificaciones
- [ ] Reportes exportando correctamente
- [ ] Dashboard mostrando métricas en tiempo real
- [ ] Comandos programados ejecutándose sin errores