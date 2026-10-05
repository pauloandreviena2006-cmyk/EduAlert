$base = 'http://localhost:8000'
$fail = 0; $pass = 0
function Get-Token($html) { if ($html -match 'name="_token" value="([^"]+)"') { return $Matches[1] } return '' }
function Chk($ok, $msg) { if ($ok) { $script:pass++; Write-Output "PASS  $msg" } else { $script:fail++; Write-Output "FAIL  $msg" } }
foreach ($cuenta in @('admin','director','docente','tutor','auxiliar')) {
    $s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $r = Invoke-WebRequest -Uri "$base/login" -WebSession $s -UseBasicParsing
    $t = Get-Token $r.Content
    $lr = Invoke-WebRequest -Uri "$base/login" -Method POST -WebSession $s -UseBasicParsing -Body @{ email = "$cuenta@edualert.test"; password = '12345678'; _token = $t } -MaximumRedirection 0 -ErrorAction SilentlyContinue
    $d = Invoke-WebRequest -Uri "$base/dashboard" -WebSession $s -UseBasicParsing
    $ok = ($lr.StatusCode -eq 302 -and $lr.Headers['Location'] -like '*dashboard*' -and $d.StatusCode -eq 200 -and $d.Content -match 'Dashboard')
    Chk $ok "$cuenta@edualert.test -> login 302 dashboard, /dashboard 200"
}
Write-Output "RESULTADO: $pass correctos, $fail fallidos"
if ($fail -gt 0) { exit 1 } else { exit 0 }