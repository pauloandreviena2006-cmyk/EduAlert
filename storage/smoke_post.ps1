# EduAlert JJP - smoke test POST: formularios CSRF + verificación en BD
$ErrorActionPreference = 'Stop'
$base = 'http://localhost:8000'
$mysql = 'C:\Users\LENOVO\Desktop\xammp\mysql\bin\mysql.exe'
$fail = 0; $pass = 0
function Log($ok, $msg) { if ($ok) { $script:pass++; Write-Output "PASS  $msg" } else { $script:fail++; Write-Output "FAIL  $msg" } }
function Q($sql) { return (& $mysql -u root -N -e $sql 2>$null | Select-Object -First 1) }
function Get-Token($html) { if ($html -match 'name="_token" value="([^"]+)"') { return $Matches[1] } return '' }
function Req($uri, $session, [string]$method = 'GET', $body = $null) {
    try {
        $p = @{ Uri = $uri; UseBasicParsing = $true; WebSession = $session; Method = $method; TimeoutSec = 180; MaximumRedirection = 8 }
        if ($null -ne $body) { $p.Body = $body }
        $r = Invoke-WebRequest @p
        return @{ status = [int]$r.StatusCode; content = $r.Content }
    } catch {
        $resp = $_.Exception.Response
        if ($resp) { return @{ status = [int]$resp.StatusCode; content = '' } }
        return @{ status = -1; content = $_.Exception.Message }
    }
}

# POST con body codificado en UTF-8 (Invoke-WebRequest de PS 5.1 codifica en ANSI y rompe los acentos)
function PostUtf8($uri, $session, $body) {
    $req = [System.Net.HttpWebRequest]::Create($uri)
    $req.Method = 'POST'
    $req.ContentType = 'application/x-www-form-urlencoded'
    $req.CookieContainer = $session.Cookies
    $req.AllowAutoRedirect = $true
    $req.UserAgent = 'Mozilla/5.0'
    $qs = ($body.GetEnumerator() | ForEach-Object { [System.Uri]::EscapeDataString($_.Key) + '=' + [System.Uri]::EscapeDataString([string]$_.Value) }) -join '&'
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($qs)
    $req.ContentLength = $bytes.Length
    $st = $req.GetRequestStream(); $st.Write($bytes, 0, $bytes.Length); $st.Close()
    try {
        $resp = $req.GetResponse()
        $sr = New-Object System.IO.StreamReader($resp.GetResponseStream())
        return @{ status = [int]$resp.StatusCode; content = $sr.ReadToEnd() }
    } catch [System.Net.WebException] {
        $resp = $_.Exception.Response
        if ($resp) { return @{ status = [int]$resp.StatusCode; content = '' } }
        throw
    }
}

# Login admin
$s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$tok = Get-Token (Req "$base/login" $s).content
Req "$base/login" $s 'POST' @{ email = 'admin@edualert.test'; password = '12345678'; _token = $tok } | Out-Null
Write-Output '=== POST: re-ejecutar motor de alertas ==='
$tok = Get-Token (Req "$base/alertas" $s).content
$antes = [int](Q "SELECT COUNT(*) FROM edualert_jjp.alertas")
$g = Req "$base/alertas/generar" $s 'POST' @{ _token = $tok }
$despues = [int](Q "SELECT COUNT(*) FROM edualert_jjp.alertas")
Log ($g.status -eq 200 -and $despues -ge $antes) "alertas/generar POST -> $($g.status) (alertas: $antes -> $despues)"

Write-Output '=== POST: guardar configuracion (name=clave) ==='
$tok = Get-Token (Req "$base/configuracion" $s).content
$c = Req "$base/configuracion" $s 'POST' @{ _token = $tok; puntos_curso_3 = '35' }
$v = Q "SELECT valor FROM edualert_jjp.parametros WHERE clave='puntos_curso_3'"
Log ($c.status -eq 200 -and $v -eq '35') "configuracion POST -> $($c.status), puntos_curso_3=$v"

Write-Output '=== POST: crear estudiante (datos + matricula + apoderado) ==='
$tok = Get-Token (Req "$base/estudiantes/crear" $s).content
$par = Q "SELECT CONCAT(g.id, ':', (SELECT s.id FROM edualert_jjp.secciones s WHERE s.grado_id=g.id LIMIT 1)) FROM edualert_jjp.grados g WHERE g.estado=1 LIMIT 1"
$gradoId = $par.Split(':')[0]; $seccionId = $par.Split(':')[1]
$e = Req "$base/estudiantes/crear" $s 'POST' @{
    _token = $tok; dni = '99988877'; nombres = 'Estudiante Smoke'; apellidos = 'Prueba Integral';
    fecha_nacimiento = '2010-05-10'; sexo = 'M'; direccion = 'Jr. Prueba 123'; telefono = '999000111'; email = '';
    grado_id = $gradoId; seccion_id = $seccionId;
    apo_nombres = 'Apoderado Smoke'; apo_apellidos = 'Prueba'; apo_dni = '44445555'; apo_telefono = '999000222';
    apo_parentesco = 'Padre'; apo_ocupacion = 'Comerciante'
}
$nuevo = Q "SELECT CONCAT(codigo, '|', (SELECT COUNT(*) FROM edualert_jjp.matriculas m WHERE m.estudiante_id=e.id), '|', (SELECT COUNT(*) FROM edualert_jjp.apoderados a WHERE a.estudiante_id=e.id)) FROM edualert_jjp.estudiantes e WHERE dni='99988877'"
Log ($e.status -eq 200 -and $nuevo -match '^\d{4}-\d{4}\|1\|1$') "estudiantes/crear POST -> $($e.status), codigo|matriculas|apoderados = $nuevo"

