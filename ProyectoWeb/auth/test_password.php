<?php
/**
 * Script de prueba para contraseñas de usuario
 * Úsalo para:
 * 1. Verificar qué contraseña corresponde a cada hash
 * 2. Generar nuevos hashes si necesitas resetear contraseñas
 */

// Hashes originales del SQL
$hashes = [
    'admin@hotmail.com' => '$2y$10$01Ijmpp3pKdfdGGyTywi.OT1L1HokM.oXK.gG0BFsX.m5amCR7VHy',
    'secretaria1@gmail.com' => '$2y$10$WyIolwWRcX24o9Z.tYheM.d4NVIMu9AitsUp2XY0J5EMBL5fHrzHm'
];

// Contraseñas comunes para probar
$passwords_to_test = ['admin', 'password', '123456', 'admin123', 'secretaria', '1234', '12345678'];

echo "<h2>Probando contraseñas contra hashes existentes:</h2>";
foreach ($hashes as $email => $hash) {
    echo "<h3>Email: $email</h3>";
    echo "<ul>";
    foreach ($passwords_to_test as $pwd) {
        $match = password_verify($pwd, $hash) ? '✓ COINCIDE' : '✗ No coincide';
        echo "<li><strong>$pwd</strong>: $match</li>";
    }
    echo "</ul>";
}

// Generar hashes para contraseñas simples (para reseteo)
echo "<h2>Hashes generados (si necesitas actualizar contraseñas):</h2>";
$new_passwords = ['admin' => 'admin', 'secretaria' => 'secretaria123'];
foreach ($new_passwords as $user => $pwd) {
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    echo "<p><strong>Usuario: $user | Contraseña: $pwd</strong><br>";
    echo "Hash: <code>$hash</code></p>";
}
?>