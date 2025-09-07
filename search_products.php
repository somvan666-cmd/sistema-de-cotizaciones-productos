<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['search']) || empty(trim($input['search']))) {
        echo json_encode([]);
        exit;
    }
    
    $searchTerm = trim($input['search']);
    $pdo = getConnection();
    
    // Search products by name or description
    $sql = "SELECT id, nombre, descripcion, imagen, precio_unitario 
            FROM products 
            WHERE nombre LIKE :search OR descripcion LIKE :search 
            ORDER BY nombre ASC 
            LIMIT 20";
    
    $stmt = $pdo->prepare($sql);
    $searchParam = '%' . $searchTerm . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    
    $products = $stmt->fetchAll();
    
    // Format the response
    $response = [];
    foreach ($products as $product) {
        $response[] = [
            'id' => (int)$product['id'],
            'nombre' => $product['nombre'],
            'descripcion' => $product['descripcion'],
            'imagen' => $product['imagen'],
            'precio_unitario' => (float)$product['precio_unitario']
        ];
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la base de datos',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Error en la solicitud',
        'message' => $e->getMessage()
    ]);
}
?>
