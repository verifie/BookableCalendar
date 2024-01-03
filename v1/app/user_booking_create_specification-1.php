<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["UserType"] !== 'admin') {
    header("location: login.php");
    exit;
}


// Include database configuration
if (!require_once 'x-dbconfig.php') {
    die("Failed to include 'x-dbconfig.php'");
}

// Setup User Alert Message variable
$message = '';

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concept Booking App: Create Booking Specification.</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php
// Include the header content
require 'x-header.php';
?>

<div class="container">

    <?php
    // Check if user is an admin and display admin functions
    if (isset($_SESSION["UserType"]) && $_SESSION["UserType"] === "admin") {
    ?>

        <!-- =========================================== ADMINISTRATOR CONTENT AND FUNCTIONS =================================== -->
        <div class="row">

            <div class="col-8">
                <h1>
                    ADMINISTRATOR ACCOUNT - <?php echo htmlspecialchars($_SESSION["username"]); ?> 
                </h1>
                <br/>
                <p>This page is only to be used by a standard user account, not an admin account.</p>
            </div>

            <!-- Right column admin Controls -->
            <div class="col-4">
                <!-- Admin controls content removed from user pages-->
            </div>

        </div>

    <?php
    } else {
    ?>

        <!-- =========================================== USER CONTENT AND FUNCTIONS =================================== -->

        <div class="row">

            <div class="col-8">
                <!-- User Welcome heading -->
                <h1 class="mt-4">
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                </h1>
                <br/> 

                <!-- User Content -->
                <p>
                    <!--INSERT NEW FUNCTIONALITY HERE -->
                </p>
            </div>

            <!-- Right column admin Controls -->
            <div class="col-4">
                <?php require_once 'x-user-controls.php'; ?>
            </div>

        </div>
        
        <!-- =========================================== END USER CONTENT AND FUNCTIONS =================================== -->


    <?php
    }
    ?>

</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
