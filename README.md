# 📋 Sistema de Gestión de Calidad

## 📌 Descripción General

Sistema integral de gestión y control de calidad diseñado para monitorear, evaluar y validar procesos de calidad en la organización. El sistema implementa un flujo de trabajo estructurado con múltiples roles, estados condicionales y niveles de aprobación.

---

## 🎯 Funcionalidades Principales

### 1. **Monitoreo de Calidad**
- El **Líder de Calidad** realiza monitoreo continuo de procesos
- Genera reportes de cumplimiento
- Identifica desviaciones y no conformidades
- Documenta hallazgos y observaciones

### 2. **Evaluación y Refutación**
- El **Asesor** revisa los monitoreos realizados
- Puede **aceptar** los hallazgos (proceso finaliza)
- Puede **refutar** los hallazgos (escala a supervisor)

### 3. **Validación por Supervisor**
- El **Supervisor** recibe refutaciones del asesor
- **Decide si procede** la refutación:
  - ✅ **SÍ PROCEDE**: Escala al Líder de Calidad para validación final
  - ❌ **NO PROCEDE**: Retorna al Asesor para replanteamiento

### 4. **Validación Final**
- El **Líder de Calidad** valida la decisión del supervisor
- Emite validación final del proceso
- Cierra o continúa el ciclo

---

## 👥 Roles y Responsabilidades

| Rol | Responsabilidades | Permisos |
|-----|-------------------|---------|
| **Líder de Calidad** | - Realizar monitoreos<br>- Evaluar procesos<br>- Validar decisiones del supervisor | Crear monitoreos, Validar decisiones, Ver reportes |
| **Asesor** | - Revisar monitoreos<br>- Aceptar o refutar hallazgos<br>- Justificar refutaciones | Revisar monitoreos, Aceptar/Refutar, Comentar |
| **Supervisor** | - Evaluar refutaciones<br>- Decidir si procede<br>- Derivar a líder si procede | Revisar refutaciones, Aprobar/Rechazar, Derivar |
| **Administrador** | - Gestionar usuarios<br>- Configurar sistema<br>- Generar reportes globales | Acceso total, Gestionar roles |

---

## 🔄 Flujo de Procesos

### **Flujo Principal: Monitoreo → Refutación → Validación**

```
┌─────────────────────────────────────────────────────────────────┐
│                    LÍDER DE CALIDAD                              │
│                  Realiza Monitoreo                               │
│            (Reporte de hallazgos y observaciones)               │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                         ASESOR                                   │
│                    Revisa Monitoreo                              │
└──────────────────────┬──────────────────────────────────────────┘
                       │
           ┌───────────┴───────────┐
           │                       │
        ACEPTA                 REFUTA
           │                       │
           │              (Justificación)
           │                       │
           ▼                       ▼
      FINALIZA            ┌─────────────────┐
      PROCESO             │    SUPERVISOR   │
                          │  Revisa Refuta  │
                          └────────┬────────┘
                                   │
                        ┌──────────┴──────────┐
                        │                     │
                   PROCEDE            NO PROCEDE
                        │                     │
                        │                     ▼
                        │              Retorna a ASESOR
                        │              (Replanteamiento)
                        │
                        ▼
                ┌──────────────────────┐
                │ LÍDER DE CALIDAD     │
                │ Validación Final     │
                │ (Cierra o continúa)  │
                └──────────────────────┘
```

---

## 🔐 Estados y Condiciones

### **Estados de un Monitoreo**

| Estado | Descripción | Siguiente Acción |
|--------|-------------|-----------------|
| **CREADO** | Monitoreo inicial realizado por líder | Enviar a Asesor |
| **EN_REVISIÓN_ASESOR** | Asesor evaluando hallazgos | Aceptar o Refutar |
| **ACEPTADO** | Asesor acepta hallazgos | ✅ FIN DEL PROCESO |
| **REFUTADO** | Asesor refuta con justificación | Enviar a Supervisor |
| **EN_REVISIÓN_SUPERVISOR** | Supervisor evaluando refutación | Aprobar o Rechazar |
| **REFUTACIÓN_APROBADA** | Supervisor aprueba refutación | Enviar a Líder |
| **REFUTACIÓN_RECHAZADA** | Supervisor rechaza refutación | Retornar a Asesor |
| **EN_VALIDACIÓN_LÍDER** | Líder realizando validación final | Validar o Rechazar |
| **VALIDADO** | Líder valida decisión | ✅ FIN DEL PROCESO |
| **RECHAZADO** | Líder rechaza decisión | Retornar a Asesor |

### **Reglas Condicionales**

#### 1️⃣ **Asesor Acepta**
```
SI Asesor.acción = ACEPTAR
ENTONCES
  - Estado = ACEPTADO
  - Fecha Finalización = HOY
  - Notificar Líder (aceptación)
  - FIN PROCESO ✅
FIN SI
```

