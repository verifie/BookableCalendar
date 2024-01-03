-- MariaDB dump 10.19  Distrib 10.6.12-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: concept_bookings
-- ------------------------------------------------------
-- Server version	10.6.12-MariaDB-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `AdminSettings`
--

DROP TABLE IF EXISTS `AdminSettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AdminSettings` (
  `SettingID` int(11) NOT NULL AUTO_INCREMENT,
  `MaxBookingLength` int(11) DEFAULT NULL,
  `AllowPauseUnpauseAssets` tinyint(1) DEFAULT NULL,
  `DefaultBookingLength` int(11) DEFAULT NULL,
  `MinimumBookingNotice` int(11) DEFAULT NULL,
  PRIMARY KEY (`SettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AdminSettings`
--

LOCK TABLES `AdminSettings` WRITE;
/*!40000 ALTER TABLE `AdminSettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `AdminSettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AssetAddOns`
--

DROP TABLE IF EXISTS `AssetAddOns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AssetAddOns` (
  `AssetAddOnID` int(11) NOT NULL AUTO_INCREMENT,
  `AssetID` int(11) DEFAULT NULL,
  `AddOnID` int(11) DEFAULT NULL,
  PRIMARY KEY (`AssetAddOnID`),
  KEY `AssetID` (`AssetID`),
  KEY `AddOnID` (`AddOnID`),
  CONSTRAINT `AssetAddOns_ibfk_1` FOREIGN KEY (`AssetID`) REFERENCES `Assets` (`AssetID`),
  CONSTRAINT `AssetAddOns_ibfk_2` FOREIGN KEY (`AddOnID`) REFERENCES `Assets` (`AssetID`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AssetAddOns`
--

LOCK TABLES `AssetAddOns` WRITE;
/*!40000 ALTER TABLE `AssetAddOns` DISABLE KEYS */;
INSERT INTO `AssetAddOns` VALUES (76,1,2),(77,1,3),(78,1,4),(81,8,3),(82,7,3),(83,5,6);
/*!40000 ALTER TABLE `AssetAddOns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AssetBlock`
--

DROP TABLE IF EXISTS `AssetBlock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AssetBlock` (
  `AssetBlockID` int(11) NOT NULL AUTO_INCREMENT,
  `AssetID` int(11) DEFAULT NULL,
  `BlockID` int(11) DEFAULT NULL,
  PRIMARY KEY (`AssetBlockID`),
  KEY `AssetID` (`AssetID`),
  KEY `BlockID` (`BlockID`),
  CONSTRAINT `AssetBlock_ibfk_1` FOREIGN KEY (`AssetID`) REFERENCES `Assets` (`AssetID`),
  CONSTRAINT `AssetBlock_ibfk_2` FOREIGN KEY (`BlockID`) REFERENCES `Assets` (`AssetID`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AssetBlock`
--

LOCK TABLES `AssetBlock` WRITE;
/*!40000 ALTER TABLE `AssetBlock` DISABLE KEYS */;
INSERT INTO `AssetBlock` VALUES (33,6,5),(34,8,7),(35,7,8);
/*!40000 ALTER TABLE `AssetBlock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Assets`
--

DROP TABLE IF EXISTS `Assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Assets` (
  `AssetID` int(11) NOT NULL AUTO_INCREMENT,
  `AssetName` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `PaymentRequired` text DEFAULT NULL,
  `MinBookingValue` decimal(10,2) DEFAULT NULL,
  `MinBookingIntervals` int(11) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `MaxBookingIntervals` int(11) DEFAULT NULL,
  `BookingIntervals` varchar(255) NOT NULL,
  PRIMARY KEY (`AssetID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Assets`
--

LOCK TABLES `Assets` WRITE;
/*!40000 ALTER TABLE `Assets` DISABLE KEYS */;
INSERT INTO `Assets` VALUES (1,'The Production Studio','1000 Sq.Ft TV Production Studios.','0',5.00,60,'0',967680,'2880,1440,720,480,240,180,120,60'),(2,'Studio Control Room / Gallery','A control room with large 2 metre wide window onlooking the studio.  Under-floor SDI cabling from the TV Production Studio terminates here.','1',1.66,60,'0',967680,'967680,20160,10080,7200,4320,2880,1440,720,480,240,180,120,60'),(3,'Board Room','7 Seater board room with conference TV, high quality sound system, and adjustable lighting.','0',0.16,15,'0',967680,'967680,20160,10080,7200,4320,2880,1440,720,480,240,180,120,60,30,15'),(4,'Dressing Room','A 2.5 metre by 1.5 metre dressing room with hot and cold running water (not drinking water).','1',0.66,15,'0',967680,'967680,20160,10080,7200,4320,2880,1440,720,480,240,180,120,60,30,15'),(5,'Creative Space 2 - Room','The full creative space. Currently set with a sofa, two desks and adjustable lighting suitable for meeting, working, editing or other.','0',0.05,15,'0',967680,'967680,20160,10080,7200,4320,2880,1440,720,480,240,180,120,60,30,15'),(6,'Creative Space 2 - Desk','A desk with internet access in a shared office / creative space.','0',0.03,30,'0',967680,'967680,20160,10080,7200,4320,2880,1440,720,480,240,180,120,60,30'),(7,'Creative Space 1 - Room','The room with the glass window nearest the entrance door.','0',0.06,60,'0',967680,'967680,20160,10080,7200,4320,2880,1440,720,480,240,180,120,60'),(8,'Creative Space 1 - Desk','A desk with internet access in a shared office / creative space in the office with the glass window nearest the entrance.','0',0.04,60,'0',967680,'1440,720,480,240,180,120,60');
/*!40000 ALTER TABLE `Assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BookingHistory`
--

DROP TABLE IF EXISTS `BookingHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BookingHistory` (
  `HistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `BookingID` int(11) DEFAULT NULL,
  `Action` varchar(50) DEFAULT NULL,
  `Timestamp` datetime DEFAULT NULL,
  `User` int(11) DEFAULT NULL,
  PRIMARY KEY (`HistoryID`),
  KEY `BookingID` (`BookingID`),
  KEY `User` (`User`),
  CONSTRAINT `BookingHistory_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `Bookings` (`BookingID`),
  CONSTRAINT `BookingHistory_ibfk_2` FOREIGN KEY (`User`) REFERENCES `Users` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BookingHistory`
--

LOCK TABLES `BookingHistory` WRITE;
/*!40000 ALTER TABLE `BookingHistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `BookingHistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BookingLengths`
--

DROP TABLE IF EXISTS `BookingLengths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BookingLengths` (
  `LengthID` int(11) NOT NULL AUTO_INCREMENT,
  `LengthInMinutes` int(11) NOT NULL,
  PRIMARY KEY (`LengthID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BookingLengths`
--

LOCK TABLES `BookingLengths` WRITE;
/*!40000 ALTER TABLE `BookingLengths` DISABLE KEYS */;
/*!40000 ALTER TABLE `BookingLengths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Bookings`
--

DROP TABLE IF EXISTS `Bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Bookings` (
  `BookingID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `AssetID` int(11) DEFAULT NULL,
  `StartTime` datetime DEFAULT NULL,
  `EndTime` datetime DEFAULT NULL,
  `PaymentValue` decimal(10,2) DEFAULT NULL,
  `LengthID` int(11) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `PrimaryBookingId` int(11) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `Attended` int(11) DEFAULT NULL,
  `Complaint` int(11) DEFAULT NULL,
  PRIMARY KEY (`BookingID`),
  KEY `UserID` (`UserID`),
  KEY `AssetID` (`AssetID`),
  KEY `LengthID` (`LengthID`),
  CONSTRAINT `Bookings_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`),
  CONSTRAINT `Bookings_ibfk_2` FOREIGN KEY (`AssetID`) REFERENCES `Assets` (`AssetID`),
  CONSTRAINT `Bookings_ibfk_3` FOREIGN KEY (`LengthID`) REFERENCES `BookingLengths` (`LengthID`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Bookings`
--

LOCK TABLES `Bookings` WRITE;
/*!40000 ALTER TABLE `Bookings` DISABLE KEYS */;
INSERT INTO `Bookings` VALUES (20,2,1,'2024-01-03 10:30:00','2024-01-03 12:30:00',NULL,NULL,'1',NULL,120,NULL,NULL),(21,2,1,'2024-01-13 10:10:00','2024-01-13 12:10:00',NULL,NULL,'1',NULL,120,NULL,NULL),(22,2,1,'2024-01-04 10:20:00','2024-01-04 12:20:00',NULL,NULL,'1',NULL,120,NULL,NULL),(23,2,2,'2024-01-04 10:20:00','2024-01-04 12:20:00',NULL,NULL,'1',22,120,NULL,NULL),(24,2,3,'2024-01-04 10:20:00','2024-01-04 12:20:00',NULL,NULL,'1',22,120,NULL,NULL),(25,2,1,'2024-01-03 10:30:00','2024-01-03 12:30:00',NULL,NULL,'1',NULL,120,NULL,NULL),(26,2,3,'2024-01-03 10:30:00','2024-01-03 12:30:00',NULL,NULL,'1',25,120,NULL,NULL),(27,2,1,'2024-01-03 14:30:00','2024-01-03 22:30:00',NULL,NULL,'1',NULL,480,NULL,NULL),(28,2,4,'2024-01-03 14:30:00','2024-01-03 22:30:00',NULL,NULL,'1',27,480,NULL,NULL),(29,2,1,'2024-01-05 14:00:00','2024-01-05 15:00:00',NULL,NULL,'1',NULL,60,NULL,NULL),(30,2,2,'2024-01-05 14:00:00','2024-01-05 15:00:00',NULL,NULL,'1',29,60,NULL,NULL),(31,2,3,'2024-01-05 14:00:00','2024-01-05 15:00:00',NULL,NULL,'1',29,60,NULL,NULL),(32,2,4,'2024-01-05 14:00:00','2024-01-05 15:00:00',NULL,NULL,'1',29,60,NULL,NULL),(33,2,1,'2024-01-24 10:55:00','2024-01-24 11:55:00',NULL,NULL,'1',NULL,60,NULL,NULL),(34,2,2,'2024-01-24 10:55:00','2024-01-24 11:55:00',NULL,NULL,'1',33,60,NULL,NULL),(35,2,1,'2024-02-14 09:10:00','2024-02-14 17:10:00',NULL,NULL,'1',NULL,480,NULL,NULL),(36,2,8,'2024-01-17 08:29:00','2024-01-17 20:29:00',NULL,NULL,'1',NULL,720,NULL,NULL),(37,2,3,'2024-01-17 08:29:00','2024-01-17 20:29:00',NULL,NULL,'1',36,720,NULL,NULL),(38,2,1,'2024-01-30 09:30:00','2024-01-30 17:30:00',NULL,NULL,'1',NULL,480,NULL,NULL),(39,2,2,'2024-01-30 09:30:00','2024-01-30 17:30:00',NULL,NULL,'1',38,480,NULL,NULL),(40,2,1,'2024-01-11 14:20:00','2024-01-11 15:20:00',NULL,NULL,'1',NULL,60,NULL,NULL),(41,2,2,'2024-01-11 14:20:00','2024-01-11 15:20:00',NULL,NULL,'1',40,60,NULL,NULL);
/*!40000 ALTER TABLE `Bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Payments`
--

DROP TABLE IF EXISTS `Payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Payments` (
  `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `ExternalPaymentReference` varchar(255) DEFAULT NULL,
  `PaymentType` varchar(50) DEFAULT NULL,
  `TransactionCreated` datetime DEFAULT NULL,
  `Value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`PaymentID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `Payments_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Payments`
--

LOCK TABLES `Payments` WRITE;
/*!40000 ALTER TABLE `Payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `Payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(255) NOT NULL,
  `EmailAddress` varchar(255) DEFAULT NULL,
  `PhoneNumberMobile` varchar(20) DEFAULT NULL,
  `PhoneNumberLandline` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `UserType` varchar(50) DEFAULT NULL,
  `FirstName` varchar(255) DEFAULT NULL,
  `MiddleNames` varchar(255) DEFAULT NULL,
  `LastName` varchar(255) DEFAULT NULL,
  `TradingName` varchar(255) DEFAULT NULL,
  `FullCompanyName` varchar(255) DEFAULT NULL,
  `LocationCompanyRegistered` varchar(255) DEFAULT NULL,
  `CompanyRegistrationNumber` varchar(50) DEFAULT NULL,
  `VATNumber` varchar(50) DEFAULT NULL,
  `CompanyAddressBuilding` varchar(255) DEFAULT NULL,
  `CompanyAddressStreet` varchar(255) DEFAULT NULL,
  `CompanyAddressLocality` varchar(255) DEFAULT NULL,
  `CompanyAddressTown` varchar(255) DEFAULT NULL,
  `CompanyAddressCounty` varchar(255) DEFAULT NULL,
  `CompanyAddressPostCode` varchar(50) DEFAULT NULL,
  `CompanyAddressCountry` varchar(255) DEFAULT NULL,
  `CompanyWebsiteAddress` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'siteadmin',NULL,NULL,NULL,'$2y$10$5/PMJkCll6xWVHzelalcg.vdiC71mnn6uAbyftByYgk5ITzTEc5Vi','admin',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'john','john@conceptstudios.co.uk','','','$2y$10$R5VrUfZYKV1FIlkx1gFtSu2Mtt90VmeB6Q/qzBcmlJl7RmN.XHtQG','activeuser','','','','','','','','','','','','','','','',''),(3,'bob','bobby@conceptmedia.group','','','$2y$10$XjaMmoAR/Bn7nNnjJDLVHORCL7bbZQeZByx9Rd7MY26yw3ELm0DZq','activeuser','','','','','','','','','','','','','','','',''),(4,'Alex','halex12345@hotmail.com','','','$2y$10$4Rf89RWqAi4.TGIysiLWyuQhRxF8jME0FFKlB44/QtC5kga7FA6lm','registered','','','','','','','','','','','','','','','','');
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'concept_bookings'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-01-03 23:51:26
