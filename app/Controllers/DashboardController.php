<?php
/**
 * EduAlert JJP - app/Controllers/DashboardController.php
 * Dashboard institucional con estadísticas y gráficos reales
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Alerta;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\NivelRiesgo;
use App\Models\Periodo;
use App\Models\Seguimiento;
use App\Services\RiskEngine;

class DashboardController extends Controller
{
    public function index(): void
    {
        $periodoActivo = Periodo::activo();
        $periodoId = $periodoActivo ? (int)$periodoActivo['id'] : null;

        $totalEstudiantes = (int)(Database::selectOne('SELECT COUNT(*) AS t FROM estudiantes WHERE estado = 1')['t'] ?? 0);
        $riesgos = NivelRiesgo::conteoEstudiantes();
        $nivelMap = [];
        foreach ($riesgos['niveles'] as $n) $nivelMap[$n['nombre']] = $n;

        $totalAlertas = Alerta::contar();
        $conteoEstados = Alerta::conteoPorEstado($periodoId);
        $alertasNuevas = $conteoEstados['Nueva'] ?? 0;

        $promedioGeneral = null;
        if ($periodoId) {
            $prom = Database::selectOne('SELECT ROUND(AVG(nota),2) AS t FROM calificaciones WHERE periodo_id = ?', [$periodoId]);
            $promedioGeneral = $prom ? (float)$prom['t'] : null;
        }
        $totalInasistencias = $periodoId ? (int)(Database::selectOne(
            'SELECT COUNT(*) AS t FROM asistencias a INNER JOIN matriculas m ON m.id = a.matricula_id WHERE m.periodo_id = ? AND a.estado = "Falta"', [$periodoId]
        )['t'] ?? 0) : 0;
        $seguimientos = Seguimiento::contar();

        $this->vista('dashboard/index', [
            'page_title' => 'Dashboard institucional',
            'active' => 'dashboard',
            'periodo' => $periodoActivo,
            'total_estudiantes' => $totalEstudiantes,
            'riesgos' => $riesgos,
            'nivel_map' => $nivelMap,
            'total_alertas' => $totalAlertas,
            'alertas_nuevas' => $alertasNuevas,
            'promedio_general' => $promedioGeneral,
            'total_inasistencias' => $totalInasistencias,
            'seguimientos' => $seguimientos,
        ]);
    }

    /** Datos JSON para los 5 gráficos del dashboard */
    public function datos(): void
    {
        $periodo = Periodo::activo();
        $periodoId = $periodo ? (int)$periodo['id'] : null;

        // Gráfico 1: Distribución por nivel de riesgo
        $g1 = [];
        foreach (NivelRiesgo::conteoEstudiantes()['niveles'] as $n) {
            $g1[] = ['label' => $n['nombre'], 'value' => (int)$n['total'], 'color' => $n['color']];
        }

        // Gráfico 2: Rendimiento por grado
        $g2 = [];
        if ($periodoId) {
            foreach (Calificacion::rendimientoPorGrado($periodoId) as $r) {
                $g2[] = ['label' => $r['grado'], 'value' => (float)$r['promedio']];
            }
        }

        // Gráfico 3: Alertas por periodo
        $g3 = [];
        foreach (Database::select('SELECT p.nombre, p.anio, COUNT(al.id) AS total FROM periodos p LEFT JOIN alertas al ON al.periodo_id = p.id GROUP BY p.id, p.nombre, p.anio ORDER BY p.id') as $r) {
            $g3[] = ['label' => $r['nombre'] . ' ' . $r['anio'], 'value' => (int)$r['total']];
        }

        // Gráfico 4: Evolución del rendimiento (promedio general por periodo)
        $g4 = [];
        foreach (Database::select('SELECT p.nombre, p.anio, ROUND(AVG(c.nota),2) AS prom FROM periodos p LEFT JOIN calificaciones c ON c.periodo_id = p.id GROUP BY p.id, p.nombre, p.anio ORDER BY p.id') as $r) {
            $g4[] = ['label' => $r['nombre'] . ' ' . $r['anio'], 'value' => $r['prom'] !== null ? (float)$r['prom'] : null];
        }

        // Gráfico 5: Inasistencias por grado
        $g5 = [];
        if ($periodoId) {
            foreach (Asistencia::resumenPorGrado($periodoId) as $r) {
                $g5[] = ['label' => $r['grado'], 'faltas' => (int)$r['faltas'], 'tardanzas' => (int)$r['tardanzas']];
            }
        }

        $this->json([
            'grafico1' => $g1,
            'grafico2' => $g2,
            'grafico3' => $g3,
            'grafico4' => $g4,
            'grafico5' => $g5,
            'periodo' => $periodo ? $periodo['nombre'] . ' ' . $periodo['anio'] : 'Sin periodo activo',
        ]);
    }

    /** Recalcula la evaluación de riesgo de todos los estudiantes (botón real) */
    public function actualizarEvaluacion(): void
    {
        $motor = new RiskEngine();
        $resultado = $motor->generarAlertas();
        if ($resultado['ok']) {
            $r = $resultado['resultado'];
            flash_set('success', 'Evaluación actualizada para el periodo ' . $resultado['periodo'] .
                ". Estudiantes evaluados: {$r['evaluados']} | Alertas creadas: {$r['alertas_creadas']} | Actualizadas: {$r['alertas_actualizadas']} | Sin riesgo: {$r['alertas_sin_riesgo']}");
            auditoria('Alertas', 'Evaluación', 'Evaluación de riesgo ejecutada: ' . json_encode($r, JSON_UNESCAPED_UNICODE));
        } else {
            flash_set('error', $resultado['mensaje']);
        }
        redirect('dashboard');
    }
}