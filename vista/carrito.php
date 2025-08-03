<?php
session_start();
require_once '../modelo/conexion2.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = 1; 
}

// Obtener filtros únicos para desplegables
$filtroTipos = $conn2->query("SELECT DISTINCT tipo FROM productos WHERE tipo IS NOT NULL AND tipo != ''");
$filtroModelos = $conn2->query("SELECT DISTINCT modelo_auto FROM productos WHERE modelo_auto IS NOT NULL AND modelo_auto != ''");
$filtroYears = $conn2->query("SELECT DISTINCT years_aplicables FROM productos WHERE years_aplicables IS NOT NULL AND years_aplicables != ''");

// Obtener valores de filtros seleccionados
$filtroTipo = $_GET['tipo'] ?? '';
$filtroModelo = $_GET['modelo'] ?? '';
$filtroYear = $_GET['years'] ?? '';

// Construir consulta con filtros aplicados
$sql = "SELECT * FROM productos WHERE stock > 0";
if (!empty($filtroTipo)) {
    $sql .= " AND tipo = '" . $conn2->real_escape_string($filtroTipo) . "'";
}
if (!empty($filtroModelo)) {
    $sql .= " AND modelo_auto = '" . $conn2->real_escape_string($filtroModelo) . "'";
}
if (!empty($filtroYear)) {
    $sql .= " AND years_aplicables = '" . $conn2->real_escape_string($filtroYear) . "'";
}
$sql .= " ORDER BY id_producto DESC";

$res = $conn2->query($sql);

// Obtener carrito actual para el usuario
$cartItems = [];
$id_usuario = $_SESSION['id_usuario'];
$sqlCarrito = "SELECT id_carrito FROM carritos WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 1";
$stmt = $conn2->prepare($sqlCarrito);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resCarrito = $stmt->get_result();

if ($resCarrito->num_rows > 0) {
    $carrito = $resCarrito->fetch_assoc();
    $id_carrito = $carrito['id_carrito'];

    $sqlProd = "SELECT p.id_producto, p.nombre, p.precio, cp.cantidad
                FROM carrito_productos cp
                JOIN productos p ON cp.id_producto = p.id_producto
                WHERE cp.id_carrito = ?";
    $stmt2 = $conn2->prepare($sqlProd);
    $stmt2->bind_param("i", $id_carrito);
    $stmt2->execute();
    $resProd = $stmt2->get_result();

    while ($row = $resProd->fetch_assoc()) {
        $cartItems[] = [
            'id' => (int)$row['id_producto'],
            'name' => $row['nombre'],
            'price' => (float)$row['precio'],
            'quantity' => (int)$row['cantidad'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Catálogo y Carrito - Vaguettos</title>
    <link rel="stylesheet" href="../scr/css/carrito.css" />
</head>
<body>

<nav>
    <a href="index.php" class="nav-link">Inicio</a>
    <a href="catalogo.php" class="nav-link">Catálogo de Productos</a>
    <a href="#" class="nav-link">Carrito de Compras</a>
    <a href="index.php" class="nav-link">Cerrar Sesión</a>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Catálogo de Productos</h1>
</header>

<!-- Filtros -->
<section>
    <h2>Filtrar productos</h2>
    <form method="get" action="">
        <select name="tipo" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            <?php while ($row = $filtroTipos->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['tipo']) ?>" <?= ($filtroTipo == $row['tipo']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['tipo']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="modelo" onchange="this.form.submit()">
            <option value="">Todos los modelos</option>
            <?php while ($row = $filtroModelos->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['modelo_auto']) ?>" <?= ($filtroModelo == $row['modelo_auto']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['modelo_auto']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="years" onchange="this.form.submit()">
            <option value="">Todos los años</option>
            <?php while ($row = $filtroYears->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['years_aplicables']) ?>" <?= ($filtroYear == $row['years_aplicables']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['years_aplicables']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>
</section>

<!-- Catálogo -->
<div class="product-container">
    <?php while ($row = $res->fetch_assoc()): ?>
        <div class="product-card">
            <img src="../scr/imagenes/productos/<?= htmlspecialchars($row['imagen_url'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($row['nombre']) ?>" />
            <h3><?= htmlspecialchars($row['nombre']) ?></h3>
            <p>Tipo: <?= htmlspecialchars($row['tipo']) ?></p>
            <p>Modelo: <?= htmlspecialchars($row['modelo_auto']) ?></p>
            <p>Años: <?= htmlspecialchars($row['years_aplicables']) ?></p>
            <p>Precio: $<?= number_format($row['precio'], 2) ?></p>
            <button onclick="addToCart(<?= $row['id_producto'] ?>, '<?= addslashes($row['nombre']) ?>', <?= $row['precio'] ?>)">Agregar al carrito</button>
        </div>
    <?php endwhile; ?>
</div>

<!-- Carrito -->
<div id="cart">
    <h3>🛒 Carrito</h3>
    <ul id="cart-items"></ul>
    <p>Total: $<span id="cart-total">0.00</span></p>
    <button onclick="openModal()">Finalizar compra</button>
</div>

<!-- Modal de Compra -->
<div id="checkout-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Finalizar compra</h3>
        <input type="text" id="name" placeholder="Nombre completo" required>
        <input type="text" id="phone" placeholder="Teléfono" required>
        <textarea id="address" placeholder="Dirección de entrega" required></textarea>
        <button onclick="submitOrder()">Confirmar pedido</button>
    </div>
</div>

<script>
let cartItems = <?= json_encode($cartItems) ?>;
let total = 0;

function updateCart() {
    const cartList = document.getElementById('cart-items');
    cartList.innerHTML = '';
    total = 0;
    cartItems.forEach((item, index) => {
        total += item.price * item.quantity;
        cartList.innerHTML += `
            <li>
                ${item.name} - $${item.price} x ${item.quantity} = $${(item.price * item.quantity).toFixed(2)}
                <button onclick="changeQuantity(${index}, 1)">+</button>
                <button onclick="changeQuantity(${index}, -1)">-</button>
                <button onclick="removeItem(${index})">Eliminar</button>
            </li>`;
    });
    document.getElementById('cart-total').innerText = total.toFixed(2);
}

function addToCart(id, name, price) {
    fetch('../controlador/engine_carrito.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_producto: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            let item = cartItems.find(i => i.id === id);
            if (item) {
                item.quantity++;
            } else {
                cartItems.push({ id, name, price, quantity: 1 });
            }
            updateCart();
        } else {
            alert('Error al agregar al carrito');
        }
    });
}

function changeQuantity(index, change) {
    const item = cartItems[index];
    fetch('../controlador/engine_carrito.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: change > 0 ? 'increment' : 'decrement', id_producto: item.id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            item.quantity += change;
            if (item.quantity <= 0) cartItems.splice(index, 1);
            updateCart();
        } else {
            alert('Error al actualizar el carrito');
        }
    });
}

function removeItem(index) {
    const item = cartItems[index];
    fetch('../controlador/engine_carrito.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'remove', id_producto: item.id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            cartItems.splice(index, 1);
            updateCart();
        } else {
            alert('Error al eliminar producto');
        }
    });
}

function openModal() {
    if (cartItems.length === 0) {
        alert('Tu carrito está vacío.');
        return;
    }
    document.getElementById('checkout-modal').style.display = 'block';
}

function submitOrder() {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();

    if (!name || !phone || !address) {
        alert('Completa todos los campos.');
        return;
    }

    alert(`¡Gracias por tu compra, ${name}!`);
    cartItems = [];
    total = 0;
    updateCart();
    document.getElementById('checkout-modal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('checkout-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    updateCart();
});
</script>
</body>
</html>