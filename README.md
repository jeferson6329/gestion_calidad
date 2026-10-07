# 📋 Sistema de Gestión de Calidad

Un sistema integral de gestión y control de calidad que automatiza el monitoreo, evaluación y validación de procesos organizacionales. Implementa un flujo de trabajo colaborativo con roles especializados para garantizar calidad en toda la organización.

**Versión**: 1.0.0  
**Última actualización**: 2026-07-03

---

## 🎯 Características Principales

### 1. **Monitoreo de Calidad**
El **Líder de Calidad** realiza evaluaciones continuas de procesos, documentando hallazgos, observaciones y adjuntando evidencia.

### 2. **Revisión y Evaluación**
El **Asesor** revisa monitoreos y puede **aceptar** (finaliza el proceso) o **refutar** (con justificación documentada).

### 3. **Validación de Refutaciones**
El **Supervisor** evalúa refutaciones y decide si proceden:
- ✅ **Procede**: Escala a Líder para validación final
- ❌ **No Procede**: Retorna al Asesor para replanteamiento

### 4. **Validación Final**
El **Líder de Calidad** realiza validación final, cierra procesos y genera certificados.

### 5. **Reportes y Análisis**
Generación de reportes por rol, estadísticas, análisis de tendencias y exportación de datos.

---

## 👥 Roles y Responsabilidades

| Rol | Responsabilidades | Permisos Clave |
|-----|-------------------|----------------|
| **Líder de Calidad** | Crear monitoreos, evaluar procesos, validar decisiones | ✓ Crear monitoreos, Validar, Ver reportes |
| **Asesor** | Revisar monitoreos, aceptar/refutar hallazgos | ✓ Revisar, Aceptar/Refutar, Comentar |
| **Supervisor** | Evaluar refutaciones, decidir procedencia | ✓ Revisar refutaciones, Aprobar/Rechazar |
| **Administrador** | Gestionar usuarios y configuración del sistema | ✓ Acceso total, Gestionar roles |

---

## 🔄 Flujo de Procesos

```
┌─────────────────────────────┐
│  LÍDER DE CALIDAD           │
│  Crea Monitoreo             │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│  ASESOR                     │
│  Revisa Monitoreo           │
└──────────────┬──────────────┘
               │
       ┌───────┴────────┐
       │                │
    ACEPTA           REFUTA
       │                │
       ▼                ▼
    ✅ FIN      ┌────────────────┐
               │   SUPERVISOR   │
               │ Evalúa Refuta  │
               └────────┬───────┘
                        │
          ┌─────────────┴──────────────┐
          │                            │
      PROCEDE                   NO PROCEDE
          │                            │
          ▼                            ▼
    ┌──────────────────┐      Retorna a ASESOR
    │ LÍDER CALIDAD    │      (Replanteamiento)
    │ Validación Final │
    │ (✅ FIN o ↻)     │
    └──────────────────┘
```

---

## 🔐 Estados de Monitoreo

| Estado | Descripción | Siguiente Acción |
|--------|-------------|-----------------|
| **CREADO** | Monitoreo inicial creado por líder | Enviar a Asesor |
| **EN_REVISIÓN_ASESOR** | Asesor evaluando hallazgos | Aceptar o Refutar |
| **ACEPTADO** | Asesor acepta hallazgos | ✅ Proceso finalizado |
| **REFUTADO** | Asesor refuta con justificación | Enviar a Supervisor |
| **EN_REVISIÓN_SUPERVISOR** | Supervisor evaluando refutación | Aprobar o Rechazar |
| **REFUTACIÓN_APROBADA** | Supervisor aprueba | Enviar a Líder para validación |
| **REFUTACIÓN_RECHAZADA** | Supervisor rechaza | Retornar a Asesor |
| **EN_VALIDACIÓN_LÍDER** | Líder realizando validación final | Validar o Rechazar |
| **VALIDADO** | Líder valida decisión | ✅ Proceso finalizado |
| **RECHAZADO** | Líder rechaza validación | Retornar a Asesor (nuevo ciclo) |

---

## 📊 Módulos del Sistema

