<?php
require_once __DIR__ . "/../config/config.php";
session_start();

// Verificar que exista la conexión creada en config
if (!isset($conn) || !$conn) {
    echo "<script>alert('Error interno: no se pudo conectar a la base de datos.'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario'] = $usuario;
        header("Location: http://127.0.0.1/ProyectoWeb/views/dashboard.php");
        exit();
    } else {
        echo "<script>alert('Credenciales incorrectas.'); window.location.href='login.php';</script>";
        exit();
    }
}
?>
