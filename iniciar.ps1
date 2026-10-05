<#
  EduAlert JJP - Iniciar sistema (MySQL portable + servidor PHP)
  Uso:  botón derecho sobre este archivo -> "Ejecutar con PowerShell"
        o desde una terminal:  powershell -ExecutionPolicy Bypass -File iniciar.ps1

  Requisitos: XAMPP portable en C:\Users\LENOVO\Desktop\xammp (PHP 8.2+, MySQL)
#>

$ErrorActionPreference = 'Continue'

# ---------- Rutas (editar si mueves el proyecto o XAMPP) ----------
$XAMPP     = 'C:\xampp'
$PROYECTO  = 'C:\Users\Master Games\Downloads\EduAlertJJP'
$PHP       = Join-Path $XAMPP 'php\php.exe'
$MYSQLD    = Join-Path $XAMPP 'mysql\bin\mysqld.exe'
$MYINI     = Join-Path $XAMPP 'mysql\my.ini'
$PUERTO_WEB = 8000
$PUERTO_MYSQL = 3306

Write-Host "== EduAlert JJP - Iniciador ==" -ForegroundColor Cyan

# ---------- 1) MySQL ----------
$testMysql = Test-NetConnection -ComputerName localhost -Port $PUERTO_MYSQL -WarningAction SilentlyContinue
if ($testMysql.TcpTestSucceeded) {
    Write-Host "[OK] MySQL ya esta corriendo (puerto $PUERTO_MYSQL)." -ForegroundColor Green
} else {
    Write-Host "[..] Arrancando MySQL portable..." -ForegroundColor Yellow
    Start-Process -FilePath $MYSQLD -ArgumentList "--defaults-file=$MYINI" -WindowStyle Hidden
    # Esperar a que acepte conexiones (max 40 s)
    $ok = $false
    for ($i = 0; $i -lt 40; $i++) {
        Start-Sleep -Seconds 1
        $t = Test-NetConnection -ComputerName localhost -Port $PUERTO_MYSQL -WarningAction SilentlyContinue
        if ($t.TcpTestSucceeded) {
            $ok = $true; break
        }
    }
    if ($ok) { Write-Host "[OK] MySQL listo." -ForegroundColor Green }
    else { Write-Host "[!!] MySQL no respondio. Revisa $XAMPP\mysql\data\mysql_error.log" -ForegroundColor Red; exit 1 }
}

# ---------- 2) Servidor web PHP ----------
$testWeb = Test-NetConnection -ComputerName localhost -Port $PUERTO_WEB -WarningAction SilentlyContinue
if ($testWeb.TcpTestSucceeded) {
    Write-Host "[OK] Servidor web ya esta corriendo (puerto $PUERTO_WEB)." -ForegroundColor Green
} else {
    Write-Host "[..] Arrancando servidor PHP en localhost:$PUERTO_WEB..." -ForegroundColor Yellow
    Start-Process -FilePath $PHP -ArgumentList "-S","localhost:$PUERTO_WEB","-t","public","public/server.php" -WorkingDirectory $PROYECTO -WindowStyle Hidden
    $ok = $false
    for ($i = 0; $i -lt 20; $i++) {
        Start-Sleep -Milliseconds 500
        try {
            $r = Invoke-WebRequest -Uri "http://localhost:$PUERTO_WEB/login" -UseBasicParsing -TimeoutSec 3
            $ok = $true; break
        } catch { }
    }
    if ($ok) { Write-Host "[OK] Servidor web listo (HTTP $($r.StatusCode))." -ForegroundColor Green }
    else { Write-Host "[!!] El servidor web no respondio. Revisa que el puerto 8000 este libre." -ForegroundColor Red; exit 1 }
}

# ---------- 3) Abrir navegador ----------
Start-Process "http://localhost:$PUERTO_WEB/login"

Write-Host ""
Write-Host "== Sistema listo ==" -ForegroundColor Cyan
Write-Host "URL: http://localhost:$PUERTO_WEB/login" -ForegroundColor White
Write-Host "Cuentas demo (password: 12345678):"
Write-Host "  admin@edualert.test   (acceso total: usuarios, configuracion, auditoria, reportes)"
Write-Host "  director@edualert.test (dashboard, alertas, reportes)"
Write-Host "  docente@edualert.test  (calificaciones, asistencia, alertas)"
Write-Host "  tutor@edualert.test    (alertas y seguimiento)"
Write-Host "  auxiliar@edualert.test (registro de asistencia)"