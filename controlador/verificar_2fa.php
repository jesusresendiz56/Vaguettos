<?php
session_start();
require_once '../pdo_2fa/GoogleAuthenticator.php';

if (!isset($_SESSION['secret'])) {
    echo "No se ha encontrado el secreto para verificar.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_ingresado = $_POST['codigo'] ?? '';

    $ga = new PHPGangsta_GoogleAuthenticator();

    $es_valido = $ga->verifyCode($_SESSION['secret'], $codigo_ingresado, 2); // tolerancia +/- 2*30s

    if ($es_valido) {
        // Código correcto, redirigir al index.php
        header("Location: ../vista/index.php");
        exit();
    } else {
        // Código incorrecto, regresar a la página de verificación con error
        header("Location: ../vista/verificar_2fa.php?error=codigo");
        exit();
    }
} else {
    header("Location: ../vista/verificar_2fa.php");
    exit();
}
