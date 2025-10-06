# Claude Code Startup Script for Blue Motors Southampton Plugin
# This script starts Claude Code in the correct directory with proper environment

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Starting Claude Code for Blue Motors Southampton" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Set Git Bash path for Claude Code
$env:CLAUDE_CODE_GIT_BASH_PATH = "C:\Program Files\Git\bin\bash.exe"

# Navigate to plugin directory
Set-Location "C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton"

Write-Host "Current Directory:" -ForegroundColor Yellow
Get-Location
Write-Host ""
Write-Host "Starting Claude Code..." -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Tips:" -ForegroundColor Yellow
Write-Host "- Type 'exit' to quit Claude Code" -ForegroundColor Gray
Write-Host "- Use 'claude help' for available commands" -ForegroundColor Gray
Write-Host "- Your conversation will be saved automatically" -ForegroundColor Gray
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Start Claude Code
claude chat
