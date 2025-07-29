<?php
// --------------------- SESIÓN E INACTIVIDAD ---------------------
ini_set('session.gc_maxlifetime', 300);
session_set_cookie_params(300);
session_start();

$tiempo_max_inactivo = 300;

if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > $tiempo_max_inactivo) {
        session_unset();
        session_destroy();
        header("Location: login.php?expirado=1");
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time();
$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Vaguettos Accesorios</title>
  <link rel="stylesheet" href="../scr/css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>

<!-- =================== ENCABEZADO =================== -->
 
<header>
  <div class="top-bar">
    <img src="../scr/imagenes/logo.jpg" alt="Vaguettos Logo" class="logo">
    <p>Vaguettos</p>

    <?php if ($usuario): ?>
      <form method="POST" action="../controlador/logout.php" style="display:inline;">
        <button type="submit" class="btn-login">
          <?= htmlspecialchars($usuario); ?> (Cerrar sesión)
        </button>
      </form>
    <?php else: ?>
      <a href="login.php" class="btn-login">Iniciar Sesión</a>
    <?php endif; ?>
  </div>

  <!-- Menú principal -->

  <nav class="main-nav">
    <a href="index.php">Inicio</a>
    <a href="catalogo.php">Catálogo de Productos</a>
    <a href="carrito.php">Carrito de Compras</a>
    <a href="#">Editar Perfil</a>
  </nav>
</header>

<!-- =================== BANNER PRINCIPAL =================== -->

<section class="banner">
  <h1>“Todo Para Tu Auto, Cuando Y<br>Donde Quieras”</h1>
</section>

<!-- =================== PRODUCTOS DESTACADOS =================== -->

<section class="productos">
  <h2>Productos Destacados</h2>
  <div class="cards">

    
    <div class="card">
      <span class="categoria">Rines</span>
      <img src="../scr/producto1.jpg" alt="Producto 1">
      <p class="precio">$650</p>
    </div>

    <div class="card">
      <span class="categoria">Cámaras</span>
      <img src="../scr/producto2.jpg" alt="Producto 2">
      <p class="precio">$850</p>
    </div>

    <div class="card">
      <span class="categoria">Asientos</span>
      <img src="../scr/producto3.jpg" alt="Producto 3">
      <p class="precio">$990</p>
    </div>

    <div class="card">
      <span class="categoria">Parrillas</span>
      <img src="../scr/producto4.jpg" alt="Producto 4">
      <p class="precio">$899</p>
    </div>
  </div>
</section>

<!-- =================== MARCAS DESTACADAS =================== -->

<section class="marcas">
  <h2>Marcas Destacadas</h2>
  <div class="marca-botones">

  

    <button>Jetta</button>
    <button>Golf</button>
    <button>Tiguan</button>
    <button>Beetle</button>
    <button>Polo</button>
  </div>
</section>

<!-- =================== SERVICIOS Y UBICACIÓN =================== -->

<section class="info">
  <div class="servicios">
    <h3>Nuestros Servicios</h3>
    <ul>
      <li>Compra en Línea rapidá y segura</li>
      <li>Servicio técnico especializado a distancia</li>
      <li>Envío a domicilio</li>
      <li>Garantía de satisfacción en cada pedido</li>
    </ul>
  </div>

  <div class="ubicacion">
    <h3>Ubicación</h3>
    
    <div id="mapa"></div>
  </div>
</section>

<!-- =================== REDES SOCIALES =================== -->

<section class="redes">
  <h3>Nuestras Redes</h3>
  <div class="iconos">
    <span><i class="fab fa-facebook-f"></i></span>
    <span><i class="fab fa-instagram"></i></span>
    <span><i class="fab fa-tiktok"></i></span>
  </div>
</section>

<!-- =================== FOOTER =================== -->

<footer class="footer">
  <div class="footer-links">
    <a href="#">Contacto</a>
    <a href="#">Servicios al Cliente</a>
    <a href="#">Información</a>
  </div>
  <p>&copy; 2025 VAGUETTOS</p>
</footer>

<!-- =================== SCRIPT DE SESIÓN =================== -->
<script>
  let tiempoInactivo = 0;
  const limite = 300;

  function resetInactividad() {
    tiempoInactivo = 0;
  }

  ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt =>
    window.addEventListener(evt, resetInactividad)
  );

  setInterval(() => {
    tiempoInactivo++;
    if (tiempoInactivo === (limite - 60)) {
      alert("⚠️ Tu sesión está por expirar en 1 minuto por inactividad.");
    }
    if (tiempoInactivo < limite) {
      fetch("../controlador/ping.php").catch(() => {});
    } else {
      window.location.href = "sesion_expirada.php";
    }
  }, 1000);
</script>

</body>
</html>


