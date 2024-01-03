<?php
// 1. Start the session
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

// Check if the user is logged in and is an admin. If not, redirect them to the login.php page.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["UserType"] !== 'admin') {
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

// Initialize variables
$asset = null;
$addOns = [];
$assetblocks = [];
$allAssets = [];
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



// 7. Fetch Commands
// -----------------

// Fetch Asset details using the row ID, as passed from the view screen. 
if (isset($_GET['id'])) {
    $assetId = $_GET['id'];

    // Fetch the full details of asset to be edited, and place in $asset
    $sql = "SELECT * FROM Assets WHERE AssetID = ?";
    if (!$stmt = $conn->prepare($sql)) {
        die("SQL preparation error: " . $conn->error);
    }
    $stmt->bind_param("i", $assetId);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();
    $stmt->close();

    
    // Fetch Booking Intervals for this Asset, and place in $asset
    $asset['BookingIntervals'] = explode(',', $asset['BookingIntervals']);

    // How we Fetch data from the database:
    // 1. Create SQL Command.
    // 2. Prepare the SQL command securely: preparing an SQL query for execution, making it ready to have parameters bound to it and to be executed in a safe and efficient manner. Prepared statements are a way to execute SQL queries in a more secure way, helping to prevent SQL injection attacks, and are especially useful when executing the same statement repeatedly with different parameters.
    // 3. Bind the integer value in $assetId to the first placeholder in the prepared SQL statement stored in $addOnStmt. This is commonly used to insert or update data in a database securely, helping to prevent SQL injection attacks.
    // 4. Run the command.
    // 5. Get the rows and put them in a variable. The get_result() method returns a result object that represents the result set obtained from the executed prepared statement. This result set contains the rows returned by the SQL query. If the query was, for example, a SELECT statement, then $addOnResult will contain the rows that match the query criteria.
    // 6. Get data from a database result set and store it in an array in PHP
    // 7. Close the database connection.


    // Fetch AddOns for this Asset, and place in $addOns
    $addOnSql = "SELECT a.AssetID, a.AssetName FROM AssetAddOns aa
                 JOIN Assets a ON aa.AddOnID = a.AssetID
                 WHERE aa.AssetID = ?";
    $addOnStmt = $conn->prepare($addOnSql);
    $addOnStmt->bind_param("i", $assetId);
    $addOnStmt->execute();
    $addOnResult = $addOnStmt->get_result();
    while ($addOnRow = $addOnResult->fetch_assoc()) {
        $addOns[] = $addOnRow;
    }
    $addOnStmt->close();
    

    // Fetch Asset Blocks for this Asset, and place in $assetBlocks
    $assetBlockSql = "SELECT a.AssetID, a.AssetName FROM AssetBlock ab
    JOIN Assets a ON ab.BlockID = a.AssetID
    WHERE ab.AssetID = ?";
    $assetBlockStmt = $conn->prepare($assetBlockSql);
    $assetBlockStmt->bind_param("i", $assetId);
    $assetBlockStmt->execute();
    $assetBlockResult = $assetBlockStmt->get_result();
    $assetBlocks = [];
    while ($assetBlockRow = $assetBlockResult->fetch_assoc()) {
    $assetBlocks[] = $assetBlockRow;
    }
    $assetBlockStmt->close();


    // Fetch all other assets for add-on selection
    $allAssetsSql = "SELECT AssetID, AssetName FROM Assets WHERE AssetID != ?";
    $allAssetsStmt = $conn->prepare($allAssetsSql);
    $allAssetsStmt->bind_param("i", $assetId);
    $allAssetsStmt->execute();
    $allAssetsResult = $allAssetsStmt->get_result();
    while ($allAssetRow = $allAssetsResult->fetch_assoc()) {
        $allAssets[$allAssetRow['AssetID']] = $allAssetRow['AssetName'];
    }
    $allAssetsStmt->close();
}


// Retrieve the selected minimum and maximum booking intervals for this asset.
$selectedMinInterval = isset($asset['MinBookingIntervals']) ? $asset['MinBookingIntervals'] : 0;
$selectedMaxInterval = isset($asset['MaxBookingIntervals']) ? $asset['MaxBookingIntervals'] : PHP_INT_MAX;


