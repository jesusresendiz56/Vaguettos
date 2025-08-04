<?php
ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);
session_set_cookie_params(604800);
session_start();
require_once '../modelo/conexion2.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = 1; 
}

// Obtener filtros
$filtroTipos = $conn2->query("SELECT DISTINCT tipo FROM productos WHERE tipo IS NOT NULL AND tipo != ''");
$filtroModelos = $conn2->query("SELECT DISTINCT modelo_auto FROM productos WHERE modelo_auto IS NOT NULL AND modelo_auto != ''");
$filtroYears = $conn2->query("SELECT DISTINCT years_aplicables FROM productos WHERE years_aplicables IS NOT NULL AND years_aplicables != ''");

// Filtros seleccionados
$filtroTipo = $_GET['tipo'] ?? '';
$filtroModelo = $_GET['modelo'] ?? '';
$filtroYear = $_GET['years'] ?? '';

// Consulta productos con filtros
$sql = "SELECT * FROM productos WHERE stock > 0";
if (!empty($filtroTipo)) $sql .= " AND tipo = '" . $conn2->real_escape_string($filtroTipo) . "'";
if (!empty($filtroModelo)) $sql .= " AND modelo_auto = '" . $conn2->real_escape_string($filtroModelo) . "'";
if (!empty($filtroYear)) $sql .= " AND years_aplicables = '" . $conn2->real_escape_string($filtroYear) . "'";
$sql .= " ORDER BY id_producto DESC";
$res = $conn2->query($sql);

// Obtener nombre usuario
$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = "Usuario";
$stmtUser = $conn2->prepare("SELECT nombre_completo FROM usuarios WHERE id_usuario = ?");
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
if ($resUser->num_rows > 0) {
    $fila = $resUser->fetch_assoc();
    $nombre_usuario = $fila['nombre_completo'];
}
$stmtUser->close();

// Obtener carrito actual
$cartItems = [];
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
    $stmt2->close();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Catálogo y Carrito - Vaguettos</title>
    <link rel="stylesheet" href="../scr/css/carrito.css" />
    <style>
      #paypal-button-container { margin-top: 15px; }
      .modal { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; 
      }
      .modal-content {
        background: #fff; padding: 20px; border-radius: 8px; width: 320px;
      }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="nav-link">Inicio</a>
    <a href="catalogo.php" class="nav-link">Catálogo de Productos</a>
    <a href="#" class="nav-link">Carrito de Compras</a>
    <a href="editar_perfil.php" class="nav-link">Editar Perfil</a>
    <a href="../controlador/logout.php" class="nav-link">Cerrar Sesión</a>
    <span style="color:#fff; margin-left: 20px;">Bienvenido, <?= htmlspecialchars($nombre_usuario) ?></span>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Catálogo de Productos</h1>
</header>

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

<div id="cart">
    <h3>🛒 Carrito</h3>
    <ul id="cart-items"></ul>
    <p>Total: $<span id="cart-total">0.00</span></p>
    <button onclick="openModal()">Finalizar compra</button>
</div>

<div id="checkout-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Finalizar compra</h3>
        <input type="text" id="name" placeholder="Nombre completo" required />
        <input type="text" id="phone" placeholder="Teléfono" required />
        <textarea id="address" placeholder="Dirección de entrega" required></textarea>
        <button id="confirm-order-btn" onclick="submitOrder()">Confirmar pedido</button>

        <div id="paypal-button-container"></div>
        <p id="result-message" style="color:green; font-weight:bold;"></p>
    </div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id=Ad8eprBV7VKf8Z3Fvb5SaW7dYymWDNJDE5zdhVgJ_hr1B8UTP5NAZrJKFXSt8uTzYgtdJEKG8cshD7jL"></script>

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
    const formData = new URLSearchParams();
    formData.append('accion', 'agregar');
    formData.append('id_producto', id);
    formData.append('cantidad', '1');

    fetch('../controlador/engine_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
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
            alert(res.error || 'Error al agregar al carrito');
        }
    });
}

