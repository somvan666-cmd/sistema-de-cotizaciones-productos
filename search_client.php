<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['ci']) || empty(trim($input['ci']))) {
        echo json_encode(['found' => false]);
        exit;
    }
    
    $ci = trim($input['ci']);
    $pdo = getConnection();
    
    // Search client by CI
    $sql = "SELECT id, ci, nombre, apellido, celular 
            FROM clientes 
            WHERE ci = :ci 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
    $stmt->execute();
    
    $client = $stmt->fetch();
    
    if ($client) {
        echo json_encode([
            'found' => true,
            'client' => [
                'id' => (int)$client['id'],
                'ci' => $client['ci'],
                'nombre' => $client['nombre'],
                'apellido' => $client['apellido'],
                'celular' => $client['celular']
            ]
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'found' => false,
        'error' => 'Error en la base de datos',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'found' => false,
        'error' => 'Error en la solicitud',
        'message' => $e->getMessage()
    ]);
}
?>
