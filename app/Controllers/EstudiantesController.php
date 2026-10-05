<?php
/**
 * EduAlert JJP - app/Controllers/EstudiantesController.php
 * CRUD completo de estudiantes + ficha académica + evolución
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Alerta;
use App\Models\Apoderado;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use App\Services\Exportador;

class EstudiantesController extends Controller
{
    public function index(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $estado = (string)($_GET['estado'] ?? '');
        $periodoId = (int)($_GET['periodo_id'] ?? 0);

        $limite = 50;
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $offset = ($pagina - 1) * $limite;

        $total = Estudiante::contarFiltros($busqueda, $gradoId, $seccionId, $periodoId ?: null, $estado);
        $estudiantes = Estudiante::listar($busqueda, $gradoId, $seccionId, $periodoId ?: null, $estado, $limite, $offset);
        $totalPaginas = max(1, (int)ceil($total / $limite));

        $this->vista('estudiantes/index', [
            'page_title' => 'Estudiantes',
            'active' => 'estudiantes',
            'estudiantes' => $estudiantes,
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'periodos' => Periodo::listarOrdenados(),
            'q' => $busqueda,
            'grado_id' => $gradoId,
            'seccion_id' => $seccionId,
            'estado' => $estado,
            'periodo_id' => $periodoId,
            'total' => $total,
            'pagina' => $pagina,
            'total_paginas' => $totalPaginas,
        ]);
    }

    public function crear(): void
    {
        $this->vista('estudiantes/form', [
            'page_title' => 'Nuevo estudiante',
            'active' => 'estudiantes',
            'estudiante' => null,
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'modo' => 'crear',
        ]);
    }

    public function guardar(): void
    {
        $datos = $this->datosFormulario();
        $v = new Validator($datos, [
            'dni' => 'obligatorio|dni|unico:estudiantes,dni',
            'nombres' => 'obligatorio|min:3|max:80',
            'apellidos' => 'obligatorio|min:3|max:80',
            'fecha_nacimiento' => 'fecha',
            'grado_id' => 'obligatorio|entero',
            'seccion_id' => 'obligatorio|entero',
        ]);
        if ($v->falla()) {
            $_SESSION['_old'] = $datos;
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('estudiantes/crear');
        }

        $codigo = Estudiante::proximoCodigo();
        Database::begin();
        try {
            $id = Estudiante::crear([
                'codigo' => $codigo,
                'dni' => $datos['dni'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?: null,
                'sexo' => $datos['sexo'] === 'F' ? 'F' : 'M',
                'direccion' => $datos['direccion'] ?: null,
                'telefono' => $datos['telefono'] ?: null,
                'email' => $datos['email'] ?: null,
                'estado' => 1,
            ]);
            Apoderado::reemplazar($id, $datos);
            // Matrícula automática en el periodo activo
            $periodo = Periodo::activo();
            if ($periodo) {
                Matricula::crear([
                    'estudiante_id' => $id,
                    'periodo_id' => (int)$periodo['id'],
                    'grado_id' => (int)$datos['grado_id'],
                    'seccion_id' => (int)$datos['seccion_id'],
                    'fecha' => date('Y-m-d'),
                    'estado' => 1,
                ]);
            }
            Database::commit();
            auditoria('Estudiantes', 'Registro', 'Estudiante registrado: ' . $datos['nombres'] . ' ' . $datos['apellidos'] . ' (DNI: ' . $datos['dni'] . ')');
            flash_set('success', '✅ Estudiante registrado correctamente. Código: ' . $codigo);
            redirect('estudiantes');
        } catch (\Throwable $e) {
            Database::rollback();
            log_error('Error creando estudiante: ' . $e->getMessage());
            flash_set('error', 'No se pudo completar la operación. Verifique los datos e inténtelo nuevamente.');
            redirect('estudiantes/crear');
        }
    }

    public function editar(string $id): void
    {
        $estudiante = Estudiante::ficha((int)$id);
        if (!$estudiante) { flash_set('error', 'Estudiante no encontrado.'); redirect('estudiantes'); }
        $matricula = Estudiante::matriculaActiva((int)$id);
        $this->vista('estudiantes/form', [
            'page_title' => 'Editar estudiante',
            'active' => 'estudiantes',
            'estudiante' => $estudiante,
            'matricula' => $matricula,
            'grados' => Grado::todos('orden ASC'),
            'secciones' => Seccion::todos('id ASC'),
            'modo' => 'editar',
        ]);
    }

    public function actualizar(string $id): void
    {
        $id = (int)$id;
        $estudiante = Estudiante::buscar($id);
        if (!$estudiante) { flash_set('error', 'Estudiante no encontrado.'); redirect('estudiantes'); }

        $datos = $this->datosFormulario();
        $v = new Validator($datos, [
            'dni' => 'obligatorio|dni|unico:estudiantes,dni',
            'nombres' => 'obligatorio|min:3|max:80',
            'apellidos' => 'obligatorio|min:3|max:80',
            'fecha_nacimiento' => 'fecha',
            'grado_id' => 'obligatorio|entero',
            'seccion_id' => 'obligatorio|entero',
        ]);
        if ($v->falla()) {
            $_SESSION['_old'] = $datos;
            foreach ($v->errores() as $e) flash_set('error', $e);
            redirect('estudiantes/editar/' . $id);
        }

        Database::begin();
        try {
            Estudiante::actualizar($id, [
                'dni' => $datos['dni'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?: null,
                'sexo' => $datos['sexo'] === 'F' ? 'F' : 'M',
                'direccion' => $datos['direccion'] ?: null,
                'telefono' => $datos['telefono'] ?: null,
                'email' => $datos['email'] ?: null,
            ]);
            Apoderado::reemplazar($id, $datos);
            // Actualizar matrícula activa
            $periodo = Periodo::activo();
            if ($periodo) {
                $mat = Matricula::primero('estudiante_id = ? AND periodo_id = ?', [$id, $periodo['id']]);
                if ($mat) {
                    Matricula::actualizar((int)$mat['id'], [
                        'grado_id' => (int)$datos['grado_id'],
                        'seccion_id' => (int)$datos['seccion_id'],
                    ]);
                }
            }
            Database::commit();
            auditoria('Estudiantes', 'Actualización', 'Estudiante actualizado: ' . $datos['nombres'] . ' ' . $datos['apellidos']);
            flash_set('success', '✅ Estudiante actualizado correctamente.');
            redirect('estudiantes');
        } catch (\Throwable $e) {
            Database::rollback();
            log_error('Error actualizando estudiante: ' . $e->getMessage());
            flash_set('error', 'No se pudo completar la operación. Verifique los datos e inténtelo nuevamente.');
            redirect('estudiantes/editar/' . $id);
        }
    }

    /** Ficha completa del estudiante */
    public function ver(string $id): void
    {
        $estudiante = Estudiante::ficha((int)$id);
        if (!$estudiante) { flash_set('error', 'Estudiante no encontrado.'); redirect('estudiantes'); }

        $matricula = Estudiante::matriculaActiva((int)$id);
        $periodos = Periodo::listarOrdenados();
        $periodoFiltro = (int)($_GET['periodo_id'] ?? 0);
        $calificaciones = Calificacion::deEstudiante((int)$id, $periodoFiltro ?: null);
        $promedios = Calificacion::promediosPorPeriodo((int)$id);
        $asistencia = Asistencia::deEstudiante((int)$id);
        $asistenciaResumen = [];
        foreach ($periodos as $p) {
            $asistenciaResumen[(int)$p['id']] = Asistencia::resumenEstudiantePeriodo((int)$id, (int)$p['id']);
        }
        $alertas = Alerta::donde('estudiante_id = ? ORDER BY fecha_generacion DESC', [(int)$id]);

        $this->vista('estudiantes/show', [
            'page_title' => 'Ficha del estudiante',
            'active' => 'estudiantes',
            'est' => $estudiante,
            'matricula' => $matricula,
            'periodos' => $periodos,
            'periodo_filtro' => $periodoFiltro,
            'calificaciones' => $calificaciones,
            'promedios' => $promedios,
            'asistencia' => $asistencia,
            'asistencia_resumen' => $asistenciaResumen,
            'alertas' => $alertas,
            'seguimientos' => $this->seguimientosDeEstudiante((int)$id),
        ]);
    }

    private function seguimientosDeEstudiante(int $estudianteId): array
    {
        return Database::select(
            'SELECT seg.*, al.id AS alerta_id, u.nombres AS resp_nombres, u.apellidos AS resp_apellidos
             FROM seguimientos seg
             INNER JOIN alertas al ON al.id = seg.alerta_id
             LEFT JOIN users u ON u.id = seg.responsable_id
             WHERE al.estudiante_id = ?
             ORDER BY seg.fecha DESC, seg.id DESC LIMIT 50', [$estudianteId]
        );
    }

    public function cambiarEstado(string $id): void
    {
        $estudiante = Estudiante::buscar((int)$id);
        if (!$estudiante) { flash_set('error', 'Estudiante no encontrado.'); redirect('estudiantes'); }
        $nuevo = (int)$estudiante['estado'] === 1 ? 0 : 1;
        Estudiante::actualizar((int)$id, ['estado' => $nuevo]);
        auditoria('Estudiantes', $nuevo === 0 ? 'Desactivación' : 'Reactivación', 'Estudiante ' . ($nuevo === 0 ? 'desactivado' : 'reactivado') . ': ' . $estudiante['nombres'] . ' ' . $estudiante['apellidos']);
        flash_set('success', $nuevo === 0 ? 'Estudiante desactivado.' : 'Estudiante reactivado.');
        redirect('estudiantes');
    }

    public function eliminar(string $id): void
    {
        $estudiante = Estudiante::buscar((int)$id);
        if (!$estudiante) { flash_set('error', 'Estudiante no encontrado.'); redirect('estudiantes'); }
        Database::delete('estudiantes', 'id = ?', [(int)$id]);
        auditoria('Estudiantes', 'Eliminación', 'Estudiante eliminado: ' . $estudiante['nombres'] . ' ' . $estudiante['apellidos']);
        flash_set('success', 'Estudiante eliminado definitivamente.');
        redirect('estudiantes');
    }

    /** Datos para gráficos de evolución del estudiante */
    public function evolucion(string $id): void
    {
        $id = (int)$id;
        $promedios = Calificacion::promediosPorPeriodo($id);
        $asistencia = [];
        foreach (Periodo::listarOrdenados() as $p) {
            $asistencia[] = [
                'label' => $p['nombre'] . ' ' . $p['anio'],
                'value' => Asistencia::resumenEstudiantePeriodo($id, (int)$p['id'])['asistencia_pct'],
            ];
        }
        $this->json([
            'promedios' => array_map(fn($x) => ['label' => $x['periodo_nombre'] . ' ' . $x['anio'], 'value' => (float)$x['promedio']], $promedios),
            'asistencia' => array_reverse($asistencia),
        ]);
    }

    public function exportar(): void
    {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $gradoId = (int)($_GET['grado_id'] ?? 0);
        $seccionId = (int)($_GET['seccion_id'] ?? 0);
        $estado = (string)($_GET['estado'] ?? '');
        $estudiantes = Estudiante::listar($busqueda, $gradoId, $seccionId, null, $estado, 5000, 0);

        $headers = ['codigo' => 'Código', 'dni' => 'DNI', 'nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'grado_nombre' => 'Grado', 'seccion_nombre' => 'Sección', 'sexo' => 'Sexo', 'fecha_nacimiento' => 'Fecha Nacimiento', 'telefono' => 'Teléfono', 'riesgo_nombre' => 'Nivel Riesgo', 'puntaje_riesgo' => 'Puntaje'];
        $filas = [];
        foreach ($estudiantes as $e) {
            $filas[] = [
                'codigo' => $e['codigo'], 'dni' => $e['dni'], 'nombres' => $e['nombres'], 'apellidos' => $e['apellidos'],
                'grado_nombre' => $e['grado_nombre'] ?? '', 'seccion_nombre' => $e['seccion_nombre'] ?? '',
                'sexo' => $e['sexo'], 'fecha_nacimiento' => $e['fecha_nacimiento'] ? f_fecha($e['fecha_nacimiento']) : '',
                'telefono' => $e['telefono'] ?? '', 'riesgo_nombre' => $e['riesgo_nombre'] ?? '', 'puntaje_riesgo' => $e['puntaje_riesgo'] ?? '',
            ];
        }
        $formato = $_GET['formato'] ?? 'csv';
        $nombre = 'estudiantes';
        match ($formato) {
            'xls' => Exportador::xls($nombre, array_keys($headers), $filas),
            'pdf' => Exportador::pdf('Listado de Estudiantes', array_keys($headers), $filas, 'I.E. Juan Jiménez Pimentel - Tarapoto'),
            default => Exportador::csv($nombre, array_keys($headers), $filas),
        };
    }

    private function datosFormulario(): array
    {
        return [
            'dni' => trim((string)($_POST['dni'] ?? '')),
            'nombres' => trim((string)($_POST['nombres'] ?? '')),
            'apellidos' => trim((string)($_POST['apellidos'] ?? '')),
            'fecha_nacimiento' => (string)($_POST['fecha_nacimiento'] ?? ''),
            'sexo' => (string)($_POST['sexo'] ?? 'M'),
            'direccion' => trim((string)($_POST['direccion'] ?? '')),
            'telefono' => trim((string)($_POST['telefono'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'grado_id' => (string)($_POST['grado_id'] ?? '0'),
            'seccion_id' => (string)($_POST['seccion_id'] ?? '0'),
            // Apoderado
            'apo_nombres' => trim((string)($_POST['apo_nombres'] ?? '')),
            'apo_apellidos' => trim((string)($_POST['apo_apellidos'] ?? '')),
            'apo_dni' => trim((string)($_POST['apo_dni'] ?? '')),
            'apo_telefono' => trim((string)($_POST['apo_telefono'] ?? '')),
            'apo_parentesco' => trim((string)($_POST['apo_parentesco'] ?? 'Padre')),
            'apo_ocupacion' => trim((string)($_POST['apo_ocupacion'] ?? '')),
        ];
    }
}