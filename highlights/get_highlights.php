<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getHighlights() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        $sql = "SELECT highlight_id, headline, date, link, image FROM highlight ORDER BY date DESC";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $highlights = [];
        
        while ($row = $result->fetch_assoc()) {
            // Convert the binary image data to base64
            $imageBase64 = base64_encode($row['image']);
            
            $highlights[] = [
                'id' => $row['highlight_id'],
                'title' => $row['headline'],
                'date' => $row['date'],
                'link' => $row['link'],
                'image' => 'data:image/png;base64,' . $imageBase64
            ];
        }
        
        echo json_encode($highlights);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    $conn->close();
}

getHighlights();
?>