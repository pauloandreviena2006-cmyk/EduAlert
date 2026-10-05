<?php
/**
 * EduAlert JJP - app/Controllers/CalificacionesController.php
 * Registro de calificaciones (masivo por sección y curso), consulta y filtros
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;

class CalificacionesController extends Controller
{
    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $cursoId = (int)($_GET['curso_id'] ?? 0);
        $periodoId = (int)($_GET['periodo_id'] ?? 0);
        $periodo = $periodoId ? Periodo::buscar($periodoId) : Periodo::activo();
        $periodoId = $periodo ? (int)$periodo['id'] : 0;

        $sql = 'SELECT c.*, e.codigo, e.dni, e.nombres, e.apellidos, cu.nombre AS curso_nombre,
                       g.nombre AS grado_nombre, s.nombre AS seccion_nombre, p.nombre AS periodo_nombre
                FROM calificaciones c
                INNER JOIN matriculas m ON m.id = c.matricula_id
                INNER JOIN estudiantes e ON e.id = m.estudiante_id
                INNER JOIN cursos cu ON cu.id = c.curso_id
                INNER JOIN grados g ON g.id = m.grado_id
                INNER JOIN secciones s ON s.id = m.seccion_id
                INNER JOIN periodos p ON p.id = c.periodo_id
                WHERE c.periodo_id = ?';
        $params = [$periodoId];
        if ($busqueda !== '') {
            $sql .= ' AND (e.codigo LIKE ? OR e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)';
            $b = "%$busqueda%"; $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
        if ($seccionId > 0) { $sql .= ' AND m.seccion_id = ?'; $params[] = $seccionId; }
        if ($cursoId > 0) { $sql .= ' AND c.curso_id = ?'; $params[] = $cursoId; }
        $sql .= ' ORDER BY g.orden, s.nombre, e.apellidos ASC LIMIT 1000';

        $this->vista('calificaciones/index', [
            'page_title' => 'Calificaciones',
            'active' => 'academico',
            'calificaciones' => Database::select($sql, $params),
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'cursos' => Curso::todos('nombre ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'periodo' => $periodo,
            'periodo_id' => $periodoId,
            'q' => $busqueda, 'grado_id' => $gradoId, 'seccion_id' => $seccionId, 'curso_id' => $cursoId,
        ]);
    }

    /** Formulario masivo: seleccionar sección, curso y periodo */
    public function registro(): void
    {
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $cursoId = (int)($_GET['curso_id'] ?? 0);
        $periodo = Periodo::activo();
        $periodoId = (int)($_GET['periodo_id'] ?? ($periodo ? (int)$periodo['id'] : 0));

        $estudiantes = [];
        $curso = null;
        $docenteActual = null;
        if ($seccionId > 0 && $cursoId > 0 && $periodoId > 0) {
            $estudiantes = Matricula::estudiantesDeSeccion($seccionId, $periodoId);
            $curso = Curso::buscar($cursoId);
            // Docente asignado al curso/sección
            $doc = Database::selectOne(
                'SELECT d.id, d.nombres, d.apellidos FROM docentes_cursos dc
                 INNER JOIN docentes d ON d.id = dc.docente_id
                 WHERE dc.curso_id = ? AND dc.seccion_id = ? AND dc.periodo_id = ? LIMIT 1',
                [$cursoId, $seccionId, $periodoId]
            );
            if ($doc) $docenteActual = $doc;
            // Cargar notas existentes
            $notas = [];
            if (!empty($estudiantes)) {
                $ids = array_map(fn($e) => (int)$e['matricula_id'], $estudiantes);
                $marcadores = implode(',', array_fill(0, count($ids), '?'));
                $existentes = Database::select(
                    "SELECT matricula_id, nota FROM calificaciones WHERE curso_id = ? AND periodo_id = ? AND matricula_id IN ($marcadores)",
                    array_merge([$cursoId, $periodoId], $ids)
                );
                foreach ($existentes as $ex) $notas[(int)$ex['matricula_id']] = (float)$ex['nota'];
            }
        }

        $this->vista('calificaciones/form', [
            'page_title' => 'Registro de calificaciones',
            'active' => 'academico',
            'secciones' => Seccion::listarConGrado(),
            'cursos' => Curso::todos('nombre ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'seccion_id' => $seccionId, 'curso_id' => $cursoId, 'periodo_id' => $periodoId,
            'estudiantes' => $estudiantes,
            'curso' => $curso,
            'docente' => $docenteActual,
            'notas' => $notas ?? [],
            'modo' => 'registro',
        ]);
    }

    public function guardarRegistro(): void
    {
        $seccionId = (int)($_POST['seccion_id'] ?? 0);
        $cursoId = (int)($_POST['curso_id'] ?? 0);
        $periodoId = (int)($_POST['periodo_id'] ?? 0);
        $notas = $_POST['notas'] ?? [];
        $competencias = $_POST['competencia'] ?? [];

        if (!$seccionId || !$cursoId || !$periodoId) {
            flash_set('error', 'Complete la selección de sección, curso y periodo.');
            redirect('calificaciones/registro');
        }

        // Docente actual (del usuario o asignación)
        $docenteId = null;
        if (has_role('docente')) {
            $u = current_user();
            $doc = Database::selectOne('SELECT id FROM docentes WHERE user_id = ?', [(int)$u['id']]);
            if ($doc) $docenteId = (int)$doc['id'];
        } else {
            $doc = Database::selectOne(
                'SELECT docente_id FROM docentes_cursos WHERE curso_id = ? AND seccion_id = ? AND periodo_id = ? LIMIT 1',
                [$cursoId, $seccionId, $periodoId]
            );
            if ($doc) $docenteId = (int)$doc['docente_id'];
        }

        $contador = 0;
        $errores = 0;
        Database::begin();
        try {
            foreach ($notas as $matriculaId => $nota) {
                $matriculaId = (int)$matriculaId;
                $nota = trim((string)$nota);
                if ($nota === '') continue;
                if (!is_numeric($nota) || (float)$nota < 0 || (float)$nota > 20) {
                    $errores++;
                    continue;
                }
                $competencia = $competencias[$matriculaId] ?? 'Logros de aprendizaje';
                $existente = Database::selectOne(
                    'SELECT id FROM calificaciones WHERE matricula_id = ? AND curso_id = ? AND periodo_id = ?',
                    [$matriculaId, $cursoId, $periodoId]
                );
                if ($existente) {
                    Database::update('calificaciones', ['nota' => (float)$nota, 'competencia' => $competencia, 'docente_id' => $docenteId], 'id = ?', [1 => $existente['id']]);
                } else {
                    Calificacion::crear([
                        'matricula_id' => $matriculaId,
                        'curso_id' => $cursoId,
                        'docente_id' => $docenteId,
                        'periodo_id' => $periodoId,
                        'competencia' => $competencia,
                        'nota' => (float)$nota,
                    ]);
                }
                $contador++;
            }
            Database::commit();
            auditoria('Calificaciones', 'Registro', "Se registraron/actualizaron $contador calificaciones del curso ID $cursoId (sección $seccionId, periodo $periodoId)");
            flash_set('success', "✅ Se guardaron $contador calificaciones correctamente." . ($errores > 0 ? " ($errores omitidas por nota inválida)" : ''));
        } catch (\Throwable $e) {
            Database::rollback();
            log_error('Error calificaciones: ' . $e->getMessage());
            flash_set('error', 'No se pudo completar el registro. Verifique los datos.');
        }
        redirect('calificaciones/registro?seccion_id=' . $seccionId . '&curso_id=' . $cursoId . '&periodo_id=' . $periodoId);
    }
}