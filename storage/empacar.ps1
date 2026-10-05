# ============================================================
#  EduAlert JJP - Empacador de la version portatil
#  (herramienta de desarrollo - se ejecuta en la maquina origen)
#  Requisito: detener MySQL ANTES de ejecutar para copiar el
#  datadir de forma consistente (mysqladmin -u root shutdown).
# ============================================================
$ErrorActionPreference = 'Stop'

$origenProy  = 'C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP'
$origenXampp = 'C:\Users\LENOVO\Desktop\xammp'
$plantilla   = Join-Path $origenProy 'storage\plantilla_portable'
$destino     = 'C:\Users\LENOVO\Desktop\EduAlertJJP-Portable'
$zip         = 'C:\Users\LENOVO\Desktop\EduAlertJJP-Portable.zip'

if (Test-Path $destino) { Remove-Item $destino -Recurse -Force }
if (Test-Path $zip)     { Remove-Item $zip -Force }

$proy  = Join-Path $destino 'EduAlertJJP'
$php   = Join-Path $destino 'xampp\php'
$mysql = Join-Path $destino 'xampp\mysql'
$tmp   = Join-Path $destino 'xampp\tmp'
New-Item -ItemType Directory -Force -Path $proy, $php, $mysql, $tmp | Out-Null

# ----- 1) Proyecto (sin pruebas, logs ni empacador) -----
robocopy $origenProy $proy /E `
  /XF smoke_install.ps1 smoke_funcional.ps1 smoke_post.ps1 smoke_final.ps1 empacar.ps1 iniciar.ps1 *.log `
  /XD plantilla_portable storage\logs /NFL /NDL /NJH /NJS /NP
if ($LASTEXITCODE -ge 8) { throw 'Fallo al copiar el proyecto' }

# ----- 2) PHP completo + runtime de C++ (para PC sin VC++ Redistributable)
robocopy "$origenXampp\php" $php /E /NFL /NDL /NJH /NJS /NP
if ($LASTEXITCODE -ge 8) { throw 'Fallo al copiar PHP' }
New-Item -ItemType Directory -Force -Path (Join-Path $php 'logs') | Out-Null
foreach ($d in 'vcruntime140.dll','vcruntime140_1.dll','msvcp140.dll') {
    Copy-Item (Join-Path $env:windir "System32\$d") (Join-Path $php $d) -Force
}

# ----- 3) MySQL: bin y share -----
robocopy "$origenXampp\mysql\bin"   (Join-Path $mysql 'bin')   /E /NFL /NDL /NJH /NJS /NP
if ($LASTEXITCODE -ge 8) { throw 'Fallo al copiar bin' }
robocopy "$origenXampp\mysql\share" (Join-Path $mysql 'share') /E /NFL /NDL /NJH /NJS /NP
if ($LASTEXITCODE -ge 8) { throw 'Fallo al copiar share' }

# ----- 4) Data: solo sistema + edualert_jjp (sin BD ajenas,
#         sin my.ini interno y sin logs - evita rutas absolutas) -----
robocopy "$origenXampp\mysql\data" (Join-Path $mysql 'data') /E `
  /XD clima_db phpmyadmin test `
  /XF my.ini mysql_error.log *.err /NFL /NDL /NJH /NJS /NP
if ($LASTEXITCODE -ge 8) { throw 'Fallo al copiar data' }

# ----- 5) my.ini portatil (rutas relativas: se inicia con el
#         working directory dentro de xampp\mysql) -----
$ini = @'
[client]
port=3306
default-character-set=utf8mb4

[mysqld]
port=3306
bind-address=127.0.0.1
basedir="."
datadir="data"
tmpdir="../tmp"
character-sets-dir="share/charsets"
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
innodb_data_home_dir="data"
innodb_data_file_path=ibdata1:10M:autoextend
innodb_log_group_home_dir="data"
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
'@
Set-Content -Path (Join-Path $mysql 'my.ini') -Value $ini -Encoding Ascii

# ----- 6) Lanzadores y LEEME -----
Copy-Item (Join-Path $plantilla '*') $destino -Recurse -Force

Write-Host ''
Write-Host ('LISTO. Carpeta portatil: ' + $destino)
Write-Host 'Para crear el ZIP:  tar -a -c -f "C:\Users\LENOVO\Desktop\EduAlertJJP-Portable.zip" -C "C:\Users\LENOVO\Desktop" EduAlertJJP-Portable'