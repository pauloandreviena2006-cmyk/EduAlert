<?php
/**
 * EduAlert JJP - app/Controllers/MatriculasController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;

class MatriculasController extends Controller
{
    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $periodoId = (int)($_GET['periodo_id'] ?? 0);

        $this->vista('matriculas/index', [
            'page_title' => 'Matrículas',
            'active' => 'academico',
            'matriculas' => Matricula::listar($busqueda, $gradoId, $seccionId, $periodoId ?: null),
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'q' => $busqueda, 'grado_id' => $gradoId, 'seccion_id' => $seccionId, 'periodo_id' => $periodoId,
        ]);
    }

    public function crear(): void
    {
        $this->vista('matriculas/form', [
            'page_title' => 'Nueva matrícula',
            'active' => 'academico',
            'estudiantes' => Estudiante::donde('estado = 1 ORDER BY apellidos ASC'),
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'matricula' => null,
            'modo' => 'crear',
        ]);
    }

    public function guardar(): void
    {
        $datos = [
            'estudiante_id' => (string)($_POST['estudiante_id'] ?? '0'),
            'periodo_id' => (string)($_POST['periodo_id'] ?? '0'),
            'grado_id' => (string)($_POST['grado_id'] ?? '0'),
            'seccion_id' => (string)($_POST['seccion_id'] ?? '0'),
            'fecha' => (string)($_POST['fecha'] ?? date('Y-m-d')),
        ];
        $v = new Validator($datos, [
            'estudiante_id' => 'obligatorio|existe:estudiantes,id',
            'periodo_id' => 'obligatorio|existe:periodos,id',
            'grado_id' => 'obligatorio|existe:grados,id',
            'seccion_id' => 'obligatorio|existe:secciones,id',
            'fecha' => 'obligatorio|fecha',
        ]);
        if ($v->falla()) {
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('matriculas/crear');
        }
        $duplicado = Matricula::primero('estudiante_id = ? AND periodo_id = ?', [$datos['estudiante_id'], $datos['periodo_id']]);
        if ($duplicado) {
            flash_set('error', 'El estudiante ya está matriculado en ese periodo.');
            redirect('matriculas/crear');
        }
        Matricula::crear([
            'estudiante_id' => (int)$datos['estudiante_id'],
            'periodo_id' => (int)$datos['periodo_id'],
            'grado_id' => (int)$datos['grado_id'],
            'seccion_id' => (int)$datos['seccion_id'],
            'fecha' => $datos['fecha'],
            'estado' => 1,
        ]);
        auditoria('Matrículas', 'Registro', 'Matrícula registrada para estudiante ID ' . $datos['estudiante_id'] . ' en periodo ' . $datos['periodo_id']);
        flash_set('success', '✅ Matrícula registrada correctamente.');
        redirect('matriculas');
    }

    public function editar(string $id): void
    {
        $matricula = Matricula::buscar((int)$id);
        if (!$matricula) redirect('matriculas');
        $this->vista('matriculas/form', [
            'page_title' => 'Editar matrícula',
            'active' => 'academico',
            'estudiantes' => Estudiante::donde('estado = 1 ORDER BY apellidos ASC'),
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'matricula' => $matricula,
            'modo' => 'editar',
        ]);
    }

    public function actualizar(string $id): void
    {
        $id = (int)$id;
        $matricula = Matricula::buscar($id);
        if (!$matricula) redirect('matriculas');
        Matricula::actualizar($id, [
            'estudiante_id' => (int)($_POST['estudiante_id'] ?? 0),
            'periodo_id' => (int)($_POST['periodo_id'] ?? 0),
            'grado_id' => (int)($_POST['grado_id'] ?? 0),
            'seccion_id' => (int)($_POST['seccion_id'] ?? 0),
            'fecha' => (string)($_POST['fecha'] ?? date('Y-m-d')),
        ]);
        auditoria('Matrículas', 'Actualización', 'Matrícula actualizada ID ' . $id);
        flash_set('success', '✅ Matrícula actualizada correctamente.');
        redirect('matriculas');
    }

    public function cambiarEstado(string $id): void
    {
        $matricula = Matricula::buscar((int)$id);
        if (!$matricula) redirect('matriculas');
        $nuevo = (int)$matricula['estado'] === 1 ? 0 : 1;
        Matricula::actualizar((int)$id, ['estado' => $nuevo]);
        auditoria('Matrículas', $nuevo === 0 ? 'Retiro' : 'Reactivación', 'Matrícula ' . ($nuevo === 0 ? 'retirada' : 'reactivada') . ' ID ' . $id);
        flash_set('success', $nuevo === 0 ? 'Matrícula retirada.' : 'Matrícula reactivada.');
        redirect('matriculas');
    }
}