<?php
require_once("../config.php");

function insertCompleteAthletesList() {
    $conn = getConnection();
    if (!$conn) {
        die("Database connection failed");
    }
    
    // Directory containing athlete images
    $imageDir = 'athletes-images/';
    
    // Array of athlete data with corresponding image files
    $athleteData = [
        'kwame.png' => [
            'first_name' => 'Kwame',
            'last_name' => 'Nkrumah',
            'gender' => 'm',
            'sport_id' => 1,
            'position' => 'Forward',
            'year_group' => 2,
            'nationality' => 'Ghana'
        ],
        'ngozi.png' => [
            'first_name' => 'Ngozi',
            'last_name' => 'Okonjo',
            'gender' => 'f',
            'sport_id' => 1,
            'position' => 'Midfielder',
            'year_group' => 1,
            'nationality' => 'Nigeria'
        ],
        'samuel.png' => [
            'first_name' => 'Samuel',
            'last_name' => 'Eto',
            'gender' => 'm',
            'sport_id' => 1,
            'position' => 'Defender',
            'year_group' => 3,
            'nationality' => 'Cameroon'
        ],
        // Add all other athletes following the same pattern...
        'patrick.png' => [
            'first_name' => 'Patrick',
            'last_name' => 'Gama',
            'gender' => 'm',
            'sport_id' => 1,
            'position' => 'Striker',
            'year_group' => 3,
            'nationality' => 'Zambia'
        ]
    ];
    
    foreach ($athleteData as $filename => $data) {
        if (file_exists($imageDir . $filename)) {
            $imageData = file_get_contents($imageDir . $filename);
            
            // Prepare the SQL statement
            $sql = "INSERT INTO athletes (first_name, last_name, gender, sport_id, position, year_group, nationality, image) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            try {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                // Bind parameters
                $stmt->bind_param("sssisiss", 
                    $data['first_name'],
                    $data['last_name'],
                    $data['gender'],
                    $data['sport_id'],
                    $data['position'],
                    $data['year_group'],
                    $data['nationality'],
                    $imageData
                );
                
                // Execute the statement
                if ($stmt->execute()) {
                    echo "Successfully inserted athlete: {$data['first_name']} {$data['last_name']}\n";
                } else {
                    echo "Error inserting athlete {$filename}: " . $stmt->error . "\n";
                }
                
                $stmt->close();
                
            } catch(Exception $e) {
                echo "Error inserting athlete {$filename}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Image file not found: {$filename}\n";
        }
    }
    
    // Close the connection
    $conn->close();
}

// Run the function
insertCompleteAthletesList();
?>