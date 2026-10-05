# EduAlert JJP - Detiene solo los procesos (php.exe / mysqld.exe)
# que pertenecen a ESTA carpeta portatil.
$raiz   = Split-Path -Parent $MyInvocation.MyCommand.Path
$patron = (Join-Path $raiz 'xampp') -replace '\\', '\\'
$cont   = 0
Get-CimInstance Win32_Process -Filter "Name='mysqld.exe' or Name='php.exe'" -ErrorAction SilentlyContinue |
    Where-Object { $_.ExecutablePath -and ($_.ExecutablePath -match $patron) } |
    ForEach-Object {
        try {
            Stop-Process -Id $_.ProcessId -Force -ErrorAction Stop
            Write-Host ('Detenido: ' + $_.Name + ' (PID ' + $_.ProcessId + ')')
            $cont++
        } catch { }
    }
if ($cont -eq 0) { Write-Host 'No habia procesos de EduAlert JJP activos.' }
Write-Host 'Listo.'