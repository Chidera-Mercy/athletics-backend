<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("../config.php");

function addOrUpdateNews() {
    $conn = getConnection();
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    if (!isset($data['headline']) || !isset($data['sport_id'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]);
        return;
    }
    
    try {
        // Prepare data
        $headline = $conn->real_escape_string($data['headline']);
        $sport_id = intval($data['sport_id']); // Explicitly cast to integer
        
        // Check if it's an update or new entry
        if (isset($data['news_id'])) {
            // Update existing news
            $news_id = intval($data['news_id']); // Explicitly cast to integer
            $sql = "UPDATE news SET 
                    headline = '$headline', 
                    sport_id = $sport_id 
                    WHERE news_id = $news_id";
        } else {
            // Insert new news
            $sql = "INSERT INTO news (headline, sport_id) 
                    VALUES ('$headline', $sport_id)";
        }
        
        // Execute query
        if ($conn->query($sql)) {
            echo json_encode([
                'status' => 'success',
                'message' => isset($data['news_id']) ? 'News updated' : 'News added'
            ]);
        } else {
            throw new Exception($conn->error);
        }
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
    $conn->close();
}

// Handle the request
addOrUpdateNews();
?>