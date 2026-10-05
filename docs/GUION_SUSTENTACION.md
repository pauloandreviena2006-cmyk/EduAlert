# 🎤 Guion de sustentación — EduAlert JJP

Guion tipo para demostrar el sistema en la sustentación de tesis.
**Duración sugerida: 12-15 minutos.** Póngalo en pantalla completa con las
cuentas demo preparadas.

---

## 0. Preparación previa (antes de la sustentación)

1. Iniciar MySQL.
2. Iniciar PHP: `php -S localhost:8000 -t public public/server.php` (desde `EduAlertJJP`).
3. Verificar que la instalación y los datos demo están cargados.
4. **Opcional pero recomendado:** ejecutar el motor de alertas un par de
   veces para tener alertas y seguimientos ya registrados.

---

## 1. Presentación del problema (no tocar el sistema)

- "La I.E. Juan Jiménez Pimentel de Tarapoto detectaba el riesgo académico
  de forma tardía y manual, luego de los resultados consolidados."
- "Esto impedía intervenir a tiempo a los estudiantes con riesgo de
  repitencia o abandono."
- "EduAlert JJP automatiza la **detección temprana** cruzando
  **calificaciones, asistencia y tendencia** mediante un motor de riesgo con
  **umbrales configurables**."

## 2. Ingreso y roles (1 min)

1. Abrir `http://localhost:8000`.
2. Usar un botón de acceso rápido (o escribir `admin@edualert.test` /
   `12345678`).
3. Señalar: "El sistema controla 5 roles; el administrador ve todo, el
   docente solo registra notas, el auxiliar solo asistencia, etc."

## 3. Dashboard (1 min)

- Señalar los **indicadores**: total de estudiantes, distribución por riesgo,
  alertas nuevas, promedio general, inasistencias.
- Señalar los **gráficos** (Chart.js): distribución de riesgo, rendimiento por
  grado, alertas por periodo, evolución, asistencia por grado.

## 4. Registro de estudiante (1-2 min)

1. **Estudiantes → Nuevo estudiante**.
2. Llenar DNI, nombres, apellidos, grado y sección (mostrar la cascada
   grado→sección).
3. Guardar → se asigna el **código automático** `AÑO-NNNN` y queda matriculado
   en el periodo activo.
4. Abrir la **ficha del estudiante**: datos, apoderado, notas por periodo,
   evolución (gráfico), asistencia, alertas y seguimientos.

## 5. Notas y asistencia (2 min)

1. **Calificaciones → Registrar notas**: elegir sección, curso, periodo →
   aparecen los estudiantes de esa sección → cargar notas (0-20) → Guardar.
2. **Asistencia → Registrar asistencia**: elegir sección y fecha → marcar
   presente/tardanza/falta/justificado (botones rápidos).

## 6. Motor de riesgo y alertas (2 min) ⭐

1. **Alertas → Ejecutar motor de alertas**:
   - Muestra el resumen: "Evaluados, Alertas creadas, Actualizadas, Sin riesgo".
   - Explicar la **fórmula de puntaje**:
     `rendimiento (medio 20 / alto 40) + asistencia (media 20 / baja 30) +
     desaprobados (10/20/30) + tendencia descendente (20)`.
2. Filtrar por nivel **Alto** y abrir una alerta:
   - **Motivos** detallados por qué se disparó.
   - **Puntaje** y nivel (Bajo/Medio/Alto).
   - Gestión: cambiar estado (Nueva → En revisión → En seguimiento → Atendida)
     y **asignar responsable**.

## 7. Seguimiento e intervención (1-2 min)

- Desde la alerta, **Registrar seguimiento**: tipo de intervención,
  descripción, acuerdo, compromiso y próxima revisión.
- Mostrar cómo la **línea de tiempo** acumula las intervenciones y cómo la
  alerta cambia de estado.

## 8. Configuración del motor (1 min)

- **Configuración**: puntajes por curso desaprobado, asistencia, umbrales.
- Cambiar un valor (ej. `puntos_curso_3` a 35) y **volver a ejecutar el motor**
  para mostrar que usa los nuevos valores (no hay código hardcodeado).

## 9. Reportes (2 min) ⭐

- **Reportes → Estudiantes en riesgo**: tabla con promedio, asistencia %,
  desaprobados, nivel y puntaje.
- Exportar a **PDF** (se abre el documento TCPDF con el membrete),
  **Excel** y **CSV**.
- Mostrar "Alertas" y "Seguimientos" como reportes alternativos.

## 10. Seguridad y auditoría (1 min)

- **Auditoría**: mostrar el registro de las acciones que acabamos de hacer
  (registro de estudiante, generación de alertas, seguimiento).
- Mencionar: contraseñas encriptadas (bcrypt), tokens CSRF, consultas
  preparadas, permisos por rol.

## 11. Cierre

- "El prototipo valida la hipótesis: con datos tempranos (bimestrales y de
  asistencia), el motor anticipa el riesgo y permite **intervenir antes** de
  la repitencia."
- "Trabajo futuro: envío de notificaciones por correo/WhatsApp a apoderados,
  integración con SIAGIE, y vistas por periodo para el docente de aula."

---

## Tarjetas de verificación (para el jurado)

- ✅ Instalación en 1 clic (asistente web).
- ✅ 5 cuentas demo por rol con permisos diferenciados.
- ✅ 200 estudiantes de demo con datos completos.
- ✅ Cadena: estudiante → matrícula → cursos → notas/asistencia → riesgo →
  alerta → dashboard → seguimiento → reporte exportable.
- ✅ Umbrales y puntajes configurables desde la UI.