# 3 Reportes Básicos del Sistema

## 1. Reporte de Documentos Vencidos

### Funcionalidad
Identifica vehículos con documentos obligatorios vencidos que representan riesgo legal inmediato.

### Utilidad
- **Control de cumplimiento legal**: Evita multas y sanciones por circular con documentos vencidos
- **Gestión de riesgo**: Identifica vehículos que no pueden circular legalmente
- **Priorización de acciones**: Ordena las renovaciones por urgencia según días de vencimiento
- **Responsabilidad operativa**: Asigna tareas específicas de renovación a responsables

### Datos Incluidos
- **Placa del vehículo**: Identificador único para localización rápida
- **Marca y modelo**: Información descriptiva del vehículo
- **Días vencido SOAT**: Cantidad exacta de días que lleva vencido el seguro obligatorio
- **Días vencido Revisión Técnica**: Cantidad exacta de días que lleva vencida la revisión técnica
- **Acción requerida**: Renovación inmediata de documentos

### Criterios de Inclusión
- Vehículos con SOAT vencido (fecha_vencimiento < fecha_actual)
- Vehículos con Revisión Técnica vencida (fecha_vencimiento < fecha_actual)
- Solo documentos más recientes por vehículo

### Ordenamiento
Por días de vencimiento descendente (más críticos primero)

---

## 2. Reporte de Próximos Vencimientos (30 días)

### Funcionalidad
Proporciona control preventivo de documentos que vencerán en los próximos 30 días.

### Utilidad
- **Planificación preventiva**: Permite programar renovaciones antes del vencimiento
- **Gestión de tiempo**: Distribuye la carga de trabajo de renovaciones
- **Prevención de multas**: Evita que documentos lleguen a vencer
- **Control de presupuesto**: Permite planificar gastos en renovaciones
- **Gestión de citas**: Programa visitas a aseguradoras y centros de revisión

### Datos Incluidos
- **Placa del vehículo**: Identificador único para gestión
- **Marca y modelo**: Información descriptiva del vehículo
- **Días restantes SOAT**: Tiempo disponible para renovar el seguro obligatorio
- **Días restantes Revisión Técnica**: Tiempo disponible para realizar la revisión técnica
- **Fecha límite**: Fecha exacta de vencimiento

### Criterios de Inclusión
- Documentos que vencen entre la fecha actual y los próximos 30 días
- Solo el documento más reciente por vehículo y tipo
- Vehículos activos únicamente

### Ordenamiento
Por días restantes ascendente (más urgentes primero)

---

## 3. Reporte de Gastos por Vehículo (Mes Actual)

### Funcionalidad
Presenta el resumen de gastos en mantenimientos por vehículo durante el mes en curso.

### Utilidad
- **Control financiero**: Monitorea los costos de mantenimiento mensual
- **Identificación de anomalías**: Detecta vehículos con gastos excesivos
- **Análisis de eficiencia**: Compara costos entre vehículos similares
- **Planificación presupuestaria**: Proyecta gastos para el resto del mes
- **Toma de decisiones**: Identifica vehículos candidatos para baja por alto costo
- **Evaluación de proveedores**: Analiza el impacto de talleres en los costos

### Datos Incluidos
- **Placa del vehículo**: Identificador único para seguimiento
- **Marca y modelo**: Información descriptiva del vehículo
- **Total gastado**: Suma total de gastos en mantenimientos del mes actual
- **Número de mantenimientos**: Cantidad de servicios realizados en el período
- **Promedio por servicio**: Costo promedio calculado automáticamente

### Criterios de Inclusión
- Mantenimientos realizados en el mes calendario actual
- Solo vehículos con al menos un gasto registrado
- Mantenimientos completados únicamente
- Excluye registros eliminados (soft delete)

### Ordenamiento
Por total gastado descendente (más costosos primero)

---

## Características Técnicas Comunes

### Formato de Exportación
- **PDF**: Documento imprimible de una página con tabla simple
- **Excel**: Hoja de cálculo básica sin formato avanzado
- **Vista web**: Tabla HTML responsiva en el sistema

### Frecuencia de Generación
- **Reporte 1**: Diario o bajo demanda (crítico)
- **Reporte 2**: Semanal o bajo demanda (preventivo)
- **Reporte 3**: Mensual o bajo demanda (financiero)

### Usuarios Objetivo
- **Operadores**: Gestión diaria de documentos y mantenimientos
- **Supervisores**: Control y seguimiento de cumplimiento
- **Administradores**: Análisis financiero y toma de decisiones
- **Gerencia**: Visión ejecutiva de costos y cumplimiento

### Integración con Alertas
- Datos utilizados para generar notificaciones automáticas
- Base para envío de emails de recordatorio
- Fuente de información para dashboard principal
- Insumo para indicadores de gestión (KPIs)