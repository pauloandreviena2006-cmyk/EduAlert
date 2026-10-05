<?php
/**
 * EduAlert JJP - app/Controllers/AsistenciaController.php
 * Control de asistencia: registro masivo por sección/fecha + consultas
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Asistencia;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;

class AsistenciaController extends Controller
{
    public function index(): void
    {
        $periodo = Periodo::activo();
        $periodoId = (int)($_GET['periodo_id'] ?? ($periodo ? (int)$periodo['id'] : 0));
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $fecha = (string)($_GET['fecha'] ?? date('Y-m-d'));

        // Resumen mensual por sección
        $resumen = [];
        if ($periodoId) {
            $resumen = Database::select(
                'SELECT s.id AS seccion_id, s.nombre AS seccion_nombre, g.nombre AS grado_nombre,
                        COUNT(DISTINCT a.estudiante_id) AS estudiantes,
                        SUM(CASE WHEN a.estado="Presente" THEN 1 ELSE 0 END) AS presentes,
                        SUM(CASE WHEN a.estado="Tardanza" THEN 1 ELSE 0 END) AS tardanzas,
                        SUM(CASE WHEN a.estado="Falta" THEN 1 ELSE 0 END) AS faltas,
                        SUM(CASE WHEN a.estado="Justificado" THEN 1 ELSE 0 END) AS justificados
                 FROM asistencias a
                 INNER JOIN matriculas m ON m.id = a.matricula_id
                 INNER JOIN secciones s ON s.id = m.seccion_id
                 INNER JOIN grados g ON g.id = m.grado_id
                 WHERE m.periodo_id = ?
                 GROUP BY s.id, s.nombre, g.nombre, g.orden
                 ORDER BY g.orden, s.nombre', [$periodoId]
            );
        }

        $this->vista('asistencia/index', [
            'page_title' => 'Control de asistencia',
            'active' => 'academico',
            'periodos' => Periodo::listarOrdenados(),
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'periodo_id' => $periodoId, 'seccion_id' => $seccionId, 'grado_id' => $gradoId, 'fecha' => $fecha,
            'resumen' => $resumen,
        ]);
    }

    /** Registro masivo: seleccionar sección y fecha */
    public function registro(): void
    {
        $periodo = Periodo::activo();
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $fecha = (string)($_GET['fecha'] ?? date('Y-m-d'));
        $periodoId = (int)($_GET['periodo_id'] ?? ($periodo ? (int)$periodo['id'] : 0));

        $estudiantes = [];
        $existentes = [];
        if ($seccionId > 0 && $periodoId > 0) {
            $estudiantes = Matricula::estudiantesDeSeccion($seccionId, $periodoId);
            if (!empty($estudiantes)) {
                $ids = array_map(fn($e) => (int)$e['matricula_id'], $estudiantes);
                $marcadores = implode(',', array_fill(0, count($ids), '?'));
                $existentes = array_column(
                    Database::select("SELECT matricula_id, estado, observacion FROM asistencias WHERE fecha = ? AND matricula_id IN ($marcadores)", array_merge([$fecha], $ids)),
                    null, 'matricula_id'
                );
            }
        }

        $this->vista('asistencia/form', [
            'page_title' => 'Registro de asistencia',
            'active' => 'academico',
            'secciones' => Seccion::listarConGrado(),
            'periodos' => Periodo::listarOrdenados(),
            'seccion_id' => $seccionId, 'fecha' => $fecha, 'periodo_id' => $periodoId,
            'estudiantes' => $estudiantes,
            'existentes' => $existentes,
            'periodo' => $periodo,
        ]);
    }

    public function guardarRegistro(): void
    {
        $seccionId = (int)($_POST['seccion_id'] ?? 0);
        $periodoId = (int)($_POST['periodo_id'] ?? 0);
        $fecha = (string)($_POST['fecha'] ?? date('Y-m-d'));
        $estados = $_POST['estado'] ?? [];
        $observaciones = $_POST['observacion'] ?? [];

        if (!$seccionId || !$periodoId || !strtotime($fecha)) {
            flash_set('error', 'Complete la selección de sección, periodo y fecha válida.');
            redirect('asistencia/registro');
        }

        $registradoPor = current_user_id();
        $contador = 0;
        foreach ($estados as $matriculaId => $estado) {
            $matriculaId = (int)$matriculaId;
            if (!in_array($estado, ['Presente', 'Falta', 'Tardanza', 'Justificado'], true)) continue;
            $observacion = trim((string)($observaciones[$matriculaId] ?? ''));
            $existente = Database::selectOne('SELECT id FROM asistencias WHERE matricula_id = ? AND fecha = ?', [$matriculaId, $fecha]);
            if ($existente) {
                Database::update('asistencias', ['estado' => $estado, 'observacion' => $observacion ?: null, 'registrado_por' => $registradoPor], 'id = ?', [1 => $existente['id']]);
            } else {
                $mat = Matricula::buscar($matriculaId);
                Asistencia::crear([
                    'matricula_id' => $matriculaId,
                    'estudiante_id' => (int)($mat['estudiante_id'] ?? 0),
                    'fecha' => $fecha,
                    'estado' => $estado,
                    'observacion' => $observacion ?: null,
                    'registrado_por' => $registradoPor,
                ]);
            }
            $contador++;
        }
        auditoria('Asistencia', 'Registro', "Asistencia registrada para $contador estudiantes el $fecha (sección $seccionId)");
        flash_set('success', "✅ Se registró la asistencia de $contador estudiantes para la fecha $fecha.");
        redirect('asistencia/registro?seccion_id=' . $seccionId . '&periodo_id=' . $periodoId . '&fecha=' . $fecha);
    }

    /** Consulta de asistencia por día (para un sección y fecha) */
    public function porDia(): void
    {
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $fecha = (string)($_GET['fecha'] ?? date('Y-m-d'));
        $periodo = Periodo::activo();
        $registros = [];
        if ($seccionId > 0 && $periodo) {
            $registros = Database::select(
                'SELECT e.codigo, e.nombres, e.apellidos, a.estado, a.observacion, s.nombre AS seccion_nombre
                 FROM asistencias a
                 INNER JOIN matriculas m ON m.id = a.matricula_id
                 INNER JOIN estudiantes e ON e.id = m.estudiante_id
                 INNER JOIN secciones s ON s.id = m.seccion_id
                 WHERE m.seccion_id = ? AND m.periodo_id = ? AND a.fecha = ?
                 ORDER BY e.apellidos ASC', [$seccionId, (int)$periodo['id'], $fecha]
            );
        }
        $this->vista('asistencia/dia', [
            'page_title' => 'Asistencia por día',
            'active' => 'academico',
            'secciones' => Seccion::listarConGrado(),
            'seccion_id' => $seccionId, 'fecha' => $fecha,
            'registros' => $registros,
        ]);
    }
}