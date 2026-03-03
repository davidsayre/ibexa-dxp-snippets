/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: db    Database: dxp5_fresh
-- ------------------------------------------------------
-- Server version	8.4.6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `ibexa_action_configuration`
--

DROP TABLE IF EXISTS `ibexa_action_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_action_configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `action_type_options` json DEFAULT NULL,
  `action_handler_options` json DEFAULT NULL,
  `action_handler_identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_action_configuration_identifier_uc` (`identifier`),
  KEY `ibexa_action_configuration_identifier_idx` (`identifier`),
  KEY `ibexa_action_configuration_enabled_idx` (`enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_action_configuration_ml`
--

DROP TABLE IF EXISTS `ibexa_action_configuration_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_action_configuration_ml` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action_configuration_id` int NOT NULL,
  `language_id` bigint NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_action_configuration_ml_uidx` (`action_configuration_id`,`language_id`),
  KEY `ibexa_action_configuration_ml_name_idx` (`name`),
  KEY `ibexa_action_configuration_ml_language_idx` (`language_id`),
  KEY `ibexa_action_configuration_ml_action_configuration_idx` (`action_configuration_id`),
  CONSTRAINT `ibexa_action_configuration_ml_to_action_configuration_fk` FOREIGN KEY (`action_configuration_id`) REFERENCES `ibexa_action_configuration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_action_configuration_ml_to_language_fk` FOREIGN KEY (`language_id`) REFERENCES `ibexa_content_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_activity_log`
--

DROP TABLE IF EXISTS `ibexa_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_activity_log` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `object_class_id` int NOT NULL,
  `action_id` int NOT NULL,
  `group_id` bigint NOT NULL,
  `object_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `object_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `data` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_activity_log_object_class_idx` (`object_class_id`),
  KEY `ibexa_activity_log_action_idx` (`action_id`),
  KEY `ibexa_activity_log_object_idx` (`object_id`),
  KEY `ibexa_activity_log_object_name_idx` (`object_name`),
  KEY `ibexa_activity_log_group_idx` (`group_id`),
  CONSTRAINT `ibexa_activity_log_action_fk` FOREIGN KEY (`action_id`) REFERENCES `ibexa_activity_log_action` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `ibexa_activity_log_group_fk` FOREIGN KEY (`group_id`) REFERENCES `ibexa_activity_log_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_activity_log_object_class_fk` FOREIGN KEY (`object_class_id`) REFERENCES `ibexa_activity_log_object_class` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_activity_log_action`
--

DROP TABLE IF EXISTS `ibexa_activity_log_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_activity_log_action` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_activity_log_action_uidx` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_activity_log_group`
--

DROP TABLE IF EXISTS `ibexa_activity_log_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_activity_log_group` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `source_id` int DEFAULT NULL,
  `ip_id` int DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `logged_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_activity_log_group_source_idx` (`source_id`),
  KEY `ibexa_activity_log_ip_idx` (`ip_id`),
  KEY `ibexa_activity_log_logged_at_idx` (`logged_at`),
  KEY `ibexa_activity_log_user_id_idx` (`user_id`),
  CONSTRAINT `ibexa_activity_log_group_source_fk` FOREIGN KEY (`source_id`) REFERENCES `ibexa_activity_log_group_source` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `ibexa_activity_log_ip_fk` FOREIGN KEY (`ip_id`) REFERENCES `ibexa_activity_log_ip` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_activity_log_group_source`
--

