<?php
// Add these headers at the very top of the script
header('Access-Control-Allow-Origin: *'); // Specify exact origin instead of *
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 1728000');
header('Content-Type: application/json');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("../config.php");

// Receive raw POST data
$input = json_decode(file_get_contents('php://input'), true);

function setAthletesOfWeek($athleteIds) {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        return;
    }
    
    // Start a transaction to ensure atomicity
    $conn->begin_transaction();
    
    try {
        // First, clear existing records in weekathletes table
        $clearSql = "DELETE FROM week_athletes";
        if (!$conn->query($clearSql)) {
            throw new Exception("Failed to clear existing athletes of the week");
        }
        
        // Prepare statement to insert new athletes of the week
        $insertSql = "INSERT INTO week_athletes (athlete_id) VALUES (?)";
        $stmt = $conn->prepare($insertSql);
        
        // Insert each selected athlete
        foreach ($athleteIds as $athleteId) {
            // Validate that the athlete exists
            $validationSql = "SELECT athlete_id FROM athletes WHERE athlete_id = ?";
            $validationStmt = $conn->prepare($validationSql);
            $validationStmt->bind_param("i", $athleteId);
            $validationStmt->execute();
            $validationResult = $validationStmt->get_result();
            
            if ($validationResult->num_rows === 0) {
                throw new Exception("Invalid athlete ID: $athleteId");
            }
            
            // Bind and execute insert
            $stmt->bind_param("i", $athleteId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert athlete $athleteId");
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Athletes of the Week updated successfully'
        ]);
        
    } catch(Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
    // Close connections
    $stmt->close();
    $conn->close();
}

// Validate input
if (!isset($input['athleteIds']) || !is_array($input['athleteIds']) || count($input['athleteIds']) !== 2) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Must provide exactly 2 athlete IDs'
    ]);
    exit;
}

// Call the function with athlete IDs
setAthletesOfWeek($input['athleteIds']);
?>