<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getTeams() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // Fetch teams with their associated sport name and additional team details
        $sql = "SELECT t.team_id, t.team_name, t.sport_id, t.team_email, t.coach_name, s.sport_name 
                FROM teams t
                JOIN sports s ON t.sport_id = s.sport_id";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $teams = [];
        
        while ($row = $result->fetch_assoc()) {
            $teams[] = [
                'id' => $row['team_id'],
                'name' => $row['team_name'],
                'sportId' => $row['sport_id'],
                'sportName' => $row['sport_name'],
                'team_email' => $row['team_email'], // Added team email
                'coach_name' => $row['coach_name']  // Added coach name
            ];
        }
        
        echo json_encode($teams);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    $conn->close();
}

getTeams();
?>