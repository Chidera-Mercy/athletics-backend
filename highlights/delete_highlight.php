<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header('Access-Control-Allow-Methods: GET, POST, DELETE');


require_once("../config.php");

$conn = getConnection();

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    return;
}

function deleteHighlight($id) {
    global $conn;

    try {
        $sql = "DELETE FROM highlight WHERE highlight_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Highlight deleted successfully'
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

if (isset($_GET['id'])) {
    deleteHighlight($_GET['id']);
}
?>