function changeQuantity(index, change) {
    const item = cartItems[index];
    if (!item) return;

    let accion;
    if (change > 0) {
        accion = 'agregar';
    } else {
        if (item.quantity === 1) {
            accion = 'eliminar';
        } else {
            accion = 'actualizar';
        }
    }

    const formData = new URLSearchParams();
    formData.append('accion', accion);
    formData.append('id_producto', item.id);
    if (accion === 'agregar') {
        formData.append('cantidad', '1');
    }
    if (accion === 'actualizar') {
        formData.append('cantidad', item.quantity - 1);
    }

    fetch('../controlador/engine_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            if (accion === 'agregar') {
                item.quantity++;
            } else if (accion === 'eliminar') {
                cartItems.splice(index, 1);
            } else if (accion === 'actualizar') {
                item.quantity--;
            }
            updateCart();
        } else {
            alert(res.error || 'Error al actualizar el carrito');
        }
    });
}

function removeItem(index) {
    const item = cartItems[index];
    if (!item) return;

    const formData = new URLSearchParams();
    formData.append('accion', 'eliminar');
    formData.append('id_producto', item.id);

    fetch('../controlador/engine_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            cartItems.splice(index, 1);
            updateCart();
        } else {
            alert(res.error || 'Error al eliminar producto');
        }
    });
}

function openModal() {
    if (cartItems.length === 0) {
        alert('Tu carrito está vacío.');
        return;
    }
    document.getElementById('checkout-modal').style.display = 'flex';

    // Reset modal
    document.getElementById('paypal-button-container').innerHTML = '';
    document.getElementById('result-message').innerText = '';
    document.getElementById('name').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('address').value = '';
    document.getElementById('name').style.display = 'block';
    document.getElementById('phone').style.display = 'block';
    document.getElementById('address').style.display = 'block';
    document.getElementById('confirm-order-btn').style.display = 'inline-block';
}

function submitOrder() {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();

    if (!name || !phone || !address) {
        alert('Completa todos los campos.');
        return;
    }

    // Ocultar inputs y botón confirmar
    document.getElementById('name').style.display = 'none';
    document.getElementById('phone').style.display = 'none';
    document.getElementById('address').style.display = 'none';
    document.getElementById('confirm-order-btn').style.display = 'none';

    // Mostrar PayPal
    const paypalContainer = document.getElementById('paypal-button-container');
    paypalContainer.style.display = 'block';

    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: { value: total.toFixed(2) }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Enviar datos para generar y descargar ticket PDF
                const orderData = {
                    name: name,
                    phone: phone,
                    address: address,
                    cart: cartItems
                };

                fetch('../controlador/generar_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error al generar el ticket');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'ticket_compra.pdf';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(err => {
                    alert(err.message);
                });

                // Limpiar carrito y mostrar mensaje
                cartItems = [];
                updateCart();
                document.getElementById('result-message').innerText = "Pago completado con éxito, gracias por tu compra!";
            });
        },
        onCancel: function(data) {
            alert("Pago cancelado");
            // Restaurar formulario
            document.getElementById('name').style.display = 'block';
            document.getElementById('phone').style.display = 'block';
            document.getElementById('address').style.display = 'block';
            document.getElementById('confirm-order-btn').style.display = 'inline-block';
            document.getElementById('paypal-button-container').style.display = 'none';
            document.getElementById('result-message').innerText = '';
        },
        onError: function(err) {
            alert("Error en el pago");
            console.error(err);
            document.getElementById('name').style.display = 'block';
            document.getElementById('phone').style.display = 'block';
            document.getElementById('address').style.display = 'block';
            document.getElementById('confirm-order-btn').style.display = 'inline-block';
            document.getElementById('paypal-button-container').style.display = 'none';
            document.getElementById('result-message').innerText = '';
        }
    }).render('#paypal-button-container');
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
