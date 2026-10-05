param(
    [string]$NginxDirectory = 'C:\nginx',
    [string]$PhpDirectory = 'C:\xampp\php'
)
$ErrorActionPreference = 'Stop'
$nginxExe = Join-Path $NginxDirectory 'nginx.exe'
$phpCgiExe = Join-Path $PhpDirectory 'php-cgi.exe'
$phpIni = Join-Path $PhpDirectory 'php.ini'
foreach ($requiredPath in @($nginxExe, $phpCgiExe, $phpIni)) {
    if (!(Test-Path $requiredPath)) { throw "Missing file: $requiredPath" }
}
if (Get-NetTCPConnection -LocalPort 9000 -State Listen -ErrorAction SilentlyContinue) {
    throw 'Port 9000 is already in use. Keep the existing PHP window open, or stop it before running this script.'
}
Push-Location $NginxDirectory
try {
    & $nginxExe -t
    if ($LASTEXITCODE -ne 0) { throw 'Nginx configuration test failed.' }
    if (Get-Process nginx -ErrorAction SilentlyContinue) {
        & $nginxExe -s reload
        if ($LASTEXITCODE -ne 0) { throw 'Nginx reload failed.' }
    } else {
        Start-Process -FilePath $nginxExe -WorkingDirectory $NginxDirectory -WindowStyle Hidden
    }
} finally {
    Pop-Location
}
Write-Host 'Keep this window open. MySQL must be running.'
Write-Host 'Open http://localhost:8080/index.php?r=site/index after PHP starts.'
& $phpCgiExe -c $phpIni -d cgi.force_redirect=0 -d cgi.fix_pathinfo=0 -b 127.0.0.1:9000
if ($LASTEXITCODE -ne 0) { throw 'PHP FastCGI failed to start.' }
