<?php
// Database configuration
$dbHost = '127.0.0.1';
$dbName = 'phpcrudd';
$dbUser = 'root';
$dbPass = 'root';  // Ajusta aquí si tu XAMPP tiene contraseña para root
$dbCharset = 'utf8mb4';

$db_error_message = '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $conn = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Guardar el mensaje de error para diagnóstico y evitar terminar el script
    $db_error_message = 'Error de conexión a la base de datos: ' . $e->getMessage();
    $conn = null;
}

?>