### **Módulo de Monitoreo**
Gestión completa del ciclo de monitoreo:
- Crear nuevos monitoreos
- Registrar hallazgos con severidad
- Documentar observaciones
- Adjuntar evidencia (archivos)
- Historial completo de cambios

**Funciones API:**
- `POST /api/monitoreos` - Crear monitoreo
- `GET /api/monitoreos` - Listar monitoreos
- `GET /api/monitoreos/:id` - Obtener detalles
- `PUT /api/monitoreos/:id` - Actualizar monitoreo

### **Módulo de Evaluación**
Revisión y evaluación de monitoreos:
- Revisar hallazgos
- Aceptar con confirmación
- Refutar con justificación detallada
- Agregar comentarios y observaciones

**Funciones API:**
- `GET /api/monitoreos?estado=EN_REVISIÓN_ASESOR` - Listar asignados
- `PUT /api/monitoreos/:id/aceptar` - Aceptar hallazgos
- `PUT /api/monitoreos/:id/refutar` - Refutar con justificación

### **Módulo de Supervisión**
Evaluación de refutaciones:
- Recibir refutaciones del Asesor
- Evaluar procedencia de refutaciones
- Aprobar o rechazar con motivo documentado
- Derivar a Líder si procede

**Funciones API:**
- `GET /api/refutaciones` - Listar refutaciones pendientes
- `PUT /api/monitoreos/:id/supervisor-aprobar` - Aprobar refutación
- `PUT /api/monitoreos/:id/supervisor-rechazar` - Rechazar refutación

### **Módulo de Validación**
Validación final y cierre:
- Recibir validaciones pendientes
- Realizar validación final del proceso
- Generar certificados de cierre
- Archivar procesos completados

**Funciones API:**
- `GET /api/validaciones` - Listar validaciones pendientes
- `PUT /api/monitoreos/:id/validar` - Validar proceso
- `GET /api/monitoreos/:id/certificado` - Generar certificado

### **Módulo de Reportes**
Análisis e inteligencia de negocio:
- Reportes personalizados por rol
- Estadísticas de procesos
- Análisis de tendencias mensuales
- Exportación en múltiples formatos

**Funciones API:**
- `GET /api/reportes` - Generar reporte
- `GET /api/estadísticas` - Obtener estadísticas
- `GET /api/exportar` - Exportar datos

### **Módulo de Notificaciones**
Sistema de alertas en tiempo real:
- Alertas de cambio de estado
- Recordatorios de acciones pendientes
- Notificaciones personalizadas por rol
- Integraciones con WebSockets

---

## 📋 Estructura de Datos

### Monitoreo (Ejemplo)
```json
{
  "id": "MON-2026-001",
  "líder": {
    "id": "USR-001",
    "nombre": "Juan Pérez",
    "rol": "LÍDER_CALIDAD"
  },
  "proceso": "Producción - Línea A",
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
  "historial": [
    {
      "fecha": "2026-07-03 10:00",
      "acción": "CREADO",
      "usuario": "Juan Pérez"
    }
  ]
}
```

---

## 🚀 Inicio Rápido

### Requisitos Previos
- Node.js v14 o superior
- Base de datos: MongoDB o PostgreSQL
- npm o yarn
- Git

### Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/jeferson6329/gestion_calidad.git
cd gestion_calidad

# 2. Instalar dependencias
npm install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con tus configuraciones

# 4. Configurar base de datos
npm run db:setup

# 5. Iniciar servidor de desarrollo
npm start
```

El servidor estará disponible en `http://localhost:3000`

---

## 📚 Ejemplos de Uso

### Para Líder de Calidad: Crear Monitoreo
```bash
curl -X POST http://localhost:3000/api/monitoreos \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "proceso": "Control de Calidad - Línea A",
    "hallazgos": [
      {
        "descripción": "Temperatura fuera de rango",
        "severidad": "ALTA",
        "observación": "Se registró 5°C por debajo del límite"
      }
    ]
  }'
```

### Para Asesor: Refutar Hallazgos
```bash
curl -X PUT http://localhost:3000/api/monitoreos/MON-001/refutar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "justificación": "El sensor estaba mal calibrado. Temperatura es correcta según lectura manual."
  }'
```

