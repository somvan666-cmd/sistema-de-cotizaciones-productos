<?php
require_once 'fpdf/fpdf.php';

class QuotationPDF extends FPDF
{
    private $quotationData;
    private $currency;
    private $format;
    private $exchangeRate;
    
    public function __construct($quotationData, $currency = 'BOB', $format = 'carta', $exchangeRate = 6.96)
    {
        parent::__construct($format === 'rollo' ? 'P' : 'P', 'mm', $format === 'rollo' ? array(80, 200) : 'A4');
        $this->quotationData = $quotationData;
        $this->currency = $currency;
        $this->format = $format;
        $this->exchangeRate = (float)$exchangeRate;
    }
    
    function Header()
    {
        if ($this->format === 'carta') {
            // Logo placeholder
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, 'EMPRESA XYZ', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, 'Direccion: Av. Principal #123', 0, 1, 'C');
            $this->Cell(0, 5, 'Telefono: +591 2 1234567', 0, 1, 'C');
            $this->Cell(0, 5, 'Email: ventas@empresa.com', 0, 1, 'C');
            $this->Ln(10);
            
            // Title
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 10, 'COTIZACION', 0, 1, 'C');
            $this->Ln(5);
        } else {
            // Rollo format - compact header
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(0, 5, 'EMPRESA XYZ', 0, 1, 'C');
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 3, 'Tel: +591 2 1234567', 0, 1, 'C');
            $this->Ln(3);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(0, 5, 'COTIZACION', 0, 1, 'C');
            $this->Ln(2);
        }
    }
    
    function Footer()
    {
        if ($this->format === 'carta') {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
        }
    }
    
    function QuotationInfo()
    {
        $data = $this->quotationData;
        
        if ($this->format === 'carta') {
            // Quotation details
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(40, 6, 'Numero:', 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->Cell(60, 6, $data['numero_cotizacion'], 0, 0);
            
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(30, 6, 'Fecha:', 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 6, date('d/m/Y H:i'), 0, 1);
            
            // Validity
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(40, 6, 'Validez:', 0, 0);
            $this->SetFont('Arial', '', 10);
            $validityText = $data['validez'] == 30 ? '1 mes' : $data['validez'] . ' dias';
            $this->Cell(0, 6, $validityText, 0, 1);
            
            $this->Ln(5);
            
            // Client info
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, 'DATOS DEL CLIENTE', 0, 1);
            $this->SetFont('Arial', '', 10);
            
            $this->Cell(30, 6, 'CI/NIT:', 0, 0);
            $this->Cell(60, 6, $data['cliente']['ci'], 0, 0);
            $this->Cell(30, 6, 'Nombre:', 0, 0);
            $this->Cell(0, 6, $data['cliente']['nombre'] . ' ' . $data['cliente']['apellido'], 0, 1);
            
            $this->Cell(30, 6, 'Celular:', 0, 0);
            $this->Cell(0, 6, $data['cliente']['celular'], 0, 1);
            
            $this->Ln(10);
        } else {
            // Rollo format - compact info
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(0, 4, 'No: ' . $data['numero_cotizacion'], 0, 1);
            $this->Cell(0, 4, 'Fecha: ' . date('d/m/Y H:i'), 0, 1);
            
            $validityText = $data['validez'] == 30 ? '1 mes' : $data['validez'] . ' dias';
            $this->Cell(0, 4, 'Validez: ' . $validityText, 0, 1);
            $this->Ln(2);
            
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(0, 4, 'CLIENTE:', 0, 1);
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 3, 'CI: ' . $data['cliente']['ci'], 0, 1);
            $this->Cell(0, 3, $data['cliente']['nombre'] . ' ' . $data['cliente']['apellido'], 0, 1);
            $this->Cell(0, 3, 'Tel: ' . $data['cliente']['celular'], 0, 1);
            $this->Ln(3);
        }
    }
    
    function ProductsTable()
    {
        $data = $this->quotationData;
        
        if ($this->format === 'carta') {
            // Table header
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(10, 8, '#', 1, 0, 'C');
            $this->Cell(80, 8, 'PRODUCTO', 1, 0, 'C');
            $this->Cell(20, 8, 'CANT.', 1, 0, 'C');
            $this->Cell(30, 8, 'P. UNIT.', 1, 0, 'C');
            $this->Cell(30, 8, 'SUBTOTAL', 1, 1, 'C');
            
            // Table content
            $this->SetFont('Arial', '', 9);
            $total = 0;
            $counter = 1;
            
            foreach ($data['productos'] as $product) {
                $price = $this->currency === 'USD' ? $product['price'] / $this->exchangeRate : $product['price'];
                $subtotal = $this->currency === 'USD' ? $product['subtotal'] / $this->exchangeRate : $product['subtotal'];
                $total += $subtotal;
                
                $currencySymbol = $this->currency === 'USD' ? '$' : 'Bs.';
                
                $this->Cell(10, 6, $counter, 1, 0, 'C');
                $this->Cell(80, 6, substr($product['name'], 0, 40), 1, 0, 'L');
                $this->Cell(20, 6, $product['quantity'], 1, 0, 'C');
                $this->Cell(30, 6, $currencySymbol . ' ' . number_format($price, 2), 1, 0, 'R');
                $this->Cell(30, 6, $currencySymbol . ' ' . number_format($subtotal, 2), 1, 1, 'R');
                
                $counter++;
            }
            
            // Total
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(140, 8, 'TOTAL:', 1, 0, 'R');
            $currencySymbol = $this->currency === 'USD' ? '$' : 'Bs.';
            $this->Cell(30, 8, $currencySymbol . ' ' . number_format($total, 2), 1, 1, 'R');
            
        } else {
            // Rollo format - compact table
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(0, 4, 'PRODUCTOS:', 0, 1);
            $this->Ln(1);
            
            $this->SetFont('Arial', '', 7);
            $total = 0;
            $counter = 1;
            
            foreach ($data['productos'] as $product) {
                $price = $this->currency === 'USD' ? $product['price'] / $this->exchangeRate : $product['price'];
                $subtotal = $this->currency === 'USD' ? $product['subtotal'] / $this->exchangeRate : $product['subtotal'];
                $total += $subtotal;
                
                $currencySymbol = $this->currency === 'USD' ? '$' : 'Bs.';
                
                $this->Cell(0, 3, $counter . '. ' . substr($product['name'], 0, 30), 0, 1);
                $this->Cell(0, 3, '   Cant: ' . $product['quantity'] . ' x ' . $currencySymbol . number_format($price, 2) . ' = ' . $currencySymbol . number_format($subtotal, 2), 0, 1);
                $this->Ln(1);
                
                $counter++;
            }
            
            // Total
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(0, 1, str_repeat('-', 25), 0, 1, 'C');
            $currencySymbol = $this->currency === 'USD' ? '$' : 'Bs.';
            $this->Cell(0, 5, 'TOTAL: ' . $currencySymbol . ' ' . number_format($total, 2), 0, 1, 'C');
        }
    }
    
    function Observations()
    {
        $data = $this->quotationData;
        
        if (!empty($data['observaciones'])) {
            $this->Ln(10);
            
            if ($this->format === 'carta') {
                $this->SetFont('Arial', 'B', 10);
                $this->Cell(0, 6, 'OBSERVACIONES:', 0, 1);
                $this->SetFont('Arial', '', 9);
                $this->MultiCell(0, 5, $data['observaciones'], 0, 'L');
            } else {
                $this->Ln(3);
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(0, 4, 'OBSERVACIONES:', 0, 1);
                $this->SetFont('Arial', '', 7);
                $this->MultiCell(0, 3, $data['observaciones'], 0, 'L');
            }
        }
        
        // Exchange rate info for USD
        if ($this->currency === 'USD') {
            $this->Ln(5);
            $fontSize = $this->format === 'carta' ? 8 : 6;
            $this->SetFont('Arial', 'I', $fontSize);
            $this->Cell(0, 4, 'Tipo de cambio: 1 USD = Bs. ' . number_format($this->exchangeRate, 2), 0, 1);
        }
    }
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    if (!isset($_POST['quotation_data'])) {
        throw new Exception('Datos de cotización no encontrados');
    }
    
    $quotationData = json_decode($_POST['quotation_data'], true);
    
    if (!$quotationData) {
        throw new Exception('Datos de cotización inválidos');
    }
    
    $currency = $quotationData['currency'] ?? 'BOB';
    $format = $quotationData['format'] ?? 'carta';
    $exchangeRate = $quotationData['exchange_rate'] ?? 6.96;
    
    // Create PDF
    $pdf = new QuotationPDF($quotationData, $currency, $format, $exchangeRate);
    $pdf->AddPage();
    $pdf->QuotationInfo();
    $pdf->ProductsTable();
    $pdf->Observations();
    
    // Output PDF
    $filename = 'Cotizacion_' . $quotationData['numero_cotizacion'] . '_' . date('Ymd_His') . '.pdf';
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    http_response_code(400);
    echo 'Error al generar PDF: ' . $e->getMessage();
}
?>
