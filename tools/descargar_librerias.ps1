# Script de descarga de librerías frontend para EduAlert JJP
# Ejecución: powershell -ExecutionPolicy Bypass -File descargar_librerias.ps1
$ErrorActionPreference = "Stop"
$base = "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\public\assets\vendor"
$dirs = @("bootstrap","jquery","datatables","chartjs","sweetalert2","icons")
foreach ($d in $dirs) { New-Item -ItemType Directory -Force -Path "$base\$d" | Out-Null }

function DL($url, $dest) {
    Write-Output "Descargando: $url"
    curl.exe -sSL --retry 3 -o $dest $url
    if (Test-Path $dest) {
        $len = (Get-Item $dest).Length
        Write-Output "  OK ($len bytes) -> $dest"
        if ($len -lt 1000) { Write-Output "  *** ADVERTENCIA: archivo demasiado pequeno, puede estar fallido ***" }
    } else {
        Write-Output "  *** ERROR: no se descargo $url ***"
    }
}

# Bootstrap 5.3.3
DL "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" "$base\bootstrap\bootstrap.min.css"
DL "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" "$base\bootstrap\bootstrap.bundle.min.js"

# jQuery 3.7.1
DL "https://code.jquery.com/jquery-3.7.1.min.js" "$base\jquery\jquery.min.js"

# DataTables 1.13.8
DL "https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" "$base\datatables\dataTables.bootstrap5.min.css"
DL "https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" "$base\datatables\jquery.dataTables.min.js"
DL "https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js" "$base\datatables\dataTables.bootstrap5.min.js"

# Chart.js 4.4.3
DL "https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" "$base\chartjs\chart.umd.min.js"

# SweetAlert2 11.12.4
DL "https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.js" "$base\sweetalert2\sweetalert2.min.js"
DL "https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css" "$base\sweetalert2\sweetalert2.min.css"

# Bootstrap Icons 1.11.3
DL "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" "$base\icons\bootstrap-icons.min.css"
DL "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2" "$base\icons\bootstrap-icons.woff2"
DL "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff" "$base\icons\bootstrap-icons.woff"

# TCPDF 6.7.5 (para generar PDF reales offline)
New-Item -ItemType Directory -Force -Path "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor" | Out-Null
DL "https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.7.5.zip" "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor\tcpdf.zip"
if (Test-Path "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor\tcpdf.zip") {
    Expand-Archive -Force -Path "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor\tcpdf.zip" -DestinationPath "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor"
    Remove-Item "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor\tcpdf.zip"
    if (Test-Path "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor\TCPDF-6.7.5") {
        Rename-Item "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP\storage\vendor\TCPDF-6.7.5" "tcpdf"
        Write-Output "TCPDF extraido OK"
    }
}
Write-Output "===== DESCARGA COMPLETA ====="