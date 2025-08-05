<?php
require '../fpdf186/fpdf.php';
require '../modelo/conexion2.php';

date_default_timezone_set('America/Mexico_City');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'], $data['cantidad'])) {
    http_response_code(400);
    echo 'Parámetros inválidos';
    exit;
}

$id = intval($data['id']);
$cantidad = intval($data['cantidad']);

if ($id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo 'ID o cantidad inválida';
    exit;
}

// Obtener producto
$stmt = $conn2->prepare("SELECT nombre, precio, stock FROM productos WHERE id_producto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo 'Producto no encontrado';
    exit;
}

$producto = $result->fetch_assoc();
$stmt->close();

// Validar stock
if ($producto['stock'] < $cantidad) {
    http_response_code(400);
    echo 'Stock insuficiente';
    exit;
}

// Actualizar stock
$stmt = $conn2->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
$stmt->bind_param("ii", $cantidad, $id);
$stmt->execute();
$stmt->close();

// Datos para el ticket
$total = $producto['precio'] * $cantidad;
$fechaHora = date('d/m/Y H:i:s');
$cliente = $_SESSION['usuario'] ?? 'Cliente Vaguettos';

// Clase personalizada para ticket
class PDF_Ticket extends FPDF {
    protected $logoPath = '../scr/imagenes/logo.jpg';

    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'VAGUETTOS', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, utf8_decode('Accesorios Volkswagen'), 0, 1, 'C');
        $this->Cell(0, 5, 'Vaguettos.com', 0, 1, 'C');
        $this->Ln(2);
        $this->Line(5, $this->GetY(), 75, $this->GetY());
        $this->Ln(2);

        // Mostrar imagen centrada, tamaño pequeño (20 mm de ancho)
    if (file_exists($this->logoPath)) {
        $this->Image($this->logoPath, ($this->GetPageWidth() - 20) / 2, $this->GetY(), 20); 
        $this->Ln(15); // Ajusta el salto de línea si deseas más separación
    }

    $this->Line(5, $this->GetY(), 75, $this->GetY()); // Línea separadora
    $this->Ln(2);
}

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 4, utf8_decode('¡Gracias por tu compra!'), 0, 1, 'C');
        $this->Cell(0, 4, 'VAGUETTOS - Neza', 0, 1, 'C');
    }
}

// Altura del ticket
$alto_base = 100;
$alto_final = max($alto_base, 120);

$pdf = new PDF_Ticket('P', 'mm', array(80, $alto_final));
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

// Cliente y fecha
$pdf->Cell(0, 4, 'Cliente: ' . utf8_decode(substr($cliente, 0, 30)), 0, 1);
$pdf->Cell(0, 4, 'Fecha: ' . $fechaHora, 0, 1);
$pdf->Ln(2);
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(3);

// Detalle del producto
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, 'PRODUCTO', 0);
$pdf->Cell(10, 5, 'CANT', 0, 0, 'C');
$pdf->Cell(15, 5, 'P.UNIT', 0, 0, 'R');
$pdf->Cell(15, 5, 'TOTAL', 0, 1, 'R');
$pdf->SetFont('Arial', '', 8);

$nombre = utf8_decode($producto['nombre']);
if(strlen($nombre) > 20) {
    $nombre = substr($nombre, 0, 17) . '...';
}

$pdf->Cell(35, 5, $nombre, 0);
$pdf->Cell(10, 5, $cantidad, 0, 0, 'C');
$pdf->Cell(15, 5, '$' . number_format($producto['precio'], 2, '.', ','), 0, 0, 'R');
$pdf->Cell(15, 5, '$' . number_format($total, 2, '.', ','), 0, 1, 'R');

$pdf->Ln(3);
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(50, 6, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(20, 6, '$' . number_format($total, 2, '.', ','), 0, 1, 'R');
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());

// Salida del PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ticket_vaguettos.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

$pdf->Output('D');
exit;
