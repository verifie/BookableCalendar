<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include header file (optional, if you have a header file)
// include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Request Received</title>
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
            </div>

            <!-- Right column admin Controls -->
            <div class="col-4">
                <?php require_once 'x-admin-controls.php'; ?>
            </div>

        </div>
        <!-- =========================================== END ADMINISTRATOR CONTENT AND FUNCTIONS =================================== -->
        

    <?php
    } else {
    ?>

        <!-- =========================================== USER CONTENT AND FUNCTIONS =================================== -->

        <div class="row">

            <div class="col-8">
                <!-- User Welcome heading -->
                <h1 class="mt-4">
                    Thank you, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                </h1>
                <br/> 

                <!-- User Content -->
                <p>
                    Your last booking request has been received.  We will contact you to confirm this booking as soon as possible.
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
