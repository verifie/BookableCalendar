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

// Handle POST request for adding/updating/deleting closed days
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            // Logic for adding a new closed day
            $closedDayName = $_POST['closedDayName'] ?? '';
            $closedDayDate = $_POST['closedDayDate'] ?? '';
            if ($closedDayName && $closedDayDate) {
                $insertSql = "INSERT INTO WorkingDaysClosed (WorkingClosedDaysName, WorkingClosedDays) VALUES (?, ?)";
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param("ss", $closedDayName, $closedDayDate);
                $stmt->execute();
            }
            break;

        // Add cases for 'edit', 'delete', etc. as needed
    }
}

// Fetch current closed days
$closedDaysSql = "SELECT WorkingClosedDaysName, WorkingClosedDays FROM WorkingDaysClosed";
$closedDaysResult = $conn->query($closedDaysSql);
$closedDays = [];
while ($row = $closedDaysResult->fetch_assoc()) {
    $closedDays[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Closed Days - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require 'x-header.php'; ?>

    <div class="container">
        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '?>
        <div class="row">
            <div class="col-8">
                <h2>Configure Closed Days</h2>
                <br/>

                <!-- Form to add new closed day -->
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="closedDayName">Closed Day Name:</label>
                                <input type="text" class="form-control" id="closedDayName" name="closedDayName">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="closedDayDate">Closed Day Date:</label>
                                <input type="date" class="form-control" id="closedDayDate" name="closedDayDate">
                            </div>
                        </div>
                        <div class="col d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Add Closed Day</button><br/><br/>
                        </div>
                    </div>
                </form>

                <!-- List of existing closed days -->
                <?php foreach ($closedDays as $closedDay): ?>
                    <div class="holiday-entry">
                        <p>
                            <!-- Edit button 
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="closedDayName" value="<?= htmlspecialchars($closedDay['WorkingClosedDaysName']) ?>">
                                <button type="submit" class="btn btn-primary">Edit</button>
                            </form>

                            <!-- Copy button 
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="copy">
                                <input type="hidden" name="closedDayName" value="<?= htmlspecialchars($closedDay['WorkingClosedDaysName']) ?>">
                                <button type="submit" class="btn btn-secondary">Copy</button>
                            </form>-->

                            <!-- Delete button -->
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="closedDayName" value="<?= htmlspecialchars($closedDay['WorkingClosedDaysName']) ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                            &nbsp;&nbsp;&nbsp;
                            <?= htmlspecialchars($closedDay['WorkingClosedDaysName']) ?>: <?= htmlspecialchars($closedDay['WorkingClosedDays']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <br/>

            </div>

            <!-- Right column admin Controls -->
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
