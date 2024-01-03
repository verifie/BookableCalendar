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

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Assets
$sql = "SELECT * FROM Assets";
$result = $conn->query($sql);

// Function to get qualitative booking interval
function getBookingInterval($interval) {
    switch($interval) {
        case 480: return '8 Hours (One Day)';
        case 240: return '4 Hours (Half Day)';
        case 60: return '1 Hour';
        case 30: return '30 Minutes';
        case 15: return '15 Minutes';
        case 10: return '10 Minutes';
        default: return 'Undefined';
    }
}

// Function to get asset status
function getAssetStatus($status) {
    switch($status) {
        case 2: return 'Not Bookable, No longer available';
        case 1: return 'Not Bookable, but existing not cancelled';
        case 0: return 'Bookable';
        default: return 'Undefined';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assets - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require 'x-header.php'; ?>

    <div class="container">

        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '?>
        <div class="row">
            <div class="col-8">

                <h2>View and Manage Assets</h2>
                <br/>

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Payment Required</th>
                            <th>Min Booking Value</th>
                            <th>Min Booking Intervals</th>
                            <th>Status</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['AssetName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                                echo "<td>" . ($row['PaymentRequired'] ? 'Yes' : 'No') . "</td>";
                                echo "<td>" . htmlspecialchars($row['MinBookingValue']) . "</td>";
                                echo "<td>" . getBookingInterval($row['MinBookingIntervals']) . "</td>";
                                echo "<td>" . getAssetStatus($row['Status']) . "</td>";
                                echo "<td><a class='btn btn-primary' href='admin_asset_edit.php?id=" . $row['AssetID'] . "'>Edit</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No assets found</td></tr>";
                        }
                        ?>
                    </tbody>

                </table>
            </div>

            <!-- Standard segment on all admin panels: Right column admin Controls -->
            <div class="col-4">
                <?php require_once 'x-admin-controls.php'; ?>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
