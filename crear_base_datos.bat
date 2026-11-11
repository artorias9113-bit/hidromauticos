@echo off
REM ==============================================================================
REM Script para crear/restaurar la Base de Datos de Hidromaticos Alexis Navas
REM ==============================================================================
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup_database.ps1"
pause
