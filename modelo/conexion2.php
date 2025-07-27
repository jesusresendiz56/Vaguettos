<?php
$host = "gondola.proxy.rlwy.net";
$puerto = 34046;
$usuario = "root";
$contrasena = "DxrJBPoUPakOytrQMCKfRsjQfjYCwgXd";
$base_datos = "railway";

// Crear conexión
$conn2 = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

// Verificar conexión
if ($conn2->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo " Conexión exitosa a Railway";
}
?>



