/* these are tables that need to be created pre ezplatform v2.5 */

/*
ezcontentclass_attribute_ml

ezdatebasedpublisher_scheduled_version
ezdatebasedpublisher_scheduled_entries

ezdfsfile

ezeditorialworkflow_markings
ezeditorialworkflow_transitions
ezeditorialworkflow_workflows

ezform_field_attributes
ezform_field_validators
ezform_fields
ezform_form_submission_data
ezform_form_submissions
ezform_forms

eznotification

ezpage_attributes
ezpage_blocks
ezpage_blocks_design
ezpage_blocks_visibility
ezpage_map_attributes_blocks
ezpage_map_blocks_zones
ezpage_map_zones_pages
ezpage_pages
ezpage_zones

ezsession

ezsite
ezsite_public_access

ezuser_discountrule
*/


DROP TABLE IF EXISTS `ezcontentclass_attribute_ml`;
CREATE TABLE `ezcontentclass_attribute_ml` (
  `contentclass_attribute_id` INT NOT NULL,
  `version` INT NOT NULL,
  `language_id` BIGINT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `data_text` TEXT NULL,
  `data_json` TEXT NULL,
  PRIMARY KEY (`contentclass_attribute_id`, `version`, `language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ezdatebasedpublisher_scheduled_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `version_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `publication_date` int(11) NOT NULL,
  `url_root` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`content_id`,`version_number`),
  KEY `index2` (`content_id`),
  KEY `index3` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ezdatebasedpublisher_scheduled_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezdatebasedpublisher_scheduled_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_id` int NOT NULL,
  `version_id` int DEFAULT NULL,
  `version_number` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `action_timestamp` int NOT NULL,
  `action` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `url_root` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_id_version_number_action` (`content_id`,`version_number`,`action`),
  KEY `content_id` (`content_id`),
  KEY `version_id` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ezdfsfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE ezdfsfile (
  `name` text NOT NULL,
  `name_trunk` text NOT NULL,
  `name_hash` varchar(34) NOT NULL DEFAULT '',
  `datatype` varchar(255) NOT NULL DEFAULT 'application/octet-stream',
  `scope` varchar(25) NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name_hash`),
  KEY `ezdfsfile_name` (`name` (191)),
  KEY `ezdfsfile_name_trunk` (`name_trunk` (191)),
  KEY `ezdfsfile_mtime` (`mtime`),
  KEY `ezdfsfile_expired_name` (`expired`,`name` (191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;


/* before ezeditorialworkflow_markings, ezeditorialworkflow_transitions */
CREATE TABLE `ezeditorialworkflow_workflows` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `version_no` int(11) NOT NULL,
  `workflow_name` varchar(255) NOT NULL DEFAULT '',
  `initial_owner_id` int(255) DEFAULT NULL,
  `start_date` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_id` (`id`),
  KEY `initial_owner_id` (`initial_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ezeditorialworkflow_markings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_workflow_id` (`workflow_id`),
  CONSTRAINT `fk_ezeditorialworkflow_markings_workflow_id` FOREIGN KEY (`workflow_id`) REFERENCES `ezeditorialworkflow_workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ezeditorialworkflow_markings`
    ADD COLUMN `message`     TEXT NOT NULL DEFAULT '',
    ADD COLUMN `reviewer_id` INT(11),
    ADD COLUMN `result`      TEXT;

CREATE TABLE `ezeditorialworkflow_transitions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `fk_workflow_id` (`workflow_id`),
  CONSTRAINT `fk_ezeditorialworkflow_transitions_workflow_id` FOREIGN KEY (`workflow_id`) REFERENCES `ezeditorialworkflow_workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




DROP TABLE IF EXISTS `ezform_fields`;
CREATE TABLE `ezform_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) DEFAULT NULL,
  `name` VARCHAR(128) NOT NULL,
  `identifier` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ezform_field_attributes`;
CREATE TABLE `ezform_field_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) DEFAULT NULL,
  `identifier` varchar(128) DEFAULT NULL,
  `value` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ezform_field_validators`;
CREATE TABLE `ezform_field_validators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) DEFAULT NULL,
  `identifier` varchar(128) DEFAULT NULL,
  `value` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ezform_form_submissions`;
CREATE TABLE `ezform_form_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `content_id` INT NOT NULL,
  `language_code` VARCHAR(6) NOT NULL,
  `user_id` INT NOT NULL,
  `created` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ezform_form_submission_data`;
CREATE TABLE `ezform_form_submission_data` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `form_submission_id` INT NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `identifier` VARCHAR(128) NOT NULL,
  `value` BLOB NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ezform_forms`;
CREATE TABLE `ezform_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) DEFAULT NULL,
  `version_no` int(11) DEFAULT NULL,
  `content_field_id` int(11) DEFAULT NULL,
  `language_code` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `eznotification`;
CREATE TABLE `eznotification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL DEFAULT 0,
  `is_pending` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(128) NOT NULL DEFAULT '',
  `created` int(11) NOT NULL DEFAULT 0,
  `data` text,
  PRIMARY KEY (`id`),
  KEY `eznotification_owner` (`owner_id`),
  KEY `eznotification_owner_is_pending` (`owner_id`, `is_pending`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




DROP TABLE IF EXISTS `ezpage_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_attributes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_attributes`
--

LOCK TABLES `ezpage_attributes` WRITE;
/*!40000 ALTER TABLE `ezpage_attributes` DISABLE KEYS */;
INSERT INTO `ezpage_attributes` VALUES (1,'actions','create_content,create_form,create_product,create_catalog,create_company'),(2,'show_only_current_user','0'),(3,'limit','5'),(4,'limit','7');
/*!40000 ALTER TABLE `ezpage_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_blocks`
--

DROP TABLE IF EXISTS `ezpage_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `view` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_blocks`
--

LOCK TABLES `ezpage_blocks` WRITE;
/*!40000 ALTER TABLE `ezpage_blocks` DISABLE KEYS */;
INSERT INTO `ezpage_blocks` VALUES (1,'quick_actions','default','Quick actions'),(2,'ibexa_news','default','Ibexa news'),(3,'review_queue','default','Review queue'),(4,'my_content','default','My content'),(5,'common_content','default','Common content'),(6,'quick_actions','default','Quick actions'),(7,'ibexa_news','default','Ibexa news'),(8,'review_queue','default','Review queue'),(9,'my_content','default','My content'),(10,'common_content','default','Common content'),(11,'quick_actions','default','Quick actions'),(12,'ibexa_news','default','Ibexa news'),(13,'review_queue','default','Review queue'),(14,'my_content','default','My content'),(15,'common_content','default','Common content'),(16,'quick_actions','default','Quick actions'),(17,'ibexa_activity_log_list','activity_log_list','Recent activity'),(18,'ibexa_news','default','Ibexa news'),(19,'review_queue','default','Review queue'),(20,'my_content','default','My content'),(21,'common_content','default','Common content');
/*!40000 ALTER TABLE `ezpage_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_blocks_design`
--

DROP TABLE IF EXISTS `ezpage_blocks_design`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_blocks_design` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `style` text COLLATE utf8mb4_unicode_520_ci,
  `compiled` text COLLATE utf8mb4_unicode_520_ci,
  `class` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ezpage_blocks_design_block_id` (`block_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_blocks_design`
--

LOCK TABLES `ezpage_blocks_design` WRITE;
/*!40000 ALTER TABLE `ezpage_blocks_design` DISABLE KEYS */;
INSERT INTO `ezpage_blocks_design` VALUES (1,1,NULL,'',NULL),(2,2,NULL,'',NULL),(3,3,NULL,'',NULL),(4,4,NULL,'',NULL),(5,5,NULL,'',NULL),(6,6,NULL,'',NULL),(7,7,NULL,'',NULL),(8,8,NULL,'',NULL),(9,9,NULL,'',NULL),(10,10,NULL,'',NULL),(11,11,NULL,'',NULL),(12,12,NULL,'',NULL),(13,13,NULL,'',NULL),(14,14,NULL,'',NULL),(15,15,NULL,'',NULL),(16,16,NULL,'',NULL),(17,17,NULL,'',NULL),(18,18,NULL,'',NULL),(19,19,NULL,'',NULL),(20,20,NULL,'',NULL),(21,21,NULL,'',NULL);
/*!40000 ALTER TABLE `ezpage_blocks_design` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_blocks_visibility`
--

DROP TABLE IF EXISTS `ezpage_blocks_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_blocks_visibility` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `since` int DEFAULT NULL,
  `till` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ezpage_blocks_visibility_block_id` (`block_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_blocks_visibility`
--

LOCK TABLES `ezpage_blocks_visibility` WRITE;
/*!40000 ALTER TABLE `ezpage_blocks_visibility` DISABLE KEYS */;
INSERT INTO `ezpage_blocks_visibility` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL),(3,3,NULL,NULL),(4,4,NULL,NULL),(5,5,NULL,NULL),(6,6,NULL,NULL),(7,7,NULL,NULL),(8,8,NULL,NULL),(9,9,NULL,NULL),(10,10,NULL,NULL),(11,11,NULL,NULL),(12,12,NULL,NULL),(13,13,NULL,NULL),(14,14,NULL,NULL),(15,15,NULL,NULL),(16,16,NULL,NULL),(17,17,NULL,NULL),(18,18,NULL,NULL),(19,19,NULL,NULL),(20,20,NULL,NULL),(21,21,NULL,NULL);
/*!40000 ALTER TABLE `ezpage_blocks_visibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_map_attributes_blocks`
--

DROP TABLE IF EXISTS `ezpage_map_attributes_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_map_attributes_blocks` (
  `attribute_id` int NOT NULL,
  `block_id` int NOT NULL,
  PRIMARY KEY (`attribute_id`,`block_id`),
  KEY `ezpage_map_attributes_blocks_attribute_id` (`attribute_id`),
  KEY `ezpage_map_attributes_blocks_block_id` (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_map_attributes_blocks`
--

LOCK TABLES `ezpage_map_attributes_blocks` WRITE;
/*!40000 ALTER TABLE `ezpage_map_attributes_blocks` DISABLE KEYS */;
INSERT INTO `ezpage_map_attributes_blocks` VALUES (1,16),(2,17),(3,17),(4,18);
/*!40000 ALTER TABLE `ezpage_map_attributes_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_map_blocks_zones`
--

DROP TABLE IF EXISTS `ezpage_map_blocks_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_map_blocks_zones` (
  `block_id` int NOT NULL,
  `zone_id` int NOT NULL,
  PRIMARY KEY (`block_id`,`zone_id`),
  KEY `ezpage_map_blocks_zones_block_id` (`block_id`),
  KEY `ezpage_map_blocks_zones_zone_id` (`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_map_blocks_zones`
--

LOCK TABLES `ezpage_map_blocks_zones` WRITE;
/*!40000 ALTER TABLE `ezpage_map_blocks_zones` DISABLE KEYS */;
INSERT INTO `ezpage_map_blocks_zones` VALUES (1,2),(2,4),(3,5),(4,5),(5,5),(6,6),(7,8),(8,9),(9,9),(10,9),(11,10),(12,12),(13,13),(14,13),(15,13),(16,14),(17,15),(18,16),(19,17),(20,17),(21,17);
/*!40000 ALTER TABLE `ezpage_map_blocks_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_map_zones_pages`
--

DROP TABLE IF EXISTS `ezpage_map_zones_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_map_zones_pages` (
  `zone_id` int NOT NULL,
  `page_id` int NOT NULL,
  PRIMARY KEY (`zone_id`,`page_id`),
  KEY `ezpage_map_zones_pages_zone_id` (`zone_id`),
  KEY `ezpage_map_zones_pages_page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_map_zones_pages`
--

LOCK TABLES `ezpage_map_zones_pages` WRITE;
/*!40000 ALTER TABLE `ezpage_map_zones_pages` DISABLE KEYS */;
INSERT INTO `ezpage_map_zones_pages` VALUES (1,1),(2,2),(3,2),(4,2),(5,2),(6,3),(7,3),(8,3),(9,3),(14,5),(15,5),(16,5),(17,5);
/*!40000 ALTER TABLE `ezpage_map_zones_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_pages`
--

DROP TABLE IF EXISTS `ezpage_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version_no` int NOT NULL,
  `content_id` int NOT NULL,
  `language_code` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `layout` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ezpage_pages_content_id_version_no` (`content_id`,`version_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_pages`
--

LOCK TABLES `ezpage_pages` WRITE;
/*!40000 ALTER TABLE `ezpage_pages` DISABLE KEYS */;
INSERT INTO `ezpage_pages` VALUES (1,1,52,'eng-GB','default'),(2,1,67,'eng-GB','dashboard_three_rows_two_columns'),(3,2,67,'eng-GB','dashboard_three_rows_two_columns'),(5,3,67,'eng-GB','dashboard_three_rows_two_columns');
/*!40000 ALTER TABLE `ezpage_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ezpage_zones`
--

DROP TABLE IF EXISTS `ezpage_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezpage_zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ezpage_zones`
--

LOCK TABLES `ezpage_zones` WRITE;
/*!40000 ALTER TABLE `ezpage_zones` DISABLE KEYS */;
INSERT INTO `ezpage_zones` VALUES (1,'default'),(2,'top'),(3,'middle-left'),(4,'middle-right'),(5,'bottom'),(6,'top'),(7,'middle-left'),(8,'middle-right'),(9,'bottom'),(14,'top'),(15,'middle-left'),(16,'middle-right'),(17,'bottom');
/*!40000 ALTER TABLE `ezpage_zones` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `ezsession`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezsession` (
  `data` longtext NOT NULL,
  `expiration_time` int(11) NOT NULL DEFAULT '0',
  `session_key` varchar(32) NOT NULL DEFAULT '',
  `user_hash` varchar(32) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_key`),
  KEY `expiration_time` (`expiration_time`),
  KEY `ezsession_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `ezsite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezsite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `created` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `ezsite_public_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezsite_public_access` (
  `public_access_identifier` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `site_id` int NOT NULL,
  `site_access_group` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `status` int NOT NULL,
  `config` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `site_matcher_host` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `site_matcher_path` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`public_access_identifier`),
  KEY `ezsite_public_access_site_id` (`site_id`),
  CONSTRAINT `fk_ezsite_public_access_site_id` FOREIGN KEY (`site_id`) REFERENCES `ezsite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `ezuser_discountrule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ezuser_discountrule` (
  `contentobject_id` int(11) DEFAULT NULL,
  `discountrule_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;


ALTER TABLE `ezeditorialworkflow_markings`
    ADD COLUMN `message`     TEXT NOT NULL DEFAULT '',
    ADD COLUMN `reviewer_id` INT(11),
    ADD COLUMN `result`      TEXT;


