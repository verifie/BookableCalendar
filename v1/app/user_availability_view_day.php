<?php
// 1. Initialise the session
session_start();

// 2. Check user security
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 3. Database Connection
require_once 'x-dbconfig.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 4. Fetch date from URL
$year = isset($_GET['y']) ? $_GET['y'] : date("Y");
$month = isset($_GET['m']) ? $_GET['m'] : date("m");
$day = isset($_GET['d']) ? $_GET['d'] : date("d");

// 5. Fetch booking details for the selected day
$sql = "SELECT b.AssetID, a.AssetName, b.StartTime, b.EndTime 
        FROM Bookings b 
        JOIN Assets a ON b.AssetID = a.AssetID 
        WHERE DATE(b.StartTime) = ?";
$stmt = $conn->prepare($sql);
$date = "$year-$month-$day";
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        'asset' => $row['AssetName'],
        'start' => date('H:i', strtotime($row['StartTime'])),
        'end' => date('H:i', strtotime($row['EndTime']))
    ];
}

// 6. Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day View - Booking Details</title>
    <link rel="stylesheet" href="concept_styles.css">
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
                    Availability
                </h1>
                <br/> 

                <div class="container">
                    <h1>Bookings for <?= htmlspecialchars("$year-$month-$day") ?></h1>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['asset']) ?></td>
                                    <td><?= htmlspecialchars($booking['start']) ?></td>
                                    <td><?= htmlspecialchars($booking['end']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="3">No bookings for this day.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <a href="user_availability_view.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-primary">Back to Calendar</a>
                </div>
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