<?php
/**
 * EduAlert JJP - app/Controllers/SeccionesController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Grado;
use App\Models\Seccion;

class SeccionesController extends Controller
{
    public function index(): void
    {
        $this->vista('secciones/index', [
            'page_title' => 'Secciones',
            'active' => 'academico',
            'secciones' => Seccion::listarConGrado(),
        ]);
    }

    public function crear(): void
    {
        $this->vista('secciones/form', [
            'page_title' => 'Nueva sección', 'active' => 'academico',
            'seccion' => null, 'grados' => Grado::todos('orden ASC'), 'modo' => 'crear',
        ]);
    }

    public function guardar(): void
    {
        $datos = [
            'grado_id' => (string)($_POST['grado_id'] ?? '0'),
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'turno' => trim((string)($_POST['turno'] ?? 'Mañana')),
        ];
        $v = new Validator($datos, ['grado_id' => 'obligatorio|entero', 'nombre' => 'obligatorio|min:1|max:20']);
        if ($v->falla()) { foreach ($v->errores() as $e) flash_set('error', $e); redirect('secciones/crear'); }
        try {
            Seccion::crear(['grado_id' => (int)$datos['grado_id'], 'nombre' => $datos['nombre'], 'turno' => $datos['turno'], 'estado' => 1]);
            auditoria('Secciones', 'Registro', 'Sección creada: ' . $datos['nombre']);
            flash_set('success', '✅ Sección registrada correctamente.');
            redirect('secciones');
        } catch (\Throwable $e) {
            flash_set('error', 'Esa sección ya existe en el grado seleccionado.');
            redirect('secciones/crear');
        }
    }

    public function editar(string $id): void
    {
        $seccion = Seccion::buscar((int)$id);
        if (!$seccion) redirect('secciones');
        $this->vista('secciones/form', [
            'page_title' => 'Editar sección', 'active' => 'academico',
            'seccion' => $seccion, 'grados' => Grado::todos('orden ASC'), 'modo' => 'editar',
        ]);
    }

    public function actualizar(string $id): void
    {
        $id = (int)$id;
        Seccion::actualizar($id, [
            'grado_id' => (int)($_POST['grado_id'] ?? 0),
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'turno' => trim((string)($_POST['turno'] ?? 'Mañana')),
        ]);
        auditoria('Secciones', 'Actualización', 'Sección actualizada ID ' . $id);
        flash_set('success', '✅ Sección actualizada correctamente.');
        redirect('secciones');
    }

    /** Respuesta JSON de secciones para un grado (selects en cascada) */
    public function porGradoJson(string $id): void
    {
        $this->json(Seccion::porGrado((int)$id));
    }
}