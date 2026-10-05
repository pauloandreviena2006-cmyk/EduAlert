<?php
/**
 * EduAlert JJP - routes/web.php
 * Definición de rutas del sistema con permisos.
 * Formato: ['url' => '...', 'metodo' => 'GET|POST|*', 'handler' => 'Controlador@accion', 'permiso' => '...'|null, 'auth' => true|false]
 *
 * - 'auth' => false permite acceso público (login, recuperación, instalación)
 * - 'instalacion' => true solo para el instalador
 */

return [
    // ---------- AUTENTICACIÓN ----------
    ['url' => 'login', 'metodo' => 'GET', 'handler' => 'AuthController@loginForm', 'permiso' => null, 'auth' => false],
    ['url' => 'login', 'metodo' => 'POST', 'handler' => 'AuthController@login', 'permiso' => null, 'auth' => false],
    ['url' => 'logout', 'metodo' => 'GET', 'handler' => 'AuthController@logout', 'permiso' => null, 'auth' => true],
    ['url' => 'olvide', 'metodo' => 'GET', 'handler' => 'PasswordController@olvideForm', 'permiso' => null, 'auth' => false],
    ['url' => 'olvide', 'metodo' => 'POST', 'handler' => 'PasswordController@olvide', 'permiso' => null, 'auth' => false],
    ['url' => 'restablecer/{token}', 'metodo' => 'GET', 'handler' => 'PasswordController@resetForm', 'permiso' => null, 'auth' => false],
    ['url' => 'restablecer/{token}', 'metodo' => 'POST', 'handler' => 'PasswordController@reset', 'permiso' => null, 'auth' => false],
    ['url' => 'perfil', 'metodo' => 'GET', 'handler' => 'PerfilController@index', 'permiso' => null, 'auth' => true],
    ['url' => 'perfil', 'metodo' => 'POST', 'handler' => 'PerfilController@actualizar', 'permiso' => null, 'auth' => true],
    ['url' => 'perfil/password', 'metodo' => 'POST', 'handler' => 'PerfilController@password', 'permiso' => null, 'auth' => true],

    // ---------- INSTALADOR ----------
    ['url' => 'instalacion', 'metodo' => 'GET', 'handler' => 'InstalacionController@index', 'permiso' => null, 'auth' => false, 'instalacion' => true],
    ['url' => 'instalacion/crear-bd', 'metodo' => 'POST', 'handler' => 'InstalacionController@crearBaseDatos', 'permiso' => null, 'auth' => false, 'instalacion' => true],
    ['url' => 'instalacion/datos-demo', 'metodo' => 'POST', 'handler' => 'InstalacionController@cargarDatosDemo', 'permiso' => null, 'auth' => false, 'instalacion' => true],

    // ---------- DASHBOARD ----------
    ['url' => '', 'metodo' => 'GET', 'handler' => 'DashboardController@index', 'permiso' => 'dashboard.ver', 'auth' => true],
    ['url' => 'dashboard', 'metodo' => 'GET', 'handler' => 'DashboardController@index', 'permiso' => 'dashboard.ver', 'auth' => true],
    ['url' => 'dashboard/datos', 'metodo' => 'GET', 'handler' => 'DashboardController@datos', 'permiso' => 'dashboard.ver', 'auth' => true],
    ['url' => 'dashboard/actualizar-evaluacion', 'metodo' => 'POST', 'handler' => 'DashboardController@actualizarEvaluacion', 'permiso' => 'alertas.generar', 'auth' => true],

    // ---------- ESTUDIANTES ----------
    ['url' => 'estudiantes', 'metodo' => 'GET', 'handler' => 'EstudiantesController@index', 'permiso' => 'estudiantes.ver', 'auth' => true],
    ['url' => 'estudiantes/crear', 'metodo' => 'GET', 'handler' => 'EstudiantesController@crear', 'permiso' => 'estudiantes.crear', 'auth' => true],
    ['url' => 'estudiantes/crear', 'metodo' => 'POST', 'handler' => 'EstudiantesController@guardar', 'permiso' => 'estudiantes.crear', 'auth' => true],
    ['url' => 'estudiantes/editar/{id}', 'metodo' => 'GET', 'handler' => 'EstudiantesController@editar', 'permiso' => 'estudiantes.editar', 'auth' => true],
    ['url' => 'estudiantes/editar/{id}', 'metodo' => 'POST', 'handler' => 'EstudiantesController@actualizar', 'permiso' => 'estudiantes.editar', 'auth' => true],
    ['url' => 'estudiantes/ver/{id}', 'metodo' => 'GET', 'handler' => 'EstudiantesController@ver', 'permiso' => 'estudiantes.ver', 'auth' => true],
    ['url' => 'estudiantes/estado/{id}', 'metodo' => 'POST', 'handler' => 'EstudiantesController@cambiarEstado', 'permiso' => 'estudiantes.eliminar', 'auth' => true],
    ['url' => 'estudiantes/eliminar/{id}', 'metodo' => 'POST', 'handler' => 'EstudiantesController@eliminar', 'permiso' => 'estudiantes.eliminar', 'auth' => true],
    ['url' => 'estudiantes/exportar', 'metodo' => 'GET', 'handler' => 'EstudiantesController@exportar', 'permiso' => 'estudiantes.exportar', 'auth' => true],
    ['url' => 'estudiantes/evolucion/{id}', 'metodo' => 'GET', 'handler' => 'EstudiantesController@evolucion', 'permiso' => 'estudiantes.ver', 'auth' => true],

    // ---------- DOCENTES ----------
    ['url' => 'docentes', 'metodo' => 'GET', 'handler' => 'DocentesController@index', 'permiso' => 'docentes.ver', 'auth' => true],
    ['url' => 'docentes/crear', 'metodo' => 'GET', 'handler' => 'DocentesController@crear', 'permiso' => 'docentes.gestion', 'auth' => true],
    ['url' => 'docentes/crear', 'metodo' => 'POST', 'handler' => 'DocentesController@guardar', 'permiso' => 'docentes.gestion', 'auth' => true],
    ['url' => 'docentes/editar/{id}', 'metodo' => 'GET', 'handler' => 'DocentesController@editar', 'permiso' => 'docentes.gestion', 'auth' => true],
    ['url' => 'docentes/editar/{id}', 'metodo' => 'POST', 'handler' => 'DocentesController@actualizar', 'permiso' => 'docentes.gestion', 'auth' => true],
    ['url' => 'docentes/estado/{id}', 'metodo' => 'POST', 'handler' => 'DocentesController@cambiarEstado', 'permiso' => 'docentes.gestion', 'auth' => true],

    // ---------- ACADÉMICO: PERIODOS ----------
    ['url' => 'periodos', 'metodo' => 'GET', 'handler' => 'PeriodosController@index', 'permiso' => 'periodos.gestion', 'auth' => true],
    ['url' => 'periodos/crear', 'metodo' => 'GET', 'handler' => 'PeriodosController@crear', 'permiso' => 'periodos.gestion', 'auth' => true],
    ['url' => 'periodos/crear', 'metodo' => 'POST', 'handler' => 'PeriodosController@guardar', 'permiso' => 'periodos.gestion', 'auth' => true],
    ['url' => 'periodos/editar/{id}', 'metodo' => 'GET', 'handler' => 'PeriodosController@editar', 'permiso' => 'periodos.gestion', 'auth' => true],
    ['url' => 'periodos/editar/{id}', 'metodo' => 'POST', 'handler' => 'PeriodosController@actualizar', 'permiso' => 'periodos.gestion', 'auth' => true],
    ['url' => 'periodos/activar/{id}', 'metodo' => 'POST', 'handler' => 'PeriodosController@activar', 'permiso' => 'periodos.gestion', 'auth' => true],

    // ---------- ACADÉMICO: GRADOS ----------
    ['url' => 'grados', 'metodo' => 'GET', 'handler' => 'GradosController@index', 'permiso' => 'grados.gestion', 'auth' => true],
    ['url' => 'grados/crear', 'metodo' => 'GET', 'handler' => 'GradosController@crear', 'permiso' => 'grados.gestion', 'auth' => true],
    ['url' => 'grados/crear', 'metodo' => 'POST', 'handler' => 'GradosController@guardar', 'permiso' => 'grados.gestion', 'auth' => true],
    ['url' => 'grados/editar/{id}', 'metodo' => 'GET', 'handler' => 'GradosController@editar', 'permiso' => 'grados.gestion', 'auth' => true],
    ['url' => 'grados/editar/{id}', 'metodo' => 'POST', 'handler' => 'GradosController@actualizar', 'permiso' => 'grados.gestion', 'auth' => true],

    // ---------- ACADÉMICO: SECCIONES ----------
    ['url' => 'secciones', 'metodo' => 'GET', 'handler' => 'SeccionesController@index', 'permiso' => 'secciones.gestion', 'auth' => true],
    ['url' => 'secciones/crear', 'metodo' => 'GET', 'handler' => 'SeccionesController@crear', 'permiso' => 'secciones.gestion', 'auth' => true],
    ['url' => 'secciones/crear', 'metodo' => 'POST', 'handler' => 'SeccionesController@guardar', 'permiso' => 'secciones.gestion', 'auth' => true],
    ['url' => 'secciones/editar/{id}', 'metodo' => 'GET', 'handler' => 'SeccionesController@editar', 'permiso' => 'secciones.gestion', 'auth' => true],
    ['url' => 'secciones/editar/{id}', 'metodo' => 'POST', 'handler' => 'SeccionesController@actualizar', 'permiso' => 'secciones.gestion', 'auth' => true],
    ['url' => 'secciones/por-grado/{id}', 'metodo' => 'GET', 'handler' => 'SeccionesController@porGradoJson', 'permiso' => null, 'auth' => true],

    // ---------- ACADÉMICO: CURSOS ----------
    ['url' => 'cursos', 'metodo' => 'GET', 'handler' => 'CursosController@index', 'permiso' => 'cursos.gestion', 'auth' => true],
    ['url' => 'cursos/crear', 'metodo' => 'GET', 'handler' => 'CursosController@crear', 'permiso' => 'cursos.gestion', 'auth' => true],
    ['url' => 'cursos/crear', 'metodo' => 'POST', 'handler' => 'CursosController@guardar', 'permiso' => 'cursos.gestion', 'auth' => true],
    ['url' => 'cursos/editar/{id}', 'metodo' => 'GET', 'handler' => 'CursosController@editar', 'permiso' => 'cursos.gestion', 'auth' => true],
    ['url' => 'cursos/editar/{id}', 'metodo' => 'POST', 'handler' => 'CursosController@actualizar', 'permiso' => 'cursos.gestion', 'auth' => true],
    ['url' => 'cursos/asignaciones', 'metodo' => 'GET', 'handler' => 'CursosController@asignaciones', 'permiso' => 'cursos.gestion', 'auth' => true],
    ['url' => 'cursos/asignaciones', 'metodo' => 'POST', 'handler' => 'CursosController@guardarAsignaciones', 'permiso' => 'cursos.gestion', 'auth' => true],

    // ---------- MATRÍCULAS ----------
    ['url' => 'matriculas', 'metodo' => 'GET', 'handler' => 'MatriculasController@index', 'permiso' => 'matriculas.gestion', 'auth' => true],
    ['url' => 'matriculas/crear', 'metodo' => 'GET', 'handler' => 'MatriculasController@crear', 'permiso' => 'matriculas.gestion', 'auth' => true],
    ['url' => 'matriculas/crear', 'metodo' => 'POST', 'handler' => 'MatriculasController@guardar', 'permiso' => 'matriculas.gestion', 'auth' => true],
    ['url' => 'matriculas/editar/{id}', 'metodo' => 'GET', 'handler' => 'MatriculasController@editar', 'permiso' => 'matriculas.gestion', 'auth' => true],
    ['url' => 'matriculas/editar/{id}', 'metodo' => 'POST', 'handler' => 'MatriculasController@actualizar', 'permiso' => 'matriculas.gestion', 'auth' => true],
    ['url' => 'matriculas/estado/{id}', 'metodo' => 'POST', 'handler' => 'MatriculasController@cambiarEstado', 'permiso' => 'matriculas.gestion', 'auth' => true],

    // ---------- CALIFICACIONES ----------
    ['url' => 'calificaciones', 'metodo' => 'GET', 'handler' => 'CalificacionesController@index', 'permiso' => 'calificaciones.gestion', 'auth' => true],
    ['url' => 'calificaciones/registro', 'metodo' => 'GET', 'handler' => 'CalificacionesController@registro', 'permiso' => 'calificaciones.gestion', 'auth' => true],
    ['url' => 'calificaciones/registro', 'metodo' => 'POST', 'handler' => 'CalificacionesController@guardarRegistro', 'permiso' => 'calificaciones.gestion', 'auth' => true],

    // ---------- ASISTENCIA ----------
    ['url' => 'asistencia', 'metodo' => 'GET', 'handler' => 'AsistenciaController@index', 'permiso' => 'asistencia.gestion', 'auth' => true],
    ['url' => 'asistencia/registro', 'metodo' => 'GET', 'handler' => 'AsistenciaController@registro', 'permiso' => 'asistencia.gestion', 'auth' => true],
    ['url' => 'asistencia/registro', 'metodo' => 'POST', 'handler' => 'AsistenciaController@guardarRegistro', 'permiso' => 'asistencia.gestion', 'auth' => true],
    ['url' => 'asistencia/dia', 'metodo' => 'GET', 'handler' => 'AsistenciaController@porDia', 'permiso' => 'asistencia.gestion', 'auth' => true],

    // ---------- ALERTAS ----------
    ['url' => 'alertas', 'metodo' => 'GET', 'handler' => 'AlertasController@index', 'permiso' => 'alertas.ver', 'auth' => true],
    ['url' => 'alertas/ver/{id}', 'metodo' => 'GET', 'handler' => 'AlertasController@ver', 'permiso' => 'alertas.ver', 'auth' => true],
    ['url' => 'alertas/generar', 'metodo' => 'POST', 'handler' => 'AlertasController@generar', 'permiso' => 'alertas.generar', 'auth' => true],
    ['url' => 'alertas/estado/{id}', 'metodo' => 'POST', 'handler' => 'AlertasController@cambiarEstado', 'permiso' => 'alertas.gestion', 'auth' => true],
    ['url' => 'alertas/asignar/{id}', 'metodo' => 'POST', 'handler' => 'AlertasController@asignar', 'permiso' => 'alertas.gestion', 'auth' => true],
    ['url' => 'alertas/exportar', 'metodo' => 'GET', 'handler' => 'AlertasController@exportar', 'permiso' => 'reportes.exportar', 'auth' => true],

    // ---------- SEGUIMIENTO ----------
    ['url' => 'seguimiento', 'metodo' => 'GET', 'handler' => 'SeguimientoController@index', 'permiso' => 'seguimiento.gestion', 'auth' => true],
    ['url' => 'seguimiento/crear/{alertaId}', 'metodo' => 'GET', 'handler' => 'SeguimientoController@crear', 'permiso' => 'seguimiento.gestion', 'auth' => true],
    ['url' => 'seguimiento/crear/{alertaId}', 'metodo' => 'POST', 'handler' => 'SeguimientoController@guardar', 'permiso' => 'seguimiento.gestion', 'auth' => true],
    ['url' => 'seguimiento/eliminar/{id}', 'metodo' => 'POST', 'handler' => 'SeguimientoController@eliminar', 'permiso' => 'seguimiento.gestion', 'auth' => true],

    // ---------- REPORTES ----------
    ['url' => 'reportes', 'metodo' => 'GET', 'handler' => 'ReportesController@index', 'permiso' => 'reportes.ver', 'auth' => true],
    ['url' => 'reportes/exportar', 'metodo' => 'GET', 'handler' => 'ReportesController@exportar', 'permiso' => 'reportes.exportar', 'auth' => true],

    // ---------- NOTIFICACIONES ----------
    ['url' => 'notificaciones', 'metodo' => 'GET', 'handler' => 'NotificacionesController@index', 'permiso' => 'notificaciones.ver', 'auth' => true],
    ['url' => 'notificaciones/marcar/{id}', 'metodo' => 'POST', 'handler' => 'NotificacionesController@marcar', 'permiso' => 'notificaciones.ver', 'auth' => true],
    ['url' => 'notificaciones/marcar-todas', 'metodo' => 'POST', 'handler' => 'NotificacionesController@marcarTodas', 'permiso' => 'notificaciones.ver', 'auth' => true],

    // ---------- USUARIOS ----------
    ['url' => 'usuarios', 'metodo' => 'GET', 'handler' => 'UsuariosController@index', 'permiso' => 'usuarios.gestion', 'auth' => true],
    ['url' => 'usuarios/crear', 'metodo' => 'GET', 'handler' => 'UsuariosController@crear', 'permiso' => 'usuarios.gestion', 'auth' => true],
    ['url' => 'usuarios/crear', 'metodo' => 'POST', 'handler' => 'UsuariosController@guardar', 'permiso' => 'usuarios.gestion', 'auth' => true],
    ['url' => 'usuarios/editar/{id}', 'metodo' => 'GET', 'handler' => 'UsuariosController@editar', 'permiso' => 'usuarios.gestion', 'auth' => true],
    ['url' => 'usuarios/editar/{id}', 'metodo' => 'POST', 'handler' => 'UsuariosController@actualizar', 'permiso' => 'usuarios.gestion', 'auth' => true],
    ['url' => 'usuarios/estado/{id}', 'metodo' => 'POST', 'handler' => 'UsuariosController@cambiarEstado', 'permiso' => 'usuarios.gestion', 'auth' => true],
    ['url' => 'usuarios/eliminar/{id}', 'metodo' => 'POST', 'handler' => 'UsuariosController@eliminar', 'permiso' => 'usuarios.gestion', 'auth' => true],

    // ---------- AUDITORÍA ----------
    ['url' => 'auditoria', 'metodo' => 'GET', 'handler' => 'AuditoriaController@index', 'permiso' => 'auditoria.ver', 'auth' => true],

    // ---------- CONFIGURACIÓN ----------
    ['url' => 'configuracion', 'metodo' => 'GET', 'handler' => 'ConfiguracionController@index', 'permiso' => 'configuracion.gestion', 'auth' => true],
    ['url' => 'configuracion', 'metodo' => 'POST', 'handler' => 'ConfiguracionController@guardar', 'permiso' => 'configuracion.gestion', 'auth' => true],
    ['url' => 'configuracion/limpiar-demo', 'metodo' => 'POST', 'handler' => 'ConfiguracionController@limpiarDemo', 'permiso' => 'configuracion.gestion', 'auth' => true],
];