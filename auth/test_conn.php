<?php
// Test script para verificar la conexión PDO a la base de datos
require_once __DIR__ . "/../config/config.php";

try {
    if (!isset($conn)) {
        throw new Exception('La variable $conn no está definida en config.php');
    }
    // Ejecutar una consulta simple
    $stmt = $conn->query('SELECT DATABASE() AS db');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<h3>Conexión OK</h3>';
    echo '<p>Base de datos actual: ' . htmlspecialchars($row['db']) . '</p>';
} catch (Exception $e) {
    echo '<h3>Error de conexión</h3>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
?>