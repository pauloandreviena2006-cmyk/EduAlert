<?php
/**
 * EduAlert JJP - app/Services/SeederDemo.php
 * Genera datos de DEMOSTRACIÓN realistas para la sustentación:
 * 200 estudiantes peruanos, docentes, calificaciones, asistencias.
 * Al final ejecuta el motor de alertas para generar alertas reales.
 *
 * IMPORTANTE: Estos son datos DEMO (superpoblados con el sello DEMO).
 * Se pueden eliminar y los parámetros permiten reiniciar el sistema.
 */

namespace App\Services;

use App\Core\Database;
use App\Models\Alerta;
use App\Models\Calificacion;
use App\Models\Configuracion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use App\Models\Seguimiento;
use App\Models\Usuario;

class SeederDemo
{
    private array $nombresM = [];
    private array $nombresF = [];
    private array $apellidos = [];
    private array $docentes = [];

    public function __construct()
    {
        $this->nombresM = ['Juan','Carlos','Pedro','Luis','Miguel','Jorge','Raúl','Óscar','Manuel','José','Fernando','Ricardo','Diego','Andrés','César','Marco','Hugo','Iván','Óscar','Dante','Bruno','Raul','Pablo','Renzo','Álvaro','Eduardo','Frank','Gerson','Jhordan','Adrián'];
        $this->nombresF = ['María','Lucía','Carmen','Rosa','Ana','Milagros','Diana','Paola','Fiorella','Carla','Sandra','Noemí','Vanessa','Gabriela','Miriam','Elena','Roxana','Karla','Brenda','Valeria','Alessandra','Camila','Deyanira','Luana','Sofía','Xiomara','Flor','Zenobia','Yajaira','Cinthia'];
        $this->apellidos = ['García','Rodríguez','Pérez','González','Sánchez','Ramírez','Torres','Flores','Rivera','Vásquez','Castillo','Rojas','Vargas','Castro','Ramos','Mendoza','Aguilar','Salazar','Chávez','Huamán','Quispe','Mamani','Cordova','Paredes','Vela','Guevara','Rengifo','Pinedo','Tello','Sangama','Amasifuén','Chujandama','Pizango','Dela Cruz','Gómez','López','Díaz','Martínez','Cahuaza','Saavedra','Vásquez','Grandes','Pezo','Roncal','Arévalo','Sandoval'];
    }

