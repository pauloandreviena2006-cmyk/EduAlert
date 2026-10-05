<?php
/**
 * EduAlert JJP - app/Controllers/CursosController.php
 * CRUD de cursos + asignación docente-curso-sección-período
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Periodo;
use App\Models\Seccion;

class CursosController extends Controller
{
    public function index(): void
    {
        $this->vista('cursos/index', [
            'page_title' => 'Cursos',
            'active' => 'academico',
            'cursos' => Curso::todos('nombre ASC'),
        ]);
    }

    public function crear(): void
    {
        $this->vista('cursos/form', ['page_title' => 'Nuevo curso', 'active' => 'academico', 'curso' => null, 'modo' => 'crear']);
    }

    public function guardar(): void
    {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if ($nombre === '') { flash_set('error', 'El nombre del curso es obligatorio.'); redirect('cursos/crear'); }
        try {
            Curso::crear([
                'nombre' => $nombre,
                'area' => trim((string)($_POST['area'] ?? '')) ?: null,
                'descripcion' => trim((string)($_POST['descripcion'] ?? '')) ?: null,
                'estado' => 1,
            ]);
            auditoria('Cursos', 'Registro', 'Curso registrado: ' . $nombre);
            flash_set('success', '✅ Curso registrado correctamente.');
            redirect('cursos');
        } catch (\Throwable $e) {
            flash_set('error', 'Ese curso ya está registrado.');
            redirect('cursos/crear');
        }
    }

    public function editar(string $id): void
    {
        $curso = Curso::buscar((int)$id);
        if (!$curso) redirect('cursos');
        $this->vista('cursos/form', ['page_title' => 'Editar curso', 'active' => 'academico', 'curso' => $curso, 'modo' => 'editar']);
    }

    public function actualizar(string $id): void
    {
        Curso::actualizar((int)$id, [
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'area' => trim((string)($_POST['area'] ?? '')) ?: null,
            'descripcion' => trim((string)($_POST['descripcion'] ?? '')) ?: null,
        ]);
        auditoria('Cursos', 'Actualización', 'Curso actualizado ID ' . $id);
        flash_set('success', '✅ Curso actualizado correctamente.');
        redirect('cursos');
    }

    /** Asignaciones: matríz docente ↔ curso ↔ sección por período */
    public function asignaciones(): void
    {
        $periodoId = (int)($_GET['periodo_id'] ?? 0);
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $periodo = $periodoId ? Periodo::buscar($periodoId) : Periodo::activo();
        if (!$periodo) $periodo = Periodo::primero('1=1 ORDER BY id DESC');

        $secciones = [];
        $docentes = Docente::listar('');
        $cursos = Curso::todos('nombre ASC');
        $asignaciones = [];
        if ($periodo) {
            $asignaciones = Database::select(
                'SELECT * FROM docentes_cursos WHERE periodo_id = ?', [(int)$periodo['id']]
            );
            $secciones = Seccion::listarConGrado();
        }

        // Mapa rápido: "cursoId-seccionId" -> docenteId
        $mapa = [];
        foreach ($asignaciones as $a) {
            $mapa[$a['curso_id'] . '-' . $a['seccion_id']] = $a['docente_id'];
        }

        $this->vista('cursos/asignaciones', [
            'page_title' => 'Asignación docente - curso',
            'active' => 'academico',
            'periodos' => Periodo::listarOrdenados(),
            'periodo_id' => $periodo ? (int)$periodo['id'] : 0,
            'periodo' => $periodo,
            'secciones' => $secciones,
            'docentes' => $docentes,
            'cursos' => $cursos,
            'mapa' => $mapa,
        ]);
    }

    public function guardarAsignaciones(): void
    {
        $periodoId = (int)($_POST['periodo_id'] ?? 0);
        if (!$periodoId) { flash_set('error', 'Seleccione un periodo académico.'); redirect('cursos/asignaciones'); }
        Database::delete('docentes_cursos', 'periodo_id = ?', [$periodoId]);
        $asignaciones = $_POST['asignacion'] ?? [];
        $contador = 0;
        foreach ($asignaciones as $cursoId => $porSeccion) {
            foreach ($porSeccion as $seccionId => $docenteId) {
                if ((int)$docenteId > 0) {
                    Database::insert('docentes_cursos', [
                        'docente_id' => (int)$docenteId,
                        'curso_id' => (int)$cursoId,
                        'seccion_id' => (int)$seccionId,
                        'periodo_id' => $periodoId,
                    ]);
                    $contador++;
                }
            }
        }
        auditoria('Cursos', 'Asignación', "Asignaciones guardadas para periodo $periodoId ($contador registros)");
        flash_set('success', "✅ Se guardaron $contador asignaciones docente-curso.");
        redirect('cursos/asignaciones?periodo_id=' . $periodoId);
    }
}