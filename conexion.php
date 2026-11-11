<?php
/**
 * ==============================================================================
 * Sistema de Información para el Control de Inventario y Gestión Comercial
 * Empresa: Hidromáticos Alexis Navas
 * Módulo: Conexión Segura a Base de Datos (PDO)
 * ==============================================================================
 * 
 * Implementación de conexión basada en PDO con buenas prácticas de seguridad:
 * 1. Variables de entorno (getenv) para proteger credenciales.
 * 2. Manejo estricto de excepciones (try / catch) con PDOException.
 * 3. Consultas preparadas reales (ATTR_EMULATE_PREPARES => false).
 * 4. Ocultamiento de información sensible ante el cliente final (log seguro).
 */

// Carga de variables de entorno con valores por defecto para entorno local
$dbHost     = getenv('DB_HOST')     ?: 'localhost';
$dbPort     = getenv('DB_PORT')     ?: '3306';
$dbName     = getenv('DB_NAME')     ?: 'hidromaticos_db';
$dbUser     = getenv('DB_USER')     ?: 'root';
$dbPassword = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$dbCharset  = getenv('DB_CHARSET')  ?: 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

// Opciones recomendadas de PDO para máxima seguridad y rendimiento
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza PDOException ante fallos
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Prepared statements nativos en el motor MySQL
];

try {
    // Instanciación del objeto PDO
    $pdo = new PDO($dsn, $dbUser, $dbPassword, $opciones);
} catch (PDOException $e) {
    // Registro del error en el log del servidor sin exponer datos sensibles al cliente
    error_log("[ERROR BD - Hidromáticos Alexis Navas] " . $e->getMessage());

    // Detención con mensaje controlado
    die("Error de conexión: No se pudo conectar a la base de datos. Por favor verifique la configuración o contacte al administrador.");
}

/**
 * Función helper para reutilizar la conexión en los distintos controladores y módulos
 * 
 * @return PDO Instancia activa de conexión
 */
function getConexion(): PDO {
    global $pdo;
    return $pdo;
}
