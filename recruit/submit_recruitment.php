<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../libs/PHPMailer/src/Exception.php';
require_once '../libs/PHPMailer/src/PHPMailer.php';
require_once '../libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once("../config.php");

function submitRecruitment() {
    // Create a response array
    $response = [
        'status' => 'error',
        'message' => 'Unknown error occurred'
    ];

    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed'
        ]);
        return;
    }
    
    try {
        // Validate required fields
        $requiredFields = [
            'firstName', 'lastName', 'dateOfBirth', 'gender', 'height', 
            'weight', 'graduationYear', 'gpa', 'sport', 'team', 
            'position', 'email', 'phoneNumber'
        ];
        
        // Check for missing required fields
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ]);
            return;
        }

        // Get team details to send email
        $teamQuery = "SELECT team_email, coach_name FROM teams WHERE team_id = ?";
        $teamStmt = $conn->prepare($teamQuery);
        $teamStmt->bind_param("i", $_POST['team']);
        $teamStmt->execute();
        $teamResult = $teamStmt->get_result();
        $teamData = $teamResult->fetch_assoc();

        // Prepare PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;     // Your SMTP server (replace with your config)
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME; // SMTP username from config
            $mail->Password   = SMTP_PASSWORD; // SMTP password from config
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('abaraonyechidera@gmail.com', 'Recruitment Team');
            $mail->addAddress($teamData['team_email'] ?? 'default@example.com');
            $mail->addReplyTo($_POST['email'], $_POST['firstName'] . ' ' . $_POST['lastName']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "New Athlete Recruitment Application";
            
            // Prepare HTML email body
            $emailBody = "<h2>New Recruitment Application Details</h2><ul>";
            foreach ($_POST as $key => $value) {
                $emailBody .= "<li><strong>" . htmlspecialchars(ucfirst($key)) . ":</strong> " . htmlspecialchars($value) . "</li>";
            }
            $emailBody .= "</ul>";

            $mail->Body = $emailBody;
            $mail->AltBody = strip_tags($emailBody);

            // Handle file upload if photo is present
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/recruitment/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $photoPath = $uploadDir . uniqid() . '_' . basename($_FILES['photo']['name']);
                move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);

                // Attach photo if uploaded successfully
                if (file_exists($photoPath)) {
                    $mail->addAttachment($photoPath);
                }
            }

            // Send email
            $mailSent = $mail->send();

            // Respond with success
            http_response_code(200);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Application submitted successfully',
                'emailSent' => $mailSent,
                'photoUploaded' => !is_null($photoPath)
            ]);
            
        } catch (PHPMailerException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Email sending failed: ' . $mail->ErrorInfo
            ]);
        }
        
    } catch(Exception $e) {
        // Catch any unexpected errors
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => $e->getMessage()
        ]);
    } finally {
        // Always close the connection
        if ($conn) {
            $conn->close();
        }
    }
}

// Explicitly catch any PHP errors and return as JSON
try {
    submitRecruitment();
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $t->getMessage()
    ]);
}
?>