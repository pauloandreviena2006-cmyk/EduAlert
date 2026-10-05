<?php
/**
 * EduAlert JJP - app/Core/Controller.php
 * Controlador base con helpers de renderizado
 */

namespace App\Core;

abstract class Controller
{
    /** Renderiza una vista con layout por defecto 'main' */
    protected function vista(string $vista, array $datos = [], string $layout = 'main'): void
    {
        view($vista, $datos, $layout);
        exit;
    }

    /** Redirige a una ruta interna */
    protected function redirigir(string $ruta): void
    {
        redirect($ruta);
    }

    /** Respuesta JSON */
    protected function json($datos, int $codigo = 200): void
    {
        json_response($datos, $codigo);
    }

    /** Validación simple de campos obligatorios (usa Validator) */
    protected function validar(array $datos, array $reglas): array
    {
        $v = new Validator($datos, $reglas);
        if ($v->falla()) {
            $_SESSION['_old'] = $datos;
            foreach ($v->errores() as $error) {
                flash_set('error', $error);
            }
        }
        return $v;
    }
}