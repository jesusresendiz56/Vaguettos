<?php
session_start();
require_once '../pdo_2fa/GoogleAuthenticator.php';

if (!isset($_SESSION['secret']) || empty($_SESSION['secret'])) {
    echo "❌ No se ha encontrado el secreto para verificar.";
    exit();
}

$ga = new PHPGangsta_GoogleAuthenticator();
$codigo_ingresado = $_POST['codigo_2fa'] ?? '';

if ($ga->verifyCode($_SESSION['secret'], $codigo_ingresado, 2)) {
    echo "✅ Código verificado correctamente. Bienvenido, " . $_SESSION['usuario'];
    // Aquí podrías redirigir al dashboard
    // header("Location: ../vista/dashboard.php");
    exit();
} else {
    echo "❌ Código incorrecto. Intenta de nuevo.";
    // Podrías redirigir otra vez al formulario
    // header("Location: ../vista/verificar_2fa.php?error=1");
}
?>
