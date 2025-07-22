<?php
session_start(); // Iniciar sesión
require '../modelo/conexion.php'; // Asegúrate de tener una conexión activa

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_correo = $_POST['usuario_correo'];
    $contraseña = $_POST['contraseña'];

    // Validar campos vacíos
    if (empty($usuario_correo) || empty($contraseña)) {
        die("❌ Por favor, completa todos los campos.");
    }

    // Buscar al usuario por nombre de usuario o correo
    $stmt = $conn->prepare("SELECT id_usuario, nombre_completo, usuario, correo, contraseña FROM usuarios WHERE usuario = ? OR correo = ?");
    $stmt->bind_param("ss", $usuario_correo, $usuario_correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificar contraseña
        if (password_verify($contraseña, $usuario['contraseña'])) {
            // Guardar datos en sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
            $_SESSION['usuario'] = $usuario['usuario'];

            // Redirigir al panel principal o inicio
            header("Location: ../vista/inicio.php");
            exit();
        } else {
            echo "❌ Contraseña incorrecta.";
        }
    } else {
        echo "❌ Usuario o correo no encontrado.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acceso no autorizado.";
}
?>
