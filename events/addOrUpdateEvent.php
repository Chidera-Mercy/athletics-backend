<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once("../config.php");

function addOrUpdateEvent() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Check for required fields
        $requiredFields = ['name', 'sport_id', 'location', 'date', 'time'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : null;
        $name = $conn->real_escape_string($_POST['name']);
        $sport_id = intval($_POST['sport_id']);
        $location = $conn->real_escape_string($_POST['location']);
        $date = $conn->real_escape_string($_POST['date']);
        $time = $conn->real_escape_string($_POST['time']);
        $details = isset($_POST['details']) ? $conn->real_escape_string($_POST['details']) : '';
        $result = isset($_POST['result']) ? $conn->real_escape_string($_POST['result']) : '';
        
        if ($event_id) {
            // Update existing event
            $sql = "UPDATE events SET 
                    event_name = '$name', 
                    sport_id = $sport_id, 
                    location = '$location', 
                    event_date = '$date', 
                    event_time = '$time', 
                    details = '$details', 
                    result = '$result' 
                    WHERE event_id = $event_id";
        } else {
            // Insert new event
            $sql = "INSERT INTO events 
                    (event_name, sport_id, location, event_date, event_time, details, result) 
                    VALUES 
                    ('$name', $sport_id, '$location', '$date', '$time', '$details', '$result')";
        }
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                'status' => 'success', 
                'message' => $event_id ? 'Event updated successfully' : 'Event added successfully'
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

addOrUpdateEvent();
?>