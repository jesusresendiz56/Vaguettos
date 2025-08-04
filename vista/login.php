<?php
session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="../scr/css/estiloslogin.css" />
</head>
<body class="container">
  <div class="form-section">
    <h2>Iniciar sesión</h2>

    <?php
    if (isset($_SESSION['error_login'])) {
        echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error_login']) . '</p>';
        unset($_SESSION['error_login']);
    }
    ?>

    <form method="POST" action="../controlador/engine_login.php">
      <label for="usuario_correo">Usuario o correo</label>
      <input type="text" id="usuario_correo" name="usuario_correo" placeholder="Usuario o correo" required />

      <label for="clave">Contraseña</label>
      <input type="password" id="clave" name="clave" placeholder="Contraseña" required />

      <button type="submit">Ingresar</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro.html">Regístrate aquí</a></p>
    <p><a href="../vista/recuperar_contraseña.html">¿Olvidaste tu contraseña?</a></p>
  </div>

  <div class="logo-section left-rounded">
    <img src="../scr/imagenes/logo.jpg" alt="Logo" class="logo" />
  </div>
</body>
</html>
