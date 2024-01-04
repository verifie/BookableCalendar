<?php
// Start the session
session_start();

// Check if the user is logged in and is an user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["UserType"] !== 'activeuser') {
    header("location: login.php");
    exit;
}

// If login is successful, store the user ID in a variable that will be used alongside other booking credentials.
// $_SESSION['userid'] = $user_id; // Where $user_id is the ID of the user from the database

// Retrieve user ID from session
$userId = $_SESSION['id'];

// Include database configuration
if (!require_once 'x-dbconfig.php') {
    die("Failed to include 'x-dbconfig.php'");
}

// Setup User Alert Message variable
$message = '';

// Debug to console
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
    $output = implode(',', $output);
    
    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }


// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Fetch assets and add-ons data from the database


// Initialize variables
$assets = [];
$addOns = [];
$bookingIntervalsCsv = '';

// Define possible booking intervals. This is done in minutes, with human-readable options.
$bookingIntervals = [
    967680 => "4 Weeks",
    20160 => "2 Weeks",
    10080 => "1 Week",
    7200 => "5 Days",
    4320 => "3 Days",
    2880 => "2 Days",
    1440 => "24 Hours - Full Day",
    720 => "12 Hours - Long Day",
    480 => "8 Hours - One Day",
    240 => "4 Hours - Half Day",
    180 => "3 Hours",
    120 => "2 Hours",
    60 => "1 Hour",
    30 => "30 Minutes",
    15 => "15 Minutes",
    10 => "10 Minutes",
    5 => "5 Minutes"
];


// Get asset details.
$assets = [];
$assetQuery = "SELECT AssetID, AssetName, BookingIntervals FROM Assets WHERE Status = 0"; // Assuming 0 is bookable
$assetResult = $conn->query($assetQuery);
if ($assetResult && $assetResult->num_rows > 0) {
    while ($row = $assetResult->fetch_assoc()) {
        $assets[$row['AssetID']] = [
            'name' => $row['AssetName'],
            'intervals' => explode(',', $row['BookingIntervals'])
        ];
    }
}



// Get addons
$addOnQuery = "SELECT a.AssetID, a.AssetName, aa.AssetID as LinkedAssetID FROM Assets a JOIN AssetAddOns aa ON a.AssetID = aa.AddOnID";
$addOnResult = $conn->query($addOnQuery);
if ($addOnResult && $addOnResult->num_rows > 0) {
    while ($row = $addOnResult->fetch_assoc()) {
        $addOns[] = $row;
    }
}



// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $selectedAsset = $_POST['asset'];
    $selectedAddOns = $_POST['addOns'] ?? []; // Using null coalescing operator
    $bookingDate = $_POST['bookingDate'];
    $bookingTime = $_POST['bookingTime'];

    // Combine date and time
    $startTime = date('Y-m-d H:i:s', strtotime("$bookingDate $bookingTime"));

    // Duration in minutes
    $bookingDuration = $_POST['duration']; 

    // Calculate EndTime
    $startTime = date('Y-m-d H:i:s', strtotime("$bookingDate $bookingTime"));
    $endTime = date('Y-m-d H:i:s', strtotime($startTime . " + $bookingDuration minutes"));

    // Calculate PaymentValue based on your logic
    // ... (Code for calculating PaymentValue)

    $status = 1; // Set the booking as "Provisional" status.  This means the booking will show as "Pencil" i.e. not approved or confirmed. Multiple people can request the same data, knowing that it has been requested already. That leaves the site admin the option to choose the winning user.

    
    // Insert booking for the primary asset
    $insertSql = "INSERT INTO Bookings (UserID, AssetID, StartTime, EndTime, Duration, PaymentValue, Status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("iissdis", $userId, $selectedAsset, $startTime, $endTime, $bookingDuration, $paymentValue, $status);

    if ($stmt->execute()) {
        $primaryBookingId = $conn->insert_id; // Get the ID of the primary booking
        $message = "Primary asset booking request submitted successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }

    // Handle AddOns if selected
    if (!empty($selectedAddOns)) {
        foreach ($addOns as $addOn) {
            if (in_array($addOn['AssetID'], $selectedAddOns)) {
                $insertAddOnSql = "INSERT INTO Bookings (UserID, AssetID, PrimaryBookingId, StartTime, EndTime, Duration, PaymentValue, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtAddOn = $conn->prepare($insertAddOnSql);
                $stmtAddOn->bind_param("iiissdis", $userId, $addOn['AssetID'], $primaryBookingId, $startTime, $endTime, $bookingDuration, $paymentValue, $status);
                
                if ($stmtAddOn->execute()) {
                    $message .= " Add-on (ID: {$addOn['AssetID']}) booked successfully.";
                } else {
                    $message .= " Error booking add-on (ID: {$addOn['AssetID']}): " . $stmtAddOn->error;
                }
        // Close the statement
        $stmt->close();

        // Redirect to the booking received page
        header("Location: user_booking_request_received.php");
        exit();
    } else {
        // Handle errors
        $message = "Error: " . $stmt->error;
    }

    // Close the statement if it's not closed earlier
    $stmt->close();
}
}
}

