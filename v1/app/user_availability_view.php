<?php
// 1. Initialise the session
session_start();

// 2. Debug Printer
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
    $output = implode(',', $output);
    
    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }

// 3. User Security
// ----------------

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


// 4. Database Connection
// -----------------------

// Include database configuration
if (!require_once 'x-dbconfig.php') {
    die("Failed to include 'x-dbconfig.php'");
}

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// 5. Setup User Alert Message variable
$message = '';


// 6. Definitions
// --------------


// 7. Fetches
// ----------

// Helper to get todays date in the right format. Used to show past, present and future days in the calendar.
function classifyDay($year, $month, $day) {
    $today = date('Y-m-d');
    $currentDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

    if ($currentDate < $today) return 'past';
    if ($currentDate == $today) return 'today';
    return 'future';
}

// Fetch closed days
$closedDaysSql = "SELECT WorkingClosedDaysName, WorkingClosedDays FROM WorkingDaysClosed";
$closedDaysResult = $conn->query($closedDaysSql);
$closedDays = [];
while ($row = $closedDaysResult->fetch_assoc()) {
    $closedDayFormattedDate = date('Y-m-d', strtotime($row['WorkingClosedDays']));
    $closedDays[$closedDayFormattedDate] = $row['WorkingClosedDaysName'];
}

// Fetch holidays
$holidaysSql = "SELECT WorkingHolidaysName, WorkingHolidays FROM WorkingDaysHoliday";
$holidaysResult = $conn->query($holidaysSql);
$holidays = [];
while ($row = $holidaysResult->fetch_assoc()) {
    $holidayFormattedDate = date('Y-m-d', strtotime($row['WorkingHolidays']));
    $holidays[$holidayFormattedDate] = $row['WorkingHolidaysName'];
}


// Fetch assets from the database
$assetQuery = "SELECT AssetID, AssetName FROM Assets";
$assetResult = $conn->query($assetQuery);
$assets = [];
while($assetRow = $assetResult->fetch_assoc()) {
    $assets[$assetRow['AssetID']] = $assetRow['AssetName'];
}


// 7. Calendar View

