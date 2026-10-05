-- ============================================================
-- EduAlert JJP - Datos semilla (base): roles, permisos, usuarios,
-- parámetros, niveles de riesgo, grados, secciones, periodos, cursos
-- Password de usuarios demo: 12345678
-- ============================================================
USE edualert_jjp;

-- ---------- Roles ----------
INSERT INTO roles (nombre, slug, descripcion) VALUES
('Administrador', 'admin', 'Acceso total al sistema y configuración'),
('Director', 'director', 'Visión institucional, reportes y seguimiento'),
('Docente', 'docente', 'Registro de calificaciones, asistencia y alertas'),
('Tutor', 'tutor', 'Seguimiento y acompañamiento de estudiantes'),
('Auxiliar', 'auxiliar', 'Control de asistencia e incidencias básicas');

-- ---------- Permisos ----------
INSERT INTO permisos (nombre, descripcion) VALUES
('dashboard.ver', 'Ver dashboard'),
('usuarios.gestion', 'Gestionar usuarios'),
('estudiantes.ver', 'Ver estudiantes'),
('estudiantes.crear', 'Crear estudiantes'),
('estudiantes.editar', 'Editar estudiantes'),
('estudiantes.eliminar', 'Eliminar/desactivar estudiantes'),
('estudiantes.exportar', 'Exportar estudiantes'),
('docentes.ver', 'Ver docentes'),
('docentes.gestion', 'Gestionar docentes'),
('cursos.gestion', 'Gestionar cursos'),
('grados.gestion', 'Gestionar grados'),
('secciones.gestion', 'Gestionar secciones'),
('periodos.gestion', 'Gestionar periodos académicos'),
('matriculas.gestion', 'Gestionar matrículas'),
('calificaciones.gestion', 'Registrar calificaciones'),
('asistencia.gestion', 'Registrar asistencia'),
('alertas.ver', 'Ver alertas'),
('alertas.generar', 'Generar alertas automáticas'),
('alertas.gestion', 'Atender y gestionar alertas'),
('seguimiento.gestion', 'Registrar seguimiento'),
('reportes.ver', 'Ver reportes'),
('reportes.exportar', 'Exportar reportes'),
('notificaciones.ver', 'Ver notificaciones'),
('auditoria.ver', 'Ver auditoría'),
('configuracion.gestion', 'Gestionar configuración');

-- ---------- Rol - permisos ----------
-- ADMIN: todos
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p WHERE r.slug='admin';

-- DIRECTOR
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p
WHERE r.slug='director' AND p.nombre IN
('dashboard.ver','estudiantes.ver','docentes.ver','alertas.ver','alertas.gestion','seguimiento.gestion','reportes.ver','reportes.exportar','notificaciones.ver','cursos.gestion','grados.gestion','secciones.gestion','periodos.gestion','matriculas.gestion','calificaciones.gestion','asistencia.gestion','estudiantes.exportar');

-- DOCENTE
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p
WHERE r.slug='docente' AND p.nombre IN
('dashboard.ver','estudiantes.ver','calificaciones.gestion','asistencia.gestion','alertas.ver','alertas.generar','alertas.gestion','seguimiento.gestion','reportes.ver','notificaciones.ver','cursos.gestion');

-- TUTOR
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p
WHERE r.slug='tutor' AND p.nombre IN
('dashboard.ver','estudiantes.ver','alertas.ver','seguimiento.gestion','notificaciones.ver','reportes.ver','estudiantes.exportar');

-- AUXILIAR
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p
WHERE r.slug='auxiliar' AND p.nombre IN
('dashboard.ver','estudiantes.ver','asistencia.gestion','notificaciones.ver');

-- ---------- Usuarios demo (contraseña: 12345678) ----------
INSERT INTO users (rol_id, nombres, apellidos, email, password, estado) VALUES
((SELECT id FROM roles WHERE slug='admin'), 'Administrador', 'EduAlert', 'admin@edualert.test', '$2y$10$51NM42tR4Y4w3uMw4c/wxuznEBotZADAkRhWSEIjYd/6QIyGBSVli', 1),
((SELECT id FROM roles WHERE slug='director'), 'María', 'Ramírez Torres', 'director@edualert.test', '$2y$10$51NM42tR4Y4w3uMw4c/wxuznEBotZADAkRhWSEIjYd/6QIyGBSVli', 1),
((SELECT id FROM roles WHERE slug='docente'), 'Carlos', 'Gómez Ruiz', 'docente@edualert.test', '$2y$10$51NM42tR4Y4w3uMw4c/wxuznEBotZADAkRhWSEIjYd/6QIyGBSVli', 1),
((SELECT id FROM roles WHERE slug='tutor'), 'Lucía', 'Fernández Díaz', 'tutor@edualert.test', '$2y$10$51NM42tR4Y4w3uMw4c/wxuznEBotZADAkRhWSEIjYd/6QIyGBSVli', 1),
((SELECT id FROM roles WHERE slug='auxiliar'), 'Pedro', 'Cárdenas López', 'auxiliar@edualert.test', '$2y$10$51NM42tR4Y4w3uMw4c/wxuznEBotZADAkRhWSEIjYd/6QIyGBSVli', 1);

-- ---------- Niveles de riesgo ----------
INSERT INTO niveles_riesgo (nombre, color, min_puntaje, max_puntaje, descripcion) VALUES
('Bajo', 'success', 0, 29, 'Desempeño académico sin riesgo significativo'),
('Medio', 'warning', 30, 59, 'Presenta factores de riesgo que requieren monitoreo'),
('Alto', 'danger', 60, 100, 'Riesgo académico elevado, requiere intervención inmediata');

