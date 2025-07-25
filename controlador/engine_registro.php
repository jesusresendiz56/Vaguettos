<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../modelo/conexion.php';
require_once '../modelo/conexion2.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verifica que se enviaron ambos campos
    if (!empty($_POST['usuario']) && !empty($_POST['password'])) {
        $usuario = trim($_POST['usuario']); // elimina espacios en blanco
        $password = $_POST['password'];

        // Consulta segura con prepared statements
        $stmt = $conn->prepare("SELECT id_usuario, contraseña, secret FROM usuarios WHERE usuario = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_usuario, $hash, $secret);
            $stmt->fetch();

            // Verifica la contraseña
            if (password_verify($password, $hash)) {
                // Asigna variables de sesión
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['secret'] = $secret;

                // Redirección segura
                header("Location: ../vista/verificar_2fa.php");
                exit();
            } else {
                echo "Contraseña incorrecta.";
            }
        } else {
            echo "Usuario no encontrado.";
        }

        $stmt->close();
    } else {
        echo "Datos incompletos. Por favor, llena ambos campos.";
    }
}

$conn->close();
?>
