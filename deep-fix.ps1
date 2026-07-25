# Deep diagnostic + repair for Printa Signages HTTP 500
# Run on the BROKEN computer. Copy ALL output and send to developer.

$ErrorActionPreference = "Continue"
$report = @()

function Log($msg, $color = "White") {
    Write-Host $msg -ForegroundColor $color
    $script:report += $msg
}

Log ""
Log "========================================" "Cyan"
Log "Printa Signages Deep Diagnostic" "Cyan"
Log "========================================" "Cyan"
Log "Time: $(Get-Date)" 
Log "Computer: $env:COMPUTERNAME"
Log "User: $env:USERNAME"
Log ""

# --- Find AppData ---
$appDataPath = $null
foreach ($name in @("Printa Signages", "printa-signages")) {
    $test = Join-Path $env:APPDATA $name
    if (Test-Path $test) {
        $appDataPath = $test
        break
    }
}

if (-not $appDataPath) {
    Log "FATAL: No AppData folder found at $env:APPDATA\Printa Signages" "Red"
    exit 1
}
Log "AppData: $appDataPath" "Green"

# --- Find install + PHP ---
$installPaths = @(
    "${env:ProgramFiles}\Printa Signages",
    "${env:ProgramFiles(x86)}\Printa Signages",
    "${env:LocalAppData}\Programs\Printa Signages"
)
$installPath = $installPaths | Where-Object { Test-Path $_ } | Select-Object -First 1

$phpCandidates = @()
if ($installPath) {
    Log "Install: $installPath" "Green"
    $phpCandidates += Join-Path $installPath "resources\runtime\php\php.exe"
    $phpCandidates += Join-Path $installPath "runtime\php\php.exe"
}
$phpCandidates += "php"

$phpExe = $null
foreach ($p in $phpCandidates) {
    try {
        $ver = & $p --version 2>&1
        if ($LASTEXITCODE -eq 0 -or $ver -match "PHP") {
            $phpExe = $p
            Log "PHP: $p ($ver)" "Green"
            break
        }
    } catch {}
}
if (-not $phpExe) {
    Log "WARNING: PHP not found - some checks skipped" "Yellow"
}

# --- Key paths ---
$laravelPath = Join-Path $appDataPath "runtime\laravel-app"
$dbPath      = Join-Path $appDataPath "runtime\database\database.sqlite"
$configPath  = Join-Path $appDataPath "config.json"
$envPath     = Join-Path $laravelPath ".env"
$markerPath  = Join-Path $appDataPath "runtime\.database-ready"
$laravelLog  = Join-Path $laravelPath "storage\logs\laravel.log"
$startupLog  = Join-Path $appDataPath "startup.log"

Log ""
Log "--- File checks ---" "Yellow"

function Check-PathInfo($label, $path) {
    if (Test-Path $path) {
        $item = Get-Item $path
        $size = if ($item.PSIsContainer) { "(folder)" } else { "$($item.Length) bytes" }
        Log "  OK  $label : $path ($size)" "Green"
        return $true
    }
    Log "  MISSING  $label : $path" "Red"
    return $false
}

Check-PathInfo "Laravel app" $laravelPath
$dbExists = Check-PathInfo "Database" $dbPath
Check-PathInfo "config.json" $configPath
Check-PathInfo ".env" $envPath
Check-PathInfo ".database-ready marker" $markerPath
Check-PathInfo "startup.log" $startupLog
Check-PathInfo "laravel.log" $laravelLog
Check-PathInfo "vendor/autoload.php" (Join-Path $laravelPath "vendor\autoload.php")
Check-PathInfo "artisan" (Join-Path $laravelPath "artisan")
Check-PathInfo "public/index.php" (Join-Path $laravelPath "public\index.php")

# --- Read config + env ---
Log ""
Log "--- config.json ---" "Yellow"
if (Test-Path $configPath) {
    Log (Get-Content $configPath -Raw)
}

Log ""
Log "--- .env (APP_KEY and DB only) ---" "Yellow"
if (Test-Path $envPath) {
    Get-Content $envPath | Where-Object { $_ -match '^(APP_KEY|APP_DEBUG|DB_|APP_ENV)=' } | ForEach-Object { Log "  $_" }
}

# --- Database size warning ---
Log ""
Log "--- Database analysis ---" "Yellow"
if ($dbExists) {
    $dbSize = (Get-Item $dbPath).Length
    if ($dbSize -lt 1000) {
        Log "  WARNING: Database is very small ($dbSize bytes) - likely empty or broken!" "Red"
    } else {
        Log "  Database size looks OK ($dbSize bytes)" "Green"
    }
    if ((Test-Path $markerPath) -and $dbSize -lt 1000) {
        Log "  PROBLEM: .database-ready exists but database is empty!" "Red"
        Log "  FIX: Removing .database-ready so app can re-initialize..." "Yellow"
        Remove-Item $markerPath -Force -ErrorAction SilentlyContinue
        Log "  Removed .database-ready" "Green"
    }
} else {
    Log "  PROBLEM: No database file! Copy database.sqlite from old computer to:" "Red"
    Log "  $dbPath" "Red"
}

