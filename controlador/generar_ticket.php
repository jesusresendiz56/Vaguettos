<?php
require '../fpdf186/fpdf.php';  // Ajusta ruta si hace falta
require '../modelo/conexion2.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart'])) {
    http_response_code(400);
    echo 'Datos inválidos';
    exit;
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Ticket de Compra - Vaguettos', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);
$pdf->Cell(0, 10, 'Cliente: ' . $data['name'], 0, 1);
$pdf->Cell(0, 10, 'Teléfono: ' . $data['phone'], 0, 1);
$pdf->Cell(0, 10, 'Dirección: ' . $data['address'], 0, 1);
$pdf->Ln(5);

$pdf->Cell(80, 10, 'Producto', 1);
$pdf->Cell(30, 10, 'Cantidad', 1);
$pdf->Cell(40, 10, 'Precio Unitario', 1);
$pdf->Cell(40, 10, 'Subtotal', 1);
$pdf->Ln();

$total = 0;
foreach ($data['cart'] as $item) {
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
    $pdf->Cell(80, 10, utf8_decode($item['name']), 1);
    $pdf->Cell(30, 10, $item['quantity'], 1);
    $pdf->Cell(40, 10, '$' . number_format($item['price'], 2), 1);
    $pdf->Cell(40, 10, '$' . number_format($subtotal, 2), 1);
    $pdf->Ln();
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Total: $' . number_format($total, 2), 0, 1, 'R');

// Headers para que el navegador entienda que es un PDF para descargar
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ticket_compra.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

$pdf->Output('D'); // D = fuerza descarga del PDF
exit;
