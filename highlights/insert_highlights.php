<?php
require_once("../config.php");

function insertHighlightImages() {
    $conn = getConnection();
    if (!$conn) {
        die("Database connection failed");
    }
    
    // Directory containing highlight images
    $imageDir = 'highlight-images/';
    
    // Array mapping image files to their corresponding headlines and dates
    $highlightData = [
        'image1.png' => [
            'headline' => 'Elite are the league winners',
            'date' => '2024-01-05',
            'link' => 'http://footballchampionship1.com'
        ],
        'image2.png' => [
            'headline' => "Northside are the men's Champions League winners of 2024",
            'date' => '2023-12-01',
            'link' => 'http://volleyballregional2.com'
        ],
        'image3.png' => [
            'headline' => "Prince's stunning goal put Kasanoma in the lead as they dominated the first half!",
            'date' => '2023-12-01',
            'link' => 'http://volleyballregional3.com'
        ],
        'image4.png' => [
            'headline' => 'Red Army winning the Ashesi Champions League',
            'date' => '2023-12-01',
            'link' => 'http://volleyballregional4.com'
        ],
    ];
    
    foreach ($highlightData as $filename => $data) {
        if (file_exists($imageDir . $filename)) {
            $imageData = file_get_contents($imageDir . $filename);
            
            // Prepare the SQL statement
            $sql = "INSERT INTO highlight (headline, date, link, image) VALUES (?, ?, ?, ?)";
            
            try {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                // Bind parameters
                $stmt->bind_param("ssss", 
                    $data['headline'],
                    $data['date'],
                    $data['link'],
                    $imageData
                );
                
                // Execute the statement
                if ($stmt->execute()) {
                    echo "Successfully inserted highlight: {$data['headline']}\n";
                } else {
                    echo "Error inserting highlight {$filename}: " . $stmt->error . "\n";
                }
                
                $stmt->close();
                
            } catch(Exception $e) {
                echo "Error inserting highlight {$filename}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Image file not found: {$filename}\n";
        }
    }
    
    // Close the connection
    $conn->close();
}

// Run the function
insertHighlightImages();