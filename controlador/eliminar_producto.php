<?php
// --- conexión local ---
include '../modelo/conexion.php';

// --- conexión remota (Railway) ---
 include '../modelo/conexion2.php';


$usarConexionRemota = true;

$conn = $usarConexionRemota ? $conn2 : $conn;


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_producto = intval($_GET['id']);

    
    $sql = "DELETE FROM productos WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);

    if ($stmt->execute()) {
      
        header("Location: ../vista/stock.php?mensaje=Producto eliminado correctamente"); 
        exit();
    } else {
        echo "Error al eliminar el producto: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "ID de producto no válido.";
}

$conn->close();
?>
