<?php
/**
 * EduAlert JJP - app/Core/Validator.php
 * Validación de formularios con reglas declarativas
 */

namespace App\Core;

class Validator
{
    private array $errores = [];
    private array $datos;

    public function __construct(array $datos, array $reglas)
    {
        $this->datos = $datos;
        foreach ($reglas as $campo => $reglasCampo) {
            $this->validarCampo($campo, $reglasCampo);
        }
    }

    private function validarCampo(string $campo, $reglas): void
    {
        $rolLegible = ucfirst(str_replace('_', ' ', $campo));
        $listado = is_array($reglas) ? $reglas : explode('|', $reglas);
        $valor = $this->datos[$campo] ?? null;

        foreach ($listado as $regla) {
            // regla:parametro
            $partes = explode(':', $regla, 2);
            $nombre = $partes[0];
            $parametro = $partes[1] ?? null;
            $mensaje = null;

            switch ($nombre) {
                case 'obligatorio':
                    if ($valor === null || trim((string)$valor) === '') {
                        $mensaje = "El campo $rolLegible es obligatorio.";
                    }
                    break;
                case 'email':
                    if ($valor !== null && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                        $mensaje = "El campo $rolLegible no tiene un formato de correo válido.";
                    }
                    break;
                case 'dni':
                    if ($valor !== null && $valor !== '' && !preg_match('/^\d{8}$/', (string)$valor)) {
                        $mensaje = "El campo $rolLegible debe contener 8 dígitos.";
                    }
                    break;
                case 'codigo':
                    if ($valor !== null && $valor !== '' && !preg_match('/^[A-Za-z0-9\-_\/]+$/', (string)$valor)) {
                        $mensaje = "El campo $rolLegible contiene caracteres no válidos.";
                    }
                    break;
                case 'numero':
                    if ($valor !== null && $valor !== '' && !is_numeric($valor)) {
                        $mensaje = "El campo $rolLegible debe ser un número.";
                    }
                    break;
                case 'entero':
                    if ($valor !== null && $valor !== '' && filter_var($valor, FILTER_VALIDATE_INT) === false) {
                        $mensaje = "El campo $rolLegible debe ser un número entero.";
                    }
                    break;
                case 'min':
                    if (is_numeric($valor) && (float)$valor < (float)$parametro) {
                        $mensaje = "El campo $rolLegible debe ser mayor o igual a $parametro.";
                    } elseif (!is_numeric($valor) && mb_strlen((string)$valor) < (int)$parametro) {
                        $mensaje = "El campo $rolLegible debe tener al menos $parametro caracteres.";
                    }
                    break;
                case 'max':
                    if (is_numeric($valor) && (float)$valor > (float)$parametro) {
                        $mensaje = "El campo $rolLegible debe ser menor o igual a $parametro.";
                    } elseif (!is_numeric($valor) && mb_strlen((string)$valor) > (int)$parametro) {
                        $mensaje = "El campo $rolLegible no debe superar $parametro caracteres.";
                    }
                    break;
                case 'between':
                    [$min, $max] = explode(',', $parametro);
                    if (is_numeric($valor) && ((float)$valor < (float)$min || (float)$valor > (float)$max)) {
                        $mensaje = "El campo $rolLegible debe estar entre $min y $max.";
                    }
                    break;
                case 'fecha':
                    if ($valor !== null && $valor !== '' && strtotime((string)$valor) === false) {
                        $mensaje = "El campo $rolLegible debe ser una fecha válida.";
                    }
                    break;
                case 'in':
                    $opciones = explode(',', (string)$parametro);
                    if ($valor !== null && $valor !== '' && !in_array($valor, $opciones)) {
                        $mensaje = "El campo $rolLegible contiene un valor no permitido.";
                    }
                    break;
                case 'unico':
                    [$tabla, $columna] = explode(',', (string)$parametro);
                    $ignorar = $this->datos['_id'] ?? null;
                    $sql = "SELECT COUNT(*) AS total FROM $tabla WHERE $columna = :v";
                    $params = [':v' => $valor];
                    if ($ignorar) {
                        $sql .= ' AND id <> :id';
                        $params[':id'] = $ignorar;
                    }
                    if ((int)(Database::selectOne($sql, $params)['total'] ?? 0) > 0) {
                        $mensaje = "El campo $rolLegible ya se encuentra registrado.";
                    }
                    break;
                case 'existe':
                    [$tabla, $columna] = explode(',', (string)$parametro);
                    if ($valor !== null && $valor !== '' &&
                        (int)(Database::selectOne("SELECT COUNT(*) AS total FROM $tabla WHERE $columna = :v", [':v' => $valor])['total'] ?? 0) === 0) {
                        $mensaje = "El valor de $rolLegible no existe en el sistema.";
                    }
                    break;
                case 'confirmado':
                    break; // se maneja con 'igual'
                case 'igual':
                    [$otro] = explode(',', (string)$parametro . ',');
                    $otroValor = $this->datos[$otro] ?? null;
                    if ($valor !== $otroValor) {
                        $mensaje = "El campo $rolLegible no coincide con " . ucfirst(str_replace('_', ' ', $otro)) . ".";
                    }
                    break;
            }

            if ($mensaje !== null) {
                $this->errores[] = $mensaje;
                break;
            }
        }
    }

    public function falla(): bool
    {
        return count($this->errores) > 0;
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function valido(): bool
    {
        return !$this->falla();
    }
}