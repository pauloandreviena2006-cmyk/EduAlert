<?php
/**
 * EduAlert JJP - app/Controllers/PeriodosController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Periodo;

class PeriodosController extends Controller
{
    public function index(): void
    {
        $this->vista('periodos/index', [
            'page_title' => 'Periodos académicos',
            'active' => 'academico',
            'periodos' => Periodo::listarOrdenados(),
        ]);
    }

    public function crear(): void
    {
        $this->vista('periodos/form', ['page_title' => 'Nuevo periodo', 'active' => 'academico', 'periodo' => null, 'modo' => 'crear']);
    }

    public function guardar(): void
    {
        $datos = $this->datos();
        $v = new Validator($datos, [
            'nombre' => 'obligatorio|min:3',
            'anio' => 'obligatorio|entero|between:2000,2100',
            'fecha_inicio' => 'obligatorio|fecha',
            'fecha_fin' => 'obligatorio|fecha',
        ]);
        if ($v->falla()) {
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('periodos/crear');
        }
        try {
            $id = Periodo::crear([
                'nombre' => $datos['nombre'],
                'anio' => (int)$datos['anio'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'activo' => (int)Periodo::contar() === 0 ? 1 : 0,
                'estado' => 1,
            ]);
            auditoria('Periodos', 'Registro', 'Periodo creado: ' . $datos['nombre'] . ' ' . $datos['anio']);
            flash_set('success', '✅ Periodo académico registrado correctamente.');
            redirect('periodos');
        } catch (\Throwable $e) {
            flash_set('error', 'Ese periodo ya está registrado.');
            redirect('periodos/crear');
        }
    }

    public function editar(string $id): void
    {
        $periodo = Periodo::buscar((int)$id);
        if (!$periodo) redirect('periodos');
        $this->vista('periodos/form', ['page_title' => 'Editar periodo', 'active' => 'academico', 'periodo' => $periodo, 'modo' => 'editar']);
    }

    public function actualizar(string $id): void
    {
        $datos = $this->datos();
        Periodo::actualizar((int)$id, [
            'nombre' => $datos['nombre'],
            'anio' => (int)$datos['anio'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
        ]);
        auditoria('Periodos', 'Actualización', 'Periodo actualizado ID ' . $id);
        flash_set('success', '✅ Periodo actualizado correctamente.');
        redirect('periodos');
    }

    public function activar(string $id): void
    {
        $periodo = Periodo::buscar((int)$id);
        if (!$periodo) { flash_set('error', 'Periodo no encontrado.'); redirect('periodos'); }
        Periodo::activar((int)$id);
        auditoria('Periodos', 'Activación', 'Periodo activado: ' . $periodo['nombre'] . ' ' . $periodo['anio']);
        flash_set('success', '✅ Periodo activado: ' . $periodo['nombre'] . ' ' . $periodo['anio']);
        redirect('periodos');
    }

    private function datos(): array
    {
        return [
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'anio' => (string)($_POST['anio'] ?? date('Y')),
            'fecha_inicio' => (string)($_POST['fecha_inicio'] ?? ''),
            'fecha_fin' => (string)($_POST['fecha_fin'] ?? ''),
        ];
    }
}