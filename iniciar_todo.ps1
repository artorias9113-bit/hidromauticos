# Script maestro de inicio para Hidromáticos Alexis Navas
# Inicia MySQL si está apagado, valida la base de datos y lanza el servidor web en http://localhost:8000

$ErrorActionPreference = "Continue"

Write-Host "==============================================================================" -ForegroundColor DarkCyan
Write-Host "     SISTEMA DE GESTIÓN COMERCIAL - HIDROMÁTICOS ALEXIS NAVAS 2016 C.A.       " -ForegroundColor Cyan
Write-Host "==============================================================================" -ForegroundColor DarkCyan
Write-Host ""

$mysqlBin = "C:\xamppp\mysql\bin\mysql.exe"
$mysqldBin = "C:\xamppp\mysql\bin\mysqld.exe"
$myIni = "C:\xamppp\mysql\bin\my.ini"
$phpBin = "C:\xamppp\php\php.exe"

# Fallback a C:\xampp si C:\xamppp no existe
if (-not (Test-Path $mysqlBin)) {
    $mysqlBin = "C:\xampp\mysql\bin\mysql.exe"
    $mysqldBin = "C:\xampp\mysql\bin\mysqld.exe"
    $myIni = "C:\xampp\mysql\bin\my.ini"
    $phpBin = "C:\xampp\php\php.exe"
}

# 1. VERIFICAR / INICIAR MYSQL
Write-Host "[1/3] Comprobando conexión a MySQL..." -NoNewline
$testMySQL = & $mysqlBin -u root -e "SELECT 1;" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host " [INACTIVO]" -ForegroundColor Yellow
    Write-Host "      Iniciando motor de base de datos MySQL..." -ForegroundColor Yellow
    Start-Process -FilePath $mysqldBin -ArgumentList "--defaults-file=$myIni", "--standalone" -WindowStyle Hidden
    Start-Sleep -Seconds 3
    
    # Re-probar
    $testRetry = & $mysqlBin -u root -e "SELECT 1;" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "      [OK] MySQL iniciado correctamente en segundo plano." -ForegroundColor Green
    } else {
        Write-Host "      [ADVERTENCIA] Si MySQL no responde, abra XAMPP Control Panel y presione 'Start' en MySQL." -ForegroundColor Red
    }
} else {
    Write-Host " [ACTIVO - OK]" -ForegroundColor Green
}

# 2. VALIDAR EXISTENCIA DE LA BASE DE DATOS
Write-Host "[2/3] Verificando base de datos 'hidromaticos_db'..." -NoNewline
$checkDb = & $mysqlBin -u root -e "USE hidromaticos_db; SELECT COUNT(*) FROM repuesto;" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host " [NO ENCONTRADA]" -ForegroundColor Yellow
    Write-Host "      Creando esquema y cargando catálogo..." -ForegroundColor Yellow
    & powershell.exe -ExecutionPolicy Bypass -File "$PSScriptRoot\setup_database.ps1"
} else {
    Write-Host " [ENCONTRADA Y POBLADA - OK]" -ForegroundColor Green
}

# 3. LANZAR SERVIDOR PHP Y ABRIR NAVEGADOR
Write-Host "[3/3] Iniciando Servidor Web PHP en http://localhost:8000 ..." -ForegroundColor Cyan
Write-Host ""
Write-Host "------------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " ✓ Punto de Venta: http://localhost:8000/index.php" -ForegroundColor White
Write-Host " ✓ Inventario:     http://localhost:8000/inventario.php" -ForegroundColor White
Write-Host "------------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " Abriendo el sistema en su navegador predeterminado..." -ForegroundColor Yellow
Write-Host " (Para detener el servidor, cierre esta ventana o presione Ctrl + C)" -ForegroundColor DarkGray
Write-Host ""

Start-Process "http://localhost:8000/index.php"

# Iniciar servidor PHP integrado en el directorio actual
Set-Location $PSScriptRoot
& $phpBin -S localhost:8000