### Para Supervisor: Aprobar Refutación
```bash
curl -X PUT http://localhost:3000/api/monitoreos/MON-001/supervisor-aprobar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "observaciones": "Se confirma la refutación. Requiere recalibración de sensor."
  }'
```

### Para Líder: Validar Proceso
```bash
curl -X PUT http://localhost:3000/api/monitoreos/MON-001/validar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "observaciones_finales": "Proceso validado. Se programó recalibración de sensor."
  }'
```

---

## 📊 Reportes Disponibles

1. **Reporte de Monitoreos por Líder**
   - Cantidad de monitoreos realizados
   - Hallazgos por nivel de severidad
   - Tasa de aceptación/refutación

2. **Reporte de Evaluaciones (Asesor)**
   - Monitoreos aceptados
   - Monitoreos refutados
   - Justificaciones más frecuentes

3. **Reporte de Supervisor**
   - Refutaciones aprobadas
   - Refutaciones rechazadas
   - Tiempo promedio de decisión

4. **Reporte Ejecutivo**
   - KPIs generales del sistema
   - Tendencias mensuales
   - Procesos críticos identificados
   - Métricas de conformidad

---

## 🔒 Seguridad y Control de Acceso

### Permisos por Rol

**LÍDER_CALIDAD**
```
✓ Crear monitoreos
✓ Ver sus monitoreos
✓ Validar decisiones del supervisor
✓ Acceder a reportes personales
✗ Aceptar/Refutar
```

**ASESOR**
```
✓ Ver monitoreos asignados
✓ Aceptar/Refutar hallazgos
✓ Agregar comentarios
✓ Adjuntar evidencia adicional
✗ Crear monitoreos
✗ Validar decisiones
```

**SUPERVISOR**
```
✓ Ver refutaciones pendientes
✓ Aprobar/Rechazar refutaciones
✓ Derivar a líder
✓ Acceder a reportes de supervisión
✗ Crear o editar monitoreos
✗ Realizar validación final
```

**ADMINISTRADOR**
```
✓ Acceso total al sistema
✓ Gestionar usuarios y roles
✓ Configurar parámetros del sistema
✓ Ver todos los reportes
✓ Exportar datos
```

---

## 🛠️ Stack Tecnológico

- **Backend**: Node.js + Express
- **Base de Datos**: MongoDB / PostgreSQL
- **Frontend**: React / Vue.js
- **Autenticación**: JWT
- **Comunicación en Tiempo Real**: Socket.io / WebSockets
- **Reportes**: ReportLab / jsPDF
- **Validación**: Joi / Yup

---

## 📁 Estructura del Proyecto

```
gestion_calidad/
├── README.md                 # Este archivo
├── package.json              # Dependencias del proyecto
├── .env.example              # Variables de entorno ejemplo
├── src/
│   ├── config/              # Configuración (DB, Auth, etc)
│   ├── models/              # Esquemas/Modelos de datos
│   ├── controllers/         # Lógica de negocio
│   ├── routes/              # Definición de rutas API
│   ├── middleware/          # Middleware (Auth, Validación)
│   ├── services/            # Servicios (Notificaciones, Reportes)
│   └── utils/               # Utilidades y helpers
├── tests/                   # Suite de pruebas
├── docs/                    # Documentación adicional
└── scripts/                 # Scripts de utilidad
```

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Para contribuir:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📞 Soporte

Para reportar problemas, sugerencias o preguntas:

- **Issues**: [GitHub Issues](https://github.com/jeferson6329/gestion_calidad/issues)
- **Email**: support@gestioncalidad.com
- **Documentación**: Consulta la carpeta `/docs` para guías detalladas

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Consulta el archivo `LICENSE` para más detalles.

---

## 🔄 Historial de Cambios

### v1.0.0 (2026-07-03)
- ✨ Versión inicial del sistema
- 🎯 Implementación completa del flujo de monitoreo
- 👥 Sistema de roles y permisos
- 📊 Módulo de reportes básico
- 🔐 Autenticación JWT

---

**Última actualización**: 2026-07-03  
**Mantenedor**: [@jeferson6329](https://github.com/jeferson6329)
