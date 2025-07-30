<?php
include '../modelo/conexion2.php';

// Obtener filtros únicos
$filtroTipos = $conn2->query("SELECT DISTINCT tipo FROM productos WHERE tipo IS NOT NULL AND tipo != ''");
$filtroModelos = $conn2->query("SELECT DISTINCT modelo_auto FROM productos WHERE modelo_auto IS NOT NULL AND modelo_auto != ''");
$filtroYears = $conn2->query("SELECT DISTINCT years_aplicables FROM productos WHERE years_aplicables IS NOT NULL AND years_aplicables != ''");

// Obtener valores seleccionados
$filtroTipo = $_GET['tipo'] ?? '';
$filtroModelo = $_GET['modelo'] ?? '';
$filtroYear = $_GET['years'] ?? '';

// Armar consulta con filtros
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras - Vaguettos</title>
    <link rel="stylesheet" href="../scr/css/stock.css" />
</head>
<body>

<nav>
    <a href="index.php" class="nav-link">Inicio</a>
    <a href="catalogo.php" class="nav-link">Catálogo de Productos</a>
    <a href="#" class="nav-link">Carrito de Compras</a>
    <a href="cerrarSesion.html" class="nav-link">Cerrar Sesión</a>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Catálogo de Productos</h1>
</header>

<!-- Filtros -->
<section>
    <h2>Filtrar productos</h2>
    <form method="get" action="">
        <label>Tipo:</label>
        <select name="tipo">
            <option value="">Todos</option>
            <?php while ($row = $filtroTipos->fetch_assoc()): ?>
                <option value="<?= $row['tipo'] ?>" <?= ($filtroTipo == $row['tipo']) ? 'selected' : '' ?>>
                    <?= $row['tipo'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Modelo:</label>
        <select name="modelo">
            <option value="">Todos</option>
            <?php while ($row = $filtroModelos->fetch_assoc()): ?>
                <option value="<?= $row['modelo_auto'] ?>" <?= ($filtroModelo == $row['modelo_auto']) ? 'selected' : '' ?>>
                    <?= $row['modelo_auto'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Años:</label>
        <select name="years">
            <option value="">Todos</option>
            <?php while ($row = $filtroYears->fetch_assoc()): ?>
                <option value="<?= $row['years_aplicables'] ?>" <?= ($filtroYear == $row['years_aplicables']) ? 'selected' : '' ?>>
                    <?= $row['years_aplicables'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Aplicar filtros</button>
    </form>
</section>

<!-- Productos -->
<section class="product-container">
    <?php while ($row = $res->fetch_assoc()): ?>
        <div class="product-card">
            <?php if (!empty($row['imagen_url'])): ?>
                <img src="../scr/imagenes/productos/<?= htmlspecialchars($row['imagen_url']) ?>" alt="<?= htmlspecialchars($row['nombre']) ?>">
            <?php else: ?>
                <p>Sin imagen</p>
            <?php endif; ?>
            <h3><?= htmlspecialchars($row['nombre']) ?></h3>
            <p><?= htmlspecialchars($row['descripcion']) ?></p>
            <p>Modelo: <?= htmlspecialchars($row['modelo_auto']) ?></p>
            <p>Tipo: <?= htmlspecialchars($row['tipo']) ?></p>
            <p>Años: <?= htmlspecialchars($row['years_aplicables']) ?></p>
            <p><strong>$<?= number_format($row['precio'], 2) ?> MXN</strong></p>
            <button onclick="addToCart(<?= $row['id_producto'] ?>, '<?= htmlspecialchars($row['nombre']) ?>', <?= $row['precio'] ?>)">Agregar</button>
        </div>
    <?php endwhile; ?>
</section>

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

<!-- JS para carrito -->
<script>
let cartItems = [];
let total = 0;

function addToCart(id, name, price) {
    let item = cartItems.find(i => i.id === id);
    if (item) {
        item.quantity++;
    } else {
        cartItems.push({ id, name, price, quantity: 1 });
    }
    updateCart();
}

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

function changeQuantity(index, change) {
    if (cartItems[index].quantity + change > 0) {
        cartItems[index].quantity += change;
    } else {
        cartItems.splice(index, 1);
    }
    updateCart();
}

function removeItem(index) {
    cartItems.splice(index, 1);
    updateCart();
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
</script>

</body>
</html>
