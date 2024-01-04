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

// Definitions
// Initialize variables for edit mode
$editMode = false;
$editableHolidayName = '';
$editableHolidayDate = '';



// Handle POST request for adding/updating/deleting holidays
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            // Logic for adding a new holiday
            $holidayName = $_POST['holidayName'] ?? '';
            $holidayDate = $_POST['holidayDate'] ?? '';
            if ($holidayName && $holidayDate) {
                $insertSql = "INSERT INTO WorkingDaysHoliday (WorkingHolidaysName, WorkingHolidays) VALUES (?, ?)";
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param("ss", $holidayName, $holidayDate);
                $stmt->execute();
            }
            break;

        case 'edit':
            // Set the form to edit mode
            $editableHolidayName = $_POST['holidayName'];
            $editableHolidayDate = $_POST['holidayDate'];  // Assuming you send this
            $editMode = true;
            break;

        case 'update':
            // Logic for updating an existing holiday
            $originalHolidayName = $_POST['originalHolidayName'];
            $newHolidayName = $_POST['holidayName'];
            $newHolidayDate = $_POST['holidayDate'];
            // Perform update SQL query here
            break;

        case 'copy':
            // Retrieve the name of the holiday to copy
            $holidayNameToCopy = $_POST['holidayName'] ?? '';
            
            if ($holidayNameToCopy) {
                // Fetch the holiday to copy
                $fetchSql = "SELECT WorkingHolidaysName, WorkingHolidays FROM WorkingDaysHoliday WHERE WorkingHolidaysName = ?";
                $fetchStmt = $conn->prepare($fetchSql);
                $fetchStmt->bind_param("s", $holidayNameToCopy);
                $fetchStmt->execute();
                $result = $fetchStmt->get_result();
                $holidayToCopy = $result->fetch_assoc();
        
                // Prepare the new holiday name (append a unique identifier like a timestamp)
                $newHolidayName = $holidayToCopy['WorkingHolidaysName'] . ' - Copy ' . time();
        
                // Insert the copied holiday
                $insertSql = "INSERT INTO WorkingDaysHoliday (WorkingHolidaysName, WorkingHolidays) VALUES (?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("ss", $newHolidayName, $holidayToCopy['WorkingHolidays']);
                $insertStmt->execute();
            }
            break;

        case 'delete':
            // Logic for deleting a holiday
            $holidayNameToDelete = $_POST['holidayName'] ?? '';
            if ($holidayNameToDelete) {
                $deleteSql = "DELETE FROM WorkingDaysHoliday WHERE WorkingHolidaysName = ?";
                $stmt = $conn->prepare($deleteSql);
                $stmt->bind_param("s", $holidayNameToDelete);
                $stmt->execute();
            }
            break;

        case 'import':
            // Logic for importing official UK holidays
            // Placeholder for future implementation
            break;
    }
}

// Fetch current working holidays
$holidaySql = "SELECT WorkingHolidaysName, WorkingHolidays FROM WorkingDaysHoliday";
$holidayResult = $conn->query($holidaySql);
$holidays = [];
while ($row = $holidayResult->fetch_assoc()) {
    $holidays[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Working Hours - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require 'x-header.php'; ?>

    <div class="container">
        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '?>
        <div class="row">
            <div class="col-8">
                <h2>Configure Working Holidays</h2>
                <br/>

                <!-- Form to add new holiday -->
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="holidayName">Holiday Name:</label>
                                <input type="text" class="form-control" id="holidayName" name="holidayName">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="holidayDate">Holiday Date:</label>
                                <input type="date" class="form-control" id="holidayDate" name="holidayDate">
                            </div>
                        </div>
                        <div class="col d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Add Holiday</button><br/><br/>
                        </div>
                    </div>
                </form>


                <br/>

                <!-- List of existing holidays -->
                <?php foreach ($holidays as $holiday): ?>
                    <div class="holiday-entry">

                        <p>
                            <!-- Edit button 
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="holidayName" value="<?= htmlspecialchars($holiday['WorkingHolidaysName']) ?>">
                                <button type="submit" class="btn btn-primary">Edit</button>
                            </form>

                            <!-- Copy button
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="copy">
                                <input type="hidden" name="holidayName" value="<?= htmlspecialchars($holiday['WorkingHolidaysName']) ?>">
                                <button type="submit" class="btn btn-secondary">Copy</button>
                            </form>-->

                            <!-- Delete button -->
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="holidayName" value="<?= htmlspecialchars($holiday['WorkingHolidaysName']) ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                            &nbsp;&nbsp;&nbsp;
                            <?= htmlspecialchars($holiday['WorkingHolidaysName']) ?>: <?= htmlspecialchars($holiday['WorkingHolidays']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <br/>

                <!-- Placeholder for importing official UK holidays -->
                <form method="post">
                    <button type="submit" class="btn btn-secondary">Import Official UK Holidays</button>
                </form>

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
