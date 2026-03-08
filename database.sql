-- MySQL dump 10.13  Distrib 8.4.4, for macos15 (arm64)
--
-- Host: 127.0.0.1    Database: license_manager
-- ------------------------------------------------------
-- Server version	8.4.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activations`
--

DROP TABLE IF EXISTS `activations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activations_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activations`
--

LOCK TABLES `activations` WRITE;
/*!40000 ALTER TABLE `activations` DISABLE KEYS */;
INSERT INTO `activations` VALUES ('a13b453c-0f26-4345-a3ee-3e36c69b6e02','a13b453c-0c77-4e0b-978e-245efb84b296','C7mtBrrTGTdrVgulf7VkyO5dNsO2nwQB',1,'2026-03-05 18:09:27','2026-03-05 18:09:27','2026-03-05 18:09:27');
/*!40000 ALTER TABLE `activations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_notifications`
--

DROP TABLE IF EXISTS `admin_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_label` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `permission` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_notifications`
--

LOCK TABLES `admin_notifications` WRITE;
/*!40000 ALTER TABLE `admin_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard_widget_settings`
--

DROP TABLE IF EXISTS `dashboard_widget_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboard_widget_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb4_unicode_ci,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widget_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` tinyint unsigned NOT NULL DEFAULT '0',
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dashboard_widget_settings_user_id_index` (`user_id`),
  KEY `dashboard_widget_settings_widget_id_index` (`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_widget_settings`
--

LOCK TABLES `dashboard_widget_settings` WRITE;
/*!40000 ALTER TABLE `dashboard_widget_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboard_widget_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard_widgets`
--

DROP TABLE IF EXISTS `dashboard_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboard_widgets` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_widgets`
--

LOCK TABLES `dashboard_widgets` WRITE;
/*!40000 ALTER TABLE `dashboard_widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboard_widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_tokens` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_token_unique` (`token`),
  KEY `device_tokens_user_type_user_id_index` (`user_type`,`user_id`),
  KEY `device_tokens_platform_is_active_index` (`platform`,`is_active`),
  KEY `device_tokens_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_tokens`
--

LOCK TABLES `device_tokens` WRITE;
/*!40000 ALTER TABLE `device_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_activations`
--

DROP TABLE IF EXISTS `lm_activations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_activations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_reference_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lm_activations_license_code_is_active_index` (`license_code`,`is_active`),
  KEY `lm_activations_product_reference_id_is_active_index` (`product_reference_id`,`is_active`),
  KEY `lm_activations_product_reference_id_index` (`product_reference_id`),
  KEY `lm_activations_customer_id_index` (`customer_id`),
  KEY `lm_activations_license_code_index` (`license_code`),
  KEY `lm_activations_ip_address_index` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_activations`
--

LOCK TABLES `lm_activations` WRITE;
/*!40000 ALTER TABLE `lm_activations` DISABLE KEYS */;
INSERT INTO `lm_activations` VALUES ('a13b453e-78f2-4526-a07d-e95a95697d5d','BOTBLE-CMS','CUST-001','550e8400-e29b-41d4-a716-446655440001','https://example.com','192.168.1.100','2026-01-22 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7a4e-43b4-bd5f-b0c915dc3547','BOTBLE-CMS','CUST-001','550e8400-e29b-41d4-a716-446655440001','https://staging.example.com','192.168.1.101','2026-01-08 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7ba8-48cb-976d-16f50abf43bb','BOTBLE-CMS','CUST-002','550e8400-e29b-41d4-a716-446655440002','https://jane-site.com','10.0.0.50','2026-02-25 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7e0a-465b-b595-3ce0a8065b7c','FLEX-HOME','CUST-001','6ba7b810-9dad-11d1-80b4-00c04fd430c1','https://realestate.example.com','172.16.0.10','2025-12-26 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7f79-4d9b-a35e-d117569261dd','MARTFURY','CUST-004','6ba7b811-9dad-11d1-80b4-00c04fd430c2','https://shop1.alice.com','203.0.113.1','2026-03-02 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-803d-4c0e-bdf6-b6e039465b7b','MARTFURY','CUST-004','6ba7b811-9dad-11d1-80b4-00c04fd430c2','https://shop2.alice.com','203.0.113.2','2026-01-13 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-80e3-4bfe-bbec-b08cc1115138','MARTFURY','CUST-004','6ba7b811-9dad-11d1-80b4-00c04fd430c2','https://shop3.alice.com','203.0.113.3','2025-12-24 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-823e-4fff-b1bd-15d4f3e0eb10','BOTBLE-CMS','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11','https://demo-cms.botble.com','103.45.67.89','2026-02-08 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8389-4944-a45b-a6efb9032795','BOTBLE-CMS','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11','https://staging-cms.botble.com','103.45.67.90','2026-01-07 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8473-42c9-9b3d-64455905c1ff','BOTBLE-CMS','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11','https://dev-cms.botble.com','127.0.0.1','2026-02-20 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-86c2-4e79-ae87-3c4f607d9406','BOTBLE-CMS','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11','https://old-site.botble.com','103.45.67.88','2026-01-03 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,0,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-879c-463f-b29a-ad91cbe6e888','BOTBLE-CMS','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11','https://deprecated.botble.com','103.45.67.87','2025-12-06 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,0,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-892f-4f11-8e04-2ffb1cd70375','FLEX-HOME','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a12','https://realestate.botble.com','103.45.67.91','2026-01-16 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8a73-4d92-b902-0870875dc4e1','MARTFURY','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a13','https://shop.botble.com','103.45.67.92','2026-01-04 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8b21-4e80-a0c4-3561d7f1fe96','MARTFURY','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a13','https://marketplace.botble.com','103.45.67.93','2026-02-03 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8bf7-4929-93f9-f5c99d359570','MARTFURY','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a13','https://old-shop.botble.com','103.45.67.96','2026-01-14 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,0,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8d78-4798-907b-e084e849b9c0','SHOFY','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a14','https://shofy-demo.botble.com','103.45.67.94','2025-12-13 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8efb-4612-9835-840934de74a8','FARMART','CUST-DEMO','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a15','https://grocery.botble.com','103.45.67.95','2026-01-19 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-905f-4ae6-a55f-07bd40fde164','FARMART','CUST-002','f47ac10b-58cc-4372-a567-0e02b2c3d479','https://grocery.jane.com','198.51.100.50','2025-12-30 18:09:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,1,'2026-03-05 18:09:28','2026-03-05 18:09:28');
/*!40000 ALTER TABLE `lm_activations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_activity_logs`
--

DROP TABLE IF EXISTS `lm_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_activity_logs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lm_activity_logs_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_activity_logs`
--

LOCK TABLES `lm_activity_logs` WRITE;
/*!40000 ALTER TABLE `lm_activity_logs` DISABLE KEYS */;
INSERT INTO `lm_activity_logs` VALUES ('a13b453e-95fc-4834-8744-befba7766ba0','license_activated','License <strong>LIC-DEMO-CMS-001</strong> activated for <a href=\"#\">demo-cms.botble.com</a> by customer@botble.com','2026-03-05 17:09:28','2026-03-05 18:09:28'),('a13b453e-967e-4edc-beed-df3edf3cf7e8','license_verified','License <strong>LIC-DEMO-MART-001</strong> verified successfully for <a href=\"#\">shop.botble.com</a>','2026-03-05 16:09:28','2026-03-05 18:09:28'),('a13b453e-96f2-484a-896c-2cb4ebbc8fe7','license_deactivated','License <strong>LIC-DEMO-CMS-001</strong> deactivated for <a href=\"#\">old-site.botble.com</a> by customer@botble.com','2026-03-05 15:09:28','2026-03-05 18:09:28'),('a13b453e-9766-4b7f-8524-99798fa19dc0','customer_login','Customer <strong>customer@botble.com</strong> logged in from IP 103.45.67.89','2026-03-05 14:09:28','2026-03-05 18:09:28'),('a13b453e-97e0-4d06-9cf0-dc8ed2cded6e','update_downloaded','Update v2.0.0 for <strong>Botble CMS</strong> downloaded by license LIC-DEMO-CMS-001','2026-03-05 13:09:28','2026-03-05 18:09:28'),('a13b453e-985b-49d5-97d3-c66b9037e357','license_created','New license <strong>LIC-DEMO-SHOFY-001</strong> created for product Shofy','2026-03-05 12:09:28','2026-03-05 18:09:28'),('a13b453e-99c9-40d2-8a71-e5807f530cb1','api_request','API request from <strong>ext_demo_api_key</strong>: POST /api/v1/license/verify','2026-03-05 11:09:28','2026-03-05 18:09:28'),('a13b453e-9a4d-472d-aca5-56a58f9b878b','license_verified','License <strong>LIC-DEMO-FLEX-001</strong> verified successfully for <a href=\"#\">realestate.botble.com</a>','2026-03-05 10:09:28','2026-03-05 18:09:28'),('a13b453e-9ada-49a7-86a1-2af4be96b0e4','customer_registered','New customer <strong>customer@botble.com</strong> registered','2026-03-05 08:09:28','2026-03-05 18:09:28'),('a13b453e-9b7b-4057-b09a-3b0033869961','license_activated','License <strong>LIC-DEMO-MART-001</strong> activated for <a href=\"#\">marketplace.botble.com</a> by customer@botble.com','2026-03-05 06:09:28','2026-03-05 18:09:28'),('a13b453e-9c0b-4e2e-9b6f-479f01e30cb6','product_updated','Product <strong>Botble CMS</strong> updated to version 2.0.0','2026-03-05 04:09:28','2026-03-05 18:09:28'),('a13b453e-9c96-4d1a-85c9-931c153feba2','update_downloaded','Update v1.5.0 for <strong>Flex Home</strong> downloaded by license LIC-DEMO-FLEX-001','2026-03-05 02:09:28','2026-03-05 18:09:28'),('a13b453e-9d14-4d60-85bf-333a6cadfec4','license_expired','License <strong>LIC-BOTBLE-EXPIRED</strong> has expired','2026-03-04 22:09:28','2026-03-05 18:09:28'),('a13b453e-9d91-49cf-bbf2-1ebf1ab66532','api_request','API request from <strong>int_admin_api_key</strong>: GET /api/v1/licenses','2026-03-04 20:09:28','2026-03-05 18:09:28'),('a13b453e-9e0d-41bc-ae31-42746bc2c80c','customer_login','Customer <strong>john@example.com</strong> logged in from IP 192.168.1.100','2026-03-04 19:09:28','2026-03-05 18:09:28');
/*!40000 ALTER TABLE `lm_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_api_keys`
--

DROP TABLE IF EXISTS `lm_api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_api_keys` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'external',
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `special` tinyint(1) NOT NULL DEFAULT '0',
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ls_api_keys_key_unique` (`key`),
  KEY `ls_api_keys_type_key_index` (`type`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_api_keys`
--

LOCK TABLES `lm_api_keys` WRITE;
/*!40000 ALTER TABLE `lm_api_keys` DISABLE KEYS */;
INSERT INTO `lm_api_keys` VALUES ('a13b453e-9194-4937-ae6d-e7c3f122ddd0','external','ext_demo_api_key_for_testing_purposes','[\"connection:check\",\"license:activate\",\"license:verify\",\"license:deactivate\",\"license:check\",\"update:list\",\"update:latest\",\"update:check\",\"update:download\"]',0,0,'2026-03-05 18:09:28','2026-03-05 18:09:28',NULL),('a13b453e-9336-4128-b864-6a853c11438b','internal','int_admin_api_key_full_access','[\"*\"]',1,0,'2026-03-05 18:09:28','2026-03-05 18:09:28',NULL),('a13b453e-9410-46dd-beec-f8f4117542cd','external','ext_limited_verify_only_key','[\"license:verify\"]',0,0,'2026-03-05 18:09:28','2026-03-05 18:09:28','2026-09-06 01:09:28'),('a13b453e-94a9-4aa8-bfda-fb95ead3729a','external','ext_revoked_inactive_key','[\"license:activate\",\"license:verify\"]',0,1,'2026-03-05 18:09:28','2026-03-05 18:09:28',NULL);
/*!40000 ALTER TABLE `lm_api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_customer_activity_logs`
--

DROP TABLE IF EXISTS `lm_customer_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_customer_activity_logs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lm_customer_activity_logs_customer_id_created_at_index` (`customer_id`,`created_at`),
  KEY `lm_customer_activity_logs_customer_id_index` (`customer_id`),
  KEY `lm_customer_activity_logs_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_customer_activity_logs`
--

LOCK TABLES `lm_customer_activity_logs` WRITE;
/*!40000 ALTER TABLE `lm_customer_activity_logs` DISABLE KEYS */;
INSERT INTO `lm_customer_activity_logs` VALUES ('a13b453e-9ed6-4ba4-acd2-c3044aa9bc03','CUST-DEMO','login','Logged in from IP 103.45.67.89','103.45.67.89',NULL,NULL,'2026-03-05 17:39:28','2026-03-05 18:09:28'),('a13b453e-9fa2-4a9f-bedd-7a110f3546c2','CUST-DEMO','license_activated','License <strong>LIC-DEMO-CMS-001</strong> activated for <strong>demo-cms.botble.com</strong>','103.45.67.89',NULL,NULL,'2026-03-05 16:09:28','2026-03-05 18:09:28'),('a13b453e-a042-494e-9fc3-84a092f53664','CUST-DEMO','license_verified','License <strong>LIC-DEMO-MART-001</strong> verified for <strong>shop.botble.com</strong>','103.45.67.92',NULL,NULL,'2026-03-05 14:09:28','2026-03-05 18:09:28'),('a13b453e-a0df-48b8-ba28-b63f7bed26d3','CUST-DEMO','license_deactivated','License <strong>LIC-DEMO-CMS-001</strong> deactivated for <strong>old-site.botble.com</strong>','103.45.67.89',NULL,NULL,'2026-03-05 12:09:28','2026-03-05 18:09:28'),('a13b453e-a15c-4165-987c-34e5608b33e2','CUST-DEMO','update_downloaded','Downloaded update <strong>v2.0.0</strong> for <strong>Botble CMS</strong>','103.45.67.89',NULL,NULL,'2026-03-05 06:09:28','2026-03-05 18:09:28'),('a13b453e-a1e4-4f34-9edc-c8c9b0075c76','CUST-DEMO','license_activated','License <strong>LIC-DEMO-MART-001</strong> activated for <strong>marketplace.botble.com</strong>','103.45.67.93',NULL,NULL,'2026-03-04 18:09:28','2026-03-05 18:09:28'),('a13b453e-a2bb-4345-8227-9001f65c6c91','CUST-DEMO','license_verified','License <strong>LIC-DEMO-FLEX-001</strong> verified for <strong>realestate.botble.com</strong>','103.45.67.91',NULL,NULL,'2026-03-03 18:09:28','2026-03-05 18:09:28'),('a13b453e-a343-436c-9bca-660acaa634e7','CUST-DEMO','update_downloaded','Downloaded update <strong>v1.5.0</strong> for <strong>Flex Home</strong>','103.45.67.91',NULL,NULL,'2026-03-02 18:09:28','2026-03-05 18:09:28'),('a13b453e-a3cb-4231-b9c7-a14cbe846419','CUST-DEMO','login','Logged in from IP 103.45.67.90','103.45.67.90',NULL,NULL,'2026-02-28 18:09:28','2026-03-05 18:09:28'),('a13b453e-a453-46e0-9e59-4164fc5fce1d','CUST-DEMO','license_activated','License <strong>LIC-DEMO-FLEX-001</strong> activated for <strong>realestate.botble.com</strong>','103.45.67.91',NULL,NULL,'2026-02-26 18:09:28','2026-03-05 18:09:28');
/*!40000 ALTER TABLE `lm_customer_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_customer_password_reset_tokens`
--

DROP TABLE IF EXISTS `lm_customer_password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_customer_password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_customer_password_reset_tokens`
--

LOCK TABLES `lm_customer_password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `lm_customer_password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `lm_customer_password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_customers`
--

DROP TABLE IF EXISTS `lm_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_customers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ls_customers_email_unique` (`email`),
  UNIQUE KEY `ls_customers_client_id_unique` (`client_id`),
  UNIQUE KEY `lm_customers_client_id_unique` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_customers`
--

LOCK TABLES `lm_customers` WRITE;
/*!40000 ALTER TABLE `lm_customers` DISABLE KEYS */;
INSERT INTO `lm_customers` VALUES ('a13b453c-e686-489d-af9e-bca24a62cedf','John Doe','john@example.com','$2y$12$cFDv96H1CqoL.3kxuc.t4.eHa8gBU1WbhrqIg/vLpY4CzzK8YUMc.',NULL,'CUST-001',NULL,NULL,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL),('a13b453d-4303-4c9c-9e21-2303dd42ced2','Jane Smith','jane@example.com','$2y$12$lHVPraW5GJmS76ZHTQfMge83EY6IYne7IQmRU7Q/3OqQU27hCOwZq',NULL,'CUST-002',NULL,NULL,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL),('a13b453d-9dce-464a-a668-61894a65e941','Bob Wilson','bob@example.com','$2y$12$qYFBdeMQgY.A.g9AxDwqpuQ38hTWCbaXEwMgeoj1d33FgoXEQCdKC',NULL,'CUST-003',NULL,NULL,'2026-03-05 18:09:28','2026-03-05 18:09:28',NULL),('a13b453e-068c-4100-a7a1-407116405407','Alice Brown','alice@example.com','$2y$12$YRBwu1YxgppGewolUOZXYuodSDXSoJqR4D3B6Am5XJKImbMEzY1Bm',NULL,'CUST-004',NULL,NULL,'2026-03-05 18:09:28','2026-03-05 18:09:28',NULL),('a13b453e-76c3-46a3-aa54-655c9c6ee3f3','Demo Customer','customer@botble.com','$2y$12$I/hpYzXutMOyp16yepsR3eW0tpIbnzmTmC3ACLvhgl4MRIxkkcnyy',NULL,'CUST-DEMO',NULL,NULL,'2026-03-05 18:09:28','2026-03-05 18:09:28',NULL);
/*!40000 ALTER TABLE `lm_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_licenses`
--

DROP TABLE IF EXISTS `lm_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_licenses` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_reference_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_envato` tinyint(1) NOT NULL DEFAULT '0',
  `customer_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uses` int NOT NULL DEFAULT '0',
  `parallel_uses` int DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `expiry_days` int DEFAULT NULL,
  `updates_until` date DEFAULT NULL,
  `support_until` date DEFAULT NULL,
  `domains` text COLLATE utf8mb4_unicode_ci,
  `ips` text COLLATE utf8mb4_unicode_ci,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `is_valid` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lm_licenses_license_code_unique` (`license_code`),
  KEY `lm_licenses_product_reference_id_is_valid_index` (`product_reference_id`,`is_valid`),
  KEY `lm_licenses_customer_id_is_valid_index` (`customer_id`,`is_valid`),
  KEY `lm_licenses_product_reference_id_index` (`product_reference_id`),
  KEY `lm_licenses_invoice_index` (`invoice`),
  KEY `lm_licenses_customer_id_index` (`customer_id`),
  KEY `lm_licenses_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_licenses`
--

LOCK TABLES `lm_licenses` WRITE;
/*!40000 ALTER TABLE `lm_licenses` DISABLE KEYS */;
INSERT INTO `lm_licenses` VALUES ('a13b453e-77d5-406a-88c2-3b857e48635d','BOTBLE-CMS','550e8400-e29b-41d4-a716-446655440001','regular',NULL,0,'CUST-001','john@example.com',0,3,'2027-03-06',NULL,'2027-03-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7af0-4799-b400-faeee8180db2','BOTBLE-CMS','550e8400-e29b-41d4-a716-446655440002','extended',NULL,0,'CUST-002','jane@example.com',0,5,NULL,NULL,'2028-03-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7cac-4b6e-b397-435506887a3b','BOTBLE-CMS','550e8400-e29b-41d4-a716-446655440003','regular',NULL,0,'CUST-003','bob@example.com',0,1,'2026-01-06',NULL,'2026-01-06',NULL,NULL,NULL,NULL,0,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7d5d-4b65-bf4e-8c18e775055e','FLEX-HOME','6ba7b810-9dad-11d1-80b4-00c04fd430c1','regular',NULL,0,'CUST-001','john@example.com',0,2,'2026-09-06',NULL,'2026-09-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-7ebc-430f-82e3-0d4564737f2a','MARTFURY','6ba7b811-9dad-11d1-80b4-00c04fd430c2','extended',NULL,0,'CUST-004','alice@example.com',0,10,NULL,NULL,'2029-03-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8189-4df6-a4a4-19fdcd3fa204','BOTBLE-CMS','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11','extended',NULL,0,'CUST-DEMO','customer@botble.com',0,5,NULL,NULL,'2028-03-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-886c-4ebb-8c72-849e3589a7e3','FLEX-HOME','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a12','regular',NULL,0,'CUST-DEMO','customer@botble.com',0,3,'2027-03-06',NULL,'2027-03-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-89c9-460d-bf12-81fb295e4571','MARTFURY','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a13','extended',NULL,0,'CUST-DEMO','customer@botble.com',0,10,NULL,NULL,'2029-03-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8ca7-4582-9218-d8ef80c09bce','SHOFY','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a14','regular',NULL,0,'CUST-DEMO','customer@botble.com',0,2,'2026-09-06',NULL,'2026-09-06',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8e27-4c7e-8235-a14855abcf77','FARMART','a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a15','regular',NULL,0,'CUST-DEMO','customer@botble.com',0,2,'2026-06-06',NULL,'2026-03-01',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28'),('a13b453e-8fad-4a3f-b783-b17c80586d3a','FARMART','f47ac10b-58cc-4372-a567-0e02b2c3d479','regular',NULL,0,'CUST-002','jane@example.com',0,2,'2026-11-06',NULL,'2026-02-24',NULL,NULL,NULL,NULL,1,'2026-03-05 18:09:28','2026-03-05 18:09:28');
/*!40000 ALTER TABLE `lm_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_product_versions`
--

DROP TABLE IF EXISTS `lm_product_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_product_versions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_reference_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `released_at` date DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `changelog` text COLLATE utf8mb4_unicode_ci,
  `main_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lm_product_versions_version_id_unique` (`version_id`),
  KEY `lm_product_versions_product_reference_id_is_active_index` (`product_reference_id`,`is_active`),
  KEY `lm_product_versions_product_reference_id_index` (`product_reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_product_versions`
--

LOCK TABLES `lm_product_versions` WRITE;
/*!40000 ALTER TABLE `lm_product_versions` DISABLE KEYS */;
INSERT INTO `lm_product_versions` VALUES ('a13b453c-719b-43f1-9faf-c6dff9bafcb6','botble-cms-100','BOTBLE-CMS','1.0.0','2026-01-02','Initial release','<ul>\n<li>Fixed bug in license validation</li>\n<li>Added new dashboard widgets</li>\n<li>Updated dependencies to latest versions</li>\n<li>Added multi-language support</li>\n<li>Improved error handling</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-723a-4af7-810d-30cbc5d505d4','botble-cms-110','BOTBLE-CMS','1.1.0','2025-09-03','Bug fixes and improvements','<ul>\n<li>Improved API response time</li>\n<li>Updated dependencies to latest versions</li>\n<li>Fixed compatibility issues</li>\n<li>Added multi-language support</li>\n<li>Optimized database queries</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-72d9-4ac7-91a4-960c4097b275','botble-cms-120','BOTBLE-CMS','1.2.0','2026-01-04','New features added','<ul>\n<li>Improved API response time</li>\n<li>Enhanced security measures</li>\n<li>Optimized database queries</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7383-4f41-943f-5305c65ce8f8','botble-cms-200','BOTBLE-CMS','2.0.0','2025-12-05','Major update with breaking changes','<ul>\n<li>Improved API response time</li>\n<li>Updated dependencies to latest versions</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7504-4ab8-b16c-1d75f7c962d3','flex-home-100','FLEX-HOME','1.0.0','2026-02-03','Initial release','<ul>\n<li>Fixed bug in license validation</li>\n<li>Updated dependencies to latest versions</li>\n<li>Fixed compatibility issues</li>\n<li>Added multi-language support</li>\n<li>Improved error handling</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-75a2-40f9-8987-95b30e5b9d9a','flex-home-150','FLEX-HOME','1.5.0','2025-10-04','Added property comparison','<ul>\n<li>Added new dashboard widgets</li>\n<li>Fixed compatibility issues</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7653-44bc-b59a-8262f8697811','flex-home-200','FLEX-HOME','2.0.0','2025-09-05','New dashboard design','<ul>\n<li>Improved API response time</li>\n<li>Added new dashboard widgets</li>\n<li>Updated dependencies to latest versions</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7792-407e-bde1-9d6b1c3dbedf','martfury-100','MARTFURY','1.0.0','2025-11-04','Initial release','<ul>\n<li>Fixed bug in license validation</li>\n<li>Improved API response time</li>\n<li>Updated dependencies to latest versions</li>\n<li>Enhanced security measures</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7848-4bea-a176-c74a457f7cb6','martfury-130','MARTFURY','1.3.0','2025-10-05','Added multi-vendor support','<ul>\n<li>Fixed bug in license validation</li>\n<li>Improved API response time</li>\n<li>Added new dashboard widgets</li>\n<li>Updated dependencies to latest versions</li>\n<li>Enhanced security measures</li>\n<li>Fixed compatibility issues</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-799b-458a-8c52-8804db6ac879','shofy-100','SHOFY','1.0.0','2025-09-05','Initial release','<ul>\n<li>Added new dashboard widgets</li>\n<li>Enhanced security measures</li>\n<li>Added multi-language support</li>\n<li>Added webhook notifications</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7b6b-4b1e-ae2f-128ae681534d','farmart-100','FARMART','1.0.0','2025-09-04','Initial release','<ul>\n<li>Enhanced security measures</li>\n<li>Fixed compatibility issues</li>\n<li>Improved error handling</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-7fec-4cda-ac3e-08a03f163dc4','farmart-120','FARMART','1.2.0','2025-09-05','Performance improvements','<ul>\n<li>Fixed bug in license validation</li>\n<li>Enhanced security measures</li>\n<li>Added multi-language support</li>\n<li>Optimized database queries</li>\n</ul>',NULL,NULL,1,'2026-03-05 18:09:27','2026-03-05 18:09:27');
/*!40000 ALTER TABLE `lm_product_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_products`
--

DROP TABLE IF EXISTS `lm_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `envato_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `license_update` tinyint(1) NOT NULL DEFAULT '0',
  `serve_latest_updates` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `default_license_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_uses` int DEFAULT NULL,
  `default_parallel_uses` int DEFAULT NULL,
  `default_expiry_days` int DEFAULT NULL,
  `default_updates_until_days` int DEFAULT NULL,
  `default_support_until_days` int DEFAULT NULL,
  `default_comments` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lm_products_reference_id_unique` (`reference_id`),
  KEY `lm_products_is_active_index` (`is_active`),
  KEY `lm_products_envato_id_index` (`envato_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_products`
--

LOCK TABLES `lm_products` WRITE;
/*!40000 ALTER TABLE `lm_products` DISABLE KEYS */;
INSERT INTO `lm_products` VALUES ('a13b453c-70ca-4e83-9297-92fba81cf83d','BOTBLE-CMS','16928182','Botble CMS','Laravel CMS - Pair any design with CMS in no time',1,1,1,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('a13b453c-7427-45d8-a795-10adcd43f2df','FLEX-HOME','21705196','Flex Home','Laravel Real Estate Multilingual System',1,1,1,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('a13b453c-76f6-4f0e-ae62-9c1f3d356d1a','MARTFURY','29856498','Martfury','Laravel Ecommerce - Pair any design with CMS in no time',1,1,1,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('a13b453c-78ed-4813-bd5f-92b8c0f82f8c','SHOFY','45003000','Shofy','Laravel Multipurpose Ecommerce',1,1,1,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('a13b453c-7a90-4a6c-b10b-a3d15ccb80ad','FARMART','34719755','Farmart','Laravel Ecommerce for grocery, food & organic',1,0,1,'2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `lm_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lm_update_downloads`
--

DROP TABLE IF EXISTS `lm_update_downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lm_update_downloads` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `download_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_reference_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '1',
  `downloaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lm_update_downloads_download_id_unique` (`download_id`),
  KEY `lm_update_downloads_product_reference_id_is_valid_index` (`product_reference_id`,`is_valid`),
  KEY `lm_update_downloads_product_reference_id_index` (`product_reference_id`),
  KEY `lm_update_downloads_version_id_index` (`version_id`),
  KEY `lm_update_downloads_ip_address_index` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lm_update_downloads`
--

LOCK TABLES `lm_update_downloads` WRITE;
/*!40000 ALTER TABLE `lm_update_downloads` DISABLE KEYS */;
/*!40000 ALTER TABLE `lm_update_downloads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_files`
--

DROP TABLE IF EXISTS `media_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_files` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `folder_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `visibility` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  PRIMARY KEY (`id`),
  KEY `media_files_user_id_index` (`user_id`),
  KEY `media_files_index` (`folder_id`,`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_files`
--

LOCK TABLES `media_files` WRITE;
/*!40000 ALTER TABLE `media_files` DISABLE KEYS */;
INSERT INTO `media_files` VALUES ('a13b453c-56de-4529-ac1d-d5b797e2cdf8','0','favicon','favicon','a13b453c-4db1-4426-bb99-912667689cab','image/png',9662,'general/favicon.png','[]','2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,'public'),('a13b453c-5d4e-410c-a6b2-627384f8e33e','0','logo-dark','logo-dark','a13b453c-4db1-4426-bb99-912667689cab','image/png',4326,'general/logo-dark.png','[]','2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,'public'),('a13b453c-607e-4d75-b805-63101c5f24df','0','logo','logo','a13b453c-4db1-4426-bb99-912667689cab','image/png',4326,'general/logo.png','[]','2026-03-05 18:09:27','2026-03-05 18:09:27',NULL,'public');
/*!40000 ALTER TABLE `media_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_folders`
--

DROP TABLE IF EXISTS `media_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_folders` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_folders_user_id_index` (`user_id`),
  KEY `media_folders_index` (`parent_id`,`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_folders`
--

LOCK TABLES `media_folders` WRITE;
/*!40000 ALTER TABLE `media_folders` DISABLE KEYS */;
INSERT INTO `media_folders` VALUES ('a13b453c-4db1-4426-bb99-912667689cab','0','general',NULL,'general','0','2026-03-05 18:09:27','2026-03-05 18:09:27',NULL);
/*!40000 ALTER TABLE `media_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_settings`
--

DROP TABLE IF EXISTS `media_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `media_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_settings`
--

LOCK TABLES `media_settings` WRITE;
/*!40000 ALTER TABLE `media_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `media_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_locations`
--

DROP TABLE IF EXISTS `menu_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_locations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_locations_menu_id_created_at_index` (`menu_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_locations`
--

LOCK TABLES `menu_locations` WRITE;
/*!40000 ALTER TABLE `menu_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_nodes`
--

DROP TABLE IF EXISTS `menu_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_nodes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `reference_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_font` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` tinyint unsigned NOT NULL DEFAULT '0',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `css_class` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `has_child` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_nodes_menu_id_index` (`menu_id`),
  KEY `menu_nodes_parent_id_index` (`parent_id`),
  KEY `reference_id` (`reference_id`),
  KEY `reference_type` (`reference_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_nodes`
--

LOCK TABLES `menu_nodes` WRITE;
/*!40000 ALTER TABLE `menu_nodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_boxes`
--

DROP TABLE IF EXISTS `meta_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meta_boxes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_value` text COLLATE utf8mb4_unicode_ci,
  `reference_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meta_boxes_reference_id_index` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meta_boxes`
--

LOCK TABLES `meta_boxes` WRITE;
/*!40000 ALTER TABLE `meta_boxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `meta_boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'2013_04_09_032329_create_base_tables',1),(3,'2013_04_09_062329_create_revisions_table',1),(4,'2014_10_12_000000_create_users_table',1),(5,'2014_10_12_100000_create_password_reset_tokens_table',1),(6,'2016_06_10_230148_create_acl_tables',1),(7,'2016_06_14_230857_create_menus_table',1),(8,'2016_06_28_221418_create_pages_table',1),(9,'2016_10_05_074239_create_setting_table',1),(10,'2016_10_07_193005_create_translations_table',1),(11,'2016_11_28_032840_create_dashboard_widget_tables',1),(12,'2016_12_16_084601_create_widgets_table',1),(13,'2017_05_09_070343_create_media_tables',1),(14,'2017_11_03_070450_create_slug_table',1),(15,'2019_01_05_053554_create_jobs_table',1),(16,'2019_08_19_000000_create_failed_jobs_table',1),(17,'2019_12_14_000001_create_personal_access_tokens_table',1),(18,'2022_04_20_100851_add_index_to_media_table',1),(19,'2022_04_20_101046_add_index_to_menu_table',1),(20,'2022_07_10_034813_move_lang_folder_to_root',1),(21,'2022_08_04_051940_add_missing_column_expires_at',1),(22,'2022_09_01_000001_create_admin_notifications_tables',1),(23,'2022_10_14_024629_drop_column_is_featured',1),(24,'2022_11_18_063357_add_missing_timestamp_in_table_settings',1),(25,'2022_12_02_093615_update_slug_index_columns',1),(26,'2023_01_01_000001_create_lm_products_table',1),(27,'2023_01_01_000002_create_lm_product_versions_table',1),(28,'2023_01_01_000003_create_lm_licenses_table',1),(29,'2023_01_01_000004_create_lm_activations_table',1),(30,'2023_01_01_000005_create_lm_activity_logs_table',1),(31,'2023_01_01_000006_create_lm_update_downloads_table',1),(32,'2023_01_30_024431_add_alt_to_media_table',1),(33,'2023_02_16_042611_drop_table_password_resets',1),(34,'2023_04_23_005903_add_column_permissions_to_admin_notifications',1),(35,'2023_05_10_075124_drop_column_id_in_role_users_table',1),(36,'2023_06_20_000001_create_ls_customers_table',1),(37,'2023_08_21_090810_make_page_content_nullable',1),(38,'2023_09_14_021936_update_index_for_slugs_table',1),(39,'2023_12_07_095130_add_color_column_to_media_folders_table',1),(40,'2023_12_12_105220_drop_translations_table',1),(41,'2023_12_17_162208_make_sure_column_color_in_media_folders_nullable',1),(42,'2024_04_04_110758_update_value_column_in_user_meta_table',1),(43,'2024_05_12_091229_add_column_visibility_to_table_media_files',1),(44,'2024_05_20_000001_create_ls_api_keys_table',1),(45,'2024_06_05_101527_add_last_login_at_column_to_ls_customers_table',1),(46,'2024_07_07_091316_fix_column_url_in_menu_nodes_table',1),(47,'2024_07_12_100000_change_random_hash_for_media',1),(48,'2024_09_30_024515_create_sessions_table',1),(49,'2024_12_01_000000_add_indexes_to_pages_translations_table',1),(50,'2024_12_01_000000_add_key_prefix_index_to_slugs_table',1),(51,'2024_12_19_000001_create_device_tokens_table',1),(52,'2024_12_19_000002_create_push_notifications_table',1),(53,'2024_12_19_000003_create_push_notification_recipients_table',1),(54,'2024_12_30_000001_create_user_settings_table',1),(55,'2025_04_08_040931_create_social_logins_table',1),(56,'2025_07_06_030754_add_phone_to_users_table',1),(57,'2025_07_31_add_performance_indexes_to_slugs_table',1),(58,'2025_11_10_000000_cleanup_duplicate_widgets',1),(59,'2025_11_30_100000_add_sessions_invalidated_at_to_users_table',1),(60,'2025_12_01_000001_rename_ls_prefix_to_lm_prefix_in_license_manager_tables',1),(61,'2025_12_30_051712_import_legacy_licensebox_settings',1),(62,'2025_12_30_052741_drop_legacy_options_table',1),(63,'2025_12_30_082724_migrate_licensebox_settings_to_license_manager',1),(64,'2025_12_30_083317_add_performance_indexes_to_license_tables',1),(65,'2025_12_30_213000_rename_ls_prefix_to_lm',1),(66,'2025_12_31_030506_migrate_ls_settings_to_lm_settings',1),(67,'2025_12_31_032326_rename_ls_tables_to_lm_tables',1),(68,'2026_01_05_080438_drop_unused_lm_tables',1),(69,'2026_01_17_104247_rename_email_verified_at_to_confirmed_at_in_lm_customers_table',1),(70,'2026_01_30_000000_migrate_copyright_text_to_theme_option',1),(71,'2026_02_03_000001_rename_legacy_tables_to_lm_prefix',1),(72,'2026_02_03_000002_modernize_lm_products_columns',1),(73,'2026_02_03_000003_modernize_lm_product_versions_columns',1),(74,'2026_02_03_000004_modernize_lm_licenses_columns',1),(75,'2026_02_03_000005_modernize_lm_activations_columns',1),(76,'2026_02_03_000006_modernize_lm_activity_logs_columns',1),(77,'2026_02_03_000007_modernize_lm_update_downloads_columns',1),(78,'2026_02_03_100000_migrate_legacy_data_to_lm_tables',1),(79,'2026_02_04_020417_create_lm_customer_activity_logs_table',1),(80,'2026_02_12_000001_make_client_id_nullable_in_lm_customers_table',1),(81,'2026_03_03_000001_add_license_defaults_to_lm_products_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pages_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES ('a13b453c-48a6-415c-b1c3-a86681307471','Terms of Service','<h2>1. Acceptance of Terms</h2>\n<p>By accessing and using the License Manager service, you agree to be bound by these Terms of Service.</p>\n\n<h2>2. License Usage</h2>\n<p>Licenses purchased through our platform are subject to the specific terms of each product. You may not redistribute, resell, or share your license keys without authorization.</p>\n\n<h2>3. Account Responsibilities</h2>\n<p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>\n\n<h2>4. Modifications</h2>\n<p>We reserve the right to modify these terms at any time. Continued use of the service constitutes acceptance of any modifications.</p>\n\n<h2>5. Contact</h2>\n<p>For questions about these terms, please contact our support team.</p>',NULL,NULL,'main',NULL,'published','2026-03-05 18:09:27','2026-03-05 18:09:27'),('a13b453c-4aba-4d63-8046-12640653953d','Privacy Policy','<h2>1. Information We Collect</h2>\n<p>We collect information you provide directly to us, including name, email address, and license usage data.</p>\n\n<h2>2. How We Use Your Information</h2>\n<p>We use the information we collect to provide and improve our services, process transactions, and communicate with you.</p>\n\n<h2>3. Data Security</h2>\n<p>We implement appropriate security measures to protect your personal information against unauthorized access or disclosure.</p>\n\n<h2>4. Data Retention</h2>\n<p>We retain your information for as long as your account is active or as needed to provide you services.</p>\n\n<h2>5. Contact</h2>\n<p>For privacy-related inquiries, please contact our support team.</p>',NULL,NULL,'main',NULL,'published','2026-03-05 18:09:27','2026-03-05 18:09:27');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_notification_recipients`
--

DROP TABLE IF EXISTS `push_notification_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_notification_recipients` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `push_notification_id` bigint unsigned NOT NULL,
  `user_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `device_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `fcm_response` json DEFAULT NULL,
  `error_message` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pnr_notification_user_index` (`push_notification_id`,`user_type`,`user_id`),
  KEY `pnr_user_status_index` (`user_type`,`user_id`,`status`),
  KEY `pnr_user_read_index` (`user_type`,`user_id`,`read_at`),
  KEY `pnr_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_notification_recipients`
--

LOCK TABLES `push_notification_recipients` WRITE;
/*!40000 ALTER TABLE `push_notification_recipients` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_notification_recipients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_notifications`
--

DROP TABLE IF EXISTS `push_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `target_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `sent_count` int NOT NULL DEFAULT '0',
  `failed_count` int NOT NULL DEFAULT '0',
  `delivered_count` int NOT NULL DEFAULT '0',
  `read_count` int NOT NULL DEFAULT '0',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `push_notifications_type_created_at_index` (`type`,`created_at`),
  KEY `push_notifications_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `push_notifications_created_by_index` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_notifications`
--

LOCK TABLES `push_notifications` WRITE;
/*!40000 ALTER TABLE `push_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `revisions`
--

DROP TABLE IF EXISTS `revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `revisions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revisionable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revisionable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `revisions_revisionable_id_revisionable_type_index` (`revisionable_id`,`revisionable_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revisions`
--

LOCK TABLES `revisions` WRITE;
/*!40000 ALTER TABLE `revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_users`
--

DROP TABLE IF EXISTS `role_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_users` (
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_users_user_id_index` (`user_id`),
  KEY `role_users_role_id_index` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_users`
--

LOCK TABLES `role_users` WRITE;
/*!40000 ALTER TABLE `role_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint unsigned NOT NULL DEFAULT '0',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`),
  KEY `roles_created_by_index` (`created_by`),
  KEY `roles_updated_by_index` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES ('a13b453c-118a-4b57-a9de-2295f9495141','admin','Admin','{\"users.index\":true,\"users.create\":true,\"users.edit\":true,\"users.destroy\":true,\"roles.index\":true,\"roles.create\":true,\"roles.edit\":true,\"roles.destroy\":true,\"core.system\":true,\"core.cms\":true,\"core.manage.license\":true,\"systems.cronjob\":true,\"core.tools\":true,\"tools.data-synchronize\":true,\"media.index\":true,\"files.index\":true,\"files.create\":true,\"files.edit\":true,\"files.trash\":true,\"files.destroy\":true,\"folders.index\":true,\"folders.create\":true,\"folders.edit\":true,\"folders.trash\":true,\"folders.destroy\":true,\"settings.index\":true,\"settings.common\":true,\"settings.options\":true,\"settings.email\":true,\"settings.media\":true,\"settings.admin-appearance\":true,\"settings.cache\":true,\"settings.datatables\":true,\"settings.email.rules\":true,\"settings.phone-number\":true,\"settings.others\":true,\"menus.index\":true,\"menus.create\":true,\"menus.edit\":true,\"menus.destroy\":true,\"optimize.settings\":true,\"pages.index\":true,\"pages.create\":true,\"pages.edit\":true,\"pages.destroy\":true,\"plugins.index\":true,\"plugins.edit\":true,\"plugins.remove\":true,\"plugins.marketplace\":true,\"sitemap.settings\":true,\"core.appearance\":true,\"theme.index\":true,\"theme.activate\":true,\"theme.remove\":true,\"theme.options\":true,\"theme.custom-css\":true,\"theme.custom-js\":true,\"theme.custom-html\":true,\"theme.robots-txt\":true,\"settings.website-tracking\":true,\"widgets.index\":true,\"backups.index\":true,\"backups.create\":true,\"backups.restore\":true,\"backups.destroy\":true,\"captcha.settings\":true,\"packages.license-manager\":true,\"lm.customers.index\":true,\"lm.customers.create\":true,\"lm.customers.edit\":true,\"lm.customers.destroy\":true,\"lm.licenses.index\":true,\"lm.licenses.create\":true,\"lm.licenses.edit\":true,\"lm.licenses.destroy\":true,\"lm.licenses.export\":true,\"lm.licenses.import\":true,\"lm.activations.index\":true,\"lm.activations.destroy\":true,\"lm.activations.edit\":true,\"lm.products.index\":true,\"lm.products.create\":true,\"lm.products.edit\":true,\"lm.products.show\":true,\"lm.products.versions.index\":true,\"lm.products.versions.create\":true,\"lm.products.versions.edit\":true,\"lm.products.versions.destroy\":true,\"lm.products.destroy\":true,\"lm.update-downloads.index\":true,\"lm.activity-logs.index\":true,\"lm.php-obfuscator\":true,\"lm.generate-helper\":true,\"lm.manual-cron\":true,\"lm.settings.index\":true,\"lm.settings.general\":true,\"lm.settings.website_appearance\":true,\"lm.api_keys.settings\":true,\"lm.api.settings\":true,\"social-login.settings\":true,\"plugins.translation\":true,\"translations.locales\":true,\"translations.theme-translations\":true,\"translations.index\":true,\"theme-translations.export\":true,\"other-translations.export\":true,\"theme-translations.import\":true,\"other-translations.import\":true,\"api.settings\":true,\"api.sanctum-token.index\":true,\"api.sanctum-token.create\":true,\"api.sanctum-token.destroy\":true}','Admin users role',1,'a13b453c-0c77-4e0b-978e-245efb84b296','a13b453c-0c77-4e0b-978e-245efb84b296','2026-03-05 18:09:27','2026-03-05 18:09:27');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('a13b453b-81d6-4fe5-a58d-32a6151ae536','media_random_hash','5f8308d749056d45e1c76a2c1d6ffd93',NULL,'2026-03-05 18:09:27'),('a13b453b-81db-442a-836e-fca8c64666e8','api_enabled','0',NULL,'2026-03-05 18:09:27'),('a13b453b-8920-4ce7-b0fd-7d7357951523','activated_plugins','[\"backup\",\"captcha\",\"license-manager\",\"licensebox-legacy-api\",\"social-login\",\"social-login-envato\",\"translation\"]',NULL,'2026-03-05 18:09:27'),('a13b453b-97bd-4e49-9d74-17981f97cca0','theme','license',NULL,'2026-03-05 18:09:27'),('a13b453b-97c2-47bf-a712-bbabf6deb871','show_admin_bar','0',NULL,'2026-03-05 18:09:27'),('a13b453c-3da9-4605-a406-8e9f8d3cfca9','admin_title','License Manager',NULL,'2026-03-05 18:09:27'),('a13b453c-3dae-406d-817c-606cd62d344f','admin_logo','general/logo-dark.png',NULL,'2026-03-05 18:09:27'),('a13b453c-3db1-4e65-b822-e11a9fcf1c2c','admin_favicon','general/favicon.png',NULL,'2026-03-05 18:09:27'),('a13b453c-3db3-47f7-aa03-f943eb6bc529','time_zone','UTC',NULL,'2026-03-05 18:09:27'),('a13b453c-3db6-4cd9-b2a8-b086908c2ca9','locale','en',NULL,'2026-03-05 18:09:27'),('a13b453c-3db8-48c2-ba2f-ff9b37b073af','enable_page_visual_builder','0',NULL,'2026-03-05 18:09:27'),('a13b453c-3dbb-47b7-8cf3-283e6220881c','social_login_enable','1',NULL,'2026-03-05 18:09:27'),('a13b453c-3dbd-42e8-b048-f7d5149ec081','social_login_envato_enable','1',NULL,'2026-03-05 18:09:27'),('a13b453c-3dc0-43c5-a380-c18301e12072','social_login_envato_app_id','demo-envato-app-id',NULL,'2026-03-05 18:09:27'),('a13b453c-3dc2-4a6e-936f-94a3adc00422','social_login_envato_app_secret','demo-envato-app-secret',NULL,'2026-03-05 18:09:27'),('a13b453c-64b2-45b6-bd8f-526166a584fb','theme-license-site_title','Botble License',NULL,'2026-03-05 18:09:27'),('a13b453c-64b6-4b00-9ee6-234835cccf6b','theme-license-seo_description','Manage your software licenses with ease. Activate, verify, and track licenses for your products.',NULL,'2026-03-05 18:09:27'),('a13b453c-64b9-4311-8c0f-5a9fe087d615','theme-license-logo','general/logo.png',NULL,'2026-03-05 18:09:27'),('a13b453c-64bc-4e9f-8d4f-e1f74f7017c5','theme-license-logo_dark','general/logo-dark.png',NULL,'2026-03-05 18:09:27'),('a13b453c-64be-4894-bd7e-0e74ac0f9c03','theme-license-favicon','general/favicon.png',NULL,'2026-03-05 18:09:27'),('a13b453c-64c0-4688-aa3c-a21a83a21638','theme-license-primary_color','#206bc4',NULL,'2026-03-05 18:09:27'),('a13b453c-64c3-4d44-ac6e-d1a407950a67','theme-license-secondary_color','#6c7a91',NULL,'2026-03-05 18:09:27'),('a13b453c-64c5-4f59-8c28-2e4fa644288c','theme-license-heading_color','inherit',NULL,'2026-03-05 18:09:27'),('a13b453c-64c7-4aca-8191-e57bd62d8647','theme-license-text_color','#182433',NULL,'2026-03-05 18:09:27'),('a13b453c-64c9-4c50-930a-e2001c8d2505','theme-license-link_color','#206bc4',NULL,'2026-03-05 18:09:27'),('a13b453c-64cc-4d45-bb9a-ff370847d201','theme-license-link_hover_color','#1a569d',NULL,'2026-03-05 18:09:27'),('a13b453c-64cf-4783-8059-cf66567dc8b5','theme-license-primary_font','Inter',NULL,'2026-03-05 18:09:27'),('a13b453c-64d1-4bc9-9bee-128f5990cd42','theme-license-support_email','support@botble.com',NULL,'2026-03-05 18:09:27'),('a13b453c-64d3-46d4-b4dd-6bd3d09791ba','theme-license-documentation_url','https://docs.botble.com',NULL,'2026-03-05 18:09:27'),('a13b453c-64d6-4125-8c97-9252d6b3361f','theme-license-social_facebook','https://facebook.com/botaboratories',NULL,'2026-03-05 18:09:27'),('a13b453c-64d8-4e73-916c-da58b1940f3d','theme-license-social_twitter','https://twitter.com/nicksaenzllc',NULL,'2026-03-05 18:09:27'),('a13b453c-64da-4971-9501-27978ac68a7b','theme-license-social_github','https://github.com/botble',NULL,'2026-03-05 18:09:27'),('a13b453c-64dd-4409-a1df-947a9556d068','theme-license-terms_url','https://policies.google.com/terms',NULL,'2026-03-05 18:09:27'),('a13b453c-64df-4279-86ed-ae57122d3230','theme-license-privacy_url','https://policies.google.com/privacy',NULL,'2026-03-05 18:09:27'),('a13b453c-64e1-4538-b7c7-fde3dde3a995','theme-license-copyright','© %Y Botble Technologies. All rights reserved.',NULL,'2026-03-05 18:09:27'),('a13b453c-6bf8-4e7a-a52a-b04ec60e1cf7','admin_appearance_layout','horizontal',NULL,'2026-03-05 18:09:27'),('a13b453c-6bfc-4f04-a644-85e634e25814','admin_appearance_container_width','container-fluid',NULL,'2026-03-05 18:09:27'),('a13b453c-700e-4d88-9bcf-8b859ba9e35d','lm_license_encryption_key','base64:cggoCPJiR/318+7w4WyKTQ==',NULL,NULL),('a13b453c-7012-4f43-aa4e-26b5c52cec24','lm_license_encryption_cipher','aes-128-cbc',NULL,NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slugs`
--

DROP TABLE IF EXISTS `slugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slugs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slugs_reference_id_index` (`reference_id`),
  KEY `slugs_key_index` (`key`),
  KEY `slugs_prefix_index` (`prefix`),
  KEY `slugs_reference_index` (`reference_id`,`reference_type`),
  KEY `idx_key_prefix` (`key`,`prefix`),
  KEY `idx_slugs_reference` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slugs`
--

LOCK TABLES `slugs` WRITE;
/*!40000 ALTER TABLE `slugs` DISABLE KEYS */;
INSERT INTO `slugs` VALUES ('a13b453e-a5e2-420c-bcd6-7f011e96d524','terms-of-service','a13b453c-48a6-415c-b1c3-a86681307471','Botble\\Page\\Models\\Page','','2026-03-05 18:09:27','2026-03-05 18:09:28'),('a13b453e-a6ed-47f8-aaac-9ab8a8c364c2','privacy-policy','a13b453c-4aba-4d63-8046-12640653953d','Botble\\Page\\Models\\Page','','2026-03-05 18:09:27','2026-03-05 18:09:28');
/*!40000 ALTER TABLE `slugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_logins`
--

DROP TABLE IF EXISTS `social_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_logins` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` text COLLATE utf8mb4_unicode_ci,
  `refresh_token` text COLLATE utf8mb4_unicode_ci,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `provider_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_logins_provider_provider_id_unique` (`provider`,`provider_id`),
  KEY `social_logins_user_type_user_id_index` (`user_type`,`user_id`),
  KEY `social_logins_user_id_user_type_index` (`user_id`,`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_logins`
--

LOCK TABLES `social_logins` WRITE;
/*!40000 ALTER TABLE `social_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_meta`
--

DROP TABLE IF EXISTS `user_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_meta` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_meta_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_meta`
--

LOCK TABLES `user_meta` WRITE;
/*!40000 ALTER TABLE `user_meta` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_settings_user_type_user_id_key_unique` (`user_type`,`user_id`,`key`),
  KEY `user_settings_user_type_user_id_index` (`user_type`,`user_id`),
  KEY `user_settings_key_index` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `first_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_user` tinyint(1) NOT NULL DEFAULT '0',
  `manage_supers` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` text COLLATE utf8mb4_unicode_ci,
  `last_login` timestamp NULL DEFAULT NULL,
  `sessions_invalidated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('a13b453c-0c77-4e0b-978e-245efb84b296','admin@company.com',NULL,NULL,'$2y$12$Cg1sIlMeQSNdHgg5K03/EeLX5Sa0dh5GLTOBkcn2OL/2y5HKt/.uq',NULL,'2026-03-05 18:09:27','2026-03-05 18:09:27','System','Admin','admin',NULL,1,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `widgets` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widget_id` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sidebar_id` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` tinyint unsigned NOT NULL DEFAULT '0',
  `data` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `widgets_unique_index` (`theme`,`sidebar_id`,`widget_id`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets`
--

LOCK TABLES `widgets` WRITE;
/*!40000 ALTER TABLE `widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `widgets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-06  8:09:29
