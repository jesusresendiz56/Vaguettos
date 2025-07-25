<?php
// conexión a base de datos remota (Railway)
$host = 'trolley.proxy.rlwy.net';
$port = 49388;
$user = 'root';
$pass = 'OSjIyZStEWbFPNPMCcKZEMuPIxNnyjNL';
$dbname = 'railway';

$conn2 = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn2->connect_error) {
    die('Error de conexión a Railway (' . $conn2->connect_errno . ') ' . $conn2->connect_error);
}
?>
