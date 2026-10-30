-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mahuran_camel_farm
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','late') DEFAULT 'present',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,6,'2026-02-24','12:05:59',NULL,'present',NULL),(2,1,'2026-03-03','10:11:07',NULL,'present',NULL),(3,3,'2026-03-09','10:56:17',NULL,'late',NULL),(5,2,'2026-05-21','09:56:53',NULL,'late',NULL),(6,1,'2026-06-05','17:58:47',NULL,'present',NULL),(7,2,'2026-06-05','17:59:04',NULL,'present',NULL),(8,3,'2026-06-05','17:59:11',NULL,'present',NULL),(9,4,'2026-06-05','17:59:17',NULL,'present',NULL),(10,5,'2026-06-05','17:59:26',NULL,'present',NULL),(11,6,'2026-06-05','17:59:32',NULL,'present',NULL),(12,8,'2026-06-05','17:59:41',NULL,'present',NULL),(13,1,'2026-06-11','09:43:32',NULL,'present',NULL);
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Main Branch','Hargeisa Headquarters',NULL,NULL,'active','2026-04-24 15:55:12'),(5,'mahuran3','burco','555','m3@gmail.com','active','2026-06-17 08:15:11');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `breeding_records`
--

DROP TABLE IF EXISTS `breeding_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `breeding_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `female_camel_id` int(11) DEFAULT NULL,
  `male_camel_id` int(11) DEFAULT NULL,
  `breeding_date` date DEFAULT NULL,
  `expected_delivery` date DEFAULT NULL,
  `actual_delivery` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `female_camel_id` (`female_camel_id`),
  KEY `male_camel_id` (`male_camel_id`),
  CONSTRAINT `breeding_records_ibfk_1` FOREIGN KEY (`female_camel_id`) REFERENCES `camels` (`id`),
  CONSTRAINT `breeding_records_ibfk_2` FOREIGN KEY (`male_camel_id`) REFERENCES `camels` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `breeding_records`
--

