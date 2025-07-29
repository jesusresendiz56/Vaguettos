<?php

$host = "gondola.proxy.rlwy.net";
$puerto = 34046;
$usuario = "root";
$contrasena = "DxrJBPoUPakOytrQMCKfRsjQfjYCwgXd";
$base_datos = "railway";

$conexion = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

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
