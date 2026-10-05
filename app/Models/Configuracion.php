<?php
/**
 * EduAlert JJP - app/Models/Configuracion.php
 * Parámetros configurables del sistema (tabla parametros)
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Configuracion extends Model
{
    protected string $tabla = 'parametros';
    private static ?array $cache = null;

    /** Parámetros como array clave => valor (ignora el orden del padre) */
    public static function todos(string $orden = 'id DESC'): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Database::select('SELECT * FROM parametros') as $p) {
                self::$cache[$p['clave']] = $p['valor'];
            }
        }
        return self::$cache;
    }

    public static function get(string $clave, $defecto = null)
    {
        $params = self::todos();
        return array_key_exists($clave, $params) ? $params[$clave] : $defecto;
    }

    public static function set(string $clave, string $valor): void
    {
        $fila = Database::selectOne('SELECT id FROM parametros WHERE clave = ?', [$clave]);
        if ($fila) {
            Database::update('parametros', ['valor' => $valor], 'id = ?', [1 => $fila['id']]);
        } else {
            Database::insert('parametros', ['clave' => $clave, 'valor' => $valor]);
        }
        self::$cache = null;
    }

    public static function actualizarMasivo(array $datos): void
    {
        foreach ($datos as $clave => $valor) {
            if (in_array($clave, ['_token'], true)) continue;
            self::set($clave, (string)$valor);
        }
    }
}