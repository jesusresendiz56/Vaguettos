<?php
session_start();
require_once '../modelo/conexion2.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['usuario_correo']) && !empty($_POST['clave'])) {
        $input = trim($_POST['usuario_correo']);
        $clave = $_POST['clave'];

        // Admin predeterminado (sin hash)
        if ($input === 'jesus' && $clave === 'resendiz123') {
            $_SESSION['id_usuario'] = 0;
            $_SESSION['usuario'] = 'jesus';
            $_SESSION['nombre_completo'] = 'Administrador Jesús Resendiz';
            $_SESSION['rol'] = 'admin';
            header("Location: ../vista/indexadmin.php");
            exit;
        }

        // Usuario en la base de datos
        $stmt = $conn2->prepare("SELECT id_usuario, usuario, correo, clave, secret FROM usuarios WHERE usuario = ? OR correo = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn2->error);
        }

        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_usuario, $usuario, $correo, $clave_hash, $secret);
            $stmt->fetch();

            if (password_verify($clave, $clave_hash)) {
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['correo'] = $correo;
                $_SESSION['secret'] = $secret;
                $_SESSION['rol'] = 'usuario';

                header("Location: ../vista/verificacion.php");
                exit;
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
    exit;
}

$conn2->close();
?>
