<?php
// Concept Television Studios Limited
// Asset Booking System
// PME
// 20231230-0842


// Include database configuration
require_once '../x-dbconfig.php'; // Make sure to create this file with your database configuration


// Create a connection to the MySQL database server
$conn = new mysqli($servername, $username, $password);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Close the connection to the MySQL server
$conn->close();

// Create a new connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statements to create tables based on your schema
$sql = "
    CREATE TABLE AssetBlock (
        AssetBlockID INT AUTO_INCREMENT PRIMARY KEY,
        AssetID INT,
        BlockID INT,
        FOREIGN KEY (AssetID) REFERENCES Assets(AssetID),
        FOREIGN KEY (BlockID) REFERENCES Assets(AssetID)
    );

";

if ($conn->multi_query($sql) === TRUE) {
    echo "Tables created successfully\n";
} else {
    echo "Error creating tables: " . $conn->error . "\n";
}

// Close the connection
$conn->close();
?>
