<?php
session_start();
require_once '../pdo_2fa/GoogleAuthenticator.php';

// Validar que el usuario esté logueado y tenga el secreto 2FA generado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['secret'])) {
    echo "❌ Acceso no autorizado o falta de datos de sesión.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_ingresado = $_POST['codigo'] ?? '';

    // Verificar el código con la librería
    $ga = new PHPGangsta_GoogleAuthenticator();
    $es_valido = $ga->verifyCode($_SESSION['secret'], $codigo_ingresado, 2); // +/- 2x30 segundos de tolerancia

    if ($es_valido) {
        // ✅ Código correcto, marcar como verificado en la sesión
        $_SESSION['verificado_2fa'] = true;

        // Redirigir al dashboard o index
        header("Location: ../vista/index.php");
        exit();
    } else {
        // ❌ Código incorrecto, redirigir con error
        header("Location: ../vista/verificar_2fa.php?error=codigo");
        exit();
    }
} else {
    // ❌ Método no permitido, redirigir al formulario
    header("Location: ../vista/verificar_2fa.php");
    exit();
}
