$ErrorActionPreference = "Stop"
$root = Resolve-Path (Join-Path $PSScriptRoot "..")

Set-Location $root

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)]
        [string]$FilePath,
        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]]$Arguments
    )

    & $FilePath @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "$FilePath failed with exit code $LASTEXITCODE"
    }
}

if (-not (Test-Path "vendor\autoload.php")) {
    Invoke-Checked composer install --no-dev --optimize-autoloader
}

Invoke-Checked npm run build
& "$PSScriptRoot\prepare-desktop.ps1"

if (-not (Test-Path "desktop\runtime\php\php.exe")) {
    throw "Missing portable PHP runtime. See desktop\runtime\README.md."
}

Invoke-Checked npx electron-builder --win nsis
