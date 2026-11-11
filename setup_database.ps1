# Script de inicialización de Base de Datos para Hidromáticos Alexis Navas

$mysqlBin = "C:\xamppp\mysql\bin\mysql.exe"
$mysqldBin = "C:\xamppp\mysql\bin\mysqld.exe"
$myIni = "C:\xamppp\mysql\bin\my.ini"

if (-not (Test-Path $mysqlBin)) {
    if (Test-Path "C:\xampp\mysql\bin\mysql.exe") {
        $mysqlBin = "C:\xampp\mysql\bin\mysql.exe"
        $mysqldBin = "C:\xampp\mysql\bin\mysqld.exe"
        $myIni = "C:\xampp\mysql\bin\my.ini"
    } else {
        Write-Error "No se encontro la instalacion de MySQL en C:\xamppp ni en C:\xampp."
        exit 1
    }
}

Write-Host "[1/3] Verificando estado de MySQL..." -ForegroundColor Cyan

# Comprobar si responde
$test = & $mysqlBin -u root -e "SELECT 1;" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[INFO] MySQL no esta respondiendo. Iniciando servidor mysqld..." -ForegroundColor Yellow
    Start-Process -FilePath $mysqldBin -ArgumentList "--defaults-file=$myIni", "--standalone" -WindowStyle Hidden
    Start-Sleep -Seconds 3
} else {
    Write-Host "[OK] MySQL esta activo y respondiendo." -ForegroundColor Green
}

Write-Host "[2/3] Creando base de datos e importando tablas..." -ForegroundColor Cyan
$sqlFile1 = "$PSScriptRoot\database.sql"
Get-Content $sqlFile1 | & $mysqlBin -u root
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Base de datos 'hidromaticos_db' creada con exito." -ForegroundColor Green
} else {
    Write-Error "Error al ejecutar database.sql"
    exit 1
}

Write-Host "[3/3] Insertando catalogo y datos iniciales..." -ForegroundColor Cyan
$sqlFile2 = "$PSScriptRoot\datos_prueba.sql"
if (Test-Path $sqlFile2) {
    Get-Content $sqlFile2 | & $mysqlBin -u root
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Datos de prueba cargados correctamente." -ForegroundColor Green
    }
}

Write-Host "`n=== PROCESO COMPLETADO EXITOSAMENTE ===" -ForegroundColor Green
