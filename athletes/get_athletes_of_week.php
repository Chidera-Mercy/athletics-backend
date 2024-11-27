<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getAthletesOfTheWeek() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // SQL query to get athletes of the week with their details
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
                    s.snap_link,
                    s.insta_link,
                    s.x_link,
                    GROUP_CONCAT(ach.achievement SEPARATOR '|||') AS achievements
                FROM week_athletes wa
                JOIN athletes a ON wa.athlete_id = a.athlete_id
                LEFT JOIN sports s ON a.sport_id = s.sport_id
                LEFT JOIN achievements ach ON a.athlete_id = ach.athlete_id
                GROUP BY a.athlete_id
                ORDER BY a.first_name, a.last_name";
                
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $athletesOfTheWeek = [];
        
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
            
            $athletesOfTheWeek[] = [
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
                'achievements' => $achievements,
                'socialLinks' => [
                    'snapchat' => $row['snap_link'],
                    'instagram' => $row['insta_link'],
                    'x' => $row['x_link']
                ]
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'count' => count($athletesOfTheWeek),
            'athletesOfTheWeek' => $athletesOfTheWeek
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

getAthletesOfTheWeek();
?>