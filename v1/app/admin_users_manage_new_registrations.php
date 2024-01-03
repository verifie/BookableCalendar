<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["UserType"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Include database configuration
require_once 'x-dbconfig.php';

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a user id is passed for UserType update
if (isset($_GET['activateUserId'])) {
    $activateUserId = $_GET['activateUserId'];
    $updateStmt = $conn->prepare("UPDATE Users SET UserType = 'activeuser' WHERE UserID = ?");
    $updateStmt->bind_param("i", $activateUserId);
    $updateStmt->execute();
    $updateStmt->close();

    // Redirect to the same page to refresh the list
    header("location: admin_users_manage_new_registrations.php");
    exit;
}

// Fetch registered users with all fields
$sql = "SELECT UserID, Username, FirstName, MiddleNames, LastName, EmailAddress, PhoneNumberMobile, PhoneNumberLandline, TradingName, FullCompanyName, LocationCompanyRegistered, CompanyRegistrationNumber, VATNumber, CompanyAddressBuilding, CompanyAddressStreet, CompanyAddressLocality, CompanyAddressTown, CompanyAddressCounty, CompanyAddressPostCode, CompanyAddressCountry, CompanyWebsiteAddress FROM Users WHERE UserType = 'registered'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php require 'x-header.php'; ?>

<div class="container">
<?php
    // Check if user is an admin and display admin functions
    if (isset($_SESSION["UserType"]) && $_SESSION["UserType"] === "admin") {
        echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/>';
        echo '<div class="row">';
        echo '<div class="col-8">';
        echo '<h2>Manage Registered Users</h2>';
        echo '<ul class="list-group">';
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<li class="list-group-item">';
                echo 'Username: ' . htmlspecialchars($row["Username"]) . '<br/>';
                echo '<a href="admin_users_manage_new_registrations.php?activateUserId=' . $row["UserID"] . '" class="btn btn-primary btn-sm">Activate User</a><br/>';
        
                // Display additional details if not null
                $fields = [
                    'FirstName' => 'First Name',
                    'MiddleNames' => 'Middle Names',
                    'LastName' => 'Last Name',
                    'TradingName' => 'Trading Name',
                    'FullCompanyName' => 'Full Company Name',
                    'LocationCompanyRegistered' => 'Location Company Registered',
                    'CompanyRegistrationNumber' => 'Company Registration Number',
                    'VATNumber' => 'VAT Number',
                    'CompanyAddressBuilding' => 'Company Address Building',
                    'CompanyAddressStreet' => 'Company Address Street',
                    'CompanyAddressLocality' => 'Company Address Locality',
                    'CompanyAddressTown' => 'Company Address Town',
                    'CompanyAddressCounty' => 'Company Address County',
                    'CompanyAddressPostCode' => 'Company Address Post Code',
                    'CompanyAddressCountry' => 'Company Address Country',
                    'CompanyWebsiteAddress' => 'Company Website Address',
                    'PhoneNumberMobile' => 'Mobile Phone',
                    'PhoneNumberLandline' => 'Landline Phone',
                    'EmailAddress' => 'Email Address'
                ];
        
                foreach ($fields as $key => $label) {
                    if (!empty($row[$key])) {
                        echo $label . ': ' . htmlspecialchars($row[$key]) . '<br/>';
                    }
                }
        
                echo '</li>';
            }
        } else {
            echo '<li class="list-group-item">No new registered users need activating.</li>';
        }
        
        echo '</ul></div>'; // Close col-8
        echo '
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

<?php $conn->close(); ?>












