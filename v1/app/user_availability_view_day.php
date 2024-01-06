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

// Fetch working hours settings
$settingsSql = "SELECT SettingName, Setting FROM AdminSettings WHERE SettingName IN ('WorkingHoursStart', 'WorkingHoursEnd')";
$settingsResult = $conn->query($settingsSql);
$workingHours = [];
while ($row = $settingsResult->fetch_assoc()) {
    $workingHours[$row['SettingName']] = $row['Setting'];
}

// Default display times are set to working hours plus an hour buffer
$displayStart = DateTime::createFromFormat('H:i', $workingHours['WorkingHoursStart'])->modify('-1 hour');
$displayEnd = DateTime::createFromFormat('H:i', $workingHours['WorkingHoursEnd'])->modify('+1 hour');

// Adjust display times based on bookings
foreach ($bookings as $booking) {
    $bookingStart = DateTime::createFromFormat('H:i', $booking['start']);
    $bookingEnd = DateTime::createFromFormat('H:i', $booking['end']);

    if ($bookingStart < $displayStart) {
        $displayStart = (clone $bookingStart)->modify('-1 hour');
    }
    if ($bookingEnd > $displayEnd) {
        $displayEnd = (clone $bookingEnd)->modify('+1 hour');
    }
}

// Fetch assets for columns
$assetSql = "SELECT AssetID, AssetName FROM Assets";
$assetResult = $conn->query($assetSql);
$assets = [];
while ($row = $assetResult->fetch_assoc()) {
    $assets[$row['AssetID']] = $row['AssetName'];
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

// Initialize schedule array for the entire day
$schedule = [];
for ($hour = 0; $hour < 24; $hour++) {
    for ($minute = 0; $minute < 60; $minute += 15) {
        $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minute, 2, '0', STR_PAD_LEFT);
        foreach ($assets as $assetId => $assetName) {
            $schedule[$time][$assetId] = ''; // Initialize with empty string
        }
    }
}



// Filter the schedule to include only times within the display range
$filteredSchedule = [];
foreach ($schedule as $time => $row) {
    $currentTime = DateTime::createFromFormat('H:i', $time);
    if ($currentTime >= $displayStart && $currentTime <= $displayEnd) {
        $filteredSchedule[$time] = $row;
    }
}


// Initialize schedule array within display times
$schedule = [];
$current = clone $displayStart;
while ($current <= $displayEnd) {
    $timeSlot = $current->format('H:i');
    foreach ($assets as $assetId => $assetName) {
        $schedule[$timeSlot][$assetId] = ''; // Initialize with empty string
    }
    $current->modify('+15 minutes'); // Increment by 15 minutes
}

// 6. Fetch closed days and holidays
$closedDaysSql = "SELECT WorkingClosedDaysName FROM WorkingDaysClosed WHERE WorkingClosedDays = ?";
$holidaysSql = "SELECT WorkingHolidaysName FROM WorkingDaysHoliday WHERE WorkingHolidays = ?";
$specialDayInfo = '';

$stmtClosed = $conn->prepare($closedDaysSql);
$stmtClosed->bind_param("s", $date);
$stmtClosed->execute();
$resultClosed = $stmtClosed->get_result();
if ($rowClosed = $resultClosed->fetch_assoc()) {
    $specialDayInfo = "Closed" ;// Hiding closure reason for now.
    //. $rowClosed['WorkingClosedDaysName'];
}

$stmtHoliday = $conn->prepare($holidaysSql);
$stmtHoliday->bind_param("s", $date);
$stmtHoliday->execute();
$resultHoliday = $stmtHoliday->get_result();
if ($rowHoliday = $resultHoliday->fetch_assoc()) {
    $specialDayInfo = "National Holiday - " . $rowHoliday['WorkingHolidaysName'];
}

// Add code for a day schedule.



// Populate the schedule with booking information
foreach ($bookings as $booking) {
    $startTime = DateTime::createFromFormat('H:i', $booking['start']);
    $endTime = DateTime::createFromFormat('H:i', $booking['end']);
    $assetId = array_search($booking['asset'], $assets);

    if ($assetId === false) {
        continue; // Skip if asset ID is not found
    }

    // Adjust startTime to the nearest previous 15-minute interval
    $startMinutes = (int)$startTime->format('i');
    if ($startMinutes % 15 !== 0) {
        $adjustment = $startMinutes % 15;
        $startTime->modify("-$adjustment minutes");
    }

    // Adjust endTime to the nearest next 15-minute interval
    $endMinutes = (int)$endTime->format('i');
    if ($endMinutes % 15 !== 0) {
        $adjustment = 15 - ($endMinutes % 15);
        $endTime->modify("+$adjustment minutes");
    }

    // Loop through each 15-minute interval and mark as booked
    $current = clone $startTime;
    while ($current < $endTime) {
        $timeSlot = $current->format('H:i');
        if (isset($schedule[$timeSlot][$assetId])) {
            $schedule[$timeSlot][$assetId] = 'booked';
        }
        $current->modify('+15 minutes');
    }
}


// Make the date pretty
// Assuming $year, $month, and $day are already set to the respective values
$timestamp = mktime(0, 0, 0, $month, $day, $year);
$formattedDate = date("l jS F Y", $timestamp); // Formats date as 'Thursday 4th January 2024'


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
                    <h2>Bookings for <?= htmlspecialchars($formattedDate) ?></h2>

                    <?php if ($specialDayInfo): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($specialDayInfo) ?>
                        </div>
                    <?php endif; ?>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
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



                <!-- Day schedule here -->
                <br/><hr><br/>

                <div class="schedule-matrix">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <?php foreach ($assets as $assetName): ?>
                                    <th><?= htmlspecialchars($assetName) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedule as $time => $row): ?>
                                <tr>
                                    <td><?= $time ?></td>
                                    <?php foreach ($assets as $assetId => $assetName): ?>
                                        <td class="<?= $row[$assetId] === 'booked' ? 'booked-slot' : '' ?>"></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <br/><br/>
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