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

function addOrUpdateTeam() {
    $conn = getConnection();
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    if (!isset($data['team_name']) || !isset($data['sport_id'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]);
        return;
    }
    
    try {
        // Prepare data
        $team_name = $conn->real_escape_string($data['team_name']);
        $sport_id = intval($data['sport_id']); // Explicitly cast to integer
        
        // Optional fields
        $team_email = isset($data['team_email']) ? $conn->real_escape_string($data['team_email']) : null;
        $coach_name = isset($data['coach_name']) ? $conn->real_escape_string($data['coach_name']) : null;
        
        // Check if it's an update or new entry
        if (isset($data['team_id'])) {
            // Update existing team
            $team_id = intval($data['team_id']); // Explicitly cast to integer
            
            // Construct SQL with optional fields
            $sql = "UPDATE teams SET 
                    team_name = '$team_name', 
                    sport_id = $sport_id";
            
            // Add optional fields if they exist
            if ($team_email !== null) {
                $sql .= ", team_email = '$team_email'";
            }
            if ($coach_name !== null) {
                $sql .= ", coach_name = '$coach_name'";
            }
            
            $sql .= " WHERE team_id = $team_id";
        } else {
            // Insert new team
            $sql = "INSERT INTO teams (team_name, sport_id, team_email, coach_name) 
                    VALUES ('$team_name', $sport_id, " . 
                    ($team_email ? "'$team_email'" : "NULL") . ", " . 
                    ($coach_name ? "'$coach_name'" : "NULL") . ")";
        }
        
        // Execute query
        if ($conn->query($sql)) {
            echo json_encode([
                'status' => 'success',
                'message' => isset($data['team_id']) ? 'Team updated' : 'Team added'
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
addOrUpdateTeam();
?>