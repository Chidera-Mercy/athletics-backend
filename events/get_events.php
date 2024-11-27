<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once("../config.php");

function getEvents() {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // JOIN query to get events with sport names
        $sql = "SELECT 
                    e.event_id,
                    e.event_name,
                    e.location,
                    e.event_date,
                    e.event_time,
                    e.details,
                    e.result,
                    s.sport_name
                FROM events e
                LEFT JOIN sports s ON e.sport_id = s.sport_id
                ORDER BY e.event_date DESC, e.event_time DESC";
                
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Format date and time for better readability
            $eventDate = $row['event_date'] ? date('Y-m-d', strtotime($row['event_date'])) : null;
            $eventTime = $row['event_time'] ? date('H:i', strtotime($row['event_time'])) : null;
            
            $events[] = [
                'id' => $row['event_id'],
                'name' => $row['event_name'],
                'sport' => $row['sport_name'],
                'location' => $row['location'],
                'date' => $eventDate,
                'time' => $eventTime,
                'details' => $row['details'],
                'result' => $row['result']
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'count' => count($events),
            'events' => $events
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

getEvents();
?>