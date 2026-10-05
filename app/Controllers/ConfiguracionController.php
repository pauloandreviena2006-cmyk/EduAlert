<?php
/**
 * EduAlert JJP - app/Controllers/ConfiguracionController.php
 * Parámetros configurables del motor de alertas (admin)
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Configuracion;
use App\Models\NivelRiesgo;

class ConfiguracionController extends Controller
{
    public function index(): void
    {
        $parametros = Configuracion::todos();
        $niveles = NivelRiesgo::todos('min_puntaje ASC');
        $this->vista('configuracion/index', [
            'page_title' => 'Configuración del sistema',
            'active' => 'configuracion',
            'parametros' => $parametros,
            'niveles' => $niveles,
            'demo_data' => $parametros['demo_data'] ?? '0',
        ]);
    }

    public function guardar(): void
    {
        Configuracion::actualizarMasivo($_POST);
        auditoria('Configuración', 'Actualización', 'Parámetros del sistema actualizados');
        flash_set('success', '✅ Parámetros del sistema guardados correctamente.');
        redirect('configuracion');
    }

    /** Elimina toda la data demo (estudiantes, calificaciones, asistencia, alertas, etc.) */
    public function limpiarDemo(): void
    {
        try {
            Database::begin();
            foreach (['seguimientos', 'alertas_motivos', 'alertas', 'calificaciones', 'asistencias', 'matriculas', 'apoderados', 'estudiantes', 'docentes_cursos', 'docentes', 'notificaciones'] as $tabla) {
                Database::delete($tabla, '1=1');
            }
            Database::update('estudiantes', ['nivel_riesgo_id' => null, 'puntaje_riesgo' => null, 'ultimo_promedio' => null, 'ultimo_asistencia_pct' => null, 'ultimo_desaprobados' => null], '1=1');
            Configuracion::set('demo_data', '0');
            Database::commit();
            auditoria('Configuración', 'Limpieza', 'Datos demo eliminados completamente');
            flash_set('success', '✅ Datos de demostración eliminados. El sistema quedó limpio.');
        } catch (\Throwable $e) {
            Database::rollback();
            log_error('Error limpiando demo: ' . $e->getMessage());
            flash_set('error', 'No se pudieron eliminar los datos. Consulte el log.');
        }
        redirect('configuracion');
    }
}