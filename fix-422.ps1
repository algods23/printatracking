# One-click fix for Printa Signages HTTP 422 when creating tasks
# Run this script to fix the validation mismatch causing 422 errors

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "Printa Signages - Fix HTTP 422 (Task Creation)" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$bladeFile = Join-Path $scriptDir "resources\views\tasks\create.blade.php"
$controllerFile = Join-Path $scriptDir "app\Http\Controllers\TaskController.php"

Write-Host "Step 1: Fixing create.blade.php validation..." -ForegroundColor Yellow

if (Test-Path $bladeFile) {
    $content = Get-Content $bladeFile -Raw
    
    # Remove required from due_date input
    $content = $content -replace 'value="{{ old\(''due_date'', date\(''Y-m-d''\) \) }}" required', 'value="{{ old(''due_date'', date(''Y-m-d'')) }}"'
    
    # Remove required from priority select
    $content = $content -replace 'name="priority" required class', 'name="priority" class'
    
    Set-Content $bladeFile $content -NoNewline
    Write-Host "  - Removed required attributes from due_date and priority fields" -ForegroundColor Green
} else {
    Write-Host "  - ERROR: create.blade.php not found at $bladeFile" -ForegroundColor Red
}

Write-Host ""
Write-Host "Step 2: Fixing TaskController.php validation..." -ForegroundColor Yellow

if (Test-Path $controllerFile) {
    $content = Get-Content $controllerFile -Raw
    
    # Replace customer_name validation rule
    $oldValidation = "'customer_name'     => 'required|string|max:255',"
    $newValidation = "'customer_name'     => [
                'required_unless:is_walkin,true',
                'string',
                'max:255',
            ],"
    
    $content = $content -replace [regex]::Escape($oldValidation), $newValidation
    
    # Add walk-in handling logic after product_type line
    $productTypeLine = "`$validated['product_type'] = `$validated['product_type'] ?? 'Other';"
    $walkinLogic = "`$validated['product_type'] = `$validated['product_type'] ?? 'Other';

        // Handle walk-in customer
        if (`$request->boolean('is_walkin')) {
            `$validated['customer_name'] = 'walk-in';
        }"
    
    $content = $content -replace [regex]::Escape($productTypeLine), $walkinLogic
    
    Set-Content $controllerFile $content -NoNewline
    Write-Host "  - Updated customer_name validation to handle walk-in customers" -ForegroundColor Green
    Write-Host "  - Added automatic walk-in name assignment" -ForegroundColor Green
} else {
    Write-Host "  - ERROR: TaskController.php not found at $controllerFile" -ForegroundColor Red
}

Write-Host ""
Write-Host "Done! Restart Printa Signages to apply the fix." -ForegroundColor Green
Write-Host ""
Write-Host "The 422 error when creating tasks should now be resolved." -ForegroundColor Yellow
Write-Host ""
