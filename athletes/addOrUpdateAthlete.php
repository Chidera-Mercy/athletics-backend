<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

require_once("../config.php");

$conn = getConnection();

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

function addOrUpdateAthlete() {
    global $conn;

    // Get posted data
    $first_name = $_POST['firstName'] ?? null;
    $last_name = $_POST['lastName'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $sport_id = isset($_POST['sport']) ? intval($_POST['sport']) : null;
    $position = $_POST['position'] ?? null;
    $year_group = isset($_POST['yearGroup']) ? intval($_POST['yearGroup']) : null;
    $nationality = $_POST['nationality'] ?? null;
    $athlete_id = isset($_POST['athlete_id']) ? intval($_POST['athlete_id']) : null;

    // Validate required fields
    if (!$first_name || !$last_name || !$gender || !$sport_id) {
        http_response_code(400);
        die(json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]));
    }

    try {
        // Handle image upload
        $image_data = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_size = $_FILES['image']['size'];
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
        }

        if ($athlete_id) {
            // Update existing athlete
            if ($image_data !== null) {
                $sql = "UPDATE athletes SET 
                        first_name = ?, 
                        last_name = ?, 
                        gender = ?, 
                        sport_id = ?, 
                        position = ?, 
                        year_group = ?, 
                        nationality = ?, 
                        image = ? 
                        WHERE athlete_id = ?";
                $stmt = $conn->prepare($sql);
                $null = null; // Use a null variable for bind_param
                $stmt->bind_param("sssisisbi", 
                    $first_name, 
                    $last_name, 
                    $gender, 
                    $sport_id, 
                    $position, 
                    $year_group, 
                    $nationality, 
                    $null, 
                    $athlete_id
                );
                $stmt->send_long_data(7, $image_data); // Send blob data separately
            } else {
                $sql = "UPDATE athletes SET 
                        first_name = ?, 
                        last_name = ?, 
                        gender = ?, 
                        sport_id = ?, 
                        position = ?, 
                        year_group = ?, 
                        nationality = ? 
                        WHERE athlete_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssisisi", 
                    $first_name, 
                    $last_name, 
                    $gender, 
                    $sport_id, 
                    $position, 
                    $year_group, 
                    $nationality, 
                    $athlete_id
                );
            }
        } else {
            // Insert new athlete
            if ($image_data !== null) {
                $sql = "INSERT INTO athletes (first_name, last_name, gender, sport_id, position, year_group, nationality, image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $null = null; // Use a null variable for bind_param
                $stmt->bind_param("sssisisb", 
                    $first_name, 
                    $last_name, 
                    $gender, 
                    $sport_id, 
                    $position, 
                    $year_group, 
                    $nationality, 
                    $null
                );
                $stmt->send_long_data(7, $image_data); // Send blob data separately
            } else {
                $sql = "INSERT INTO athletes (first_name, last_name, gender, sport_id, position, year_group, nationality) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssisis", 
                    $first_name, 
                    $last_name, 
                    $gender, 
                    $sport_id, 
                    $position, 
                    $year_group, 
                    $nationality
                );
            }
        }

        if ($stmt->execute()) {
            $last_id = $athlete_id ? $athlete_id : $conn->insert_id;
            echo json_encode([
                'status' => 'success',
                'message' => $athlete_id ? 'Athlete updated successfully' : 'Athlete added successfully',
                'athlete_id' => $last_id
            ]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch(Exception $e) {
        http_response_code(500);
        die(json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]));
    } finally {
        if (isset($stmt)) $stmt->close();
    }
}

// Process the request
addOrUpdateAthlete();

$conn->close();
?>