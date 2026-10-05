# EduAlert JJP - smoke test funcional: login, permisos, módulos, exportaciones
$ErrorActionPreference = 'Stop'
$base = 'http://localhost:8000'
$mysql = 'C:\Users\LENOVO\Desktop\xammp\mysql\bin\mysql.exe'
$fail = 0
$pass = 0

function Log($ok, $msg) {
    if ($ok) { $script:pass++; Write-Output "PASS  $msg" }
    else { $script:fail++; Write-Output "FAIL  $msg" }
}

function Get-Token($html) {
    if ($html -match 'name="_token" value="([^"]+)"') { return $Matches[1] }
    return ''
}

function Req($uri, $session, [string]$method = 'GET', $body = $null) {
    try {
        $p = @{ Uri = $uri; UseBasicParsing = $true; WebSession = $session; Method = $method; TimeoutSec = 120; MaximumRedirection = 8 }
        if ($null -ne $body) { $p.Body = $body }
        $r = Invoke-WebRequest @p
        return @{ status = [int]$r.StatusCode; content = $r.Content; type = [string]$r.Headers['Content-Type'] }
    } catch {
        $resp = $_.Exception.Response
        if ($resp) { return @{ status = [int]$resp.StatusCode; content = ''; type = [string]$resp.ContentType } }
        return @{ status = -1; content = $_.Exception.Message; type = '' }
    }
}

function Login-As($email, $password) {
    $s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $r = Req "$base/login" $s
    $t = Get-Token $r.content
    if (-not $t) { return $null }
    $lr = Req "$base/login" $s 'POST' @{ email = $email; password = $password; _token = $t }
    if ($lr.status -eq 200 -and $lr.content -match 'Dashboard|dashboard') { return $s }
    if ($lr.status -eq 200 -and $lr.content -match 'Credenciales|No puede ingresar|error') { return $null }
    # Puede no redirigir; probar dashboard directamente
    $d = Req "$base/dashboard" $s
    if ($d.status -eq 200 -and $d.content -match 'Dashboard') { return $s }
    return $null
}

# ID de ejemplo para ficha/detalles
$alertaId = (& $mysql -u root -N -e "SELECT id FROM edualert_jjp.alertas LIMIT 1;" 2>$null | Select-Object -First 1).Trim()
$estudianteId = (& $mysql -u root -N -e "SELECT id FROM edualert_jjp.estudiantes LIMIT 1;" 2>$null | Select-Object -First 1).Trim()
Write-Output "IDs de prueba: alerta=$alertaId estudiante=$estudianteId"
Write-Output ''

# ---------- 1) ADMIN: acceso total + exportaciones ----------
Write-Output '=== ADMIN ==='
$admin = Login-As 'admin@edualert.test' '12345678'
Log ($null -ne $admin) 'admin: login correcto'
if ($admin) {
    $d = Req "$base/dashboard" $admin
    Log ($d.status -eq 200) "admin: /dashboard -> $($d.status)"
    $dj = Req "$base/dashboard/datos" $admin
    $jsonOk = $false
    if ($dj.status -eq 200) { try { $j = $dj.content | ConvertFrom-Json; $jsonOk = ($null -ne $j) } catch { $jsonOk = $false } }
    Log $jsonOk "admin: /dashboard/datos JSON OK ($($dj.content.Length) bytes)"
    Log ((Req "$base/usuarios" $admin).status -eq 200) 'admin: /usuarios 200'
    Log ((Req "$base/configuracion" $admin).status -eq 200) 'admin: /configuracion 200'
    Log ((Req "$base/auditoria" $admin).status -eq 200) 'admin: /auditoria 200'
    Log ((Req "$base/estudiantes" $admin).status -eq 200) 'admin: /estudiantes 200'
    Log ((Req "$base/estudiantes/crear" $admin).status -eq 200) 'admin: /estudiantes/crear 200'
    Log ((Req "$base/estudiantes/ver/$estudianteId" $admin).status -eq 200) "admin: /estudiantes/ver/$estudianteId 200"
    Log ((Req "$base/alertas" $admin).status -eq 200) 'admin: /alertas 200'
    Log ((Req "$base/alertas/ver/$alertaId" $admin).status -eq 200) "admin: /alertas/ver/$alertaId 200"
    Log ((Req "$base/seguimiento" $admin).status -eq 200) 'admin: /seguimiento 200'
    Log ((Req "$base/matriculas" $admin).status -eq 200) 'admin: /matriculas 200'
    Log ((Req "$base/calificaciones" $admin).status -eq 200) 'admin: /calificaciones 200'
    Log ((Req "$base/asistencia" $admin).status -eq 200) 'admin: /asistencia 200'
    Log ((Req "$base/periodos" $admin).status -eq 200) 'admin: /periodos 200'

    # Exportar CSV reporte
    $csv = Req "$base/reportes/exportar?tipo=estudiantes_riesgo&formato=csv&periodo_id=0&seccion_id=0&grado_id=0" $admin
    $csvOk = ($csv.status -eq 200 -and $csv.content -match 'Código|Codigo|Nombres|promedio')
    Log $csvOk "admin: reporte CSV -> status $($csv.status), contiene datos ($($csv.content.Length) bytes)"
    # Exportar PDF (binario)
    $pdf = Req "$base/reportes/exportar?tipo=estudiantes_riesgo&formato=pdf&periodo_id=0&seccion_id=0&grado_id=0" $admin
    $pdfHead = ''
    if ($pdf.content -is [byte[]]) { $pdfHead = [System.Text.Encoding]::ASCII.GetString($pdf.content, 0, [Math]::Min(8, $pdf.content.Length)) }
    elseif ($pdf.content) { $pdfHead = $pdf.content.Substring(0, [Math]::Min(8, $pdf.content.Length)) }
    Log ($pdf.status -eq 200 -and $pdfHead.StartsWith('%PDF')) "admin: reporte PDF -> status $($pdf.status), header '$pdfHead'"
    # Exportar XLS
    $xls = Req "$base/reportes/exportar?tipo=alertas&formato=xls&periodo_id=0&seccion_id=0&grado_id=0" $admin
    $xlsStr = if ($xls.content -is [byte[]]) { [System.Text.Encoding]::UTF8.GetString($xls.content) } else { [string]$xls.content }
    Log ($xls.status -eq 200 -and $xlsStr -match 'html|table') "admin: reporte XLS -> status $($xls.status), tipo $($xls.type)"
    # Exportar estudiantes CSV
    $ecsv = Req "$base/estudiantes/exportar?formato=csv" $admin
    Log ($ecsv.status -eq 200) "admin: estudiantes/exportar CSV -> $($ecsv.status)"
    # Exportar alertas CSV
    $acsv = Req "$base/alertas/exportar?formato=csv" $admin
    Log ($acsv.status -eq 200) "admin: alertas/exportar CSV -> $($acsv.status)"
}

