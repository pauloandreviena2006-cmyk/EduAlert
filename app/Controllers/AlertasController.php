<?php
/**
 * EduAlert JJP - app/Controllers/AlertasController.php
 * Alertas académicas: consulta, detalle, generación automática,
 * cambio de estado, asignación de responsable, exportación
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Alerta;
use App\Models\Grado;
use App\Models\NivelRiesgo;
use App\Models\Periodo;
use App\Models\Seguimiento;
use App\Models\Usuario;
use App\Services\Exportador;
use App\Services\Notificador;
use App\Services\RiskEngine;

class AlertasController extends Controller
{
    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $nivelId = (int)($_GET['nivel_id'] ?? 0);
        $estado = (string)($_GET['estado'] ?? '');
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $periodoId = (int)($_GET['periodo_id'] ?? 0);

        $this->vista('alertas/index', [
            'page_title' => 'Alertas académicas',
            'active' => 'alertas',
            'alertas' => Alerta::listar($busqueda, $nivelId ?: null, $estado, $gradoId, $periodoId ?: null),
            'niveles' => NivelRiesgo::todos('min_puntaje ASC'),
            'grados' => Grado::todos('orden ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'estados' => Alerta::ESTADOS,
            'q' => $busqueda, 'nivel_id' => $nivelId, 'estado' => $estado, 'grado_id' => $gradoId, 'periodo_id' => $periodoId,
        ]);
    }

    public function ver(string $id): void
    {
        $alerta = Alerta::detalle((int)$id);
        if (!$alerta) { flash_set('error', 'Alerta no encontrada.'); redirect('alertas'); }
        $motivos = Alerta::motivos((int)$id);
        $seguimientos = Seguimiento::deAlerta((int)$id);
        $usuariosGestion = Usuario::listarConRol('', 0);
        // Promedio por periodo del estudiante
        $promedios = Database::select(
            'SELECT p.nombre, p.anio, ROUND(AVG(c.nota),2) AS prom
             FROM calificaciones c INNER JOIN periodos p ON p.id = c.periodo_id
             INNER JOIN matriculas m ON m.id = c.matricula_id
             WHERE m.estudiante_id = ? GROUP BY p.id, p.nombre, p.anio ORDER BY p.id',
            [(int)$alerta['estudiante_id']]
        );

        $this->vista('alertas/show', [
            'page_title' => 'Detalle de alerta',
            'active' => 'alertas',
            'alerta' => $alerta,
            'motivos' => $motivos,
            'seguimientos' => $seguimientos,
            'usuarios' => $usuariosGestion,
            'promedios' => $promedios,
        ]);
    }

    /** Genera/actualiza alertas automáticamente con el motor de riesgo */
    public function generar(): void
    {
        $motor = new RiskEngine();
        $resultado = $motor->generarAlertas();
        if ($resultado['ok']) {
            $r = $resultado['resultado'];
            flash_set('success', '⏰ Motor de alertas ejecutado para el periodo ' . $resultado['periodo'] .
                " | Evaluados: {$r['evaluados']} | Alertas creadas: {$r['alertas_creadas']} | Actualizadas: {$r['alertas_actualizadas']} | Sin riesgo: {$r['alertas_sin_riesgo']}");
            auditoria('Alertas', 'Generación', 'Alertas generadas: ' . json_encode($r, JSON_UNESCAPED_UNICODE));
        } else {
            flash_set('error', $resultado['mensaje']);
        }
        redirect('alertas');
    }

    public function cambiarEstado(string $id): void
    {
        $alerta = Alerta::buscar((int)$id);
        if (!$alerta) { flash_set('error', 'Alerta no encontrada.'); redirect('alertas'); }
        $nuevoEstado = (string)($_POST['estado'] ?? '');
        if (!in_array($nuevoEstado, Alerta::ESTADOS, true)) {
            flash_set('error', 'Estado no válido.');
            redirect('alertas/ver/' . $id);
        }
        Alerta::actualizar((int)$id, ['estado' => $nuevoEstado]);
        // Notificar al responsable
        $responsableId = (int)($alerta['responsable_id'] ?? 0);
        if ($responsableId > 0) {
            $est = Alerta::detalle((int)$id);
            Notificador::crearParaUsuario($responsableId, 'Estado de alerta actualizado',
                'La alerta de ' . $est['nombres'] . ' ' . $est['apellidos'] . ' cambió a: ' . $nuevoEstado, 'alerta', url('alertas/ver/' . $id));
        }
        auditoria('Alertas', 'Estado', "Alerta #$id cambió a estado: $nuevoEstado");
        flash_set('success', '✅ Estado de la alerta actualizado a: ' . $nuevoEstado);
        redirect('alertas/ver/' . $id);
    }

    public function asignar(string $id): void
    {
        $alerta = Alerta::buscar((int)$id);
        if (!$alerta) { flash_set('error', 'Alerta no encontrada.'); redirect('alertas'); }
        $responsableId = (int)($_POST['responsable_id'] ?? 0);
        if (!$responsableId) {
            flash_set('error', 'Seleccione un responsable válido.');
            redirect('alertas/ver/' . $id);
        }
        Alerta::actualizar((int)$id, ['responsable_id' => $responsableId, 'estado' => 'En revisión']);
        $responsable = Usuario::buscar($responsableId);
        $est = Alerta::detalle((int)$id);
        Notificador::crearParaUsuario($responsableId, 'Alerta asignada',
            'Se le asignó la alerta del estudiante ' . $est['nombres'] . ' ' . $est['apellidos'] . '.', 'alerta', url('alertas/ver/' . $id));
        auditoria('Alertas', 'Asignación', "Alerta #$id asignada al usuario $responsableId");
        flash_set('success', '✅ Responsable asignado correctamente.');
        redirect('alertas/ver/' . $id);
    }

    public function exportar(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $nivelId = (int)($_GET['nivel_id'] ?? 0);
        $estado = (string)($_GET['estado'] ?? '');
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $periodoId = (int)($_GET['periodo_id'] ?? 0);
        $descargar = (string)($_GET['formato'] ?? 'csv');

        $headers = ['codigo' => 'Código', 'apellidos' => 'Estudiante', 'grado_nombre' => 'Grado', 'seccion_nombre' => 'Sección', 'riesgo_nombre' => 'Nivel Riesgo', 'puntaje' => 'Puntaje', 'estado' => 'Estado', 'fecha_generacion' => 'Fecha', 'responsable' => 'Responsable', 'total_seguimientos' => 'Seguimientos'];
        $filas = [];
        foreach (Alerta::listar($busqueda, $nivelId ?: null, $estado, $gradoId, $periodoId ?: null, 5000) as $a) {
            $filas[] = [
                'codigo' => $a['codigo'],
                'apellidos' => $a['nombres'] . ' ' . $a['apellidos'],
                'grado_nombre' => $a['grado_nombre'] ?? '',
                'seccion_nombre' => $a['seccion_nombre'] ?? '',
                'riesgo_nombre' => $a['riesgo_nombre'],
                'puntaje' => $a['puntaje'],
                'estado' => $a['estado'],
                'fecha_generacion' => f_fecha_hora($a['fecha_generacion']),
                'responsable' => trim(($a['resp_nombres'] ?? '') . ' ' . ($a['resp_apellidos'] ?? '')),
                'total_seguimientos' => $a['total_seguimientos'],
            ];
        }
        $nombre = 'alertas_academicas';
        match ($descargar) {
            'xls' => Exportador::xls($nombre, array_keys($headers), $filas),
            'pdf' => Exportador::pdf('Reporte de Alertas Académicas', array_keys($headers), $filas, 'I.E. Juan Jiménez Pimentel - Tarapoto'),
            default => Exportador::csv($nombre, array_keys($headers), $filas),
        };
    }
}