LOCK TABLES `breeding_records` WRITE;
/*!40000 ALTER TABLE `breeding_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `breeding_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calves`
--

DROP TABLE IF EXISTS `calves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `mother_id` int(11) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `health_status` varchar(50) DEFAULT NULL,
  `birth_weight` decimal(10,2) DEFAULT NULL,
  `tag_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mother_id` (`mother_id`),
  CONSTRAINT `calves_ibfk_1` FOREIGN KEY (`mother_id`) REFERENCES `camels` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calves`
--

LOCK TABLES `calves` WRITE;
/*!40000 ALTER TABLE `calves` DISABLE KEYS */;
/*!40000 ALTER TABLE `calves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `camel_ownership`
--

DROP TABLE IF EXISTS `camel_ownership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camel_ownership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `owner_name` varchar(200) DEFAULT NULL,
  `owner_phone` varchar(20) DEFAULT NULL,
  `owner_address` text DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  CONSTRAINT `camel_ownership_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `camel_ownership`
--

LOCK TABLES `camel_ownership` WRITE;
/*!40000 ALTER TABLE `camel_ownership` DISABLE KEYS */;
/*!40000 ALTER TABLE `camel_ownership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `camels`
--

DROP TABLE IF EXISTS `camels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female') NOT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `farm_location` varchar(200) DEFAULT NULL,
  `status` enum('active','sold','deceased') DEFAULT 'active',
  `image` varchar(255) DEFAULT 'default-camel.jpg',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `camels`
--

LOCK TABLES `camels` WRITE;
/*!40000 ALTER TABLE `camels` DISABLE KEYS */;
INSERT INTO `camels` VALUES (1,'01','TIIR','b',10,'female',263.00,'hargiesa','active','default-camel.jpg',NULL,'2026-03-02 08:25:45',1),(2,'02','maandeeq','5',22,'male',302.00,'hargiesa','active','default-camel.jpg',NULL,'2026-03-09 08:01:09',1),(4,'11','mahuraan','5',16,'female',456.00,'boorama','active','default-camel.jpg',NULL,'2026-06-09 07:44:53',4);
/*!40000 ALTER TABLE `camels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_conversations`
--

DROP TABLE IF EXISTS `chat_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) DEFAULT NULL,
  `user2_id` int(11) DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_conversation` (`user1_id`,`user2_id`),
  KEY `user2_id` (`user2_id`),
  CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_conversations`
--

LOCK TABLES `chat_conversations` WRITE;
/*!40000 ALTER TABLE `chat_conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_logo`
--

DROP TABLE IF EXISTS `company_logo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_logo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logo_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_logo`
--

LOCK TABLES `company_logo` WRITE;
/*!40000 ALTER TABLE `company_logo` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_logo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `address_type` enum('home','work','other') DEFAULT 'home',
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_subscriptions`
--

DROP TABLE IF EXISTS `customer_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','paused','cancelled') DEFAULT 'active',
  `delivery_address` text DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `customer_subscriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `customer_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_subscriptions`
--

LOCK TABLES `customer_subscriptions` WRITE;
/*!40000 ALTER TABLE `customer_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,7,'kahin','1234567','m@gmail.com','jigjiga yar','hargiesa','active',NULL),(2,8,'farah gedi warfaa','4882200','fa@gamil.com','harawo','borama','active',NULL),(3,9,'iid ','77777','hasancismaan2@gmail.com','hargiesa jameeco weyn','hargiesa','active',NULL),(4,96,'mohamed','2526355555','mus@gmail.com','12m','hargiesa','active',NULL),(5,269,'ali abdi','4264312','ali@gmail.com','harawo','borama','active',NULL),(8,908,'abdi gedi','3000000','','jigjiga yar','','active',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_backups`
--

DROP TABLE IF EXISTS `database_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_name` varchar(255) DEFAULT NULL,
  `backup_path` varchar(255) DEFAULT NULL,
  `backup_size` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `database_backups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_backups`
--

LOCK TABLES `database_backups` WRITE;
/*!40000 ALTER TABLE `database_backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `database_backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` enum('assigned','in_transit','delivered','failed') DEFAULT 'assigned',
  `delivery_address` text DEFAULT NULL,
  `recipient_name` varchar(200) DEFAULT NULL,
  `proof_of_delivery` text DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
INSERT INTO `deliveries` VALUES (1,1,NULL,'2026-02-26','delivered','jigjiga yar','kahin','proof_1773042869_1249.webp',NULL,'2026-03-09 10:54:29',NULL),(2,3,NULL,'2026-03-05','delivered','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(3,4,NULL,'2026-03-05','assigned','harawo','farah gedi warfaa',NULL,NULL,NULL,NULL),(4,5,NULL,'2026-03-06','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(5,8,NULL,'2026-03-08','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(6,9,NULL,'2026-03-08','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(7,10,NULL,'2026-03-09','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(8,11,NULL,'2026-03-09','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(9,12,NULL,'2026-03-10','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(10,13,NULL,'2026-03-16','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(11,14,NULL,'2026-03-27','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(12,15,NULL,'2026-04-04','assigned','jigjiga yar','kahin',NULL,NULL,NULL,NULL),(13,17,NULL,'2026-04-28','assigned','12m','mohamed',NULL,NULL,NULL,NULL),(14,18,NULL,'2026-05-20','assigned','harawo','ali abdi',NULL,NULL,NULL,NULL),(15,19,4,'2026-05-29','delivered','jigjiga yar','kahin',NULL,NULL,'2026-06-08 12:47:08','2026-06-05 20:49:28'),(16,20,4,'2026-06-10','delivered','jigjiga yar','abdi gedi',NULL,NULL,'2026-06-18 12:52:26','2026-06-09 10:28:42');
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_performance`
--

DROP TABLE IF EXISTS `delivery_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `deliveries_count` int(11) DEFAULT NULL,
  `on_time_deliveries` int(11) DEFAULT NULL,
  `delayed_deliveries` int(11) DEFAULT NULL,
  `failed_deliveries` int(11) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `delivery_performance_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_performance`
--

LOCK TABLES `delivery_performance` WRITE;
/*!40000 ALTER TABLE `delivery_performance` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_performance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_performance_reports`
--

DROP TABLE IF EXISTS `delivery_performance_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_performance_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_deliveries` int(11) DEFAULT NULL,
  `on_time_deliveries` int(11) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT NULL,
  `driver_performance` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`driver_performance`)),
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_performance_reports`
--

LOCK TABLES `delivery_performance_reports` WRITE;
/*!40000 ALTER TABLE `delivery_performance_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_performance_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_proofs`
--

DROP TABLE IF EXISTS `delivery_proofs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_proofs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `delivery_id` (`delivery_id`),
  CONSTRAINT `delivery_proofs_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_proofs`
--

LOCK TABLES `delivery_proofs` WRITE;
/*!40000 ALTER TABLE `delivery_proofs` DISABLE KEYS */;
INSERT INTO `delivery_proofs` VALUES (1,1,'proof_1773042869_1249.webp','2026-03-09 07:54:29');
/*!40000 ALTER TABLE `delivery_proofs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_zones`
--

DROP TABLE IF EXISTS `delivery_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT NULL,
  `estimated_time` varchar(50) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_zones`
--

LOCK TABLES `delivery_zones` WRITE;
/*!40000 ALTER TABLE `delivery_zones` DISABLE KEYS */;
INSERT INTO `delivery_zones` VALUES (1,'m.moge','hargiesa',3.00,'20',NULL),(2,'qudhac dheer','hargiesa',0.05,'20',NULL);
/*!40000 ALTER TABLE `delivery_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Management','Management Department','2026-02-24 08:11:03',1),(2,'Veterinary','Veterinary Department','2026-02-24 08:11:03',1),(3,'Farm Operations','Farm Operations Department','2026-02-24 08:11:03',1),(4,'Processing','Processing Department','2026-02-24 08:11:03',1),(5,'Delivery','Delivery Department','2026-02-24 08:11:03',1),(6,'Sales','Sales Department','2026-02-24 08:11:03',1),(8,'IT','','2026-05-02 08:48:57',1),(17,'hrn','','2026-06-09 06:41:17',1),(19,'hrm','','2026-06-09 07:40:20',4);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disease_records`
--

DROP TABLE IF EXISTS `disease_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disease_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `disease_name` varchar(200) DEFAULT NULL,
  `diagnosed_date` date DEFAULT NULL,
  `severity` enum('mild','moderate','severe') DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `recovery_date` date DEFAULT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  CONSTRAINT `disease_records_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disease_records`
--

LOCK TABLES `disease_records` WRITE;
/*!40000 ALTER TABLE `disease_records` DISABLE KEYS */;
INSERT INTO `disease_records` VALUES (1,1,'caal','2026-06-11','mild','injection','2026-06-11','ahmed',NULL);
/*!40000 ALTER TABLE `disease_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver_performance`
--

DROP TABLE IF EXISTS `driver_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `driver_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `deliveries_count` int(11) DEFAULT NULL,
  `on_time_deliveries` int(11) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `driver_performance_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver_performance`
--

LOCK TABLES `driver_performance` WRITE;
/*!40000 ALTER TABLE `driver_performance` DISABLE KEYS */;
INSERT INTO `driver_performance` VALUES (1,4,'2026-06-08',1,1,5.00),(2,4,'2026-06-18',1,1,5.00);
/*!40000 ALTER TABLE `driver_performance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_notifications`
--

DROP TABLE IF EXISTS `email_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(100) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_notifications`
--

LOCK TABLES `email_notifications` WRITE;
/*!40000 ALTER TABLE `email_notifications` DISABLE KEYS */;
INSERT INTO `email_notifications` VALUES (1,'m@gmail.com','Order Confirmation #ORD-20260308-9524','Thank you for your order. Your order #ORD-20260308-9524 has been placed successfully.','pending',NULL,'2026-03-08 07:37:43'),(2,'m@gmail.com','Payment Verified - Order #ORD-20260308-9524','Your payment has been verified. Your order is now being processed.','pending',NULL,'2026-03-08 07:40:00');
/*!40000 ALTER TABLE `email_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_awards`
--

DROP TABLE IF EXISTS `employee_awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_awards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `award_type` varchar(100) NOT NULL COMMENT 'employee_of_month, top_performer, etc.',
  `award_title` varchar(200) NOT NULL,
  `award_description` text DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `cash_bonus` decimal(10,2) DEFAULT 0.00,
  `given_by` int(11) DEFAULT NULL COMMENT 'user_id who gave the award',
  `award_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_awards`
--

LOCK TABLES `employee_awards` WRITE;
/*!40000 ALTER TABLE `employee_awards` DISABLE KEYS */;
INSERT INTO `employee_awards` VALUES (1,4,NULL,'custom','Employee of the Month (June)','Awarded for exceptional performance and dedication during June 2026.',6,2026,50.00,10,'2026-06-05','2026-06-05 15:27:15');
/*!40000 ALTER TABLE `employee_awards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_performance`
--

DROP TABLE IF EXISTS `employee_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `month` int(11) NOT NULL COMMENT '1-12',
  `year` int(11) NOT NULL,
  `attendance_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Based on attendance percentage',
  `punctuality_score` decimal(5,2) DEFAULT 0.00,
  `tasks_completed` int(11) DEFAULT 0,
  `milk_collected_liters` decimal(10,2) DEFAULT 0.00,
  `custom_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Manual score set by manager',
  `total_score` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `ranked_position` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_staff_month` (`staff_id`,`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_performance`
--

LOCK TABLES `employee_performance` WRITE;
/*!40000 ALTER TABLE `employee_performance` DISABLE KEYS */;
INSERT INTO `employee_performance` VALUES (1,4,NULL,6,2026,39.00,14.00,30,0.00,10.00,93.00,'',0,'2026-06-05 15:26:15','2026-06-05 15:26:15'),(2,1,NULL,6,2026,34.00,20.00,30,0.00,0.00,84.00,'',0,'2026-06-05 15:26:51','2026-06-05 15:26:51'),(3,3,NULL,6,2026,40.00,16.00,30,0.00,0.00,86.00,'',0,'2026-06-05 16:01:01','2026-06-05 16:01:01'),(4,5,NULL,6,2026,38.00,20.00,30,0.00,0.00,88.00,'',0,'2026-06-05 16:01:36','2026-06-05 16:01:36');
/*!40000 ALTER TABLE `employee_performance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_photos`
--

DROP TABLE IF EXISTS `employee_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `employee_photos_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_photos`
--

LOCK TABLES `employee_photos` WRITE;
/*!40000 ALTER TABLE `employee_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_reports`
--

DROP TABLE IF EXISTS `expense_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `total_expenses` decimal(10,2) DEFAULT NULL,
  `feed_expenses` decimal(10,2) DEFAULT NULL,
  `veterinary_expenses` decimal(10,2) DEFAULT NULL,
  `salary_expenses` decimal(10,2) DEFAULT NULL,
  `other_expenses` decimal(10,2) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_reports`
--

LOCK TABLES `expense_reports` WRITE;
/*!40000 ALTER TABLE `expense_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `expense_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_date` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,'2026-03-08','Salary','musharaad',10000.00,'mobile',NULL),(2,'2026-06-16','Transport','transportation',2000.00,'cash',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_consumption`
--

DROP TABLE IF EXISTS `feed_consumption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed_consumption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `feed_type_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  KEY `feed_type_id` (`feed_type_id`),
  CONSTRAINT `feed_consumption_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`),
  CONSTRAINT `feed_consumption_ibfk_2` FOREIGN KEY (`feed_type_id`) REFERENCES `feed_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_consumption`
--

LOCK TABLES `feed_consumption` WRITE;
/*!40000 ALTER TABLE `feed_consumption` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_consumption` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_plans`
--

DROP TABLE IF EXISTS `feed_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `camel_type` varchar(100) DEFAULT NULL,
  `daily_quantity` decimal(10,2) DEFAULT NULL,
  `feed_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feed_type_id` (`feed_type_id`),
  CONSTRAINT `feed_plans_ibfk_1` FOREIGN KEY (`feed_type_id`) REFERENCES `feed_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_plans`
--

LOCK TABLES `feed_plans` WRITE;
/*!40000 ALTER TABLE `feed_plans` DISABLE KEYS */;
INSERT INTO `feed_plans` VALUES (1,'dheemaal','waxa loogu tala galay in ay kor uqaado naaxidda xoolaha','Adult Male',10.00,3,'2026-04-05 08:44:15',1);
/*!40000 ALTER TABLE `feed_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_purchases`
--

DROP TABLE IF EXISTS `feed_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed_purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `feed_type_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('paid','unpaid','partial') DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `feed_type_id` (`feed_type_id`),
  CONSTRAINT `feed_purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `feed_suppliers` (`id`),
  CONSTRAINT `feed_purchases_ibfk_2` FOREIGN KEY (`feed_type_id`) REFERENCES `feed_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_purchases`
--

LOCK TABLES `feed_purchases` WRITE;
/*!40000 ALTER TABLE `feed_purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_suppliers`
--

DROP TABLE IF EXISTS `feed_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_suppliers`
--

LOCK TABLES `feed_suppliers` WRITE;
/*!40000 ALTER TABLE `feed_suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_types`
--

DROP TABLE IF EXISTS `feed_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `price_per_unit` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_types`
--

LOCK TABLES `feed_types` WRITE;
/*!40000 ALTER TABLE `feed_types` DISABLE KEYS */;
INSERT INTO `feed_types` VALUES (1,'Hay','Forage','kg',5.00,1),(2,'Grain Mix','Concentrate','kg',15.00,1),(3,'Mineral Blocks','Supplement','piece',25.00,1),(4,'Fresh Fodder','Green Feed','kg',3.00,1);
/*!40000 ALTER TABLE `feed_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fertility_reports`
--

DROP TABLE IF EXISTS `fertility_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fertility_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `fertility_score` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recommended_for_breeding` tinyint(1) DEFAULT 0,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  CONSTRAINT `fertility_reports_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fertility_reports`
--

LOCK TABLES `fertility_reports` WRITE;
/*!40000 ALTER TABLE `fertility_reports` DISABLE KEYS */;
INSERT INTO `fertility_reports` VALUES (1,1,'2026-03-05',5,'waa hal wanaagsan',1,1),(2,1,'2026-06-11',4,'',1,NULL);
/*!40000 ALTER TABLE `fertility_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `financial_reports`
--

DROP TABLE IF EXISTS `financial_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_income` decimal(10,2) DEFAULT NULL,
  `total_expenses` decimal(10,2) DEFAULT NULL,
  `net_profit` decimal(10,2) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_reports`
--

LOCK TABLES `financial_reports` WRITE;
/*!40000 ALTER TABLE `financial_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `financial_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `health_records`
--

DROP TABLE IF EXISTS `health_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `next_visit` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `health_records`
--

LOCK TABLES `health_records` WRITE;
/*!40000 ALTER TABLE `health_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `health_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_date` datetime DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('paid','unpaid','overdue') DEFAULT 'unpaid',
  `pdf_path` varchar(255) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,'INV-20260304-2332',3,1,'2026-03-04 10:46:49','2026-03-11',12.00,0.60,2.00,14.60,'unpaid',NULL,NULL,NULL),(2,'INV-20260304-1259',4,2,'2026-03-04 10:57:18','2026-03-11',15.00,0.75,2.00,17.75,'unpaid',NULL,NULL,NULL),(3,'INV-20260305-4687',5,1,'2026-03-05 10:03:57','2026-03-12',48.00,2.40,2.00,52.40,'unpaid',NULL,NULL,NULL),(4,'INV-20260307-3063',8,1,'2026-03-07 10:46:03','2026-03-14',8.00,0.40,2.00,10.40,'unpaid',NULL,NULL,NULL),(5,'INV-20260307-3721',9,1,'2026-03-07 12:01:32','2026-03-14',8.00,0.40,2.00,10.40,'unpaid',NULL,NULL,NULL),(6,'INV-20260308-1445',10,1,'2026-03-08 10:37:41','2026-03-15',12.00,0.60,2.00,14.60,'paid',NULL,NULL,NULL),(7,'INV-20260308-9802',11,1,'2026-03-08 11:42:35','2026-03-15',30.00,1.50,2.00,33.50,'unpaid',NULL,NULL,NULL),(8,'INV-20260309-7638',12,1,'2026-03-09 11:45:51','2026-03-16',15.00,0.75,2.00,17.75,'unpaid',NULL,NULL,NULL),(9,'INV-20260315-7390',13,1,'2026-03-15 12:07:05','2026-03-22',15.00,0.75,2.00,17.75,'unpaid',NULL,NULL,NULL),(10,'INV-20260326-3463',14,1,'2026-03-26 19:21:56','2026-04-02',12.00,0.60,2.00,14.60,'unpaid',NULL,NULL,NULL),(11,'INV-20260403-8453',15,1,'2026-04-03 16:57:47','2026-04-10',3.00,0.15,2.00,5.15,'unpaid',NULL,NULL,NULL),(12,'INV-20260403-5736',16,1,'2026-04-03 18:06:46','2026-04-10',12.00,0.60,2.00,14.60,'unpaid',NULL,NULL,NULL),(13,'INV-20260427-2978',17,4,'2026-04-27 12:45:35','2026-05-04',15.00,0.75,2.00,17.75,'unpaid',NULL,NULL,NULL),(14,'INV-20260519-1998',18,5,'2026-05-19 11:14:55','2026-05-26',12.00,0.60,2.00,14.60,'unpaid',NULL,NULL,NULL),(15,'INV-20260528-1675',19,1,'2026-05-28 11:46:23','2026-06-04',3.00,0.15,2.00,5.15,'unpaid',NULL,NULL,NULL),(16,'INV-20260609-3230',20,8,'2026-06-09 10:25:55','2026-06-16',8.00,0.40,2.00,10.40,'unpaid',NULL,NULL,NULL);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed') DEFAULT 'success',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_history`
--

LOCK TABLES `login_history` WRITE;
/*!40000 ALTER TABLE `login_history` DISABLE KEYS */;
INSERT INTO `login_history` VALUES (1,1,'2026-02-24 08:11:31','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(2,3,'2026-02-24 08:12:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(3,6,'2026-02-24 08:29:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(4,7,'2026-02-25 07:00:59','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(5,1,'2026-02-25 07:04:44','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(6,4,'2026-02-25 08:02:46','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(7,7,'2026-03-02 06:58:27','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(8,7,'2026-03-02 07:26:40','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(9,1,'2026-03-02 08:24:18','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(10,7,'2026-03-03 06:23:52','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(11,3,'2026-03-03 06:24:45','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(12,7,'2026-03-03 07:09:57','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(13,1,'2026-03-03 07:10:17','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(14,1,'2026-03-03 09:14:50','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(15,1,'2026-03-03 09:17:05','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(16,1,'2026-03-04 07:09:06','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(17,7,'2026-03-04 07:46:38','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(18,1,'2026-03-04 07:48:15','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(19,1,'2026-03-04 08:00:46','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(20,7,'2026-03-05 07:03:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(21,1,'2026-03-05 07:05:26','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(22,7,'2026-03-07 07:45:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(23,1,'2026-03-07 07:46:40','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(24,7,'2026-03-07 09:01:16','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(25,1,'2026-03-08 06:15:29','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(26,7,'2026-03-08 07:37:27','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(27,1,'2026-03-08 07:39:39','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(28,7,'2026-03-08 08:42:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(29,1,'2026-03-08 08:44:01','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(30,1,'2026-03-09 07:51:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(31,7,'2026-03-09 08:45:42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(32,1,'2026-03-09 08:46:37','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(33,1,'2026-03-14 17:18:04','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(34,7,'2026-03-15 09:06:55','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(35,1,'2026-03-15 09:08:17','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(36,7,'2026-03-16 07:59:16','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','success'),(37,1,'2026-03-18 08:09:55','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(38,1,'2026-03-19 07:40:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(39,1,'2026-03-20 14:48:20','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(40,1,'2026-03-26 14:50:56','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(41,7,'2026-03-26 16:21:29','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(42,1,'2026-03-26 16:23:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(43,1,'2026-03-27 14:16:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','success'),(44,1,'2026-04-02 08:44:33','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(45,7,'2026-04-03 13:57:04','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(46,1,'2026-04-03 14:01:05','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(47,7,'2026-04-03 15:06:41','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(48,1,'2026-04-03 15:07:18','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(49,1,'2026-04-05 08:36:59','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','success'),(50,1,'2026-04-05 16:34:20','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','success'),(51,1,'2026-04-05 16:40:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','success'),(52,1,'2026-04-08 09:13:14','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','success'),(53,1,'2026-04-22 14:25:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','failed'),(54,1,'2026-04-22 14:25:50','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(55,1,'2026-04-24 16:02:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(56,10,'2026-04-24 16:13:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(57,1,'2026-04-24 16:52:55','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(58,10,'2026-04-26 07:39:53','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(59,1,'2026-04-27 06:09:39','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(60,10,'2026-04-27 09:47:13','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(61,10,'2026-05-01 13:59:45','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','success'),(62,10,'2026-05-02 08:47:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','success'),(63,10,'2026-05-15 16:57:19','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(64,10,'2026-05-18 14:02:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(65,10,'2026-05-18 14:15:17','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(66,10,'2026-05-18 14:19:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(67,10,'2026-05-18 14:27:40','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','failed'),(68,1,'2026-05-18 14:30:02','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(69,1,'2026-05-18 14:38:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(70,1,'2026-05-18 14:48:54','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(71,10,'2026-05-18 14:59:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(72,3,'2026-05-18 15:01:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(73,10,'2026-05-18 15:12:22','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','failed'),(74,10,'2026-05-18 15:13:54','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','failed'),(75,1,'2026-05-18 15:16:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(76,10,'2026-05-18 15:16:17','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(77,3,'2026-05-18 15:18:37','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(78,3,'2026-05-18 15:22:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','success'),(79,10,'2026-05-18 15:24:11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(80,4,'2026-05-18 15:24:58','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(81,10,'2026-05-19 08:17:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(82,10,'2026-05-19 14:32:53','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(83,5,'2026-05-19 14:34:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(84,10,'2026-05-19 14:37:11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(85,10,'2026-05-20 08:07:44','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(86,124,'2026-05-20 08:11:19','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','failed'),(87,10,'2026-05-20 08:11:46','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','failed'),(88,10,'2026-05-20 08:11:59','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(89,124,'2026-05-20 08:13:42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(90,10,'2026-05-20 08:37:19','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(91,10,'2026-05-20 16:25:59','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(92,10,'2026-05-20 16:37:52','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(93,7,'2026-05-28 08:46:00','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(94,10,'2026-05-28 08:49:02','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(95,7,'2026-05-28 08:51:46','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','failed'),(96,6,'2026-05-28 08:52:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(97,7,'2026-05-28 08:52:37','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(98,10,'2026-05-28 08:53:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(99,10,'2026-06-05 14:50:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(100,6,'2026-06-05 17:52:07','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(101,10,'2026-06-05 17:53:57','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','success'),(102,10,'2026-06-07 08:00:39','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','success'),(103,6,'2026-06-08 10:27:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','success'),(104,10,'2026-06-08 10:47:00','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','success'),(105,1,'2026-06-09 06:39:54','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(106,10,'2026-06-09 07:27:05','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','failed'),(107,10,'2026-06-09 07:27:41','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(108,908,'2026-06-09 07:29:51','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(109,10,'2026-06-09 07:30:42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(110,965,'2026-06-09 07:39:33','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(111,10,'2026-06-09 07:45:52','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(112,10,'2026-06-11 07:42:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(113,10,'2026-06-14 08:40:27','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(114,10,'2026-06-16 08:46:11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(115,10,'2026-06-16 09:03:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','success'),(116,10,'2026-06-17 07:19:31','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(117,10,'2026-06-20 07:02:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success'),(118,10,'2026-06-20 07:07:24','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','success');
/*!40000 ALTER TABLE `login_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_mode`
--

DROP TABLE IF EXISTS `maintenance_mode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_enabled` tinyint(1) DEFAULT 0,
  `message` text DEFAULT NULL,
  `allowed_ips` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `maintenance_mode_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_mode`
--

LOCK TABLES `maintenance_mode` WRITE;
/*!40000 ALTER TABLE `maintenance_mode` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_mode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_batches`
--

DROP TABLE IF EXISTS `milk_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(50) DEFAULT NULL,
  `collection_date` date DEFAULT NULL,
  `total_quantity` decimal(10,2) DEFAULT NULL,
  `processing_status` enum('pending','processed','rejected') DEFAULT 'pending',
  `quality_approved` tinyint(1) DEFAULT 0,
  `pasteurized` tinyint(1) DEFAULT 0,
  `pasteurization_date` datetime DEFAULT NULL,
  `pasteurized_by` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_number` (`batch_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_batches`
--

LOCK TABLES `milk_batches` WRITE;
/*!40000 ALTER TABLE `milk_batches` DISABLE KEYS */;
INSERT INTO `milk_batches` VALUES (1,'BATCH-20260405-381','2026-04-05',0.00,'processed',1,1,'2026-06-15 11:28:55',10,'2026-04-10',NULL);
/*!40000 ALTER TABLE `milk_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_collection`
--

DROP TABLE IF EXISTS `milk_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `collection_date` date DEFAULT NULL,
  `shift` enum('morning','afternoon','evening') DEFAULT NULL,
  `quantity_liters` decimal(10,2) DEFAULT NULL,
  `quality_grade` enum('A','B','C','rejected') DEFAULT NULL,
  `collector_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  KEY `collector_id` (`collector_id`),
  CONSTRAINT `milk_collection_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`),
  CONSTRAINT `milk_collection_ibfk_2` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_collection`
--

LOCK TABLES `milk_collection` WRITE;
/*!40000 ALTER TABLE `milk_collection` DISABLE KEYS */;
INSERT INTO `milk_collection` VALUES (1,1,'2026-03-03','morning',0.90,'A',1,NULL),(2,2,'2026-04-05','morning',2.00,'rejected',1,NULL),(3,1,'2026-05-21','morning',1.50,'A',10,NULL),(4,2,'2026-06-05','morning',3.50,'A',10,NULL),(5,4,'2026-06-15','morning',4.00,'A',10,NULL);
/*!40000 ALTER TABLE `milk_collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_processing`
--

DROP TABLE IF EXISTS `milk_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_processing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) DEFAULT NULL,
  `process_date` datetime DEFAULT NULL,
  `process_type` varchar(50) DEFAULT NULL,
  `quantity_processed` decimal(10,2) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `milk_processing_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `milk_batches` (`id`),
  CONSTRAINT `milk_processing_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_processing`
--

LOCK TABLES `milk_processing` WRITE;
/*!40000 ALTER TABLE `milk_processing` DISABLE KEYS */;
/*!40000 ALTER TABLE `milk_processing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_production_reports`
--

DROP TABLE IF EXISTS `milk_production_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_production_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `total_morning` decimal(10,2) DEFAULT NULL,
  `total_afternoon` decimal(10,2) DEFAULT NULL,
  `total_evening` decimal(10,2) DEFAULT NULL,
  `total_daily` decimal(10,2) DEFAULT NULL,
  `average_quality` varchar(10) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_production_reports`
--

LOCK TABLES `milk_production_reports` WRITE;
/*!40000 ALTER TABLE `milk_production_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `milk_production_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_reports`
--

DROP TABLE IF EXISTS `milk_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `total_collection` decimal(10,2) DEFAULT NULL,
  `grade_a_quantity` decimal(10,2) DEFAULT NULL,
  `grade_b_quantity` decimal(10,2) DEFAULT NULL,
  `grade_c_quantity` decimal(10,2) DEFAULT NULL,
  `rejected_quantity` decimal(10,2) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_reports`
--

LOCK TABLES `milk_reports` WRITE;
/*!40000 ALTER TABLE `milk_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `milk_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','danger') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'New Order','New order #ORD-20260225-8140 has been placed. Total: $21.425','info',1,'2026-02-25 07:01:13',NULL),(2,1,'New Order Received','New order #ORD-20260304-1555 has been placed. Total: $14.60','info',1,'2026-03-04 07:46:51',NULL),(3,2,'New Order Received','New order #ORD-20260304-1555 has been placed. Total: $14.60','info',0,'2026-03-04 07:46:51',NULL),(4,5,'New Order Received','New order #ORD-20260304-1555 has been placed. Total: $14.60','info',0,'2026-03-04 07:46:51',NULL),(5,6,'New Order Received','New order #ORD-20260304-1555 has been placed. Total: $14.60','info',0,'2026-03-04 07:46:52',NULL),(6,1,'New Order Received','New order #ORD-20260304-2746 has been placed. Total: $17.75','info',1,'2026-03-04 07:57:19',NULL),(7,2,'New Order Received','New order #ORD-20260304-2746 has been placed. Total: $17.75','info',0,'2026-03-04 07:57:19',NULL),(8,5,'New Order Received','New order #ORD-20260304-2746 has been placed. Total: $17.75','info',0,'2026-03-04 07:57:19',NULL),(9,6,'New Order Received','New order #ORD-20260304-2746 has been placed. Total: $17.75','info',0,'2026-03-04 07:57:19',NULL),(10,1,'New Order Received','New order #ORD-20260305-6985 has been placed. Total: $52.40','info',1,'2026-03-05 07:03:58',NULL),(11,2,'New Order Received','New order #ORD-20260305-6985 has been placed. Total: $52.40','info',0,'2026-03-05 07:03:58',NULL),(12,5,'New Order Received','New order #ORD-20260305-6985 has been placed. Total: $52.40','info',0,'2026-03-05 07:03:58',NULL),(13,6,'New Order Received','New order #ORD-20260305-6985 has been placed. Total: $52.40','info',0,'2026-03-05 07:03:58',NULL),(14,1,'New Order Received','New order #ORD-20260307-7769 has been placed. Total: $10.40','info',1,'2026-03-07 07:46:04',NULL),(15,2,'New Order Received','New order #ORD-20260307-7769 has been placed. Total: $10.40','info',0,'2026-03-07 07:46:04',NULL),(16,5,'New Order Received','New order #ORD-20260307-7769 has been placed. Total: $10.40','info',0,'2026-03-07 07:46:04',NULL),(17,6,'New Order Received','New order #ORD-20260307-7769 has been placed. Total: $10.40','info',0,'2026-03-07 07:46:05',NULL),(18,7,'Order #ORD-20260225-8140 On the Way','Your order is out for delivery and will arrive soon!','info',0,'2026-03-07 08:50:49',NULL),(19,1,'New Order Received','New order #ORD-20260307-4159 has been placed. Total: $10.40','info',1,'2026-03-07 09:01:33',NULL),(20,2,'New Order Received','New order #ORD-20260307-4159 has been placed. Total: $10.40','info',0,'2026-03-07 09:01:33',NULL),(21,5,'New Order Received','New order #ORD-20260307-4159 has been placed. Total: $10.40','info',0,'2026-03-07 09:01:33',NULL),(22,6,'New Order Received','New order #ORD-20260307-4159 has been placed. Total: $10.40','info',0,'2026-03-07 09:01:34',NULL),(23,1,'New Order Received','New order #ORD-20260308-9524 has been placed. Total: $14.60','info',1,'2026-03-08 07:37:42',NULL),(24,2,'New Order Received','New order #ORD-20260308-9524 has been placed. Total: $14.60','info',0,'2026-03-08 07:37:43',NULL),(25,5,'New Order Received','New order #ORD-20260308-9524 has been placed. Total: $14.60','info',0,'2026-03-08 07:37:43',NULL),(26,6,'New Order Received','New order #ORD-20260308-9524 has been placed. Total: $14.60','info',0,'2026-03-08 07:37:43',NULL),(27,7,'Payment Verified','Your payment for order #ORD-20260308-9524 has been verified.','success',0,'2026-03-08 07:40:00',NULL),(28,1,'New Order Received','New order #ORD-20260308-3099 has been placed. Total: $33.50','info',1,'2026-03-08 08:42:36',NULL),(29,2,'New Order Received','New order #ORD-20260308-3099 has been placed. Total: $33.50','info',0,'2026-03-08 08:42:36',NULL),(30,5,'New Order Received','New order #ORD-20260308-3099 has been placed. Total: $33.50','info',0,'2026-03-08 08:42:36',NULL),(31,6,'New Order Received','New order #ORD-20260308-3099 has been placed. Total: $33.50','info',0,'2026-03-08 08:42:36',NULL),(32,1,'New Order Received','New order #ORD-20260309-9302 has been placed. Total: $17.75','info',1,'2026-03-09 08:45:51',NULL),(33,2,'New Order Received','New order #ORD-20260309-9302 has been placed. Total: $17.75','info',0,'2026-03-09 08:45:52',NULL),(34,5,'New Order Received','New order #ORD-20260309-9302 has been placed. Total: $17.75','info',0,'2026-03-09 08:45:52',NULL),(35,6,'New Order Received','New order #ORD-20260309-9302 has been placed. Total: $17.75','info',0,'2026-03-09 08:45:52',NULL),(36,7,'New Login','You have successfully logged in.','success',0,'2026-03-15 09:06:55',NULL),(37,1,'New Order Received','New order #ORD-20260315-6422 has been placed. Total: $17.75','info',1,'2026-03-15 09:07:06',NULL),(38,2,'New Order Received','New order #ORD-20260315-6422 has been placed. Total: $17.75','info',0,'2026-03-15 09:07:06',NULL),(39,5,'New Order Received','New order #ORD-20260315-6422 has been placed. Total: $17.75','info',0,'2026-03-15 09:07:07',NULL),(40,6,'New Order Received','New order #ORD-20260315-6422 has been placed. Total: $17.75','info',0,'2026-03-15 09:07:07',NULL),(41,7,'Order Placed Successfully','Your order #ORD-20260315-6422 has been placed successfully. Total: $17.75','success',0,'2026-03-15 09:07:07',NULL),(42,7,'Logged Out','You have been logged out.','info',0,'2026-03-15 09:08:03',NULL),(43,1,'New Login','You have successfully logged in.','success',1,'2026-03-15 09:08:17',NULL),(44,1,'New Order Received','New order #ORD-20260326-9697 has been placed. Total: $14.60','info',1,'2026-03-26 16:21:56',NULL),(45,2,'New Order Received','New order #ORD-20260326-9697 has been placed. Total: $14.60','info',0,'2026-03-26 16:21:56',NULL),(46,5,'New Order Received','New order #ORD-20260326-9697 has been placed. Total: $14.60','info',0,'2026-03-26 16:21:56',NULL),(47,6,'New Order Received','New order #ORD-20260326-9697 has been placed. Total: $14.60','info',0,'2026-03-26 16:21:56',NULL),(48,NULL,'Sms Alert','SMS to +252 61 1111111: effd','',0,'2026-03-27 15:22:57',NULL),(49,1,'New Order Received','New order #ORD-20260403-4501 has been placed. Total: $5.15','info',1,'2026-04-03 13:57:53',NULL),(50,2,'New Order Received','New order #ORD-20260403-4501 has been placed. Total: $5.15','info',0,'2026-04-03 13:57:54',NULL),(51,5,'New Order Received','New order #ORD-20260403-4501 has been placed. Total: $5.15','info',0,'2026-04-03 13:57:54',NULL),(52,6,'New Order Received','New order #ORD-20260403-4501 has been placed. Total: $5.15','info',0,'2026-04-03 13:57:55',NULL),(53,1,'New Order Received','New order #ORD-20260403-4865 has been placed automatically. Total: $14.60','info',1,'2026-04-03 15:06:46',NULL),(54,2,'New Order Received','New order #ORD-20260403-4865 has been placed automatically. Total: $14.60','info',0,'2026-04-03 15:06:46',NULL),(55,5,'New Order Received','New order #ORD-20260403-4865 has been placed automatically. Total: $14.60','info',0,'2026-04-03 15:06:47',NULL),(56,6,'New Order Received','New order #ORD-20260403-4865 has been placed automatically. Total: $14.60','info',0,'2026-04-03 15:06:47',NULL),(57,1,'New Order Received','New order #ORD-20260427-7277 has been placed. Total: $17.75','info',0,'2026-04-27 09:45:35',NULL),(58,2,'New Order Received','New order #ORD-20260427-7277 has been placed. Total: $17.75','info',0,'2026-04-27 09:45:35',NULL),(59,5,'New Order Received','New order #ORD-20260427-7277 has been placed. Total: $17.75','info',0,'2026-04-27 09:45:36',NULL),(60,6,'New Order Received','New order #ORD-20260427-7277 has been placed. Total: $17.75','info',0,'2026-04-27 09:45:36',NULL),(61,124,'Welcome to the system','Your account has been created successfully.','success',0,'2026-05-02 14:44:35',NULL),(62,1,'New Order Received','New order #ORD-20260519-9904 has been placed. Total: $14.60','info',0,'2026-05-19 08:14:56',NULL),(63,2,'New Order Received','New order #ORD-20260519-9904 has been placed. Total: $14.60','info',0,'2026-05-19 08:14:56',NULL),(64,5,'New Order Received','New order #ORD-20260519-9904 has been placed. Total: $14.60','info',0,'2026-05-19 08:14:56',NULL),(65,6,'New Order Received','New order #ORD-20260519-9904 has been placed. Total: $14.60','info',0,'2026-05-19 08:14:56',NULL),(66,124,'New Order Received','New order #ORD-20260519-9904 has been placed. Total: $14.60','info',0,'2026-05-19 08:14:56',NULL),(67,1,'New Order Received','New order #ORD-20260528-4972 has been placed. Total: $5.15','info',0,'2026-05-28 08:46:24',NULL),(68,2,'New Order Received','New order #ORD-20260528-4972 has been placed. Total: $5.15','info',0,'2026-05-28 08:46:24',NULL),(69,5,'New Order Received','New order #ORD-20260528-4972 has been placed. Total: $5.15','info',0,'2026-05-28 08:46:24',NULL),(70,6,'New Order Received','New order #ORD-20260528-4972 has been placed. Total: $5.15','info',0,'2026-05-28 08:46:24',NULL),(71,124,'New Order Received','New order #ORD-20260528-4972 has been placed. Total: $5.15','info',0,'2026-05-28 08:46:24',NULL),(72,7,'Delivery Assigned','Your order has been assigned for delivery.','info',0,'2026-06-05 17:49:28',NULL),(73,4,'Salary Paid','Your salary for July 2026 of $2000.00 has been paid.','success',0,'2026-06-08 08:03:08',NULL),(74,7,'Order Accepted','Your order has been accepted and is being processed.','success',0,'2026-06-08 09:22:13',NULL),(75,269,'Order Status Updated','Your order has been confirmed.','info',0,'2026-06-08 09:42:55',NULL),(76,1,'New Order Received','New order #ORD-20260609-6483 has been placed. Total: $10.40','info',0,'2026-06-09 07:25:56',NULL),(77,2,'New Order Received','New order #ORD-20260609-6483 has been placed. Total: $10.40','info',0,'2026-06-09 07:25:56',NULL),(78,5,'New Order Received','New order #ORD-20260609-6483 has been placed. Total: $10.40','info',0,'2026-06-09 07:25:56',NULL),(79,6,'New Order Received','New order #ORD-20260609-6483 has been placed. Total: $10.40','info',0,'2026-06-09 07:25:56',NULL),(80,124,'New Order Received','New order #ORD-20260609-6483 has been placed. Total: $10.40','info',0,'2026-06-09 07:25:56',NULL),(81,908,'Order Accepted','Your order has been accepted and is being processed.','success',0,'2026-06-09 07:28:34',NULL),(82,908,'Delivery Assigned','Your order has been assigned for delivery.','info',0,'2026-06-09 07:28:42',NULL),(83,965,'Welcome to the system','Your account has been created successfully.','success',0,'2026-06-09 07:39:03',NULL),(84,1,'New Staff Added','New staff member 06 has been added.','info',0,'2026-06-09 07:41:27',4),(85,2,'New Staff Added','New staff member 06 has been added.','info',0,'2026-06-09 07:41:27',4),(86,5,'New Staff Added','New staff member 06 has been added.','info',0,'2026-06-09 07:41:27',4),(87,6,'New Staff Added','New staff member 06 has been added.','info',0,'2026-06-09 07:41:27',4),(88,124,'New Staff Added','New staff member 06 has been added.','info',0,'2026-06-09 07:41:27',4),(89,965,'New Staff Added','New staff member 06 has been added.','info',1,'2026-06-09 07:41:27',4),(90,1,'New Camel Registered','New camel 11 has been registered.','info',0,'2026-06-09 07:44:54',4),(91,2,'New Camel Registered','New camel 11 has been registered.','info',0,'2026-06-09 07:44:54',4),(92,5,'New Camel Registered','New camel 11 has been registered.','info',0,'2026-06-09 07:44:54',4),(93,6,'New Camel Registered','New camel 11 has been registered.','info',0,'2026-06-09 07:44:54',4),(94,124,'New Camel Registered','New camel 11 has been registered.','info',0,'2026-06-09 07:44:55',4),(95,965,'New Camel Registered','New camel 11 has been registered.','info',1,'2026-06-09 07:44:55',4),(96,2,'Salary Paid','Your salary for October 2026 of $4000.00 has been paid.','success',0,'2026-06-14 07:54:35',NULL),(97,1,'Batch Pasteurized','Batch has been pasteurized and is ready for packaging.','info',0,'2026-06-15 08:28:56',NULL),(98,2,'Batch Pasteurized','Batch has been pasteurized and is ready for packaging.','info',0,'2026-06-15 08:28:56',NULL),(99,5,'Batch Pasteurized','Batch has been pasteurized and is ready for packaging.','info',0,'2026-06-15 08:28:56',NULL),(100,6,'Batch Pasteurized','Batch has been pasteurized and is ready for packaging.','info',0,'2026-06-15 08:28:56',NULL),(101,124,'Batch Pasteurized','Batch has been pasteurized and is ready for packaging.','info',0,'2026-06-15 08:28:56',NULL),(102,965,'Batch Pasteurized','Batch has been pasteurized and is ready for packaging.','info',0,'2026-06-15 08:28:56',NULL),(103,7,'Order Status Updated','Your order has been cancelled.','info',0,'2026-06-16 13:23:33',NULL),(104,269,'Order Status Updated','Your order has been delivered.','success',0,'2026-06-19 13:39:10',NULL),(105,96,'Order Accepted','Your order has been accepted and is being processed.','success',0,'2026-06-21 06:58:28',NULL),(106,96,'Order Accepted','Your order has been accepted and is being processed.','success',0,'2026-06-21 06:58:33',NULL),(107,96,'Order Accepted','Your order has been accepted and is being processed.','success',0,'2026-06-21 06:58:43',NULL);
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,3.00,3.50),(2,1,4,1.00,8.00),(3,2,1,10.00,0.00),(4,3,5,1.00,12.00),(5,4,7,1.00,15.00),(6,5,5,4.00,12.00),(7,6,2,2.00,0.00),(8,7,1,10.00,0.00),(9,8,4,1.00,8.00),(10,9,4,1.00,8.00),(11,10,5,1.00,12.00),(12,11,5,1.00,12.00),(13,11,7,1.00,15.00),(14,11,8,1.00,3.00),(15,12,7,1.00,15.00),(16,13,7,1.00,15.00),(17,14,5,1.00,12.00),(18,15,8,1.00,3.00),(19,16,5,1.00,12.00),(20,17,7,1.00,15.00),(21,18,5,1.00,12.00),(22,19,8,1.00,3.00),(23,20,4,1.00,8.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
INSERT INTO `order_status_history` VALUES (1,3,'pending',7,'2026-03-04 07:46:51','Order placed',NULL),(2,4,'pending',8,'2026-03-04 07:57:18','Order placed',NULL),(3,5,'pending',7,'2026-03-05 07:03:58','Order placed',NULL),(4,8,'pending',7,'2026-03-07 07:46:04','Order placed',NULL),(5,9,'pending',7,'2026-03-07 09:01:33','Order placed',NULL),(6,10,'pending',7,'2026-03-08 07:37:42','Order placed',NULL),(7,11,'pending',7,'2026-03-08 08:42:36','Order placed',NULL),(8,12,'pending',7,'2026-03-09 08:45:51','Order placed',NULL),(9,13,'pending',7,'2026-03-15 09:07:06','Order placed',NULL),(10,14,'pending',7,'2026-03-26 16:21:56','Order placed',NULL),(11,15,'pending',7,'2026-04-03 13:57:51','Order placed',NULL),(12,16,'pending',7,'2026-04-03 15:06:46','Order created via add to cart',NULL),(13,17,'pending',96,'2026-04-27 09:45:35','Order placed',NULL),(14,18,'pending',269,'2026-05-19 08:14:56','Order placed',NULL),(15,19,'pending',7,'2026-05-28 08:46:23','Order placed',NULL),(16,19,'processing',10,'2026-06-05 17:49:28','Delivery assigned to driver',NULL),(17,13,'confirmed',10,'2026-06-08 09:22:13','Order accepted by staff',NULL),(18,18,'confirmed',10,'2026-06-08 09:42:55','',NULL),(19,20,'pending',908,'2026-06-09 07:25:56','Order placed',NULL),(20,20,'confirmed',10,'2026-06-09 07:28:33','Order accepted by staff',NULL),(21,20,'processing',10,'2026-06-09 07:28:42','Delivery assigned to driver',NULL),(22,11,'cancelled',10,'2026-06-16 13:23:31','',NULL),(23,18,'delivered',10,'2026-06-19 13:39:08','',NULL),(24,17,'confirmed',10,'2026-06-21 06:58:24','Order accepted by staff',NULL),(25,17,'confirmed',10,'2026-06-21 06:58:31','Order accepted by staff',NULL),(26,17,'confirmed',10,'2026-06-21 06:58:41','Order accepted by staff',NULL);
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('paid','unpaid','partial') DEFAULT 'unpaid',
  `delivery_address` text DEFAULT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `payment_verified_by` int(11) DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `packed_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'ORD-20260225-8140',1,'2026-02-25 10:01:12',21.43,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'ORD-20260225-7797',1,'2026-02-25 11:03:26',0.00,'pending','unpaid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'ORD-20260304-1555',1,'2026-03-04 10:46:47',14.60,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'ORD-20260304-2746',2,'2026-03-04 10:57:16',17.75,'pending','unpaid','harawo','4882200','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'ORD-20260305-6985',1,'2026-03-05 10:03:56',52.40,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'ORD-20260305-5120',1,'2026-03-05 11:04:40',0.00,'pending','unpaid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,'ORD-20260307-3675',2,'2026-03-07 10:43:54',0.00,'pending','unpaid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,'ORD-20260307-7769',1,'2026-03-07 10:46:02',10.40,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,'ORD-20260307-4159',1,'2026-03-07 12:01:32',10.40,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,'ORD-20260308-9524',1,'2026-03-08 10:37:41',14.60,'pending','paid','jigjiga yar','1234567','',1,'2026-03-08 10:40:00',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,'ORD-20260308-3099',1,'2026-03-08 11:42:34',33.50,'cancelled','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-16 16:23:30'),(12,'ORD-20260309-9302',1,'2026-03-09 11:45:50',17.75,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(13,'ORD-20260315-6422',1,'2026-03-15 12:07:04',17.75,'confirmed','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,'2026-06-08 12:22:13',NULL,NULL,NULL,NULL,NULL),(14,'ORD-20260326-9697',1,'2026-03-26 19:21:55',14.60,'pending','unpaid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(15,'ORD-20260403-4501',1,'2026-04-03 16:57:45',5.15,'pending','unpaid','jigjiga yar','1234567','ka soo horjeedka maansoor',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(16,'ORD-20260403-4865',1,'2026-04-03 18:06:46',14.60,'pending','paid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(17,'ORD-20260427-7277',4,'2026-04-27 12:45:35',17.75,'confirmed','paid','12m','2526355555','',NULL,NULL,NULL,'2026-06-21 09:58:40',NULL,NULL,NULL,NULL,NULL),(18,'ORD-20260519-9904',5,'2026-05-19 11:14:53',14.60,'delivered','paid','harawo','4264312','',NULL,NULL,NULL,'2026-06-08 12:42:54',NULL,NULL,NULL,'2026-06-19 16:39:08',NULL),(19,'ORD-20260528-4972',1,'2026-05-28 11:46:22',5.15,'delivered','paid','jigjiga yar','1234567','',NULL,NULL,NULL,NULL,'2026-06-05 20:49:28',NULL,NULL,'2026-06-08 12:47:08',NULL),(20,'ORD-20260609-6483',8,'2026-06-09 10:25:54',10.40,'delivered','paid','jigjiga yar','3000000','',NULL,NULL,NULL,'2026-06-09 10:28:33','2026-06-09 10:28:42',NULL,NULL,'2026-06-18 12:52:26',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_method` enum('cash','mobile_money','bank') DEFAULT NULL,
  `status` enum('completed','pending','failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,21.43,'2026-02-25 10:01:13','cash','pending',NULL,NULL,NULL,NULL),(2,3,14.60,'2026-03-04 10:46:51','cash','pending',NULL,NULL,NULL,NULL),(3,4,17.75,'2026-03-04 10:57:18','mobile_money','pending',NULL,NULL,NULL,NULL),(4,5,52.40,'2026-03-05 10:03:58','mobile_money','pending',NULL,NULL,NULL,NULL),(5,8,10.40,'2026-03-07 10:46:04','cash','pending',NULL,NULL,NULL,NULL),(6,9,10.40,'2026-03-07 12:01:33','mobile_money','pending',NULL,NULL,NULL,NULL),(7,10,14.60,'2026-03-08 10:37:42','cash','completed',NULL,1,'2026-03-08 10:40:00',NULL),(8,11,33.50,'2026-03-08 11:42:36','cash','pending',NULL,NULL,NULL,NULL),(9,12,17.75,'2026-03-09 11:45:51','cash','pending',NULL,NULL,NULL,NULL),(10,13,17.75,'2026-03-15 12:07:06','cash','pending',NULL,NULL,NULL,NULL),(11,14,14.60,'2026-03-26 19:21:56','bank','pending',NULL,NULL,NULL,NULL),(12,15,5.15,'2026-04-03 16:57:51','mobile_money','pending',NULL,NULL,NULL,NULL),(13,16,14.60,'2026-04-03 18:06:46','','completed',NULL,NULL,NULL,NULL),(14,17,17.75,'2026-04-27 12:45:35','cash','completed',NULL,NULL,NULL,NULL),(15,18,14.60,'2026-05-19 11:14:56','mobile_money','completed',NULL,NULL,NULL,NULL),(16,19,5.15,'2026-05-28 11:46:23','cash','completed',NULL,NULL,NULL,NULL),(17,20,10.40,'2026-06-09 10:25:56','cash','completed',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payslips`
--

DROP TABLE IF EXISTS `payslips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payslips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `total_deductions` decimal(10,2) DEFAULT NULL,
  `total_bonuses` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) DEFAULT NULL,
  `generated_date` date DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_payslip` (`staff_id`,`month`,`year`),
  CONSTRAINT `payslips_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payslips`
--

LOCK TABLES `payslips` WRITE;
/*!40000 ALTER TABLE `payslips` DISABLE KEYS */;
INSERT INTO `payslips` VALUES (1,1,'March',2026,5000.00,0.00,0.00,5000.00,'2026-03-26','payslip_1_2026_March.pdf',1),(2,2,'March',2026,4000.00,0.00,0.00,4000.00,'2026-03-26','payslip_2_2026_March.pdf',1),(3,4,'March',2026,2000.00,0.00,0.00,2000.00,'2026-03-26','payslip_4_2026_March.pdf',1),(4,5,'March',2026,1500.00,0.00,0.00,1500.00,'2026-03-26','payslip_5_2026_March.pdf',1),(5,6,'March',2026,250.00,0.00,0.00,250.00,'2026-03-26','payslip_6_2026_March.pdf',1),(7,1,'May',2026,5000.00,0.00,0.00,5000.00,'2026-05-20','payslip_1_2026_May.pdf',1),(8,2,'May',2026,4000.00,0.00,0.00,4000.00,'2026-05-20','payslip_2_2026_May.pdf',1),(9,3,'May',2026,3500.00,0.00,0.00,3500.00,'2026-05-20','payslip_3_2026_May.pdf',1),(10,4,'May',2026,2000.00,0.00,0.00,2000.00,'2026-05-20','payslip_4_2026_May.pdf',1),(11,5,'May',2026,1500.00,0.00,0.00,1500.00,'2026-05-20','payslip_5_2026_May.pdf',1),(12,6,'May',2026,250.00,0.00,0.00,250.00,'2026-05-20','payslip_6_2026_May.pdf',1),(13,8,'May',2026,5000.00,0.00,0.00,5000.00,'2026-05-20','payslip_8_2026_May.pdf',1),(14,6,'June',2026,250.00,20.00,0.00,230.00,'2026-05-20','payslip_6_2026_June.pdf',1),(16,1,'June',2026,5000.00,0.00,0.00,5000.00,'2026-06-05','payslip_1_2026_June.pdf',1),(17,2,'June',2026,4000.00,0.00,0.00,4000.00,'2026-06-05','payslip_2_2026_June.pdf',1),(18,3,'June',2026,3500.00,0.00,0.00,3500.00,'2026-06-05','payslip_3_2026_June.pdf',1),(19,4,'June',2026,2000.00,0.00,0.00,2000.00,'2026-06-05','payslip_4_2026_June.pdf',1),(20,5,'June',2026,1500.00,0.00,0.00,1500.00,'2026-06-05','payslip_5_2026_June.pdf',1),(21,8,'June',2026,5000.00,0.00,0.00,5000.00,'2026-06-05','payslip_8_2026_June.pdf',1),(22,1,'February',2026,5000.00,0.00,0.00,5000.00,'2026-06-08','payslip_1_2026_February.pdf',1),(23,2,'February',2026,4000.00,0.00,0.00,4000.00,'2026-06-08','payslip_2_2026_February.pdf',1),(24,3,'February',2026,3500.00,0.00,0.00,3500.00,'2026-06-08','payslip_3_2026_February.pdf',1),(25,4,'February',2026,2000.00,0.00,0.00,2000.00,'2026-06-08','payslip_4_2026_February.pdf',1),(26,5,'February',2026,1500.00,0.00,0.00,1500.00,'2026-06-08','payslip_5_2026_February.pdf',1),(27,6,'February',2026,250.00,0.00,0.00,250.00,'2026-06-08','payslip_6_2026_February.pdf',1),(28,8,'February',2026,5000.00,0.00,0.00,5000.00,'2026-06-08','payslip_8_2026_February.pdf',1),(29,4,'July',2026,2000.00,0.00,0.00,2000.00,'2026-06-08','payslip_4_2026_July.pdf',1),(30,6,'July',2026,250.00,250.00,0.00,0.00,'2026-06-08','payslip_6_2026_July.pdf',1),(31,6,'January',2026,250.00,0.00,0.00,250.00,'2026-06-08','payslip_6_2026_January.pdf',1),(32,2,'October',2026,4000.00,0.00,0.00,4000.00,'2026-06-14','payslip_2_2026_October.pdf',NULL),(33,11,'January',2026,100.00,0.00,20.00,120.00,'2026-06-17','payslip_11_2026_January.pdf',NULL);
/*!40000 ALTER TABLE `payslips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_walkin_items`
--

DROP TABLE IF EXISTS `pos_walkin_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pos_walkin_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  CONSTRAINT `pos_walkin_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `pos_walkin_sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_walkin_items`
--

LOCK TABLES `pos_walkin_items` WRITE;
/*!40000 ALTER TABLE `pos_walkin_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `pos_walkin_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_walkin_sales`
--

DROP TABLE IF EXISTS `pos_walkin_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pos_walkin_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_number` varchar(50) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `cashier_id` int(11) DEFAULT NULL COMMENT 'user_id of cashier',
  `customer_name` varchar(200) DEFAULT 'Walk-in Customer',
  `customer_phone` varchar(30) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_number` (`sale_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_walkin_sales`
--

LOCK TABLES `pos_walkin_sales` WRITE;
/*!40000 ALTER TABLE `pos_walkin_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `pos_walkin_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pregnancy_tracking`
--

DROP TABLE IF EXISTS `pregnancy_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pregnancy_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `breeding_record_id` int(11) DEFAULT NULL,
  `check_date` date DEFAULT NULL,
  `trimester` int(11) DEFAULT NULL,
  `health_status` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  KEY `breeding_record_id` (`breeding_record_id`),
  CONSTRAINT `pregnancy_tracking_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`),
  CONSTRAINT `pregnancy_tracking_ibfk_2` FOREIGN KEY (`breeding_record_id`) REFERENCES `breeding_records` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pregnancy_tracking`
--

LOCK TABLES `pregnancy_tracking` WRITE;
/*!40000 ALTER TABLE `pregnancy_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `pregnancy_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `image` varchar(255) DEFAULT 'default-product.jpg',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Fresh Camel Milk','Milk',3.50,87.00,'liter','active','default-product.jpg','Fresh raw camel milk straight from our farm. Rich in nutrients and vitamins.','2026-02-24 08:11:05',1),(2,'Organic Camel Milk','Milk',4.50,50.00,'liter','active','default-product.jpg','Certified organic camel milk from free-range camels.','2026-02-24 08:11:05',1),(3,'Camel Milk Yogurt','Dairy',5.00,60.00,'piece','active','default-product.jpg','Creamy yogurt made from fresh camel milk. Probiotic-rich.','2026-02-24 08:11:05',1),(4,'Camel Cheese','Dairy',8.00,36.00,'piece','active','default-product.jpg','Aged camel milk cheese with unique flavor.','2026-02-24 08:11:05',1),(5,'Camel Ghee','Dairy',12.00,15.00,'kg','active','default-product.jpg','Pure clarified butter from camel milk. Ideal for cooking.','2026-02-24 08:11:06',1),(6,'Flavored Camel Milk','Milk',4.00,75.00,'liter','active','default-product.jpg','Camel milk with natural flavors - chocolate and strawberry available.','2026-02-24 08:11:06',1),(7,'Camel Milk Powder','Dairy',15.00,25.00,'kg','active','default-product.jpg','Premium camel milk powder. Easy to store and transport.','2026-02-24 08:11:06',1),(8,'Camel Milk Soap','Beauty',3.00,197.00,'piece','active','default-product.jpg','Natural soap made with camel milk. Good for skin.','2026-02-24 08:11:06',1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quick_sale_items`
--

DROP TABLE IF EXISTS `quick_sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quick_sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quick_sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quick_sale_id` (`quick_sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `quick_sale_items_ibfk_1` FOREIGN KEY (`quick_sale_id`) REFERENCES `quick_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quick_sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quick_sale_items`
--

LOCK TABLES `quick_sale_items` WRITE;
/*!40000 ALTER TABLE `quick_sale_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `quick_sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quick_sales`
--

DROP TABLE IF EXISTS `quick_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quick_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_number` varchar(50) DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `sale_date` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('cash','mobile_money','bank','card') DEFAULT NULL,
  `payment_status` enum('paid','unpaid') DEFAULT 'paid',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_number` (`sale_number`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `quick_sales_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quick_sales`
--

LOCK TABLES `quick_sales` WRITE;
/*!40000 ALTER TABLE `quick_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `quick_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_bonuses`
--

DROP TABLE IF EXISTS `salary_bonuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_bonuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `bonus_type` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `salary_bonuses_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_bonuses`
--

LOCK TABLES `salary_bonuses` WRITE;
/*!40000 ALTER TABLE `salary_bonuses` DISABLE KEYS */;
INSERT INTO `salary_bonuses` VALUES (1,11,'January',2026,'time keeping',20.00,'',NULL);
/*!40000 ALTER TABLE `salary_bonuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_deductions`
--

DROP TABLE IF EXISTS `salary_deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_deductions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `deduction_type` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `salary_deductions_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_deductions`
--

LOCK TABLES `salary_deductions` WRITE;
/*!40000 ALTER TABLE `salary_deductions` DISABLE KEYS */;
INSERT INTO `salary_deductions` VALUES (1,6,'June',2026,'guno',20.00,'iibsi',1),(2,6,'July',2026,'lacag qaldan tay',250.00,'',1);
/*!40000 ALTER TABLE `salary_deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_payments`
--

DROP TABLE IF EXISTS `salary_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('paid','pending') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `salary_payments_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_payments`
--

LOCK TABLES `salary_payments` WRITE;
/*!40000 ALTER TABLE `salary_payments` DISABLE KEYS */;
INSERT INTO `salary_payments` VALUES (1,3,'March',2026,20000.00,'2026-03-07','paid',NULL,NULL,NULL,NULL),(2,1,'March',2026,5000.00,'2026-03-26','paid',NULL,NULL,NULL,NULL),(3,2,'March',2026,4000.00,'2026-03-26','paid',NULL,NULL,NULL,NULL),(4,4,'March',2026,2000.00,'2026-03-26','paid',NULL,NULL,NULL,NULL),(5,5,'March',2026,1500.00,'2026-03-26','paid',NULL,NULL,NULL,NULL),(6,6,'March',2026,250.00,'2026-03-26','paid',NULL,NULL,NULL,NULL),(8,1,'April',2026,5000.00,'2026-04-27','paid',NULL,NULL,NULL,NULL),(9,1,'May',2026,5000.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(10,2,'May',2026,4000.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(11,3,'May',2026,3500.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(12,4,'May',2026,2000.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(13,5,'May',2026,1500.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(14,6,'May',2026,250.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(15,8,'May',2026,5000.00,'2026-05-20','paid',NULL,NULL,NULL,NULL),(16,6,'June',2026,1000.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(17,1,'June',2026,5000.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(18,2,'June',2026,4000.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(19,3,'June',2026,3500.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(20,4,'June',2026,2000.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(21,5,'June',2026,1500.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(22,8,'June',2026,5000.00,'2026-06-05','paid',NULL,NULL,NULL,NULL),(23,1,'February',2026,5000.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(24,2,'February',2026,4000.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(25,3,'February',2026,3500.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(26,4,'February',2026,2000.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(27,5,'February',2026,1500.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(28,6,'February',2026,250.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(29,8,'February',2026,5000.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(30,4,'July',2026,2000.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(31,6,'January',2026,250.00,'2026-06-08','paid',NULL,NULL,NULL,NULL),(32,2,'October',2026,4000.00,'2026-06-14','paid',NULL,NULL,NULL,NULL),(33,11,'January',2026,100.00,'2026-06-17','paid',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `salary_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  `payment_method` enum('cash','mobile_money','bank','credit') DEFAULT 'cash',
  `payment_status` enum('paid','pending','partial') DEFAULT 'paid',
  `sold_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id` (`customer_id`),
  KEY `sold_by` (`sold_by`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,1,2,10.00,2.00,20.00,'2026-03-15 12:09:44','mobile_money','paid',1,'');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_commissions`
--

DROP TABLE IF EXISTS `sales_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `commission_amount` decimal(10,2) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `calculated_date` date DEFAULT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `paid_date` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `sales_commissions_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`),
  CONSTRAINT `sales_commissions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_commissions`
--

LOCK TABLES `sales_commissions` WRITE;
/*!40000 ALTER TABLE `sales_commissions` DISABLE KEYS */;
INSERT INTO `sales_commissions` VALUES (1,11,20,0.52,5.00,'2026-06-16','paid','2026-06-16',NULL);
/*!40000 ALTER TABLE `sales_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_reports`
--

DROP TABLE IF EXISTS `sales_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `total_sales` decimal(10,2) DEFAULT NULL,
  `total_orders` int(11) DEFAULT NULL,
  `average_order_value` decimal(10,2) DEFAULT NULL,
  `top_product_id` int(11) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`),
  KEY `top_product_id` (`top_product_id`),
  CONSTRAINT `sales_reports_ibfk_1` FOREIGN KEY (`top_product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_reports`
--

LOCK TABLES `sales_reports` WRITE;
/*!40000 ALTER TABLE `sales_reports` DISABLE KEYS */;
INSERT INTO `sales_reports` VALUES (1,'2026-03-08',0.00,0,0.00,NULL,'{\"start_date\":\"2026-02-01\",\"end_date\":\"2026-03-08\",\"total_sales\":0,\"total_orders\":0,\"avg_order\":0,\"top_product_id\":null}'),(2,'2026-04-02',0.00,0,0.00,NULL,'{\"start_date\":\"2026-03-03\",\"end_date\":\"2026-04-02\",\"sales_by_day\":[]}'),(3,'2026-04-03',0.00,0,0.00,NULL,'{\"start_date\":\"2026-03-04\",\"end_date\":\"2026-04-03\",\"sales_by_day\":[]}'),(4,'2026-05-28',0.00,0,0.00,NULL,'{\"start_date\":\"2026-04-28\",\"end_date\":\"2026-05-28\",\"sales_by_day\":[]}'),(5,'2026-06-08',5.15,1,5.15,8,'{\"start_date\":\"2026-05-09\",\"end_date\":\"2026-06-08\",\"sales_by_day\":[]}'),(6,'2026-06-14',0.00,0,0.00,NULL,'{\"start_date\":\"2026-06-14\",\"end_date\":\"2026-06-14\",\"sales_by_day\":[]}'),(7,'2026-06-16',5.15,1,5.15,8,'{\"start_date\":\"2026-05-17\",\"end_date\":\"2026-06-16\",\"sales_by_day\":[]}');
/*!40000 ALTER TABLE `sales_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_targets`
--

DROP TABLE IF EXISTS `sales_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `target_amount` decimal(10,2) DEFAULT NULL,
  `achieved_amount` decimal(10,2) DEFAULT 0.00,
  `staff_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `sales_targets_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_targets`
--

LOCK TABLES `sales_targets` WRITE;
/*!40000 ALTER TABLE `sales_targets` DISABLE KEYS */;
INSERT INTO `sales_targets` VALUES (1,2026,3,100.00,0.00,1,NULL),(2,2026,1,1000.00,0.00,NULL,NULL),(3,2026,1,0.01,0.00,NULL,NULL);
/*!40000 ALTER TABLE `sales_targets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_notifications`
--

DROP TABLE IF EXISTS `sms_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_notifications`
--

LOCK TABLES `sms_notifications` WRITE;
/*!40000 ALTER TABLE `sms_notifications` DISABLE KEYS */;
INSERT INTO `sms_notifications` VALUES (1,'1234567','Your order #ORD-20260308-9524 has been placed successfully. Total: $14.60','pending',NULL,'2026-03-08 07:37:43'),(2,'1234567','Your payment for order #ORD-20260308-9524 has been verified. Thank you!','pending',NULL,'2026-03-08 07:40:00');
/*!40000 ALTER TABLE `sms_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT 'default-staff.jpg',
  `profile_picture` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,1,'ADMIN001',1,'System Administrator','2023-01-01',5000.00,'+252 61 1111111','admin@mahuran.com','Main Office','active','default-staff.jpg',NULL,1),(2,2,'MGR001',1,'Farm Manager','2023-01-01',4000.00,'+252 61 2222222','manager@mahuran.com','Main Office','active','default-staff.jpg',NULL,1),(3,3,'VET001',2,'Senior Veterinarian','2023-01-01',3500.00,'+252 61 3333333','vet@mahuran.com','Vet Clinic','active','default-staff.jpg',NULL,1),(4,4,'DRV001',5,'Delivery Driver','2023-01-01',2000.00,'+252 61 4444444','driver@mahuran.com','Delivery Department','active','default-staff.jpg',NULL,1),(5,5,'STF001',3,'Farm Worker','2023-01-01',1500.00,'+252 61 5555555','staff@mahuran.com','Farm Area','active','default-staff.jpg',NULL,1),(6,NULL,'001',5,'sales','2026-02-24',250.00,'634255363','ma4832101@gmail.com','jigjiga yar','active','default-staff.jpg',NULL,1),(8,NULL,'EMP999',1,'Tester','2026-05-02',5000.00,'123456789','test@test.com','123 Test St','active','default-staff.jpg',NULL,1),(11,NULL,'06',19,'sales','2026-05-02',100.00,'434000','a@gmail.com','','active','default-staff.jpg',NULL,4);
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_performance_reports`
--

DROP TABLE IF EXISTS `staff_performance_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_performance_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `attendance_rate` decimal(5,2) DEFAULT NULL,
  `tasks_completed` int(11) DEFAULT NULL,
  `performance_score` decimal(5,2) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `staff_performance_reports_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_performance_reports`
--

LOCK TABLES `staff_performance_reports` WRITE;
/*!40000 ALTER TABLE `staff_performance_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_performance_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_invoices`
--

DROP TABLE IF EXISTS `subscription_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('paid','unpaid','overdue') DEFAULT 'unpaid',
  `payment_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `subscription_id` (`subscription_id`),
  CONSTRAINT `subscription_invoices_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `customer_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_invoices`
--

LOCK TABLES `subscription_invoices` WRITE;
/*!40000 ALTER TABLE `subscription_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_pause_history`
--

DROP TABLE IF EXISTS `subscription_pause_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_pause_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) DEFAULT NULL,
  `pause_start` date DEFAULT NULL,
  `pause_end` date DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subscription_id` (`subscription_id`),
  CONSTRAINT `subscription_pause_history_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `customer_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_pause_history`
--

LOCK TABLES `subscription_pause_history` WRITE;
/*!40000 ALTER TABLE `subscription_pause_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_pause_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `plan_type` enum('daily','weekly','monthly') DEFAULT NULL,
  `quantity_per_day` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'Daily Basic','daily',1.00,30.00,'active',NULL),(2,'Daily Family','daily',3.00,80.00,'active',NULL),(3,'Weekly Saver','weekly',2.00,50.00,'active',NULL),(4,'Monthly Premium','monthly',2.00,55.00,'active',NULL);
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `ticket_number` varchar(50) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `customer_id` (`customer_id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_activity_logs`
--

DROP TABLE IF EXISTS `system_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_activity_logs`
--

LOCK TABLES `system_activity_logs` WRITE;
/*!40000 ALTER TABLE `system_activity_logs` DISABLE KEYS */;
INSERT INTO `system_activity_logs` VALUES (1,7,'login','User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','2026-03-08 07:37:27',NULL),(2,7,'order_status_change','Order #10 status changed to pending by m@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','2026-03-08 07:37:42',NULL),(3,7,'logout','User logged out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','2026-03-08 07:39:23',NULL),(4,1,'login','User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0','2026-03-08 07:39:39',NULL),(5,1,'logout','User logged out','::1',NULL,'2026-03-09 08:44:58',NULL),(6,1,'logout','User logged out','::1',NULL,'2026-03-09 08:44:59',NULL),(7,7,'login','User logged in','::1',NULL,'2026-03-09 08:45:42',NULL),(8,7,'logout','User logged out','::1',NULL,'2026-03-09 08:46:25',NULL),(9,1,'login','User logged in','::1',NULL,'2026-03-09 08:46:37',NULL),(10,1,'update_delivery_status','Updated delivery ID: 3 to status: assigned','::1',NULL,'2026-03-26 16:04:22',NULL),(11,1,'add_sales_target','Added sales target for 1/2026: 1000','::1',NULL,'2026-03-26 16:06:47',NULL),(12,1,'add_sales_target','Added sales target for 1/2026: 0.01','::1',NULL,'2026-03-26 16:06:55',NULL),(13,1,'mark_attendance','Marked attendance for staff ID: 7 - present','::1',NULL,'2026-03-26 16:07:54',NULL),(14,1,'run_payroll','Payroll run for March 2026','::1',NULL,'2026-03-26 16:12:14',NULL),(15,1,'run_payroll','Payroll run for March 2026','::1',NULL,'2026-03-26 16:12:57',NULL),(16,1,'update_staff','Updated staff ID: 1','::1',NULL,'2026-03-27 14:19:06',NULL),(17,1,'add_delivery_zone','Added delivery zone: qudhac dheer','::1',NULL,'2026-03-27 15:09:02',NULL),(18,1,'send_sms','Sent SMS notification','::1',NULL,'2026-03-27 15:22:57',NULL),(19,1,'delete_staff','Deleted staff ID: 7','::1',NULL,'2026-04-02 08:47:54',NULL),(20,1,'generate_sales_report','Generated sales report for 2026-03-03 to 2026-04-02','::1',NULL,'2026-04-02 08:49:03',NULL),(21,1,'run_payroll','Payroll run for March 2026','::1',NULL,'2026-04-02 08:49:52',NULL),(22,1,'generate_sales_report','Generated sales report for 2026-03-04 to 2026-04-03','::1',NULL,'2026-04-03 14:05:47',NULL),(23,1,'add_temperature_log','Recorded temperature at Cooler A: 2°C','::1',NULL,'2026-04-03 14:09:01',NULL),(24,1,'add_temperature_log','Recorded temperature at Cooler A: 1.3°C','::1',NULL,'2026-04-03 14:09:15',NULL),(25,1,'add_milk_collection','Added milk collection - Camel: 2, Qty: 2L','::1',NULL,'2026-04-05 08:38:12',NULL),(26,1,'create_batch','Created batch: BATCH-20260405-381','::1',NULL,'2026-04-05 08:40:03',NULL),(27,1,'add_feed_plan','Added feed plan: dheemaal','::1',NULL,'2026-04-05 08:44:15',NULL),(28,1,'add_department','Added department: sales','::1',NULL,'2026-04-27 06:13:59',NULL),(29,10,'add_department','Added department: IT','::1',NULL,'2026-05-02 08:48:58',NULL),(30,10,'add_user','Added user: muse','::1',NULL,'2026-05-02 14:44:34',NULL),(31,10,'add_department','Added department: IT','::1',NULL,'2026-05-02 14:49:11',NULL),(32,1,'add_branch','Added branch: mahuran 2','::1',NULL,'2026-05-18 14:33:56',NULL),(33,1,'delete_branch','Deleted branch ID: 2','::1',NULL,'2026-05-18 14:39:15',NULL),(34,10,'update_user','Updated user ID: 5','::1',NULL,'2026-05-19 14:33:49',NULL),(35,10,'add_branch','Added branch: mahuran2','::1',NULL,'2026-05-20 08:09:29',NULL),(36,10,'update_user','Updated user ID: 124','::1',NULL,'2026-05-20 08:13:18',NULL),(37,124,'add_department','Added department: SALES','::1',NULL,'2026-05-20 08:14:50',NULL),(38,124,'add_department','Added department: SALES','::1',NULL,'2026-05-20 08:31:09',NULL),(39,124,'add_department','Added department: SALES','::1',NULL,'2026-05-20 08:31:36',NULL),(40,124,'add_department','Added department: SALES','::1',NULL,'2026-05-20 08:35:06',NULL),(41,124,'add_department','Added department: SALES','::1',NULL,'2026-05-20 08:36:48',NULL),(42,10,'run_payroll','Payroll run for May 2026','::1',NULL,'2026-05-20 09:46:27',NULL),(43,10,'add_deduction','Added deduction for staff ID: 6 - guno: 20','::1',NULL,'2026-05-20 10:40:39',NULL),(44,10,'mark_attendance','Marked attendance for staff ID: 2 - late','::1',NULL,'2026-05-21 07:56:55',NULL),(45,10,'add_milk_collection','Added milk collection - Camel: 1, Qty: 1.5L','::1',NULL,'2026-05-21 08:01:48',NULL),(46,10,'generate_sales_report','Generated sales report for 2026-04-28 to 2026-05-28','::1',NULL,'2026-05-28 17:20:52',NULL),(47,10,'update_delivery_status','Updated delivery ID: 3 to status: assigned','::1',NULL,'2026-05-28 17:21:33',NULL),(48,10,'give_award','Gave award \'Employee of the Month (June)\' to staff ID 4','::1',NULL,'2026-06-05 15:27:16',NULL),(49,10,'add_milk_collection','Added milk collection - Camel: 2, Qty: 3.5L','::1',NULL,'2026-06-05 15:56:14',NULL),(50,10,'mark_attendance','Marked attendance for staff ID: 1 - present','::1',NULL,'2026-06-05 15:58:47',NULL),(51,10,'mark_attendance','Marked attendance for staff ID: 2 - present','::1',NULL,'2026-06-05 15:59:05',NULL),(52,10,'mark_attendance','Marked attendance for staff ID: 3 - present','::1',NULL,'2026-06-05 15:59:11',NULL),(53,10,'mark_attendance','Marked attendance for staff ID: 4 - present','::1',NULL,'2026-06-05 15:59:18',NULL),(54,10,'mark_attendance','Marked attendance for staff ID: 5 - present','::1',NULL,'2026-06-05 15:59:26',NULL),(55,10,'mark_attendance','Marked attendance for staff ID: 6 - present','::1',NULL,'2026-06-05 15:59:33',NULL),(56,10,'mark_attendance','Marked attendance for staff ID: 8 - present','::1',NULL,'2026-06-05 15:59:41',NULL),(57,10,'assign_delivery','Assigned delivery for order ID: 19 to driver ID: 4','::1',NULL,'2026-06-05 17:49:28',NULL),(58,6,'add_department','Added department: SALES','::1',NULL,'2026-06-05 17:53:25',NULL),(59,10,'pay_salary','Paid salary for staff ID: 6 - June 2026: 1000','::1',NULL,'2026-06-05 18:03:35',NULL),(60,10,'run_payroll','Payroll run for June 2026','::1',NULL,'2026-06-05 18:04:50',NULL),(61,10,'run_payroll','Payroll run for February 2026','::1',NULL,'2026-06-08 07:57:45',NULL),(62,10,'pay_salary','Paid salary for staff ID: 4 - July 2026: 2000.00','::1',NULL,'2026-06-08 08:03:07',NULL),(63,10,'add_deduction','Added deduction for staff ID: 6 - lacag qaldan tay: 250','::1',NULL,'2026-06-08 08:04:33',NULL),(64,10,'pay_salary','Paid salary for staff ID: 6 - January 2026: 250.00','::1',NULL,'2026-06-08 08:05:12',NULL),(65,10,'accept_order','Accepted order ID: 13','::1',NULL,'2026-06-08 09:22:13',NULL),(66,10,'update_order_status','Updated order ID: 18 to status: confirmed','::1',NULL,'2026-06-08 09:42:55',NULL),(67,10,'update_delivery_status','Updated delivery ID: 15 to status: in_transit','::1',NULL,'2026-06-08 09:47:00',NULL),(68,10,'update_delivery_status','Updated delivery ID: 15 to status: delivered','::1',NULL,'2026-06-08 09:47:08',NULL),(69,10,'generate_sales_report','Generated sales report for 2026-05-09 to 2026-06-08','::1',NULL,'2026-06-08 09:55:57',NULL),(70,1,'add_department','Added department: hrn','::1',NULL,'2026-06-09 06:41:17',NULL),(71,10,'accept_order','Accepted order ID: 20','::1',NULL,'2026-06-09 07:28:34',NULL),(72,10,'assign_delivery','Assigned delivery for order ID: 20 to driver ID: 4','::1',NULL,'2026-06-09 07:28:42',NULL),(73,10,'update_delivery_status','Updated delivery ID: 16 to status: in_transit','::1',NULL,'2026-06-09 07:29:13',NULL),(74,10,'delete_branch','Deleted branch ID: 3','::1',NULL,'2026-06-09 07:36:28',NULL),(75,10,'add_branch','Added branch: mahuran2','::1',NULL,'2026-06-09 07:37:17',NULL),(76,10,'add_user','Added user: yusuf','::1',NULL,'2026-06-09 07:39:03',NULL),(77,965,'add_department','Added department: hrm','::1',NULL,'2026-06-09 07:40:20',4),(78,965,'add_staff','Added staff: 06','::1',NULL,'2026-06-09 07:41:27',4),(79,965,'add_camel','Added camel: 11','::1',NULL,'2026-06-09 07:44:54',4),(80,10,'mark_attendance','Marked attendance for staff ID: 1 - present','::1',NULL,'2026-06-11 07:43:32',NULL),(81,10,'add_disease','Added disease record for camel ID: 1 - caal','::1',NULL,'2026-06-11 07:45:58',NULL),(82,10,'add_fertility_report','Added fertility report for camel ID: 1 - Score: 4','::1',NULL,'2026-06-11 07:46:51',NULL),(83,10,'pay_salary','Paid salary for staff ID: 2 - October 2026: 4000.00','::1',NULL,'2026-06-14 07:54:32',NULL),(84,10,'generate_sales_report','Generated sales report for 2026-06-14 to 2026-06-14','::1',NULL,'2026-06-14 08:10:16',NULL),(85,10,'approve_quality','Approved quality for batch ID: 1','::1',NULL,'2026-06-15 08:28:40',NULL),(86,10,'pasteurize_batch','Pasteurized batch ID: 1','::1',NULL,'2026-06-15 08:28:56',NULL),(87,10,'add_milk_collection','Added milk collection - Camel: 4, Qty: 4L','::1',NULL,'2026-06-15 08:29:34',NULL),(88,10,'generate_sales_report','Generated sales report for 2026-05-17 to 2026-06-16','::1',NULL,'2026-06-16 08:58:45',NULL),(89,10,'add_expense','Added expense: Transport - 2000','::1',NULL,'2026-06-16 08:59:56',NULL),(90,10,'add_sales_commission','Added sales commission for staff ID: 11, order ID: 20','::1',NULL,'2026-06-16 13:22:17',NULL),(91,10,'update_order_status','Updated order ID: 11 to status: cancelled','::1',NULL,'2026-06-16 13:23:33',NULL),(92,10,'add_bonus','Added bonus for staff ID: 11 - time keeping: 20','::1',NULL,'2026-06-17 08:02:31',NULL),(93,10,'pay_salary','Paid salary for staff ID: 11 - January 2026: 100.00','::1',NULL,'2026-06-17 08:02:58',NULL),(94,10,'add_branch','Added branch: mahuran3','::1',NULL,'2026-06-17 08:15:11',NULL),(95,10,'update_branch','Updated branch ID: 5','::1',NULL,'2026-06-17 08:15:43',NULL),(96,10,'delete_branch','Deleted branch ID: 4','::1',NULL,'2026-06-17 08:17:42',NULL),(97,10,'update_delivery_status','Updated delivery ID: 16 to status: delivered','::1',NULL,'2026-06-18 09:52:26',NULL),(98,10,'update_order_status','Updated order ID: 18 to status: delivered','::1',NULL,'2026-06-19 13:39:09',NULL),(99,10,'accept_order','Accepted order ID: 17','::1',NULL,'2026-06-21 06:58:25',NULL),(100,10,'accept_order','Accepted order ID: 17','::1',NULL,'2026-06-21 06:58:32',NULL),(101,10,'accept_order','Accepted order ID: 17','::1',NULL,'2026-06-21 06:58:42',NULL);
/*!40000 ALTER TABLE `system_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_monitoring`
--

DROP TABLE IF EXISTS `system_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpu_usage` decimal(5,2) DEFAULT NULL,
  `memory_usage` decimal(5,2) DEFAULT NULL,
  `disk_usage` decimal(5,2) DEFAULT NULL,
  `active_users` int(11) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_monitoring`
--

LOCK TABLES `system_monitoring` WRITE;
/*!40000 ALTER TABLE `system_monitoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_monitoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'company_name','','Company Name'),(2,'company_email','','Company Email'),(3,'company_phone','','Company Phone'),(4,'currency','USD','Default Currency'),(5,'tax_rate','5','Tax Rate'),(6,'delivery_fee','2.00','Default Delivery Fee');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temperature_logs`
--

DROP TABLE IF EXISTS `temperature_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temperature_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(100) DEFAULT NULL,
  `log_date` datetime DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `temperature_logs_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temperature_logs`
--

LOCK TABLES `temperature_logs` WRITE;
/*!40000 ALTER TABLE `temperature_logs` DISABLE KEYS */;
INSERT INTO `temperature_logs` VALUES (1,'Cooler A','2026-04-03 17:09:00',2.00,1,''),(2,'Cooler A','2026-04-03 17:09:15',1.30,1,'2');
/*!40000 ALTER TABLE `temperature_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_replies`
--

DROP TABLE IF EXISTS `ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_replies`
--

LOCK TABLES `ticket_replies` WRITE;
/*!40000 ALTER TABLE `ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) DEFAULT NULL,
  `translation_key` varchar(255) DEFAULT NULL,
  `translation_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`language_id`,`translation_key`),
  CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('super_admin','admin','manager','staff','vet','driver','customer') DEFAULT 'customer',
  `status` enum('active','blocked','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default-user.jpg',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1299 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$rfVyGpSMd0mWra3JjQQkzOGrZuNMRAsQtLGqmxzXA50WIHYBf/qx6','admin@mahuran.com','System Administrator',NULL,'admin','active','2026-02-24 08:11:02','2026-06-09 06:39:54','default-user.jpg',NULL),(2,'manager','$2y$10$D3PDBYSi8tQ6y1NrcCrlB.Yzry2HYmUBD2daRrFNiRf/odKb/LZt.','manager@mahuran.com','Farm Manager',NULL,'manager','active','2026-02-24 08:11:02',NULL,'default-user.jpg',NULL),(3,'vet','$2y$10$u6me1z.TF7ftW7bv8N8cguWYMDvpwYzjx2XefAyqgdRuFlRUb39xO','vet@mahuran.com','Dr. Ahmed Hassan',NULL,'vet','active','2026-02-24 08:11:02','2026-05-18 15:22:49','default-user.jpg',NULL),(4,'driver','$2y$10$0mGuVaGIbl.A5kWSE9.f1.HRRk5jdSZvDprdlN9JwplMFZJdaXnvy','driver@mahuran.com','Ali Omar',NULL,'driver','active','2026-02-24 08:11:02','2026-05-18 15:24:57','default-user.jpg',NULL),(5,'staff','$2y$10$sH5BD6hprveyG/OwGdKGb.oauR/idU8o2P7p7q2OwGW4XjZzLXvHK','staff@mahuran.com','Farm Staff','','staff','active','2026-02-24 08:11:03','2026-05-19 14:34:28','default-user.jpg',NULL),(6,'mohamed','$2y$10$Q/sJ31sed9eoGdBcoAV8l.055esQDiJKPxBbWtNb45PHwQNur3eaO','mo@gmail.com','mohamed ahmed','634255363','admin','active','2026-02-24 08:28:32','2026-06-08 10:27:09','default-user.jpg',NULL),(7,'m@gmail.com','$2y$10$n2e9YMpEGJEWmJ698iTxl.0SkogcS1VzST3c2H6Usl6oRUB9X/cT2','m@gmail.com','kahin','1234567','customer','active','2026-02-24 09:04:26','2026-05-28 08:52:36','default-user.jpg',NULL),(8,'fa@gamil.com','$2y$10$5Z769bVgdWetdxFNC6lDvu9APUsnnhoR6jKLwq6VXlIQI1Bpn7HHO','fa@gamil.com','farah gedi warfaa','4882200','customer','active','2026-03-04 07:56:45',NULL,'default-user.jpg',NULL),(9,'hasancismaan2@gmail.com','$2y$10$r9eUY7C3PCm3gdGEHRqVi.ve4beWTO.lQJ5iBoE75znHdfsGYNBrC','hasancismaan2@gmail.com','iid ','77777','customer','active','2026-04-08 14:54:31',NULL,'default-user.jpg',NULL),(10,'superadmin','$2y$10$cAG41RHZ97XEjQ1ohYxI6umMzxVM6XzWsb4Pa.nmQfLFXTdSzyQL2','superadmin@mahuran.com','Global Super Administrator',NULL,'super_admin','active','2026-04-24 16:13:34','2026-06-20 07:07:23','default-user.jpg',NULL),(96,'mus@gmail.com','$2y$10$quD32OfURcB17VdCt7uxK.Hfi3gxwDeWNUdjK3XHd7ZZ1FwraQgsa','mus@gmail.com','mohamed','2526355555','customer','active','2026-04-27 09:45:23',NULL,'default-user.jpg',NULL),(124,'muse','$2y$10$G83ONMggh84fGaRrJIVVL.NZxHPuHLn5njJKBYc9n7R4RiukxCitK','ma4832101@gmail.com','muse farah','634526667','admin','active','2026-05-02 14:44:34','2026-05-20 08:13:42','default-user.jpg',NULL),(269,'ali@gmail.com','$2y$10$pDxgPm3R6qKCVrnYtjdbK.pv1QqB8ee81oOFFlTeoWpMtemho4FG6','ali@gmail.com','ali abdi','4264312','customer','active','2026-05-19 08:14:29',NULL,'default-user.jpg',NULL),(908,'3000000','$2y$10$e5WU2ULx27sHTfrVyYZKk.358UzUDrjJKPe4Wy/tGvt5CGWSGu7oC','','abdi gedi','3000000','customer','active','2026-06-09 07:25:24','2026-06-09 07:29:50','default-user.jpg',NULL),(965,'yusuf','$2y$10$boexvb2y/TGMOMJoKB/6iuLjPQzkSRLAssKDDDOQgOT0pinyJ6phO','y@gmail.com','yusuf abdi gedi','462732','admin','active','2026-06-09 07:39:01','2026-06-09 07:39:33','default-user.jpg',4);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vaccinations`
--

DROP TABLE IF EXISTS `vaccinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vaccinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `vaccine_name` varchar(200) DEFAULT NULL,
  `vaccination_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `administered_by` varchar(100) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  CONSTRAINT `vaccinations_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vaccinations`
--

LOCK TABLES `vaccinations` WRITE;
/*!40000 ALTER TABLE `vaccinations` DISABLE KEYS */;
/*!40000 ALTER TABLE `vaccinations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vet_visits`
--

DROP TABLE IF EXISTS `vet_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vet_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `camel_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camel_id` (`camel_id`),
  CONSTRAINT `vet_visits_ibfk_1` FOREIGN KEY (`camel_id`) REFERENCES `camels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vet_visits`
--

LOCK TABLES `vet_visits` WRITE;
/*!40000 ALTER TABLE `vet_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `vet_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_inventory`
--

DROP TABLE IF EXISTS `warehouse_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type` varchar(100) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `storage_location` varchar(100) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('in_stock','low_stock','expired','disposed') DEFAULT 'in_stock',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `warehouse_inventory_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `milk_batches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_inventory`
--

LOCK TABLES `warehouse_inventory` WRITE;
/*!40000 ALTER TABLE `warehouse_inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouse_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_notifications`
--

DROP TABLE IF EXISTS `whatsapp_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_notifications`
--

LOCK TABLES `whatsapp_notifications` WRITE;
/*!40000 ALTER TABLE `whatsapp_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_notifications` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-22 12:18:01
