INSERT INTO `ibexa_site_data` VALUES ('ibexa-release','5.0');


DROP TABLE IF EXISTS `ibexa_action_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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

DROP TABLE IF EXISTS `ibexa_action_configuration_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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


DROP TABLE IF EXISTS `ibexa_collaboration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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

DROP TABLE IF EXISTS `ibexa_collaboration_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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


DROP TABLE IF EXISTS `ibexa_collaboration_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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


DROP TABLE IF EXISTS `ibexa_collaboration_participant_external`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_participant_external` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ibexa_collaboration_participant_external_pk` FOREIGN KEY (`id`) REFERENCES `ibexa_collaboration_participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `ibexa_collaboration_participant_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_collaboration_participant_internal` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E838B79AA76ED395` (`user_id`),
  CONSTRAINT `ibexa_collaboration_participant_internal_pk` FOREIGN KEY (`id`) REFERENCES `ibexa_collaboration_participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ibexa_collaboration_participant_internal_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `ibexa_user` (`contentobject_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


/* After ibexa_collaboration_participant */
DROP TABLE IF EXISTS `ibexa_collaboration_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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

DROP TABLE IF EXISTS `ibexa_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_migrations` (
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ibexa_product_specification_attribute_datetime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_product_specification_attribute_datetime` (
  `id` int NOT NULL,
  `value` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `ibexa_product_specification_attribute_datetime_idx` (`value`),
  CONSTRAINT `ibexa_product_specification_attribute_datetime_fk` FOREIGN KEY (`id`) REFERENCES `ibexa_product_specification_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
