<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getSports() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        $sql = "SELECT sport_id, sport_name, snap_link, insta_link, x_link FROM sports";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $sports = [];
        
        while ($row = $result->fetch_assoc()) {
            // Convert the binary image data to base64
            
            $sports[] = [
                'id' => $row['sport_id'],
                'name' => $row['sport_name'],
                'snap' => $row['snap_link'],
                'insta' => $row['insta_link'],
                'x' => $row['x_link']
            ];
        }
        
        echo json_encode($sports);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    $conn->close();
}

getSports();
?>