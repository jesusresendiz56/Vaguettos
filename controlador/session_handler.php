<?php
// Configuración para sesiones con expiración de 5 minutos
ini_set('session.gc_maxlifetime', 300); 
session_set_cookie_params(300); 
session_start();

// Tiempo máximo de inactividad
$tiempo_max_inactivo = 300;

if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > $tiempo_max_inactivo) {
        session_unset();
        session_destroy();
        header("Location: ../vista/login.php?expirado=1");
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time();

$usuario = $_SESSION['usuario'] ?? null;
