# 👥 Cuentas y datos de demostración

## Cuentas de acceso

Todas las cuentas demo usan la contraseña `12345678`.

| Rol | Correo | Permisos principales |
|---|---|---|
| **ADMINISTRADOR** | `admin@edualert.test` | Todo el sistema: usuarios, auditoría, configuración, alertas, reportes... |
| **DIRECTOR** | `director@edualert.test` | Dashboard, estudiantes, matrículas, calificaciones, alertas, seguimiento, reportes |
| **DOCENTE** | `docente@edualert.test` | Dashboard, estudiantes (ver), calificaciones (registrar notas), asistencia |
| **TUTOR** | `tutor@edualert.test` | Dashboard, estudiantes (ver), alertas, seguimiento (gestionar), reportes |
| **AUXILIAR** | `auxiliar@edualert.test` | Dashboard, estudiantes (ver), asistencia, notificaciones |

## Datos cargados por el SeederDemo

Al presionar **"Cargar datos demo"** en el instalador se genera:

- **200 estudiantes** peruanos con nombres realistas (DNI únicos, código
  `AÑO-NNNN` según el año lectivo del parámetro `anio_lectivo`).
- **12 docentes** con usuario asociado al docente demo.
- **Matrículas** de todos los estudiantes en **todos los periodos**.
- **Calificaciones** por curso/periodo (con algunos desaprobados para activar
  el motor de riesgo).
- **Asistencia** de varios días con presentes, tardanzas, faltas y justificados.
- **Apoderados** por estudiante.
- **Asignación docente ↔ curso ↔ sección** por periodo.

> Todos los registros demo se pueden eliminar desde
> **Configuración → Eliminar datos demo** (no afecta cuentas ni parámetros).

## Flujo de demostración completo

1. Login como `admin@edualert.test` / `12345678`.
2. **Alertas → Ejecutar motor de alertas** (genera alertas con puntajes).
3. **Dashboard**: estadísticas y 5 gráficos actualizados.
4. **Alertas** → filtrar por nivel "Alto" → ver detalle → **Registrar seguimiento**.
5. **Estudiantes** → abrir la ficha de un estudiante con alerta (evolución,
   calificaciones, asistencias, alertas y seguimientos).
6. **Reportes** → "Estudiantes en riesgo" → exportar a **PDF** y **CSV**.
7. **Configuración** → modificar un puntaje (ej. `puntos_curso_3`) y volver a
   ejecutar el motor para ver el cambio.