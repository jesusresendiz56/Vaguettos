<?php
// conexion 1 de base de datos (form 1 tabla registro)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vaguettos";

// Create connection 1 (registro)
$conn = mysqli_connect(hostname:$servername, username:$username, password:$password,database:$dbname);

if (!$conn) {
    die("fallo la conexion: <br>" . mysqli_connect_error());
}
   // echo "Conexión exitosa.";
   