Write-Output '=== POST: cambiar estado de alerta ==='
$alertaId = [int](Q "SELECT id FROM edualert_jjp.alertas ORDER BY id LIMIT 1")
$tok = Get-Token (Req "$base/alertas/ver/$alertaId" $s).content
$ae = PostUtf8 "$base/alertas/estado/$alertaId" $s @{ _token = $tok; estado = 'Atendida' }
$est = Q "SELECT estado FROM edualert_jjp.alertas WHERE id=$alertaId"
Log ($ae.status -eq 200 -and $est -eq 'Atendida') "alertas/estado/{id} POST -> $($ae.status), estado=$est"

Write-Output '=== POST: registrar seguimiento ==='
$tok = Get-Token (Req "$base/seguimiento/crear/$alertaId" $s).content
$sg = PostUtf8 "$base/seguimiento/crear/$alertaId" $s @{
    _token = $tok; fecha = '2026-09-21'; tipo_intervencion = 'Reforzamiento pedagógico';
    descripcion = 'Smoke test: sesión de reforzamiento programada por el tutor.';
    acuerdo = 'Reunión informativa con el apoderado'; compromiso = 'Asistir a las sesiones de reforzamiento';
    proxima_revision = '2026-10-05'; estado = 'En seguimiento'
}
$segOk = Q "SELECT COUNT(*) FROM edualert_jjp.seguimientos WHERE alerta_id=$alertaId AND estado='En seguimiento'"
Log ($sg.status -eq 200 -and [int]$segOk -ge 1) "seguimiento/crear POST -> $($sg.status), seguimientos encontrados=$segOk"

Write-Output '=== POST: crear y eliminar usuario ==='
$tok = Get-Token (Req "$base/usuarios/crear" $s).content
$u = Req "$base/usuarios/crear" $s 'POST' @{ _token = $tok; nombres = 'Smoke'; apellidos = 'Test'; email = 'smoke@edualert.test'; telefono = '999333444'; rol_id = '1'; password = '12345678' }
$uid = [int](Q "SELECT id FROM edualert_jjp.users WHERE email='smoke@edualert.test'")
Log ($u.status -eq 200 -and $uid -gt 0) "usuarios/crear POST -> $($u.status), id=$uid"
if ($uid -gt 0) {
    $tok = Get-Token (Req "$base/usuarios" $s).content
    $u2 = Req "$base/usuarios/eliminar/$uid" $s 'POST' @{ _token = $tok }
    $queda = [int](Q "SELECT COUNT(*) FROM edualert_jjp.users WHERE id=$uid")
    Log ($u2.status -eq 200 -and $queda -eq 0) "usuarios/eliminar POST -> $($u2.status), restantes=$queda"
}

Write-Output '=== POST: activar periodo ==='
$antesAct = Q "SELECT id FROM edualert_jjp.periodos WHERE activo=1 LIMIT 1"
$otroId = Q "SELECT id FROM edualert_jjp.periodos WHERE activo=0 LIMIT 1"
if ($otroId) {
    $tok = Get-Token (Req "$base/periodos" $s).content
    $p = Req "$base/periodos/activar/$otroId" $s 'POST' @{ _token = $tok }
    $activo = Q "SELECT COUNT(*) FROM edualert_jjp.periodos WHERE activo=1"
    Log ($p.status -eq 200 -and [int]$activo -eq 1) "periodos/activar POST -> $($p.status), periodos activos=$activo"
    # restaurar
    $tok = Get-Token (Req "$base/periodos" $s).content
    Req "$base/periodos/activar/$antesAct" $s 'POST' @{ _token = $tok } | Out-Null
} else { Write-Output 'SKIP  periodos/activar (no hay periodo inactivo)' }

Write-Output '=== POST: notificacion marcar (AJAX) ==='
$nid = [int](Q "SELECT id FROM edualert_jjp.notificaciones WHERE user_id=1 AND leido=0 LIMIT 1")
if ($nid -gt 0) {
    $tok = Get-Token (Req "$base/notificaciones" $s).content
    $n = Req "$base/notificaciones/marcar/$nid" $s 'POST' @{ _token = $tok }
    $leido = Q "SELECT leido FROM edualert_jjp.notificaciones WHERE id=$nid"
    Log ($n.status -eq 200 -and $leido -eq '1') "notificaciones/marcar/{id} POST -> $($n.status), leido=$leido"
} else { Write-Output 'SKIP  notificaciones/marcar (sin notificaciones sin leer para admin)' }

# Limpieza: revertir parametro
& $mysql -u root -e "UPDATE edualert_jjp.parametros SET valor='30' WHERE clave='puntos_curso_3';" 2>&1 | Out-Null

Write-Output ''
Write-Output "RESULTADO POST: $pass correctos, $fail fallidos"
if ($fail -gt 0) { exit 1 } else { exit 0 }