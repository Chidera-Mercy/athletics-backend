<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, DELETE');

require_once("../config.php");

function deleteTeam($id) {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Prepare statement to prevent SQL injection
        $sql = "DELETE FROM teams WHERE team_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Team deleted successfully'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
        
        // Close statement
        $stmt->close();
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
if (isset($_GET['id'])) {
    deleteTeam($_GET['id']);
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'No team ID provided'
    ]);
}
?>