// Fetch other (Main) Assets for which this asset is an Add-On, and place in $mainAssets
$mainAssets = [];
$mainAssetsSql = "SELECT a.AssetID, a.AssetName FROM AssetAddOns aa
                  JOIN Assets a ON aa.AssetID = a.AssetID
                  WHERE aa.AddOnID = ?";
$mainAssetsStmt = $conn->prepare($mainAssetsSql);
$mainAssetsStmt->bind_param("i", $assetId);
$mainAssetsStmt->execute();
$mainAssetsResult = $mainAssetsStmt->get_result();
while ($mainAssetRow = $mainAssetsResult->fetch_assoc()) {
    $mainAssets[] = $mainAssetRow;
}
$mainAssetsStmt->close();


// Fetch Assets for which this asset is blocked, and place in $blockedForAssets
$blockedForAssets = [];
$blockedForSql = "SELECT a.AssetID, a.AssetName FROM AssetBlock ab
                  JOIN Assets a ON ab.AssetID = a.AssetID
                  WHERE ab.BlockID = ?";
$blockedForStmt = $conn->prepare($blockedForSql);
$blockedForStmt->bind_param("i", $assetId);
$blockedForStmt->execute();
$blockedForResult = $blockedForStmt->get_result();
while ($blockedForRow = $blockedForResult->fetch_assoc()) {
    $blockedForAssets[] = $blockedForRow;
}
$blockedForStmt->close();


// 8. Send back data - Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    // Prepare data to update asset entry with. Retrieve form data.
    $assetName = $_POST['assetName'];
    $description = $_POST['description'];

    $paymentRequired = $_POST['paymentRequired'];
    $minBookingValue = $_POST['minBookingValue'];

    $minBookingIntervals = $_POST['minBookingIntervals'];
    $maxBookingIntervals = $_POST['maxBookingIntervals'];
    
    $status = $_POST['status'];

    // Check if bookingIntervals is set and is an array
    if (isset($_POST['bookingIntervals']) && is_array($_POST['bookingIntervals'])) {
        // Convert selected booking intervals to CSV
        $bookingIntervalsCsv = implode(',', $_POST['bookingIntervals']);
    }


    //   database query to include BookingIntervals
    $updateSql = "UPDATE Assets SET AssetName = ?, Description = ?, PaymentRequired = ?, MinBookingValue = ?, MinBookingIntervals = ?, MaxBookingIntervals = ?, BookingIntervals = ?, Status = ? WHERE AssetID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssdisssi", $assetName, $description, $paymentRequired, $minBookingValue, $minBookingIntervals, $maxBookingIntervals, $bookingIntervalsCsv, $status, $assetId);



    // Update the database entry for this asset.
    if ($updateStmt->execute()) {
       
        
        // Handle AddOns update
        // ----------------------------------------------------------------------------
        // First, remove existing add-ons
        $deleteAddOnSql = "DELETE FROM AssetAddOns WHERE AssetID = ?";
        $deleteAddOnStmt = $conn->prepare($deleteAddOnSql);
        $deleteAddOnStmt->bind_param("i", $assetId);
        $deleteAddOnStmt->execute();
        $deleteAddOnStmt->close();

        // Then, insert selected add-ons
        $insertAddOnSql = "INSERT INTO AssetAddOns (AssetID, AddOnID) VALUES (?, ?)";
        $insertAddOnStmt = $conn->prepare($insertAddOnSql);
        foreach ($_POST['addOns'] as $addOnId) {
            $insertAddOnStmt->bind_param("ii", $assetId, $addOnId);
            $insertAddOnStmt->execute();
        }
        $insertAddOnStmt->close();


        // Handle Asset Blocks update
        // ----------------------------------------------------------------------------
        // First, remove existing asset blocks
        $deleteAssetBlockSql = "DELETE FROM AssetBlock WHERE AssetID = ?";
        $deleteAssetBlockStmt = $conn->prepare($deleteAssetBlockSql);
        $deleteAssetBlockStmt->bind_param("i", $assetId);
        $deleteAssetBlockStmt->execute();
        $deleteAssetBlockStmt->close();

        // Then, insert selected asset blocks
        if(isset($_POST['assetBlocks'])) {
            $insertAssetBlockSql = "INSERT INTO AssetBlock (AssetID, BlockID) VALUES (?, ?)";
            $insertAssetBlockStmt = $conn->prepare($insertAssetBlockSql);
            foreach ($_POST['assetBlocks'] as $blockId) {
                $insertAssetBlockStmt->bind_param("ii", $assetId, $blockId);
                $insertAssetBlockStmt->execute();
            }
            $insertAssetBlockStmt->close();
        }


        // Redirect to asset view page
        // ----------------------------------------------------------------------------
        $message = "Asset updated successfully.";
        header("location: admin_asset_view.php");

    } else {
        $message = "Error: " . $updateStmt->error;
    }


    $updateStmt->close();
}


