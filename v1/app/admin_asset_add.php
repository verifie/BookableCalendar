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


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO Assets (AssetName, Description, PaymentRequired, MinBookingValue, MinBookingIntervals, Status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdis", $assetName, $description, $paymentRequired, $minBookingValue, $minBookingIntervals, $status);

    // Set parameters and execute
    $assetName = $_POST['assetName'];
    $description = $_POST['description'];
    $paymentRequired = $_POST['paymentRequired'];
    $minBookingValue = $_POST['minBookingValue'];
    $minBookingIntervals = $_POST['minBookingIntervals'];
    $status = $_POST['status'];

    if ($stmt->execute()) {
        $message = "Asset added successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Asset - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>


<?php require 'x-header.php'; ?>

<div class="container">
<?php
    // Check if user is an admin and display admin functions
    if (isset($_SESSION["UserType"]) && $_SESSION["UserType"] === "admin") {
        echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/>
        <div class="row">
            <div class="col-8">

                <h2>Add New Asset</h2>

                <p><?php echo $message; ?></p>

                <form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">

                    <div class="form-group">
                        <label>Asset Name</label>
                        <input type="text" name="assetName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Payment Required to book?</label>
                        <select name="paymentRequired" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>1 minute booking cost (Prices are calculated as 1 minute multiples)</label>
                        <input type="number" step="0.01" name="minBookingValue" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Minimum Booking Intervals (Minutes)</label>
                        <select name="minBookingIntervals" class="form-control">
                            <option value="480">8 Hours (One Day)</option>
                            <option value="240">4 Hours (Half Day)</option>
                            <option value="60">1 Hour</option>
                            <option value="30">30 Minutes</option>
                            <option value="15">15 Minutes</option>
                            <option value="10">10 Minutes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="3">Not Bookable, No longer available. Any outstanding cancelled.</option>
                            <option value="2">Not Bookable, but existing not cancelled.</option>
                            <option value="1" selected>Add-On to other Bookable Asset ONLY</option>
                            <option value="0" selected>Bookable</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Asset</button>

                </form>
            </div>'
    . '
        <!-- Right column admin Controls -->
        <div class="col-4">
            '; require_once 'x-admin-controls.php';
            echo '
        </div>'; 

// If they fail the IF, they are not an admin and are not allowed to access this page. So notify them.
// We should also log an access attempt from a user here.
} else {

    echo '<h1 class="mt-4">WARNING: ' . htmlspecialchars($_SESSION["username"]) . '!</h1>';
    echo '<p>You may not access this page.</p>';
}
?>

</div>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
