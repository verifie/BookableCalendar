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

// Fetch current working hours settings
$settingsSql = "SELECT SettingName, Setting FROM AdminSettings WHERE SettingName IN ('WorkingHoursStart', 'WorkingHoursEnd')";
$settingsResult = $conn->query($settingsSql);
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['SettingName']] = $row['Setting'];
}

// Process POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $workingHoursStart = $_POST['workingHoursStart'];
    $workingHoursEnd = $_POST['workingHoursEnd'];

    // Update query for each setting
    $updateSql = "INSERT INTO AdminSettings (SettingName, Setting) VALUES (?, ?) ON DUPLICATE KEY UPDATE Setting = VALUES(Setting)";
    $updateStartStmt = $conn->prepare($updateSql);
    $updateStartStmt->bind_param("ss", $settingNameStart, $workingHoursStart);
    $settingNameStart = 'WorkingHoursStart';
    $updateStartStmt->execute();

    $updateEndStmt = $conn->prepare($updateSql);
    $updateEndStmt->bind_param("ss", $settingNameEnd, $workingHoursEnd);
    $settingNameEnd = 'WorkingHoursEnd';
    $updateEndStmt->execute();

    // Reload the page to reflect new settings
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
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

        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '; ?>
        
        <div class="row">

            <div class="col-8">
                <h2>Configure Working Hours</h2>
                <br/>

                <!-- Form to show and change working hours -->
                <form method="post" class="form-inline">

                    <div class="form-group mr-3">
                        <label for="workingHoursStart" class="mr-2">Start Time:</label>
                        <select class="form-control" id="workingHoursStart" name="workingHoursStart">
                            <?php
                            for ($i = 0; $i < 24; $i++) {
                                for ($j = 0; $j < 60; $j += 15) {
                                    $timeValue = str_pad($i, 2, "0", STR_PAD_LEFT) . ':' . str_pad($j, 2, "0", STR_PAD_LEFT);
                                    $selected = (isset($settings['WorkingHoursStart']) && $settings['WorkingHoursStart'] == $timeValue) ? 'selected' : '';
                                    echo "<option value='$timeValue' $selected>$timeValue</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mr-3">
                        <label for="workingHoursEnd" class="mr-2">End Time:</label>
                        <select class="form-control" id="workingHoursEnd" name="workingHoursEnd">
                            <?php
                            for ($i = 0; $i < 24; $i++) {
                                for ($j = 0; $j < 60; $j += 15) {
                                    $timeValue = str_pad($i, 2, "0", STR_PAD_LEFT) . ':' . str_pad($j, 2, "0", STR_PAD_LEFT);
                                    $selected = (isset($settings['WorkingHoursEnd']) && $settings['WorkingHoursEnd'] == $timeValue) ? 'selected' : '';
                                    echo "<option value='$timeValue' $selected>$timeValue</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Working Hours</button>
                
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