// Check if month and year are set in the URL
if (isset($_GET['month']) && isset($_GET['year'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
} else {
    // If the month and year aren't in the URL, Get current month and year and present that.
    // This is the default view.
    // We can improve this by checking if the current month is fully booked and then present the next month with availability.
    $dateComponents = getdate();
    $month = $dateComponents['mon'];
    $year = $dateComponents['year'];
}



// Function to build the calendar
function build_calendar($month, $year, $conn, $selectedAsset = '', $showDetails = false, $closedDays, $holidays) {
    
    // Create an array containing abbreviations of days of week
    $daysOfWeek = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

    // What is the first day of the month in question?
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);

    // How many days does this month contain?
    $numberDays = date('t', $firstDayOfMonth);

    // Retrieve some information about the first day of the month
    $dateComponents = getdate($firstDayOfMonth);

    // What is the name of the month in question?
    $monthName = $dateComponents['month'];

    // What is the index value (0-6) of the first day of the month?
    $dayOfWeek = $dateComponents['wday'];

    // What is the index value (0-6) of the first day of the month?
    // Adjusting for Monday as the first day of the week
    $dayOfWeek = $dateComponents['wday'] - 1;
    if ($dayOfWeek < 0) {
        $dayOfWeek = 6; // If the first day of the month is a Sunday, set $dayOfWeek to 6 (Sunday)
    }


    // Create the table tag opener and day headers
    $calendar = "<table class='table table-bordered calendar weekend-shaded'>";
    $calendar .= "<caption>$monthName $year</caption>";
    $calendar .= "<tr>";

    // Create the calendar headers
    foreach($daysOfWeek as $day) {
        $calendar .= "<th class='header'>$day</th>";
    }

    // Modify SQL query to filter by selected asset if needed
    $sql = "SELECT b.AssetID, a.AssetName, b.StartTime, b.EndTime FROM Bookings b 
            JOIN Assets a ON b.AssetID = a.AssetID 
            WHERE MONTH(b.StartTime) = ? AND YEAR(b.StartTime) = ?";
    if ($selectedAsset) {
        $sql .= " AND b.AssetID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $month, $year, $selectedAsset);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $day = (int)date('j', strtotime($row['StartTime']));
        if ($showDetails) {
            // Show detailed booking info if 'showDetails' is true
            $bookingDetail = $row['AssetName'] . " (" . date('H:i', strtotime($row['StartTime'])) . " - " . date('H:i', strtotime($row['EndTime'])) . ")";
            $bookings[$day][] = $bookingDetail;
        } else {
            // Just mark the day as booked, without adding details
            $bookings[$day] = ["Booked"];
        }
    }

    $currentDay = 1;
    $calendar .= "</tr><tr>";

    // The variable $dayOfWeek is used to ensure that the calendar
    // display consists of exactly 7 columns.
    if ($dayOfWeek > 0) { 
        for($k = 0; $k < $dayOfWeek; $k++){ 
            $calendar .= "<td></td>"; 
        }
    }

    while ($currentDay <= $numberDays) {
        // Format the current day in Y-m-d format for comparison
        $formattedDate = sprintf("%04d-%02d-%02d", $year, $month, $currentDay);
    
        // Add a cross or circle if the day is in the past or today.
        $dayClassification = classifyDay($year, $month, $currentDay);
        $additionalClass = '';
        $additionalStyle = '';
    
        if ($dayClassification == 'past') {
            $additionalClass = 'past-day';
            $additionalStyle = 'border: 1px solid black;';
            // Removed line-through style, as it's not needed with the X overlay
        } elseif ($dayClassification == 'today') {
            $additionalClass = 'today-day';
            $additionalStyle = 'border: 2px (253, 80, 0, 0.616); background-color: rgb(76, 191, 206);';
        }

        // Initialize booking info and class for the day
        $bookingInfo = '';
        $availableClass = '';
    
        // Link to the detailed day view
        $dayLink = "<a href='user_availability_view_day.php?y=$year&m=$month&d=$currentDay'>$currentDay</a>";
    
        // Check for closed days and holidays
        $isClosedDay = isset($closedDays[$formattedDate]);
        $isHoliday = isset($holidays[$formattedDate]);
    
        if ($isClosedDay || $isHoliday) {
            $closedOrHolidayName = $isClosedDay ? "Closed - " . $closedDays[$formattedDate] : "National Holiday - " . $holidays[$formattedDate];
            $bookingInfo = $showDetails ? $closedOrHolidayName : ($isClosedDay ? "Closed" : "National Holiday");
            $availableClass = "closed-day"; // Class for closed days or holidays
        } else {
            // Check if the day is booked
            if (isset($bookings[$currentDay])) {
                $bookingInfo = implode(', ', $bookings[$currentDay]);
                $availableClass = "booked-day"; // Class for booked days
            } else {
                $bookingInfo = "Available";
                $availableClass = "available-day"; // Class for available days
            }
    
            // Shade weekends
            if ($dayOfWeek == 5 || $dayOfWeek == 6) { // 5 = Saturday, 6 = Sunday
                $availableClass .= " weekend-shadeds";
            }
        }
    
        // Add hover effect class
        $hoverClass = "day-hover";
    
        // Add the table cell to the calendar
        $calendar .= "<td class='$availableClass $hoverClass $additionalClass' style='$additionalStyle'><h4>$dayLink</h4> <p>$bookingInfo</p></td>";
        
        // Increment counters
        $currentDay++;
        $dayOfWeek++;
    
        // Seventh column (Sunday) reached. Start a new row.
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }
    }
    
    
    // Complete the row of the last week in month, if necessary
    if ($dayOfWeek != 0) {
        $remainingDays = 7 - $dayOfWeek;
        for($l = 0; $l < $remainingDays; $l++){ 
            $calendar .= "<td></td>"; 
        }
    }
    
    $calendar .= "</tr>";
    $calendar .= "</table>";
    
    return $calendar;
}



