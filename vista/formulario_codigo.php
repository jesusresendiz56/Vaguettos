<?php $correo = $_GET['correo'] ?? ''; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Verificar código</title>
  <link rel="stylesheet" href="../scr/css/estiloslogin.css" />
</head>
<body class="container">
  <div class="form-section">
    <h2>Verificar código</h2>
    <form method="POST" action="../controlador/verificar_codigo.php">
      <input type="hidden" name="correo" value="<?php echo htmlspecialchars($correo); ?>">
      <label for="codigo">Código de verificación</label>
      <input type="text" name="codigo" required>

      <label for="nueva_clave">Nueva contraseña</label>
      <input type="password" name="nueva_clave" required>

      <button type="submit">Cambiar contraseña</button>
    </form>
  </div>
</body>
</html>
