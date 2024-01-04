<?php
// Concept Television Studios Limited
// Asset Booking System
// PME
// 20231230-0842


// Include database configuration
require_once '../x-dbconfig.php'; // Make sure to create this file with your database configuration


// Create a connection to the MySQL database server
$conn = new mysqli($servername, $username, $password);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Close the connection to the MySQL server
$conn->close();

// Create a new connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statements to create tables based on your schema
$sql = "
    CREATE TABLE Users (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(255) NOT NULL,
        EmailAddress VARCHAR(255),
        PhoneNumberMobile VARCHAR(20),
        PhoneNumberLandline VARCHAR(20),
        Password VARCHAR(255) NOT NULL,
        UserType VARCHAR(50),
        FirstName VARCHAR(255),
        MiddleNames VARCHAR(255),
        LastName VARCHAR(255),
        TradingName VARCHAR(255),
        FullCompanyName VARCHAR(255),
        LocationCompanyRegistered VARCHAR(255),
        CompanyRegistrationNumber VARCHAR(50),
        VATNumber VARCHAR(50),
        CompanyAddressBuilding VARCHAR(255),
        CompanyAddressStreet VARCHAR(255),
        CompanyAddressLocality VARCHAR(255),
        CompanyAddressTown VARCHAR(255),
        CompanyAddressCounty VARCHAR(255),
        CompanyAddressPostCode VARCHAR(50),
        CompanyAddressCountry VARCHAR(255),
        CompanyWebsiteAddress VARCHAR(255)
    );

    CREATE TABLE Assets (
        AssetID INT AUTO_INCREMENT PRIMARY KEY,
        AssetName VARCHAR(255) NOT NULL,
        Description TEXT,
        PaymentRequired TEXT,
        MinBookingValue DECIMAL(10, 2),
        MinBookingIntervals INT,
        MaxBookingIntervals INT,
        BookingIntervals VARCHAR(255),
        Status VARCHAR(50)
    );
    
    CREATE TABLE AssetAddOns (
        AssetAddOnID INT AUTO_INCREMENT PRIMARY KEY,
        AssetID INT,
        AddOnID INT,
        FOREIGN KEY (AssetID) REFERENCES Assets(AssetID),
        FOREIGN KEY (AddOnID) REFERENCES Assets(AssetID)
    );

    CREATE TABLE AssetBlock (
        AssetBlockID INT AUTO_INCREMENT PRIMARY KEY,
        AssetID INT,
        BlockID INT,
        FOREIGN KEY (AssetID) REFERENCES Assets(AssetID),
        FOREIGN KEY (BlockID) REFERENCES Assets(AssetID)
    );

    CREATE TABLE Bookings (
        BookingID INT AUTO_INCREMENT PRIMARY KEY,
        UserID INT,
        AssetID INT,
        PrimaryBookingId INT,
        StartTime DATETIME,
        EndTime DATETIME,
        Duration INT,
        Attended INT,
        Complaint INT,
        PaymentValue DECIMAL(10, 2),
        LengthID INT,
        Status VARCHAR(50),
        FOREIGN KEY (UserID) REFERENCES Users(UserID),
        FOREIGN KEY (AssetID) REFERENCES Assets(AssetID),
    );

    CREATE TABLE AdminSettings (
        SettingID INT AUTO_INCREMENT PRIMARY KEY,
        SettingName VARCHAR(255),
        Setting VARCHAR(255)
    );

    CREATE TABLE WorkingDaysClosed (
        ClosedDayID INT AUTO_INCREMENT PRIMARY KEY,
        WorkingClosedDaysName VARCHAR(255),
        WorkingClosedDays DATE
    );

    CREATE TABLE WorkingDaysHoliday (
        HolidayDayID INT AUTO_INCREMENT PRIMARY KEY,
        WorkingHolidaysName VARCHAR(255)
        WorkingHolidays DATE,
    );


    CREATE TABLE Payments (
        PaymentID INT AUTO_INCREMENT PRIMARY KEY,
        UserID INT,
        ExternalPaymentReference VARCHAR(255),
        PaymentType VARCHAR(50),
        TransactionCreated DATETIME,
        Value DECIMAL(10, 2),
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
    );
";

if ($conn->multi_query($sql) === TRUE) {
    echo "Tables created successfully\n";
} else {
    echo "Error creating tables: " . $conn->error . "\n";
}

// Close the connection
$conn->close();
?>
