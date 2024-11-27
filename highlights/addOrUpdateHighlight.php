<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once("../config.php");

$conn = getConnection();

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    return;
}

function addOrUpdateHighlight() {
    global $conn;

    // Get posted data
    $title = $_POST['title'] ?? null;
    $date = $_POST['date'] ?? null;
    $link = $_POST['link'] ?? null;
    $highlight_id = isset($_POST['highlight_id']) ? intval($_POST['highlight_id']) : null;

    // Validate required fields
    if (!$title || !$date) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]);
        return;
    }

    try {
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
        }

        // Prepare data
        $title = $conn->real_escape_string($title);
        $link = $conn->real_escape_string($link);

        if ($highlight_id) {
            // Update existing highlight
            if ($image) {
                $sql = "UPDATE highlight SET 
                        headline = '$title', 
                        date = '$date', 
                        link = '$link', 
                        image = ? 
                        WHERE highlight_id = $highlight_id";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('b', $image);
                $stmt->send_long_data(0, $image);
                $result = $stmt->execute();
            } else {
                $sql = "UPDATE highlight SET 
                        headline = '$title', 
                        date = '$date', 
                        link = '$link' 
                        WHERE highlight_id = $highlight_id";
                $result = $conn->query($sql);
            }
        } else {
            // Insert new highlight
            if ($image) {
                $sql = "INSERT INTO highlight (headline, date, link, image) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $title, $date, $link, $image);
                $result = $stmt->execute();
            } else {
                $sql = "INSERT INTO highlight (headline, date, link) VALUES ('$title', '$date', '$link')";
                $result = $conn->query($sql);
            }
        }

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => $highlight_id ? 'Highlight updated' : 'Highlight added'
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
    } finally {
        $conn->close();
    }
}

// Call the function to process the request
addOrUpdateHighlight();
?>