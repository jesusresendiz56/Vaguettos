<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../modelo/conexion2.php';
require_once '../vendor/autoload.php';

use TCPDF;

session_start();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['id'], $data['cantidad'])) {
    http_response_code(400);
    echo "Parámetros inválidos JSON: " . json_encode($data);
    exit;
}

$id = intval($data['id']);
$cantidad = intval($data['cantidad']);

if ($id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo "Parámetros inválidos: id={$id}, cantidad={$cantidad}";
    exit;
}

$stmt = $conn2->prepare("SELECT nombre, precio, stock FROM productos WHERE id_producto = ?");
if (!$stmt) {
    http_response_code(500);
    echo "Error en prepare(): " . $conn2->error;
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Producto no encontrado";
    exit;
}

$producto = $result->fetch_assoc();
$stmt->close();

if ($producto['stock'] < $cantidad) {
    http_response_code(400);
    echo "Stock insuficiente";
    exit;
}

$stmt = $conn2->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
if (!$stmt) {
    http_response_code(500);
    echo "Error en prepare() update: " . $conn2->error;
    exit;
}

$stmt->bind_param("ii", $cantidad, $id);
$stmt->execute();
$stmt->close();

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$total = $producto['precio'] * $cantidad;

$html = "<h2>Ticket de Compra</h2>";
$html .= "<p><strong>Cliente:</strong> " . ($_SESSION['usuario'] ?? 'Cliente') . "</p>";
$html .= "<p><strong>Producto:</strong> " . htmlspecialchars($producto['nombre']) . "</p>";
$html .= "<p><strong>Cantidad:</strong> {$cantidad}</p>";
$html .= "<p><strong>Precio unitario:</strong> $" . number_format($producto['precio'], 2) . "</p>";
$html .= "<p><strong>Total a pagar:</strong> $" . number_format($total, 2) . "</p>";

$pdf->writeHTML($html);

$pdfContent = $pdf->Output('', 'S');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ticket_compra.pdf"');
header('Content-Length: ' . strlen($pdfContent));

echo $pdfContent;
exit;