# --- Laravel log (last 40 lines) ---
Log ""
Log "--- Last laravel.log errors ---" "Yellow"
if (Test-Path $laravelLog) {
    $lines = Get-Content $laravelLog -Tail 40
    $lines | ForEach-Object { Log "  $_" }
} else {
    Log "  No laravel.log yet - Laravel may not have written any logs" "Yellow"
}

# --- startup.log ---
Log ""
Log "--- startup.log ---" "Yellow"
if (Test-Path $startupLog) {
    Get-Content $startupLog -Tail 30 | ForEach-Object { Log "  $_" }
} else {
    Log "  No startup.log (old app version without logging)" "Yellow"
}

# --- Run artisan commands ---
if ($phpExe -and (Test-Path (Join-Path $laravelPath "artisan"))) {
    Log ""
    Log "--- Running Laravel artisan checks ---" "Yellow"
    Push-Location $laravelPath

    $commands = @(
        @("config:clear", "Clear config cache"),
        @("migrate:status", "Migration status"),
        @("migrate --force", "Run migrations")
    )

    foreach ($cmd in $commands) {
        $args = $cmd[0] -split ' '
        Log ""
        Log "  > php artisan $($cmd[0]) ($($cmd[1]))" "Cyan"
        try {
            $output = & $phpExe artisan @args 2>&1
            $output | ForEach-Object { Log "    $_" }
        } catch {
            Log "    ERROR: $_" "Red"
        }
    }

    # Check APP_KEY
    $envRaw = Get-Content $envPath -Raw -ErrorAction SilentlyContinue
    if ($envRaw -and $envRaw -notmatch '(?m)^APP_KEY=base64:.+$') {
        if ($envRaw -notmatch '(?m)^APP_KEY=') {
            Log "  Adding missing APP_KEY= line to .env..." "Yellow"
            $envRaw = "APP_KEY=`n" + $envRaw
            Set-Content $envPath $envRaw -NoNewline
        }
        Log ""
        Log "  > php artisan key:generate --force" "Cyan"
        $output = & $phpExe artisan key:generate --force 2>&1
        $output | ForEach-Object { Log "    $_" }
    }

    # storage:link
    Log ""
    Log "  > php artisan storage:link" "Cyan"
    $output = & $phpExe artisan storage:link 2>&1
    $output | ForEach-Object { Log "    $_" }

    Pop-Location
}

# --- Test HTTP ---
Log ""
Log "--- HTTP test (port 8000) ---" "Yellow"
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    Log "  GET /login => HTTP $($response.StatusCode)" "Green"
} catch {
    $status = $_.Exception.Response.StatusCode.value__
    if ($status) {
        Log "  GET /login => HTTP $status" "Red"
        if ($status -eq 500) {
            Log "  Still HTTP 500 - see laravel.log above for cause" "Red"
        }
    } else {
        Log "  Could not connect to http://127.0.0.1:8000" "Red"
        Log "  Is Printa Signages running? Start the app first, then run this script again." "Yellow"
    }
}

# --- Fix paths in config + env ---
Log ""
Log "--- Applying path fixes ---" "Yellow"
$backupFolder = Join-Path $env:USERPROFILE "Documents\Printa Signages\backups"
$config = @{
    appName = "Printa Signages"
    httpPort = 8000
    database = $dbPath
    backupFolder = $backupFolder
    installedAt = (Get-Date).ToString("o")
}
if (Test-Path $configPath) {
    try {
        $existing = Get-Content $configPath -Raw | ConvertFrom-Json
        if ($existing.installedAt) { $config.installedAt = $existing.installedAt }
    } catch {}
}
$config | ConvertTo-Json -Depth 10 | Set-Content $configPath
Log "  config.json updated" "Green"

if (Test-Path $envPath) {
    $envContent = Get-Content $envPath -Raw
    $dbForEnv = $dbPath -replace '\\', '/'
    if ($envContent -match '(?m)^DB_DATABASE=') {
        $envContent = $envContent -replace '(?m)^DB_DATABASE=.*', "DB_DATABASE=$dbForEnv"
    } else {
        $envContent += "`nDB_DATABASE=$dbForEnv`n"
    }
    Set-Content $envPath $envContent -NoNewline
    Log "  .env DB_DATABASE updated" "Green"
}

# --- Save report ---
$reportPath = Join-Path $env:USERPROFILE "Desktop\printa-deep-report.txt"
$report -join "`n" | Set-Content $reportPath
Log ""
Log "========================================" "Cyan"
Log "Report saved to: $reportPath" "Green"
Log "Please send this file to your developer!" "Yellow"
Log "Then RESTART Printa Signages." "Yellow"
Log "========================================" "Cyan"
Log ""
