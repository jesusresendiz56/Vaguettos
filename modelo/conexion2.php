<?php
$host = 'trolley.proxy.rlwy.net';
$port = 49388;
$user = 'root';
$pass = 'OSjIyZStEWbFPNPMCcKZEMuPIxNnyjNL';
$dbname = 'railway';

$mysqli = new mysqli($host, $user, $pass, $dbname, $port);

if ($mysqli->connect_error) {
    die('Error de conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
} else {
    echo "Conexión exitosa";
}
