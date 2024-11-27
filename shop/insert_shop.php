<?php
require_once("../config.php");

function insertShop() {
    $conn = getConnection();
    if (!$conn) {
        die("Database connection failed");
    }
    
    // Increase max allowed packet for this session
    try {
        $conn->query("SET GLOBAL max_allowed_packet=16777216"); // 16MB
    } catch (Exception $e) {
        echo "Warning: Could not set max_allowed_packet. Some large images might fail to upload.\n";
    }
    
    // Directory containing shop item images
    $imageDir = 'shop-images/';
    
    // Array of shop item data with corresponding image files
    $shopData = [
        'basketBall.jpg' => [
            'name' => 'Basketball',
            'price' => 70,
            'stock_quantity' => 50,
            'category' => 'Equipment'
        ],
        'sportBoots.jpg' => [
            'name' => 'Field sport Shoes',
            'price' => 60,
            'stock_quantity' => 100,
            'category' => 'Footwear'
        ],
        'volleyBall.jpg' => [
            'name' => 'Volley Ball',
            'price' => 45,
            'stock_quantity' => 50,
            'category' => 'Equipment'
        ],
        'jerseyPair.jpg' => [
            'name' => 'Basketball Jersey',
            'price' => 40,
            'stock_quantity' => 75,
            'category' => 'Apparel'
        ],
        'helmet.jpg' => [
            'name' => 'Helmet',
            'price' => 30,
            'stock_quantity' => 120,
            'category' => 'Equipment'
        ],
        'karateBelt.jpeg' => [  // Changed from .jpeg to .jpg
            'name' => 'Karate Belt',
            'price' => 50,
            'stock_quantity' => 90,
            'category' => 'Apparel'
        ],
        'jerseyShort.jpg' => [
            'name' => 'Jersey Shorts Only',
            'price' => 20,
            'stock_quantity' => 150,
            'category' => 'Apparel'
        ],
        'jerseyTops.jpg' => [
            'name' => 'Jersey Tops Only',
            'price' => 25,
            'stock_quantity' => 110,
            'category' => 'Apparel'
        ],
        'karateUniform.jpg' => [
            'name' => 'Karate Uniforms (Both tops and trouser)',
            'price' => 35,
            'stock_quantity' => 130,
            'category' => 'Apparel'
        ],
        'soccerBall.jpg' => [
            'name' => 'Soccer ball',
            'price' => 55,
            'stock_quantity' => 100,
            'category' => 'Equipment'
        ],
        'socks.jpg' => [
            'name' => 'Socks',
            'price' => 15,
            'stock_quantity' => 200,
            'category' => 'Footwear'
        ]
    ];
    
    foreach ($shopData as $filename => $data) {
        $imagePath = $imageDir . $filename;
        if (file_exists($imagePath)) {
            // Read image file directly
            try {
                $imageData = file_get_contents($imagePath);
                if ($imageData === false) {
                    echo "Error reading image file: {$filename}\n";
                    continue;
                }
                
                // Prepare the SQL statement
                $sql = "INSERT INTO shop (name, price, stock_quantity, category, image) 
                       VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                // Bind parameters
                $stmt->bind_param("sdiss", 
                    $data['name'],
                    $data['price'],
                    $data['stock_quantity'],
                    $data['category'],
                    $imageData
                );
                
                // Execute the statement
                if ($stmt->execute()) {
                    echo "Successfully inserted item: {$data['name']}\n";
                } else {
                    echo "Error inserting item {$filename}: " . $stmt->error . "\n";
                }
                
                $stmt->close();
                
            } catch(Exception $e) {
                echo "Error inserting item {$filename}: " . $e->getMessage() . "\n";
            }
            
            // Add a small delay between inserts
            sleep(1);
            
        } else {
            echo "Image file not found: {$filename}\n";
        }
    }
    
    // Close the connection
    $conn->close();
}

// Run the function
insertShop();
?>