// Close the database: Belt and braces
$conn->close();
?>


<!-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
<!-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
<!-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concept Booking App: Create a Booking</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  
</head>
<body>

<?php require 'x-header.php'; ?>

<!-- =========================================== Page Title =================================== -->

<div class="container">
    <h1 class="mt-4">Create Booking Specification</h1>

    
    <!-- =========================================== ADMINISTRATORS MAY NOT USE THIS PAGE =================================== -->
    <?php if (isset($_SESSION["UserType"]) && $_SESSION["UserType"] === "admin"): ?>
        <p>This page is only to be used by a standard user account, not an admin account.</p>
    <?php else: ?>



        <!-- =========================================== USER CONTENT AND FUNCTIONS =================================== -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> <!-- Update action path accordingly -->
            <div class="form-group">
                
            <hr>
            <h4><small><strong>What would you like to book?</strong></small></h4>

            <!-- Primary asset -->
            <select name="asset" id="assetSelect" class="form-control" onchange="onAssetChange()">
                <?php foreach($assets as $assetID => $asset): ?>
                    <option value="<?= $assetID; ?>"><?= htmlspecialchars($asset['name']); ?></option>
                <?php endforeach; ?>
            </select>



            <!-- Add-on checkbox selector -->
            <h4><small><strong>You may also add these to your booking:</strong></small></h4>
            <?php foreach($addOns as $addOn): ?>
                <div class="form-check add-on" data-asset-id="<?= $addOn['LinkedAssetID']; ?>" style="display: none;">
                    <input class="form-check-input" type="checkbox" name="addOns[]" id="addOn<?= $addOn['AssetID']; ?>" value="<?= $addOn['AssetID']; ?>">
                    <label class="form-check-label" for="addOn<?= $addOn['AssetID']; ?>">
                        <?= htmlspecialchars($addOn['AssetName']); ?>
                    </label>
                </div>
            <?php endforeach; ?>


            <!--Booking Date -->
            <hr>
            <div class="form-group">
                <h4><small><strong>Booking Date</strong></small></h4>
                <input type="date" name="bookingDate" id="bookingDate" class="form-control">
            </div>

                                                                                                                                                                                                                           
            <!-- Booking Start Time -->
            <hr>
            <div class="form-group">
                <h4><small><strong>Start Time</strong></small></h4>
                <input type="time" name="bookingTime" id="bookingTime" class="form-control">
            </div>


            <!-- Booking Duration -->
            <hr>
            <div class="form-group">
                <h4><small><strong>Duration</strong></small></h4>
                <select name="duration" id="duration" class="form-control">
                    <!-- Options will be dynamically populated here -->
                </select>
            </div>

            
            <!-- Booking End Date and Time -->
            <hr>
            <div class="form-group">
                <h4><small><strong>End Date and Time</strong></small></h4>
                <p id="endDateTime"><!-- Calculated end date and time will appear here --></p>
            </div>
            

            <!-- Booking Cost -->
            <hr>
            <div class="form-group">
                <h4><small><strong>Ratecard Cost</strong></small></h4>
                <p id="bookingRatecardCost"><!-- Calculated cost --></p>
            </div>


            <!-- Submit booking -->
            <hr>
            <button type="submit" class="btn btn-primary">Submit Booking Request</button>
        </form>



        
        <!-- =========================================== END USER CONTENT AND FUNCTIONS =================================== -->

    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

  
