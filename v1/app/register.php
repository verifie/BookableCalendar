<?php
// Concept Television Studios Limited
// Asset Booking System
// User Registration
// PME
// 20231230-0842

// Include database configuration
require_once 'x-dbconfig.php';

// Handle form submission
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create a connection to the MySQL database server
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set parameters with default values for optional fields
    $username = $_REQUEST['username'];
    $email = $_REQUEST['email'];
    $phoneMobile = $_REQUEST['phoneMobile'] ?? '';
    $phoneLandline = $_REQUEST['phoneLandline'] ?? '';
    $passwordHashed = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
    $firstName = $_REQUEST['firstName'] ?? '';
    $middleNames = $_REQUEST['middleNames'] ?? '';
    $lastName = $_REQUEST['lastName'] ?? '';
    $tradingName = $_REQUEST['tradingName'] ?? '';
    $fullCompanyName = $_REQUEST['fullCompanyName'] ?? '';
    $locationCompanyRegistered = $_REQUEST['locationCompanyRegistered'] ?? '';
    $companyRegistrationNumber = $_REQUEST['companyRegistrationNumber'] ?? '';
    $vatNumber = $_REQUEST['vatNumber'] ?? '';
    $companyAddressBuilding = $_REQUEST['companyAddressBuilding'] ?? '';
    $companyAddressStreet = $_REQUEST['companyAddressStreet'] ?? '';
    $companyAddressLocality = $_REQUEST['companyAddressLocality'] ?? '';
    $companyAddressTown = $_REQUEST['companyAddressTown'] ?? '';
    $companyAddressCounty = $_REQUEST['companyAddressCounty'] ?? '';
    $companyAddressPostCode = $_REQUEST['companyAddressPostCode'] ?? '';
    $companyAddressCountry = $_REQUEST['companyAddressCountry'] ?? '';
    $companyWebsiteAddress = $_REQUEST['companyWebsiteAddress'] ?? '';

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO Users (Username, EmailAddress, PhoneNumberMobile, PhoneNumberLandline, Password, UserType, FirstName, MiddleNames, LastName, TradingName, FullCompanyName, LocationCompanyRegistered, CompanyRegistrationNumber, VATNumber, CompanyAddressBuilding, CompanyAddressStreet, CompanyAddressLocality, CompanyAddressTown, CompanyAddressCounty, CompanyAddressPostCode, CompanyAddressCountry, CompanyWebsiteAddress) VALUES (?, ?, ?, ?, ?, 'registered', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssssssss", $username, $email, $phoneMobile, $phoneLandline, $passwordHashed, $firstName, $middleNames, $lastName, $tradingName, $fullCompanyName, $locationCompanyRegistered, $companyRegistrationNumber, $vatNumber, $companyAddressBuilding, $companyAddressStreet, $companyAddressLocality, $companyAddressTown, $companyAddressCounty, $companyAddressPostCode, $companyAddressCountry, $companyWebsiteAddress);
    
    // Set parameters and execute
    $username = $_REQUEST['username'];
    $email = $_REQUEST['email'];
    $phoneMobile = $_REQUEST['phoneMobile'];
    $phoneLandline = $_REQUEST['phoneLandline'];
    $passwordHashed = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
    $firstName = $_REQUEST['firstName'];
    $middleNames = $_REQUEST['middleNames'];
    $lastName = $_REQUEST['lastName'];
    $tradingName = $_REQUEST['tradingName'];
    $fullCompanyName = $_REQUEST['fullCompanyName'];
    $locationCompanyRegistered = $_REQUEST['locationCompanyRegistered'];
    $companyRegistrationNumber = $_REQUEST['companyRegistrationNumber'];
    $vatNumber = $_REQUEST['vatNumber'];
    $companyAddressBuilding = $_REQUEST['companyAddressBuilding'];
    $companyAddressStreet = $_REQUEST['companyAddressStreet'];
    $companyAddressLocality = $_REQUEST['companyAddressLocality'];
    $companyAddressTown = $_REQUEST['companyAddressTown'];
    $companyAddressCounty = $_REQUEST['companyAddressCounty'];
    $companyAddressPostCode = $_REQUEST['companyAddressPostCode'];
    $companyAddressCountry = $_REQUEST['companyAddressCountry'];
    $companyWebsiteAddress = $_REQUEST['companyWebsiteAddress'];

    if ($stmt->execute()) {
        $message = "User registered successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require 'x-header.php'; ?>

<div class="container">
    <h2>Register User</h2>
    <p><?php echo $message; ?></p>
    <form action="register.php" method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Mobile Phone Number</label>
            <input type="text" name="phoneMobile" class="form-control">
        </div>
        <div class="form-group">
            <label>Landline Phone Number</label>
            <input type="text" name="phoneLandline" class="form-control">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="firstName" class="form-control">
        </div>
        <div class="form-group">
            <label>Middle Name(s)</label>
            <input type="text" name="middleNames" class="form-control">
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lastName" class="form-control">
        </div>
        <div class="form-group">
            <label>Trading Name</label>
            <input type="text" name="tradingName" class="form-control">
        </div>
        <div class="form-group">
            <label>Full Company Name</label>
            <input type="text" name="fullCompanyName" class="form-control">
        </div>
        <div class="form-group">
            <label>Location Company Registered</label>
            <input type="text" name="locationCompanyRegistered" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Registration Number</label>
            <input type="text" name="companyRegistrationNumber" class="form-control">
        </div>
        <div class="form-group">
            <label>VAT Number</label>
            <input type="text" name="vatNumber" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - Building Number or Name</label>
            <input type="text" name="companyAddressBuilding" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - Street Name</label>
            <input type="text" name="companyAddressStreet" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - Locality</label>
            <input type="text" name="companyAddressLocality" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - Town</label>
            <input type="text" name="companyAddressTown" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - County</label>
            <input type="text" name="companyAddressCounty" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - Post Code</label>
            <input type="text" name="companyAddressPostCode" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Address - Country</label>
            <input type="text" name="companyAddressCountry" class="form-control">
        </div>
        <div class="form-group">
            <label>Company Website Address</label>
            <input type="text" name="companyWebsiteAddress" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>


<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
