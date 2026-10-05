<?php
/**
 * EduAlert JJP - app/Models/AlertaMotivo.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class AlertaMotivo extends Model
{
    protected string $tabla = 'alertas_motivos';

    public static function reemplazar(int $alertaId, array $motivos): void
    {
        Database::delete('alertas_motivos', 'alerta_id = ?', [$alertaId]);
        foreach ($motivos as $m) {
            Database::insert('alertas_motivos', [
                'alerta_id' => $alertaId,
                'tipo' => $m['tipo'] ?? 'general',
                'detalle' => $m['detalle'] ?? '',
            ]);
        }
    }
}