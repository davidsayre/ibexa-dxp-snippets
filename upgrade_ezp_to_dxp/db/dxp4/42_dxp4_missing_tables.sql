DROP TABLE IF EXISTS `ibexa_taxonomy_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_taxonomy_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `names` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `mainLanguageCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_id` int NOT NULL,
  `left` int NOT NULL,
  `right` int NOT NULL,
  `root` int DEFAULT NULL,
  `lvl` int NOT NULL,
  `taxonomy` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_taxonomy_entries_identifier_idx` (`taxonomy`,`identifier`),
  KEY `IDX_74706FD6727ACA70` (`parent_id`),
  KEY `ibexa_taxonomy_entries_content_id_idx` (`content_id`),
  CONSTRAINT `FK_74706FD6727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `ibexa_taxonomy_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibexa_taxonomy_entries`
--

LOCK TABLES `ibexa_taxonomy_entries` WRITE;
/*!40000 ALTER TABLE `ibexa_taxonomy_entries` DISABLE KEYS */;
INSERT INTO `ibexa_taxonomy_entries` VALUES (1,NULL,'root','Root','a:1:{s:6:\"eng-GB\";s:4:\"Root\";}','eng-GB',58,1,2,1,0,'tags'),(2,NULL,'product_root','Product Root Tag','a:1:{s:6:\"eng-GB\";s:16:\"Product Root Tag\";}','eng-GB',64,1,2,2,0,'product_categories');
/*!40000 ALTER TABLE `ibexa_taxonomy_entries` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `ibexa_taxonomy_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_taxonomy_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entry_id` int DEFAULT NULL,
  `content_id` int NOT NULL,
  `version_no` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_taxonomy_assignments_unique_entry_content_idx` (`entry_id`,`content_id`,`version_no`),
  KEY `ibexa_taxonomy_assignments_entry_id_idx` (`entry_id`),
  KEY `ibexa_taxonomy_assignments_content_id_version_no_idx` (`content_id`,`version_no`),
  CONSTRAINT `FK_17589FEBBA364942` FOREIGN KEY (`entry_id`) REFERENCES `ibexa_taxonomy_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
