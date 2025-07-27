<?php
session_start();
//require_once '../modelo/conexion.php';
require_once '../modelo/conexion2.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['nombre_completo']) && !empty($_POST['usuario']) && !empty($_POST['correo']) 
        && !empty($_POST['clave']) && !empty($_POST['direccion']) && !empty($_POST['telefono'])) {

        $nombreCompleto = trim($_POST['nombre_completo']);
        $usuario = trim($_POST['usuario']);
        $correo = trim($_POST['correo']);
        $clave = $_POST['clave'];  // **sin hash**
        $direccion = trim($_POST['direccion']);
        $telefono = trim($_POST['telefono']);

        $secret2fa = ''; // Puedes generar aquí si quieres

        $stmt = $conn2->prepare("INSERT INTO usuarios (nombre_completo, usuario, correo, clave, direccion, telefono, secret) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Error en la preparación: " . $conn2->error);
        }
        $stmt->bind_param("sssssss", $nombreCompleto, $usuario, $correo, $clave, $direccion, $telefono, $secret2fa);
        if ($stmt->execute()) {
            header("Location: ../vista/login.php");
            exit();
        } else {
            echo "Error al registrar usuario: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Por favor llena todos los campos.";
    }
}

$conn2->close();
?>
