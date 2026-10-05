# ============================================================
#  EduAlert JJP - Lanzador portatil
#  Ejecutar: doble clic en iniciar.bat (o este archivo).
#  No requiere instalacion: usa el PHP y MySQL incluidos en la
#  carpeta xampp\. Al iniciar se genera xampp\mysql\my.ini con
#  rutas absolutas de la ubicacion actual, por lo que funciona
#  desde cualquier ruta de la PC (incluso con espacios).
# ============================================================
$ErrorActionPreference = 'Stop'

$raiz     = Split-Path -Parent $MyInvocation.MyCommand.Path
$mysqlDir = Join-Path $raiz 'xampp\mysql'
$mysqlIni = Join-Path $mysqlDir 'my.ini'
$dataDir  = Join-Path $mysqlDir 'data'
$mysqld   = Join-Path $mysqlDir 'bin\mysqld.exe'
$php      = Join-Path $raiz 'xampp\php\php.exe'
$proy     = Join-Path $raiz 'EduAlertJJP'
$tmpDir   = Join-Path $raiz 'xampp\tmp'
$logDir   = Join-Path $raiz 'xampp\php\logs'
$extDir   = Join-Path $raiz 'xampp\php\ext'
$logArch  = Join-Path $logDir 'php_error_log'
$puertoDb = 3306
$puertoWeb = 8000
$url      = 'http://127.0.0.1:' + $puertoWeb

# Rutas con '/' para my.ini (el parser de MariaDB trata '\t', '\s'
# como escapes y romperia rutas con backslashes)
$iniMysqlDir = ($mysqlDir -replace '\\', '/')
$iniDataDir  = ($dataDir  -replace '\\', '/')
$iniTmpDir   = ($tmpDir   -replace '\\', '/')
$iniCharsets = ((Join-Path $mysqlDir 'share\charsets') -replace '\\', '/')

if (-not (Test-Path $mysqld)) { Write-Host 'No se encontro MySQL portatil en: ' $mysqld; Read-Host 'Enter para salir'; exit 1 }
if (-not (Test-Path $php))    { Write-Host 'No se encontro PHP portatil en: ' $php;    Read-Host 'Enter para salir'; exit 1 }
if (-not (Test-Path $dataDir)){ Write-Host 'Falta la carpeta de datos xampp\mysql\data.'; Read-Host 'Enter para salir'; exit 1 }

function Test-Conexion([int]$puerto) {
    $c = New-Object System.Net.Sockets.TcpClient
    try {
        $iar = $c.BeginConnect('127.0.0.1', $puerto, $null, $null)
        if (-not $iar.AsyncWaitHandle.WaitOne(600)) { $c.Close(); return $false }
        $c.EndConnect($iar); $c.Close(); return $true
    } catch { $c.Close(); return $false }
}

# ---------- 1) MySQL ----------
if (Test-Conexion $puertoDb) {
    Write-Host 'MySQL ya estaba activo en el puerto 3306.'
} else {
    Write-Host 'Generando configuracion de MySQL y arrancando ...'

    # my.ini con rutas absolutas de la ubicacion actual del paquete
    $ini = @"
[client]
port=$puertoDb
default-character-set=utf8mb4

[mysqld]
port=$puertoDb
bind-address=127.0.0.1
basedir="$iniMysqlDir"
datadir="$iniDataDir"
tmpdir="$iniTmpDir"
character-sets-dir="$iniCharsets"
server-id=1
pid_file="mysql.pid"
log_error="mysql_error.log"
key_buffer=16M
max_allowed_packet=64M
sort_buffer_size=512K
net_buffer_length=8K
read_buffer_size=256K
read_rnd_buffer_size=512K
myisam_sort_buffer_size=8M
innodb_data_home_dir="$iniDataDir"
innodb_data_file_path=ibdata1:10M:autoextend
innodb_log_group_home_dir="$iniDataDir"
innodb_buffer_pool_size=16M
innodb_log_file_size=5M
innodb_log_buffer_size=8M
innodb_flush_log_at_trx_commit=1
innodb_lock_wait_timeout=50
sql_mode=NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION
log_bin_trust_function_creators=1
character-set-server=utf8mb4
collation-server=utf8mb4_general_ci

[mysqldump]
max_allowed_packet=16M
"@
    Set-Content -Path $mysqlIni -Value $ini -Encoding Ascii

    $iniArg = '--defaults-file="' + $mysqlIni + '"'
    Start-Process -FilePath $mysqld -ArgumentList $iniArg -WorkingDirectory $mysqlDir -WindowStyle Hidden | Out-Null

    $ok = $false
    for ($i = 0; $i -lt 45; $i++) {
        Start-Sleep -Seconds 1
        if (Test-Conexion $puertoDb) { $ok = $true; break }
    }
    if (-not $ok) {
        Write-Host 'No se pudo iniciar MySQL. Revise que el puerto 3306 no este ocupado.' -ForegroundColor Red
        Write-Host ('Revise el log: ' + (Join-Path $dataDir 'mysql_error.log'))
        Read-Host 'Enter para salir'; exit 1
    }
    Write-Host 'MySQL listo.'
}

# ---------- 2) Servidor web (PHP) ----------
if (Test-Conexion $puertoWeb) {
    Write-Host 'Ya hay algo escuchando en el puerto 8000; se abrira el navegador igual.'
} else {
    Write-Host 'Iniciando servidor web ...'
    if (-not (Test-Path $tmpDir)) { New-Item -ItemType Directory -Force -Path $tmpDir | Out-Null }
    if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Force -Path $logDir | Out-Null }
    $argsP = @(
        '-d', ('extension_dir="' + $extDir + '"'),
        '-d', ('session.save_path="' + $tmpDir + '"'),
        '-d', ('upload_tmp_dir="' + $tmpDir + '"'),
        '-d', ('error_log="' + $logArch + '"'),
        '-S', ('127.0.0.1:' + $puertoWeb),
        '-t', 'public',
        'public/server.php'
    )
    Start-Process -FilePath $php -ArgumentList $argsP -WorkingDirectory $proy -WindowStyle Hidden | Out-Null
    $ok = $false
    for ($i = 0; $i -lt 20; $i++) {
        Start-Sleep -Milliseconds 500
        try {
            $r = Invoke-WebRequest -Uri ($url + '/login') -UseBasicParsing -TimeoutSec 2
            if ($r.StatusCode -lt 500) { $ok = $true; break }
        } catch { }
    }
    if (-not $ok) {
        Write-Host 'No se pudo iniciar el servidor web. Revise que el puerto 8000 este libre.' -ForegroundColor Red
        Read-Host 'Enter para salir'; exit 1
    }
    Write-Host 'Servidor web listo.'
}

# ---------- 3) Abrir navegador ----------
Write-Host ''
Write-Host '================================================================'
Write-Host '  EduAlert JJP listo:  ' $url
Write-Host '  Usuarios demo:  admin / director / docente / tutor / auxiliar  @edualert.test'
Write-Host '  Contrasena:      12345678'
Write-Host '  Para detener:    doble clic en detener.bat'
Write-Host '================================================================'
Start-Process $url