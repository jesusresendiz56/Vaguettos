<?php
session_start(); // Iniciar sesión para acceder a $_SESSION
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Cambia 'usuario' por el índice correcto que usas en tu sesión
$usuario = $_SESSION['usuario'] ?? 'usuario_default@example.com';

// La clave secreta debería venir de la base de datos o ser dinámica
$secret = 'JBSWY3DPEHPK3PXP'; 

$otpauthUrl = "otpauth://totp/{$usuario}?secret={$secret}&issuer=MiApp";

$qrCode = new QrCode($otpauthUrl);

$writer = new PngWriter();
$result = $writer->write($qrCode);

$qrCodeBase64 = base64_encode($result->getString());
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Configurar 2FA</title>
</head>
<body>
  <h2>Configurar Verificación en Dos Pasos</h2>
  <p>Escanea este código QR con Google Authenticator:</p>
  <img src="data:image/png;base64,<?= $qrCodeBase64 ?>" alt="Código QR 2FA" />

  <p>Después, ingresa el código que aparece en la app:</p>
  <form method="POST" action="../vista/index.php">
    <input type="text" name="codigo" placeholder="Código 2FA" required />
    <button type="submit">Verificar</button>
  </form>

  <?php if (isset($_GET['error']) && $_GET['error'] === 'codigo'): ?>
    <p style="color:red;"> Código incorrecto. Inténtalo de nuevo.</p>
  <?php endif; ?>
</body>
</html>

