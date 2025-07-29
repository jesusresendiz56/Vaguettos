<?php
include '../modelo/conexion.php'; // o ../modelo/conexion2.php según configuración

// Obtener productos disponibles
$sql = "SELECT * FROM productos WHERE stock > 0 ORDER BY id_producto DESC";
$res = $conn->query($sql);
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
    <a href="catalogo.php" class="nav-link">Catalogo de Productos</a>
    <a href="#" class="nav-link">Carrito de Compras</a>
    <a href="cerrarSesion.html" class="nav-link">Cerrar Sesión</a>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Catálogo de Productos</h1>
</header>

<section class="product-container">
    <?php while ($row = $res->fetch_assoc()): ?>
        <div class="product-card">
            <?php if (!empty($row['imagen_url'])): ?>
                <img src="../scr/imagenes/productos/<?= htmlspecialchars($row['imagen_url']) ?>" alt="<?= htmlspecialchars($row['nombre']) ?>">
            <?php else: ?>
                <p>Sin imagen</p>
            <?php endif; ?>
            <h3><?= htmlspecialchars($row['nombre']) ?></h3>
            <p>$<?= number_format($row['precio'], 2) ?> MXN</p>
            <button onclick="addToCart(<?= $row['id_producto'] ?>, '<?= htmlspecialchars($row['nombre']) ?>', <?= $row['precio'] ?>)">Agregar</button>
        </div>
    <?php endwhile; ?>
</section>

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
