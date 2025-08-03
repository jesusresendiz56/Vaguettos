<?php
session_start();
require_once '../modelo/conexion2.php'; // Asegúrate que este archivo define $conn2

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Debes iniciar sesión para usar el carrito.']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Función: obtener o crear el carrito
function obtenerOCrearCarrito($conn2, $id_usuario) {
    $sqlCarrito = "SELECT id_carrito FROM carritos WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 1";
    $stmt = $conn2->prepare($sqlCarrito);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($id_carrito);
    
    if ($stmt->fetch()) {
        $stmt->close();
        return $id_carrito;
    }
    $stmt->close();

    // No existe carrito, crear uno nuevo
    $sqlNuevo = "INSERT INTO carritos (id_usuario) VALUES (?)";
    $stmtNuevo = $conn2->prepare($sqlNuevo);
    $stmtNuevo->bind_param("i", $id_usuario);
    
    if ($stmtNuevo->execute()) {
        $nuevoId = $stmtNuevo->insert_id;
        $stmtNuevo->close();
        return $nuevoId;
    } else {
        $stmtNuevo->close();
        return null;
    }
}

// Obtener el carrito del usuario
$id_carrito = obtenerOCrearCarrito($conn2, $id_usuario);
if (!$id_carrito) {
    echo json_encode(['error' => 'Error al obtener o crear el carrito']);
    exit;
}

// Procesar acción
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        $id_producto = intval($_POST['id_producto']);
        $cantidad = intval($_POST['cantidad']);

        // Verificar stock
        $stockCheck = $conn2->prepare("SELECT stock FROM productos WHERE id_producto = ?");
        $stockCheck->bind_param("i", $id_producto);
        $stockCheck->execute();
        $stockCheck->bind_result($stockDisponible);
        $stockCheck->fetch();
        $stockCheck->close();

        if ($stockDisponible < $cantidad) {
            echo json_encode(['error' => 'No hay suficiente stock']);
            exit;
        }

        // Verificar si ya está en el carrito
        $check = $conn2->prepare("SELECT cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
        $check->bind_param("ii", $id_carrito, $id_producto);
        $check->execute();
        $check->bind_result($cantidadExistente);
        
        if ($check->fetch()) {
            $check->close();
            // Ya existe: actualizar cantidad
            $nuevaCantidad = $cantidadExistente + $cantidad;
            $update = $conn2->prepare("UPDATE carrito_productos SET cantidad = ? WHERE id_carrito = ? AND id_producto = ?");
            $update->bind_param("iii", $nuevaCantidad, $id_carrito, $id_producto);
            $update->execute();
            $update->close();
        } else {
            $check->close();
            // Insertar nuevo
            $insert = $conn2->prepare("INSERT INTO carrito_productos (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $id_carrito, $id_producto, $cantidad);
            $insert->execute();
            $insert->close();
        }

        // Actualizar stock en productos
        $nuevoStock = $stockDisponible - $cantidad;
        $updateStock = $conn2->prepare("UPDATE productos SET stock = ?, estado = IF(? > 0, 'En Stock', 'Agotado') WHERE id_producto = ?");
        $updateStock->bind_param("iii", $nuevoStock, $nuevoStock, $id_producto);
        $updateStock->execute();
        $updateStock->close();

        echo json_encode(['success' => true]);
        break;

    case 'obtener':
        // Obtener los productos del carrito
        $sql = "SELECT cp.id_producto, p.nombre, p.precio, cp.cantidad 
                FROM carrito_productos cp
                INNER JOIN productos p ON cp.id_producto = p.id_producto
                WHERE cp.id_carrito = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $id_carrito);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $carrito = [];
        $total = 0;
        while ($row = $resultado->fetch_assoc()) {
            $row['subtotal'] = $row['precio'] * $row['cantidad'];
            $total += $row['subtotal'];
            $carrito[] = $row;
        }

        echo json_encode(['productos' => $carrito, 'total' => $total]);
        break;

    case 'eliminar':
        $id_producto = intval($_POST['id_producto']);

        // Recuperar cantidad antes de eliminar para devolver al stock
        $stmt = $conn2->prepare("SELECT cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_carrito, $id_producto);
        $stmt->execute();
        $stmt->bind_result($cantidad);
        if ($stmt->fetch()) {
            $stmt->close();
            
            // Eliminar del carrito
            $del = $conn2->prepare("DELETE FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
            $del->bind_param("ii", $id_carrito, $id_producto);
            $del->execute();
            $del->close();

            // Devolver al stock
            $updateStock = $conn2->prepare("UPDATE productos SET stock = stock + ?, estado = 'En Stock' WHERE id_producto = ?");
            $updateStock->bind_param("ii", $cantidad, $id_producto);
            $updateStock->execute();
            $updateStock->close();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Producto no encontrado']);
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
}
?>
