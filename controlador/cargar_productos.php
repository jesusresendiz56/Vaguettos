<?php
$conexion = new mysqli("gondola.proxy.rlwy.net", "root", "DxrJBPoUPakOytrQMCKfRsjQfjYCwgXd", "railway", 34046  );

if ($conexion->connect_error) {
  die("Conexión fallida: " . $conexion->connect_error);
}

$sql = "SELECT * FROM productos";
$resultado = $conexion->query($sql);

$productos = [];
while ($fila = $resultado->fetch_assoc()) {
  $productos[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($productos);

$conexion->close();
?>
