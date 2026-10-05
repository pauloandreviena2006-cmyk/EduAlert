<?php
/**
 * EduAlert JJP - app/Controllers/InstalacionController.php
 * Asistente de instalación: crea BD, esquema, datos base y datos demo
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\RiskEngine;
use App\Services\SeederDemo;

class InstalacionController extends Controller
{
    public function index(): void
    {
        if ($this->instalado()) {
            redirect('login');
        }
        $pasos = [
            'base' => $this->tablasExisten(),
            'demo' => $this->hayDatosDemo(),
        ];
        $this->vista('instalacion/index', [
            'page_title' => 'Instalación del sistema',
            'pasos' => $pasos,
        ], 'guest');
    }

    public function crearBaseDatos(): void
    {
        if ($this->instalado()) redirect('login');

        try {
            // Conectar directamente al servidor MySQL (sin seleccionar BD, aún no existe)
            $pdo = Database::pdoServidor();
            // Leer y ejecutar schema + seed base
            $this->ejecutarArchivoSql(BASE_PATH . '/database/schema.sql', $pdo);
            $this->ejecutarArchivoSql(BASE_PATH . '/database/seed_base.sql', $pdo);
            // Desconectar para que la próxima conexión seleccione la BD recién creada
            Database::desconectar();
            file_put_contents(STORAGE_PATH . '/.instalado', date('Y-m-d H:i:s'));
            flash_set('success', 'Base de datos creada correctamente. Estructura y datos base (roles, usuarios, grados, periodos, cursos) instalados.');
            auditoria_registrar_instalacion();
        } catch (\Throwable $e) {
            log_error('Error en instalación: ' . $e->getMessage());
            flash_set('error', 'No se pudo crear la base de datos: ' . $e->getMessage());
        }
        redirect('instalacion');
    }

    public function cargarDatosDemo(): void
    {
        if (!$this->tablasExisten()) {
            flash_set('error', 'Primero cree la base de datos y el esquema.');
            redirect('instalacion');
        }
        try {
            $seeder = new SeederDemo();
            $resultado = $seeder->ejecutar(200);
            flash_set('success', $resultado['mensaje']);
            auditoria_registrar_instalacion('Datos demo');
        } catch (\Throwable $e) {
            log_error('Error al cargar datos demo: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            flash_set('error', 'Ocurrió un error al generar datos demo: ' . (APP_DEBUG ? $e->getMessage() : 'Verifique el log del sistema.'));
        }
        redirect('instalacion');
    }

    private function instalado(): bool
    {
        return file_exists(STORAGE_PATH . '/.instalado');
    }

    private function tablasExisten(): bool
    {
        try {
            $fila = Database::conexion()->query('SHOW TABLES LIKE "users"');
            return $fila->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function hayDatosDemo(): bool
    {
        try {
            $total = (int)(Database::selectOne('SELECT COUNT(*) AS t FROM estudiantes')['t'] ?? 0);
            return $total > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Ejecuta un archivo SQL separando los statements por ';' fuera de strings */
    private function ejecutarArchivoSql(string $archivo, ?\PDO $pdo = null): void
    {
        $sql = file_get_contents($archivo);
        if ($sql === false) throw new \RuntimeException("No se pudo leer $archivo");
        // Quitar comentarios de línea
        $sql = preg_replace('/--[^\n]*/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $pdo = $pdo ?? Database::conexion();
        $statement = '';
        $len = strlen($sql);
        $enComilla = null;
        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            if ($enComilla !== null) {
                $statement .= $char;
                if ($char === $enComilla) {
                    // Chequear comillas escapadas
                    if (($sql[$i - 1] ?? '') === '\\') {
                        continue;
                    }
                    $enComilla = null;
                }
                continue;
            }
            if ($char === "'" || $char === '"') {
                $enComilla = $char;
                $statement .= $char;
                continue;
            }
            if ($char === ';') {
                $statement = trim($statement);
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
                $statement = '';
                continue;
            }
            $statement .= $char;
        }
        $statement = trim($statement);
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

/** Helper interno de instalación */
if (!function_exists('auditoria_registrar_instalacion')) {
    function auditoria_registrar_instalacion(string $tipo = 'Sistema instalado'): void
    {
        @file_put_contents(STORAGE_PATH . '/logs/instalacion.log', '[' . date('Y-m-d H:i:s') . '] ' . $tipo . PHP_EOL, FILE_APPEND);
    }
}