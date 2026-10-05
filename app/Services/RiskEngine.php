<?php
/**
 * EduAlert JJP - app/Services/RiskEngine.php
 * MOTOR DE EVALUACIÓN ACADÉMICA Y ALERTAS TEMPRANAS
 *
 * Calcula el nivel de riesgo de cada estudiante usando parámetros
 * 100% configurables desde la tabla `parametros`.
 */

namespace App\Services;

use App\Core\Database;
use App\Models\Alerta;
use App\Models\AlertaMotivo;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Configuracion;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\NivelRiesgo;
use App\Models\Periodo;

class RiskEngine
{
    /** Parámetros del motor (cache) */
    private array $params = [];

    public function __construct()
    {
        $this->params = Configuracion::todos();
    }

    private function p(string $clave, $defecto)
    {
        return $this->params[$clave] ?? $defecto;
    }

    /**
     * Evalúa el riesgo de UN estudiante en un periodo.
     * Devuelve estructura completa con indicadores, puntos, nivel y motivos.
     */
    public function evaluar(int $estudianteId, int $periodoId): array
    {
        $aprobatorio = (float)$this->p('promedio_aprobatorio', 11);
        $riesgoBajoMin = (float)$this->p('riesgo_bajo_min', 14);
        $riesgoMedioMin = (float)$this->p('riesgo_medio_min', 11);

        // --- Indicadores ---
        $promedio = Calificacion::promedioEstudiantePeriodo($estudianteId, $periodoId);
        $desaprobados = Calificacion::desaprobadosEstudiantePeriodo($estudianteId, $periodoId, $aprobatorio);
        $asistencia = Asistencia::resumenEstudiantePeriodo($estudianteId, $periodoId);
        $asistenciaPct = (float)$asistencia['asistencia_pct'];

        // --- Tendencia (comparar con periodo anterior) ---
        $periodoAnterior = Periodo::anteriorAl($periodoId);
        $promedioAnterior = $periodoAnterior
            ? Calificacion::promedioEstudiantePeriodo($estudianteId, (int)$periodoAnterior['id'])
            : null;
        $umbral = (float)$this->p('tendencia_umbral', 0.5);
        $tendenciaDescendente = ($promedio !== null && $promedioAnterior !== null)
            && ($promedioAnterior - $promedio) >= $umbral;

        // --- Puntaje por rendimiento ---
        $puntosRendimiento = 0;
        $tipoRendimiento = 'bajo'; // nombre del riesgo de rendimiento
        if ($promedio !== null) {
            if ($promedio >= $riesgoBajoMin) {
                $puntosRendimiento = 0; $tipoRendimiento = 'bajo';
            } elseif ($promedio >= $riesgoMedioMin) {
                $puntosRendimiento = (int)$this->p('puntos_riesgo_medio', 20); $tipoRendimiento = 'medio';
            } else {
                $puntosRendimiento = (int)$this->p('puntos_riesgo_alto', 40); $tipoRendimiento = 'alto';
            }
        }

        // --- Puntaje por asistencia ---
        $asistNormalMin = (float)$this->p('asistencia_normal_min', 90);
        $asistPrevMin = (float)$this->p('asistencia_preventiva_min', 80);
        $puntosAsistencia = 0;
        $tipoAsistencia = 'normal';
        if ($asistencia['total'] > 0) {
            if ($asistenciaPct >= $asistNormalMin) {
                $puntosAsistencia = 0; $tipoAsistencia = 'normal';
            } elseif ($asistenciaPct >= $asistPrevMin) {
                $puntosAsistencia = (int)$this->p('puntos_asistencia_media', 20); $tipoAsistencia = 'media';
            } else {
                $puntosAsistencia = (int)$this->p('puntos_asistencia_alta', 30); $tipoAsistencia = 'alta';
            }
        }

        // --- Puntaje por cursos desaprobados ---
        $puntosDesaprobados = match (true) {
            $desaprobados >= 3 => (int)$this->p('puntos_curso_3', 30),
            $desaprobados == 2 => (int)$this->p('puntos_curso_2', 20),
            $desaprobados == 1 => (int)$this->p('puntos_curso_1', 10),
            default => 0,
        };

        // --- Puntaje por tendencia ---
        $puntosTendencia = $tendenciaDescendente ? (int)$this->p('puntos_tendencia', 20) : 0;

        $puntajeTotal = $puntosRendimiento + $puntosAsistencia + $puntosDesaprobados + $puntosTendencia;

        // --- Nivel según puntaje (table niveles_riesgo configurable) ---
        $nivel = NivelRiesgo::nivelPorPuntaje($puntajeTotal);

        // --- Motivos legibles ---
        $motivos = [];
        if ($promedio !== null) {
            if ($promedio < $riesgoMedioMin) {
                $motivos[] = ['tipo' => 'promedio_bajo', 'detalle' => "Promedio general: " . number_format($promedio, 1, '.', '') . " (menor a $riesgoMedioMin)"];
            } else {
                $motivos[] = ['tipo' => 'promedio', 'detalle' => "Promedio general: " . number_format($promedio, 1, '.', '')];
            }
        }
        if ($desaprobados >= 1) {
            $motivos[] = ['tipo' => 'cursos_desaprobados', 'detalle' => ($desaprobados >= 3 ? '3 o más' : "$desaprobados") . ' curso(s) desaprobado(s)'];
        }
        if ($asistencia['total'] > 0 && $asistenciaPct < $asistNormalMin) {
            $nivelAsistencia = $asistenciaPct < $asistPrevMin ? 'baja' : 'preventiva';
            $motivos[] = ['tipo' => 'asistencia', 'detalle' => 'Asistencia: ' . number_format($asistenciaPct, 1, '.', '') . '% (' . $nivelAsistencia . ')'];
        }
        if ($asistencia['tardanzas'] > 0 && (int)$this->p('tardanzas_alerta', 4) > 0 && $asistencia['tardanzas'] >= (int)$this->p('tardanzas_alerta', 4)) {
            $motivos[] = ['tipo' => 'tardanzas', 'detalle' => $asistencia['tardanzas'] . ' tardanza(s) registrada(s)'];
        }
        if ($asistencia['faltas'] > 0 && (int)$this->p('dias_alerta_inasistencia', 3) > 0 && $asistencia['faltas'] >= (int)$this->p('dias_alerta_inasistencia', 3)) {
            $motivos[] = ['tipo' => 'inasistencia', 'detalle' => $asistencia['faltas'] . ' inasistencia(s) sin justificar'];
        }
        if ($tendenciaDescendente) {
            $motivos[] = ['tipo' => 'tendencia', 'detalle' => 'Tendencia descendente de calificaciones (' . number_format((float)$promedioAnterior, 1, '.', '') . ' → ' . number_format((float)$promedio, 1, '.', '') . ')'];
        }
        if (count($motivos) === 0) {
            $motivos[] = ['tipo' => 'sin_riesgo', 'detalle' => 'Desempeño académico dentro de los parámetros esperados'];
        }

        return [
            'estudiante_id' => $estudianteId,
            'periodo_id' => $periodoId,
            'promedio' => $promedio,
            'promedio_anterior' => $promedioAnterior,
            'desaprobados' => $desaprobados,
            'asistencia' => $asistencia,
            'tendencia_descendente' => $tendenciaDescendente,
            'puntos_rendimiento' => $puntosRendimiento,
            'puntos_asistencia' => $puntosAsistencia,
            'puntos_desaprobados' => $puntosDesaprobados,
            'puntos_tendencia' => $puntosTendencia,
            'puntaje_total' => $puntajeTotal,
            'nivel' => $nivel ? ['id' => (int)$nivel['id'], 'nombre' => $nivel['nombre'], 'color' => $nivel['color']] : null,
            'motivos' => $motivos,
            'evaluado' => $promedio !== null || $asistencia['total'] > 0,
        ];
    }

