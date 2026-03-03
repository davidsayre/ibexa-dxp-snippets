DROP TABLE IF EXISTS `ibexa_workflow_version_lock`;
DROP TABLE IF EXISTS `ibexa_workflow_transition`;
DROP TABLE IF EXISTS `ibexa_workflow_marking`;
DROP TABLE IF EXISTS `ibexa_workflow`;

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