DROP TABLE IF EXISTS `ibexa_activity_log_group_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_activity_log_group_source` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_activity_log_group_source_name_uidx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_activity_log_ip`
--

DROP TABLE IF EXISTS `ibexa_activity_log_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_activity_log_ip` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_activity_log_group_source_ip_uidx` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_activity_log_object_class`
--

DROP TABLE IF EXISTS `ibexa_activity_log_object_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_activity_log_object_class` (
  `id` int NOT NULL AUTO_INCREMENT,
  `object_class` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_activity_log_object_class_uidx` (`object_class`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_attribute_definition`
--

DROP TABLE IF EXISTS `ibexa_attribute_definition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_attribute_definition` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int NOT NULL,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `options` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_definition_identifier_idx` (`identifier`),
  KEY `ibexa_attribute_definition_attribute_group_idx` (`attribute_group_id`),
  CONSTRAINT `ibexa_attribute_definition_attribute_group_fk` FOREIGN KEY (`attribute_group_id`) REFERENCES `ibexa_attribute_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_attribute_definition_assignment`
--

DROP TABLE IF EXISTS `ibexa_attribute_definition_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_attribute_definition_assignment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attribute_definition_id` int NOT NULL,
  `field_definition_id` int NOT NULL,
  `status` int NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `discriminator` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_attribute_definition_assignment_main_uidx` (`field_definition_id`,`status`,`attribute_definition_id`),
  KEY `ibexa_attribute_definition_assignment_field_definition_idx` (`field_definition_id`),
  KEY `ibexa_attribute_definition_assignment_attribute_definition_idx` (`attribute_definition_id`),
  CONSTRAINT `ibexa_attribute_definition_assignment_attribute_definition_fk` FOREIGN KEY (`attribute_definition_id`) REFERENCES `ibexa_attribute_definition` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_attribute_definition_ml`
--

DROP TABLE IF EXISTS `ibexa_attribute_definition_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_attribute_definition_ml` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attribute_definition_id` int NOT NULL,
  `language_id` bigint NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name_normalized` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_attribute_definition_ml_uidx` (`attribute_definition_id`,`language_id`),
  KEY `ibexa_attribute_definition_ml_attribute_definition_idx` (`attribute_definition_id`),
  KEY `ibexa_attribute_definition_ml_language_idx` (`language_id`),
  KEY `ibexa_attribute_definition_ml_name_idx` (`name_normalized`),
  CONSTRAINT `ibexa_attribute_definition_ml_attribute_definition_fk` FOREIGN KEY (`attribute_definition_id`) REFERENCES `ibexa_attribute_definition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_attribute_definition_ml_language_fk` FOREIGN KEY (`language_id`) REFERENCES `ibexa_content_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_attribute_group`
--

DROP TABLE IF EXISTS `ibexa_attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_attribute_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_attribute_group_identifier_uidx` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_attribute_group_ml`
--

DROP TABLE IF EXISTS `ibexa_attribute_group_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_attribute_group_ml` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int NOT NULL,
  `language_id` bigint NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name_normalized` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_group_ml_idx` (`attribute_group_id`,`language_id`),
  KEY `ibexa_attribute_group_ml_attribute_group_idx` (`attribute_group_id`),
  KEY `ibexa_attribute_group_ml_language_idx` (`language_id`),
  KEY `ibexa_attribute_group_ml_name_idx` (`name_normalized`),
  CONSTRAINT `ibexa_attribute_group_ml_attribute_group_fk` FOREIGN KEY (`attribute_group_id`) REFERENCES `ibexa_attribute_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_attribute_group_ml_language_fk` FOREIGN KEY (`language_id`) REFERENCES `ibexa_content_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_binary_file`
--

DROP TABLE IF EXISTS `ibexa_binary_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_binary_file` (
  `contentobject_attribute_id` int NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '0',
  `download_count` int NOT NULL DEFAULT '0',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`contentobject_attribute_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_catalog`
--

DROP TABLE IF EXISTS `ibexa_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_catalog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `creator_id` int NOT NULL,
  `created` int NOT NULL,
  `modified` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'draft',
  `query_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_catalog_identifier_idx` (`identifier`),
  KEY `ibexa_catalog_creator_idx` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_catalog_ml`
--

DROP TABLE IF EXISTS `ibexa_catalog_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_catalog_ml` (
  `id` int NOT NULL AUTO_INCREMENT,
  `catalog_id` int NOT NULL,
  `language_id` bigint NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name_normalized` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_catalog_ml_catalog_language_uidx` (`catalog_id`,`language_id`),
  KEY `ibexa_catalog_catalog_idx` (`catalog_id`),
  KEY `ibexa_catalog_language_idx` (`language_id`),
  CONSTRAINT `ibexa_catalog_ml_fk` FOREIGN KEY (`catalog_id`) REFERENCES `ibexa_catalog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_collaboration`
--

DROP TABLE IF EXISTS `ibexa_collaboration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `token` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `discriminator` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `has_public_link` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_collaboration_token_idx` (`token`),
  UNIQUE KEY `ibexa_collaboration_token_uc` (`token`),
  KEY `ibexa_collaboration_owner_idx` (`owner_id`),
  CONSTRAINT `ibexa_collaboration_owner_id_fk` FOREIGN KEY (`owner_id`) REFERENCES `ibexa_user` (`contentobject_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_collaboration_content`
--

DROP TABLE IF EXISTS `ibexa_collaboration_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_content` (
  `id` int NOT NULL,
  `content_id` int NOT NULL,
  `version_no` int NOT NULL,
  `language_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_collaboration_session_content_version_language_idx` (`content_id`,`version_no`,`language_id`),
  CONSTRAINT `ibexa_collaboration_content_pk` FOREIGN KEY (`id`) REFERENCES `ibexa_collaboration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_collaboration_invitation`
--

DROP TABLE IF EXISTS `ibexa_collaboration_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_invitation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `status` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `context` json DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_36C63687613FECDF` (`session_id`),
  KEY `IDX_36C636879D1C3019` (`participant_id`),
  KEY `IDX_36C63687F624B39D` (`sender_id`),
  CONSTRAINT `ibexa_collaboration_invitation_participant_id_fk` FOREIGN KEY (`participant_id`) REFERENCES `ibexa_collaboration_participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_collaboration_invitation_sender_id_fk` FOREIGN KEY (`sender_id`) REFERENCES `ibexa_user` (`contentobject_id`) ON DELETE RESTRICT,
  CONSTRAINT `ibexa_collaboration_invitation_session_id_fk` FOREIGN KEY (`session_id`) REFERENCES `ibexa_collaboration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_collaboration_participant`
--

DROP TABLE IF EXISTS `ibexa_collaboration_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_participant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `discriminator` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_collaboration_participant_token_idx` (`token`),
  KEY `IDX_9C5C6401613FECDF` (`session_id`),
  CONSTRAINT `ibexa_collaboration_participant_session_id_fk` FOREIGN KEY (`session_id`) REFERENCES `ibexa_collaboration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_collaboration_participant_external`
--

DROP TABLE IF EXISTS `ibexa_collaboration_participant_external`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_participant_external` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ibexa_collaboration_participant_external_pk` FOREIGN KEY (`id`) REFERENCES `ibexa_collaboration_participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_collaboration_participant_internal`
--

DROP TABLE IF EXISTS `ibexa_collaboration_participant_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_participant_internal` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E838B79AA76ED395` (`user_id`),
  CONSTRAINT `ibexa_collaboration_participant_internal_pk` FOREIGN KEY (`id`) REFERENCES `ibexa_collaboration_participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_collaboration_participant_internal_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `ibexa_user` (`contentobject_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content`
--

DROP TABLE IF EXISTS `ibexa_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_type_id` int NOT NULL DEFAULT '0',
  `current_version` int DEFAULT NULL,
  `initial_language_id` bigint NOT NULL DEFAULT '0',
  `language_mask` bigint NOT NULL DEFAULT '0',
  `modified` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `owner_id` int NOT NULL DEFAULT '0',
  `published` int NOT NULL DEFAULT '0',
  `remote_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `section_id` int NOT NULL DEFAULT '0',
  `status` int DEFAULT '0',
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_content_remote_id` (`remote_id`),
  KEY `ibexa_content_type_id` (`content_type_id`),
  KEY `ibexa_content_lmask` (`language_mask`),
  KEY `ibexa_content_pub` (`published`),
  KEY `ibexa_content_section` (`section_id`),
  KEY `ibexa_content_currentversion` (`current_version`),
  KEY `ibexa_content_owner` (`owner_id`),
  KEY `ibexa_content_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_bookmark`
--

DROP TABLE IF EXISTS `ibexa_content_bookmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_bookmark` (
  `id` int NOT NULL AUTO_INCREMENT,
  `node_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ibexa_content_bookmark_location` (`node_id`),
  KEY `ibexa_content_bookmark_user` (`user_id`),
  KEY `ibexa_content_bookmark_user_location` (`user_id`,`node_id`),
  CONSTRAINT `ibexa_content_bookmark_location_fk` FOREIGN KEY (`node_id`) REFERENCES `ibexa_content_tree` (`node_id`) ON DELETE CASCADE,
  CONSTRAINT `ibexa_content_bookmark_user_fk` FOREIGN KEY (`user_id`) REFERENCES `ibexa_user` (`contentobject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_customer_group`
--

DROP TABLE IF EXISTS `ibexa_content_customer_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_customer_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_id` int NOT NULL,
  `field_version_no` int NOT NULL,
  `customer_group_id` int NOT NULL,
  `content_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_content_customer_group_attribute_uidx` (`field_id`,`field_version_no`),
  KEY `ibexa_content_customer_group_customer_group_idx` (`customer_group_id`),
  CONSTRAINT `ibexa_content_customer_group_attribute_fk` FOREIGN KEY (`field_id`, `field_version_no`) REFERENCES `ibexa_content_field` (`id`, `version`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_content_customer_group_customer_group_fk` FOREIGN KEY (`customer_group_id`) REFERENCES `ibexa_customer_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_field`
--

DROP TABLE IF EXISTS `ibexa_content_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_field` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` int NOT NULL DEFAULT '0',
  `attribute_original_id` int DEFAULT '0',
  `content_type_field_definition_id` int NOT NULL DEFAULT '0',
  `contentobject_id` int NOT NULL DEFAULT '0',
  `data_float` double DEFAULT NULL,
  `data_int` int DEFAULT NULL,
  `data_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `data_type_string` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `language_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `language_id` bigint NOT NULL DEFAULT '0',
  `sort_key_int` int NOT NULL DEFAULT '0',
  `sort_key_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`version`),
  KEY `ibexa_content_field_co_id_ver_lang_code` (`contentobject_id`,`version`,`language_code`),
  KEY `ibexa_content_field_field_definition_id` (`content_type_field_definition_id`),
  KEY `sort_key_string` (`sort_key_string`(191)),
  KEY `ibexa_content_field_language_code` (`language_code`),
  KEY `sort_key_int` (`sort_key_int`),
  KEY `ibexa_content_field_co_id_ver` (`contentobject_id`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_language`
--

DROP TABLE IF EXISTS `ibexa_content_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_language` (
  `id` bigint NOT NULL DEFAULT '0',
  `disabled` int NOT NULL DEFAULT '0',
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ibexa_content_language_name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_name`
--

DROP TABLE IF EXISTS `ibexa_content_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_name` (
  `contentobject_id` int NOT NULL DEFAULT '0',
  `content_version` int NOT NULL DEFAULT '0',
  `content_translation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `language_id` bigint NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `real_translation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`contentobject_id`,`content_version`,`content_translation`),
  KEY `ibexa_content_name_lang_id` (`language_id`),
  KEY `ibexa_content_name_cov_id` (`content_version`),
  KEY `ibexa_content_name_name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_relation`
--

DROP TABLE IF EXISTS `ibexa_content_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_relation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_type_field_definition_id` int NOT NULL DEFAULT '0',
  `from_contentobject_id` int NOT NULL DEFAULT '0',
  `from_contentobject_version` int NOT NULL DEFAULT '0',
  `relation_type` int NOT NULL DEFAULT '1',
  `to_contentobject_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ibexa_content_relation_to_co_id` (`to_contentobject_id`),
  KEY `ibexa_content_relation_from` (`from_contentobject_id`,`from_contentobject_version`,`content_type_field_definition_id`),
  KEY `ibexa_content_relation_ccfd_id` (`content_type_field_definition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_trash`
--

DROP TABLE IF EXISTS `ibexa_content_trash`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_trash` (
  `node_id` int NOT NULL DEFAULT '0',
  `contentobject_id` int DEFAULT NULL,
  `contentobject_version` int DEFAULT NULL,
  `depth` int NOT NULL DEFAULT '0',
  `is_hidden` int NOT NULL DEFAULT '0',
  `is_invisible` int NOT NULL DEFAULT '0',
  `main_node_id` int DEFAULT NULL,
  `modified_subnode` int DEFAULT '0',
  `parent_node_id` int NOT NULL DEFAULT '0',
  `path_identification_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `path_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `priority` int NOT NULL DEFAULT '0',
  `remote_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `sort_field` int DEFAULT '1',
  `sort_order` int DEFAULT '1',
  `trashed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`),
  KEY `ibexa_content_trash_depth` (`depth`),
  KEY `ibexa_content_trash_p_node_id` (`parent_node_id`),
  KEY `ibexa_content_trash_path_ident` (`path_identification_string`(50)),
  KEY `ibexa_content_trash_co_id` (`contentobject_id`),
  KEY `ibexa_content_trash_modified_subnode` (`modified_subnode`),
  KEY `ibexa_content_trash_path` (`path_string`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_tree`
--

DROP TABLE IF EXISTS `ibexa_content_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_tree` (
  `node_id` int NOT NULL AUTO_INCREMENT,
  `contentobject_id` int DEFAULT NULL,
  `contentobject_is_published` int DEFAULT NULL,
  `contentobject_version` int DEFAULT NULL,
  `depth` int NOT NULL DEFAULT '0',
  `is_hidden` int NOT NULL DEFAULT '0',
  `is_invisible` int NOT NULL DEFAULT '0',
  `main_node_id` int DEFAULT NULL,
  `modified_subnode` int DEFAULT '0',
  `parent_node_id` int NOT NULL DEFAULT '0',
  `path_identification_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `path_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `priority` int NOT NULL DEFAULT '0',
  `remote_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `sort_field` int DEFAULT '1',
  `sort_order` int DEFAULT '1',
  PRIMARY KEY (`node_id`),
  KEY `ibexa_content_tree_p_node_id` (`parent_node_id`),
  KEY `ibexa_content_tree_path_ident` (`path_identification_string`(50)),
  KEY `ibexa_content_tree_contentobject_id_path_string` (`path_string`(191),`contentobject_id`),
  KEY `ibexa_content_tree_co_id` (`contentobject_id`),
  KEY `ibexa_content_tree_depth` (`depth`),
  KEY `ibexa_content_tree_path` (`path_string`(191)),
  KEY `ibexa_content_modified_subnode` (`modified_subnode`),
  KEY `ibexa_content_tree_remote_id` (`remote_id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_type`
--

DROP TABLE IF EXISTS `ibexa_content_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` int NOT NULL DEFAULT '0',
  `always_available` int NOT NULL DEFAULT '0',
  `contentobject_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created` int NOT NULL DEFAULT '0',
  `creator_id` int NOT NULL DEFAULT '0',
  `identifier` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `initial_language_id` bigint NOT NULL DEFAULT '0',
  `is_container` int NOT NULL DEFAULT '0',
  `language_mask` bigint NOT NULL DEFAULT '0',
  `modified` int NOT NULL DEFAULT '0',
  `modifier_id` int NOT NULL DEFAULT '0',
  `remote_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `serialized_description_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `serialized_name_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `sort_field` int NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '1',
  `url_alias_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`,`status`),
  KEY `ibexa_content_type_status` (`status`),
  KEY `ibexa_content_type_identifier` (`identifier`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_type_field_definition`
--

DROP TABLE IF EXISTS `ibexa_content_type_field_definition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_type_field_definition` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` int NOT NULL DEFAULT '0',
  `can_translate` int DEFAULT '1',
  `category` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `content_type_id` int NOT NULL DEFAULT '0',
  `data_float1` double DEFAULT NULL,
  `data_float2` double DEFAULT NULL,
  `data_float3` double DEFAULT NULL,
  `data_float4` double DEFAULT NULL,
  `data_int1` int DEFAULT NULL,
  `data_int2` int DEFAULT NULL,
  `data_int3` int DEFAULT NULL,
  `data_int4` int DEFAULT NULL,
  `data_text1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `data_text2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `data_text3` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `data_text4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `data_text5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `data_type_string` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `identifier` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `is_information_collector` int NOT NULL DEFAULT '0',
  `is_required` int NOT NULL DEFAULT '0',
  `is_searchable` int NOT NULL DEFAULT '0',
  `is_thumbnail` tinyint(1) NOT NULL DEFAULT '0',
  `placement` int NOT NULL DEFAULT '0',
  `serialized_data_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `serialized_description_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `serialized_name_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`,`status`),
  KEY `ibexa_content_type_field_definition_ctid` (`content_type_id`),
  KEY `ibexa_content_type_field_definition_dts` (`data_type_string`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_type_field_definition_ml`
--

DROP TABLE IF EXISTS `ibexa_content_type_field_definition_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_type_field_definition_ml` (
  `content_type_field_definition_id` int NOT NULL,
  `status` int NOT NULL,
  `language_id` bigint NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `data_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `data_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`content_type_field_definition_id`,`status`,`language_id`),
  KEY `ibexa_content_type_field_definition_ml_lang_fk` (`language_id`),
  CONSTRAINT `ibexa_content_type_field_definition_ml_lang_fk` FOREIGN KEY (`language_id`) REFERENCES `ibexa_content_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_type_group`
--

DROP TABLE IF EXISTS `ibexa_content_type_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_type_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created` int NOT NULL DEFAULT '0',
  `creator_id` int NOT NULL DEFAULT '0',
  `modified` int NOT NULL DEFAULT '0',
  `modifier_id` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_type_group_assignment`
--

DROP TABLE IF EXISTS `ibexa_content_type_group_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_type_group_assignment` (
  `content_type_id` int NOT NULL DEFAULT '0',
  `content_type_status` int NOT NULL DEFAULT '0',
  `group_id` int NOT NULL DEFAULT '0',
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`content_type_id`,`content_type_status`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_type_name`
--

DROP TABLE IF EXISTS `ibexa_content_type_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_type_name` (
  `content_type_id` int NOT NULL DEFAULT '0',
  `content_type_status` int NOT NULL DEFAULT '0',
  `language_id` bigint NOT NULL DEFAULT '0',
  `language_locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`content_type_id`,`content_type_status`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_content_version`
--

DROP TABLE IF EXISTS `ibexa_content_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_content_version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contentobject_id` int DEFAULT NULL,
  `created` int NOT NULL DEFAULT '0',
  `creator_id` int NOT NULL DEFAULT '0',
  `initial_language_id` bigint NOT NULL DEFAULT '0',
  `language_mask` bigint NOT NULL DEFAULT '0',
  `modified` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '0',
  `workflow_event_pos` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ibexa_content_version_status` (`status`),
  KEY `ibexa_content_version_idx_ver` (`contentobject_id`,`version`),
  KEY `ibexa_content_version_idx_status` (`contentobject_id`,`status`),
  KEY `ibexa_content_version_creator_id` (`creator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=537 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_corporate_application_state`
--

DROP TABLE IF EXISTS `ibexa_corporate_application_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_corporate_application_state` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `state` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_corporate_application_state_application_unique` (`application_id`),
  UNIQUE KEY `ibexa_corporate_application_state_company_unique` (`company_id`),
  KEY `ibexa_corporate_application_state_state_idx` (`state`),
  CONSTRAINT `ibexa_corporate_application_state_application_fk` FOREIGN KEY (`application_id`) REFERENCES `ibexa_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_corporate_application_state_company_fk` FOREIGN KEY (`company_id`) REFERENCES `ibexa_content` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_corporate_company_history`
--

DROP TABLE IF EXISTS `ibexa_corporate_company_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_corporate_company_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `event_data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_corporate_company_history_company_idx` (`company_id`),
  KEY `ibexa_corporate_company_history_application_idx` (`application_id`),
  KEY `ibexa_corporate_company_history_user_idx` (`user_id`),
  CONSTRAINT `ibexa_corporate_company_history_application_fk` FOREIGN KEY (`application_id`) REFERENCES `ibexa_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_corporate_company_history_company_fk` FOREIGN KEY (`company_id`) REFERENCES `ibexa_content` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ibexa_corporate_company_history_user_fk` FOREIGN KEY (`user_id`) REFERENCES `ibexa_content` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_corporate_member_assignment`
--

DROP TABLE IF EXISTS `ibexa_corporate_member_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_corporate_member_assignment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `member_role` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `member_role_assignment_id` int NOT NULL,
  `company_id` int NOT NULL,
  `company_location_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_corporate_member_assignment_unique_idx` (`member_id`,`company_id`),
  KEY `ibexa_corporate_member_assignment_member_idx` (`member_id`),
  KEY `ibexa_corporate_member_assignment_company_idx` (`company_id`),
  KEY `ibexa_corporate_member_assignment_company_location_idx` (`company_location_id`),
  KEY `ibexa_corporate_member_assignment_member_role_assignment_idx` (`member_role_assignment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_currency`
--

DROP TABLE IF EXISTS `ibexa_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_currency` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `subunits` smallint NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_currency_code_idx` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_customer_group`
--

DROP TABLE IF EXISTS `ibexa_customer_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_customer_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `global_price_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_customer_group_identifier_idx` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_customer_group_ml`
--

DROP TABLE IF EXISTS `ibexa_customer_group_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_customer_group_ml` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_group_id` int NOT NULL,
  `language_id` bigint NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name_normalized` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_customer_group_ml_customer_group_language_uidx` (`customer_group_id`,`language_id`),
  KEY `ibexa_customer_group_idx` (`customer_group_id`),
  KEY `ibexa_language_idx` (`language_id`),
  CONSTRAINT `ibexa_customer_group__ml_fk` FOREIGN KEY (`customer_group_id`) REFERENCES `ibexa_customer_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_dfs_file`
--

DROP TABLE IF EXISTS `ibexa_dfs_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_dfs_file` (
  `name_hash` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name_trunk` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `datatype` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'application/octet-stream',
  `scope` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `size` bigint unsigned NOT NULL DEFAULT '0',
  `mtime` int NOT NULL DEFAULT '0',
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name_hash`),
  KEY `ibexa_dfs_file_name_trunk` (`name_trunk`(191)),
  KEY `ibexa_dfs_file_expired_name` (`expired`,`name`(191)),
  KEY `ibexa_dfs_file_name` (`name`(191)),
  KEY `ibexa_dfs_file_mtime` (`mtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_form`
--

DROP TABLE IF EXISTS `ibexa_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_form` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_id` int DEFAULT NULL,
  `version_no` int DEFAULT NULL,
  `content_field_id` int DEFAULT NULL,
  `language_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_form_field`
--

DROP TABLE IF EXISTS `ibexa_form_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_form_field` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_id` int DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `identifier` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_form_field_attribute`
--

DROP TABLE IF EXISTS `ibexa_form_field_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_form_field_attribute` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_id` int DEFAULT NULL,
  `identifier` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `ibexa_form_fa_f_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_form_field_validator`
--

DROP TABLE IF EXISTS `ibexa_form_field_validator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_form_field_validator` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_id` int DEFAULT NULL,
  `identifier` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `ibexa_form_fv_f_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_form_submission`
--

DROP TABLE IF EXISTS `ibexa_form_submission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_form_submission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_id` int NOT NULL,
  `language_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_id` int NOT NULL,
  `created` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_form_fs_cid_lc` (`content_id`,`language_code`),
  KEY `ibexa_form_fs_cid_lc_cr_id_uid` (`content_id`,`language_code`,`created`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_form_submission_data`
--

DROP TABLE IF EXISTS `ibexa_form_submission_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_form_submission_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_submission_id` int NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `identifier` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `ibexa_form_fsd_si` (`form_submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_image_file`
--

DROP TABLE IF EXISTS `ibexa_image_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_image_file` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contentobject_attribute_id` int NOT NULL DEFAULT '0',
  `filepath` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_image_file_file` (`filepath`(191)),
  KEY `ibexa_image_file_coid` (`contentobject_attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_keyword`
--

DROP TABLE IF EXISTS `ibexa_keyword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_keyword` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL DEFAULT '0',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_keyword_keyword` (`keyword`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_keyword_field_link`
--

DROP TABLE IF EXISTS `ibexa_keyword_field_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_keyword_field_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `keyword_id` int NOT NULL DEFAULT '0',
  `objectattribute_id` int NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ibexa_keyword_field_link_oaid` (`objectattribute_id`),
  KEY `ibexa_keyword_field_link_kid_oaid` (`keyword_id`,`objectattribute_id`),
  KEY `ibexa_keyword_field_link_oaid_ver` (`objectattribute_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_map_location`
--

DROP TABLE IF EXISTS `ibexa_map_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_map_location` (
  `contentobject_attribute_id` int NOT NULL DEFAULT '0',
  `contentobject_version` int NOT NULL DEFAULT '0',
  `latitude` double NOT NULL DEFAULT '0',
  `longitude` double NOT NULL DEFAULT '0',
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`contentobject_attribute_id`,`contentobject_version`),
  KEY `ibexa_map_location_latitude_longitude_key` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_measurement_range_value`
--

DROP TABLE IF EXISTS `ibexa_measurement_range_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_measurement_range_value` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_field_id` int NOT NULL,
  `version_no` int NOT NULL,
  `unit_id` int NOT NULL,
  `min_value` double NOT NULL,
  `max_value` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_measurement_range_value_attr_ver` (`content_field_id`,`version_no`),
  UNIQUE KEY `ibexa_measurement_range_value_attr_ver_type_unit` (`content_field_id`,`version_no`,`unit_id`),
  KEY `IDX_90D7F03DF8BD700D` (`unit_id`),
  CONSTRAINT `ibexa_measurement_range_value_attr_fk` FOREIGN KEY (`content_field_id`, `version_no`) REFERENCES `ibexa_content_field` (`id`, `version`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_measurement_range_value_unit_fk` FOREIGN KEY (`unit_id`) REFERENCES `ibexa_measurement_unit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_measurement_type`
--

DROP TABLE IF EXISTS `ibexa_measurement_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_measurement_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_measurement_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_measurement_unit`
--

DROP TABLE IF EXISTS `ibexa_measurement_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_measurement_unit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `identifier` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_measurement_unit_type_identifier` (`type_id`,`identifier`),
  KEY `IDX_7105A57BC54C8C93` (`type_id`),
  CONSTRAINT `ibexa_measurement_unit_type_fk` FOREIGN KEY (`type_id`) REFERENCES `ibexa_measurement_type` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_measurement_value`
--

DROP TABLE IF EXISTS `ibexa_measurement_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_measurement_value` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_field_id` int NOT NULL,
  `version_no` int NOT NULL,
  `unit_id` int NOT NULL,
  `value` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_measurement_value_attr_ver` (`content_field_id`,`version_no`),
  UNIQUE KEY `ibexa_measurement_value_attr_ver_unit` (`content_field_id`,`version_no`,`unit_id`),
  KEY `IDX_286F4E67F8BD700D` (`unit_id`),
  CONSTRAINT `ibexa_measurement_value_attr_fk` FOREIGN KEY (`content_field_id`, `version_no`) REFERENCES `ibexa_content_field` (`id`, `version`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_measurement_value_unit_fk` FOREIGN KEY (`unit_id`) REFERENCES `ibexa_measurement_unit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_media`
--

DROP TABLE IF EXISTS `ibexa_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_media` (
  `contentobject_attribute_id` int NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '0',
  `controls` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `has_controller` int DEFAULT '0',
  `height` int DEFAULT NULL,
  `is_autoplay` int DEFAULT '0',
  `is_loop` int DEFAULT '0',
  `mime_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `pluginspage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `quality` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `width` int DEFAULT NULL,
  PRIMARY KEY (`contentobject_attribute_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_messenger_lock_keys`
--

DROP TABLE IF EXISTS `ibexa_messenger_lock_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_messenger_lock_keys` (
  `key_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `key_token` varchar(44) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `key_expiration` int unsigned NOT NULL,
  PRIMARY KEY (`key_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_messenger_messages`
--

DROP TABLE IF EXISTS `ibexa_messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_837A775AFB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_migrations`
--

DROP TABLE IF EXISTS `ibexa_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_migrations` (
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_node_assignment`
--

DROP TABLE IF EXISTS `ibexa_node_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_node_assignment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contentobject_id` int DEFAULT NULL,
  `contentobject_version` int DEFAULT NULL,
  `from_node_id` int DEFAULT '0',
  `is_main` int NOT NULL DEFAULT '0',
  `op_code` int NOT NULL DEFAULT '0',
  `parent_node` int DEFAULT NULL,
  `parent_remote_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `remote_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '0',
  `sort_field` int DEFAULT '1',
  `sort_order` int DEFAULT '1',
  `priority` int NOT NULL DEFAULT '0',
  `is_hidden` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ibexa_node_assignment_is_main` (`is_main`),
  KEY `ibexa_node_assignment_coid_cov` (`contentobject_id`,`contentobject_version`),
  KEY `ibexa_node_assignment_parent_node` (`parent_node`),
  KEY `ibexa_node_assignment_co_version` (`contentobject_version`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_notification`
--

DROP TABLE IF EXISTS `ibexa_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL DEFAULT '0',
  `is_pending` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `created` int NOT NULL DEFAULT '0',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `ibexa_notification_owner_is_pending` (`owner_id`,`is_pending`),
  KEY `ibexa_notification_owner` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_object_state`
--

DROP TABLE IF EXISTS `ibexa_object_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state` (
  `id` int NOT NULL AUTO_INCREMENT,
  `default_language_id` bigint NOT NULL DEFAULT '0',
  `group_id` int NOT NULL DEFAULT '0',
  `identifier` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `language_mask` bigint NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_object_state_identifier` (`group_id`,`identifier`),
  KEY `ibexa_object_state_priority` (`priority`),
  KEY `ibexa_object_state_lmask` (`language_mask`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_object_state_group`
--

DROP TABLE IF EXISTS `ibexa_object_state_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `default_language_id` bigint NOT NULL DEFAULT '0',
  `identifier` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `language_mask` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_object_state_group_identifier` (`identifier`),
  KEY `ibexa_object_state_group_lmask` (`language_mask`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_object_state_group_language`
--

DROP TABLE IF EXISTS `ibexa_object_state_group_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_group_language` (
  `contentobject_state_group_id` int NOT NULL DEFAULT '0',
  `real_language_id` bigint NOT NULL DEFAULT '0',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `language_id` bigint NOT NULL DEFAULT '0',
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`contentobject_state_group_id`,`real_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_object_state_language`
--

DROP TABLE IF EXISTS `ibexa_object_state_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_language` (
  `contentobject_state_id` int NOT NULL DEFAULT '0',
  `language_id` bigint NOT NULL DEFAULT '0',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`contentobject_state_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_object_state_link`
--

DROP TABLE IF EXISTS `ibexa_object_state_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_link` (
  `contentobject_id` int NOT NULL DEFAULT '0',
  `contentobject_state_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`contentobject_id`,`contentobject_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_package`
--

DROP TABLE IF EXISTS `ibexa_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_package` (
  `id` int NOT NULL AUTO_INCREMENT,
  `install_date` int NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `version` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page`
--

DROP TABLE IF EXISTS `ibexa_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version_no` int NOT NULL,
  `content_id` int NOT NULL,
  `language_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `layout` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ibexa_page_content_id_version_no` (`content_id`,`version_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_attribute`
--

DROP TABLE IF EXISTS `ibexa_page_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_attribute` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_block`
--

DROP TABLE IF EXISTS `ibexa_page_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_block` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `view` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_block_design`
--

DROP TABLE IF EXISTS `ibexa_page_block_design`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_block_design` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `style` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `compiled` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_page_block_design_block_id` (`block_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_block_visibility`
--

DROP TABLE IF EXISTS `ibexa_page_block_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_block_visibility` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `since` int DEFAULT NULL,
  `till` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_page_block_visibility_block_id` (`block_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_map_attribute_block`
--

DROP TABLE IF EXISTS `ibexa_page_map_attribute_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_map_attribute_block` (
  `attribute_id` int NOT NULL,
  `block_id` int NOT NULL,
  PRIMARY KEY (`attribute_id`,`block_id`),
  KEY `ibexa_page_map_attribute_block_attribute_id` (`attribute_id`),
  KEY `ibexa_page_map_attribute_block_block_id` (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_map_block_zone`
--

DROP TABLE IF EXISTS `ibexa_page_map_block_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_map_block_zone` (
  `block_id` int NOT NULL,
  `zone_id` int NOT NULL,
  PRIMARY KEY (`block_id`,`zone_id`),
  KEY `ibexa_page_map_block_zone_block_id` (`block_id`),
  KEY `ibexa_page_map_block_zone_zone_id` (`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_map_zone_page`
--

DROP TABLE IF EXISTS `ibexa_page_map_zone_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_map_zone_page` (
  `zone_id` int NOT NULL,
  `page_id` int NOT NULL,
  PRIMARY KEY (`zone_id`,`page_id`),
  KEY `ibexa_page_map_zone_page_zone_id` (`zone_id`),
  KEY `ibexa_page_map_zone_page_page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_page_zone`
--

DROP TABLE IF EXISTS `ibexa_page_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_page_zone` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_policy`
--

DROP TABLE IF EXISTS `ibexa_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_policy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `function_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `module_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `original_id` int NOT NULL DEFAULT '0',
  `role_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_policy_role_id` (`role_id`),
  KEY `ibexa_policy_original_id` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=427 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_policy_limitation`
--

DROP TABLE IF EXISTS `ibexa_policy_limitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_policy_limitation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `policy_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_policy_id` (`policy_id`)
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_policy_limitation_value`
--

DROP TABLE IF EXISTS `ibexa_policy_limitation_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_policy_limitation_value` (
  `id` int NOT NULL AUTO_INCREMENT,
  `limitation_id` int DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_policy_limit_value_limit_id` (`limitation_id`),
  KEY `ibexa_policy_limitation_value_val` (`value`(191))
) ENGINE=InnoDB AUTO_INCREMENT=574 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product`
--

DROP TABLE IF EXISTS `ibexa_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `base_product_id` int DEFAULT NULL,
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_product_code_uidx` (`code`),
  KEY `ibexa_product_base_product_idx` (`base_product_id`),
  CONSTRAINT `ibexa_product_base_product_fk` FOREIGN KEY (`base_product_id`) REFERENCES `ibexa_product` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification`
--

DROP TABLE IF EXISTS `ibexa_product_specification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version_no` int DEFAULT NULL,
  `field_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `content_id` int DEFAULT NULL,
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `base_product_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_product_specification_field_version_uidx` (`field_id`,`version_no`),
  KEY `ibexa_product_specification_fid` (`field_id`),
  KEY `ibexa_product_specification_pc` (`code`),
  KEY `ibexa_product_idx` (`product_id`),
  KEY `ibexa_product_specification_base_pid` (`base_product_id`),
  KEY `ibexa_product_specification_cv` (`content_id`,`version_no`),
  CONSTRAINT `ibexa_product_fkey` FOREIGN KEY (`product_id`) REFERENCES `ibexa_product` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ibexa_product_specification_field_version_fk` FOREIGN KEY (`field_id`, `version_no`) REFERENCES `ibexa_content_field` (`id`, `version`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_asset`
--

DROP TABLE IF EXISTS `ibexa_product_specification_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_asset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_specification_id` int NOT NULL,
  `uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tags` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_asset_pid` (`product_specification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_specification_id` int NOT NULL,
  `attribute_definition_id` int NOT NULL,
  `discriminator` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_sid_idx` (`product_specification_id`),
  KEY `ibexa_product_specification_attribute_aid_idx` (`attribute_definition_id`),
  CONSTRAINT `ibexa_product_specification_attribute_aid` FOREIGN KEY (`attribute_definition_id`) REFERENCES `ibexa_attribute_definition` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ibexa_product_specification_attribute_sid_fk` FOREIGN KEY (`product_specification_id`) REFERENCES `ibexa_product_specification` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_boolean`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_boolean`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_boolean` (
  `id` int NOT NULL,
  `value` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_boolean_value_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_boolean_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_datetime`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_datetime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_datetime` (
  `id` int NOT NULL,
  `value` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_datetime_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_datetime_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_float`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_float`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_float` (
  `id` int NOT NULL,
  `value` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_float_value_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_float_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_integer`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_integer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_integer` (
  `id` int NOT NULL,
  `value` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_integer_value_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_integer_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_measurement_range`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_measurement_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_measurement_range` (
  `id` int NOT NULL,
  `unit_id` int DEFAULT NULL,
  `base_unit_id` int DEFAULT NULL,
  `min_value` double DEFAULT NULL,
  `max_value` double DEFAULT NULL,
  `base_min_value` double DEFAULT NULL,
  `base_max_value` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attr_range_measurement_unit_idx` (`unit_id`),
  KEY `ibexa_product_specification_attr_range_measurement_baseunit_idx` (`base_unit_id`),
  CONSTRAINT `ibexa_product_specification_attr_range_measurement_baseunit_fk` FOREIGN KEY (`base_unit_id`) REFERENCES `ibexa_measurement_unit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ibexa_product_specification_attr_range_measurement_range_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_product_specification_attr_range_measurement_unit_fk` FOREIGN KEY (`unit_id`) REFERENCES `ibexa_measurement_unit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_measurement_value`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_measurement_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_measurement_value` (
  `id` int NOT NULL,
  `unit_id` int DEFAULT NULL,
  `base_unit_id` int DEFAULT NULL,
  `value` double DEFAULT NULL,
  `base_value` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attr_value_measurement_unit_idx` (`unit_id`),
  KEY `ibexa_product_specification_attr_value_measurement_baseunit_idx` (`base_unit_id`),
  CONSTRAINT `ibexa_product_specification_attr_single_measurement_baseunit_fk` FOREIGN KEY (`base_unit_id`) REFERENCES `ibexa_measurement_unit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ibexa_product_specification_attr_single_measurement_unit_fk` FOREIGN KEY (`unit_id`) REFERENCES `ibexa_measurement_unit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ibexa_product_specification_attr_single_measurement_value_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_selection`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_selection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_selection` (
  `id` int NOT NULL,
  `value` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_selection_value_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_selection_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_simple_custom`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_simple_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_simple_custom` (
  `id` int NOT NULL,
  `value` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ibexa_product_specification_attribute_simple_custom_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_attribute_symbol`
--

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_symbol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_symbol` (
  `id` int NOT NULL,
  `value` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_symbol_value_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_symbol_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_availability`
--

DROP TABLE IF EXISTS `ibexa_product_specification_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_availability` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT '0',
  `is_infinite` tinyint(1) NOT NULL DEFAULT '0',
  `stock` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_product_specification_availability_product_code_uidx` (`product_code`),
  KEY `ibexa_product_specification_availability_idx` (`availability`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_price`
--

DROP TABLE IF EXISTS `ibexa_product_specification_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_price` (
  `id` int NOT NULL AUTO_INCREMENT,
  `currency_id` int NOT NULL,
  `product_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `discriminator` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'main',
  `amount` decimal(19,4) NOT NULL,
  `custom_price_amount` decimal(19,4) DEFAULT NULL,
  `custom_price_rule` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_price_product_code_idx` (`product_code`),
  KEY `ibexa_product_specification_price_currency_idx` (`currency_id`),
  CONSTRAINT `ibexa_product_specification_price_currency_fk` FOREIGN KEY (`currency_id`) REFERENCES `ibexa_currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_specification_price_customer_group`
--

DROP TABLE IF EXISTS `ibexa_product_specification_price_customer_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_price_customer_group` (
  `id` int NOT NULL,
  `customer_group_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_price_customer_group_idx` (`customer_group_id`),
  CONSTRAINT `ibexa_product_specification_price_customer_group_fk` FOREIGN KEY (`customer_group_id`) REFERENCES `ibexa_customer_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_type_setting`
--

DROP TABLE IF EXISTS `ibexa_product_type_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_type_setting` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_definition_id` int NOT NULL,
  `status` int NOT NULL,
  `is_virtual` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_product_type_setting_field_definition_uidx` (`field_definition_id`,`status`),
  KEY `ibexa_product_type_setting_field_definition_idx` (`field_definition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_product_type_specification_region_vat_category`
--

DROP TABLE IF EXISTS `ibexa_product_type_specification_region_vat_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_type_specification_region_vat_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_definition_id` int NOT NULL,
  `status` int NOT NULL,
  `region` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `vat_category` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_product_type_region_vat_category_product_region_fk` (`field_definition_id`,`status`,`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_role`
--

DROP TABLE IF EXISTS `ibexa_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_new` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `value` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `version` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_scheduler_scheduled_entry`
--

DROP TABLE IF EXISTS `ibexa_scheduler_scheduled_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_scheduler_scheduled_entry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_id` int NOT NULL,
  `version_id` int DEFAULT NULL,
  `version_number` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `action_timestamp` int NOT NULL,
  `action` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `url_root` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_id_version_number_action` (`content_id`,`version_number`,`action`),
  KEY `content_id` (`content_id`),
  KEY `version_id` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_search_object_word_link`
--

DROP TABLE IF EXISTS `ibexa_search_object_word_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_search_object_word_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_type_field_definition_id` int NOT NULL DEFAULT '0',
  `content_type_id` int NOT NULL DEFAULT '0',
  `contentobject_id` int NOT NULL DEFAULT '0',
  `frequency` double NOT NULL DEFAULT '0',
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `integer_value` int NOT NULL DEFAULT '0',
  `next_word_id` int NOT NULL DEFAULT '0',
  `placement` int NOT NULL DEFAULT '0',
  `prev_word_id` int NOT NULL DEFAULT '0',
  `published` int NOT NULL DEFAULT '0',
  `section_id` int NOT NULL DEFAULT '0',
  `word_id` int NOT NULL DEFAULT '0',
  `language_mask` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ibexa_search_object_word_link_object` (`contentobject_id`),
  KEY `ibexa_search_object_word_link_identifier` (`identifier`(191)),
  KEY `ibexa_search_object_word_link_integer_value` (`integer_value`),
  KEY `ibexa_search_object_word_link_word` (`word_id`),
  KEY `ibexa_search_object_word_link_frequency` (`frequency`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_search_word`
--

DROP TABLE IF EXISTS `ibexa_search_word`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_search_word` (
  `id` int NOT NULL AUTO_INCREMENT,
  `object_count` int NOT NULL DEFAULT '0',
  `word` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_search_word_word_i` (`word`),
  KEY `ibexa_search_word_obj_count` (`object_count`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_section`
--

DROP TABLE IF EXISTS `ibexa_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `locale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `navigation_part_identifier` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT 'ezcontentnavigationpart',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_segment`
--

DROP TABLE IF EXISTS `ibexa_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_segment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`identifier`),
  UNIQUE KEY `ibexa_segment_identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_segment_group`
--

DROP TABLE IF EXISTS `ibexa_segment_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_segment_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`identifier`),
  UNIQUE KEY `ibexa_segment_group_identifier` (`identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_segment_group_map`
--

DROP TABLE IF EXISTS `ibexa_segment_group_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_segment_group_map` (
  `segment_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`segment_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_segment_user_map`
--

DROP TABLE IF EXISTS `ibexa_segment_user_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_segment_user_map` (
  `segment_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`segment_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_setting`
--

DROP TABLE IF EXISTS `ibexa_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_setting` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `identifier` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `value` json NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_setting_group_identifier` (`group`,`identifier`),
  KEY `ibexa_setting_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_site`
--

DROP TABLE IF EXISTS `ibexa_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_site` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `created` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_site_data`
--

DROP TABLE IF EXISTS `ibexa_site_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_site_data` (
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_site_public_access`
--

DROP TABLE IF EXISTS `ibexa_site_public_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_site_public_access` (
  `public_access_identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `site_id` int NOT NULL,
  `site_access_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `status` int NOT NULL,
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `site_matcher_host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `site_matcher_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `tree_root_location_id` int NOT NULL,
  PRIMARY KEY (`public_access_identifier`),
  KEY `ibexa_site_public_access_site_id` (`site_id`),
  KEY `ibexa_spa_trl_id` (`tree_root_location_id`),
  CONSTRAINT `fk_ibexa_site_public_access_site_id` FOREIGN KEY (`site_id`) REFERENCES `ibexa_site` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_taxonomy_assignment`
--

DROP TABLE IF EXISTS `ibexa_taxonomy_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_taxonomy_assignment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entry_id` int DEFAULT NULL,
  `content_id` int NOT NULL,
  `version_no` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_taxonomy_assignment_unique_entry_content_idx` (`entry_id`,`content_id`,`version_no`),
  KEY `ibexa_taxonomy_assignment_entry_id_idx` (`entry_id`),
  KEY `ibexa_taxonomy_assignment_content_id_version_no_idx` (`content_id`,`version_no`),
  CONSTRAINT `FK_E04BD8CBBA364942` FOREIGN KEY (`entry_id`) REFERENCES `ibexa_taxonomy_entry` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_taxonomy_entry`
--

DROP TABLE IF EXISTS `ibexa_taxonomy_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_taxonomy_entry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `mainLanguageCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_id` int NOT NULL,
  `left` int NOT NULL,
  `right` int NOT NULL,
  `root` int DEFAULT NULL,
  `lvl` int NOT NULL,
  `taxonomy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_taxonomy_entry_identifier_idx` (`taxonomy`,`identifier`),
  KEY `IDX_A4052950727ACA70` (`parent_id`),
  KEY `ibexa_taxonomy_entry_content_id_idx` (`content_id`),
  CONSTRAINT `FK_A4052950727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `ibexa_taxonomy_entry` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_token`
--

DROP TABLE IF EXISTS `ibexa_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `identifier` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created` int NOT NULL DEFAULT '0',
  `expires` int NOT NULL DEFAULT '0',
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_token_unique` (`token`,`identifier`,`type_id`),
  KEY `IDX_B5412887C54C8C93` (`type_id`),
  CONSTRAINT `ibexa_token_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `ibexa_token_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_token_type`
--

DROP TABLE IF EXISTS `ibexa_token_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_token_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_token_type_unique` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_url`
--

DROP TABLE IF EXISTS `ibexa_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_url` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created` int NOT NULL DEFAULT '0',
  `is_valid` int NOT NULL DEFAULT '1',
  `last_checked` int NOT NULL DEFAULT '0',
  `modified` int NOT NULL DEFAULT '0',
  `original_url_md5` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `ibexa_url_url` (`url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_url_alias`
--

DROP TABLE IF EXISTS `ibexa_url_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_url_alias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `destination_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `forward_to_id` int NOT NULL DEFAULT '0',
  `is_imported` int NOT NULL DEFAULT '0',
  `is_internal` int NOT NULL DEFAULT '1',
  `is_wildcard` int NOT NULL DEFAULT '0',
  `source_md5` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `source_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_url_alias_source_md5` (`source_md5`),
  KEY `ibexa_url_alias_wcard_fwd` (`is_wildcard`,`forward_to_id`),
  KEY `ibexa_url_alias_forward_to_id` (`forward_to_id`),
  KEY `ibexa_url_alias_imp_wcard_fwd` (`is_imported`,`is_wildcard`,`forward_to_id`),
  KEY `ibexa_url_alias_source_url` (`source_url`(191)),
  KEY `ibexa_url_alias_desturl` (`destination_url`(191))
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_url_alias_ml`
--

DROP TABLE IF EXISTS `ibexa_url_alias_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_url_alias_ml` (
  `parent` int NOT NULL DEFAULT '0',
  `text_md5` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `action` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `action_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `alias_redirects` int NOT NULL DEFAULT '1',
  `id` int NOT NULL DEFAULT '0',
  `is_alias` int NOT NULL DEFAULT '0',
  `is_original` int NOT NULL DEFAULT '0',
  `lang_mask` bigint NOT NULL DEFAULT '0',
  `link` int NOT NULL DEFAULT '0',
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`parent`,`text_md5`),
  KEY `ibexa_url_alias_ml_actt_org_al` (`action_type`,`is_original`,`is_alias`),
  KEY `ibexa_url_alias_ml_text_lang` (`text`(32),`lang_mask`,`parent`),
  KEY `ibexa_url_alias_ml_par_act_id_lnk` (`action`(32),`id`,`link`,`parent`),
  KEY `ibexa_url_alias_ml_par_lnk_txt` (`parent`,`text`(32),`link`),
  KEY `ibexa_url_alias_ml_act_org` (`action`(32),`is_original`),
  KEY `ibexa_url_alias_ml_text` (`text`(32),`id`,`link`),
  KEY `ibexa_url_alias_ml_link` (`link`),
  KEY `ibexa_url_alias_ml_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_url_alias_ml_incr`
--

DROP TABLE IF EXISTS `ibexa_url_alias_ml_incr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_url_alias_ml_incr` (
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_url_content_link`
--

DROP TABLE IF EXISTS `ibexa_url_content_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_url_content_link` (
  `contentobject_attribute_id` int NOT NULL DEFAULT '0',
  `contentobject_attribute_version` int NOT NULL DEFAULT '0',
  `url_id` int NOT NULL DEFAULT '0',
  KEY `ibexa_url_ol_coa_id` (`contentobject_attribute_id`),
  KEY `ibexa_url_ol_url_id` (`url_id`),
  KEY `ibexa_url_ol_coa_version` (`contentobject_attribute_version`),
  KEY `ibexa_url_ol_coa_id_cav` (`contentobject_attribute_id`,`contentobject_attribute_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_url_wildcard`
--

DROP TABLE IF EXISTS `ibexa_url_wildcard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_url_wildcard` (
  `id` int NOT NULL AUTO_INCREMENT,
  `destination_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `source_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `type` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user`
--

DROP TABLE IF EXISTS `ibexa_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user` (
  `contentobject_id` int NOT NULL DEFAULT '0',
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `login` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `password_hash_type` int NOT NULL DEFAULT '1',
  `password_updated_at` int DEFAULT NULL,
  PRIMARY KEY (`contentobject_id`),
  UNIQUE KEY `ibexa_user_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user_accountkey`
--

DROP TABLE IF EXISTS `ibexa_user_accountkey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user_accountkey` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hash_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `time` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hash_key` (`hash_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user_invitation`
--

DROP TABLE IF EXISTS `ibexa_user_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user_invitation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `site_access_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `creation_date` int NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_user_invitation_email_uindex` (`email`(191)),
  UNIQUE KEY `ibexa_user_invitation_hash_uindex` (`hash`(191)),
  KEY `ibexa_user_invitation_email_idx` (`email`),
  KEY `ibexa_user_invitation_hash_idx` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user_invitation_assignment`
--

DROP TABLE IF EXISTS `ibexa_user_invitation_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user_invitation_assignment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invitation_id` int NOT NULL,
  `user_group_id` int DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `limitation_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `limitation_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9E1E6F70A35D7AF0` (`invitation_id`),
  CONSTRAINT `ibexa_user_invitation_assignment_ibexa_user_invitation_id_fk` FOREIGN KEY (`invitation_id`) REFERENCES `ibexa_user_invitation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user_preference`
--

DROP TABLE IF EXISTS `ibexa_user_preference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user_preference` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `ibexa_user_preference_user_id_idx` (`user_id`,`name`),
  KEY `ibexa_user_preference_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user_role`
--

DROP TABLE IF EXISTS `ibexa_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contentobject_id` int DEFAULT NULL,
  `limit_identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `limit_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `role_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ibexa_user_role_role_id` (`role_id`),
  KEY `ibexa_user_role_contentobject_id` (`contentobject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_user_setting`
--

DROP TABLE IF EXISTS `ibexa_user_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_user_setting` (
  `user_id` int NOT NULL DEFAULT '0',
  `is_enabled` int NOT NULL DEFAULT '0',
  `max_login` int DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_workflow`
--

DROP TABLE IF EXISTS `ibexa_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_workflow` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_id` int NOT NULL,
  `version_no` int NOT NULL,
  `workflow_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `initial_owner_id` int DEFAULT NULL,
  `start_date` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_id` (`id`),
  KEY `idx_workflow_co_id_ver` (`content_id`,`version_no`),
  KEY `idx_workflow_name` (`workflow_name`),
  KEY `initial_owner_id` (`initial_owner_id`),
  KEY `idx_workflow_start_date` (`start_date`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_workflow_marking`
--

DROP TABLE IF EXISTS `ibexa_workflow_marking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_workflow_marking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `workflow_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `reviewer_id` int DEFAULT NULL,
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `fk_workflow_id_marking` (`workflow_id`),
  CONSTRAINT `fk_ibexa_workflow_marking_workflow_id` FOREIGN KEY (`workflow_id`) REFERENCES `ibexa_workflow` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_workflow_transition`
--

DROP TABLE IF EXISTS `ibexa_workflow_transition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_workflow_transition` (
  `id` int NOT NULL AUTO_INCREMENT,
  `workflow_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `timestamp` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `fk_workflow_id_transition` (`workflow_id`),
  CONSTRAINT `fk_ibexa_workflow_transition_workflow_id` FOREIGN KEY (`workflow_id`) REFERENCES `ibexa_workflow` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibexa_workflow_version_lock`
--

DROP TABLE IF EXISTS `ibexa_workflow_version_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_workflow_version_lock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_id` int NOT NULL,
  `version` int NOT NULL,
  `user_id` int NOT NULL,
  `created` int NOT NULL,
  `modified` int NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_version_lock_content` (`content_id`),
  KEY `idx_version_lock_user` (`user_id`),
  KEY `idx_version_lock_content_version` (`content_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-03 16:24:04