<!-- JavaScript to handle dynamic add-on display -->
<script>
    var assets = <?php echo json_encode($assets); ?>;
    var bookingIntervals = <?php echo json_encode($bookingIntervals); ?>;



    function onAssetChange() {
        var selectedAssetId = document.getElementById('assetSelect').value;
        updateAddOns(selectedAssetId);
        updateDurationOptions(selectedAssetId);
    }


    window.onload = function() {
        onAssetChange(); // Call on page load with the first asset's ID
    };



    function updateAddOns(selectedAssetId) {
        document.querySelectorAll('.add-on').forEach(function(addOnDiv) {
            if (addOnDiv.getAttribute('data-asset-id') === selectedAssetId) {
                addOnDiv.style.display = 'block'; // Show the add-on
            } else {
                addOnDiv.style.display = 'none'; // Hide the add-on
            }
        });
    }

    // Call this function when the asset selection changes
    function onAssetChange() {
        var selectedAssetId = document.getElementById('assetSelect').value;
        updateAddOns(selectedAssetId);
        updateDurationOptions(selectedAssetId);
    }

    window.onload = function() {
        onAssetChange(); // Initialize with the first asset's ID
    };

    // Update end date and time based on the duration.
        function updateEndDateAndTime() {
            var bookingDate = document.getElementById('bookingDate').value;
            var bookingTime = document.getElementById('bookingTime').value;
            var duration = document.getElementById('duration').value;

            if (bookingDate && bookingTime && duration) {
                var startDateTime = new Date(bookingDate + 'T' + bookingTime);
                startDateTime.setMinutes(startDateTime.getMinutes() + parseInt(duration));

                // Formatting the date and time
                var endDate = startDateTime.getDate().toString().padStart(2, '0') + '-' +
                            (startDateTime.getMonth() + 1).toString().padStart(2, '0') + '-' +
                            startDateTime.getFullYear();
                var endTime = startDateTime.getHours().toString().padStart(2, '0') + ':' +
                            startDateTime.getMinutes().toString().padStart(2, '0');


                document.getElementById('endDateTime').textContent = `${endDate} at ${endTime}`;
            }
        }

        // Update add-ons and duration options when asset changes.
        function onAssetChange() {
            var selectedAssetId = document.getElementById('assetSelect').value;
            updateAddOns(selectedAssetId);
            updateDurationOptions(selectedAssetId);
            updateEndDateAndTime(); // Also update the end date and time.
        }

        // Update add-ons display.
        function updateAddOns(selectedAssetId) {
            document.querySelectorAll('.add-on').forEach(function(addOnDiv) {
                addOnDiv.style.display = addOnDiv.getAttribute('data-asset-id') === selectedAssetId ? 'block' : 'none';
            });
        }

        function updateDurationOptions(selectedAssetId) {
            var durationSelect = document.getElementById('duration');
            durationSelect.innerHTML = '';

            if (assets[selectedAssetId]) {
                var reversedIntervals = assets[selectedAssetId].intervals.slice().reverse(); // Clone and reverse the intervals array
                reversedIntervals.forEach(function(interval) {
                    if (bookingIntervals[interval]) {
                        var option = document.createElement('option');
                        option.value = interval;
                        option.textContent = bookingIntervals[interval];
                        durationSelect.appendChild(option);
                    }
                });
            }
        }

        // Event listeners.
        document.addEventListener('DOMContentLoaded', onAssetChange);
        document.getElementById('assetSelect').addEventListener('change', onAssetChange);
        document.getElementById('bookingDate').addEventListener('change', updateEndDateAndTime);
        document.getElementById('bookingTime').addEventListener('change', updateEndDateAndTime);
        document.getElementById('duration').addEventListener('change', updateEndDateAndTime);

    </script>


</body>
</html>
