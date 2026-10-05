<?php
/**
 * EduAlert JJP - app/Controllers/GradosController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Grado;

class GradosController extends Controller
{
    public function index(): void
    {
        $this->vista('grados/index', [
            'page_title' => 'Grados académicos',
            'active' => 'academico',
            'grados' => Grado::conSecciones(),
        ]);
    }

    public function crear(): void
    {
        $this->vista('grados/form', ['page_title' => 'Nuevo grado', 'active' => 'academico', 'grado' => null, 'modo' => 'crear']);
    }

    public function guardar(): void
    {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $nivel = trim((string)($_POST['nivel'] ?? 'Secundaria'));
        $orden = (int)($_POST['orden'] ?? 0);
        if ($nombre === '') { flash_set('error', 'El nombre del grado es obligatorio.'); redirect('grados/crear'); }
        try {
            Grado::crear(['nombre' => $nombre, 'nivel' => $nivel, 'orden' => $orden, 'estado' => 1]);
            auditoria('Grados', 'Registro', "Grado registrado: $nombre");
            flash_set('success', '✅ Grado registrado correctamente.');
            redirect('grados');
        } catch (\Throwable $e) {
            flash_set('error', 'Ese grado ya está registrado.');
            redirect('grados/crear');
        }
    }

    public function editar(string $id): void
    {
        $grado = Grado::buscar((int)$id);
        if (!$grado) redirect('grados');
        $this->vista('grados/form', ['page_title' => 'Editar grado', 'active' => 'academico', 'grado' => $grado, 'modo' => 'editar']);
    }

    public function actualizar(string $id): void
    {
        $id = (int)$id;
        Grado::actualizar($id, [
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'nivel' => trim((string)($_POST['nivel'] ?? 'Secundaria')),
            'orden' => (int)($_POST['orden'] ?? 0),
        ]);
        auditoria('Grados', 'Actualización', 'Grado actualizado ID ' . $id);
        flash_set('success', '✅ Grado actualizado correctamente.');
        redirect('grados');
    }
}