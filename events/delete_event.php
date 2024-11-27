<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');

require_once("../config.php");

function deleteEvent() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        $event_id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if (!$event_id) {
            throw new Exception("Invalid event ID");
        }
        
        // Delete event
        $sql = "DELETE FROM events WHERE event_id = $event_id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Event deleted successfully'
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

deleteEvent();
?>