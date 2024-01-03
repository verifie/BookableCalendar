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


// This is a sub-feature script, depending on being called by admin_users_view.php. It should be called with a UserID
// Check if UserID is passed.
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch User Details
    $sql = "SELECT * FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Update User Details
        $updateSql = "UPDATE Users SET 
            Username=?, 
            EmailAddress=?, 
            PhoneNumberMobile=?, 
            PhoneNumberLandline=?, 
            FirstName=?, 
            MiddleNames=?, 
            LastName=?, 
            TradingName=?, 
            FullCompanyName=?, 
            LocationCompanyRegistered=?, 
            CompanyRegistrationNumber=?, 
            VATNumber=?, 
            CompanyAddressBuilding=?, 
            CompanyAddressStreet=?, 
            CompanyAddressLocality=?, 
            CompanyAddressTown=?, 
            CompanyAddressCounty=?, 
            CompanyAddressPostCode=?, 
            CompanyAddressCountry=?, 
            CompanyWebsiteAddress=?, 
            UserType=? 
            WHERE UserID=?";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssssssssssssssssssssi", 
            $_POST['username'], 
            $_POST['email'], 
            $_POST['phoneMobile'], 
            $_POST['phoneLandline'], 
            $_POST['firstName'], 
            $_POST['middleNames'], 
            $_POST['lastName'], 
            $_POST['tradingName'], 
            $_POST['fullCompanyName'], 
            $_POST['locationCompanyRegistered'], 
            $_POST['companyRegistrationNumber'], 
            $_POST['vatNumber'], 
            $_POST['companyAddressBuilding'], 
            $_POST['companyAddressStreet'], 
            $_POST['companyAddressLocality'], 
            $_POST['companyAddressTown'], 
            $_POST['companyAddressCounty'], 
            $_POST['companyAddressPostCode'], 
            $_POST['companyAddressCountry'], 
            $_POST['companyWebsiteAddress'], 
            $_POST['userType'], 
            $userId);

        // Execute and check for errors
        if ($updateStmt->execute()) {
            $message = "User updated successfully.";
        } else {
            $message = "Error updating user: " . $updateStmt->error;
        }
        $updateStmt->close();
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require 'x-header.php'; ?>

    <div class="container">
        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '?>

            <div class="row">
                <div class="col-8">

                    <h2>Edit User</h2>
                    <p><?php echo $message; ?></p>

                    <!-- User Edit Form -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $userId; ?>" method="post">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><label>Username</label></td>
                                    <td><input type="text" name="username" class="form-control" value="<?php echo $user['Username']; ?>" required></td>
                                </tr>
                                <tr>
                                    <td><label>Email Address</label></td>
                                    <td><input type="email" name="email" class="form-control" value="<?php echo $user['EmailAddress']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Mobile Phone Number</label></td>
                                    <td><input type="text" name="phoneMobile" class="form-control" value="<?php echo $user['PhoneNumberMobile']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Landline Phone Number</label></td>
                                    <td><input type="text" name="phoneLandline" class="form-control" value="<?php echo $user['PhoneNumberLandline']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>First Name</label></td>
                                    <td><input type="text" name="firstName" class="form-control" value="<?php echo $user['FirstName']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Middle Names</label></td>
                                    <td><input type="text" name="middleNames" class="form-control" value="<?php echo $user['MiddleNames']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Last Name</label></td>
                                    <td><input type="text" name="lastName" class="form-control" value="<?php echo $user['LastName']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Trading Name</label></td>
                                    <td><input type="text" name="tradingName" class="form-control" value="<?php echo $user['TradingName']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Full Company Name</label></td>
                                    <td><input type="text" name="fullCompanyName" class="form-control" value="<?php echo $user['FullCompanyName']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Location Company Registered</label></td>
                                    <td><input type="text" name="locationCompanyRegistered" class="form-control" value="<?php echo $user['LocationCompanyRegistered']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Registration Number</label></td>
                                    <td><input type="text" name="companyRegistrationNumber" class="form-control" value="<?php echo $user['CompanyRegistrationNumber']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>VAT Number</label></td>
                                    <td><input type="text" name="vatNumber" class="form-control" value="<?php echo $user['VATNumber']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - Building Number or Name</label></td>
                                    <td><input type="text" name="companyAddressBuilding" class="form-control" value="<?php echo $user['CompanyAddressBuilding']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - Street Name</label></td>
                                    <td><input type="text" name="companyAddressStreet" class="form-control" value="<?php echo $user['CompanyAddressStreet']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - Locality</label></td>
                                    <td><input type="text" name="companyAddressLocality" class="form-control" value="<?php echo $user['CompanyAddressLocality']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - Town</label></td>
                                    <td><input type="text" name="companyAddressTown" class="form-control" value="<?php echo $user['CompanyAddressTown']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - County</label></td>
                                    <td><input type="text" name="companyAddressCounty" class="form-control" value="<?php echo $user['CompanyAddressCounty']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - Post Code</label></td>
                                    <td><input type="text" name="companyAddressPostCode" class="form-control" value="<?php echo $user['CompanyAddressPostCode']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Address - Country</label></td>
                                    <td><input type="text" name="companyAddressCountry" class="form-control" value="<?php echo $user['CompanyAddressCountry']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>Company Website Address</label></td>
                                    <td><input type="text" name="companyWebsiteAddress" class="form-control" value="<?php echo $user['CompanyWebsiteAddress']; ?>"></td>
                                </tr>
                                <tr>
                                    <td><label>User Type</label></td>
                                    <td>
                                        <select name="userType" class="form-control">
                                            <option value="admin" <?php echo $user['UserType'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="activeuser" <?php echo $user['UserType'] === 'activeuser' ? 'selected' : ''; ?>>Active User</option>
                                            <option value="disableduser" <?php echo $user['UserType'] === 'disableduser' ? 'selected' : ''; ?>>User Account Disabled</option>
                                            <option value="registered" <?php echo $user['UserType'] === 'registered' ? 'selected' : ''; ?>>New User (Not active)</option>
                                        </select>
                                    </td>
                                </tr>
                                <!-- Submit Button -->
                                <tr>
                                    <td></td>
                                    <td><button type="submit" class="btn btn-primary">Update User</button></td>
                                </tr>
                            </tbody>
                        </table>
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
