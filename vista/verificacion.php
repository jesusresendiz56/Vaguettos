<?php
session_start();
require '../pdo_2fa/GoogleAuthenticator.php';

if (!isset($_SESSION['secret'])) {
    echo "❌ No se ha encontrado el secreto.";
    exit();
}

$ga = new PHPGangsta_GoogleAuthenticator(); 
$qrCodeUrl = $ga->getQRCodeGoogleUrl('Vaguettos', $_SESSION['secret']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Configurar 2FA</title>
</head>
<body>
  <h2>Configurar Verificación en Dos Pasos</h2>
  <p>Escanea este código QR con Google Authenticator:</p>
  <img src="<?php echo $qrCodeUrl; ?>" alt="Código QR 2FA">

  <p>Después, ingresa el código que aparece en la app:</p>
  <form method="POST" action="../controlador/verificar_2fa.php">
    <input type="text" name="codigo_2fa" placeholder="Código 2FA" required>
    <button type="submit">Verificar</button>
  </form>
</body>
</html>