// Function to generate navigation URL
function nav_url($month, $year, $direction) {
    if ($direction == 'prev') {
        $month--;
        if ($month < 1) {
            $month = 12;
            $year--;
        }
    } else {
        $month++;
        if ($month > 12) {
            $month = 1;
            $year++;
        }
    }
    return "user_availability_view.php?month=" . $month . "&year=" . $year;
}


// Check if 'show_details' is set in GET request and convert it to boolean
$showDetails = isset($_GET['show_details']) && $_GET['show_details'] == 'yes';


// 9. Ensure the database connection is closed (For good practice).

?>

<!-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
<!-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
<!-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Request Received</title>
    
    <!-- Concept addtional styles -->
    <link rel="stylesheet" href="concept_styles.css" />
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
                    Availability
                </h1>
                <br/> 

                <!-- Availability Calendar -->
                <div class="container">

                    <!-- Calendar Navigation -->
                    <div class="row mb-4">

                        <!-- Asset Selection Form -->
                            

                        <div class="col">
                            <a href="<?= nav_url($month, $year, 'prev') ?>" class="btn btn-primary">&lt; Prev</a>
                        </div>

                        <div class="col text-center">
                            <strong><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></strong>
                        </div>

                        <div class="col text-right">
                            <a href="<?= nav_url($month, $year, 'next') ?>" class="btn btn-primary">Next &gt;</a>
                        </div>

                    </div>

                        <?php
                            $selectedAsset = isset($_GET['asset']) ? $_GET['asset'] : '';
                            echo build_calendar($month, $year, $conn, $selectedAsset, $showDetails, $closedDays, $holidays);
                        ?>
                        

                        <br/><hr>

                        <div class="container">
                            <div class="row mb-4">
                                <!-- Asset Selection Form -->
                                <div class="col-md-4">
                                    <form action="user_availability_view.php" method="get" class="form-inline">
                                        <div class="form-group mb-2">
                                            <label for="assetSelect" class="sr-only">Choose what you wish to book:</label>
                                            <select name="asset" id="assetSelect" class="form-control mr-2">
                                                <option value="">View All</option>
                                                <?php foreach($assets as $assetId => $assetName): ?>
                                                    <option value="<?= $assetId ?>" <?= (isset($_GET['asset']) && $_GET['asset'] == $assetId) ? 'selected' : '' ?>><?= htmlspecialchars($assetName) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <input type="hidden" name="month" value="<?= $month ?>">
                                        <input type="hidden" name="year" value="<?= $year ?>">
                                        <input type="hidden" name="show_details" value="<?= $showDetails ? 'yes' : 'no' ?>">
                                        <button type="submit" class="btn btn-primary mb-2">Show Availability</button>&nbsp;&nbsp;&nbsp;&nbsp;
                                    </form>
                                </div>

                                <!-- Detailed View Selection Form -->
                                <div class="col-md-4 ml-md-3">
                                    <form action="user_availability_view.php" method="get" class="form-inline">
                                        <div class="form-group mb-2">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <select name="show_details" id="showDetailsSelect" class="form-control mr-2">
                                                <option value="no" <?= !$showDetails ? 'selected' : '' ?>>Simple View</option>
                                                <option value="yes" <?= $showDetails ? 'selected' : '' ?>>Detailed View</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="asset" value="<?= isset($_GET['asset']) ? $_GET['asset'] : '' ?>">
                                        <input type="hidden" name="month" value="<?= $month ?>">
                                        <input type="hidden" name="year" value="<?= $year ?>">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <button type="submit" class="btn btn-primary mb-2">Update View</button>
                                    </form>
                                </div>
                            </div>
                            <hr>
                        </div>

                        
                        <br/>

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
<?php $conn->close(); ?>