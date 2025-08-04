<?php
require_once '../modelo/conexion2.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST["correo"];
    $codigo = $_POST["codigo"];
    $nueva_clave = $_POST["nueva_clave"];

    // Validar formato del código (6 dígitos)
    if (!preg_match('/^\d{6}$/', $codigo)) {
        echo "Formato de código inválido.";
        exit();
    }

    // Buscar código y expiración en DB
    $stmt = $conn2->prepare("SELECT expiracion_codigo FROM usuarios WHERE correo = ? AND codigo_recuperacion = ?");
    $stmt->bind_param("ss", $correo, $codigo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($fila = $res->fetch_assoc()) {
        if (strtotime($fila["expiracion_codigo"]) >= time()) {
            // Hashear la nueva contraseña
            $clave_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);

            // Actualizar contraseña y eliminar código
            $update = $conn2->prepare("UPDATE usuarios SET clave = ?, codigo_recuperacion = NULL, expiracion_codigo = NULL WHERE correo = ?");
            $update->bind_param("ss", $clave_hash, $correo);
            $update->execute();

            // Redirigir al login con mensaje
            header("Location: ../vista/login.php?mensaje=clave_actualizada");
            exit();
        } else {
            echo "El código ha expirado.";
        }
    } else {
        echo "Código incorrecto.";
    }
} else {
    echo "Método no permitido.";
}
