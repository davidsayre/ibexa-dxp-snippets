DROP TABLE IF EXISTS `ibexa_object_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state` (
  `id` int NOT NULL AUTO_INCREMENT,
  `default_language_id` bigint NOT NULL DEFAULT '0',
  `group_id` int NOT NULL DEFAULT '0',
  `identifier` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `language_mask` bigint NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_object_state_identifier` (`group_id`,`identifier`),
  KEY `ibexa_object_state_priority` (`priority`),
  KEY `ibexa_object_state_lmask` (`language_mask`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibexa_object_state`
--

LOCK TABLES `ibexa_object_state` WRITE;
/*!40000 ALTER TABLE `ibexa_object_state` DISABLE KEYS */;
INSERT INTO `ibexa_object_state` VALUES (1,2,2,'not_locked',3,0),
(2,2,2,'locked',3,1);
/*!40000 ALTER TABLE `ibexa_object_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibexa_object_state_group`
--

DROP TABLE IF EXISTS `ibexa_object_state_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `default_language_id` bigint NOT NULL DEFAULT '0',
  `identifier` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `language_mask` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ibexa_object_state_group_identifier` (`identifier`),
  KEY `ibexa_object_state_group_lmask` (`language_mask`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibexa_object_state_group`
--

LOCK TABLES `ibexa_object_state_group` WRITE;
/*!40000 ALTER TABLE `ibexa_object_state_group` DISABLE KEYS */;
INSERT INTO `ibexa_object_state_group` VALUES (2,2,'ibexa_lock',3);
/*!40000 ALTER TABLE `ibexa_object_state_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibexa_object_state_group_language`
--

DROP TABLE IF EXISTS `ibexa_object_state_group_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_group_language` (
  `contentobject_state_group_id` int NOT NULL DEFAULT '0',
  `real_language_id` bigint NOT NULL DEFAULT '0',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `language_id` bigint NOT NULL DEFAULT '0',
  `name` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`contentobject_state_group_id`,`real_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibexa_object_state_group_language`
--

LOCK TABLES `ibexa_object_state_group_language` WRITE;
/*!40000 ALTER TABLE `ibexa_object_state_group_language` DISABLE KEYS */;
INSERT INTO `ibexa_object_state_group_language` VALUES (2,2,'',3,'Lock');
/*!40000 ALTER TABLE `ibexa_object_state_group_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibexa_object_state_language`
--

DROP TABLE IF EXISTS `ibexa_object_state_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_language` (
  `contentobject_state_id` int NOT NULL DEFAULT '0',
  `language_id` bigint NOT NULL DEFAULT '0',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`contentobject_state_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibexa_object_state_language`
--

LOCK TABLES `ibexa_object_state_language` WRITE;
/*!40000 ALTER TABLE `ibexa_object_state_language` DISABLE KEYS */;
INSERT INTO `ibexa_object_state_language` VALUES (1,3,'','Not locked'),
(2,3,'','Locked');
/*!40000 ALTER TABLE `ibexa_object_state_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibexa_object_state_link`
--

DROP TABLE IF EXISTS `ibexa_object_state_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibexa_object_state_link` (
  `contentobject_id` int NOT NULL DEFAULT '0',
  `contentobject_state_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`contentobject_id`,`contentobject_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibexa_object_state_link`
--

/* Insert EVERY content object!!! */
LOCK TABLES `ibexa_object_state_link` WRITE;
/*!40000 ALTER TABLE `ibexa_object_state_link` DISABLE KEYS */;

INSERT INTO `ibexa_object_state_link`
(contentobject_id,contentobject_state_id)
select id, 1 from ibexa_content

/*!40000 ALTER TABLE `ibexa_object_state_link` ENABLE KEYS */;
UNLOCK TABLES;
