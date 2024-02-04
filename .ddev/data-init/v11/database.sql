
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
DROP TABLE IF EXISTS `backend_layout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backend_layout` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `config` text NOT NULL,
  `icon` text DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `be_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `be_groups` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `non_exclude_fields` text DEFAULT NULL,
  `explicit_allowdeny` text DEFAULT NULL,
  `allowed_languages` varchar(255) NOT NULL DEFAULT '',
  `custom_options` text DEFAULT NULL,
  `db_mountpoints` text DEFAULT NULL,
  `pagetypes_select` text DEFAULT NULL,
  `tables_select` text DEFAULT NULL,
  `tables_modify` text DEFAULT NULL,
  `groupMods` text DEFAULT NULL,
  `availableWidgets` text DEFAULT NULL,
  `mfa_providers` text DEFAULT NULL,
  `file_mountpoints` text DEFAULT NULL,
  `file_permissions` text DEFAULT NULL,
  `TSconfig` text DEFAULT NULL,
  `subgroup` text DEFAULT NULL,
  `workspace_perms` smallint(6) NOT NULL DEFAULT 1,
  `category_perms` longtext DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `be_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `be_sessions` (
  `ses_id` varchar(190) NOT NULL DEFAULT '',
  `ses_iplock` varchar(39) NOT NULL DEFAULT '',
  `ses_userid` int(10) unsigned NOT NULL DEFAULT 0,
  `ses_tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `ses_data` longblob DEFAULT NULL,
  PRIMARY KEY (`ses_id`),
  KEY `ses_tstamp` (`ses_tstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `be_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `be_users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `disable` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `username` varchar(50) NOT NULL DEFAULT '',
  `avatar` int(10) unsigned NOT NULL DEFAULT 0,
  `password` varchar(100) NOT NULL DEFAULT '',
  `admin` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usergroup` text DEFAULT NULL,
  `lang` varchar(10) NOT NULL DEFAULT 'default',
  `email` varchar(255) NOT NULL DEFAULT '',
  `db_mountpoints` text DEFAULT NULL,
  `options` smallint(5) unsigned NOT NULL DEFAULT 0,
  `realName` varchar(80) NOT NULL DEFAULT '',
  `userMods` text DEFAULT NULL,
  `allowed_languages` varchar(255) NOT NULL DEFAULT '',
  `uc` mediumblob DEFAULT NULL,
  `file_mountpoints` text DEFAULT NULL,
  `file_permissions` text DEFAULT NULL,
  `workspace_perms` smallint(6) NOT NULL DEFAULT 1,
  `TSconfig` text DEFAULT NULL,
  `lastlogin` int(10) unsigned NOT NULL DEFAULT 0,
  `workspace_id` int(11) NOT NULL DEFAULT 0,
  `mfa` mediumblob DEFAULT NULL,
  `category_perms` longtext DEFAULT NULL,
  `password_reset_token` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`),
  KEY `parent` (`pid`,`deleted`,`disable`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `be_users` VALUES (1,0,1706961186,1633799703,0,0,0,0,0,NULL,'admin',0,'$argon2i$v=19$m=65536,t=16,p=1$ZmhRRUhTYnh6MUNPNy5WMw$CEubtWIj8UlMXv5tBgNk5xz5Oo4AcCN/NhHJYkJddt0',1,NULL,'default','',NULL,0,'',NULL,'','a:15:{s:14:\"interfaceSetup\";s:7:\"backend\";s:10:\"moduleData\";a:8:{s:8:\"web_list\";a:4:{s:8:\"function\";N;s:8:\"language\";N;s:19:\"constant_editor_cat\";N;s:9:\"clipBoard\";s:1:\"0\";}s:57:\"TYPO3\\CMS\\Backend\\Utility\\BackendUtility::getUpdateSignal\";a:0:{}s:9:\"clipboard\";a:5:{s:5:\"tab_1\";a:0:{}s:5:\"tab_2\";a:0:{}s:5:\"tab_3\";a:0:{}s:7:\"current\";s:6:\"normal\";s:6:\"normal\";a:2:{s:2:\"el\";a:1:{s:7:\"pages|3\";s:1:\"1\";}s:4:\"mode\";s:4:\"copy\";}}s:10:\"FormEngine\";a:2:{i:0;a:0:{}i:1;s:32:\"89494ca03c0d71614c20797c37296c5a\";}s:16:\"browse_links.php\";N;s:9:\"file_list\";a:3:{s:8:\"function\";N;s:8:\"language\";N;s:19:\"constant_editor_cat\";N;}s:10:\"web_layout\";a:3:{s:8:\"function\";s:1:\"1\";s:8:\"language\";s:1:\"0\";s:19:\"constant_editor_cat\";N;}s:16:\"opendocs::recent\";a:1:{s:32:\"89494ca03c0d71614c20797c37296c5a\";a:4:{i:0;s:5:\"admin\";i:1;a:5:{s:4:\"edit\";a:1:{s:8:\"be_users\";a:1:{i:1;s:4:\"edit\";}}s:7:\"defVals\";N;s:12:\"overrideVals\";N;s:11:\"columnsOnly\";N;s:6:\"noView\";N;}i:2;s:31:\"&edit%5Bbe_users%5D%5B1%5D=edit\";i:3;a:5:{s:5:\"table\";s:8:\"be_users\";s:3:\"uid\";i:1;s:3:\"pid\";i:0;s:3:\"cmd\";s:4:\"edit\";s:12:\"deleteAccess\";b:1;}}}}s:14:\"emailMeAtLogin\";i:0;s:8:\"titleLen\";i:50;s:8:\"edit_RTE\";s:1:\"1\";s:20:\"edit_docModuleUpload\";s:1:\"1\";s:25:\"resizeTextareas_MaxHeight\";i:500;s:4:\"lang\";s:7:\"default\";s:19:\"firstLoginTimeStamp\";i:1633799716;s:15:\"moduleSessionID\";a:8:{s:8:\"web_list\";s:40:\"59d586603c4c451a7613b5866169955b23d84b34\";s:57:\"TYPO3\\CMS\\Backend\\Utility\\BackendUtility::getUpdateSignal\";s:40:\"c4a8cf3868d2873c46707ad6e4a46e7a9666e90a\";s:9:\"clipboard\";s:40:\"59d586603c4c451a7613b5866169955b23d84b34\";s:10:\"FormEngine\";s:40:\"c4a8cf3868d2873c46707ad6e4a46e7a9666e90a\";s:16:\"browse_links.php\";s:40:\"59d586603c4c451a7613b5866169955b23d84b34\";s:9:\"file_list\";s:40:\"59d586603c4c451a7613b5866169955b23d84b34\";s:10:\"web_layout\";s:40:\"59d586603c4c451a7613b5866169955b23d84b34\";s:16:\"opendocs::recent\";s:40:\"c4a8cf3868d2873c46707ad6e4a46e7a9666e90a\";}s:17:\"BackendComponents\";a:1:{s:6:\"States\";a:2:{s:8:\"Pagetree\";a:1:{s:9:\"stateHash\";a:1:{s:3:\"0_1\";s:1:\"1\";}}s:17:\"typo3-module-menu\";a:1:{s:9:\"collapsed\";s:5:\"false\";}}}s:10:\"inlineView\";s:218:\"{\"tx_news_domain_model_news\":{\"3\":{\"sys_file_reference\":[1]},\"2\":{\"sys_file_reference\":[]},\"1\":{\"sys_file_reference\":[4]},\"7\":{\"sys_file_reference\":[5]},\"6\":{\"sys_file_reference\":[6,7]},\"5\":{\"sys_file_reference\":[8]}}}\";s:10:\"navigation\";a:1:{s:5:\"width\";s:3:\"300\";}s:10:\"modulemenu\";s:2:\"{}\";s:11:\"tx_recycler\";a:3:{s:14:\"depthSelection\";i:0;s:14:\"tableSelection\";s:0:\"\";s:11:\"resultLimit\";i:25;}}',NULL,NULL,1,NULL,1706961170,0,NULL,NULL,''),(2,0,1633799707,1633799707,0,0,0,0,0,NULL,'_cli_',0,'$argon2i$v=19$m=65536,t=16,p=1$ZlQuU051OUo4dFIwaVNiTw$emkqZDcEQmxJO0dnQxX5K0DWsTP15FFLhvoj7w+aLYg',1,NULL,'default','',NULL,0,'',NULL,'','a:9:{s:14:\"interfaceSetup\";s:0:\"\";s:10:\"moduleData\";a:0:{}s:14:\"emailMeAtLogin\";i:0;s:8:\"titleLen\";i:50;s:8:\"edit_RTE\";s:1:\"1\";s:20:\"edit_docModuleUpload\";s:1:\"1\";s:25:\"resizeTextareas_MaxHeight\";i:500;s:4:\"lang\";s:7:\"default\";s:19:\"firstLoginTimeStamp\";i:1633799707;}',NULL,NULL,1,NULL,0,0,NULL,NULL,'');
DROP TABLE IF EXISTS `cache_hash`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_hash` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  `content` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(180),`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_hash_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_hash_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `tag` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(191)),
  KEY `cache_tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_imagesizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_imagesizes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  `content` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(180),`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_imagesizes_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_imagesizes_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `tag` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(191)),
  KEY `cache_tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_news_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_news_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  `content` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(180),`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_news_category_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_news_category_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `tag` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(191)),
  KEY `cache_tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  `content` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(180),`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_pages_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_pages_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `tag` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(191)),
  KEY `cache_tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_pagesection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_pagesection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  `content` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(180),`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_pagesection_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_pagesection_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `tag` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(191)),
  KEY `cache_tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_rootline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_rootline` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  `content` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(180),`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_rootline_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_rootline_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `tag` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`(191)),
  KEY `cache_tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `cache_treelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_treelist` (
  `md5hash` varchar(32) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT 0,
  `treelist` mediumtext DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `expires` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `fe_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fe_groups` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `tx_extbase_type` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '',
  `subgroup` tinytext DEFAULT NULL,
  `TSconfig` text DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `fe_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fe_sessions` (
  `ses_id` varchar(190) NOT NULL DEFAULT '',
  `ses_iplock` varchar(39) NOT NULL DEFAULT '',
  `ses_userid` int(10) unsigned NOT NULL DEFAULT 0,
  `ses_tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `ses_data` mediumblob DEFAULT NULL,
  `ses_permanent` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ses_id`),
  KEY `ses_tstamp` (`ses_tstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `fe_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fe_users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `disable` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `tx_extbase_type` varchar(255) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `usergroup` text DEFAULT NULL,
  `name` varchar(160) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `telephone` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `uc` blob DEFAULT NULL,
  `title` varchar(40) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(40) NOT NULL DEFAULT '',
  `www` varchar(80) NOT NULL DEFAULT '',
  `company` varchar(80) NOT NULL DEFAULT '',
  `image` tinytext DEFAULT NULL,
  `TSconfig` text DEFAULT NULL,
  `lastlogin` int(10) unsigned NOT NULL DEFAULT 0,
  `is_online` int(10) unsigned NOT NULL DEFAULT 0,
  `mfa` mediumblob DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`username`(100)),
  KEY `username` (`username`(100)),
  KEY `is_online` (`is_online`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `fe_group` varchar(255) NOT NULL DEFAULT '0',
  `sorting` int(11) NOT NULL DEFAULT 0,
  `rowDescription` text DEFAULT NULL,
  `editlock` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_source` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_state` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumblob DEFAULT NULL,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `perms_userid` int(10) unsigned NOT NULL DEFAULT 0,
  `perms_groupid` int(10) unsigned NOT NULL DEFAULT 0,
  `perms_user` smallint(5) unsigned NOT NULL DEFAULT 0,
  `perms_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `perms_everybody` smallint(5) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(2048) DEFAULT NULL,
  `doktype` int(10) unsigned NOT NULL DEFAULT 0,
  `TSconfig` text DEFAULT NULL,
  `is_siteroot` smallint(6) NOT NULL DEFAULT 0,
  `php_tree_stop` smallint(6) NOT NULL DEFAULT 0,
  `url` varchar(255) NOT NULL DEFAULT '',
  `shortcut` int(10) unsigned NOT NULL DEFAULT 0,
  `shortcut_mode` int(10) unsigned NOT NULL DEFAULT 0,
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `layout` int(10) unsigned NOT NULL DEFAULT 0,
  `target` varchar(80) NOT NULL DEFAULT '',
  `media` int(10) unsigned NOT NULL DEFAULT 0,
  `lastUpdated` int(10) unsigned NOT NULL DEFAULT 0,
  `keywords` text DEFAULT NULL,
  `cache_timeout` int(10) unsigned NOT NULL DEFAULT 0,
  `cache_tags` varchar(255) NOT NULL DEFAULT '',
  `newUntil` int(10) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `no_search` smallint(5) unsigned NOT NULL DEFAULT 0,
  `SYS_LASTCHANGED` int(10) unsigned NOT NULL DEFAULT 0,
  `abstract` text DEFAULT NULL,
  `module` varchar(255) NOT NULL DEFAULT '',
  `extendToSubpages` smallint(5) unsigned NOT NULL DEFAULT 0,
  `author` varchar(255) NOT NULL DEFAULT '',
  `author_email` varchar(255) NOT NULL DEFAULT '',
  `nav_title` varchar(255) NOT NULL DEFAULT '',
  `nav_hide` smallint(6) NOT NULL DEFAULT 0,
  `content_from_pid` int(10) unsigned NOT NULL DEFAULT 0,
  `mount_pid` int(10) unsigned NOT NULL DEFAULT 0,
  `mount_pid_ol` smallint(6) NOT NULL DEFAULT 0,
  `l18n_cfg` smallint(6) NOT NULL DEFAULT 0,
  `fe_login_mode` smallint(6) NOT NULL DEFAULT 0,
  `backend_layout` varchar(64) NOT NULL DEFAULT '',
  `backend_layout_next_level` varchar(64) NOT NULL DEFAULT '',
  `tsconfig_includes` text DEFAULT NULL,
  `categories` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `determineSiteRoot` (`is_siteroot`),
  KEY `language_identifier` (`l10n_parent`,`sys_language_uid`),
  KEY `slug` (`slug`(127)),
  KEY `parent` (`pid`,`deleted`,`hidden`),
  KEY `translation_source` (`l10n_source`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `pages` VALUES (1,0,1633799707,1633799707,1,0,0,0,0,'0',512,NULL,0,0,0,0,NULL,0,NULL,0,0,0,0,1,1,31,31,1,'Home','/',1,NULL,1,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','',NULL,0),(2,1,1696569526,1633799920,1,0,0,0,0,'',256,NULL,0,0,0,0,NULL,0,'{\"doktype\":\"\",\"title\":\"\",\"slug\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"author\":\"\",\"author_email\":\"\",\"lastUpdated\":\"\",\"layout\":\"\",\"newUntil\":\"\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"content_from_pid\":\"\",\"target\":\"\",\"cache_timeout\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"\",\"no_search\":\"\",\"php_tree_stop\":\"\",\"module\":\"\",\"media\":\"\",\"tsconfig_includes\":\"\",\"TSconfig\":\"\",\"l18n_cfg\":\"\",\"hidden\":\"\",\"nav_hide\":\"\",\"starttime\":\"\",\"endtime\":\"\",\"extendToSubpages\":\"\",\"fe_group\":\"\",\"fe_login_mode\":\"\",\"editlock\":\"\",\"categories\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'Page 1','/page-1',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,1696569526,NULL,'',0,'','','',0,0,0,0,0,0,'','',NULL,0),(3,1,1633800015,1633799956,1,0,0,0,0,'',512,NULL,0,0,0,0,NULL,2,'{\"doktype\":\"\",\"title\":\"\",\"slug\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"author\":\"\",\"author_email\":\"\",\"lastUpdated\":\"\",\"layout\":\"\",\"newUntil\":\"\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"content_from_pid\":\"\",\"target\":\"\",\"cache_timeout\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"\",\"no_search\":\"\",\"php_tree_stop\":\"\",\"module\":\"\",\"media\":\"\",\"tsconfig_includes\":\"\",\"TSconfig\":\"\",\"l18n_cfg\":\"\",\"hidden\":\"\",\"nav_hide\":\"\",\"starttime\":\"\",\"endtime\":\"\",\"extendToSubpages\":\"\",\"fe_group\":\"\",\"fe_login_mode\":\"\",\"editlock\":\"\",\"categories\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'News A','/1',254,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(4,1,1696569551,1633799980,1,0,0,0,0,'',384,NULL,0,0,0,0,NULL,3,'{\"doktype\":\"\",\"title\":\"\",\"slug\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"author\":\"\",\"author_email\":\"\",\"lastUpdated\":\"\",\"layout\":\"\",\"newUntil\":\"\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"content_from_pid\":\"\",\"target\":\"\",\"cache_timeout\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"\",\"no_search\":\"\",\"php_tree_stop\":\"\",\"module\":\"\",\"media\":\"\",\"tsconfig_includes\":\"\",\"TSconfig\":\"\",\"l18n_cfg\":\"\",\"hidden\":\"\",\"nav_hide\":\"\",\"starttime\":\"\",\"endtime\":\"\",\"extendToSubpages\":\"\",\"fe_group\":\"\",\"fe_login_mode\":\"\",\"editlock\":\"\",\"categories\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'Page 2','/page-2',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(5,1,1696571144,1633800052,1,0,0,0,0,'',448,NULL,0,0,0,0,NULL,0,'{\"doktype\":\"\",\"title\":\"\",\"slug\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"author\":\"\",\"author_email\":\"\",\"lastUpdated\":\"\",\"layout\":\"\",\"newUntil\":\"\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"content_from_pid\":\"\",\"target\":\"\",\"cache_timeout\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"\",\"no_search\":\"\",\"php_tree_stop\":\"\",\"module\":\"\",\"media\":\"\",\"tsconfig_includes\":\"\",\"TSconfig\":\"\",\"l18n_cfg\":\"\",\"hidden\":\"\",\"nav_hide\":\"\",\"starttime\":\"\",\"endtime\":\"\",\"extendToSubpages\":\"\",\"fe_group\":\"\",\"fe_login_mode\":\"\",\"editlock\":\"\",\"categories\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'News','/news',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,1696571144,NULL,'',0,'','','',0,0,0,0,0,0,'','',NULL,0),(6,1,1633800080,1633800077,1,0,0,0,0,'0',768,NULL,0,0,0,0,NULL,0,'{\"hidden\":\"\"}',0,0,0,0,1,0,31,27,0,'News B','/news-b',254,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','',NULL,0),(7,0,1696569499,1696569492,1,0,1,0,0,'',512,NULL,0,1,1,1,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"1\",\"slug\":\"\\/\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"0\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"Home\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"0\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"1\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,1,31,31,1,'Home','/',1,NULL,1,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(8,0,1696569506,1696569500,1,0,1,0,0,'',512,NULL,0,2,1,1,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"1\",\"slug\":\"\\/\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"0\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"Home\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"0\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"1\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,1,31,31,1,'Home','/',1,NULL,1,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(9,1,1696569897,1696569511,1,0,0,0,0,'',256,NULL,0,1,2,2,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"1\",\"slug\":\"\\/page-1\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"{\\\"hidden\\\":\\\"\\\"}\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"0\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"Page 1\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"0\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'[DE] Page 1','/page-1',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,1696569526,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(10,1,1696569905,1696569528,1,0,0,0,0,'',256,NULL,0,2,2,2,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"1\",\"slug\":\"\\/page-1\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"{\\\"doktype\\\":\\\"\\\",\\\"title\\\":\\\"\\\",\\\"slug\\\":\\\"\\\",\\\"nav_title\\\":\\\"\\\",\\\"subtitle\\\":\\\"\\\",\\\"abstract\\\":\\\"\\\",\\\"keywords\\\":\\\"\\\",\\\"description\\\":\\\"\\\",\\\"author\\\":\\\"\\\",\\\"author_email\\\":\\\"\\\",\\\"lastUpdated\\\":\\\"\\\",\\\"layout\\\":\\\"\\\",\\\"newUntil\\\":\\\"\\\",\\\"backend_layout\\\":\\\"\\\",\\\"backend_layout_next_level\\\":\\\"\\\",\\\"content_from_pid\\\":\\\"\\\",\\\"target\\\":\\\"\\\",\\\"cache_timeout\\\":\\\"\\\",\\\"cache_tags\\\":\\\"\\\",\\\"is_siteroot\\\":\\\"\\\",\\\"no_search\\\":\\\"\\\",\\\"php_tree_stop\\\":\\\"\\\",\\\"module\\\":\\\"\\\",\\\"media\\\":\\\"\\\",\\\"tsconfig_includes\\\":\\\"\\\",\\\"TSconfig\\\":\\\"\\\",\\\"l18n_cfg\\\":\\\"\\\",\\\"hidden\\\":\\\"\\\",\\\"nav_hide\\\":\\\"\\\",\\\"starttime\\\":\\\"\\\",\\\"endtime\\\":\\\"\\\",\\\"extendToSubpages\\\":\\\"\\\",\\\"fe_group\\\":\\\"\\\",\\\"fe_login_mode\\\":\\\"\\\",\\\"editlock\\\":\\\"\\\",\\\"categories\\\":\\\"\\\",\\\"rowDescription\\\":\\\"\\\"}\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"0\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"Page 1\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"0\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'[PL] Page 1','/page-1',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(11,1,1696569930,1696569539,1,0,0,0,0,'',384,NULL,0,1,4,4,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"1\",\"slug\":\"\\/page-2\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"{\\\"hidden\\\":\\\"\\\"}\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"3\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"Page 2\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"0\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'[DE] Page 2','/page-2',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(12,1,1696571164,1696569555,1,0,0,0,0,'',448,NULL,0,2,5,5,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"1\",\"slug\":\"\\/news\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"{\\\"hidden\\\":\\\"\\\"}\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"0\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"News\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"0\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"nav_title\":\"\",\"subtitle\":\"\",\"abstract\":\"\",\"keywords\":\"\",\"description\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'[PL] News','/news',1,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(13,1,1696569594,1696569572,1,0,0,0,0,'',512,NULL,0,1,3,3,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"254\",\"slug\":\"\\/1\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"{\\\"doktype\\\":\\\"\\\",\\\"title\\\":\\\"\\\",\\\"slug\\\":\\\"\\\",\\\"nav_title\\\":\\\"\\\",\\\"subtitle\\\":\\\"\\\",\\\"abstract\\\":\\\"\\\",\\\"keywords\\\":\\\"\\\",\\\"description\\\":\\\"\\\",\\\"author\\\":\\\"\\\",\\\"author_email\\\":\\\"\\\",\\\"lastUpdated\\\":\\\"\\\",\\\"layout\\\":\\\"\\\",\\\"newUntil\\\":\\\"\\\",\\\"backend_layout\\\":\\\"\\\",\\\"backend_layout_next_level\\\":\\\"\\\",\\\"content_from_pid\\\":\\\"\\\",\\\"target\\\":\\\"\\\",\\\"cache_timeout\\\":\\\"\\\",\\\"cache_tags\\\":\\\"\\\",\\\"is_siteroot\\\":\\\"\\\",\\\"no_search\\\":\\\"\\\",\\\"php_tree_stop\\\":\\\"\\\",\\\"module\\\":\\\"\\\",\\\"media\\\":\\\"\\\",\\\"tsconfig_includes\\\":\\\"\\\",\\\"TSconfig\\\":\\\"\\\",\\\"l18n_cfg\\\":\\\"\\\",\\\"hidden\\\":\\\"\\\",\\\"nav_hide\\\":\\\"\\\",\\\"starttime\\\":\\\"\\\",\\\"endtime\\\":\\\"\\\",\\\"extendToSubpages\\\":\\\"\\\",\\\"fe_group\\\":\\\"\\\",\\\"fe_login_mode\\\":\\\"\\\",\\\"editlock\\\":\\\"\\\",\\\"categories\\\":\\\"\\\",\\\"rowDescription\\\":\\\"\\\"}\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"2\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"News A\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"0\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'News A','/news-a',254,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0),(14,1,1696569594,1696569581,1,0,0,0,0,'',512,NULL,0,2,3,3,'{\"starttime\":\"parent\",\"endtime\":\"parent\",\"nav_hide\":\"parent\",\"url\":\"parent\",\"lastUpdated\":\"parent\",\"newUntil\":\"parent\",\"no_search\":\"parent\",\"shortcut\":\"parent\",\"shortcut_mode\":\"parent\",\"content_from_pid\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"media\":\"parent\"}',0,'{\"doktype\":\"254\",\"slug\":\"\\/1\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"l10n_parent\":\"0\",\"categories\":\"0\",\"l10n_diffsource\":\"{\\\"doktype\\\":\\\"\\\",\\\"title\\\":\\\"\\\",\\\"slug\\\":\\\"\\\",\\\"nav_title\\\":\\\"\\\",\\\"subtitle\\\":\\\"\\\",\\\"abstract\\\":\\\"\\\",\\\"keywords\\\":\\\"\\\",\\\"description\\\":\\\"\\\",\\\"author\\\":\\\"\\\",\\\"author_email\\\":\\\"\\\",\\\"lastUpdated\\\":\\\"\\\",\\\"layout\\\":\\\"\\\",\\\"newUntil\\\":\\\"\\\",\\\"backend_layout\\\":\\\"\\\",\\\"backend_layout_next_level\\\":\\\"\\\",\\\"content_from_pid\\\":\\\"\\\",\\\"target\\\":\\\"\\\",\\\"cache_timeout\\\":\\\"\\\",\\\"cache_tags\\\":\\\"\\\",\\\"is_siteroot\\\":\\\"\\\",\\\"no_search\\\":\\\"\\\",\\\"php_tree_stop\\\":\\\"\\\",\\\"module\\\":\\\"\\\",\\\"media\\\":\\\"\\\",\\\"tsconfig_includes\\\":\\\"\\\",\\\"TSconfig\\\":\\\"\\\",\\\"l18n_cfg\\\":\\\"\\\",\\\"hidden\\\":\\\"\\\",\\\"nav_hide\\\":\\\"\\\",\\\"starttime\\\":\\\"\\\",\\\"endtime\\\":\\\"\\\",\\\"extendToSubpages\\\":\\\"\\\",\\\"fe_group\\\":\\\"\\\",\\\"fe_login_mode\\\":\\\"\\\",\\\"editlock\\\":\\\"\\\",\\\"categories\\\":\\\"\\\",\\\"rowDescription\\\":\\\"\\\"}\",\"layout\":\"0\",\"lastUpdated\":\"0\",\"newUntil\":\"0\",\"cache_timeout\":\"0\",\"shortcut\":\"0\",\"shortcut_mode\":\"0\",\"content_from_pid\":\"0\",\"mount_pid\":\"0\",\"module\":\"\",\"t3_origuid\":\"2\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"title\":\"News A\",\"nav_hide\":\"0\",\"url\":\"\",\"no_search\":\"0\",\"author\":\"\",\"author_email\":\"\",\"media\":\"0\",\"TSconfig\":\"\",\"php_tree_stop\":\"0\",\"editlock\":\"0\",\"fe_group\":\"\",\"extendToSubpages\":\"0\",\"target\":\"\",\"cache_tags\":\"\",\"is_siteroot\":\"0\",\"mount_pid_ol\":\"0\",\"fe_login_mode\":\"0\",\"l18n_cfg\":\"0\",\"backend_layout\":\"\",\"backend_layout_next_level\":\"\",\"tsconfig_includes\":\"\",\"rowDescription\":\"\"}',0,0,0,0,1,0,31,27,0,'News A','/news-a',254,NULL,0,0,'',0,0,'',0,'',0,0,NULL,0,'',0,NULL,0,0,NULL,'',0,'','','',0,0,0,0,0,0,'','','',0);
DROP TABLE IF EXISTS `sys_be_shortcuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_be_shortcuts` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT 0,
  `route` varchar(255) NOT NULL DEFAULT '',
  `arguments` text DEFAULT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT 0,
  `sc_group` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `event` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_category` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_state` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumblob DEFAULT NULL,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `title` tinytext NOT NULL,
  `parent` int(10) unsigned NOT NULL DEFAULT 0,
  `items` int(11) NOT NULL DEFAULT 0,
  `fe_group` varchar(100) NOT NULL DEFAULT '0',
  `images` int(10) unsigned DEFAULT 0,
  `single_pid` int(10) unsigned NOT NULL DEFAULT 0,
  `shortcut` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(100) NOT NULL DEFAULT '',
  `import_source` varchar(100) NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_description` text DEFAULT NULL,
  `seo_headline` varchar(255) NOT NULL DEFAULT '',
  `seo_text` text DEFAULT NULL,
  `slug` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `category_parent` (`parent`),
  KEY `category_list` (`pid`,`deleted`,`sys_language_uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `import` (`import_id`,`import_source`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_category` VALUES (1,3,1590836114,1590700984,1,0,0,0,0,256,'',0,0,NULL,0,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 1A',0,1,'0',0,0,0,'','','','','','','category-1a'),(2,3,1590836129,1590700995,1,0,0,0,0,512,'',0,0,NULL,0,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 2A',0,2,'0',0,0,0,'','','','','','','category-2a'),(3,3,1590836141,1590701005,1,0,0,0,0,768,'',0,0,NULL,0,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 3A',0,1,'0',0,0,0,'','','','','','','category-3a'),(4,3,1590836162,1590701015,1,0,0,0,0,1024,'',0,0,NULL,0,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 4A',0,1,'0',0,0,0,'','','','','','','category-4a'),(5,6,1590836289,1590836046,1,0,0,0,0,1024,'',0,0,NULL,4,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 4B',0,0,'0',0,0,0,'','','','','','','category-4b'),(6,6,1590836299,1590836046,1,0,0,0,0,768,'',0,0,NULL,3,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 3B',0,0,'0',0,0,0,'','','','','','','category-3b'),(7,6,1590836257,1590836046,1,0,0,0,0,512,'',0,0,NULL,2,'a:16:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"parent\";N;s:5:\"items\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:6:\"images\";N;s:11:\"description\";N;s:10:\"single_pid\";N;s:8:\"shortcut\";N;}',0,0,0,0,'Category 2B',0,2,'0',0,0,0,'','','','','','','category-2b'),(8,6,1633804060,1590836046,1,0,0,0,0,256,'',0,0,NULL,1,'{\"title\":\"\",\"slug\":\"\",\"parent\":\"\",\"items\":\"\",\"sys_language_uid\":\"\",\"hidden\":\"\",\"starttime\":\"\",\"endtime\":\"\",\"seo_title\":\"\",\"seo_description\":\"\",\"seo_headline\":\"\",\"seo_text\":\"\",\"images\":\"\",\"description\":\"\",\"single_pid\":\"\",\"shortcut\":\"\"}',0,0,0,0,'Category 1B',0,0,'0',0,0,0,'','','','','','','category-1b');
DROP TABLE IF EXISTS `sys_category_record_mm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_category_record_mm` (
  `uid_local` int(10) unsigned NOT NULL DEFAULT 0,
  `uid_foreign` int(10) unsigned NOT NULL DEFAULT 0,
  `tablenames` varchar(255) NOT NULL DEFAULT '',
  `fieldname` varchar(255) NOT NULL DEFAULT '',
  `sorting` int(10) unsigned NOT NULL DEFAULT 0,
  `sorting_foreign` int(10) unsigned NOT NULL DEFAULT 0,
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_category_record_mm` VALUES (1,1,'tx_news_domain_model_news','categories',1,1),(2,1,'tx_news_domain_model_news','categories',1,2),(2,2,'tx_news_domain_model_news','categories',2,1),(3,3,'tx_news_domain_model_news','categories',1,1),(4,3,'tx_news_domain_model_news','categories',1,2),(3,8,'tx_news_domain_model_news','categories',0,1),(4,8,'tx_news_domain_model_news','categories',0,2),(3,9,'tx_news_domain_model_news','categories',0,1),(4,9,'tx_news_domain_model_news','categories',0,2),(2,10,'tx_news_domain_model_news','categories',0,1),(1,11,'tx_news_domain_model_news','categories',0,1),(2,11,'tx_news_domain_model_news','categories',0,2);
DROP TABLE IF EXISTS `sys_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_file` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `last_indexed` int(11) NOT NULL DEFAULT 0,
  `missing` smallint(6) NOT NULL DEFAULT 0,
  `storage` int(11) NOT NULL DEFAULT 0,
  `type` varchar(10) NOT NULL DEFAULT '',
  `metadata` int(11) NOT NULL DEFAULT 0,
  `identifier` text DEFAULT NULL,
  `identifier_hash` varchar(40) NOT NULL DEFAULT '',
  `folder_hash` varchar(40) NOT NULL DEFAULT '',
  `extension` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(255) NOT NULL DEFAULT '',
  `name` tinytext DEFAULT NULL,
  `sha1` varchar(40) NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `creation_date` int(11) NOT NULL DEFAULT 0,
  `modification_date` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `sel01` (`storage`,`identifier_hash`),
  KEY `folder` (`storage`,`folder_hash`),
  KEY `tstamp` (`tstamp`),
  KEY `lastindex` (`last_indexed`),
  KEY `sha1` (`sha1`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_file` VALUES (1,0,1633800114,0,0,1,'2',0,'/user_upload/test1.jpg','984f1c74213b29590ed270a4cae8d2cfd422cf12','19669f1e02c2f16705ec7587044c66443be70725','jpg','image/jpeg','test1.jpg','c7254f44aa10b6f89e328731672eda5082fd4976',42520,1633799710,1633799710),(2,0,1633803887,0,0,1,'5',0,'/user_upload/index.html','c25533f303185517ca3e1e24b215d53aa74076d2','19669f1e02c2f16705ec7587044c66443be70725','html','application/x-empty','index.html','da39a3ee5e6b4b0d3255bfef95601890afd80709',0,1633803713,1633803713);
DROP TABLE IF EXISTS `sys_file_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_file_collection` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_state` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumblob DEFAULT NULL,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `title` tinytext DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'static',
  `files` int(11) NOT NULL DEFAULT 0,
  `storage` int(11) NOT NULL DEFAULT 0,
  `folder` text DEFAULT NULL,
  `recursive` smallint(6) NOT NULL DEFAULT 0,
  `category` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_file_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_file_metadata` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_state` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumblob DEFAULT NULL,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `file` int(11) NOT NULL DEFAULT 0,
  `title` tinytext DEFAULT NULL,
  `width` int(11) NOT NULL DEFAULT 0,
  `height` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `alternative` text DEFAULT NULL,
  `categories` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `file` (`file`),
  KEY `fal_filelist` (`l10n_parent`,`sys_language_uid`),
  KEY `parent` (`pid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_file_metadata` VALUES (1,0,1633800114,1633800114,1,0,0,NULL,0,'',0,0,0,0,1,NULL,720,449,NULL,NULL,0),(2,0,1633803887,1633803887,1,0,0,NULL,0,'',0,0,0,0,2,NULL,0,0,NULL,NULL,0);
DROP TABLE IF EXISTS `sys_file_processedfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_file_processedfile` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `crdate` int(11) NOT NULL DEFAULT 0,
  `storage` int(11) NOT NULL DEFAULT 0,
  `original` int(11) NOT NULL DEFAULT 0,
  `identifier` varchar(512) NOT NULL DEFAULT '',
  `name` tinytext DEFAULT NULL,
  `processing_url` text DEFAULT NULL,
  `configuration` blob DEFAULT NULL,
  `configurationsha1` varchar(40) NOT NULL DEFAULT '',
  `originalfilesha1` varchar(40) NOT NULL DEFAULT '',
  `task_type` varchar(200) NOT NULL DEFAULT '',
  `checksum` varchar(32) NOT NULL DEFAULT '',
  `width` int(11) DEFAULT 0,
  `height` int(11) DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `combined_1` (`original`,`task_type`(100),`configurationsha1`),
  KEY `identifier` (`storage`,`identifier`(180))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_file_reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_file_reference` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_state` text DEFAULT NULL,
  `l10n_diffsource` mediumblob DEFAULT NULL,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `uid_local` int(11) NOT NULL DEFAULT 0,
  `uid_foreign` int(11) NOT NULL DEFAULT 0,
  `tablenames` varchar(64) NOT NULL DEFAULT '',
  `fieldname` varchar(64) NOT NULL DEFAULT '',
  `sorting_foreign` int(11) NOT NULL DEFAULT 0,
  `table_local` varchar(64) NOT NULL DEFAULT '',
  `title` tinytext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `alternative` text DEFAULT NULL,
  `link` varchar(1024) NOT NULL DEFAULT '',
  `crop` varchar(4000) NOT NULL DEFAULT '',
  `autoplay` smallint(6) NOT NULL DEFAULT 0,
  `showinpreview` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `tablenames_fieldname` (`tablenames`(32),`fieldname`(12)),
  KEY `deleted` (`deleted`),
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`),
  KEY `combined_1` (`l10n_parent`,`t3ver_oid`,`t3ver_wsid`,`t3ver_state`,`deleted`),
  KEY `parent` (`pid`,`deleted`,`hidden`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_file_reference` VALUES (1,3,1633800117,1633800117,1,0,0,0,0,NULL,'',0,0,0,0,1,3,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(2,3,1633804041,1633800128,1,0,0,0,0,NULL,'{\"hidden\":\"\"}',0,0,0,0,1,2,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(3,3,1633804041,1633800128,1,0,0,0,0,NULL,'{\"hidden\":\"\"}',0,0,0,0,1,2,'tx_news_domain_model_news','fal_media',2,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(4,3,1633800135,1633800135,1,0,0,0,0,NULL,'',0,0,0,0,1,1,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(5,6,1633803832,1633800152,1,0,0,0,0,NULL,'{\"showinpreview\":\"\",\"alternative\":\"\",\"description\":\"\",\"link\":\"\",\"title\":\"\",\"crop\":\"\",\"uid_local\":\"\",\"hidden\":\"\",\"sys_language_uid\":\"\"}',0,0,0,0,1,7,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(6,6,1633800161,1633800161,1,0,0,0,0,NULL,'',0,0,0,0,1,6,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(7,6,1633800161,1633800161,1,0,0,0,0,NULL,'',0,0,0,0,1,6,'tx_news_domain_model_news','fal_media',2,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(8,6,1633800167,1633800167,1,0,0,0,0,NULL,'',0,0,0,0,1,5,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(9,3,1696569623,1696569623,1,0,0,1,1,NULL,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"\",\"hidden\":\"0\",\"sorting_foreign\":\"1\",\"table_local\":\"sys_file\",\"autoplay\":\"0\",\"showinpreview\":\"0\",\"sys_language_uid\":\"0\",\"uid_local\":\"1\",\"uid_foreign\":\"3\",\"tablenames\":\"tx_news_domain_model_news\",\"fieldname\":\"fal_media\",\"title\":\"\",\"description\":\"\",\"alternative\":\"\",\"link\":\"\",\"crop\":\"{\\\"default\\\":{\\\"cropArea\\\":{\\\"x\\\":0,\\\"y\\\":0,\\\"width\\\":1,\\\"height\\\":1},\\\"selectedRatio\\\":\\\"NaN\\\",\\\"focusArea\\\":null}}\"}',0,0,0,0,1,8,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(10,3,1696569635,1696569635,1,0,0,2,1,NULL,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"\",\"hidden\":\"0\",\"sorting_foreign\":\"1\",\"table_local\":\"sys_file\",\"autoplay\":\"0\",\"showinpreview\":\"0\",\"sys_language_uid\":\"0\",\"uid_local\":\"1\",\"uid_foreign\":\"3\",\"tablenames\":\"tx_news_domain_model_news\",\"fieldname\":\"fal_media\",\"title\":\"\",\"description\":\"\",\"alternative\":\"\",\"link\":\"\",\"crop\":\"{\\\"default\\\":{\\\"cropArea\\\":{\\\"x\\\":0,\\\"y\\\":0,\\\"width\\\":1,\\\"height\\\":1},\\\"selectedRatio\\\":\\\"NaN\\\",\\\"focusArea\\\":null}}\"}',0,0,0,0,1,9,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(11,3,1696569652,1696569652,1,0,0,1,2,NULL,'{\"hidden\":\"0\",\"l10n_parent\":\"0\",\"l10n_diffsource\":\"{\\\"hidden\\\":\\\"\\\"}\",\"sorting_foreign\":\"1\",\"table_local\":\"sys_file\",\"autoplay\":\"0\",\"showinpreview\":\"0\",\"sys_language_uid\":\"0\",\"uid_local\":\"1\",\"uid_foreign\":\"2\",\"tablenames\":\"tx_news_domain_model_news\",\"fieldname\":\"fal_media\",\"title\":\"\",\"description\":\"\",\"alternative\":\"\",\"link\":\"\",\"crop\":\"{\\\"default\\\":{\\\"cropArea\\\":{\\\"x\\\":0,\\\"y\\\":0,\\\"width\\\":1,\\\"height\\\":1},\\\"selectedRatio\\\":\\\"NaN\\\",\\\"focusArea\\\":null}}\"}',0,0,0,0,1,10,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(12,3,1696569652,1696569652,1,0,0,1,3,NULL,'{\"hidden\":\"0\",\"l10n_parent\":\"0\",\"l10n_diffsource\":\"{\\\"hidden\\\":\\\"\\\"}\",\"sorting_foreign\":\"2\",\"table_local\":\"sys_file\",\"autoplay\":\"0\",\"showinpreview\":\"0\",\"sys_language_uid\":\"0\",\"uid_local\":\"1\",\"uid_foreign\":\"2\",\"tablenames\":\"tx_news_domain_model_news\",\"fieldname\":\"fal_media\",\"title\":\"\",\"description\":\"\",\"alternative\":\"\",\"link\":\"\",\"crop\":\"{\\\"default\\\":{\\\"cropArea\\\":{\\\"x\\\":0,\\\"y\\\":0,\\\"width\\\":1,\\\"height\\\":1},\\\"selectedRatio\\\":\\\"NaN\\\",\\\"focusArea\\\":null}}\"}',0,0,0,0,1,10,'tx_news_domain_model_news','fal_media',2,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0),(13,3,1696569664,1696569664,1,0,0,2,4,NULL,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"\",\"hidden\":\"0\",\"sorting_foreign\":\"1\",\"table_local\":\"sys_file\",\"autoplay\":\"0\",\"showinpreview\":\"0\",\"sys_language_uid\":\"0\",\"uid_local\":\"1\",\"uid_foreign\":\"1\",\"tablenames\":\"tx_news_domain_model_news\",\"fieldname\":\"fal_media\",\"title\":\"\",\"description\":\"\",\"alternative\":\"\",\"link\":\"\",\"crop\":\"{\\\"default\\\":{\\\"cropArea\\\":{\\\"x\\\":0,\\\"y\\\":0,\\\"width\\\":1,\\\"height\\\":1},\\\"selectedRatio\\\":\\\"NaN\\\",\\\"focusArea\\\":null}}\"}',0,0,0,0,1,11,'tx_news_domain_model_news','fal_media',1,'sys_file',NULL,NULL,NULL,'','{\"default\":{\"cropArea\":{\"x\":0,\"y\":0,\"width\":1,\"height\":1},\"selectedRatio\":\"NaN\",\"focusArea\":null}}',0,0);
DROP TABLE IF EXISTS `sys_file_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_file_storage` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `driver` tinytext DEFAULT NULL,
  `configuration` text DEFAULT NULL,
  `is_default` smallint(6) NOT NULL DEFAULT 0,
  `is_browsable` smallint(6) NOT NULL DEFAULT 0,
  `is_public` smallint(6) NOT NULL DEFAULT 0,
  `is_writable` smallint(6) NOT NULL DEFAULT 0,
  `is_online` smallint(6) NOT NULL DEFAULT 1,
  `auto_extract_metadata` smallint(6) NOT NULL DEFAULT 1,
  `processingfolder` tinytext DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_file_storage` VALUES (1,0,1633799890,1633799890,0,0,'This is the local fileadmin/ directory. This storage mount has been created automatically by TYPO3.','fileadmin','Local','<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<T3FlexForms>\n    <data>\n        <sheet index=\"sDEF\">\n            <language index=\"lDEF\">\n                <field index=\"basePath\">\n                    <value index=\"vDEF\">fileadmin/</value>\n                </field>\n                <field index=\"pathType\">\n                    <value index=\"vDEF\">relative</value>\n                </field>\n                <field index=\"caseSensitive\">\n                    <value index=\"vDEF\">1</value>\n                </field>\n            </language>\n        </sheet>\n    </data>\n</T3FlexForms>',1,1,1,1,1,1,NULL);
DROP TABLE IF EXISTS `sys_filemounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_filemounts` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `base` int(10) unsigned NOT NULL DEFAULT 0,
  `read_only` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_history` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `actiontype` smallint(6) NOT NULL DEFAULT 0,
  `usertype` varchar(2) NOT NULL DEFAULT 'BE',
  `userid` int(10) unsigned DEFAULT NULL,
  `originaluserid` int(10) unsigned DEFAULT NULL,
  `recuid` int(11) NOT NULL DEFAULT 0,
  `tablename` varchar(255) NOT NULL DEFAULT '',
  `history_data` mediumtext DEFAULT NULL,
  `workspace` int(11) DEFAULT 0,
  `correlation_id` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `recordident_1` (`tablename`(100),`recuid`),
  KEY `recordident_2` (`tablename`(100),`tstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_language` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `title` varchar(80) NOT NULL DEFAULT '',
  `flag` varchar(20) NOT NULL DEFAULT '',
  `language_isocode` varchar(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_lockedrecords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_lockedrecords` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `record_table` varchar(255) NOT NULL DEFAULT '',
  `record_uid` int(11) NOT NULL DEFAULT 0,
  `record_pid` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) NOT NULL DEFAULT '',
  `feuserid` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `event` (`userid`,`tstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_log` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `userid` int(10) unsigned NOT NULL DEFAULT 0,
  `action` smallint(5) unsigned NOT NULL DEFAULT 0,
  `recuid` int(10) unsigned NOT NULL DEFAULT 0,
  `tablename` varchar(255) NOT NULL DEFAULT '',
  `recpid` int(11) NOT NULL DEFAULT 0,
  `error` smallint(5) unsigned NOT NULL DEFAULT 0,
  `details` text DEFAULT NULL,
  `type` smallint(5) unsigned NOT NULL DEFAULT 0,
  `channel` varchar(20) NOT NULL DEFAULT 'default',
  `details_nr` smallint(6) NOT NULL DEFAULT 0,
  `IP` varchar(39) NOT NULL DEFAULT '',
  `log_data` text DEFAULT NULL,
  `event_pid` int(11) NOT NULL DEFAULT -1,
  `workspace` int(11) NOT NULL DEFAULT 0,
  `NEWid` varchar(30) NOT NULL DEFAULT '',
  `request_id` varchar(13) NOT NULL DEFAULT '',
  `time_micro` double NOT NULL DEFAULT 0,
  `component` varchar(255) NOT NULL DEFAULT '',
  `level` varchar(10) NOT NULL DEFAULT 'info',
  `message` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `event` (`userid`,`event_pid`),
  KEY `recuidIdx` (`recuid`),
  KEY `user_auth` (`type`,`action`,`tstamp`),
  KEY `request` (`request_id`),
  KEY `combined_1` (`tstamp`,`type`,`userid`),
  KEY `errorcount` (`tstamp`,`error`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_news` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`deleted`,`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `sys_refindex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_refindex` (
  `hash` varchar(32) NOT NULL DEFAULT '',
  `tablename` varchar(255) NOT NULL DEFAULT '',
  `recuid` int(11) NOT NULL DEFAULT 0,
  `field` varchar(64) NOT NULL DEFAULT '',
  `flexpointer` varchar(255) NOT NULL DEFAULT '',
  `softref_key` varchar(30) NOT NULL DEFAULT '',
  `softref_id` varchar(40) NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT 0,
  `workspace` int(11) NOT NULL DEFAULT 0,
  `ref_table` varchar(255) NOT NULL DEFAULT '',
  `ref_uid` int(11) NOT NULL DEFAULT 0,
  `ref_string` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`hash`),
  KEY `lookup_rec` (`tablename`(100),`recuid`),
  KEY `lookup_uid` (`ref_table`(100),`ref_uid`),
  KEY `lookup_string` (`ref_string`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_refindex` VALUES ('025f1f6eee5286fe817c88a57b8e5cea','sys_file_reference',5,'uid_local','','','',0,0,'sys_file',1,''),('0d4ef9b00ac543f735f74996b52cd33a','sys_category',4,'items','','','',0,0,'tx_news_domain_model_news',8,''),('104010df5c2348b208e03e6331059ea8','pages',11,'l10n_parent','','','',0,0,'pages',4,''),('15c7256ae9855ac865059bccec380298','tx_news_domain_model_news',1,'fal_media','','','',0,0,'sys_file_reference',4,''),('1828eac71a8b42ac0fe1e53b14681de3','sys_file_reference',13,'uid_local','','','',0,0,'sys_file',1,''),('1838aef14697c24cb13777052a85fee8','tx_news_domain_model_news',11,'tags','','','',0,0,'tx_news_domain_model_tag',1,''),('1c9b8784c1518ef7b22704c4fc698ca9','sys_file',2,'storage','','','',0,0,'sys_file_storage',1,''),('208f7bd2e1b5ab45f03d3140e3c104ba','tx_news_domain_model_news',3,'tags','','','',0,0,'tx_news_domain_model_tag',4,''),('269739d9a86c5de2a74e434d72607025','sys_category',1,'items','','','',1,0,'tx_news_domain_model_news',1,''),('2f165cd60efe1651c10c393df2906d42','tx_news_domain_model_news',8,'tags','','','',0,0,'tx_news_domain_model_tag',4,''),('3085a5067fd96db1d6d8099b9aa59233','sys_file_reference',10,'uid_local','','','',0,0,'sys_file',1,''),('3122a9e512a9b5afd37ccbd37f9dc862','pages',10,'l10n_parent','','','',0,0,'pages',2,''),('331d2c64ad927983a055c9daf881dd73','pages',13,'l10n_parent','','','',0,0,'pages',3,''),('356d8c8430e5aee89c837cc05d598f95','pages',14,'l10n_parent','','','',0,0,'pages',3,''),('35c6f0822bc855568b7e26f15cc3ee13','tx_news_domain_model_news',5,'tags','','','',1,0,'tx_news_domain_model_tag',9,''),('38a5116489b0ea322fb1287471da05f3','tx_news_domain_model_news',6,'tags','','','',0,0,'tx_news_domain_model_tag',6,''),('39433ea4a82060704109046e4828d3c8','sys_file',1,'storage','','','',0,0,'sys_file_storage',1,''),('3d486c3af7dc9aa218c0556d79b2ecb6','tx_news_domain_model_news',8,'l10n_parent','','','',0,0,'tx_news_domain_model_news',3,''),('3f088edd4b1a25505f275588bb25cd25','tx_news_domain_model_news',10,'tags','','','',1,0,'tx_news_domain_model_tag',2,''),('436176d28ba978caa887914c7a18bfec','tx_news_domain_model_news',9,'fal_media','','','',0,0,'sys_file_reference',10,''),('48928923aa425eec63f3ceef8fc417e7','tx_news_domain_model_news',2,'fal_media','','','',0,0,'sys_file_reference',2,''),('4a4d76413a2e762784f3c34525f4cf74','sys_category',2,'items','','','',0,0,'tx_news_domain_model_news',10,''),('4d4222bf577fc02b40b51a70014e2a1c','tx_news_domain_model_news',1,'tags','','','',0,0,'tx_news_domain_model_tag',1,''),('50740641522afdd0c4270071cb77a370','tx_news_domain_model_news',11,'tags','','','',1,0,'tx_news_domain_model_tag',4,''),('56e2d3fc74ab3f23bb90a93840b5d902','tx_news_domain_model_news',10,'fal_media','','','',1,0,'sys_file_reference',12,''),('58ae85d135e7e6d334dad2673c93fef5','tx_news_domain_model_news',9,'tags','','','',0,0,'tx_news_domain_model_tag',4,''),('5955f58c491e9c6dbd054fb43618cee2','sys_file_reference',8,'uid_local','','','',0,0,'sys_file',1,''),('5a7681a36a27cd9321d9a8a58a8861f3','tx_news_domain_model_news',5,'tags','','','',0,0,'tx_news_domain_model_tag',6,''),('5e37e1a65bc4a742d9fe5dd54c9a3de0','tt_content',2,'l18n_parent','','','',0,0,'tt_content',1,''),('64535992eebb2a6607bb378f02f59b34','tx_news_domain_model_news',6,'tags','','','',1,0,'tx_news_domain_model_tag',7,''),('64ac6914c4888f3cbd1347fc9501e2cd','pages',8,'l10n_parent','','','',0,0,'pages',1,''),('65023825a789cd826077b81ea8f2bdf6','sys_file_reference',12,'l10n_parent','','','',0,0,'sys_file_reference',3,''),('6530eefd0cbadcf894a1718b943aed39','tx_news_domain_model_news',10,'l10n_parent','','','',0,0,'tx_news_domain_model_news',2,''),('66576d2c3f5a380a66e032911986bc5c','sys_file_reference',9,'l10n_parent','','','',0,0,'sys_file_reference',1,''),('681f9be41ae9b0ce63c93b0b8f43316c','sys_file_reference',13,'l10n_parent','','','',0,0,'sys_file_reference',4,''),('703c2f3f9231619ab43c1c49c626b3a6','sys_category',1,'items','','','',0,0,'tx_news_domain_model_news',11,''),('70b8c6f79803cadb8c9b422f07c29651','sys_file_reference',11,'uid_local','','','',0,0,'sys_file',1,''),('7267ce2d32d2bc825e240b21023de59e','tx_news_domain_model_news',6,'fal_media','','','',0,0,'sys_file_reference',6,''),('75834bb3a84e1e2ff3a77350c96fab8a','pages',9,'l10n_parent','','','',0,0,'pages',2,''),('77e2a20b10c660329e00babb2903a939','tx_news_domain_model_news',10,'tags','','','',0,0,'tx_news_domain_model_tag',1,''),('79e9a94b125e57d32c09739c8b5ad309','sys_file_reference',1,'uid_local','','','',0,0,'sys_file',1,''),('7cd101cba695ca8e8f823fa3f92dec4f','sys_category',2,'items','','','',2,0,'tx_news_domain_model_news',1,''),('84ff82a67cd289d0e2439163972de22d','sys_file_reference',2,'uid_local','','','',0,0,'sys_file',1,''),('85479616fc3366451cbb16f71f92662c','sys_file_reference',3,'uid_local','','','',0,0,'sys_file',1,''),('855ce737d6008b04488b33fc90dcf88a','tx_news_domain_model_news',1,'tags','','','',1,0,'tx_news_domain_model_tag',4,''),('89aeacc022a98abf50534b791f5c086a','tx_news_domain_model_news',10,'fal_media','','','',0,0,'sys_file_reference',11,''),('8e2d62986cbbd59542d0940f12f460bd','sys_category',3,'items','','','',0,0,'tx_news_domain_model_news',8,''),('91149eeb378f7dbc3e1f197a169ad27a','tx_news_domain_model_news',11,'l10n_parent','','','',0,0,'tx_news_domain_model_news',1,''),('945c2b311c7c58b61282e1f856083b57','sys_category',3,'items','','','',1,0,'tx_news_domain_model_news',9,''),('96d0535a8f01d54e7423a5ae699d8c6f','sys_file_reference',12,'uid_local','','','',0,0,'sys_file',1,''),('998b97d158968f49ac5098ed7fc91314','sys_file_reference',9,'uid_local','','','',0,0,'sys_file',1,''),('9a1376719559acf262632a5819b7edc8','sys_category',2,'items','','','',3,0,'tx_news_domain_model_news',2,''),('9bd9be5be3631e00369b3dc4a4a61523','tx_news_domain_model_news',2,'fal_media','','','',1,0,'sys_file_reference',3,''),('a0b1243c8e8c9d79d522c7d10c56509f','pages',12,'l10n_parent','','','',0,0,'pages',5,''),('a0e3a2b8dc510c9c2ef7602e50ea5286','tx_news_domain_model_news',7,'fal_media','','','',0,0,'sys_file_reference',5,''),('a43f63608f0f9aff3f3c00adceebb185','tx_news_domain_model_news',9,'l10n_parent','','','',0,0,'tx_news_domain_model_news',3,''),('ac2d04b2e85c6906c948411fd1dd8b97','sys_file_reference',7,'uid_local','','','',0,0,'sys_file',1,''),('b2b8df62938fee399e6d20780e6d59fd','tx_news_domain_model_news',11,'fal_media','','','',0,0,'sys_file_reference',13,''),('bddf66c6f0e95c5a92cdbfc82f68f903','sys_file_reference',11,'l10n_parent','','','',0,0,'sys_file_reference',2,''),('bfae1455b2ea591a2ee15382afd432ae','sys_file_reference',10,'l10n_parent','','','',0,0,'sys_file_reference',1,''),('c65823ddb6767dd79857b1820d91f4ab','sys_category',3,'items','','','',2,0,'tx_news_domain_model_news',3,''),('c9f610514e2c64a813c56db2b030fc86','sys_category',2,'items','','','',1,0,'tx_news_domain_model_news',11,''),('d13e09e40e4504d476ed743c2a74b7d5','tx_news_domain_model_news',6,'fal_media','','','',1,0,'sys_file_reference',7,''),('d416f2db396bce4c66c16ba5c265244d','sys_file_reference',6,'uid_local','','','',0,0,'sys_file',1,''),('d828e8b6135fd9b3450090cd0f0bccf6','sys_category',4,'items','','','',2,0,'tx_news_domain_model_news',3,''),('dca1f8f1ea47222d3f33967e34d3e879','sys_file_reference',4,'uid_local','','','',0,0,'sys_file',1,''),('e013cfc353e654f398462f116078bdda','tx_news_domain_model_news',5,'fal_media','','','',0,0,'sys_file_reference',8,''),('e09d27a40ea90fb52b7c16275b8e85e8','tx_news_domain_model_news',3,'fal_media','','','',0,0,'sys_file_reference',1,''),('e6f0bd011837afedb8438fa373706eff','pages',7,'l10n_parent','','','',0,0,'pages',1,''),('ef6c13aba3c1e39ef51bc12ed168b080','tx_news_domain_model_news',7,'l10n_parent','','','',0,0,'tx_news_domain_model_news',3,''),('f7067a91ff541e254147dc3cbee6af53','sys_category',4,'items','','','',1,0,'tx_news_domain_model_news',9,''),('fbc5d4cc5f733faf521a79797e59943e','tx_news_domain_model_news',2,'l10n_parent','','','',0,0,'tx_news_domain_model_news',1,''),('fee12081389753ba0aaddd048ce84626','tx_news_domain_model_news',8,'fal_media','','','',0,0,'sys_file_reference',9,'');
DROP TABLE IF EXISTS `sys_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_registry` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entry_namespace` varchar(128) NOT NULL DEFAULT '',
  `entry_key` varchar(128) NOT NULL DEFAULT '',
  `entry_value` mediumblob DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `entry_identifier` (`entry_namespace`,`entry_key`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_registry` VALUES (1,'installUpdate','TYPO3\\CMS\\Install\\Updates\\FeeditExtractionUpdate','i:1;'),(2,'installUpdate','TYPO3\\CMS\\Install\\Updates\\TaskcenterExtractionUpdate','i:1;'),(3,'installUpdate','TYPO3\\CMS\\Install\\Updates\\SysActionExtractionUpdate','i:1;'),(4,'installUpdate','TYPO3\\CMS\\Install\\Updates\\SvgFilesSanitization','i:1;'),(5,'installUpdate','TYPO3\\CMS\\Install\\Updates\\ShortcutRecordsMigration','i:1;'),(6,'installUpdate','TYPO3\\CMS\\Install\\Updates\\CollectionsExtractionUpdate','i:1;'),(7,'installUpdate','TYPO3\\CMS\\Install\\Updates\\BackendUserLanguageMigration','i:1;'),(8,'installUpdate','TYPO3\\CMS\\Install\\Updates\\SysLogChannel','i:1;'),(9,'installUpdate','GeorgRinger\\News\\Updates\\RealurlAliasNewsSlugUpdater','i:1;'),(10,'installUpdate','GeorgRinger\\News\\Updates\\NewsSlugUpdater','i:1;'),(11,'installUpdate','GeorgRinger\\News\\Updates\\PopulateCategorySlugs','i:1;'),(12,'installUpdate','GeorgRinger\\News\\Updates\\PopulateTagSlugs','i:1;'),(13,'installUpdateRows','rowUpdatersDone','a:4:{i:0;s:69:\"TYPO3\\CMS\\Install\\Updates\\RowUpdater\\WorkspaceVersionRecordsMigration\";i:1;s:66:\"TYPO3\\CMS\\Install\\Updates\\RowUpdater\\L18nDiffsourceToJsonMigration\";i:2;s:77:\"TYPO3\\CMS\\Install\\Updates\\RowUpdater\\WorkspaceMovePlaceholderRemovalMigration\";i:3;s:76:\"TYPO3\\CMS\\Install\\Updates\\RowUpdater\\WorkspaceNewPlaceholderRemovalMigration\";}'),(14,'extensionDataImport','typo3/sysext/core/ext_tables_static+adt.sql','s:0:\"\";'),(15,'extensionDataImport','typo3/sysext/extbase/ext_tables_static+adt.sql','s:0:\"\";'),(16,'extensionDataImport','typo3/sysext/fluid/ext_tables_static+adt.sql','s:0:\"\";'),(17,'extensionDataImport','typo3/sysext/install/ext_tables_static+adt.sql','s:0:\"\";'),(18,'extensionDataImport','typo3/sysext/recordlist/ext_tables_static+adt.sql','s:0:\"\";'),(19,'extensionDataImport','typo3/sysext/backend/ext_tables_static+adt.sql','s:0:\"\";'),(20,'extensionDataImport','typo3/sysext/extensionmanager/ext_tables_static+adt.sql','s:0:\"\";'),(21,'extensionDataImport','typo3/sysext/filelist/ext_tables_static+adt.sql','s:0:\"\";'),(22,'extensionDataImport','typo3/sysext/frontend/ext_tables_static+adt.sql','s:0:\"\";'),(23,'extensionDataImport','helhum/typo3-console/ext_tables_static+adt.sql','s:0:\"\";'),(24,'extensionDataImport','typo3conf/ext/news/ext_tables_static+adt.sql','s:0:\"\";'),(25,'extensionDataImport','les_static+adt.sql','s:0:\"\";'),(26,'core','formProtectionSessionToken:1','s:64:\"f7efcce99f36cf8236cd190515998998e60c8d90da5525525e4ab1938fd2d8e6\";'),(27,'installUpdate','GeorgRinger\\News\\Updates\\PluginUpdater','i:1;');
DROP TABLE IF EXISTS `sys_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_template` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `root` smallint(5) unsigned NOT NULL DEFAULT 0,
  `clear` smallint(5) unsigned NOT NULL DEFAULT 0,
  `include_static_file` text DEFAULT NULL,
  `constants` text DEFAULT NULL,
  `config` text DEFAULT NULL,
  `basedOn` tinytext DEFAULT NULL,
  `includeStaticAfterBasedOn` smallint(5) unsigned NOT NULL DEFAULT 0,
  `static_file_mode` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `roottemplate` (`deleted`,`hidden`,`root`),
  KEY `parent` (`pid`,`deleted`,`hidden`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `sys_template` VALUES (1,1,1696571074,1633803717,1,0,0,0,0,0,'This is an Empty Site Package TypoScript template.\r\n\r\nFor each website you need a TypoScript template on the main page of your website (on the top level). For better maintenance all TypoScript should be extracted into external files via @import \'EXT:site_myproject/Configuration/TypoScript/setup.typoscript\'',0,0,0,0,0,'Main TypoScript Rendering',1,1,'EXT:t3apinews/Configuration/TypoScript','','page = PAGE\r\npage.10 = TEXT\r\npage.10.value (\r\n   <div style=\"width: 800px; margin: 15% auto;\">\r\n      <div style=\"width: 300px;\">\r\n        <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 150 42\"><path d=\"M60.2 14.4v27h-3.8v-27h-6.7v-3.3h17.1v3.3h-6.6zm20.2 12.9v14h-3.9v-14l-7.7-16.2h4.1l5.7 12.2 5.7-12.2h3.9l-7.8 16.2zm19.5 2.6h-3.6v11.4h-3.8V11.1s3.7-.3 7.3-.3c6.6 0 8.5 4.1 8.5 9.4 0 6.5-2.3 9.7-8.4 9.7m.4-16c-2.4 0-4.1.3-4.1.3v12.6h4.1c2.4 0 4.1-1.6 4.1-6.3 0-4.4-1-6.6-4.1-6.6m21.5 27.7c-7.1 0-9-5.2-9-15.8 0-10.2 1.9-15.1 9-15.1s9 4.9 9 15.1c.1 10.6-1.8 15.8-9 15.8m0-27.7c-3.9 0-5.2 2.6-5.2 12.1 0 9.3 1.3 12.4 5.2 12.4 3.9 0 5.2-3.1 5.2-12.4 0-9.4-1.3-12.1-5.2-12.1m19.9 27.7c-2.1 0-5.3-.6-5.7-.7v-3.1c1 .2 3.7.7 5.6.7 2.2 0 3.6-1.9 3.6-5.2 0-3.9-.6-6-3.7-6H138V24h3.1c3.5 0 3.7-3.6 3.7-5.3 0-3.4-1.1-4.8-3.2-4.8-1.9 0-4.1.5-5.3.7v-3.2c.5-.1 3-.7 5.2-.7 4.4 0 7 1.9 7 8.3 0 2.9-1 5.5-3.3 6.3 2.6.2 3.8 3.1 3.8 7.3 0 6.6-2.5 9-7.3 9\"/><path fill=\"#FF8700\" d=\"M31.7 28.8c-.6.2-1.1.2-1.7.2-5.2 0-12.9-18.2-12.9-24.3 0-2.2.5-3 1.3-3.6C12 1.9 4.3 4.2 1.9 7.2 1.3 8 1 9.1 1 10.6c0 9.5 10.1 31 17.3 31 3.3 0 8.8-5.4 13.4-12.8M28.4.5c6.6 0 13.2 1.1 13.2 4.8 0 7.6-4.8 16.7-7.2 16.7-4.4 0-9.9-12.1-9.9-18.2C24.5 1 25.6.5 28.4.5\"/></svg>\r\n      </div>\r\n      <h4 style=\"font-family: sans-serif;\">Welcome to a default website made with <a href=\"https://typo3.org\">TYPO3</a></h4>\r\n   </div>\r\n)\r\npage.100 = CONTENT\r\npage.100 {\r\n    table = tt_content\r\n    select {\r\n        orderBy = sorting\r\n        where = {#colPos}=0\r\n    }\r\n}\r\n',NULL,0,0);
DROP TABLE IF EXISTS `tt_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_content` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rowDescription` text DEFAULT NULL,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(10) unsigned NOT NULL DEFAULT 0,
  `crdate` int(10) unsigned NOT NULL DEFAULT 0,
  `cruser_id` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `starttime` int(10) unsigned NOT NULL DEFAULT 0,
  `endtime` int(10) unsigned NOT NULL DEFAULT 0,
  `fe_group` varchar(255) NOT NULL DEFAULT '0',
  `sorting` int(11) NOT NULL DEFAULT 0,
  `editlock` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l18n_parent` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_source` int(10) unsigned NOT NULL DEFAULT 0,
  `l10n_state` text DEFAULT NULL,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `l18n_diffsource` mediumblob DEFAULT NULL,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `CType` varchar(255) NOT NULL DEFAULT '',
  `header` varchar(255) NOT NULL DEFAULT '',
  `header_position` varchar(255) NOT NULL DEFAULT '',
  `bodytext` mediumtext DEFAULT NULL,
  `bullets_type` smallint(5) unsigned NOT NULL DEFAULT 0,
  `uploads_description` smallint(5) unsigned NOT NULL DEFAULT 0,
  `uploads_type` smallint(5) unsigned NOT NULL DEFAULT 0,
  `assets` int(10) unsigned NOT NULL DEFAULT 0,
  `image` int(10) unsigned NOT NULL DEFAULT 0,
  `imagewidth` int(10) unsigned NOT NULL DEFAULT 0,
  `imageorient` smallint(5) unsigned NOT NULL DEFAULT 0,
  `imagecols` smallint(5) unsigned NOT NULL DEFAULT 0,
  `imageborder` smallint(5) unsigned NOT NULL DEFAULT 0,
  `media` int(10) unsigned NOT NULL DEFAULT 0,
  `layout` int(10) unsigned NOT NULL DEFAULT 0,
  `frame_class` varchar(60) NOT NULL DEFAULT 'default',
  `cols` int(10) unsigned NOT NULL DEFAULT 0,
  `space_before_class` varchar(60) NOT NULL DEFAULT '',
  `space_after_class` varchar(60) NOT NULL DEFAULT '',
  `records` text DEFAULT NULL,
  `pages` text DEFAULT NULL,
  `colPos` int(10) unsigned NOT NULL DEFAULT 0,
  `subheader` varchar(255) NOT NULL DEFAULT '',
  `header_link` varchar(1024) NOT NULL DEFAULT '',
  `image_zoom` smallint(5) unsigned NOT NULL DEFAULT 0,
  `header_layout` varchar(30) NOT NULL DEFAULT '0',
  `list_type` varchar(255) NOT NULL DEFAULT '',
  `sectionIndex` smallint(5) unsigned NOT NULL DEFAULT 0,
  `linkToTop` smallint(5) unsigned NOT NULL DEFAULT 0,
  `file_collections` text DEFAULT NULL,
  `filelink_size` smallint(5) unsigned NOT NULL DEFAULT 0,
  `filelink_sorting` varchar(64) NOT NULL DEFAULT '',
  `filelink_sorting_direction` varchar(4) NOT NULL DEFAULT '',
  `target` varchar(30) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `recursive` smallint(5) unsigned NOT NULL DEFAULT 0,
  `imageheight` int(10) unsigned NOT NULL DEFAULT 0,
  `pi_flexform` mediumtext DEFAULT NULL,
  `accessibility_title` varchar(30) NOT NULL DEFAULT '',
  `accessibility_bypass` smallint(5) unsigned NOT NULL DEFAULT 0,
  `accessibility_bypass_text` varchar(30) NOT NULL DEFAULT '',
  `category_field` varchar(64) NOT NULL DEFAULT '',
  `table_class` varchar(60) NOT NULL DEFAULT '',
  `table_caption` varchar(255) DEFAULT NULL,
  `table_delimiter` smallint(5) unsigned NOT NULL DEFAULT 0,
  `table_enclosure` smallint(5) unsigned NOT NULL DEFAULT 0,
  `table_header_position` smallint(5) unsigned NOT NULL DEFAULT 0,
  `table_tfoot` smallint(5) unsigned NOT NULL DEFAULT 0,
  `categories` int(10) unsigned NOT NULL DEFAULT 0,
  `selected_categories` longtext DEFAULT NULL,
  `tx_news_related_news` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`,`sorting`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `language` (`l18n_parent`,`sys_language_uid`),
  KEY `translation_source` (`l10n_source`),
  KEY `index_newscontent` (`tx_news_related_news`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `tt_content` VALUES (1,'',5,1696571118,1696571118,1,0,0,0,0,'',256,0,0,0,0,NULL,0,'',0,0,0,0,'news_pi1','','',NULL,0,0,0,0,0,0,0,2,0,0,0,'default',0,'','',NULL,NULL,0,'','',0,'0','',1,0,NULL,0,'','','',0,0,0,'<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<T3FlexForms>\n    <data>\n        <sheet index=\"sDEF\">\n            <language index=\"lDEF\">\n                <field index=\"settings.orderBy\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.orderDirection\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.categoryConjunction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.categories\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.includeSubCategories\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.archiveRestriction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.timeRestriction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.timeRestrictionHigh\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.topNewsRestriction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.startingpoint\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.recursive\">\n                    <value index=\"vDEF\"></value>\n                </field>\n            </language>\n        </sheet>\n        <sheet index=\"additional\">\n            <language index=\"lDEF\">\n                <field index=\"settings.detailPid\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.listPid\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.backPid\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.limit\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.offset\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.tags\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.hidePagination\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.list.paginate.itemsPerPage\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.topNewsFirst\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.excludeAlreadyDisplayedNews\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.disableOverrideDemand\">\n                    <value index=\"vDEF\">1</value>\n                </field>\n            </language>\n        </sheet>\n        <sheet index=\"template\">\n            <language index=\"lDEF\">\n                <field index=\"settings.media.maxWidth\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.media.maxHeight\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.cropMaxCharacters\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.templateLayout\">\n                    <value index=\"vDEF\"></value>\n                </field>\n            </language>\n        </sheet>\n    </data>\n</T3FlexForms>','',0,'','','',NULL,124,0,0,0,0,NULL,0),(2,'',5,1696571544,1696571537,1,0,0,0,0,'',512,0,2,1,1,NULL,1,'{\"CType\":\"list\",\"starttime\":\"0\",\"endtime\":\"0\",\"categories\":\"0\",\"l18n_parent\":\"0\",\"layout\":\"0\",\"frame_class\":\"default\",\"space_before_class\":\"\",\"space_after_class\":\"\",\"bullets_type\":\"0\",\"colPos\":\"0\",\"date\":\"0\",\"header_layout\":\"0\",\"header_position\":\"\",\"imagewidth\":\"0\",\"imageheight\":\"0\",\"imageorient\":\"0\",\"imagecols\":\"2\",\"cols\":\"0\",\"recursive\":\"0\",\"list_type\":\"news_pi1\",\"target\":\"\",\"sectionIndex\":\"1\",\"accessibility_title\":\"\",\"accessibility_bypass_text\":\"\",\"l18n_diffsource\":\"\",\"table_class\":\"\",\"table_delimiter\":\"124\",\"table_enclosure\":\"0\",\"table_header_position\":\"0\",\"table_tfoot\":\"0\",\"uploads_description\":\"0\",\"uploads_type\":\"0\",\"t3_origuid\":\"0\",\"rowDescription\":\"\",\"tstamp\":\"1696571118\",\"crdate\":\"1696571118\",\"hidden\":\"0\",\"fe_group\":\"\",\"editlock\":\"0\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"header\":\"\",\"bodytext\":\"\",\"assets\":\"0\",\"image\":\"0\",\"imageborder\":\"0\",\"media\":\"0\",\"records\":\"\",\"pages\":\"\",\"subheader\":\"\",\"header_link\":\"\",\"image_zoom\":\"0\",\"linkToTop\":\"0\",\"file_collections\":\"\",\"filelink_size\":\"0\",\"filelink_sorting\":\"\",\"filelink_sorting_direction\":\"\",\"pi_flexform\":\"<?xml version=\\\"1.0\\\" encoding=\\\"utf-8\\\" standalone=\\\"yes\\\" ?>\\n<T3FlexForms>\\n    <data>\\n        <sheet index=\\\"sDEF\\\">\\n            <language index=\\\"lDEF\\\">\\n                <field index=\\\"switchableControllerActions\\\">\\n                    <value index=\\\"vDEF\\\">News-&gt;list;News-&gt;detail<\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.orderBy\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.orderDirection\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.categoryConjunction\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.categories\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.includeSubCategories\\\">\\n                    <value index=\\\"vDEF\\\">0<\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.archiveRestriction\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.timeRestriction\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.timeRestrictionHigh\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.topNewsRestriction\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.startingpoint\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.recursive\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n            <\\/language>\\n        <\\/sheet>\\n        <sheet index=\\\"additional\\\">\\n            <language index=\\\"lDEF\\\">\\n                <field index=\\\"settings.detailPid\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.listPid\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.backPid\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.limit\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.offset\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.tags\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.hidePagination\\\">\\n                    <value index=\\\"vDEF\\\">0<\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.list.paginate.itemsPerPage\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.topNewsFirst\\\">\\n                    <value index=\\\"vDEF\\\">0<\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.excludeAlreadyDisplayedNews\\\">\\n                    <value index=\\\"vDEF\\\">0<\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.disableOverrideDemand\\\">\\n                    <value index=\\\"vDEF\\\">1<\\/value>\\n                <\\/field>\\n            <\\/language>\\n        <\\/sheet>\\n        <sheet index=\\\"template\\\">\\n            <language index=\\\"lDEF\\\">\\n                <field index=\\\"settings.media.maxWidth\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.media.maxHeight\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.cropMaxCharacters\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n                <field index=\\\"settings.templateLayout\\\">\\n                    <value index=\\\"vDEF\\\"><\\/value>\\n                <\\/field>\\n            <\\/language>\\n        <\\/sheet>\\n    <\\/data>\\n<\\/T3FlexForms>\",\"accessibility_bypass\":\"0\",\"category_field\":\"\",\"table_caption\":\"\",\"selected_categories\":\"\",\"tx_news_related_news\":\"0\"}',0,0,0,0,'news_pi1','','',NULL,0,0,0,0,0,0,0,2,0,0,0,'default',0,'','','','',0,'','',0,'0','',1,0,'',0,'','','',0,0,0,'<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<T3FlexForms>\n    <data>\n        <sheet index=\"sDEF\">\n            <language index=\"lDEF\">\n                <field index=\"settings.orderBy\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.orderDirection\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.categoryConjunction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.categories\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.includeSubCategories\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.archiveRestriction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.timeRestriction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.timeRestrictionHigh\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.topNewsRestriction\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.startingpoint\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.recursive\">\n                    <value index=\"vDEF\"></value>\n                </field>\n            </language>\n        </sheet>\n        <sheet index=\"additional\">\n            <language index=\"lDEF\">\n                <field index=\"settings.detailPid\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.listPid\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.backPid\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.limit\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.offset\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.tags\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.hidePagination\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.list.paginate.itemsPerPage\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.topNewsFirst\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.excludeAlreadyDisplayedNews\">\n                    <value index=\"vDEF\">0</value>\n                </field>\n                <field index=\"settings.disableOverrideDemand\">\n                    <value index=\"vDEF\">1</value>\n                </field>\n            </language>\n        </sheet>\n        <sheet index=\"template\">\n            <language index=\"lDEF\">\n                <field index=\"settings.media.maxWidth\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.media.maxHeight\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.cropMaxCharacters\">\n                    <value index=\"vDEF\"></value>\n                </field>\n                <field index=\"settings.templateLayout\">\n                    <value index=\"vDEF\"></value>\n                </field>\n            </language>\n        </sheet>\n    </data>\n</T3FlexForms>','',0,'','','',NULL,124,0,0,0,0,'0',0);
DROP TABLE IF EXISTS `tx_extensionmanager_domain_model_extension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_extensionmanager_domain_model_extension` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0,
  `extension_key` varchar(60) NOT NULL DEFAULT '',
  `repository` int(11) NOT NULL DEFAULT 1,
  `remote` varchar(100) NOT NULL DEFAULT 'ter',
  `version` varchar(15) NOT NULL DEFAULT '',
  `alldownloadcounter` int(10) unsigned NOT NULL DEFAULT 0,
  `downloadcounter` int(10) unsigned NOT NULL DEFAULT 0,
  `title` varchar(150) NOT NULL DEFAULT '',
  `description` mediumtext DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 0,
  `review_state` int(11) NOT NULL DEFAULT 0,
  `category` int(11) NOT NULL DEFAULT 0,
  `last_updated` int(10) unsigned NOT NULL DEFAULT 0,
  `serialized_dependencies` mediumtext DEFAULT NULL,
  `author_name` varchar(255) NOT NULL DEFAULT '',
  `author_email` varchar(255) NOT NULL DEFAULT '',
  `ownerusername` varchar(50) NOT NULL DEFAULT '',
  `md5hash` varchar(35) NOT NULL DEFAULT '',
  `update_comment` mediumtext DEFAULT NULL,
  `authorcompany` varchar(255) NOT NULL DEFAULT '',
  `integer_version` int(11) NOT NULL DEFAULT 0,
  `current_version` int(11) NOT NULL DEFAULT 0,
  `lastreviewedversion` int(11) NOT NULL DEFAULT 0,
  `documentation_link` varchar(2048) DEFAULT NULL,
  `distribution_image` varchar(255) DEFAULT NULL,
  `distribution_welcome_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `versionextrepo` (`extension_key`,`version`,`remote`),
  KEY `index_extrepo` (`extension_key`,`remote`),
  KEY `index_versionrepo` (`integer_version`,`remote`,`extension_key`),
  KEY `index_currentversions` (`current_version`,`review_state`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `tx_news_domain_model_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_news_domain_model_link` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `crdate` int(11) NOT NULL DEFAULT 0,
  `cruser_id` int(11) NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(11) NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumtext DEFAULT NULL,
  `l10n_source` int(11) NOT NULL DEFAULT 0,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `deleted` smallint(6) NOT NULL DEFAULT 0,
  `hidden` smallint(6) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `l10n_state` text DEFAULT NULL,
  `parent` int(11) NOT NULL DEFAULT 0,
  `title` tinytext DEFAULT NULL,
  `uri` text DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `news` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `tx_news_domain_model_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_news_domain_model_news` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `crdate` int(11) NOT NULL DEFAULT 0,
  `cruser_id` int(11) NOT NULL DEFAULT 0,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `t3_origuid` int(10) unsigned NOT NULL DEFAULT 0,
  `editlock` smallint(6) NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(11) NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumtext DEFAULT NULL,
  `l10n_source` int(11) NOT NULL DEFAULT 0,
  `deleted` smallint(6) NOT NULL DEFAULT 0,
  `hidden` smallint(6) NOT NULL DEFAULT 0,
  `starttime` int(11) NOT NULL DEFAULT 0,
  `endtime` int(11) NOT NULL DEFAULT 0,
  `fe_group` varchar(100) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `l10n_state` text DEFAULT NULL,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `teaser` text DEFAULT NULL,
  `bodytext` mediumtext DEFAULT NULL,
  `datetime` bigint(20) NOT NULL DEFAULT 0,
  `archive` bigint(20) NOT NULL DEFAULT 0,
  `author` tinytext DEFAULT NULL,
  `author_email` tinytext DEFAULT NULL,
  `categories` int(11) NOT NULL DEFAULT 0,
  `related` int(11) NOT NULL DEFAULT 0,
  `related_from` int(11) NOT NULL DEFAULT 0,
  `related_files` tinytext DEFAULT NULL,
  `fal_related_files` int(10) unsigned DEFAULT 0,
  `related_links` int(11) NOT NULL DEFAULT 0,
  `type` varchar(100) NOT NULL DEFAULT '0',
  `keywords` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tags` int(11) NOT NULL DEFAULT 0,
  `media` text DEFAULT NULL,
  `fal_media` int(10) unsigned DEFAULT 0,
  `internalurl` text DEFAULT NULL,
  `externalurl` text DEFAULT NULL,
  `istopnews` int(11) NOT NULL DEFAULT 0,
  `content_elements` int(11) NOT NULL DEFAULT 0,
  `path_segment` varchar(2048) DEFAULT NULL,
  `alternative_title` tinytext DEFAULT NULL,
  `sitemap_changefreq` varchar(10) NOT NULL DEFAULT '',
  `sitemap_priority` decimal(2,1) NOT NULL DEFAULT 0.5,
  `import_id` varchar(100) NOT NULL DEFAULT '',
  `import_source` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `sys_language_uid_l10n_parent` (`sys_language_uid`,`l10n_parent`),
  KEY `path_segment` (`path_segment`(185),`uid`),
  KEY `import` (`import_id`,`import_source`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `tx_news_domain_model_news` VALUES (1,3,1590836228,1590700955,1,0,0,0,0,0,0,0,0,'a:27:{s:4:\"type\";N;s:9:\"istopnews\";N;s:5:\"title\";N;s:12:\"path_segment\";N;s:6:\"teaser\";N;s:8:\"datetime\";N;s:7:\"archive\";N;s:8:\"bodytext\";N;s:16:\"content_elements\";N;s:9:\"fal_media\";N;s:17:\"fal_related_files\";N;s:10:\"categories\";N;s:7:\"related\";N;s:13:\"related_links\";N;s:4:\"tags\";N;s:6:\"author\";N;s:12:\"author_email\";N;s:8:\"keywords\";N;s:11:\"description\";N;s:17:\"alternative_title\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:8:\"fe_group\";N;s:8:\"editlock\";N;s:5:\"notes\";N;}',0,0,0,0,0,'','',NULL,0,'Sed ut perspiciatis unde omnis iste natus error sit voluptatem folder A','','<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</p>',1590693600,0,'','',2,0,0,NULL,0,0,'0','','',2,NULL,1,NULL,NULL,0,0,'sed-ut-perspiciatis-unde-omnis-iste-natus-error-sit-voluptatem-folder-a','','',0.5,'',''),(2,3,1599335757,1590704324,1,0,0,0,0,1,0,0,0,'a:27:{s:4:\"type\";N;s:9:\"istopnews\";N;s:5:\"title\";N;s:12:\"path_segment\";N;s:6:\"teaser\";N;s:8:\"datetime\";N;s:7:\"archive\";N;s:8:\"bodytext\";N;s:16:\"content_elements\";N;s:9:\"fal_media\";N;s:17:\"fal_related_files\";N;s:10:\"categories\";N;s:7:\"related\";N;s:13:\"related_links\";N;s:4:\"tags\";N;s:6:\"author\";N;s:12:\"author_email\";N;s:8:\"keywords\";N;s:11:\"description\";N;s:17:\"alternative_title\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:8:\"fe_group\";N;s:8:\"editlock\";N;s:5:\"notes\";N;}',0,0,0,0,0,'','',NULL,0,'Natus error sit voluptatem folder A','','<p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas.</p>',1590695100,0,'','',1,0,0,NULL,0,0,'0','','',2,NULL,2,NULL,NULL,1,0,'natus-error-sit-voluptatem-folder-a','','',0.5,'',''),(3,3,1590836196,1590704475,1,0,0,0,0,0,0,0,0,'a:27:{s:4:\"type\";N;s:9:\"istopnews\";N;s:5:\"title\";N;s:12:\"path_segment\";N;s:6:\"teaser\";N;s:8:\"datetime\";N;s:7:\"archive\";N;s:8:\"bodytext\";N;s:16:\"content_elements\";N;s:9:\"fal_media\";N;s:17:\"fal_related_files\";N;s:10:\"categories\";N;s:7:\"related\";N;s:13:\"related_links\";N;s:4:\"tags\";N;s:6:\"author\";N;s:12:\"author_email\";N;s:8:\"keywords\";N;s:11:\"description\";N;s:17:\"alternative_title\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:8:\"fe_group\";N;s:8:\"editlock\";N;s:5:\"notes\";N;}',0,0,0,0,0,'','',NULL,0,'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder A','','<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>',1590783000,0,'','',2,0,0,NULL,0,0,'0','','',1,NULL,1,NULL,NULL,0,0,'ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-a','','',0.5,'',''),(5,6,1590836349,1590836046,1,0,0,0,0,1,0,0,0,'a:27:{s:4:\"type\";N;s:9:\"istopnews\";N;s:5:\"title\";N;s:12:\"path_segment\";N;s:6:\"teaser\";N;s:8:\"datetime\";N;s:7:\"archive\";N;s:8:\"bodytext\";N;s:16:\"content_elements\";N;s:9:\"fal_media\";N;s:17:\"fal_related_files\";N;s:10:\"categories\";N;s:7:\"related\";N;s:13:\"related_links\";N;s:4:\"tags\";N;s:6:\"author\";N;s:12:\"author_email\";N;s:8:\"keywords\";N;s:11:\"description\";N;s:17:\"alternative_title\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:8:\"fe_group\";N;s:8:\"editlock\";N;s:5:\"notes\";N;}',0,0,0,0,0,'','',NULL,0,'Sed ut perspiciatis unde omnis iste natus error sit voluptatem folder B','','<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</p>',1590693600,0,'','',0,0,0,NULL,0,0,'0','','',2,NULL,1,NULL,NULL,0,0,'sed-ut-perspiciatis-unde-omnis-iste-natus-error-sit-voluptatem-folder-b','','',0.5,'',''),(6,6,1590836334,1590836046,1,0,0,0,0,2,0,0,0,'a:27:{s:4:\"type\";N;s:9:\"istopnews\";N;s:5:\"title\";N;s:12:\"path_segment\";N;s:6:\"teaser\";N;s:8:\"datetime\";N;s:7:\"archive\";N;s:8:\"bodytext\";N;s:16:\"content_elements\";N;s:9:\"fal_media\";N;s:17:\"fal_related_files\";N;s:10:\"categories\";N;s:7:\"related\";N;s:13:\"related_links\";N;s:4:\"tags\";N;s:6:\"author\";N;s:12:\"author_email\";N;s:8:\"keywords\";N;s:11:\"description\";N;s:17:\"alternative_title\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:8:\"fe_group\";N;s:8:\"editlock\";N;s:5:\"notes\";N;}',0,0,0,0,0,'','',NULL,0,'Natus error sit voluptatem folder B','','<p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas.</p>',1590695100,0,'','',0,0,0,NULL,0,0,'0','','',2,NULL,2,NULL,NULL,0,0,'natus-error-sit-voluptatem-folder-b','','',0.5,'',''),(7,6,1590836321,1590836046,1,0,0,0,0,3,0,0,0,'a:27:{s:4:\"type\";N;s:9:\"istopnews\";N;s:5:\"title\";N;s:12:\"path_segment\";N;s:6:\"teaser\";N;s:8:\"datetime\";N;s:7:\"archive\";N;s:8:\"bodytext\";N;s:16:\"content_elements\";N;s:9:\"fal_media\";N;s:17:\"fal_related_files\";N;s:10:\"categories\";N;s:7:\"related\";N;s:13:\"related_links\";N;s:4:\"tags\";N;s:6:\"author\";N;s:12:\"author_email\";N;s:8:\"keywords\";N;s:11:\"description\";N;s:17:\"alternative_title\";N;s:16:\"sys_language_uid\";N;s:6:\"hidden\";N;s:9:\"starttime\";N;s:7:\"endtime\";N;s:8:\"fe_group\";N;s:8:\"editlock\";N;s:5:\"notes\";N;}',0,0,0,0,0,'','',NULL,0,'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder B','','<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>',1590783000,0,'','',0,0,0,NULL,0,0,'0','','',1,NULL,1,NULL,NULL,0,0,'ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-b','','',0.5,'',''),(8,3,1696569650,1696569623,1,0,0,0,0,3,0,1,3,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"a:27:{s:4:\\\"type\\\";N;s:9:\\\"istopnews\\\";N;s:5:\\\"title\\\";N;s:12:\\\"path_segment\\\";N;s:6:\\\"teaser\\\";N;s:8:\\\"datetime\\\";N;s:7:\\\"archive\\\";N;s:8:\\\"bodytext\\\";N;s:16:\\\"content_elements\\\";N;s:9:\\\"fal_media\\\";N;s:17:\\\"fal_related_files\\\";N;s:10:\\\"categories\\\";N;s:7:\\\"related\\\";N;s:13:\\\"related_links\\\";N;s:4:\\\"tags\\\";N;s:6:\\\"author\\\";N;s:12:\\\"author_email\\\";N;s:8:\\\"keywords\\\";N;s:11:\\\"description\\\";N;s:17:\\\"alternative_title\\\";N;s:16:\\\"sys_language_uid\\\";N;s:6:\\\"hidden\\\";N;s:9:\\\"starttime\\\";N;s:7:\\\"endtime\\\";N;s:8:\\\"fe_group\\\";N;s:8:\\\"editlock\\\";N;s:5:\\\"notes\\\";N;}\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"archive\":\"0\",\"istopnews\":\"0\",\"editlock\":\"0\",\"path_segment\":\"ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-a\",\"t3_origuid\":\"0\",\"tstamp\":\"1590836196\",\"crdate\":\"1590704475\",\"cruser_id\":\"1\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"fe_group\":\"\",\"notes\":\"\",\"sorting\":\"0\",\"title\":\"Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder A\",\"teaser\":\"\",\"bodytext\":\"<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.<\\/p>\",\"datetime\":\"1590783000\",\"related_from\":\"0\",\"type\":\"0\",\"internalurl\":\"\",\"externalurl\":\"\",\"alternative_title\":\"\",\"import_id\":\"\",\"import_source\":\"\",\"author\":\"\",\"author_email\":\"\",\"categories\":\"2\",\"related\":\"0\",\"related_links\":\"0\",\"keywords\":\"\",\"description\":\"\",\"content_elements\":\"0\",\"tags\":\"1\",\"fal_media\":\"1\",\"fal_related_files\":\"0\"}',3,0,0,0,0,'','','{\"starttime\":\"parent\",\"endtime\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"categories\":\"parent\",\"related\":\"parent\",\"related_links\":\"parent\",\"keywords\":\"parent\",\"description\":\"parent\",\"editlock\":\"parent\",\"content_elements\":\"parent\",\"tags\":\"parent\",\"fal_media\":\"parent\",\"fal_related_files\":\"parent\"}',0,'[DE] Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder A','','<p>[DE] At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>',1590783000,0,'','',2,0,0,NULL,0,0,'0','','',1,NULL,1,NULL,NULL,0,0,'ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-a','','',0.5,'',''),(9,3,1696569649,1696569635,1,0,0,0,0,3,0,2,3,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"a:27:{s:4:\\\"type\\\";N;s:9:\\\"istopnews\\\";N;s:5:\\\"title\\\";N;s:12:\\\"path_segment\\\";N;s:6:\\\"teaser\\\";N;s:8:\\\"datetime\\\";N;s:7:\\\"archive\\\";N;s:8:\\\"bodytext\\\";N;s:16:\\\"content_elements\\\";N;s:9:\\\"fal_media\\\";N;s:17:\\\"fal_related_files\\\";N;s:10:\\\"categories\\\";N;s:7:\\\"related\\\";N;s:13:\\\"related_links\\\";N;s:4:\\\"tags\\\";N;s:6:\\\"author\\\";N;s:12:\\\"author_email\\\";N;s:8:\\\"keywords\\\";N;s:11:\\\"description\\\";N;s:17:\\\"alternative_title\\\";N;s:16:\\\"sys_language_uid\\\";N;s:6:\\\"hidden\\\";N;s:9:\\\"starttime\\\";N;s:7:\\\"endtime\\\";N;s:8:\\\"fe_group\\\";N;s:8:\\\"editlock\\\";N;s:5:\\\"notes\\\";N;}\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"archive\":\"0\",\"istopnews\":\"0\",\"editlock\":\"0\",\"path_segment\":\"ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-a\",\"t3_origuid\":\"0\",\"tstamp\":\"1590836196\",\"crdate\":\"1590704475\",\"cruser_id\":\"1\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"fe_group\":\"\",\"notes\":\"\",\"sorting\":\"0\",\"title\":\"Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder A\",\"teaser\":\"\",\"bodytext\":\"<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.<\\/p>\",\"datetime\":\"1590783000\",\"related_from\":\"0\",\"type\":\"0\",\"internalurl\":\"\",\"externalurl\":\"\",\"alternative_title\":\"\",\"import_id\":\"\",\"import_source\":\"\",\"author\":\"\",\"author_email\":\"\",\"categories\":\"2\",\"related\":\"0\",\"related_links\":\"0\",\"keywords\":\"\",\"description\":\"\",\"content_elements\":\"0\",\"tags\":\"1\",\"fal_media\":\"1\",\"fal_related_files\":\"0\"}',3,0,0,0,0,'','','{\"starttime\":\"parent\",\"endtime\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"categories\":\"parent\",\"related\":\"parent\",\"related_links\":\"parent\",\"keywords\":\"parent\",\"description\":\"parent\",\"editlock\":\"parent\",\"content_elements\":\"parent\",\"tags\":\"parent\",\"fal_media\":\"parent\",\"fal_related_files\":\"parent\"}',0,'[PL] Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder A','','<p>[PL] At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>',1590783000,0,'','',2,0,0,NULL,0,0,'0','','',1,NULL,1,NULL,NULL,0,0,'ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-a','','',0.5,'',''),(10,3,1696569681,1696569652,1,0,0,0,0,2,0,1,2,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"a:27:{s:4:\\\"type\\\";N;s:9:\\\"istopnews\\\";N;s:5:\\\"title\\\";N;s:12:\\\"path_segment\\\";N;s:6:\\\"teaser\\\";N;s:8:\\\"datetime\\\";N;s:7:\\\"archive\\\";N;s:8:\\\"bodytext\\\";N;s:16:\\\"content_elements\\\";N;s:9:\\\"fal_media\\\";N;s:17:\\\"fal_related_files\\\";N;s:10:\\\"categories\\\";N;s:7:\\\"related\\\";N;s:13:\\\"related_links\\\";N;s:4:\\\"tags\\\";N;s:6:\\\"author\\\";N;s:12:\\\"author_email\\\";N;s:8:\\\"keywords\\\";N;s:11:\\\"description\\\";N;s:17:\\\"alternative_title\\\";N;s:16:\\\"sys_language_uid\\\";N;s:6:\\\"hidden\\\";N;s:9:\\\"starttime\\\";N;s:7:\\\"endtime\\\";N;s:8:\\\"fe_group\\\";N;s:8:\\\"editlock\\\";N;s:5:\\\"notes\\\";N;}\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"archive\":\"0\",\"istopnews\":\"1\",\"editlock\":\"0\",\"path_segment\":\"natus-error-sit-voluptatem-folder-a\",\"t3_origuid\":\"1\",\"tstamp\":\"1599335757\",\"crdate\":\"1590704324\",\"cruser_id\":\"1\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"fe_group\":\"\",\"notes\":\"\",\"sorting\":\"0\",\"title\":\"Natus error sit voluptatem folder A\",\"teaser\":\"\",\"bodytext\":\"<p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas.<\\/p>\",\"datetime\":\"1590695100\",\"related_from\":\"0\",\"type\":\"0\",\"internalurl\":\"\",\"externalurl\":\"\",\"alternative_title\":\"\",\"import_id\":\"\",\"import_source\":\"\",\"author\":\"\",\"author_email\":\"\",\"categories\":\"1\",\"related\":\"0\",\"related_links\":\"0\",\"keywords\":\"\",\"description\":\"\",\"content_elements\":\"0\",\"tags\":\"2\",\"fal_media\":\"2\",\"fal_related_files\":\"0\"}',2,0,0,0,0,'','','{\"starttime\":\"parent\",\"endtime\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"categories\":\"parent\",\"related\":\"parent\",\"related_links\":\"parent\",\"keywords\":\"parent\",\"description\":\"parent\",\"editlock\":\"parent\",\"content_elements\":\"parent\",\"tags\":\"parent\",\"fal_media\":\"parent\",\"fal_related_files\":\"parent\"}',0,'[DE] Natus error sit voluptatem folder A','','<p>[DE] Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas.</p>',1590695100,0,'','',1,0,0,NULL,0,0,'0','','',2,NULL,2,NULL,NULL,1,0,'natus-error-sit-voluptatem-folder-a','','',0.5,'',''),(11,3,1696569683,1696569664,1,0,0,0,0,1,0,2,1,'{\"l10n_parent\":\"0\",\"l10n_diffsource\":\"a:27:{s:4:\\\"type\\\";N;s:9:\\\"istopnews\\\";N;s:5:\\\"title\\\";N;s:12:\\\"path_segment\\\";N;s:6:\\\"teaser\\\";N;s:8:\\\"datetime\\\";N;s:7:\\\"archive\\\";N;s:8:\\\"bodytext\\\";N;s:16:\\\"content_elements\\\";N;s:9:\\\"fal_media\\\";N;s:17:\\\"fal_related_files\\\";N;s:10:\\\"categories\\\";N;s:7:\\\"related\\\";N;s:13:\\\"related_links\\\";N;s:4:\\\"tags\\\";N;s:6:\\\"author\\\";N;s:12:\\\"author_email\\\";N;s:8:\\\"keywords\\\";N;s:11:\\\"description\\\";N;s:17:\\\"alternative_title\\\";N;s:16:\\\"sys_language_uid\\\";N;s:6:\\\"hidden\\\";N;s:9:\\\"starttime\\\";N;s:7:\\\"endtime\\\";N;s:8:\\\"fe_group\\\";N;s:8:\\\"editlock\\\";N;s:5:\\\"notes\\\";N;}\",\"hidden\":\"0\",\"starttime\":\"0\",\"endtime\":\"0\",\"archive\":\"0\",\"istopnews\":\"0\",\"editlock\":\"0\",\"path_segment\":\"sed-ut-perspiciatis-unde-omnis-iste-natus-error-sit-voluptatem-folder-a\",\"t3_origuid\":\"0\",\"tstamp\":\"1590836228\",\"crdate\":\"1590700955\",\"cruser_id\":\"1\",\"sys_language_uid\":\"0\",\"l10n_source\":\"0\",\"fe_group\":\"\",\"notes\":\"\",\"sorting\":\"0\",\"title\":\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem folder A\",\"teaser\":\"\",\"bodytext\":\"<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?<\\/p>\",\"datetime\":\"1590693600\",\"related_from\":\"0\",\"type\":\"0\",\"internalurl\":\"\",\"externalurl\":\"\",\"alternative_title\":\"\",\"import_id\":\"\",\"import_source\":\"\",\"author\":\"\",\"author_email\":\"\",\"categories\":\"2\",\"related\":\"0\",\"related_links\":\"0\",\"keywords\":\"\",\"description\":\"\",\"content_elements\":\"0\",\"tags\":\"2\",\"fal_media\":\"1\",\"fal_related_files\":\"0\"}',1,0,0,0,0,'','','{\"starttime\":\"parent\",\"endtime\":\"parent\",\"author\":\"parent\",\"author_email\":\"parent\",\"categories\":\"parent\",\"related\":\"parent\",\"related_links\":\"parent\",\"keywords\":\"parent\",\"description\":\"parent\",\"editlock\":\"parent\",\"content_elements\":\"parent\",\"tags\":\"parent\",\"fal_media\":\"parent\",\"fal_related_files\":\"parent\"}',0,'[PL] Sed ut perspiciatis unde omnis iste natus error sit voluptatem folder A','','<p>[PL] Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</p>',1590693600,0,'','',2,0,0,NULL,0,0,'0','','',2,NULL,1,NULL,NULL,0,0,'sed-ut-perspiciatis-unde-omnis-iste-natus-error-sit-voluptatem-folder-a','','',0.5,'','');
DROP TABLE IF EXISTS `tx_news_domain_model_news_related_mm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_news_domain_model_news_related_mm` (
  `uid_local` int(11) NOT NULL DEFAULT 0,
  `uid_foreign` int(11) NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `sorting_foreign` int(11) NOT NULL DEFAULT 0,
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `tx_news_domain_model_news_tag_mm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_news_domain_model_news_tag_mm` (
  `uid_local` int(11) NOT NULL DEFAULT 0,
  `uid_foreign` int(11) NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `sorting_foreign` int(10) unsigned NOT NULL DEFAULT 0,
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `tx_news_domain_model_news_tag_mm` VALUES (1,1,1,0),(1,4,2,0),(2,1,1,0),(2,2,2,0),(3,4,1,0),(4,4,1,0),(5,6,1,0),(5,9,2,0),(6,6,1,0),(6,7,2,0),(7,9,1,0),(8,4,1,0),(9,4,1,0),(10,1,1,0),(10,2,2,0),(11,1,1,0),(11,4,2,0);
DROP TABLE IF EXISTS `tx_news_domain_model_news_ttcontent_mm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_news_domain_model_news_ttcontent_mm` (
  `uid_local` int(11) NOT NULL DEFAULT 0,
  `uid_foreign` int(11) NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `tx_news_domain_model_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tx_news_domain_model_tag` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `crdate` int(11) NOT NULL DEFAULT 0,
  `cruser_id` int(11) NOT NULL DEFAULT 0,
  `deleted` smallint(6) NOT NULL DEFAULT 0,
  `hidden` smallint(6) NOT NULL DEFAULT 0,
  `sys_language_uid` int(11) NOT NULL DEFAULT 0,
  `l10n_parent` int(11) NOT NULL DEFAULT 0,
  `l10n_diffsource` mediumtext DEFAULT NULL,
  `l10n_source` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `l10n_state` text DEFAULT NULL,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `t3ver_oid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_wsid` int(10) unsigned NOT NULL DEFAULT 0,
  `t3ver_state` smallint(6) NOT NULL DEFAULT 0,
  `t3ver_stage` int(11) NOT NULL DEFAULT 0,
  `title` tinytext DEFAULT NULL,
  `slug` varchar(2048) DEFAULT NULL,
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_description` text DEFAULT NULL,
  `seo_headline` varchar(255) NOT NULL DEFAULT '',
  `seo_text` text DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `tx_news_domain_model_tag` VALUES (1,3,1590701033,1590701033,1,0,0,0,0,'',0,'',NULL,0,0,0,0,0,'Tag 1','tag-1','','','',''),(2,3,1590701041,1590701041,1,0,0,0,0,'',0,'',NULL,0,0,0,0,0,'Tag 2','tag-2','','','',''),(3,3,1590701064,1590701048,1,0,0,0,0,'a:9:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"hidden\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:5:\"notes\";N;s:16:\"sys_language_uid\";N;}',0,'',NULL,0,0,0,0,0,'Tag 3','tag-3','','','',''),(4,3,1590701072,1590701072,1,0,0,0,0,'',0,'',NULL,0,0,0,0,0,'Tag 4','tag-4','','','',''),(6,6,1590836046,1590836046,1,0,0,0,0,'',0,'',NULL,0,0,0,0,0,'Tag 10','tag-1-1','','','',''),(7,6,1590836046,1590836046,1,0,0,0,0,'',0,'',NULL,0,0,0,0,0,'Tag 20','tag-2-1','','','',''),(8,6,1590836046,1590836046,1,0,0,0,0,'a:9:{s:5:\"title\";N;s:4:\"slug\";N;s:6:\"hidden\";N;s:9:\"seo_title\";N;s:15:\"seo_description\";N;s:12:\"seo_headline\";N;s:8:\"seo_text\";N;s:5:\"notes\";N;s:16:\"sys_language_uid\";N;}',0,'',NULL,0,0,0,0,0,'Tag 30','tag-3-1','','','',''),(9,6,1590836046,1590836046,1,0,0,0,0,'',0,'',NULL,0,0,0,0,0,'Tag 40','tag-4-1','','','','');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
