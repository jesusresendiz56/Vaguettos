<?php
ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);
session_set_cookie_params(604800);
session_start();
include '../modelo/conexion2.php';

$usarRemoto = true;
$db = $usarRemoto ? $conn2 : $conn;

$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_producto <= 0) die("ID de producto inválido.");

$stmt = $db->prepare("SELECT p.*, c.nombre AS categoria_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.id_producto = ?");
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) die("Producto no encontrado.");

$producto = $resultado->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Detalle - <?= htmlspecialchars($producto['nombre']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../scr/css/detalle_producto.css" />
</head>
<body>

<header>
  <a href="#" class="logo">
    <img src="../scr/imagenes/logo.jpg" alt="Logo" />
    <h2>Vaguettos</h2>
  </a>
  <nav>
    <a href="index.php">Inicio</a>
    <a href="catalogo.php">Catálogo</a>
    <a href="#">Carrito</a>
    <a href="#">Perfil</a>
    <a href="cerrarSesion.html">Cerrar Sesión</a>
  </nav>
</header>

<main>
  <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
  <img src="../scr/imagenes/productos/<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
  <p><strong>Categoría:</strong> <?= htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría') ?></p>
  <p><strong>Modelo Auto:</strong> <?= htmlspecialchars($producto['modelo_auto']) ?></p>
  <p><strong>Tipo:</strong> <?= htmlspecialchars($producto['tipo']) ?></p>
  <p><strong>Años aplicables:</strong> <?= htmlspecialchars($producto['years_aplicables']) ?></p>
  <p><strong>Precio unitario:</strong> $<?= number_format($producto['precio'], 2) ?></p>
  <p><strong>Stock disponible:</strong> <?= intval($producto['stock']) ?></p>

  <h3>Descripción</h3>
  <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

  <div>
    <label for="cantidad"><strong>Cantidad a comprar:</strong></label>
    <input type="number" id="cantidad" min="1" max="<?= intval($producto['stock']) ?>" value="1" />
  </div>

  <p><strong>Total a pagar:</strong> $<span id="total">0.00</span></p>

  <div id="paypal-button-container" style="margin-top: 20px;"></div>
  <p><a href="catalogo.php">&larr; Volver al catálogo</a></p>
</main>

<script src="https://www.paypal.com/sdk/js?client-id=Ad8eprBV7VKf8Z3Fvb5SaW7dYymWDNJDE5zdhVgJ_hr1B8UTP5NAZrJKFXSt8uTzYgtdJEKG8cshD7jL"></script>
<script>
const cantidadInput = document.getElementById("cantidad");
const totalSpan = document.getElementById("total");
const precioUnitario = <?= floatval($producto['precio']) ?>;
const stock = <?= intval($producto['stock']) ?>;
const idProducto = <?= $id_producto ?>;

function actualizarTotal() {
  const cantidad = parseInt(cantidadInput.value) || 0;
  const total = cantidad * precioUnitario;
  totalSpan.innerText = total.toFixed(2);
}
cantidadInput.addEventListener("input", actualizarTotal);
actualizarTotal();

paypal.Buttons({
  createOrder: function(data, actions) {
    const cantidad = parseInt(cantidadInput.value);
    if (cantidad <= 0 || cantidad > stock) {
      alert("Cantidad inválida. Debe ser mayor a 0 y no exceder el stock.");
      return;
    }
    const total = (cantidad * precioUnitario).toFixed(2);
    return actions.order.create({
      purchase_units: [{
        amount: { value: total }
      }]
    });
  },
  onApprove: function(data, actions) {
    return actions.order.capture().then(function(details) {
      const cantidad = parseInt(cantidadInput.value);

      fetch('generar_ticket2.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idProducto, cantidad: cantidad })
      })
      .then(response => {
        if (!response.ok) throw new Error("Error al generar ticket");
        return response.blob();
      })
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = "ticket_compra.pdf";
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
      })
      .catch(err => {
        alert(err.message);
      });
    });
  },
  onCancel: function(data) {
    alert("Pago cancelado.");
  }
}).render('#paypal-button-container');
</script>

</body>
</html>
