<?php
/**
 * EduAlert JJP - app/Controllers/UsuariosController.php
 * Gestión de usuarios (administrador)
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Rol;
use App\Models\Usuario;

class UsuariosController extends Controller
{
    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $rolId = (int)($_GET['rol_id'] ?? 0);
        $this->vista('usuarios/index', [
            'page_title' => 'Usuarios del sistema',
            'active' => 'usuarios',
            'usuarios' => Usuario::listarConRol($busqueda, $rolId),
            'roles' => Rol::todos('id ASC'),
            'q' => $busqueda, 'rol_id' => $rolId,
        ]);
    }

    public function crear(): void
    {
        $this->vista('usuarios/form', [
            'page_title' => 'Nuevo usuario',
            'active' => 'usuarios',
            'usuario' => null,
            'roles' => Rol::todos('id ASC'),
            'modo' => 'crear',
        ]);
    }

    public function guardar(): void
    {
        $datos = $this->datos();
        $v = new Validator($datos, [
            'nombres' => 'obligatorio|min:3|max:80',
            'apellidos' => 'obligatorio|min:3|max:80',
            'email' => 'obligatorio|email|unico:users,email',
            'rol_id' => 'obligatorio|entero',
            'password' => 'obligatorio|min:8',
        ]);
        if ($v->falla()) {
            $_SESSION['_old'] = $datos;
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('usuarios/crear');
        }
        $id = Usuario::crear([
            'rol_id' => (int)$datos['rol_id'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'email' => strtolower($datos['email']),
            'password' => password_hash($datos['password'], PASSWORD_BCRYPT),
            'telefono' => $datos['telefono'] ?: null,
            'estado' => 1,
        ]);
        auditoria('Usuarios', 'Registro', "Usuario creado: {$datos['nombres']} {$datos['apellidos']} ({$datos['email']})");
        flash_set('success', '✅ Usuario registrado correctamente.');
        redirect('usuarios');
    }

    public function editar(string $id): void
    {
        $usuario = Usuario::conRol((int)$id);
        if (!$usuario) { flash_set('error', 'Usuario no encontrado.'); redirect('usuarios'); }
        $this->vista('usuarios/form', [
            'page_title' => 'Editar usuario',
            'active' => 'usuarios',
            'usuario' => $usuario,
            'roles' => Rol::todos('id ASC'),
            'modo' => 'editar',
        ]);
    }

    public function actualizar(string $id): void
    {
        $id = (int)$id;
        $usuario = Usuario::buscar($id);
        if (!$usuario) { flash_set('error', 'Usuario no encontrado.'); redirect('usuarios'); }
        $datos = $this->datos();
        $v = new Validator($datos, [
            'nombres' => 'obligatorio|min:3|max:80',
            'apellidos' => 'obligatorio|min:3|max:80',
            'email' => 'obligatorio|email|unico:users,email',
            'rol_id' => 'obligatorio|entero',
        ]);
        if ($v->falla()) {
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('usuarios/editar/' . $id);
        }
        $datosUpdate = [
            'rol_id' => (int)$datos['rol_id'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'email' => strtolower($datos['email']),
            'telefono' => $datos['telefono'] ?: null,
        ];
        if (!empty($datos['password'])) {
            if (strlen($datos['password']) < 8) {
                flash_set('error', 'La contraseña debe tener al menos 8 caracteres.');
                redirect('usuarios/editar/' . $id);
            }
            $datosUpdate['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }
        Usuario::actualizar($id, $datosUpdate);
        auditoria('Usuarios', 'Actualización', "Usuario actualizado: {$datos['nombres']} {$datos['apellidos']}");
        flash_set('success', '✅ Usuario actualizado correctamente.');
        redirect('usuarios');
    }

    public function cambiarEstado(string $id): void
    {
        $id = (int)$id;
        if ($id === (int)current_user_id()) {
            flash_set('error', 'No puede desactivar su propia cuenta.');
            redirect('usuarios');
        }
        $usuario = Usuario::buscar($id);
        if (!$usuario) { flash_set('error', 'Usuario no encontrado.'); redirect('usuarios'); }
        $nuevo = (int)$usuario['estado'] === 1 ? 0 : 1;
        Usuario::actualizar($id, ['estado' => $nuevo]);
        auditoria('Usuarios', $nuevo === 0 ? 'Desactivación' : 'Reactivación', "Usuario {} '" . ($nuevo === 0 ? 'desactivado' : 'reactivado') . "': " . $usuario['email']);
        flash_set('success', $nuevo === 0 ? 'Usuario desactivado.' : 'Usuario reactivado.');
        redirect('usuarios');
    }

    public function eliminar(string $id): void
    {
        $id = (int)$id;
        if ($id === (int)current_user_id()) {
            flash_set('error', 'No puede eliminar su propia cuenta.');
            redirect('usuarios');
        }
        $usuario = Usuario::buscar($id);
        if (!$usuario) { flash_set('error', 'Usuario no encontrado.'); redirect('usuarios'); }
        Usuario::eliminar($id);
        auditoria('Usuarios', 'Eliminación', "Usuario eliminado: {$usuario['email']}");
        flash_set('success', 'Usuario eliminado.');
        redirect('usuarios');
    }

    private function datos(): array
    {
        return [
            'nombres' => trim((string)($_POST['nombres'] ?? '')),
            'apellidos' => trim((string)($_POST['apellidos'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'rol_id' => (string)($_POST['rol_id'] ?? '0'),
            'password' => (string)($_POST['password'] ?? ''),
            'telefono' => trim((string)($_POST['telefono'] ?? '')),
        ];
    }
}