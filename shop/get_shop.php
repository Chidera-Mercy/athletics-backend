<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getShopItems() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // Query to get all shop items
        $sql = "SELECT 
                    id,
                    name,
                    price,
                    stock_quantity,
                    category,
                    image
                FROM shop
                ORDER BY category, name";
                
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $shopItems = [];
        
        while ($row = $result->fetch_assoc()) {
            // Convert BLOB to base64 for JSON transmission
            $imageData = null;
            if ($row['image']) {
                $imageData = base64_encode($row['image']);
            }
            
            $shopItems[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => (float)$row['price'], // Convert to float for decimal prices
                'stockQuantity' => (int)$row['stock_quantity'],
                'category' => $row['category'],
                'image' => $imageData ? 'data:image/jpeg;base64,' . $imageData : null
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'count' => count($shopItems),
            'items' => $shopItems
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
    $conn->close();
}

getShopItems();
?>