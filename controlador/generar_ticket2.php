<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../modelo/conexion2.php';
require_once '../vendor/autoload.php'; // TCPDF
use TCPDF;

session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'], $data['cantidad'])) {
    http_response_code(400);
    echo "Parámetros inválidos.";
    exit;
}

$id = intval($data['id']);
$cantidad = intval($data['cantidad']);

if ($id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo "ID o cantidad inválida.";
    exit;
}

$stmt = $conn2->prepare("SELECT nombre, precio, stock FROM productos WHERE id_producto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Producto no encontrado.";
    exit;
}

$producto = $result->fetch_assoc();
$stmt->close();

if ($producto['stock'] < $cantidad) {
    http_response_code(400);
    echo "Stock insuficiente.";
    exit;
}

// Actualizar stock
$stmt = $conn2->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
$stmt->bind_param("ii", $cantidad, $id);
$stmt->execute();
$stmt->close();

// Generar PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$total = $producto['precio'] * $cantidad;
$cliente = $_SESSION['usuario'] ?? 'Cliente';

$html = "
  <h2>Ticket de Compra</h2>
  <p><strong>Cliente:</strong> {$cliente}</p>
  <p><strong>Producto:</strong> " . htmlspecialchars($producto['nombre']) . "</p>
  <p><strong>Cantidad:</strong> {$cantidad}</p>
  <p><strong>Precio unitario:</strong> $" . number_format($producto['precio'], 2) . "</p>
  <p><strong>Total:</strong> $" . number_format($total, 2) . "</p>
";

$pdf->writeHTML($html);
$pdfContent = $pdf->Output('', 'S');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ticket_compra.pdf"');
header('Content-Length: ' . strlen($pdfContent));

echo $pdfContent;
exit;
