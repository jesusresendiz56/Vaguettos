<?php
// conexion 1 de base de datos (form 1 tabla registro)
$host = 'trolley.proxy.rlwy.net';
$port = 49388;
$user = 'root';
$pass = 'OSjIyZStEWbFPNPMCcKZEMuPIxNnyjNL';
$dbname = 'railway';

// Create connection 1 (registro)
$mysqli = new mysqli($host, $user, $pass, $dbname, $port);

if ($mysqli->connect_error) {
    die('Error de conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
} else {
    echo "Conexión exitosa";
}