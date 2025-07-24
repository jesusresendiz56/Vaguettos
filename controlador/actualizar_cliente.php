<?php


include '../modelo/conexion.php';


if (isset($_POST['actualizar'])) {
   
    $id = (int) $_POST['id_usuario'];
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $correo = $conn->real_escape_string($_POST['correo']);
    $telefono = $conn->real_escape_string($_POST['telefono']);

    
    $sql = "UPDATE usuarios SET usuario='$usuario', direccion='$direccion', correo='$correo', telefono='$telefono' WHERE id_usuario=$id";

    if ($conn->query($sql) === TRUE) {
        
        header("Location: ../vista/usuarios.php");
        exit();
    } else {
       
        echo "Error al actualizar el cliente: " . $conn->error;
    }
} else {
    
    header("Location: ../vista/usuarios.php");
    exit();
}
?>
