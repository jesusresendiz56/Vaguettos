<?php
session_start();
require_once '../modelo/conexion2.php';

if (!isset($_GET['id_carrito']) || !is_numeric($_GET['id_carrito'])) {
    die("ID de carrito inválido.");
}

$id_carrito = (int)$_GET['id_carrito'];

// Primero eliminamos los productos asociados a ese carrito
$sql1 = "DELETE FROM carrito_productos WHERE id_carrito = ?";
$stmt1 = $conn2->prepare($sql1);
$stmt1->bind_param("i", $id_carrito);
$stmt1->execute();
$stmt1->close();

// Luego eliminamos el carrito
$sql2 = "DELETE FROM carritos WHERE id_carrito = ?";
$stmt2 = $conn2->prepare($sql2);
$stmt2->bind_param("i", $id_carrito);
$stmt2->execute();
$stmt2->close();

// Redirigir de vuelta a la página de administración de pagos
header("Location: ../vista/pagos.php");
exit;
?>