#### 2️⃣ **Asesor Refuta**
```
SI Asesor.acción = REFUTAR
Y Asesor.justificación ≠ vacío
ENTONCES
  - Estado = REFUTADO
  - Derivar a SUPERVISOR
  - Guardar justificación
  - Notificar Supervisor
FIN SI
```

#### 3️⃣ **Supervisor Aprueba Refutación**
```
SI Supervisor.acción = PROCEDE
ENTONCES
  - Estado = REFUTACIÓN_APROBADA
  - Derivar a LÍDER_CALIDAD
  - Guardar observaciones del supervisor
  - Notificar Líder para validación
FIN SI
```

#### 4️⃣ **Supervisor Rechaza Refutación**
```
SI Supervisor.acción = NO_PROCEDE
ENTONCES
  - Estado = REFUTACIÓN_RECHAZADA
  - Retornar a ASESOR
  - Incluir motivos de rechazo
  - Asesor debe replantearse
FIN SI
```

#### 5️⃣ **Líder Valida**
```
SI Líder.acción = VALIDAR
ENTONCES
  - Estado = VALIDADO
  - Fecha Finalización = HOY
  - Guardar validación
  - FIN PROCESO ✅
FIN SI
```

#### 6️⃣ **Líder Rechaza Validación**
```
SI Líder.acción = RECHAZAR
ENTONCES
  - Estado = RECHAZADO
  - Retornar a ASESOR
  - Motivo: Requiere replanteamiento
  - Ciclo inicia nuevamente
FIN SI
```

---

## 📊 Módulos del Sistema

### **1. Módulo de Monitoreo**
- Crear nuevos monitoreos
- Registrar hallazgos
- Documentar observaciones
- Adjuntar evidencia
- Historial de monitoreos

**Funciones:**
- `crearMonitoreo(datos)` - Crear nuevo monitoreo
- `guardarHallazgos(monitoreoId, hallazgos)` - Registrar hallazgos
- `obtenerMonitoreos(filtros)` - Listar monitoreos

### **2. Módulo de Evaluación**
- Revisar monitoreos
- Evaluar hallazgos
- Aceptar/Refutar con justificación
- Comentarios y observaciones

**Funciones:**
- `revisarMonitoreo(monitoreoId)` - Obtener detalles
- `aceptarHallazgos(monitoreoId)` - Aceptar
- `refutarHallazgos(monitoreoId, justificación)` - Refutar

### **3. Módulo de Supervisión**
- Recibir refutaciones
- Evaluar procedencia
- Aprobar/Rechazar con justificación
- Derivar a líder si procede

**Funciones:**
- `obtenerRefutaciones()` - Listar refutaciones
- `aprobarRefutacion(refutacionId)` - Aprobar
- `rechazarRefutacion(refutacionId, motivo)` - Rechazar

### **4. Módulo de Validación**
- Recibir validaciones del supervisor
- Realizar validación final
- Generar certificados
- Archivar procesos

**Funciones:**
- `obtenerValidaciones()` - Listar validaciones pendientes
- `validarProceso(monitoreoId)` - Validar
- `generarCertificado(monitoreoId)` - Generar certificado

### **5. Módulo de Reportes**
- Generar reportes por rol
- Estadísticas de procesos
- Análisis de tendencias
- Exportar datos

**Funciones:**
- `generarReporte(filtros)` - Generar reporte
- `obtenerEstadísticas(período)` - Estadísticas
- `exportarDatos(formato)` - Exportar

### **6. Módulo de Notificaciones**
- Alertas por cambio de estado
- Reminders de acciones pendientes
- Notificaciones a roles específicos

**Funciones:**
- `enviarNotificación(usuario, mensaje)` - Enviar notificación
- `crearAlerta(tipo, datos)` - Crear alerta

---

## 📋 Atributos de Monitoreo

```json
{
  "id": "MON-2026-001",
  "líder": {
    "id": "USR-001",
    "nombre": "Juan Pérez",
    "rol": "LÍDER_CALIDAD"
  },
  "proceso": "Producción",
  "fecha_creación": "2026-07-03",
  "estado": "EN_REVISIÓN_ASESOR",
  "hallazgos": [
    {
      "id": "HAL-001",
      "descripción": "Desviación en tiempo de proceso",
      "severidad": "MEDIA",
      "evidencia": ["foto_01.jpg", "reporte_01.pdf"]
    }
  ],
  "asesor": {
    "id": "USR-002",
    "nombre": "María García",
    "rol": "ASESOR"
  },
  "evaluación_asesor": {
    "acción": "REFUTADO",
    "fecha": "2026-07-03",
    "justificación": "El hallazgo no es válido según procedimiento XYZ"
  },
  "supervisor": {
    "id": "USR-003",
    "nombre": "Carlos López",
    "rol": "SUPERVISOR"
  },
  "evaluación_supervisor": {
    "acción": "PROCEDE",
    "fecha": "2026-07-03",
    "motivo": "Se confirma la refutación del asesor"
  },
  "líder_validación": {
    "id": "USR-001",
    "validación": "VALIDADO",
    "fecha": "2026-07-03"
  },
  "historial": [
    {
      "fecha": "2026-07-03 10:00",
      "acción": "CREADO",
      "usuario": "Juan Pérez"
    },
    {
      "fecha": "2026-07-03 11:30",
      "acción": "REFUTADO",
      "usuario": "María García"
    }
  ]
}
```

