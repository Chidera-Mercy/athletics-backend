<?php
require_once("../config.php");

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $conn = getConnection();

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($data['password'] ?? '');

    if (!$email || !$password) {
        throw new Exception('Email and password are required.');
    }

    // Prepare statement to get user data
    $stmt = $conn->prepare("
        SELECT user_id, first_name, last_name, email, password, role 
        FROM users 
        WHERE email = ?
    ");

    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        throw new Exception('Database execution failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid credentials.');
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Invalid credentials.');
    }

    // Remove sensitive data before sending
    unset($user['password']);

    // Create session token (optional, for additional security)
    $sessionToken = bin2hex(random_bytes(32));

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $sessionToken // Include if you're using session tokens
    ]);

} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>