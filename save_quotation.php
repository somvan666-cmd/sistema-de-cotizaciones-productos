<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['numero_cotizacion']) || !isset($input['cliente']) || !isset($input['productos'])) {
        throw new Exception('Datos incompletos');
    }
    
    $quotationNumber = trim($input['numero_cotizacion']);
    $client = $input['cliente'];
    $products = $input['productos'];
    $validity = (int)$input['validez'];
    $observations = trim($input['observaciones'] ?? '');
    $total = (float)$input['total'];
    
    // Validate client data
    if (empty($client['ci']) || empty($client['nombre'])) {
        throw new Exception('Información del cliente incompleta');
    }
    
    // Validate products
    if (empty($products)) {
        throw new Exception('Debe agregar al menos un producto');
    }
    
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    try {
        // Check if client exists, if not create it
        $clientId = null;
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE ci = :ci");
        $stmt->bindParam(':ci', $client['ci']);
        $stmt->execute();
        $existingClient = $stmt->fetch();
        
        if ($existingClient) {
            $clientId = $existingClient['id'];
            
            // Update client information
            $stmt = $pdo->prepare("UPDATE clientes SET nombre = :nombre, apellido = :apellido, celular = :celular, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindParam(':nombre', $client['nombre']);
            $stmt->bindParam(':apellido', $client['apellido']);
            $stmt->bindParam(':celular', $client['celular']);
            $stmt->bindParam(':id', $clientId);
            $stmt->execute();
        } else {
            // Create new client
            $stmt = $pdo->prepare("INSERT INTO clientes (ci, nombre, apellido, celular) VALUES (:ci, :nombre, :apellido, :celular)");
            $stmt->bindParam(':ci', $client['ci']);
            $stmt->bindParam(':nombre', $client['nombre']);
            $stmt->bindParam(':apellido', $client['apellido']);
            $stmt->bindParam(':celular', $client['celular']);
            $stmt->execute();
            $clientId = $pdo->lastInsertId();
        }
        
        // Check if quotation number already exists
        $stmt = $pdo->prepare("SELECT id FROM cotizaciones WHERE numero_cotizacion = :numero");
        $stmt->bindParam(':numero', $quotationNumber);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            throw new Exception('El número de cotización ya existe');
        }
        
        // Create quotation
        $stmt = $pdo->prepare("INSERT INTO cotizaciones (numero_cotizacion, cliente_id, validez, observaciones, total) VALUES (:numero, :cliente_id, :validez, :observaciones, :total)");
        $stmt->bindParam(':numero', $quotationNumber);
        $stmt->bindParam(':cliente_id', $clientId);
        $stmt->bindParam(':validez', $validity);
        $stmt->bindParam(':observaciones', $observations);
        $stmt->bindParam(':total', $total);
        $stmt->execute();
        
        $quotationId = $pdo->lastInsertId();
        
        // Add quotation details
        $stmt = $pdo->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (:cotizacion_id, :producto_id, :cantidad, :precio_unitario, :subtotal)");
        
        foreach ($products as $product) {
            $stmt->bindParam(':cotizacion_id', $quotationId);
            $stmt->bindParam(':producto_id', $product['id']);
            $stmt->bindParam(':cantidad', $product['quantity']);
            $stmt->bindParam(':precio_unitario', $product['price']);
            $stmt->bindParam(':subtotal', $product['subtotal']);
            $stmt->execute();
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cotización guardada exitosamente',
            'quotation_id' => $quotationId,
            'client_id' => $clientId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
