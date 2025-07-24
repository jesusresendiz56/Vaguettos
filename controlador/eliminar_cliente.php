<?php
include '../modelo/conexion.php';

// Validar que se recibió el ID
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Consulta para eliminar el usuario por id
    $sql = "DELETE FROM usuarios WHERE id_usuario = $id";

    if ($conn->query($sql) === TRUE) {
        // Redirigir al listado tras eliminar
        header("Location: ../vista/usuarios.php");
        exit();
    } else {
        echo "Error al eliminar el cliente: " . $conn->error;
    }
} else {
    // Si no se recibió id, redirigir al listado
    header("Location: ../vista/usuarios.php");
    exit();
}
?>