---

## 🚀 Instalación y Uso

### **Requisitos Previos**
- Node.js v14+
- Base de datos (MongoDB/PostgreSQL)
- npm o yarn

### **Instalación**
```bash
# Clonar repositorio
git clone https://github.com/jeferson6329/gestion_calidad.git

# Instalar dependencias
cd gestion_calidad
npm install

# Configurar variables de entorno
cp .env.example .env

# Ejecutar base de datos
npm run db:setup

# Iniciar servidor
npm start
```

### **Uso Básico**

#### Para Líder de Calidad:
```bash
# Crear monitoreo
POST /api/monitoreos
{
  "proceso": "Producción",
  "hallazgos": [...]
}

# Ver mis monitoreos
GET /api/monitoreos?estado=CREADO
```

#### Para Asesor:
```bash
# Ver monitoreos asignados
GET /api/monitoreos?estado=EN_REVISIÓN_ASESOR

# Refutar un monitoreo
PUT /api/monitoreos/{id}/refutar
{
  "justificación": "..."
}
```

---

## 🔍 Ejemplo de Flujo Completo

### **Paso 1: Líder crea monitoreo**
```json
POST /api/monitoreos
{
  "proceso": "Control de Calidad - Línea A",
  "hallazgos": [
    {
      "descripción": "Temperatura fuera de rango",
      "severidad": "ALTA",
      "observación": "Se registró 5°C por debajo del límite"
    }
  ]
}
→ Estado: CREADO → EN_REVISIÓN_ASESOR
```

### **Paso 2: Asesor refuta**
```json
PUT /api/monitoreos/MON-001/refutar
{
  "justificación": "El sensor estaba mal calibrado. Temperatura es correcta según lectura manual."
}
→ Estado: REFUTADO → EN_REVISIÓN_SUPERVISOR
→ Notificación: Supervisor recibe refutación
```

### **Paso 3: Supervisor aprueba**
```json
PUT /api/monitoreos/MON-001/supervisor-aprobar
{
  "observaciones": "Se confirma la refutación. Requiere recalibración de sensor."
}
→ Estado: REFUTACIÓN_APROBADA → EN_VALIDACIÓN_LÍDER
→ Notificación: Líder recibe validación pendiente
```

### **Paso 4: Líder valida**
```json
PUT /api/monitoreos/MON-001/validar
{
  "observaciones_finales": "Proceso validado. Se programó recalibración de sensor."
}
→ Estado: VALIDADO ✅
→ Fecha Finalización: 2026-07-03
→ Proceso FINALIZADO
```

---

## 📊 Reportes y Análisis

### **Reportes Disponibles**

1. **Reporte de Monitoreos por Líder**
   - Cantidad de monitoreos
   - Hallazgos por severidad
   - Tasa de aceptación

2. **Reporte de Evaluaciones**
   - Monitoreos aceptados
   - Monitoreos refutados
   - Justificaciones más comunes

3. **Reporte de Supervisor**
   - Refutaciones aprobadas
   - Refutaciones rechazadas
   - Tiempo promedio de decisión

4. **Reporte Ejecutivo**
   - KPIs generales
   - Tendencias mensuales
   - Procesos críticos

---

## 🔒 Seguridad y Permisos

### **Control de Acceso**

```
LÍDER_CALIDAD:
  ✓ Crear monitoreos
  ✓ Ver sus monitoreos
  ✓ Validar decisiones del supervisor
  ✗ Aceptar/Refutar

ASESOR:
  ✓ Ver monitoreos asignados
  ✓ Aceptar/Refutar hallazgos
  ✓ Agregar comentarios
  ✗ Crear monitoreos
  ✗ Validar decisiones

SUPERVISOR:
  ✓ Ver refutaciones
  ✓ Aprobar/Rechazar refutaciones
  ✓ Derivar a líder
  ✗ Crear o editar monitoreos
  ✗ Realizar validación final

ADMINISTRADOR:
  ✓ Acceso total
  ✓ Gestionar usuarios
  ✓ Configurar sistema
```

---

## 🛠️ Tecnologías Utilizadas

- **Backend**: Node.js / Express
- **Base de Datos**: MongoDB / PostgreSQL
- **Frontend**: React / Vue.js
- **Autenticación**: JWT
- **Notificaciones**: Socket.io / Webhooks
- **Reportes**: ReportLab / jsPDF

---

## 📞 Soporte y Contacto

Para reportar problemas o sugerencias:
- Email: support@gestioncalidad.com
- Issues: https://github.com/jeferson6329/gestion_calidad/issues

---

## 📝 Licencia

Proyecto de código abierto bajo licencia MIT.

---

**Última actualización**: 2026-07-03  
**Versión**: 1.0.0
