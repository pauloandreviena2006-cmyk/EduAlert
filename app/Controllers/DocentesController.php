<?php
/**
 * EduAlert JJP - app/Controllers/DocentesController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Docente;

class DocentesController extends Controller
{
    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $docentes = Docente::listar($busqueda);
        $this->vista('docentes/index', [
            'page_title' => 'Docentes',
            'active' => 'docentes',
            'docentes' => $docentes,
            'q' => $busqueda,
        ]);
    }

    public function crear(): void
    {
        $this->vista('docentes/form', ['page_title' => 'Nuevo docente', 'active' => 'docentes', 'docente' => null, 'modo' => 'crear']);
    }

    public function guardar(): void
    {
        $datos = $this->datos();
        $v = new Validator($datos, [
            'dni' => 'obligatorio|dni|unico:docentes,dni',
            'nombres' => 'obligatorio|min:3|max:80',
            'apellidos' => 'obligatorio|min:3|max:80',
        ]);
        if ($v->falla()) {
            $_SESSION['_old'] = $datos;
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('docentes/crear');
        }
        try {
            $id = Docente::crear([
                'codigo' => 'DOC-' . str_pad((string)(Docente::contar() + 1), 3, '0', STR_PAD_LEFT),
                'dni' => $datos['dni'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'email' => $datos['email'] ?: null,
                'telefono' => $datos['telefono'] ?: null,
                'especialidad' => $datos['especialidad'] ?: null,
                'estado' => 1,
            ]);
            auditoria('Docentes', 'Registro', 'Docente registrado: ' . $datos['nombres'] . ' ' . $datos['apellidos']);
            flash_set('success', '✅ Docente registrado correctamente.');
            redirect('docentes');
        } catch (\Throwable $e) {
            log_error('Error docente: ' . $e->getMessage());
            flash_set('error', 'No se pudo completar la operación. Verifique los datos.');
            redirect('docentes/crear');
        }
    }

    public function editar(string $id): void
    {
        $docente = Docente::buscar((int)$id);
        if (!$docente) { flash_set('error', 'Docente no encontrado.'); redirect('docentes'); }
        $this->vista('docentes/form', ['page_title' => 'Editar docente', 'active' => 'docentes', 'docente' => $docente, 'modo' => 'editar']);
    }

    public function actualizar(string $id): void
    {
        $id = (int)$id;
        $datos = $this->datos();
        $v = new Validator($datos, [
            'dni' => 'obligatorio|dni|unico:docentes,dni',
            'nombres' => 'obligatorio|min:3|max:80',
            'apellidos' => 'obligatorio|min:3|max:80',
        ]);
        if ($v->falla()) {
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('docentes/editar/' . $id);
        }
        Docente::actualizar($id, [
            'dni' => $datos['dni'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'email' => $datos['email'] ?: null,
            'telefono' => $datos['telefono'] ?: null,
            'especialidad' => $datos['especialidad'] ?: null,
        ]);
        auditoria('Docentes', 'Actualización', 'Docente actualizado: ' . $datos['nombres'] . ' ' . $datos['apellidos']);
        flash_set('success', '✅ Docente actualizado correctamente.');
        redirect('docentes');
    }

    public function cambiarEstado(string $id): void
    {
        $docente = Docente::buscar((int)$id);
        if (!$docente) { flash_set('error', 'Docente no encontrado.'); redirect('docentes'); }
        $nuevo = (int)$docente['estado'] === 1 ? 0 : 1;
        Docente::actualizar((int)$id, ['estado' => $nuevo]);
        auditoria('Docentes', $nuevo === 0 ? 'Desactivación' : 'Reactivación', 'Docente ' . ($nuevo === 0 ? 'desactivado' : 'reactivado') . ': ' . $docente['nombres'] . ' ' . $docente['apellidos']);
        flash_set('success', $nuevo === 0 ? 'Docente desactivado.' : 'Docente reactivado.');
        redirect('docentes');
    }

    private function datos(): array
    {
        return [
            'dni' => trim((string)($_POST['dni'] ?? '')),
            'nombres' => trim((string)($_POST['nombres'] ?? '')),
            'apellidos' => trim((string)($_POST['apellidos'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'telefono' => trim((string)($_POST['telefono'] ?? '')),
            'especialidad' => trim((string)($_POST['especialidad'] ?? '')),
        ];
    }
}