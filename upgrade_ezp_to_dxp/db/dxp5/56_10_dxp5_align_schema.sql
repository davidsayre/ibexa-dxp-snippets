/*
    GOAL: Compare the freah ibexa dxp5 schema to the upgraded schema and fix columns, column ordering
*/

/* fix LONGTEXT to JSON */
ALTER TABLE `ibexa_activity_log` CHANGE COLUMN `data` `data` JSON NOT NULL ;

ALTER TABLE `ibexa_content` CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `ibexa_content_type` CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `status` `status` INT NOT NULL DEFAULT '0' AFTER `id`,
CHANGE COLUMN `serialized_description_list` `serialized_description_list` LONGTEXT NULL DEFAULT NULL AFTER `remote_id`;

ALTER TABLE `ibexa_content_type_field_definition`
CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `status` `status` INT NOT NULL DEFAULT '0' AFTER `id`,
CHANGE COLUMN `category` `category` VARCHAR(25) NOT NULL DEFAULT '' AFTER `can_translate`,
CHANGE COLUMN `is_thumbnail` `is_thumbnail` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_searchable`,
CHANGE COLUMN `serialized_name_list` `serialized_name_list` LONGTEXT NOT NULL AFTER `serialized_description_list`;

ALTER TABLE `ibexa_content_type_group` CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `ibexa_content_name`
CHANGE COLUMN `contentobject_id` `contentobject_id` INT NOT NULL DEFAULT '0' FIRST,
CHANGE COLUMN `content_version` `content_version` INT NOT NULL DEFAULT '0' AFTER `contentobject_id`;

ALTER TABLE `ibexa_content_field`
CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `version` `version` INT NOT NULL DEFAULT '0' AFTER `id`;

ALTER TABLE `ibexa_content_tree` CHANGE COLUMN `node_id` `node_id` INT NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `ibexa_content_version` CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `ibexa_user_setting` CHANGE COLUMN `user_id` `user_id` INT NOT NULL DEFAULT '0' FIRST;

ALTER TABLE `ibexa_content_language` CHANGE COLUMN `id` `id` BIGINT NOT NULL DEFAULT '0' FIRST;

ALTER TABLE `ibexa_object_state` CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `ibexa_object_state_group` CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `ibexa_object_state_group_language` CHANGE COLUMN `real_language_id` `real_language_id` BIGINT NOT NULL DEFAULT '0' AFTER `contentobject_state_group_id`;
ALTER TABLE `ibexa_object_state_language` CHANGE COLUMN `language_id` `language_id` BIGINT NOT NULL DEFAULT '0' AFTER `contentobject_state_id`;




ALTER TABLE `ibexa_section` CHANGE COLUMN `identifier` `identifier` VARCHAR(255) NULL DEFAULT NULL AFTER `id`;

ALTER TABLE `ibexa_user_accountkey` change column `id` `id` int NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `ibexa_user_role` change column `id` `id` int NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `ibexa_user` DROP INDEX `ibexa_user_login` ;

