<?php
// Concept Television Studios Limited
// Asset Booking System
// PME
// 20231230-0842


// Include database configuration
require_once '../x-dbconfig.php'; // Make sure to create this file with your database configuration

// Admin User Credentials
$adminUsername = "siteadmin";
$adminPassword = "abcABC123";

// Set user type to 'admin'
$usertype = "admin";

// --------------------------------------------------------------------------------------------------------------------
// Run the script
try {
    // Create a connection to the MySQL database server
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the admin user already exists in the database
    $checkSql = "SELECT UserID FROM Users WHERE Username = 'siteadmin' OR Username = 'admin'";
    $checkStmt = $conn->query($checkSql);

    if ($checkStmt->rowCount() === 0) {
        // The admin user does not exist, so we can create it
        $sql = "INSERT INTO Users (Username, Password, UserType) VALUES (:username, :password, :usertype)";
        $stmt = $conn->prepare($sql);

        // Hash the password using PHP's password_hash function
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

        // Bind parameters
        $stmt->bindParam(':username', $adminUsername);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':usertype', $usertype);

        // Execute the statement
        $stmt->execute();

        echo "Admin user 'siteadmin' created successfully.\n";
    } else {
        echo "Admin user 'siteadmin' or 'admin' already exists in the database.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Close the database connection
$conn = null;
?>