    public function ejecutar(int $cantidadEstudiantes = 200): array
    {
        // La generación de demo hace muchas consultas: ampliar límites por seguridad
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        // Transacción global: insertar todo y confirmar al final (rápido y consistente; si falla, no queda data parcial)
        Database::begin();

        $resumen = ['estudiantes' => 0, 'docentes' => 0, 'matriculas' => 0, 'calificaciones' => 0, 'asistencias' => 0, 'seguimientos' => 0];

        // 1. Docentes demo (12) con usuario asociado al docente@edualert.test
        $resumen['docentes'] = $this->crearDocentes();

        // 2. Estudiantes con matrícula en TODOS los periodos
        $grados = Grado::todos('orden ASC');
        $secciones = Seccion::todos('id ASC');
        $periodos = Periodo::listarOrdenados(); // de más reciente a más antiguo
        $periodosOrdenCronologico = array_reverse($periodos);
        $cursos = Curso::todos('id ASC');
        $docentes = Docente::todos('id ASC');
        $this->docentes = $docentes;

        // Asignar docentes-cursos por grado (1 docente por curso)
        $this->asignarDocentesCursos($docentes, $cursos, $secciones, $periodos);

        for ($i = 1; $i <= $cantidadEstudiantes; $i++) {
            $sexo = ($i % 2 === 0) ? 'F' : 'M';
            $nombres = ($sexo === 'F')
                ? $this->nombresF[array_rand($this->nombresF)] . ' ' . $this->nombresF[array_rand($this->nombresF)]
                : $this->nombresM[array_rand($this->nombresM)] . ' ' . $this->nombresM[array_rand($this->nombresM)];
            $ape1 = $this->apellidos[array_rand($this->apellidos)];
            $ape2 = $this->apellidos[array_rand($this->apellidos)];
            $dni = $this->dniUnico();

            // Distribución de grados: 1° a 5°, 2 secciones por grado
            $grado = $grados[($i - 1) % count($grados)];
            $seccionesGrado = array_values(array_filter($secciones, fn($s) => (int)$s['grado_id'] === (int)$grado['id']));
            $seccion = $seccionesGrado[($i - 1) % count($seccionesGrado)];

            $anio = (int)setting_val('anio_lectivo', date('Y'));
            // Código consistente con Estudiante::proximoCodigo(): AÑO-NNNN
            $codigo = $anio . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);

            $perfilRiesgo = $this::perfilRiesgo($i);

            $estudianteId = Estudiante::crear([
                'codigo' => $codigo,
                'dni' => $dni,
                'nombres' => $nombres,
                'apellidos' => $ape1 . ' ' . $ape2,
                'fecha_nacimiento' => date('Y-m-d', strtotime('-' . (11 + ($grado['orden'] == 5 ? 4 : 0)) . ' years -' . mt_rand(1, 300) . ' days')),
                'sexo' => $sexo,
                'direccion' => 'Jr. ' . $this->apellidos[array_rand($this->apellidos)] . ' #' . mt_rand(100, 900) . ' - Tarapoto',
                'telefono' => '9' . mt_rand(10000000, 99999999),
                'estado' => 1,
            ]);
            $resumen['estudiantes']++;

            // Apoderado
            Database::insert('apoderados', [
                'estudiante_id' => $estudianteId,
                'nombres' => $sexo === 'F' ? $this->nombresM[array_rand($this->nombresM)] : $this->nombresF[array_rand($this->nombresF)],
                'apellidos' => $ape1 . ' ' . $ape2,
                'dni' => $this->dniUnico(),
                'telefono' => '9' . mt_rand(10000000, 99999999),
                'parentesco' => ['Padre', 'Madre', 'Tío(a)', 'Abuelo(a)'][array_rand([0, 1, 2, 3])],
                'ocupacion' => ['Comerciante', 'Agricultor', 'Docente', 'Mecánico', 'Ama de casa', 'Mototaxista'][array_rand([0, 1, 2, 3, 4, 5])],
            ]);

            // Matrícula en cada periodo cronológico (para ver evolución)
            foreach ($periodosOrdenCronologico as $pi => $periodo) {
                $matriculaId = Matricula::crear([
                    'estudiante_id' => $estudianteId,
                    'periodo_id' => (int)$periodo['id'],
                    'grado_id' => (int)$grado['id'],
                    'seccion_id' => (int)$seccion['id'],
                    'fecha' => $periodo['fecha_inicio'],
                    'estado' => 1,
                ]);
                $resumen['matriculas']++;

                // Calificaciones por curso
                $perfil = $perfilRiesgo['por_periodo'][$pi] ?? $perfilRiesgo['por_periodo'][count($perfilRiesgo['por_periodo']) - 1];
                foreach ($cursos as $curso) {
                    $nota = $this::notaSegunPerfil($perfil, $curso['id'], $grado['id'], $i);
                    $docenteId = $this->docenteParaCurso((int)$curso['id'], (int)$seccion['id'], (int)$periodo['id'], $docentes);
                    Calificacion::crear([
                        'matricula_id' => $matriculaId,
                        'curso_id' => (int)$curso['id'],
                        'docente_id' => $docenteId,
                        'periodo_id' => (int)$periodo['id'],
                        'competencia' => 'Logros de aprendizaje',
                        'nota' => $nota,
                    ]);
                    $resumen['calificaciones']++;
                }

                // Asistencia: ~10 fechas de evaluación por periodo
                $fechas = $this::fechasPeriodo($periodo);
                $perfilAsistencia = $perfilRiesgo['asistencia'];
                foreach ($fechas as $fi => $fecha) {
                    $estado = 'Presente';
                    $rnd = mt_rand(1, 100);
                    if ($rnd <= $perfilAsistencia['falta_pct']) $estado = 'Falta';
                    elseif ($rnd <= $perfilAsistencia['falta_pct'] + $perfilAsistencia['tardanza_pct']) $estado = 'Tardanza';
                    elseif ($rnd <= $perfilAsistencia['falta_pct'] + $perfilAsistencia['tardanza_pct'] + $perfilAsistencia['justificado_pct']) $estado = 'Justificado';
                    Database::insert('asistencias', [
                        'matricula_id' => $matriculaId,
                        'estudiante_id' => $estudianteId,
                        'fecha' => $fecha,
                        'estado' => $estado,
                        'registrado_por' => $this->userAuxiliarId(),
                    ]);
                    $resumen['asistencias']++;
                }
            }
        }

        // 3. Ejecutar motor de alertas para el último periodo (activo)
        $config = new RiskEngine();
        $generacion = $config->generarAlertas();

        // 4. Seguimientos de ejemplo sobre algunas alertas
        $resumen['seguimientos'] = $this->crearSeguimientosDemo();

