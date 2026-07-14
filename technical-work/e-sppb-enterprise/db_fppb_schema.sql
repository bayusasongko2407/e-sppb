/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: db_fppb
-- ------------------------------------------------------
-- Server version	10.11.10-MariaDB-log

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
-- Table structure for table `master_assets`
--

DROP TABLE IF EXISTS `master_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_assets` (
  `master_assets_id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_barcode` varchar(50) NOT NULL,
  `assets_name` varchar(255) NOT NULL,
  `default_unit` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`master_assets_id`),
  UNIQUE KEY `assets_barcode` (`assets_barcode`),
  KEY `fk_assets_unit` (`default_unit`),
  CONSTRAINT `fk_assets_unit` FOREIGN KEY (`default_unit`) REFERENCES `master_unit` (`unit_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=24349 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_departments`
--

DROP TABLE IF EXISTS `master_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_locations`
--

DROP TABLE IF EXISTS `master_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(100) NOT NULL,
  `address` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `location_name` (`location_name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_roles`
--

DROP TABLE IF EXISTS `master_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_status`
--

DROP TABLE IF EXISTS `master_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_status` (
  `status_code` varchar(50) NOT NULL,
  `status_name` varchar(100) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_unit`
--

DROP TABLE IF EXISTS `master_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_unit` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(20) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `unit_category` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `unit_code` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_approval_history`
--

DROP TABLE IF EXISTS `tbl_approval_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_approval_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `fppb_id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `action` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `action_timestamp` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `fk_history_fppb` (`fppb_id`),
  KEY `fk_history_actor` (`actor_id`),
  CONSTRAINT `fk_history_actor` FOREIGN KEY (`actor_id`) REFERENCES `tbl_users` (`user_id`),
  CONSTRAINT `fk_history_fppb` FOREIGN KEY (`fppb_id`) REFERENCES `tbl_fppb_requests` (`fppb_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_approval_queue`
--

DROP TABLE IF EXISTS `tbl_approval_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_approval_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `fppb_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `acted_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `fk_queue_user` (`approver_id`),
  KEY `idx_queue_fppb` (`fppb_id`),
  KEY `idx_queue_status` (`status`),
  CONSTRAINT `fk_queue_fppb` FOREIGN KEY (`fppb_id`) REFERENCES `tbl_fppb_requests` (`fppb_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_queue_user` FOREIGN KEY (`approver_id`) REFERENCES `tbl_users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_approval_rules`
--

DROP TABLE IF EXISTS `tbl_approval_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_approval_rules` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `approval_level` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `fk_rule_department` (`department_id`),
  KEY `fk_rule_role` (`role_id`),
  CONSTRAINT `fk_rule_department` FOREIGN KEY (`department_id`) REFERENCES `master_departments` (`department_id`),
  CONSTRAINT `fk_rule_role` FOREIGN KEY (`role_id`) REFERENCES `master_roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_attachments`
--

DROP TABLE IF EXISTS `tbl_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `fppb_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attachment_id`),
  KEY `fk_attach_user` (`uploaded_by`),
  KEY `fk_attach_fppb` (`fppb_id`),
  CONSTRAINT `fk_attach_fppb` FOREIGN KEY (`fppb_id`) REFERENCES `tbl_fppb_requests` (`fppb_id`) ON DELETE NO ACTION,
  CONSTRAINT `fk_attach_user` FOREIGN KEY (`uploaded_by`) REFERENCES `tbl_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_fppb_items`
--

DROP TABLE IF EXISTS `tbl_fppb_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_fppb_items` (
  `item_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `fppb_id` int(11) NOT NULL,
  `is_from_master` tinyint(1) DEFAULT 0,
  `barcode_confirmed` tinyint(1) DEFAULT 0,
  `master_assets_id` int(11) DEFAULT NULL,
  `assets_name` varchar(255) DEFAULT NULL,
  `assets_description` varchar(255) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_detail_id`),
  KEY `idx_fppbitems_fppbid` (`fppb_id`),
  KEY `fk_items_master` (`master_assets_id`),
  CONSTRAINT `fk_items_fppb` FOREIGN KEY (`fppb_id`) REFERENCES `tbl_fppb_requests` (`fppb_id`),
  CONSTRAINT `fk_items_master` FOREIGN KEY (`master_assets_id`) REFERENCES `master_assets` (`master_assets_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_fppb_requests`
--

DROP TABLE IF EXISTS `tbl_fppb_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_fppb_requests` (
  `fppb_id` int(11) NOT NULL AUTO_INCREMENT,
  `fppb_number` varchar(50) DEFAULT NULL,
  `requester_id` int(11) NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `origin_location_id` int(11) DEFAULT NULL,
  `destination_location_id` int(11) DEFAULT NULL,
  `date_needed` date DEFAULT NULL,
  `necessity` text DEFAULT NULL,
  `surat_jalan_number` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `fppb_hash` char(64) DEFAULT NULL,
  `is_urgent` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`fppb_id`),
  UNIQUE KEY `fppb_number` (`fppb_number`),
  KEY `fk_fppb_origin` (`origin_location_id`),
  KEY `fk_fppb_destination` (`destination_location_id`),
  KEY `fk_fppb_approved_by` (`approved_by`),
  KEY `idx_fppb_status` (`status`),
  KEY `idx_fppb_requester` (`requester_id`),
  KEY `idx_fppb_date` (`created_at`),
  CONSTRAINT `fk_fppb_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `tbl_users` (`user_id`),
  CONSTRAINT `fk_fppb_destination` FOREIGN KEY (`destination_location_id`) REFERENCES `master_locations` (`location_id`),
  CONSTRAINT `fk_fppb_origin` FOREIGN KEY (`origin_location_id`) REFERENCES `master_locations` (`location_id`),
  CONSTRAINT `fk_fppb_requester` FOREIGN KEY (`requester_id`) REFERENCES `tbl_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`db_fppb`@`%`*/ /*!50003 TRIGGER trg_auto_approval
BEFORE UPDATE ON tbl_fppb_requests
FOR EACH ROW
BEGIN
    IF NEW.status = 'APPROVED' AND OLD.status <> 'APPROVED' THEN
        SET NEW.approved_at = NOW();
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tbl_manual_sj`
--

DROP TABLE IF EXISTS `tbl_manual_sj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_manual_sj` (
  `msj_id` int(11) NOT NULL AUTO_INCREMENT,
  `fppb_id` int(11) NOT NULL,
  `msj_date` date NOT NULL,
  `msj_no` varchar(50) NOT NULL,
  `msj_driver` varchar(50) NOT NULL,
  `msj_vn` varchar(50) NOT NULL,
  `msj_delivery_date` date NOT NULL,
  `msj_sender` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`msj_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_notifications`
--

DROP TABLE IF EXISTS `tbl_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `fk_notif_user` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_surat_jalan`
--

DROP TABLE IF EXISTS `tbl_surat_jalan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_surat_jalan` (
  `surat_jalan_id` int(11) NOT NULL AUTO_INCREMENT,
  `surat_jalan_number` varchar(50) DEFAULT NULL,
  `fppb_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_address` text DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `receiver_address` text DEFAULT NULL,
  `sender_user_id` int(11) DEFAULT NULL,
  `receiver_user_id` int(11) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `expedition_name` varchar(100) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Draft',
  `surat_jalan_hash` char(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`surat_jalan_id`),
  UNIQUE KEY `surat_jalan_number` (`surat_jalan_number`),
  KEY `fk_sj_fppb` (`fppb_id`),
  KEY `fk_sj_created` (`created_by`),
  KEY `fk_sj_sender_user` (`sender_user_id`),
  KEY `fk_sj_receiver_user` (`receiver_user_id`),
  CONSTRAINT `fk_sj_created` FOREIGN KEY (`created_by`) REFERENCES `tbl_users` (`user_id`),
  CONSTRAINT `fk_sj_fppb` FOREIGN KEY (`fppb_id`) REFERENCES `tbl_fppb_requests` (`fppb_id`),
  CONSTRAINT `fk_sj_receiver_user` FOREIGN KEY (`receiver_user_id`) REFERENCES `tbl_users` (`user_id`),
  CONSTRAINT `fk_sj_sender_user` FOREIGN KEY (`sender_user_id`) REFERENCES `tbl_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_surat_jalan_items`
--

DROP TABLE IF EXISTS `tbl_surat_jalan_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_surat_jalan_items` (
  `sj_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `surat_jalan_id` int(11) NOT NULL,
  `fppb_item_id` int(11) NOT NULL,
  `qty_requested` decimal(12,2) DEFAULT 0.00,
  `qty_sent` decimal(12,2) DEFAULT 0.00,
  `qty_remaining` decimal(12,2) DEFAULT 0.00,
  `is_checked` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sj_item_id`),
  KEY `fk_sjitems_sj` (`surat_jalan_id`),
  KEY `fk_sjitems_fppb` (`fppb_item_id`),
  CONSTRAINT `fk_sjitems_fppb` FOREIGN KEY (`fppb_item_id`) REFERENCES `tbl_fppb_items` (`item_detail_id`),
  CONSTRAINT `fk_sjitems_sj` FOREIGN KEY (`surat_jalan_id`) REFERENCES `tbl_surat_jalan` (`surat_jalan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_user_logs`
--

DROP TABLE IF EXISTS `tbl_user_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_user_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `fk_logs_user` (`user_id`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 4,
  `department_id` int(11) NOT NULL DEFAULT 1,
  `manager_id` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_login_attempt` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_manager` (`manager_id`),
  KEY `idx_users_department` (`department_id`),
  CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `master_departments` (`department_id`),
  CONSTRAINT `fk_users_manager` FOREIGN KEY (`manager_id`) REFERENCES `tbl_users` (`user_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `master_roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_users_uggroups`
--

DROP TABLE IF EXISTS `tbl_users_uggroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_users_uggroups` (
  `GroupID` int(11) NOT NULL AUTO_INCREMENT,
  `Label` varchar(300) DEFAULT NULL,
  `Provider` varchar(10) DEFAULT '',
  `Comment` longtext DEFAULT NULL,
  PRIMARY KEY (`GroupID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_users_ugmembers`
--

DROP TABLE IF EXISTS `tbl_users_ugmembers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_users_ugmembers` (
  `UserName` varchar(100) NOT NULL,
  `GroupID` int(11) NOT NULL,
  `Provider` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`UserName`,`GroupID`,`Provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_users_ugrights`
--

DROP TABLE IF EXISTS `tbl_users_ugrights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_users_ugrights` (
  `TableName` varchar(100) NOT NULL,
  `GroupID` int(11) NOT NULL,
  `AccessMask` varchar(10) DEFAULT NULL,
  `Page` longtext DEFAULT NULL,
  PRIMARY KEY (`TableName`,`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-14 11:01:46
