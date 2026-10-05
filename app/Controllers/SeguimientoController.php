<?php
/**
 * EduAlert JJP - app/Controllers/SeguimientoController.php
 * Seguimiento académico de alertas
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Alerta;
use App\Models\Seguimiento;

class SeguimientoController extends Controller
{
    public const TIPOS_INTERVENCION = [
        'Entrevista con el estudiante',
        'Comunicación con apoderado',
        'Orientación académica',
        'Reforzamiento pedagógico',
        'Compromiso de mejora',
        'Derivación a psicología',
        'Taller de hábitos de estudio',
    ];

    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $estado = (string)($_GET['estado'] ?? '');
        $this->vista('seguimiento/index', [
            'page_title' => 'Seguimiento académico',
            'active' => 'seguimiento',
            'seguimientos' => Seguimiento::listar($busqueda, $estado),
            'q' => $busqueda, 'estado' => $estado,
        ]);
    }

    public function crear(string $alertaId): void
    {
        $alerta = Alerta::detalle((int)$alertaId);
        if (!$alerta) { flash_set('error', 'Alerta no encontrada.'); redirect('alertas'); }
        $this->vista('seguimiento/form', [
            'page_title' => 'Registrar seguimiento',
            'active' => 'seguimiento',
            'alerta' => $alerta,
            'tipos' => self::TIPOS_INTERVENCION,
            'modo' => 'crear',
        ]);
    }

    public function guardar(string $alertaId): void
    {
        $alerta = Alerta::buscar((int)$alertaId);
        if (!$alerta) { flash_set('error', 'Alerta no encontrada.'); redirect('alertas'); }

        $fecha = (string)($_POST['fecha'] ?? date('Y-m-d'));
        $tipo = (string)($_POST['tipo_intervencion'] ?? '');
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $acuerdo = trim((string)($_POST['acuerdo'] ?? ''));
        $compromiso = trim((string)($_POST['compromiso'] ?? ''));
        $proxima = (string)($_POST['proxima_revision'] ?? '');
        $estado = (string)($_POST['estado'] ?? 'En seguimiento');

        if (!strtotime($fecha) || $tipo === '' || $descripcion === '') {
            flash_set('error', 'Complete fecha, tipo de intervención y descripción.');
            redirect('seguimiento/crear/' . $alertaId);
        }
        $estadoAlerta = in_array($estado, Alerta::ESTADOS, true) ? $estado : 'En seguimiento';

        $id = Seguimiento::crear([
            'alerta_id' => (int)$alertaId,
            'fecha' => $fecha,
            'responsable_id' => current_user_id(),
            'tipo_intervencion' => $tipo,
            'descripcion' => $descripcion,
            'acuerdo' => $acuerdo ?: null,
            'compromiso' => $compromiso ?: null,
            'proxima_revision' => $proxima ?: null,
            'estado' => 'En seguimiento',
        ]);

        // Actualizar alerta
        $datosAlerta = ['estado' => $estadoAlerta];
        if (!$alerta['responsable_id']) $datosAlerta['responsable_id'] = current_user_id();
        Alerta::actualizar((int)$alertaId, $datosAlerta);

        auditoria('Seguimiento', 'Registro', "Seguimiento #$id registrado para alerta #$alertaId ($tipo)");
        flash_set('success', '✅ Seguimiento registrado correctamente. La alerta quedó en estado: ' . $estadoAlerta);
        redirect('alertas/ver/' . $alertaId);
    }

    public function eliminar(string $id): void
    {
        $seguimiento = Seguimiento::buscar((int)$id);
        if (!$seguimiento) { flash_set('error', 'Seguimiento no encontrado.'); redirect('seguimiento'); }
        $alertaId = (int)$seguimiento['alerta_id'];
        Seguimiento::eliminar((int)$id);
        auditoria('Seguimiento', 'Eliminación', "Seguimiento #$id eliminado");
        flash_set('success', 'Seguimiento eliminado.');
        redirect('alertas/ver/' . $alertaId);
    }
}