        // Marcar sistema como instalado con datos demo
        Configuracion::set('demo_data', '1');

        Database::commit();

        return [
            'ok' => true,
            'resumen' => $resumen,
            'alertas' => $generacion['resultado'] ?? [],
            'mensaje' => 'Datos de demostración generados correctamente. ' .
                $generacion['resultado']['alertas_creadas'] . ' alertas creadas automáticamente.',
        ];
    }

    /** Perfil de riesgo pseudo-aleatorio pero determinista por índice */
    private static function perfilRiesgo(int $i): array
    {
        // Distribución: ~60% bajo, ~25% medio, ~15% alto
        $r = ($i * 37) % 100;
        if ($r < 60) {
            $tipo = 'bajo';
            $base = 12.5;   // promedio base 12-17
            $baja = 0.10;   // 10% cursos bajos
            $falta = 4; $tardanza = 8; $justificado = 3; // asistencia 85-95%
        } elseif ($r < 85) {
            $tipo = 'medio';
            $base = 10.5;
            $baja = 0.35;
            $falta = 10; $tardanza = 15; $justificado = 5;
        } else {
            $tipo = 'alto';
            $base = 7.5;
            $baja = 0.70;
            $falta = 22; $tardanza = 14; $justificado = 8;
        }
        // Tendencia: los de riesgo alto tienden a descender en el tiempo
        $porPeriodo = [];
        $baseInicio = $base + ($tipo === 'alto' ? 3.2 : ($tipo === 'medio' ? 1.0 : 0.6));
        for ($p = 0; $p < 4; $p++) {
            $factor = match ($tipo) {
                'bajo' => 1.0 + ($p * 0.015),
                'medio' => 1.0 - ($p * 0.05),
                'alto' => 1.0 - ($p * 0.16),
            };
            $prom = max(4.0, min(19.5, $baseInicio * $factor));
            // Se amplifica para el periodo activo en riesgo alto
            if ($p === 3 && $tipo === 'alto') $prom = $base;
            if ($p === 3 && $tipo === 'medio') $prom = $base - 0.2;
            $porPeriodo[] = ['promedio' => $prom, 'baja_pct' => $baja + ($p * 0.03)];
        }
        return [
            'tipo' => $tipo,
            'promedio_base' => $base,
            'por_periodo' => $porPeriodo,
            'asistencia' => [
                'falta_pct' => $falta, 'tardanza_pct' => $tardanza, 'justificado_pct' => $justificado,
            ],
        ];
    }

    private static function notaSegunPerfil(array $perfil, int $cursoId, int $gradoId, int $i): float
    {
        $promedio = $perfil['promedio'];
        // Variación por curso (materias básicas más difíciles)
        $extra = match ($cursoId % 5) {
            0 => 1.2, 1 => -0.8, 2 => -1.4, 3 => 0.4, default => 0.2,
        };
        $nota = $promedio + $extra + (mt_rand(0, 100) / 100 * 2 - 1);
        // Probabilidad de nota baja extra según perfil
        if (mt_rand(1, 100) <= ($perfil['baja_pct'] * 100)) {
            $nota -= 3.5;
        }
        $nota = round(max(2.0, min(20.0, $nota)), 1);
        // Marco de puntuación peruana: 0-20
        return $nota;
    }

    private static function fechasPeriodo(array $periodo): array
    {
        $inicio = strtotime($periodo['fecha_inicio']);
        $fin = strtotime($periodo['fecha_fin']);
        $fechas = [];
        $dias = [];
        for ($t = $inicio; $t <= $fin; $t += 86400) {
            $dow = (int)date('N', $t);
            if ($dow <= 5) $dias[] = date('Y-m-d', $t);
        }
        // Tomar ~10 días distribuidos
        $total = count($dias);
        if ($total === 0) return [date('Y-m-d')];
        $pasos = max(1, intdiv($total, 10));
        for ($k = 0; $k < min(10, $total); $k++) {
            $fechas[] = $dias[min($total - 1, $k * $pasos)];
        }
        return $fechas;
    }

    private function dniUnico(): string
    {
        do {
            $dni = str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            $existe = (int)(Database::selectOne('SELECT COUNT(*) AS c FROM estudiantes WHERE dni = ?', [$dni])['c'] ?? 0);
        } while ($existe > 0);
        return $dni;
    }

    private function crearDocentes(): int
    {
        $especialidades = ['Comunicación', 'Matemática', 'Ciencia y Tecnología', 'Ciencias Sociales', 'Inglés', 'Educación Física', 'Arte y Cultura', 'Educación Religiosa', 'Tutoría'];
        $docenteUser = Usuario::porEmail('docente@edualert.test');
        $total = 0;
        foreach ($especialidades as $idx => $esp) {
            $nombres = $this->nombresM[array_rand($this->nombresM)] . ' ' . $this->nombresM[array_rand($this->nombresM)];
            $user_id = null;
            if ($idx === 1 && $docenteUser) $user_id = (int)$docenteUser['id']; // matemática -> docente@edualert.test
            $docenteId = Docente::crear([
                'user_id' => $user_id,
                'codigo' => 'DOC-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT),
                'dni' => $this->dniDocenteUnico(),
                'nombres' => $nombres,
                'apellidos' => $this->apellidos[array_rand($this->apellidos)] . ' ' . $this->apellidos[array_rand($this->apellidos)],
                'especialidad' => $esp,
                'estado' => 1,
            ]);
            $total++;
        }
        return $total;
    }

    private function dniDocenteUnico(): string
    {
        do {
            $dni = str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            $existe = (int)(Database::selectOne('SELECT COUNT(*) AS c FROM docentes WHERE dni = ?', [$dni])['c'] ?? 0);
        } while ($existe > 0);
        return $dni;
    }

    /** Asigna a cada docente cursos por periodo/sección/grado */
    private function asignarDocentesCursos(array $docentes, array $cursos, array $secciones, array $periodos): void
    {
        foreach ($periodos as $periodo) {
            foreach ($secciones as $seccion) {
                foreach ($cursos as $ci => $curso) {
                    $docente = $docentes[$ci % count($docentes)];
                    try {
                        Database::insert('docentes_cursos', [
                            'docente_id' => (int)$docente['id'],
                            'curso_id' => (int)$curso['id'],
                            'seccion_id' => (int)$seccion['id'],
                            'periodo_id' => (int)$periodo['id'],
                        ]);
                    } catch (\Throwable $e) { /* duplicado: ignorar */ }
                }
            }
        }
    }

    private function docenteParaCurso(int $cursoId, int $seccionId, int $periodoId, array $docentes): ?int
    {
        $fila = Database::selectOne(
            'SELECT docente_id FROM docentes_cursos WHERE curso_id = ? AND seccion_id = ? AND periodo_id = ? LIMIT 1',
            [$cursoId, $seccionId, $periodoId]
        );
        if ($fila) return (int)$fila['docente_id'];
        return $docentes ? (int)$docentes[0]['id'] : null;
    }

    private function userAuxiliarId(): ?int
    {
        static $id = null;
        if ($id === null) {
            $u = Usuario::porEmail('auxiliar@edualert.test');
            $id = $u ? (int)$u['id'] : null;
        }
        return $id;
    }

    /** Crea seguimientos demo sobre alertas abiertas */
    private function crearSeguimientosDemo(): int
    {
        $alertas = Database::select(
            "SELECT al.*, u.id AS user_director_id FROM alertas al
             INNER JOIN users u ON u.email = 'director@edualert.test'
             WHERE al.estado IN ('Nueva','En revisión','En seguimiento')
             ORDER BY al.id DESC LIMIT 25"
        );
        $tipos = ['Entrevista con el estudiante', 'Comunicación con apoderado', 'Orientación académica', 'Reforzamiento pedagógico', 'Compromiso de mejora', 'Derivación a psicología'];
        $total = 0;
        foreach ($alertas as $alerta) {
            $total++;
            Seguimiento::crear([
                'alerta_id' => (int)$alerta['id'],
                'fecha' => date('Y-m-d'),
                'responsable_id' => (int)$alerta['user_director_id'],
                'tipo_intervencion' => $tipos[array_rand($tipos)],
                'descripcion' => 'Primera intervención registrada durante la demostración inicial del sistema. Se identificaron los factores de riesgo y se coordina con el estudiante y su apoderado.',
                'acuerdo' => 'El estudiante se compromete a presentar sus tareas y participar activamente en las sesiones de reforzamiento.',
                'compromiso' => 'Cumplir con el plan de mejora y asistir puntualmente a clases durante las próximas semanas.',
                'proxima_revision' => date('Y-m-d', strtotime('+2 weeks')),
                'estado' => 'En seguimiento',
            ]);
            // Actualizar alerta a "En seguimiento"
            Database::update('alertas', [
                'estado' => 'En seguimiento',
                'responsable_id' => (int)$alerta['user_director_id'],
            ], 'id = ?', [1 => $alerta['id']]);
        }
        return $total;
    }
}