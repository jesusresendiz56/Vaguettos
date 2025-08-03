<?php
// --- Incluir conexiones ---
//include '../modelo/conexion.php';   // local
include '../modelo/conexion2.php';  // remota

// --- Cambiar aquí a false para usar local ---
$usarRemoto = true;
$conn = $usarRemoto ? $conn2 : $conn;

$modo = "nuevo";
$producto = [
    'id_producto' => '',
    'nombre' => '',
    'descripcion' => '',
    'precio' => '',
    'stock' => '',
    'imagen_url' => '',
    'id_categoria' => '',
    'tipo' => '',
    'modelo_auto' => '',
    'years_aplicables' => ''
];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 1) {
        $producto = $resultado->fetch_assoc();
        $modo = "editar";
    }
    $stmt->close();
}

// Obtener categorías desde base de datos
$cats = $conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre");

// Listado predefinido de tipos de accesorio
$tipos_accesorio = ["Espejo", "Bocina", "Sensor de reversa", "Pantalla", "Cámara", "Tapete", "Otro"];

// Listado predefinido de modelos de auto
$modelos_auto = ["Vento", "Beetle", "Tiguan", "Golf", "Jetta", "Polo"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gestión de Inventario - Formulario</title>
<link rel="stylesheet" href="../scr/css/inventario.css" />
</head>
<body>

<nav>
    <a href="indexadmin.html" class="nav-link">Inicio</a>
    <a href="usuarios.php" class="nav-link">Administración de Clientes</a>
    <a href="pagos.html" class="nav-link">Administración de Pagos</a>
    <a href="inventario.php" class="nav-link">Gestión de Inventario</a>
    <a href="stock.php" class="nav-link">Listado de Productos</a>
    <a href="../vista/login.php" class="nav-link">Cerrar Sesión</a>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Gestión de Inventario</h1>
</header>

<form method="post" enctype="multipart/form-data" action="../controlador/guardar_producto.php">
    <input type="hidden" name="modo" value="<?= $modo ?>">
    <input type="hidden" name="id_producto" value="<?= htmlspecialchars($producto['id_producto']) ?>">

    <label for="nombre">Nombre del producto:</label>
    <input type="text" id="nombre" name="nombre" placeholder="Nombre del producto" value="<?= htmlspecialchars($producto['nombre']) ?>" required>

    <!-- CAMBIO: modelo_auto como <select> con opciones fijas -->
    <label for="modelo_auto">Modelo del auto:</label>
    <select id="modelo_auto" name="modelo_auto" required>
        <option value="">Selecciona un modelo</option>
        <?php foreach ($modelos_auto as $modelo): ?>
            <option value="<?= $modelo ?>" <?= ($producto['modelo_auto'] == $modelo) ? 'selected' : '' ?>><?= $modelo ?></option>
        <?php endforeach; ?>
    </select>

    <!-- CAMBIO: tipo como <select> con valores predefinidos -->
    <label for="tipo">Tipo de accesorio:</label>
    <select id="tipo" name="tipo" required>
        <option value="">Selecciona un tipo</option>
        <?php foreach ($tipos_accesorio as $tipo): ?>
            <option value="<?= $tipo ?>" <?= ($producto['tipo'] == $tipo) ? 'selected' : '' ?>><?= $tipo ?></option>
        <?php endforeach; ?>
    </select>

    <label for="years_aplicables">Años aplicables:</label>
    <input type="text" id="years_aplicables" name="years_aplicables" placeholder="Ej. 2015-2020" value="<?= htmlspecialchars($producto['years_aplicables']) ?>" required>

    <label for="stock">Cantidad en stock:</label>
    <input type="number" id="stock" name="stock" placeholder="Cantidad en stock" min="0" value="<?= htmlspecialchars($producto['stock']) ?>" required>

    <label for="precio">Precio del producto:</label>
    <input type="text" id="precio" name="precio" placeholder="Precio del producto" value="<?= htmlspecialchars($producto['precio']) ?>" required>

    <label for="id_categoria">Categoría:</label>
    <select id="id_categoria" name="id_categoria" required>
        <option value="">Selecciona una categoría</option>
        <?php
        while ($cat = $cats->fetch_assoc()) {
            $selected = ($cat['id_categoria'] == $producto['id_categoria']) ? "selected" : "";
            echo "<option value='" . htmlspecialchars($cat['id_categoria']) . "' $selected>" . htmlspecialchars($cat['nombre']) . "</option>";
        }
        ?>
    </select>

    <label for="imagen">Imagen del producto:</label>
    <input type="file" id="imagen" name="imagen" accept="image/*" <?= $modo === "nuevo" ? "required" : "" ?>>

    <?php if ($modo === "editar" && !empty($producto['imagen_url'])): ?>
        <p>Imagen actual:</p>
        <img src="../scr/imagenes/productos/<?= htmlspecialchars($producto['imagen_url']) ?>" alt="Imagen actual" style="max-width:100px; height:auto;">
    <?php endif; ?>

    <label for="descripcion">Descripción del producto:</label>
    <textarea id="descripcion" name="descripcion" placeholder="Descripción del producto..." rows="3"><?= htmlspecialchars($producto['descripcion']) ?></textarea>

    <button type="submit" name="accion" value="modificar"><?= $modo === "nuevo" ? "Guardar" : "Actualizar" ?></button>
</form>

</body>
</html>