    /**
     * Genera/actualiza alertas para TODOS los estudiantes matriculados
     * en el periodo activo (o uno indicado). Devuelve resumen.
     */
    public function generarAlertas(?int $periodoId = null): array
    {
        $periodo = $periodoId ? Periodo::buscar($periodoId) : Periodo::activo();
        if (!$periodo) {
            return ['ok' => false, 'mensaje' => 'No existe un periodo académico activo. Actívelo desde Gestión Académica.'];
        }
        $periodoId = (int)$periodo['id'];

        $matriculas = Matricula::donde('periodo_id = ? AND estado = 1', [$periodoId]);
        $contador = [
            'evaluados' => 0, 'sin_datos' => 0,
            'alertas_creadas' => 0, 'alertas_actualizadas' => 0, 'alertas_sin_riesgo' => 0,
        ];
        $notificadas = 0;

        foreach ($matriculas as $m) {
            $resultado = $this->evaluar((int)$m['estudiante_id'], $periodoId);

            // Actualizar datos de riesgo en el estudiante (para dashboard y listados)
            Estudiante::actualizar((int)$m['estudiante_id'], [
                'ultimo_promedio' => $resultado['promedio'],
                'ultimo_asistencia_pct' => $resultado['asistencia']['asistencia_pct'],
                'ultimo_desaprobados' => $resultado['desaprobados'],
                'nivel_riesgo_id' => $resultado['nivel']['id'] ?? null,
                'puntaje_riesgo' => $resultado['puntaje_total'],
            ]);
            $contador['evaluados']++;

            if (!$resultado['evaluado']) { $contador['sin_datos']++; continue; }

            // ¿Tiene alerta abierta en el periodo?
            $alertaExistente = Alerta::abiertaDeEstudiantePeriodo((int)$m['estudiante_id'], $periodoId);

            if ($resultado['nivel'] && in_array($resultado['nivel']['nombre'], ['Medio', 'Alto'], true)) {
                $descripcion = $this->descripcionAlerta($resultado, $m);
                if ($alertaExistente) {
                    // Actualizar nivel/puntaje
                    Database::update('alertas', [
                        'nivel_riesgo_id' => $resultado['nivel']['id'],
                        'puntaje' => $resultado['puntaje_total'],
                        'descripcion' => $descripcion,
                    ], 'id = ?', [1 => $alertaExistente['id']]);
                    AlertaMotivo::reemplazar((int)$alertaExistente['id'], $resultado['motivos']);
                    $contador['alertas_actualizadas']++;
                } else {
                    $alertaId = Alerta::crear([
                        'estudiante_id' => (int)$m['estudiante_id'],
                        'periodo_id' => $periodoId,
                        'nivel_riesgo_id' => $resultado['nivel']['id'],
                        'puntaje' => $resultado['puntaje_total'],
                        'descripcion' => $descripcion,
                        'estado' => 'Nueva',
                    ]);
                    AlertaMotivo::reemplazar($alertaId, $resultado['motivos']);
                    $contador['alertas_creadas']++;
                    $notificadas += $this->notificarNuevaAlerta($alertaId, $resultado, $m, $periodo);
                }
            } else {
                $contador['alertas_sin_riesgo']++;
            }
        }

        return ['ok' => true, 'periodo' => $periodo['nombre'] . ' ' . $periodo['anio'], 'resultado' => $contador, 'notificaciones' => $notificadas];
    }

