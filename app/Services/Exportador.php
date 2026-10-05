<?php
/**
 * EduAlert JJP - app/Services/Exportador.php
 * Exportación de reportes: CSV, Excel (.xls) y PDF (TCPDF)
 */

namespace App\Services;

class Exportador
{
    /**
     * Exporta a CSV
     */
    public static function csv(string $nombre, array $headers, array $filas): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::nombreSeguro($nombre) . '.csv"');
        $salida = fopen('php://output', 'w');
        fwrite($salida, "\xEF\xBB\xBF"); // BOM para Excel
        fputcsv($salida, $headers, ';');
        foreach ($filas as $fila) {
            $linea = [];
            foreach ($headers as $h) {
                $linea[] = $fila[$h] ?? $fila[strtolower($h)] ?? '';
            }
            fputcsv($salida, $linea, ';');
        }
        fclose($salida);
        exit;
    }

    /**
     * Exporta a Excel (.xls) usando HTML con cabecera de Excel
     */
    public static function xls(string $nombre, array $headers, array $filas): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::nombreSeguro($nombre) . '.xls"');
        echo "<html><head><meta charset='utf-8'></head><body>
              <table border='1'><thead><tr style='background:#1e3a5f;color:#fff'>";
        foreach ($headers as $h) {
            echo '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($filas as $fila) {
            echo '<tr>';
            foreach ($headers as $h) {
                $v = $fila[$h] ?? $fila[strtolower($h)] ?? '';
                echo '<td>' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }

    /**
     * Exporta a PDF usando TCPDF (biblioteca local en storage/vendor/tcpdf)
     */
    public static function pdf(string $titulo, array $headers, array $filas, string $subtitulo = ''): void
    {
        $rutaTcpdf = STORAGE_PATH . '/vendor/tcpdf/tcpdf.php';
        if (!file_exists($rutaTcpdf)) {
            // Fallback: vista imprimible
            self::vistaImprimible($titulo, $subtitulo, $headers, $filas);
        }

        define('K_TCPDF_EXTERNAL_CONFIG', true);
        define('K_PATH_MAIN', rtrim(dirname($rutaTcpdf), '/') . '/');
        define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
        define('K_PATH_URL', '');
        define('K_PATH_IMAGES', K_PATH_MAIN);
        define('K_BLANK_IMAGE', '_blank.png');
        define('PDF_HEADER_LOGO', '');
        define('PDF_HEADER_LOGO_WIDTH', 0);
        define('PDF_HEADER_TITLE', '');
        define('PDF_HEADER_STRING', '');
        define('PDF_MARGIN_HEADER', 5);
        define('PDF_MARGIN_FOOTER', 10);
        define('PDF_MARGIN_TOP', 20);
        define('PDF_MARGIN_BOTTOM', 20);
        define('PDF_MARGIN_LEFT', 15);
        define('PDF_MARGIN_RIGHT', 15);
        define('PDF_FONT_NAME_MAIN', 'helvetica');
        define('PDF_FONT_SIZE_MAIN', 9);
        define('PDF_FONT_NAME_DATA', 'helvetica');
        define('PDF_FONT_SIZE_DATA', 8);
        define('PDF_FONT_MONOSPACED', 'courier');
        define('PDF_MARGIN_FOOTER_SMALL', 5);

        ob_clean();
        require_once $rutaTcpdf;

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('EduAlert JJP');
        $pdf->SetTitle($titulo);
        $pdf->SetMargins(15, PDF_MARGIN_TOP, 15);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        // Encabezado institucional
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'EDUALERT JJP', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Sistema de Alerta Temprana Académica', 0, 1, 'C');
        $pdf->Cell(0, 6, setting_val('institucion_nombre', 'Institución Educativa Juan Jiménez Pimentel'), 0, 1, 'C');
        $pdf->Cell(0, 6, setting_val('institucion_ciudad', 'Tarapoto, San Martín - Perú'), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, $titulo, 0, 1, 'C');
        if ($subtitulo) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $subtitulo, 0, 1, 'C');
        }
        $pdf->Ln(3);

        // Fecha de generación
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'Generado por: ' . (current_user()['nombres'] ?? 'Sistema') . ' ' . (current_user()['apellidos'] ?? '')
            . ' - Fecha: ' . date('d/m/Y H:i'), 0, 1, 'R');
        $pdf->Ln(2);

        // Tabla
        $anchoPagina = 180; // mm útiles (210 - 30)
        $numCols = count($headers);
        $anchos = array_fill(0, $numCols, $anchoPagina / $numCols);

        $pdf->SetFillColor(30, 58, 95);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);
        foreach ($headers as $i => $h) {
            $pdf->Cell($anchos[$i], 7, mb_convert_encoding($h, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        }
        $pdf->Ln();

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        $filaAlterna = false;
        foreach ($filas as $fila) {
            if ($pdf->getY() > 250) {
                $pdf->AddPage();
            }
            $pdf->SetFillColor(245, 247, 250);
            foreach ($headers as $i => $h) {
                $v = $fila[$h] ?? $fila[strtolower($h)] ?? '';
                $v = mb_convert_encoding((string)$v, 'ISO-8859-1', 'UTF-8');
                $pdf->Cell($anchos[$i], 6, mb_substr($v, 0, 60, 'ISO-8859-1'), 1, 0, 'L', $filaAlterna ? 1 : 0);
            }
            $pdf->Ln();
            $filaAlterna = !$filaAlterna;
        }

        $pdf->Output(self::nombreSeguro($titulo) . '.pdf', 'D');
        exit;
    }

    /** Vista imprimible de respaldo si no existe TCPDF */
    private static function vistaImprimible(string $titulo, string $subtitulo, array $headers, array $filas): void
    {
        echo '<html><head><meta charset="utf-8"><title>' . e($titulo) . '</title></head><body>';
        echo '<h1 style="text-align:center">EduAlert JJP</h1>';
        echo '<h2 style="text-align:center">' . e($titulo) . '</h2>';
        if ($subtitulo) echo '<p style="text-align:center">' . e($subtitulo) . '</p>';
        echo '<table border="1" cellpadding="4" style="border-collapse:collapse;width:100%"><thead><tr>';
        foreach ($headers as $h) echo '<th style="background:#1e3a5f;color:#fff">' . e($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($filas as $fila) {
            echo '<tr>';
            foreach ($headers as $h) {
                echo '<td>' . e($fila[$h] ?? '') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }

    private static function nombreSeguro(string $nombre): string
    {
        $nombre = strtolower(trim($nombre));
        $nombre = preg_replace('/[^a-z0-9_\-]+/', '_', $nombre);
        return $nombre ?: 'exportacion';
    }
}

/** Compatibilidad con helper setting_val en el contexto PDF */
if (!function_exists('setting_val')) {
    function setting_val(string $clave, $defecto = null)
    {
        require_once APP_PATH . '/Models/Configuracion.php';
        return \App\Models\Configuracion::get($clave, $defecto);
    }
}