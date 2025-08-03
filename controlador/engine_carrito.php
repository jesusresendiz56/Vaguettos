<?php
session_start();
require_once '../modelo/conexion2.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Función para obtener id_carrito (crear si no existe)
function obtenerCarrito($conn2, $id_usuario) {
    $sql = "SELECT id_carrito FROM carritos WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 1";
    $stmt = $conn2->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        $sqlInsert = "INSERT INTO carritos (id_usuario) VALUES (?)";
        $stmtInsert = $conn2->prepare($sqlInsert);
        $stmtInsert->bind_param("i", $id_usuario);
        $stmtInsert->execute();
        $id_carrito = $stmtInsert->insert_id;
        $stmtInsert->close();
        return $id_carrito;
    } else {
        $carrito = $res->fetch_assoc();
        $stmt->close();
        return $carrito['id_carrito'];
    }
}

$id_carrito = obtenerCarrito($conn2, $id_usuario);

// Acción: obtener carrito (GET)
if ($action === 'get' && $method === 'GET') {
    $sqlProd = "SELECT p.id_producto, p.nombre, p.precio, cp.cantidad
                FROM carrito_productos cp
                JOIN productos p ON cp.id_producto = p.id_producto
                WHERE cp.id_carrito = ?";
    $stmt2 = $conn2->prepare($sqlProd);
    $stmt2->bind_param("i", $id_carrito);
    $stmt2->execute();
    $resProd = $stmt2->get_result();

    $cartItems = [];
    while ($row = $resProd->fetch_assoc()) {
        $cartItems[] = [
            'id' => (int)$row['id_producto'],
            'name' => $row['nombre'],
            'price' => (float)$row['precio'],
            'quantity' => (int)$row['cantidad'],
        ];
    }
    echo json_encode($cartItems);
    exit();
}

// Acción: agregar producto (POST, action=add)
if ($action === 'add' && $method === 'POST') {
    $id_producto = intval($_POST['id_producto'] ?? 0);
    if (!$id_producto) {
        echo json_encode(['error' => 'Producto no especificado']);
        exit();
    }
    // Verificar si producto ya existe
    $sqlCheck = "SELECT id_carrito_productos, cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?";
    $stmtCheck = $conn2->prepare($sqlCheck);
    $stmtCheck->bind_param("ii", $id_carrito, $id_producto);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck->num_rows > 0) {
        // Actualizar cantidad +1
        $item = $resCheck->fetch_assoc();
        $cantidadNueva = $item['cantidad'] + 1;
        $sqlUpdate = "UPDATE carrito_productos SET cantidad = ? WHERE id_carrito_productos = ?";
        $stmtUpdate = $conn2->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $cantidadNueva, $item['id_carrito_productos']);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    } else {
        // Insertar nuevo
        $cantidadNueva = 1;
        $sqlInsert = "INSERT INTO carrito_productos (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)";
        $stmtInsert = $conn2->prepare($sqlInsert);
        $stmtInsert->bind_param("iii", $id_carrito, $id_producto, $cantidadNueva);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
    $stmtCheck->close();

    // Responder OK o redirigir
    // Aquí responderemos JSON con carrito actualizado:
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit();
}

// Acción: actualizar cantidad o eliminar producto (POST, action=update)
if ($action === 'update' && $method === 'POST') {
    // Esperamos JSON en body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['action'], $data['id_producto'])) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit();
    }
    $updAction = $data['action'];
    $id_producto = intval($data['id_producto']);

    // Obtener carrito_producto si existe
    $sqlCheck = "SELECT id_carrito_productos, cantidad FROM carrito_productos WHERE id_carrito = ? AND id_producto = ?";
    $stmtCheck = $conn2->prepare($sqlCheck);
    $stmtCheck->bind_param("ii", $id_carrito, $id_producto);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows === 0) {
        echo json_encode(['error' => 'Producto no encontrado en carrito']);
        exit();
    }
    $item = $resCheck->fetch_assoc();
    $id_cp = $item['id_carrito_productos'];
    $cantidad = $item['cantidad'];

    if ($updAction === 'increment') {
        $cantidad++;
        $sqlUpdate = "UPDATE carrito_productos SET cantidad = ? WHERE id_carrito_productos = ?";
        $stmtUpdate = $conn2->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $cantidad, $id_cp);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    } elseif ($updAction === 'decrement') {
        $cantidad--;
        if ($cantidad <= 0) {
            // eliminar producto
            $sqlDelete = "DELETE FROM carrito_productos WHERE id_carrito_productos = ?";
            $stmtDelete = $conn2->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $id_cp);
            $stmtDelete->execute();
            $stmtDelete->close();
        } else {
            $sqlUpdate = "UPDATE carrito_productos SET cantidad = ? WHERE id_carrito_productos = ?";
            $stmtUpdate = $conn2->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ii", $cantidad, $id_cp);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    } elseif ($updAction === 'remove') {
        $sqlDelete = "DELETE FROM carrito_productos WHERE id_carrito_productos = ?";
        $stmtDelete = $conn2->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $id_cp);
        $stmtDelete->execute();
        $stmtDelete->close();
    } else {
        echo json_encode(['error' => 'Acción inválida']);
        exit();
    }
    $stmtCheck->close();

    echo json_encode(['success' => true]);
    exit();
}
// Si no envían acción válida:
echo json_encode(['error' => 'Acción no especificada o inválida']);
exit();