<?php
session_start();
require_once '../modelo/conexion2.php';

header('Content-Type: application/json');

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
$id_producto = $input['id_producto'] ?? null;

if (!$id_producto) {
    echo json_encode(['success' => false, 'message' => 'Producto no especificado']);
    exit;
}

// 1. Verificar si el carrito existe para el usuario actual
$stmt = $conn2->prepare("SELECT id_carrito FROM carritos WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 1");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $id_carrito = $res->fetch_assoc()['id_carrito'];
} else {
    // Crear nuevo carrito
    $stmt = $conn2->prepare("INSERT INTO carritos (id_usuario) VALUES (?)");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $id_carrito = $conn2->insert_id;
}

// --- Obtener stock actual del producto ---
$stmt = $conn2->prepare("SELECT stock FROM productos WHERE id_producto = ?");
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$resStock = $stmt->get_result();

if ($resStock->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit;
}
$stock_actual = $resStock->fetch_assoc()['stock'];

// Acción: agregar producto
if ($action === 'add') {
    if ($stock_actual <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sin stock']);
        exit;
    }

    // Verificar si ya está en el carrito
    $stmt = $conn2->prepare("SELECT cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
    $stmt->bind_param("ii", $id_carrito, $id_producto);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // Ya existe, incrementar cantidad
        $stmt = $conn2->prepare("UPDATE carrito_productos SET cantidad = cantidad + 1 WHERE id_carrito = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_carrito, $id_producto);
    } else {
        // Nuevo registro
        $stmt = $conn2->prepare("INSERT INTO carrito_productos (id_carrito, id_producto, cantidad) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $id_carrito, $id_producto);
    }

    $stmt->execute();

    // Actualizar stock
    $stmt = $conn2->prepare("UPDATE productos SET stock = stock - 1 WHERE id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// Acción: actualizar (incrementar, decrementar o eliminar)
if ($action === 'update') {
    $accion = $input['action'] ?? '';

    if ($accion === 'increment') {
        if ($stock_actual <= 0) {
            echo json_encode(['success' => false, 'message' => 'Sin stock']);
            exit;
        }

        // Incrementar cantidad en carrito
        $stmt = $conn2->prepare("UPDATE carrito_productos SET cantidad = cantidad + 1 WHERE id_carrito = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_carrito, $id_producto);
        $stmt->execute();

        // Disminuir stock
        $stmt = $conn2->prepare("UPDATE productos SET stock = stock - 1 WHERE id_producto = ?");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;

    } elseif ($accion === 'decrement') {
        // Verificar cantidad actual
        $stmt = $conn2->prepare("SELECT cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_carrito, $id_producto);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $cantidad = $res->fetch_assoc()['cantidad'];
            if ($cantidad > 1) {
                // Decrementar cantidad
                $stmt = $conn2->prepare("UPDATE carrito_productos SET cantidad = cantidad - 1 WHERE id_carrito = ? AND id_producto = ?");
                $stmt->bind_param("ii", $id_carrito, $id_producto);
                $stmt->execute();
            } else {
                // Eliminar del carrito
                $stmt = $conn2->prepare("DELETE FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
                $stmt->bind_param("ii", $id_carrito, $id_producto);
                $stmt->execute();
            }

            // Aumentar stock
            $stmt = $conn2->prepare("UPDATE productos SET stock = stock + 1 WHERE id_producto = ?");
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();

            echo json_encode(['success' => true]);
            exit;
        }

    } elseif ($accion === 'remove') {
        // Obtener cantidad antes de eliminar
        $stmt = $conn2->prepare("SELECT cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_carrito, $id_producto);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $cantidad = $res->fetch_assoc()['cantidad'];

            // Eliminar del carrito
            $stmt = $conn2->prepare("DELETE FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?");
            $stmt->bind_param("ii", $id_carrito, $id_producto);
            $stmt->execute();

            // Devolver stock
            $stmt = $conn2->prepare("UPDATE productos SET stock = stock + ? WHERE id_producto = ?");
            $stmt->bind_param("ii", $cantidad, $id_producto);
            $stmt->execute();

            echo json_encode(['success' => true]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
