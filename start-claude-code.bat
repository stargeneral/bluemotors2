@echo off
REM Claude Code Startup Script for Blue Motors Southampton Plugin
REM This script starts Claude Code in the correct directory with proper environment

echo ================================================
echo Starting Claude Code for Blue Motors Southampton
echo ================================================
echo.

REM Set Git Bash path for Claude Code
set CLAUDE_CODE_GIT_BASH_PATH=C:\Program Files\Git\bin\bash.exe

REM Navigate to plugin directory
cd /d "C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton"

echo Current Directory:
cd
echo.
echo Starting Claude Code...
echo ================================================
echo.
echo Tips:
echo - Type 'exit' to quit Claude Code
echo - Use 'claude help' for available commands
echo - Your conversation will be saved automatically
echo.
echo ================================================

REM Start Claude Code
claude chat

pause
