<?php
session_start();
require_once '../pdo_2fa/GoogleAuthenticator.php';

if (!isset($_SESSION['secret'])) {
    echo "❌ No se ha encontrado el secreto para verificar.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_ingresado = $_POST['codigo_2fa'] ?? '';

    $ga = new PHPGangsta_GoogleAuthenticator();

    if ($ga->verifyCode($_SESSION['secret'], $codigo_ingresado, 2)) {
        // Código correcto: aquí guardas el secreto en la BD para ese usuario
        // Ejemplo: guardar $_SESSION['secret'] en tabla usuarios para $user_id

        // Luego confirmas activación 2FA para el usuario
        echo "✅ 2FA activado correctamente.";

        // Redirige o muestra dashboard
        // header("Location: ../vista/dashboard.php");
        exit();
    } else {
        echo "❌ Código incorrecto. Intenta de nuevo.";
        // Redirigir con error o mostrar formulario otra vez
        // header("Location: activar_2fa.php?error=codigo");
        exit();
    }
} else {
    header("Location: activar_2fa.php");
    exit();
}