-- ---------- Parámetros configurables (motor de alertas) ----------
INSERT INTO parametros (clave, valor, descripcion, grupo) VALUES
('promedio_aprobatorio', '11', 'Nota mínima aprobatoria', 'rendimiento'),
('riesgo_bajo_min', '14', 'Promedio mínimo para riesgo bajo', 'rendimiento'),
('riesgo_medio_min', '11', 'Promedio mínimo para riesgo medio (entre 11 y 13.99)', 'rendimiento'),
('puntos_riesgo_medio', '20', 'Puntos por rendimiento medio', 'puntaje'),
('puntos_riesgo_alto', '40', 'Puntos por rendimiento alto', 'puntaje'),
('asistencia_normal_min', '90', 'Porcentaje mínimo de asistencia normal', 'asistencia'),
('asistencia_preventiva_min', '80', 'Porcentaje mínimo de asistencia preventiva', 'asistencia'),
('puntos_asistencia_media', '20', 'Puntos por asistencia preventiva (80-89%)', 'puntaje'),
('puntos_asistencia_alta', '30', 'Puntos por asistencia baja (<80%)', 'puntaje'),
('puntos_curso_1', '10', 'Puntos por 1 curso desaprobado', 'puntaje'),
('puntos_curso_2', '20', 'Puntos por 2 cursos desaprobados', 'puntaje'),
('puntos_curso_3', '30', 'Puntos por 3 o más cursos desaprobados', 'puntaje'),
('puntos_tendencia', '20', 'Puntos por tendencia descendente', 'puntaje'),
('tendencia_umbral', '0.5', 'Diferencia mínima de promedio para considerar descenso', 'rendimiento'),
('max_intentos_login', '5', 'Máximo de intentos de login antes de bloqueo', 'seguridad'),
('minutos_bloqueo', '15', 'Minutos de bloqueo por intentos fallidos', 'seguridad'),
('dias_alerta_inasistencia', '3', 'Faltas en un mes para alerta de inasistencia', 'asistencia'),
('tardanzas_alerta', '4', 'Tardanzas para considerar alerta', 'asistencia'),
('semanas_retrospectivas_tendencia', '2', 'Semanas para evaluar tendencia', 'rendimiento'),
('institucion_nombre', 'Institución Educativa Juan Jiménez Pimentel', 'Nombre de la institución', 'institucion'),
('institucion_director', 'Director(a) de la I.E.', 'Nombre del director para reportes', 'institucion'),
('institucion_ciudad', 'Tarapoto, San Martín - Perú', 'Ubicación', 'institucion'),
('anio_lectivo', '2025', 'Año lectivo activo', 'institucion');

-- ---------- Grados (Secundaria) ----------
INSERT INTO grados (nombre, nivel, orden) VALUES
('1° de Secundaria', 'Secundaria', 1),
('2° de Secundaria', 'Secundaria', 2),
('3° de Secundaria', 'Secundaria', 3),
('4° de Secundaria', 'Secundaria', 4),
('5° de Secundaria', 'Secundaria', 5);

-- ---------- Secciones ----------
INSERT INTO secciones (grado_id, nombre, turno) VALUES
((SELECT id FROM grados WHERE orden=1), 'A', 'Mañana'),
((SELECT id FROM grados WHERE orden=1), 'B', 'Mañana'),
((SELECT id FROM grados WHERE orden=2), 'A', 'Mañana'),
((SELECT id FROM grados WHERE orden=2), 'B', 'Mañana'),
((SELECT id FROM grados WHERE orden=3), 'A', 'Mañana'),
((SELECT id FROM grados WHERE orden=3), 'B', 'Turno Tarde'),
((SELECT id FROM grados WHERE orden=4), 'A', 'Mañana'),
((SELECT id FROM grados WHERE orden=4), 'B', 'Turno Tarde'),
((SELECT id FROM grados WHERE orden=5), 'A', 'Mañana'),
((SELECT id FROM grados WHERE orden=5), 'B', 'Turno Tarde');

-- ---------- Periodos académicos ----------
INSERT INTO periodos (nombre, anio, fecha_inicio, fecha_fin, activo) VALUES
('I Bimestre', 2025, '2025-03-10', '2025-05-15', 0),
('II Bimestre', 2025, '2025-05-19', '2025-07-25', 0),
('III Bimestre', 2025, '2025-08-11', '2025-10-10', 0),
('IV Bimestre', 2025, '2025-10-14', '2025-12-18', 0),
('I Bimestre', 2026, '2026-03-09', '2026-05-14', 0),
('II Bimestre', 2026, '2026-05-18', '2026-07-24', 0),
('III Bimestre', 2026, '2026-08-10', '2026-10-09', 1),
('IV Bimestre', 2026, '2026-10-13', '2026-12-17', 0);

-- ---------- Cursos ----------
INSERT INTO cursos (nombre, area, descripcion) VALUES
('Comunicación', 'Comunicación', 'Comprensión lectora y producción escrita'),
('Matemática', 'Matemática', 'Razonamiento matemático y resolución de problemas'),
('Ciencia y Tecnología', 'Ciencia y Tecnología', 'Investigación y pensamiento científico'),
('Ciencias Sociales', 'Ciencias Sociales', 'Historia, geografía y ciudadanía'),
('Inglés', 'Idiomas', 'Idioma extranjero'),
('Educación Física', 'Educación Física', 'Actividad física y salud'),
('Arte y Cultura', 'Arte y Cultura', 'Expresión artística y cultural'),
('Educación Religiosa', 'Educación Religiosa', 'Formación espiritual'),
('Tutoría', 'Tutoría', 'Orientación y acompañamiento');