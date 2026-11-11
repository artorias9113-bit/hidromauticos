@echo off
title Subir Proyecto a GitHub - Hidromáticos Alexis Navas
echo ==============================================================================
echo     SUBIENDO PROYECTO A GITHUB: artorias9113-bit/hidromauticos
echo ==============================================================================
echo.
echo Ejecutando: git push -u origin main ...
echo.

git push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ==============================================================================
    echo  [EXITO] ¡PROYECTO SUBIDO SATISFACTORIAMENTE A GITHUB!
    echo ==============================================================================
    echo Puedes revisar tu repositorio en:
    echo https://github.com/artorias9113-bit/hidromauticos
) else (
    echo.
    echo ==============================================================================
    echo  [AVISO] Si te pide usuario y contrasena:
    echo  - Usuario: artorias9113-bit
    echo  - Contrasena: Debes usar un Personal Access Token (PAT) de GitHub
    echo    (Puedes generarlo en: https://github.com/settings/tokens)
    echo ==============================================================================
)

echo.
pause
