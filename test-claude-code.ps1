# Test Claude Code Installation Script
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Claude Code Installation Test" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Set environment variable for this session
$env:CLAUDE_CODE_GIT_BASH_PATH = "C:\Program Files\Git\bin\bash.exe"

Write-Host "1. Checking Prerequisites..." -ForegroundColor Yellow
Write-Host ""

# Check Node.js
Write-Host "   Node.js Version: " -NoNewline
try {
    $nodeVersion = node --version 2>$null
    Write-Host $nodeVersion -ForegroundColor Green
} catch {
    Write-Host "NOT INSTALLED" -ForegroundColor Red
}

# Check npm
Write-Host "   npm Version: " -NoNewline
try {
    $npmVersion = npm --version 2>$null
    Write-Host $npmVersion -ForegroundColor Green
} catch {
    Write-Host "NOT INSTALLED" -ForegroundColor Red
}

# Check Git
Write-Host "   Git Installation: " -NoNewline
if (Test-Path "C:\Program Files\Git\bin\bash.exe") {
    Write-Host "INSTALLED" -ForegroundColor Green
    Write-Host "   Git Bash Path: C:\Program Files\Git\bin\bash.exe" -ForegroundColor Gray
} else {
    Write-Host "NOT INSTALLED" -ForegroundColor Red
}

# Check Claude Code
Write-Host "   Claude Code Version: " -NoNewline
try {
    $claudeVersion = claude --version 2>$null
    Write-Host $claudeVersion -ForegroundColor Green
} catch {
    Write-Host "NOT INSTALLED" -ForegroundColor Red
}

Write-Host ""
Write-Host "2. Plugin Configuration..." -ForegroundColor Yellow
Write-Host ""

# Check plugin directory
$pluginPath = "C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton"
Write-Host "   Plugin Path: " -NoNewline
if (Test-Path $pluginPath) {
    Write-Host "EXISTS" -ForegroundColor Green
    Write-Host "   Path: $pluginPath" -ForegroundColor Gray
} else {
    Write-Host "NOT FOUND" -ForegroundColor Red
}

# Check .claude folder
Write-Host "   Claude Config: " -NoNewline
if (Test-Path "$pluginPath\.claude") {
    Write-Host "EXISTS" -ForegroundColor Green
    
    if (Test-Path "$pluginPath\.claude\settings.json") {
        Write-Host "   Settings: settings.json found" -ForegroundColor Gray
    }
} else {
    Write-Host "NOT FOUND" -ForegroundColor Red
}

Write-Host ""
Write-Host "3. Environment Variables..." -ForegroundColor Yellow
Write-Host ""

# Check if CLAUDE_CODE_GIT_BASH_PATH is set
$envVar = [System.Environment]::GetEnvironmentVariable('CLAUDE_CODE_GIT_BASH_PATH', 'User')
Write-Host "   CLAUDE_CODE_GIT_BASH_PATH: " -NoNewline
if ($envVar) {
    Write-Host "SET" -ForegroundColor Green
    Write-Host "   Value: $envVar" -ForegroundColor Gray
} else {
    Write-Host "NOT SET" -ForegroundColor Red
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Test Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "To start Claude Code, use one of these commands:" -ForegroundColor Yellow
Write-Host '   $env:CLAUDE_CODE_GIT_BASH_PATH = "C:\Program Files\Git\bin\bash.exe"; claude chat' -ForegroundColor Cyan
Write-Host "   OR" -ForegroundColor Yellow
Write-Host "   Restart PowerShell and use: claude chat" -ForegroundColor Cyan
Write-Host ""
Write-Host "Navigate to your plugin first:" -ForegroundColor Yellow
Write-Host '   cd "C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton"' -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
