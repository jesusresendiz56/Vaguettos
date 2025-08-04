<?php
require '../fpdf186/fpdf.php';
require '../modelo/conexion2.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart'])) {
    http_response_code(400);
    echo 'Datos inválidos';
    exit;
}

class PDF_Ticket extends FPDF {
    protected $logoPath = '../scr/imagenes/logo.jpg';

    function Header() {
        // Encabezado 
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'VAGUETTOS', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, utf8_decode('Accesorios Volkswagen'), 0, 1, 'C');
        $this->Cell(0, 5, 'Vaguettos.com', 0, 1, 'C');
        $this->Ln(2);
        $this->Line(5, $this->GetY(), 75, $this->GetY());
        $this->Ln(3);
        
        // Logo 
        if (file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 20, $this->GetY() + 20, 40);
        }
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 4, utf8_decode('¡Gracias por tu compra!'), 0, 1, 'C');
        $this->Cell(0, 4, 'VAGUETTOS - Neza', 0, 1, 'C');
    }
}

// Calcular altura 
$alto_base = 80;
$alto_lineas = count($data['cart']) * 8;
$alto_total = $alto_base + $alto_lineas;
$alto_final = max($alto_total, 120);

$pdf = new PDF_Ticket('P', 'mm', array(80, $alto_final));
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

// Datos del cliente (compactos)
$pdf->Cell(0, 4, 'Cliente: ' . utf8_decode(substr($data['name'], 0, 30)), 0, 1);
$pdf->Cell(0, 4, 'Telefono: ' . substr($data['phone'], 0, 15), 0, 1);
$pdf->Cell(0, 4, 'Direccion: ' . utf8_decode(substr($data['address'], 0, 30)), 0, 1);

// ➕ Fecha y hora actual (zona horaria México)
date_default_timezone_set('America/Mexico_City');
$fechaHora = date('d/m/Y H:i:s');
$pdf->Cell(0, 4, 'Fecha: ' . $fechaHora, 0, 1); // Nueva línea agregada


$pdf->Ln(2);
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(3);

// Encabezado de productos optimizado
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, 'PRODUCTO', 0);
$pdf->Cell(10, 5, 'CANT', 0, 0, 'C');
$pdf->Cell(15, 5, 'P.UNIT', 0, 0, 'R');
$pdf->Cell(15, 5, 'TOTAL', 0, 1, 'R');
$pdf->SetFont('Arial', '', 8);

// Listado de productos mejorado
$total = 0;
foreach ($data['cart'] as $item) {
    $nombre = utf8_decode($item['name']);
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
    
    // Ajustar texto largo
    if(strlen($nombre) > 20) {
        $nombre = substr($nombre, 0, 17) . '...';
    }
    
    $pdf->Cell(35, 5, $nombre, 0);
    $pdf->Cell(10, 5, $item['quantity'], 0, 0, 'C');
    $pdf->Cell(15, 5, '$' . number_format($item['price'], 2, '.', ','), 0, 0, 'R');
    $pdf->Cell(15, 5, '$' . number_format($subtotal, 2, '.', ','), 0, 1, 'R');
}

// Total mejor formateado
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