// 9. Ensure the database connection is closed (For good practice).
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
    <title>Edit Asset - Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require 'x-header.php'; ?>

    <div class="container">

        <?php echo '<h1>ADMINISTRATOR ACCOUNT - ' . htmlspecialchars($_SESSION["username"]) . '</h1><br/><br/> '?>
        <div class="row">
            <div class="col-8">
                <h2>Edit Asset</h2>
                <p><?php echo $message; ?></p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $assetId; ?>" method="post">
                    <div class="form-group">
                        <label>Asset Name</label>
                        <input type="text" name="assetName" class="form-control" value="<?php echo htmlspecialchars($asset['AssetName']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" required><?php echo htmlspecialchars($asset['Description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Payment Required to book?</label>
                        <select name="paymentRequired" class="form-control">
                            <option value="1" <?php echo $asset['PaymentRequired'] ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo !$asset['PaymentRequired'] ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>1 minute booking cost (Prices are calculated as 1 minute multiples)</label>
                        <input type="number" step="0.01" name="minBookingValue" class="form-control" value="<?php echo htmlspecialchars($asset['MinBookingValue']); ?>">
                    </div>


                    <!-- Minimum Booking Intervals Dropdown -->
                    <div class="form-group">
                        <label>Minimum Booking Intervals</label>
                        <select name="minBookingIntervals" class="form-control">
                            <?php foreach ($bookingIntervals as $intervalValue => $intervalLabel): ?>
                                <option value="<?= $intervalValue ?>" <?= $intervalValue == $selectedMinInterval ? 'selected' : '' ?>>
                                    <?= $intervalLabel ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- Maximum Booking Intervals Dropdown -->
                    <div class="form-group">
                        <label>Maximum Booking Intervals</label>
                        <select name="maxBookingIntervals" class="form-control">
                            <?php foreach ($bookingIntervals as $intervalValue => $intervalLabel): ?>
                                <option value="<?= $intervalValue ?>" <?= $intervalValue == $selectedMaxInterval ? 'selected' : '' ?>>
                                    <?= $intervalLabel ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- Booking intervals available for this asset - checkbox -->
                    <div class="form-group">
                        <label>Booking Intervals (Minutes)</label>
                        <?php foreach ($bookingIntervals as $intervalValue => $intervalLabel): // Run through the Booking intervals array and separate the minute count and the human readable labels into 2x 2d arrays, referenced by an index ?> 
                            <?php if ($intervalValue >= $selectedMinInterval && $intervalValue <= $selectedMaxInterval): // Only make the intervals available for selection according to the earlier rules set. (This is not dynamic, so they must be updated first. May cause an issue if already selected and then not shown as then removed by min/ max, so we should check this when booking.?>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="bookingIntervals[]" id="interval_<?= $intervalValue ?>" value="<?= $intervalValue ?>" <?= in_array((string)$intervalValue, $asset['BookingIntervals']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="interval_<?= $intervalValue ?>"><?= $intervalLabel ?></label>
                                </div>

                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>


                    <!-- Booking type / status -->
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="2" <?php echo $asset['Status'] == 3 ? 'selected' : ''; ?>>Not Bookable, No longer available. Any outstanding cancelled.</option>
                            <option value="1" <?php echo $asset['Status'] == 2 ? 'selected' : ''; ?>>Not Bookable, but existing not cancelled.</option>
                            <option value="1" <?php echo $asset['Status'] == 1 ? 'selected' : ''; ?>>Add-On to other Bookable Asset ONLY.</option>
                            <option value="0" <?php echo $asset['Status'] == 0 ? 'selected' : ''; ?>>Bookable</option>
                        </select>
                    </div>

                    
                    <!-- List Current Add-Ons -->
                    <!-- Hidden as redundant (It's shown below).
                    <br/><hr style="height:1px;border:none;color:#333;background-color:#333;"><br/>
                    <h3>Current Add-Ons for this Asset</h3>-->

                    <!--?php if (!empty($addOns)): ?-->
                        <!--<ul>-->
                            <!--?php foreach ($addOns as $addOn): ?-->
                                <!--li--><!--?php echo htmlspecialchars($addOn['AssetName']); ?--><!--/li-->
                            <!--?php endforeach; ?>
                        </ul-->
                    <!--?php else: ?-->
                        <!--p>No add-ons for this asset.</p-->
                    <!--?php endif; ?-->



                    <!-- Show the relationship this asset has to other assets -->

                    <!-- This is an add-on for these assets: -->
                    <br/><hr style="height:1px;border:none;color:#333;background-color:#333;"><br/>
                    <h3>This Asset can be an Add-On for these Assets</h3>
                    <?php if (!empty($mainAssets)): ?>
                        <ul>
                            <?php foreach ($mainAssets as $mainAsset): ?>
                                <li><?php echo htmlspecialchars($mainAsset['AssetName']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>This asset is not an add-on for any other assets.</p>
                    <?php endif; ?>
                    

                    <!-- This asset is blocked for booking by these other assets -->
                    <br/><hr style="height:1px;border:none;color:#333;background-color:#333;"><br/>
                    <h3>This asset can be blocked for booking by these other assets:</h3>
                    <?php if (!empty($blockedForAssets)): ?>
                        <ul>
                            <?php foreach ($blockedForAssets as $blockedAsset): ?>
                                <li><?php echo htmlspecialchars($blockedAsset['AssetName']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>This asset is not blocked by any other asset.</p>
                    <?php endif; ?>
                    

                    <!-- Add or Remove Add-Ons -->
                    <br/><hr style="height:1px;border:none;color:#333;background-color:#333;"><br/>
                    <h3>Assign Add-Ons</h3>
                    <p>Select add-ons for this asset.  These will be presented to the person booking this asset as an option.  Their costs will be added cart and they will have their calendar booked with the same booking ID.</p>

                    <div class="form-group">
                        <?php foreach ($allAssets as $id => $name): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="addon-<?php echo $id; ?>" name="addOns[]" value="<?php echo $id; ?>" <?php echo in_array($id, array_column($addOns, 'AssetID')) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="addon-<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <!-- Add or Remove Asset Blocks -->
                    <br/><hr style="height:1px;border:none;color:#333;background-color:#333;"><br/>
                    <h3>Assign Asset Blocks</h3>
                    <p>There may be a case where another asset cannot be used if this asset is booked.  This allows us to block out the ability to book the ticked asset below if this asset is booked.  Blocked assets are optionally charged, so select "Charge" if their cost is to automatically be added to the cost of this asset.</p>
                    <p>This differs from Add-On Assets which can be booked (and charged) optionally with other asset bookings, and are chargeable (upsell opportunity).</p>

                    <div class="form-group">
                        <?php foreach ($allAssets as $id => $name): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="block-<?php echo $id; ?>" name="assetBlocks[]" value="<?php echo $id; ?>" <?php echo in_array($id, array_column($assetBlocks, 'AssetID')) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="block-<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <!-- Update entry -->
                    <br/><hr style="height:1px;border:none;color:#333;background-color:#333;"><br/>
                    <button type="submit" class="btn btn-primary">Update Asset</button>
                    <br/>
                    

                </form>
            </div>

            <!-- *********************************************************************************************** -->
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
