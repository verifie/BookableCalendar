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

// Fetch Users
$sql = "SELECT UserID, Username, CONCAT(FirstName, ' ', MiddleNames, ' ', LastName) AS FullName, UserType FROM Users";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require 'x-header.php'; ?>

    <div class="container">

        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '?>
        <div class="row">
            <div class="col-8">

                <h2>View and Manage Users</h2>
                <br/>

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>User Type</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['FullName']) . "</td>";

            // Interpret UserType
            $userTypeDescription = '';
            switch ($row['UserType']) {
                case 'admin':
                    $userTypeDescription = 'Admin';
                    break;
                case 'activeuser':
                    $userTypeDescription = 'Active User';
                    break;
                case 'disableduser':
                    $userTypeDescription = 'User Account Disabled';
                    break;
                case 'registered':
                    $userTypeDescription = 'New User (Not active)';
                    break;
                // Add other cases as needed
                default:
                    $userTypeDescription = 'Unknown';
            }
            echo "<td>" . htmlspecialchars($userTypeDescription) . "</td>";

            echo "<td><a class='btn btn-primary' href='admin_users_edit.php?id=" . $row['UserID'] . "'>Edit</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No users found</td></tr>";
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
