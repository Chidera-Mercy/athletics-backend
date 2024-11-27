<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getAthletes() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // SQL query to get athletes with their achievements
        $sql = "SELECT 
                    a.athlete_id,
                    a.first_name,
                    a.last_name,
                    a.gender,
                    a.position,
                    a.year_group,
                    a.nationality,
                    a.image,
                    s.sport_name,
                    GROUP_CONCAT(ach.achievement SEPARATOR '|||') AS achievements
                FROM athletes a
                LEFT JOIN sports s ON a.sport_id = s.sport_id
                LEFT JOIN achievements ach ON a.athlete_id = ach.athlete_id
                GROUP BY a.athlete_id
                ORDER BY a.first_name, a.last_name";
                
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $athletes = [];
        
        while ($row = $result->fetch_assoc()) {
            // Convert BLOB to base64 for JSON transmission
            $imageData = null;
            if ($row['image']) {
                $imageData = base64_encode($row['image']);
            }
            
            // Parse achievements
            $achievements = $row['achievements'] 
                ? explode('|||', $row['achievements']) 
                : [];
            
            $athletes[] = [
                'id' => $row['athlete_id'],
                'firstName' => $row['first_name'],
                'lastName' => $row['last_name'],
                'fullName' => $row['first_name'] . ' ' . $row['last_name'],
                'gender' => $row['gender'],
                'sport' => $row['sport_name'],
                'position' => $row['position'],
                'yearGroup' => $row['year_group'],
                'nationality' => $row['nationality'],
                'image' => $imageData ? 'data:image/png;base64,' . $imageData : null,
                'achievements' => $achievements
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'count' => count($athletes),
            'athletes' => $athletes
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

getAthletes();
?>