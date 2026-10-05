<?php
/**
 * EduAlert JJP - app/Controllers/ReportesController.php
 * Reportes con datos reales + exportación PDF/Excel/CSV
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Periodo;
use App\Models\Seccion;
use App\Services\Exportador;

class ReportesController extends Controller
{
    private const TIPOS = [
        'estudiantes_riesgo' => 'Estudiantes en riesgo',
        'estudiantes_por_grado' => 'Estudiantes por grado',
        'rendimiento_academico' => 'Rendimiento académico',
        'inasistencias' => 'Inasistencias',
        'cursos_desaprobados' => 'Cursos desaprobados',
        'alertas' => 'Alertas',
        'seguimientos' => 'Seguimientos',
        'evolucion' => 'Evolución académica',
    ];

    public function index(): void
    {
        $tipo = (string)($_GET['tipo'] ?? 'estudiantes_riesgo');
        if (!isset(self::TIPOS[$tipo])) $tipo = 'estudiantes_riesgo';
        $periodoId = (int)($_GET['periodo_id'] ?? 0);
        $periodo = $periodoId ? Periodo::buscar($periodoId) : Periodo::activo();
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $gradoId = (int)($_GET['grado_id'] ?? 0);

        [$headers, $filas] = $this->datosReporte($tipo, $periodo, $seccionId, $gradoId);

        $this->vista('reportes/index', [
            'page_title' => 'Reportes',
            'active' => 'reportes',
            'tipos' => self::TIPOS,
            'tipo' => $tipo,
            'periodos' => Periodo::listarOrdenados(),
            'secciones' => Seccion::listarConGrado(),
            'periodo' => $periodo,
            'periodo_id' => $periodo ? (int)$periodo['id'] : 0,
            'seccion_id' => $seccionId,
            'grado_id' => $gradoId,
            'headers' => $headers,
            'filas' => $filas,
        ]);
    }

    public function exportar(): void
    {
        $tipo = (string)($_GET['tipo'] ?? 'estudiantes_riesgo');
        $formato = (string)($_GET['formato'] ?? 'csv');
        $periodoId = (int)($_GET['periodo_id'] ?? 0);
        $periodo = $periodoId ? Periodo::buscar($periodoId) : Periodo::activo();
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $gradoId = (int)($_GET['grado_id'] ?? 0);

        if (!isset(self::TIPOS[$tipo])) $tipo = 'estudiantes_riesgo';
        [$headers, $filas] = $this->datosReporte($tipo, $periodo, $seccionId, $gradoId);
        $subtitulo = 'I.E. Juan Jiménez Pimentel - Tarapoto';
        if ($periodo) $subtitulo .= ' | Periodo: ' . $periodo['nombre'] . ' ' . $periodo['anio'];

        match ($formato) {
            'xls' => Exportador::xls(self::TIPOS[$tipo], array_keys($headers), $filas),
            'pdf' => Exportador::pdf(self::TIPOS[$tipo], array_keys($headers), $filas, $subtitulo),
            default => Exportador::csv(self::TIPOS[$tipo], array_keys($headers), $filas),
        };
    }

    private function datosReporte(string $tipo, ?array $periodo, int $seccionId, int $gradoId): array
    {
        $periodoId = $periodo ? (int)$periodo['id'] : 0;

        switch ($tipo) {
            case 'estudiantes_riesgo':
                $headers = ['codigo' => 'Código', 'apellidos' => 'Apellidos', 'nombres' => 'Nombres', 'grado' => 'Grado', 'seccion' => 'Sección', 'promedio' => 'Promedio', 'asistencia' => 'Asistencia %', 'desaprobados' => 'Desaprobados', 'riesgo' => 'Nivel Riesgo', 'puntaje' => 'Puntaje'];
                $sql = 'SELECT e.codigo, e.apellidos, e.nombres, g.nombre AS grado, s.nombre AS seccion,
                               e.ultimo_promedio AS promedio, e.ultimo_asistencia_pct AS asistencia,
                               e.ultimo_desaprobados AS desaprobados, nr.nombre AS riesgo, e.puntaje_riesgo AS puntaje
                        FROM estudiantes e
                        LEFT JOIN matriculas m ON m.estudiante_id = e.id AND m.periodo_id = ?
                        LEFT JOIN grados g ON g.id = m.grado_id
                        LEFT JOIN secciones s ON s.id = m.seccion_id
                        LEFT JOIN niveles_riesgo nr ON nr.id = e.nivel_riesgo_id
                        WHERE nr.nombre IS NOT NULL AND nr.nombre <> "Bajo" ORDER BY e.puntaje_riesgo DESC LIMIT 500';
                $filas = array_map(fn($f) => $f + ['promedio' => $f['promedio'] !== null ? number_format((float)$f['promedio'], 1, '.', ' ') : '-', 'asistencia' => $f['asistencia'] !== null ? number_format((float)$f['asistencia'], 1, '.', ' ') : '-'], Database::select($sql, [$periodoId ?: 0]));
                break;

            case 'estudiantes_por_grado':
                $headers = ['grado' => 'Grado', 'seccion' => 'Sección', 'turno' => 'Turno', 'total' => 'Total estudiantes'];
                $sql = 'SELECT g.nombre AS grado, s.nombre AS seccion, s.turno, COUNT(DISTINCT m.estudiante_id) AS total
                        FROM secciones s
                        INNER JOIN grados g ON g.id = s.grado_id
                        LEFT JOIN matriculas m ON m.seccion_id = s.id AND m.periodo_id = ? AND m.estado = 1
                        GROUP BY g.id, s.id, g.nombre, s.nombre, s.turno, g.orden ORDER BY g.orden, s.nombre';
                $filas = Database::select($sql, [$periodoId ?: 0]);
                break;

            case 'rendimiento_academico':
                $headers = ['apellidos' => 'Apellidos', 'nombres' => 'Nombres', 'grado' => 'Grado', 'seccion' => 'Sección', 'promedio' => 'Promedio', 'estado' => 'Estado'];
                $sql = 'SELECT e.codigo, e.apellidos, e.nombres, g.nombre AS grado, s.nombre AS seccion,
                               ROUND(AVG(c.nota),2) AS promedio,
                               CASE WHEN nr.nombre IS NULL THEN "Sin riesgo" ELSE nr.nombre END AS estado
                        FROM calificaciones c
                        INNER JOIN matriculas m ON m.id = c.matricula_id
                        INNER JOIN estudiantes e ON e.id = m.estudiante_id
                        INNER JOIN grados g ON g.id = m.grado_id
                        INNER JOIN secciones s ON s.id = m.seccion_id
                        LEFT JOIN niveles_riesgo nr ON nr.id = e.nivel_riesgo_id
                        WHERE c.periodo_id = ?';
                $params = [$periodoId ?: 0];
                if ($seccionId > 0) { $sql .= ' AND m.seccion_id = ?'; $params[] = $seccionId; }
                if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
                $sql .= ' GROUP BY e.id, e.codigo, e.apellidos, e.nombres, g.nombre, s.nombre, nr.nombre ORDER BY promedio DESC LIMIT 500';
                $filas = array_map(fn($f) => $f + ['promedio' => number_format((float)$f['promedio'], 1, '.', ' ')], Database::select($sql, $params));
                break;

            case 'inasistencias':
                $headers = ['apellidos' => 'Apellidos', 'nombres' => 'Nombres', 'grado' => 'Grado', 'seccion' => 'Sección', 'faltas' => 'Faltas', 'tardanzas' => 'Tardanzas', 'justificados' => 'Justificados', 'asistencia' => 'Asistencia %'];
                $sql = 'SELECT e.codigo, e.apellidos, e.nombres, g.nombre AS grado, s.nombre AS seccion,
                               SUM(CASE WHEN a.estado="Falta" THEN 1 ELSE 0 END) AS faltas,
                               SUM(CASE WHEN a.estado="Tardanza" THEN 1 ELSE 0 END) AS tardanzas,
                               SUM(CASE WHEN a.estado="Justificado" THEN 1 ELSE 0 END) AS justificados,
                               ROUND(100 * (SUM(CASE WHEN a.estado IN ("Presente","Tardanza") THEN 1 ELSE 0 END) / COUNT(*)),1) AS asistencia
                        FROM asistencias a
                        INNER JOIN matriculas m ON m.id = a.matricula_id
                        INNER JOIN estudiantes e ON e.id = m.estudiante_id
                        INNER JOIN grados g ON g.id = m.grado_id
                        INNER JOIN secciones s ON s.id = m.seccion_id
                        WHERE m.periodo_id = ?';
                $params = [$periodoId ?: 0];
                if ($seccionId > 0) { $sql .= ' AND m.seccion_id = ?'; $params[] = $seccionId; }
                if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
                $sql .= ' GROUP BY e.id, e.codigo, e.apellidos, e.nombres, g.nombre, s.nombre ORDER BY faltas DESC LIMIT 500';
                $filas = Database::select($sql, $params);
                break;

            case 'cursos_desaprobados':
                $headers = ['apellidos' => 'Apellidos', 'nombres' => 'Nombres', 'grado' => 'Grado', 'seccion' => 'Sección', 'curso' => 'Curso', 'nota' => 'Nota'];
                $aprobatorio = (float)(\App\Models\Configuracion::get('promedio_aprobatorio', 11));
                $sql = 'SELECT e.codigo, e.apellidos, e.nombres, g.nombre AS grado, s.nombre AS seccion, cu.nombre AS curso, c.nota
                        FROM calificaciones c
                        INNER JOIN matriculas m ON m.id = c.matricula_id
                        INNER JOIN estudiantes e ON e.id = m.estudiante_id
                        INNER JOIN cursos cu ON cu.id = c.curso_id
                        INNER JOIN grados g ON g.id = m.grado_id
                        INNER JOIN secciones s ON s.id = m.seccion_id
                        WHERE c.periodo_id = ? AND c.nota < ? ORDER BY c.nota ASC LIMIT 500';
                $filas = array_map(fn($f) => $f + ['nota' => number_format((float)$f['nota'], 1, '.', ' ')], Database::select($sql, [$periodoId ?: 0, $aprobatorio]));
                break;

            case 'alertas':
                $headers = ['codigo' => 'Código', 'apellidos' => 'Apellidos', 'nombres' => 'Nombres', 'grado' => 'Grado', 'seccion' => 'Sección', 'riesgo' => 'Nivel', 'puntaje' => 'Puntaje', 'estado' => 'Estado', 'fecha' => 'Fecha'];
                $sql = 'SELECT e.codigo, e.apellidos, e.nombres, g.nombre AS grado, s.nombre AS seccion,
                               nr.nombre AS riesgo, al.puntaje, al.estado, al.fecha_generacion AS fecha
                        FROM alertas al
                        INNER JOIN estudiantes e ON e.id = al.estudiante_id
                        INNER JOIN niveles_riesgo nr ON nr.id = al.nivel_riesgo_id
                        LEFT JOIN matriculas m ON m.estudiante_id = al.estudiante_id AND m.periodo_id = al.periodo_id
                        LEFT JOIN grados g ON g.id = m.grado_id
                        LEFT JOIN secciones s ON s.id = m.seccion_id
                        WHERE al.periodo_id = ? ORDER BY al.puntaje DESC LIMIT 500';
                $filas = array_map(fn($f) => $f + ['fecha' => f_fecha_hora($f['fecha'])], Database::select($sql, [$periodoId ?: 0]));
                break;

            case 'seguimientos':
                $headers = ['apellidos' => 'Estudiante', 'tipo' => 'Tipo de intervención', 'fecha' => 'Fecha', 'descripcion' => 'Descripción', 'compromiso' => 'Compromiso', 'proxima' => 'Próxima revisión'];
                $sql = 'SELECT e.codigo, e.apellidos, e.nombres, seg.tipo_intervencion AS tipo, seg.fecha, seg.descripcion, seg.compromiso, seg.proxima_revision AS proxima
                        FROM seguimientos seg
                        INNER JOIN alertas al ON al.id = seg.alerta_id
                        INNER JOIN estudiantes e ON e.id = al.estudiante_id
                        INNER JOIN periodos p ON p.id = al.periodo_id
                        WHERE p.id = ? ORDER BY seg.fecha DESC LIMIT 500';
                $filas = array_map(fn($f) => $f + ['apellidos' => $f['apellidos'] . ' ' . $f['nombres'], 'fecha' => f_fecha($f['fecha']), 'proxima' => $f['proxima'] ? f_fecha((string)$f['proxima']) : '-'], Database::select($sql, [$periodoId ?: 0]));
                break;

            case 'evolucion':
                $headers = ['periodo' => 'Periodo', 'promedio' => 'Promedio general', 'aprobados' => 'Aprobados', 'desaprobados' => 'Desaprobados'];
                $sql = 'SELECT p.nombre AS periodo, p.anio,
                               ROUND(AVG(c.nota),2) AS promedio,
                               SUM(CASE WHEN c.nota >= ? THEN 1 ELSE 0 END) AS aprobados,
                               SUM(CASE WHEN c.nota < ? THEN 1 ELSE 0 END) AS desaprobados
                        FROM calificaciones c INNER JOIN periodos p ON p.id = c.periodo_id
                        GROUP BY p.id, p.nombre, p.anio ORDER BY p.id';
                $aprobatorio = (float)(\App\Models\Configuracion::get('promedio_aprobatorio', 11));
                $filas = array_map(fn($f) => $f + ['periodo' => $f['periodo'] . ' ' . $f['anio'], 'promedio' => number_format((float)$f['promedio'], 2, '.', ' ')], Database::select($sql, [$aprobatorio, $aprobatorio]));
                break;

            default:
                $headers = []; $filas = [];
        }

        return [$headers, $filas];
    }
}