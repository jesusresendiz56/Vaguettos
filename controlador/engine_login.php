<?php
session_start();
require_once '../modelo/conexion2.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['usuario_correo']) && !empty($_POST['clave'])) {
        $input = trim($_POST['usuario_correo']);
        $clave = $_POST['clave'];

        // Preparar consulta para buscar usuario por usuario o correo
        $stmt = $conn2->prepare("SELECT id_usuario, usuario, correo, clave, secret FROM usuarios WHERE usuario = ? OR correo = ?");
        if (!$stmt) {
            die("Error en la preparación: " . $conn2->error);
        }
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_usuario, $usuario, $correo, $clave_guardada, $secret);
            $stmt->fetch();

            // Aquí la comparación, según si tienes hash o no
            // Por ejemplo, si no usas hash:
            if ($clave === $clave_guardada) {
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['secret'] = $secret;

                header("Location: ../vista/verificacion.php");
                exit();
            } else {
                $_SESSION['error_login'] = "Contraseña incorrecta.";
            }
        } else {
            $_SESSION['error_login'] = "Usuario o correo no encontrado.";
        }

        $stmt->close();
    } else {
        $_SESSION['error_login'] = "Por favor llena ambos campos.";
    }
    header("Location: ../vista/login.php");
    exit();
}

$conn2->close();
?>
