<?php
// --- Incluir conexiones ---
include '../modelo/conexion.php';   // conexión local ($conn)
include '../modelo/conexion2.php';  // conexión remota ($conn2)

// --- Cambiar aquí a false para usar local ---
$usarRemoto = true;
$db = $usarRemoto ? $conn2 : $conn;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "modificar") {
    $modo = $_POST["modo"];
    $id_producto = $_POST["id_producto"];
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];
    $id_categoria = $_POST["id_categoria"];
    $tipo = $_POST["tipo"];
    $modelo_auto = $_POST["modelo_auto"];
    $years_aplicables = $_POST["years_aplicables"];
    
    $imagen_nueva = $_FILES["imagen"]["name"] ?? '';
    $imagen_temp = $_FILES["imagen"]["tmp_name"] ?? '';
    $ruta_destino = "../scr/imagenes/productos/" . basename($imagen_nueva);

    if ($modo === "nuevo") {
        if (!empty($imagen_nueva) && move_uploaded_file($imagen_temp, $ruta_destino)) {
            $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen_url, id_categoria, tipo, modelo_auto, years_aplicables)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssdisisss", $nombre, $descripcion, $precio, $stock, $imagen_nueva, $id_categoria, $tipo, $modelo_auto, $years_aplicables);
            $stmt->execute();
            $stmt->close();
            header("Location: ../vista/inventario.php");
            exit;
        } else {
            echo "Error al subir la imagen para nuevo producto.";
            exit;
        }
    } else {
        if (!empty($imagen_nueva)) {
            move_uploaded_file($imagen_temp, $ruta_destino);
            $sql = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, imagen_url=?, id_categoria=?, tipo=?, modelo_auto=?, years_aplicables=? WHERE id_producto=?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssdisisssi", $nombre, $descripcion, $precio, $stock, $imagen_nueva, $id_categoria, $tipo, $modelo_auto, $years_aplicables, $id_producto);
        } else {
            $sql = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, id_categoria=?, tipo=?, modelo_auto=?, years_aplicables=? WHERE id_producto=?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssdiisssi", $nombre, $descripcion, $precio, $stock, $id_categoria, $tipo, $modelo_auto, $years_aplicables, $id_producto);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: ../vista/inventario.php");
        exit;
    }

    $db->close();
}
?>