    /** Descripción legible de la alerta (formato ⚠️) */
    private function descripcionAlerta(array $r, array $m): string
    {
        $lineas = [];
        $lineas[] = "Estudiante: " . trim(($m['nombres'] ?? '') . ' ' . ($m['apellidos'] ?? ''));
        $lineas[] = "Nivel: Riesgo " . ($r['nivel']['nombre'] ?? '-');
        $lineas[] = "Motivos:";
        foreach ($r['motivos'] as $mot) {
            $lineas[] = "• " . $mot['detalle'];
        }
        return implode("\n", $lineas);
    }

    private function notificarNuevaAlerta(int $alertaId, array $r, array $m, array $periodo): int
    {
        $roles = ['admin', 'director', 'tutor', 'docente'];
        $titulo = 'Nueva alerta académica: ' . trim(($m['nombres'] ?? '') . ' ' . ($m['apellidos'] ?? ''));
        $mensaje = 'Se ha generado una alerta de riesgo ' . ($r['nivel']['nombre'] ?? '') . ' (puntaje ' . $r['puntaje_total'] . ') para ' .
                   trim(($m['nombres'] ?? '') . ' ' . ($m['apellidos'] ?? '')) . ".\nPeriodo: " . $periodo['nombre'] . ' ' . $periodo['anio'];
        return \App\Services\Notificador::crearPorRoles($roles, $titulo, $mensaje, 'alerta', url('alertas/ver/' . $alertaId));
    }
}