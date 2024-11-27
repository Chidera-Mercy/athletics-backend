<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: *');

require_once("../config.php");

function getNews() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // JOIN query to get news with sport names
        $sql = "SELECT 
                    n.news_id,
                    n.headline,
                    n.date,
                    s.sport_name
                FROM news n
                LEFT JOIN sports s ON n.sport_id = s.sport_id
                ORDER BY n.date DESC";
                
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $news = [];
        
        while ($row = $result->fetch_assoc()) {
            // Format date for better readability
            $newsDate = $row['date'] ? date('Y-m-d', strtotime($row['date'])) : null;
            
            $news[] = [
                'id' => $row['news_id'],
                'headline' => $row['headline'],
                'sport' => $row['sport_name'],
                'date' => $newsDate,
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'count' => count($news),
            'news' => $news
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
getNews();
?>