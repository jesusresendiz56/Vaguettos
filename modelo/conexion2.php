<?php
// conexión a base de datos remota (Railway)
$host = 'trolley.proxy.rlwy.net';
$port = 49255;
$user = 'root';
$pass = 'fdKPJgcpqXsiSRUcwWBYWXYIbzaaZKyy';
$dbname = 'railway';

$conn2 = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn2->connect_error) {
    die('Error de conexión a Railway (' . $conn2->connect_errno . ') ' . $conn2->connect_error);
}
?>
