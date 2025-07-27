<?php
include '../modelo/conexion.php';
//include '../modelo/conexion2.php';

// Cambia aquí para usar la conexión deseada:
$db = $conn; // por ejemplo, conexión remota
// $db = $conn; // conexión local

if (isset($_POST['actualizar'])) {
    $id = (int) $_POST['id_usuario'];
    $usuario = $db->real_escape_string($_POST['usuario']);
    $direccion = $db->real_escape_string($_POST['direccion']);
    $correo = $db->real_escape_string($_POST['correo']);
    $telefono = $db->real_escape_string($_POST['telefono']);

    $sql = "UPDATE usuarios SET usuario='$usuario', direccion='$direccion', correo='$correo', telefono='$telefono' WHERE id_usuario=$id";

    if ($db->query($sql) === TRUE) {
        header("Location: ../vista/usuarios.php");
        exit();
    } else {
        echo "Error al actualizar el cliente: " . $db->error;
    }
} else {
    header("Location: ../vista/usuarios.php");
    exit();
}
?>