Write-Output ''
# ---------- 2) DIRECTOR ----------
Write-Output '=== DIRECTOR ==='
$dir = Login-As 'director@edualert.test' '12345678'
Log ($null -ne $dir) 'director: login correcto'
if ($dir) {
    Log ((Req "$base/dashboard" $dir).status -eq 200) 'director: /dashboard 200'
    Log ((Req "$base/estudiantes" $dir).status -eq 200) 'director: /estudiantes 200'
    Log ((Req "$base/alertas" $dir).status -eq 200) 'director: /alertas 200'
    Log ((Req "$base/reportes" $dir).status -eq 200) 'director: /reportes 200'
    Log ((Req "$base/usuarios" $dir).status -eq 403) 'director: /usuarios 403 (sin permiso)'
    Log ((Req "$base/configuracion" $dir).status -eq 403) 'director: /configuracion 403 (sin permiso)'
}

Write-Output ''
# ---------- 3) DOCENTE ----------
Write-Output '=== DOCENTE ==='
$doc = Login-As 'docente@edualert.test' '12345678'
Log ($null -ne $doc) 'docente: login correcto'
if ($doc) {
    Log ((Req "$base/calificaciones" $doc).status -eq 200) 'docente: /calificaciones 200'
    Log ((Req "$base/asistencia" $doc).status -eq 200) 'docente: /asistencia 200'
    Log ((Req "$base/alertas" $doc).status -eq 200) 'docente: /alertas 200'
    Log ((Req "$base/usuarios" $doc).status -eq 403) 'docente: /usuarios 403 (sin permiso)'
    Log ((Req "$base/matriculas" $doc).status -eq 403) 'docente: /matriculas 403 (sin permiso)'
}

Write-Output ''
# ---------- 4) TUTOR ----------
Write-Output '=== TUTOR ==='
$tut = Login-As 'tutor@edualert.test' '12345678'
Log ($null -ne $tut) 'tutor: login correcto'
if ($tut) {
    Log ((Req "$base/alertas" $tut).status -eq 200) 'tutor: /alertas 200'
    Log ((Req "$base/seguimiento" $tut).status -eq 200) 'tutor: /seguimiento 200'
    Log ((Req "$base/reportes" $tut).status -eq 200) 'tutor: /reportes 200'
    Log ((Req "$base/asistencia" $tut).status -eq 403) 'tutor: /asistencia 403 (sin permiso)'
}

Write-Output ''
# ---------- 5) AUXILIAR ----------
Write-Output '=== AUXILIAR ==='
$aux = Login-As 'auxiliar@edualert.test' '12345678'
Log ($null -ne $aux) 'auxiliar: login correcto'
if ($aux) {
    Log ((Req "$base/asistencia" $aux).status -eq 200) 'auxiliar: /asistencia 200'
    Log ((Req "$base/alertas" $aux).status -eq 403) 'auxiliar: /alertas 403 (sin permiso)'
    Log ((Req "$base/reportes" $aux).status -eq 403) 'auxiliar: /reportes 403 (sin permiso)'
    Log ((Req "$base/usuarios" $aux).status -eq 403) 'auxiliar: /usuarios 403 (sin permiso)'
    # marcar-todas notificaciones
    $nt = Req "$base/notificaciones" $aux
    $tok = Get-Token $nt.content
    $mt = Req "$base/notificaciones/marcar-todas" $aux 'POST' @{ _token = $tok }
    Log (($mt.status -eq 200 -or $mt.status -eq 302) -and $mt.content -match 'Notificaciones' -or $mt.status -eq 302) "auxiliar: notificaciones/marcar-todas -> $($mt.status)"
}

Write-Output ''
# ---------- 6) Acceso anónimo ----------
Write-Output '=== ANONIMO ==='
$anon = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$ar = Req "$base/dashboard" $anon
Log (($ar.status -eq 302 -or $ar.status -eq 200 -and $ar.content -match 'login')) "anonimo: /dashboard sin sesion -> $($ar.status)"

Write-Output ''
Write-Output "RESULTADO: $pass correctos, $fail fallidos"
if ($fail -gt 0) { exit 1 } else { exit 0 }