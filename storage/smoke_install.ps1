# EduAlert JJP - smoke test: instalación
$ErrorActionPreference = 'Stop'
$base = 'http://localhost:8000'
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

function Get-Token($html) {
    if ($html -match 'name="_token" value="([^"]+)"') { return $Matches[1] }
    throw 'CSRF token no encontrado en la página'
}

Write-Output '=== 1) GET /instalacion ==='
$r1 = Invoke-WebRequest -Uri "$base/instalacion" -WebSession $session -UseBasicParsing
Write-Output "HTTP $($r1.StatusCode)"
$t1 = Get-Token $r1.Content

Write-Output '=== 2) POST /instalacion/crear-bd ==='
$r2 = Invoke-WebRequest -Uri "$base/instalacion/crear-bd" -Method POST -WebSession $session -UseBasicParsing -Body @{ _token = $t1 }
Write-Output "HTTP $($r2.StatusCode)"

Write-Output '=== 3) GET /instalacion (paso 2) ==='
$r3 = Invoke-WebRequest -Uri "$base/instalacion" -WebSession $session -UseBasicParsing
$t2 = Get-Token $r3.Content
if ($r3.Content -match 'Cargar datos de demostración') { Write-Output 'Paso 2 visible: OK' } else { Write-Output 'AVISO: paso 2 no visible' }

Write-Output '=== 4) POST /instalacion/datos-demo (200 estudiantes) ==='
$sw = [System.Diagnostics.Stopwatch]::StartNew()
$r4 = Invoke-WebRequest -Uri "$base/instalacion/datos-demo" -Method POST -WebSession $session -UseBasicParsing -Body @{ _token = $t2 } -TimeoutSec 300
$sw.Stop()
Write-Output "HTTP $($r4.StatusCode) | demo cargado en $($sw.Elapsed.TotalSeconds)s"