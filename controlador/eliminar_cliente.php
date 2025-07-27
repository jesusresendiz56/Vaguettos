<?php
include '../modelo/conexion.php';     // conexión local ($conn)
include '../modelo/conexion2.php';    // conexión remota ($conn2)

// Cambiar aquí para usar la conexión deseada
$db = $conn2;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $sql = "DELETE FROM usuarios WHERE id_usuario = $id";

    if ($db->query($sql) === TRUE) {
        header("Location: ../vista/usuarios.php");
        exit();
    } else {
        echo "Error al eliminar el cliente: " . $db->error;
    }
} else {
    header("Location: ../vista/usuarios.php");
    exit();
}
?>
