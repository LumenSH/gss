-- --------------------------------------------------------
-- Host:                         sinon.shyim.de
-- Server Version:               10.3.11-MariaDB-1:10.3.11+maria~stretch - mariadb.org binary distribution
-- Server Betriebssystem:        debian-linux-gnu
-- HeidiSQL Version:             9.5.0.5249
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Struktur von Tabelle shyimsql12.blocked_tasks
CREATE TABLE IF NOT EXISTS `blocked_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TTL` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_blocked_tasks_users` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.blocked_tasks: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `blocked_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_tasks` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.blog
CREATE TABLE IF NOT EXISTS `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_de` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_en` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_de` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_en` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `publish` int(11) DEFAULT 0,
  `image` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` enum('html','markdown') COLLATE utf8mb4_unicode_ci DEFAULT 'html',
  `cssListing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cssDetail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `blog_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.blog: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `blog` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.blog_comments
CREATE TABLE IF NOT EXISTS `blog_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `blog_id` (`blog_id`),
  CONSTRAINT `blog_comments_blogid` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blog_comments_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.blog_comments: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `blog_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_comments` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.cms
CREATE TABLE IF NOT EXISTS `cms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CMS Tabelle';

-- Exportiere Daten aus Tabelle shyimsql12.cms: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `cms` DISABLE KEYS */;
/*!40000 ALTER TABLE `cms` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.core_menu
CREATE TABLE IF NOT EXISTS `core_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menuActive` tinyint(4) DEFAULT 1,
  `menuID` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `menuClass` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `menuDefaultName` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `menuTyp` tinyint(4) DEFAULT 0,
  `menuSort` tinyint(4) DEFAULT 0,
  `menuLink` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `menuParent` tinyint(4) DEFAULT 0,
  `menuAttrA` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menuAttrLi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.core_menu: ~19 rows (ungefähr)
/*!40000 ALTER TABLE `core_menu` DISABLE KEYS */;
INSERT INTO `core_menu` (`id`, `menuActive`, `menuID`, `menuClass`, `menuDefaultName`, `menuTyp`, `menuSort`, `menuLink`, `menuParent`, `menuAttrA`, `menuAttrLi`) VALUES
	(1, 1, 'dashboard', 'icon ion-stats-bars', 'Dashboard', 2, 0, 'index', 0, NULL, NULL),
	(3, 1, 'gameserver', 'icon ion-ios-cart-outline', 'Shop', 0, 2, 'shop', 0, NULL, NULL),
	(6, 1, 'gp_overview', 'icon ion-pie-graph', 'GP Übersicht', 1, 1, 'gp', 0, NULL, NULL),
	(7, 1, 'server', 'icon ion-cloud', 'Gameserver', 1, 2, 'server', 0, NULL, NULL),
	(8, 1, 'support', 'icon ion-help-buoy', 'Support', 1, 5, 'support', 0, NULL, NULL),
	(9, 1, 'shop', 'icon ion-ios-cart-outline', 'Shop', 1, 4, 'shop', 0, NULL, NULL),
	(10, 1, 'forum', 'icon ion-chatboxes', 'Forum', 2, 3, 'forum', 0, NULL, NULL),
	(11, 1, 'Dashboard', 'icon ion-stats-bars', 'Dashboard', 3, 0, 'index', 0, NULL, NULL),
	(12, 1, 'user', 'icon ion-person', 'Kunden', 3, 1, 'users', 0, NULL, NULL),
	(13, 1, 'blog', 'icon ion-clipboard', 'Blog', 3, 2, 'blog', 0, NULL, NULL),
	(17, 1, 'menu', 'icon ion-navicon-round', 'Menü', 3, 3, 'menu', 0, NULL, NULL),
	(18, 1, 'products', 'icon ion-network', 'Produkte', 3, 5, 'products', 0, NULL, NULL),
	(19, 1, 'blog', 'icon ion-clipboard', 'Blog', 2, 2, 'blog', 0, NULL, NULL),
	(20, 1, 'support', 'icon ion-help-buoy', 'Support', 3, 10, 'support', 0, NULL, NULL),
	(21, 1, 'cms', 'icon ion-gear-b', 'Cms', 3, 10, 'cms', 0, NULL, NULL),
	(22, 1, 'gameserver', 'icon ion-ios-game-controller-b', 'Gameserver', 3, 12, 'gameserver', 0, NULL, NULL),
	(23, 1, 'Forum', 'icon ion-email', 'Forum', 3, 50, 'forum', 0, NULL, NULL),
	(31, 1, 'faq', 'icon ion-help-circled', 'FAQ', 2, 0, 'faq', 0, NULL, NULL),
	(32, 1, 'browse', 'icon ion-cloud', 'Server Browser', 2, 50, 'browse', 0, NULL, NULL);
/*!40000 ALTER TABLE `core_menu` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.core_rewrite
CREATE TABLE IF NOT EXISTS `core_rewrite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `forwardController` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `forwardAction` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `forwardParams` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.core_rewrite: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `core_rewrite` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_rewrite` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.crontab
CREATE TABLE IF NOT EXISTS `crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Name',
  `Action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Die Aktion die ausgeführt werden soll',
  `LastExecute` int(50) NOT NULL DEFAULT 0 COMMENT 'Letzte ausführung',
  `NextExecute` int(50) NOT NULL DEFAULT 0 COMMENT 'Nächste ausführung',
  `Time` int(50) NOT NULL DEFAULT 0 COMMENT 'In welchen Zeitabständen in Sekunden',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.crontab: ~11 rows (ungefähr)
/*!40000 ALTER TABLE `crontab` DISABLE KEYS */;
INSERT INTO `crontab` (`id`, `Name`, `Action`, `LastExecute`, `NextExecute`, `Time`) VALUES
	(3, 'Abgelaufene Gameserver löschen', 'delete_expirated_servers', 1519686076, 1519772461, 86400),
	(4, 'Dashboard Statistiken erstellen', 'build_stats_dashboard', 1519754522, 1519754641, 120),
	(5, 'Blockierte Aufgaben freigeben', 'delete_block_tasks', 1519754221, 1519757821, 3600),
	(6, 'Steam Spiele mit Steam-Servern abgleichen', 'update_steam_games', 1519737782, 1519780981, 43200),
	(7, 'GP Punkte verteilen an Aktive Server', 'server_player', 1519752490, 1519756082, 3600),
	(8, 'Benachrichtigung Server Ablauf', 'server_reminder', 1519689542, 1519775941, 86400),
	(9, 'Leere Server stoppen', 'emptyserverstop', 1519754366, 1519755241, 900),
	(10, 'WebPush', 'webpush', 1519689786, 1519776181, 86400),
	(11, 'Minecraft Versionen Updaten', 'minecraft_version_updater', 1519690023, 1519776421, 86400),
	(12, 'Cache aktualisieren', 'refresh_cache', 1519754523, 1519755422, 900),
	(13, 'Update Server Browser', 'updatebrowser', 1519617012, 1519755423, 900);
/*!40000 ALTER TABLE `crontab` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.forum_board
CREATE TABLE IF NOT EXISTS `forum_board` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boardName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `boardSubName` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boardSub` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boardOrder` int(11) NOT NULL DEFAULT 0,
  `boardType` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.forum_board: ~16 rows (ungefähr)
/*!40000 ALTER TABLE `forum_board` DISABLE KEYS */;
INSERT INTO `forum_board` (`id`, `boardName`, `boardSubName`, `boardSub`, `boardOrder`, `boardType`) VALUES
	(1, 'Gameserver-Sponsor', NULL, NULL, 0, 1),
	(2, 'Allgemein / General', '', '1', 0, 0),
	(3, 'Fehler / Bugs', NULL, '1', 0, 0),
	(4, 'Verbesserungsvorschläge / improvement suggestions', NULL, '1', 0, 0),
	(5, 'Multi Theft Auto : San Andreas', NULL, NULL, 0, 1),
	(6, 'Allgemein / General', NULL, '5', 0, 0),
	(7, 'San Andreas Multiplayer', NULL, NULL, 0, 1),
	(8, 'Allgemein / General', NULL, '7', 0, 0),
	(9, 'Counter Strike', NULL, NULL, 0, 1),
	(12, 'Allgemein zu / General to Counter Strike Global Of', NULL, '9', 0, 0),
	(13, 'Garry´s Mod', NULL, NULL, 0, 1),
	(14, 'Allgemein / General', NULL, '13', 0, 0),
	(15, 'Left 4 Dead 2', NULL, NULL, 0, 0),
	(16, 'Allgemein / General', NULL, '15', 0, 0),
	(17, 'Minecraft', NULL, NULL, 0, 1),
	(18, 'Allgemein / General', NULL, '17', 0, 0);
/*!40000 ALTER TABLE `forum_board` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.forum_entries
CREATE TABLE IF NOT EXISTS `forum_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boardID` int(11) DEFAULT NULL,
  `threadID` int(11) DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `boardID_threadID` (`boardID`,`threadID`),
  KEY `forum_entries_threadid` (`threadID`),
  CONSTRAINT `forum_entries_boardid` FOREIGN KEY (`boardID`) REFERENCES `forum_board` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `forum_entries_threadid` FOREIGN KEY (`threadID`) REFERENCES `forum_thread` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `forum_entries_userid` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.forum_entries: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `forum_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_entries` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.forum_thread
CREATE TABLE IF NOT EXISTS `forum_thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boardID` int(11) DEFAULT NULL,
  `threadName` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `threadDate` int(25) DEFAULT NULL,
  `threadViews` int(11) DEFAULT 0,
  `threadClosed` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `boardID` (`boardID`),
  CONSTRAINT `forum_board_boardid` FOREIGN KEY (`boardID`) REFERENCES `forum_board` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.forum_thread: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `forum_thread` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_thread` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.ftpgroup
CREATE TABLE IF NOT EXISTS `ftpgroup` (
  `groupname` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `gid` smallint(6) NOT NULL DEFAULT 3000,
  `members` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`groupname`),
  KEY `groupname` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.ftpgroup: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `ftpgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `ftpgroup` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.ftpuser
CREATE TABLE IF NOT EXISTS `ftpuser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `userid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `passwd` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `uid` smallint(6) NOT NULL DEFAULT 3000,
  `gid` smallint(6) NOT NULL DEFAULT 3000,
  `homedir` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `shell` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/sbin/nologin',
  `count` int(11) NOT NULL DEFAULT 0,
  `accessed` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.ftpuser: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `ftpuser` DISABLE KEYS */;
/*!40000 ALTER TABLE `ftpuser` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.gameroot
CREATE TABLE IF NOT EXISTS `gameroot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `sshIp` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `sshPort` int(8) NOT NULL DEFAULT 0,
  `sshUser` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `mysqlPassword` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `curRam` int(255) DEFAULT NULL,
  `freeRam` int(11) DEFAULT NULL,
  `maxRam` int(255) DEFAULT NULL,
  `curCpu` float DEFAULT NULL,
  `cpus` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daemonUsername` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daemonPassword` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Exportiere Struktur von Tabelle shyimsql12.gameroot_avg
CREATE TABLE IF NOT EXISTS `gameroot_avg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostID` int(11) NOT NULL,
  `loadavg` float NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hostid_gameroot` (`hostID`),
  CONSTRAINT `hostid_gameroot` FOREIGN KEY (`hostID`) REFERENCES `gameroot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.gameroot_avg: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gameroot_avg` DISABLE KEYS */;
/*!40000 ALTER TABLE `gameroot_avg` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.gameroot_ip
CREATE TABLE IF NOT EXISTS `gameroot_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gamerootID` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gamerootID` (`gamerootID`),
  CONSTRAINT `gamerootID` FOREIGN KEY (`gamerootID`) REFERENCES `gameroot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Struktur von Tabelle shyimsql12.gameserver
CREATE TABLE IF NOT EXISTS `gameserver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gameRootID` int(11) DEFAULT NULL,
  `gameRootIpID` int(11) DEFAULT NULL,
  `productID` int(11) DEFAULT NULL,
  `versionID` int(11) DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `port` int(11) NOT NULL DEFAULT 0,
  `slot` smallint(6) NOT NULL DEFAULT 0,
  `price` smallint(6) NOT NULL DEFAULT 0,
  `duration` int(16) NOT NULL DEFAULT 0,
  `typ` smallint(6) NOT NULL DEFAULT 0,
  `info` smallint(6) NOT NULL DEFAULT 0,
  `createdAt` date DEFAULT NULL,
  `onlineAt` date DEFAULT NULL,
  `bannerName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bannerPlayers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bannerOn` tinyint(4) DEFAULT NULL,
  `properties` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `startParams` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `Root` (`gameRootID`),
  KEY `Root Ip` (`gameRootIpID`),
  KEY `Owner` (`userID`),
  KEY `FK_gameserver_products` (`productID`),
  KEY `FK_gameserver_products_version` (`versionID`),
  CONSTRAINT `FK_gameserver_products` FOREIGN KEY (`productID`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_gameserver_products_version` FOREIGN KEY (`versionID`) REFERENCES `products_version` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_gameserver_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Owner` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Root` FOREIGN KEY (`gameRootID`) REFERENCES `gameroot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Root Ip` FOREIGN KEY (`gameRootIpID`) REFERENCES `gameroot_ip` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.gameserver: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gameserver` DISABLE KEYS */;
/*!40000 ALTER TABLE `gameserver` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.gameserver_browse
CREATE TABLE IF NOT EXISTS `gameserver_browse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serverID` int(11) NOT NULL,
  `online` tinyint(4) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cur_players` int(4) NOT NULL,
  `gamemode` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gametype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gamemap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.gameserver_browse: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gameserver_browse` DISABLE KEYS */;
/*!40000 ALTER TABLE `gameserver_browse` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.gameserver_cloudflare
CREATE TABLE IF NOT EXISTS `gameserver_cloudflare` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gameserverID` int(11) NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subdomain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recordId` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gameserverID_gameserver` (`gameserverID`),
  CONSTRAINT `gameserverID_gameserver` FOREIGN KEY (`gameserverID`) REFERENCES `gameserver` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.gameserver_cloudflare: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gameserver_cloudflare` DISABLE KEYS */;
/*!40000 ALTER TABLE `gameserver_cloudflare` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.gameserver_database
CREATE TABLE IF NOT EXISTS `gameserver_database` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gameserverID` int(11) NOT NULL DEFAULT 0,
  `databaseName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `databaseInternalName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `databaseDescription` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__gameserver` (`gameserverID`),
  CONSTRAINT `gameserverDB_gameserver` FOREIGN KEY (`gameserverID`) REFERENCES `gameserver` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.gameserver_database: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gameserver_database` DISABLE KEYS */;
/*!40000 ALTER TABLE `gameserver_database` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.gp_stats
CREATE TABLE IF NOT EXISTS `gp_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` int(11) NOT NULL,
  `status` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_gp_stats_users` (`userID`),
  CONSTRAINT `FK_gp_stats_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.gp_stats: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gp_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `gp_stats` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.likes
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT 0,
  `table` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `table_id` int(11) DEFAULT 0,
  `liked_user` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `likes_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.likes: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.migration_versions
CREATE TABLE IF NOT EXISTS `migration_versions` (
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.migration_versions: ~4 rows (ungefähr)
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` (`version`) VALUES
	('20170829165920'),
	('20170829170205'),
	('20171007131154'),
	('20171228181936');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT 1,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_de` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internalName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executable` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `steamID` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consoleCommands_de` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consoleCommands_en` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.products: ~9 rows (ungefähr)
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `active`, `name`, `description_de`, `description_en`, `img`, `internalName`, `executable`, `steamID`, `consoleCommands_de`, `consoleCommands_en`, `banner`) VALUES
	(1, 1, 'Multi Theft Auto: San Andreas', 'Eine Multiplayermodifikation für das Spiel GTA: San Andreas von Rockstar Games.', 'A multiplayer mod for GTA: San Andreas by Rockstar Games.', '598f77c0b3d7b.jpg', 'mta', 'mta-server64', NULL, '<p><strong>Befehle</strong></p>\n\n<ul>\n	<li>start [resourcen name]</li>\n    <li>stop [resource_name]</li>\n    <li>refresh</li>\n    <li>help</li>\n</ul>', '<p><strong>Commands</strong></p>\n\n<ul>\n	<li>start [resourcen name]</li>\n    <li>stop [resource_name]</li>\n    <li>refresh</li>\n    <li>help</li>\n</ul>', 1),
	(2, 0, 'Counter-Strike:Global Offensive', 'Counter-Strike:Global Offensive, der momentan neuste Teil der Counter-Strike Reihe, hat mit ingame Skins und guter Grafik den Ego shooter Markt für sich erobert.', 'Counter-Strike: Global Offensive is the latest game of the Counter-Strike series and seized the ego shooter marked with ingame skins and great graphics.', '598f77c9c23da.jpg', 'csgo', 'srcds_linux', '740', '<ul>\n  <li>changelevel</li>\n  <li>map</li>\n  <li>sv_password</li>\n  <li>rcon_password</li>\n</ul>\n', '<ul>\n  <li>changelevel</li>\n  <li>map</li>\n  <li>sv_password</li>\n  <li>rcon_password</li>\n</ul>\n', 1),
	(5, 1, 'Garry\'s Mod', 'Garry\'s Mod ist ein Custom Game welches auf der Source Engine basiert. Es wurde 2006 veröffentlicht und war erst als Modifikation für Source Spiele gedacht.', 'Garry\'s Mod is a custom game which is based on the Source Engine. It was published in 2006 and, at first, was supposed to be a modification for Source games.', '598f77cf0ac21.jpg', 'gmod', 'srcds_linux', '4020', '<p><strong>​Coming soon</strong></p>\n', '<p><strong>​Coming soon</strong></p>\n', 1),
	(6, 1, 'Minecraft', 'Minecraft ist ein Open World Survival Spiel welches auf 16x16 Block Grafik basiert. Es wurde von Mojang entwickelt und bereits 20 Millionen mal verkauft.', 'Minecraft is an open world survival game which is based on 16x16 block graphics. It was developed by Mojang and has already been sold 20 million times.', '598f77d4c89d8.jpg', 'mc', '/usr/bin/java', NULL, NULL, NULL, 1),
	(8, 1, 'San Andreas Multiplayer', 'Eine Multiplayermodifikation für das Spiel GTA: San Andreas von Rockstar Games.', 'A multiplayer modification for the game GTA: San Andreas by Rockstar Games.', '598f7831375fd.jpg', 'samp', 'samp03svr', NULL, NULL, NULL, 1),
	(14, 1, 'Grand Theft Multiplayer', 'Grand Theft Multiplayer is a free alternative multiplayer modification for Grand Theft Auto V, giving you the possibility to play with hundreds of players on dedicated servers with custom gamemodes built from the ground up and modified game experiences.', 'Grand Theft Multiplayer is a free alternative multiplayer modification for Grand Theft Auto V, giving you the possibility to play with hundreds of players on dedicated servers with custom gamemodes built from the ground up and modified game experiences.', '598f77e882027.jpg', 'gta5mp', '/usr/bin/mono', NULL, NULL, NULL, 1),
	(15, 0, 'Factorio', 'Factorio is a game in which you build and maintain factories.', 'Factorio is a game in which you build and maintain factories.', '598c59708a088.png', 'factorio', 'bin/x64/factorio', NULL, NULL, NULL, 0),
	(16, 1, 'Terraria', 'Terraria ist ein Open-World-Spiel, das 2011 über Steam von vier unabhängigen Entwicklern unter dem Namen „Re-Logic“ veröffentlicht wurde. Es ist in einer 2D-Grafik gehalten, hat jedoch teilweise eine dritte Dimension integriert.', 'Terraria is a 2D action-adventure sandbox video game developed by Re-Logic. The game was initially released for Microsoft Windows in May 2011, and has since been released for various other platforms and devices. Gameplay of Terraria features exploration, crafting, building, and combat with a variety of creatures in a procedurally generated 2D world.', '598f77f01fb23.jpg', 'terraria', '/usr/bin/mono', NULL, NULL, NULL, 1),
	(17, 1, 'FiveM', 'FiveM is a modification for Grand Theft Auto V  enabling you to play on dedicated servers with  custom, modified experiences.', 'FiveM is a modification for Grand Theft Auto V  enabling you to play on dedicated servers with  custom, modified experiences.', '59a9a2b7b10eb.jpg', 'fivem', '/bin/bash', NULL, NULL, NULL, 1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.products_sub
CREATE TABLE IF NOT EXISTS `products_sub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT 1,
  `productID` int(11) DEFAULT NULL,
  `slots` int(11) DEFAULT NULL,
  `ram` int(11) DEFAULT NULL,
  `gp` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product` (`productID`),
  CONSTRAINT `product` FOREIGN KEY (`productID`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.products_sub: ~20 rows (ungefähr)
/*!40000 ALTER TABLE `products_sub` DISABLE KEYS */;
INSERT INTO `products_sub` (`id`, `active`, `productID`, `slots`, `ram`, `gp`, `type`) VALUES
	(2, 1, 2, 10, 1000, 0, 1),
	(5, 1, 5, 5, 1000, 0, 1),
	(6, 0, 6, 2, 750, 200, 0),
	(7, 0, 6, 5, 1500, 500, 0),
	(8, 1, 6, 10, 1500, 0, 1),
	(10, 1, 8, 32, 320, 1000, 0),
	(11, 1, 8, 64, 640, 2000, 0),
	(12, 1, 8, 128, 1280, 4000, 0),
	(13, 1, 8, 10, 100, 0, 1),
	(20, 1, 1, 32, 320, 1000, 0),
	(21, 1, 1, 64, 640, 2000, 0),
	(23, 1, 1, 32, 0, 0, 1),
	(24, 1, 14, 32, 0, 1000, 0),
	(25, 1, 15, 5, 0, 1000, 0),
	(26, 1, 16, 10, 0, 1000, 1),
	(27, 1, 14, 64, 1000, 2000, 0),
	(28, 1, 14, 32, 0, 0, 1),
	(29, 1, 1, 6, 0, 150, 0),
	(30, 1, 17, 32, 0, 1000, 0),
	(31, 1, 8, 16, 0, 500, 0);
/*!40000 ALTER TABLE `products_sub` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.products_version
CREATE TABLE IF NOT EXISTS `products_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productID` int(11) DEFAULT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products` (`productID`),
  CONSTRAINT `products` FOREIGN KEY (`productID`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93054 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Exportiere Struktur von Tabelle shyimsql12.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`text`),
  UNIQUE KEY `text` (`text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.tags: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.tickets
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `gameserverID` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder` int(11) DEFAULT 0,
  `typ` int(11) DEFAULT 0,
  `created_at` int(24) NOT NULL,
  `lastchange_at` int(24) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `gameserverID` (`gameserverID`),
  CONSTRAINT `tickets_gameserverid` FOREIGN KEY (`gameserverID`) REFERENCES `gameserver` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tickets_userid` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.tickets: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.tickets_answers
CREATE TABLE IF NOT EXISTS `tickets_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) NOT NULL,
  `support` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ticketID_userID` (`ticketID`,`userID`),
  KEY `tickets_answers_userid` (`userID`),
  CONSTRAINT `tickets_answers_ticketid` FOREIGN KEY (`ticketID`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tickets_answers_userid` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.tickets_answers: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `tickets_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets_answers` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.translation
CREATE TABLE IF NOT EXISTS `translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `de` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `en` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=547 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.translation: ~546 rows (ungefähr)
/*!40000 ALTER TABLE `translation` DISABLE KEYS */;
INSERT INTO `translation` (`id`, `name`, `namespace`, `de`, `en`) VALUES
	(1, 'Rank_5', 'User', 'Anfänger', 'Beginner'),
	(2, 'Rank_10', 'User', 'Grünschnabel', 'Greenhorn'),
	(3, 'Rank_20', 'User', 'Lehrling', 'Trainee'),
	(4, 'Register', 'User', 'Registrieren', 'Register'),
	(5, 'Login', 'User', 'Login', 'Login'),
	(6, 'UserProfileFrom', 'User', 'Benutzerprofil von ', 'User profile from '),
	(7, 'Posts', 'User', 'Beiträge', 'Posts'),
	(8, 'Likes', 'User', 'Erhaltene Likes', 'Likes you\'ve got'),
	(9, 'ProfileRequests', 'User', 'Profil-Aufrufe', 'Profile clicks'),
	(10, 'CurrentRank', 'User', 'Aktueller Rang:', 'Current rank:'),
	(11, 'RegisterDate', 'User', 'Mitglied seit dem', 'Member since'),
	(12, 'ProfilDescription', 'User', 'Profilbeschreibung', 'Profile description'),
	(13, 'LoginSuccess', 'User', 'Du wurdest erfolgreich eingeloggt', 'You were successfully logged in'),
	(14, 'Rank_30', 'User', 'Fortgeschrittener', 'Advanced'),
	(15, 'Rank_40', 'User', 'Anwärter', 'Contender'),
	(16, 'LoginError', 'User', 'Deine Email-Adresse oder Dein Passwort ist falsch', 'Your e-mail address or your password is wrong'),
	(17, 'Username', 'User', 'Username', 'Username'),
	(18, 'Email', 'User', 'E-Mail', 'E-Mail'),
	(19, 'Password', 'User', 'Passwort', 'Password'),
	(20, 'Password2', 'User', 'Passwort wiederholen', 'Retype password'),
	(21, 'RegisterNow', 'User', 'Registrieren', 'Register'),
	(22, 'Email/Register/Subject', 'User', 'Ihre Registrierung bei Gameserver-Sponsor', 'Your registration at Gameserver-Sponsor'),
	(23, 'SuccessRegistered', 'User', 'Du hast Dich erfolgreich registriert. Bitte prüfe Deine Email-Adresse', 'Your registration was successfull. Please check your E-Mails'),
	(24, 'LoginNotActivated', 'User', 'Bitte verifiziere zuerst Deine Email-Adresse', 'Please verify your E-Mail first'),
	(25, 'Contact', 'User', 'Kontaktmöglichkeiten', 'Contact us'),
	(26, 'General', 'User', 'Allgemein', 'General'),
	(27, 'Security', 'User', 'Sicherheit', 'Security'),
	(28, 'Profil', 'User', 'Profil', 'Profile'),
	(29, 'UsernameOnlyChangeableFromSupport', 'User', 'Deinen Usernamen oder Email-Adresse können nur durch den Support geändert werden', 'Your username or Email Adress can only be changed by the support.'),
	(30, 'OldPassword', 'User', 'Altes Passwort', 'Old password'),
	(31, 'NewPassword', 'User', 'Neues Passwort', 'New password'),
	(32, 'NewPassword2', 'User', 'Neues Passwort wiederholen', 'Retype new password'),
	(33, 'Avatar', 'User', 'Avatar', 'Avatar'),
	(34, 'AvatarDelete', 'User', 'Avatar löschen', 'Delete Avatar'),
	(35, 'Skype', 'User', 'Skype', 'Skype'),
	(36, 'Signatur', 'User', 'Signatur', 'Signature'),
	(37, 'Save', 'User', 'Speichern', 'Save'),
	(38, 'AvatarCut', 'User', 'Avatar zuschneiden', 'Cut Avatar'),
	(39, 'Close', 'User', 'Schliessen', 'Close'),
	(40, 'ProfilUpdated', 'User', 'Dein Profil wurde erfolgreich aktualisiert', 'Your profile has been updated successfully'),
	(41, 'PasswordForgot', 'User', 'Passwort Vergessen', 'Lost password'),
	(42, 'Request', 'User', 'Anfordern', 'Request'),
	(43, 'ForgotPasswordMail', 'User', 'Password Vergessen', 'Lost password'),
	(44, 'MailSend', 'User', 'Es wurde eine E-Mail zum zurücksetzen, deines Kontos versendet', 'An e-mail with instructions has been sent to the account´s e-mail address'),
	(45, 'NewPasswordSaved', 'User', 'Dein Passwort wurde erfolgreich gespeichert. Du kannst Dich nun damit einloggen', 'Your password has been saved successfully.'),
	(46, 'Rank_50', 'User', 'Mitglied', 'Member'),
	(47, 'RegisterCompletedHalf', 'User', 'Die Registrierung ist fast abgeschlossen', 'The registration is near the completion'),
	(48, 'EndRegister', 'User', 'Registrierung abschliessen', 'Finish registration'),
	(49, 'UsernameIsAlreadyTaken', 'User', 'Der Username ist bereits in Benutzung', 'The Username is already in use'),
	(50, 'CouldNotFoundUser', 'User', 'Diese E-Mail Adresse ist kein User zugewiesen', 'The e-mail address isn\'t linked to an user'),
	(51, 'AlreadyLoggedin', 'User', 'Du bist bereits eingeloggt', 'You\'re already logged in'),
	(52, 'AlreadyActivated', 'User', 'Ein Konto ist bereits unter dieser Email registriert!', 'An Account is already linked to this E-mail!'),
	(53, 'CouldntFoundCode', 'User', 'Es konnte kein Benutzerkonto, zu Deinem Code zugeordnet werden', 'Your code doesn\'t refer to an Account '),
	(54, 'AlreadySendTodayForgotPassword', 'User', 'Du hast bereits heute eine Passwort vergessen Anfrage versendet', 'You already sent a request'),
	(55, 'Rank_0', 'User', 'Neuling', 'Newbie'),
	(56, 'DeleteAvatar', 'User', 'Dein Avatar wurde erfolgreich gelöscht', 'Your avatar has been deleted successfully'),
	(57, 'TrashMail', 'User', 'Diese Email-Adresse kann leider nicht verwendet werden.', 'This e-mail address can\'t be used.'),
	(58, 'Rank_75', 'User', 'Routinier', 'Routineer'),
	(59, 'OldPasswordWrong', 'User', 'Dein altes Passwort passt nicht', 'Your old password doesn\'t match'),
	(60, 'PasswordEqual', 'User', 'Die Passwörter müssen übereinstimmen', 'The passwords must match'),
	(61, 'Rank_100', 'User', 'König der Server', 'King of servers'),
	(62, 'Rank_125', 'User', 'Auskenner', 'Knower'),
	(63, 'Rank_250', 'User', 'Experte', 'Expert'),
	(64, 'Rank_375', 'User', 'Profi', 'Professional'),
	(65, 'Rank_500', 'User', 'Idol', 'Idol'),
	(66, 'Rank_750', 'User', 'Mogul', 'Mogul'),
	(67, 'Rank_1000', 'User', 'Champ', 'Champ'),
	(68, 'Rank_1250', 'User', 'Meister', 'Master'),
	(69, 'Rank_1500', 'User', 'Großmeister', 'Grandmaster'),
	(70, 'Rank_2000', 'User', 'Veteran', 'Veteran'),
	(71, 'Rank_4000', 'User', 'Halbgott', 'Half-god'),
	(72, 'Rank_8000', 'User', 'Legende', 'Legend'),
	(73, 'Rank_16000', 'User', 'Ehrenmitglied', 'Honourable Member'),
	(74, 'UsernameTaken', 'User', 'Dein Username ist bereits vergeben', 'Your username is already taken'),
	(75, 'NeuPasswordsAreNotMatching', 'User', 'Deine neuen Passwörter passen nicht', 'Passwords dont equal'),
	(76, '2StepVerification', 'User', '2 Schritte Authentifizierung', 'Two factor authentication'),
	(77, '2StepAppInfo', 'User', 'Du brauchst für die Zwei Schritte Verification die Google Authentificator App.', 'You need the google authentificator app for the Two factor authenticator'),
	(78, '2StepAuthActivate', 'User', 'Google Authentificator für deinen Account aktivieren?', 'Activate google authentificator for your account?'),
	(79, 'GoogleAuthentificatorCode', 'User', 'Google Authentificator Code', 'Your authentificator code'),
	(80, 'Problems Auth', 'User', 'Probleme beim Authentifizieren? Erstelle ein Support Ticket', 'Problems with your Code? Contact Support'),
	(81, 'DeleteAvatarError', 'User', 'Avatar konnte nicht gesetzt werden', 'Something went wrong while changing your avatar'),
	(82, 'PhonenumberFormat', 'User', 'Telefonnummer muss folgendes Format haben: +49 15731111', 'Your phone number must match the following format: +49 15731111'),
	(83, 'Activate', 'User', 'Aktivieren', 'Activate'),
	(84, 'OtherNumber', 'User', 'andere Telefonnummer versuchen', 'another phone number'),
	(85, 'SMSAttempsReached', 'User', 'You can only do 3 SMS attemps per day.', '>You can only send up to 3 verification SMS per day'),
	(86, 'Timezone', 'User', 'Timezone', 'Timezone'),
	(87, 'RemindServerDeleteTitle', 'WebPush', 'Ablauf des Gameservers', 'The expiration day of your gameserver'),
	(88, 'UsernameToShort', 'OAuth', 'Dein Username ist zu kurz', 'Your username is too short'),
	(89, 'Activeserver', 'Shop', 'Aktive Server', 'Active servers'),
	(90, 'ShopHead', 'Shop', 'Shop', 'Shop'),
	(91, 'ActiveGameserver', 'Shop', 'Aktive Gameserver', 'Active Gameservers'),
	(92, 'PassiveGameserver', 'Shop', 'Passive Gameserver', 'Passive Gameservers'),
	(93, 'AccountUpgrade', 'Shop', 'Account Upgrades', 'Account Upgrades'),
	(94, '24/7OnlineInfo', 'Shop', 'Unsere aktiven Server sind 24/7 Online. Du kannst sie also für Deine Projekte benutzen. Aber beachte, dass Du auch genügend aktive Spieler brauchst um Ihn bezahlen zu können.', 'Our active servers are 24/7 online. You can use them for your projects. But make sure you have enough players to pay it.'),
	(95, 'NowFrom', 'Shop', 'Jetzt ab', 'Order now with'),
	(96, 'Order', 'Shop', 'GP Bestellen', 'GP'),
	(97, 'Passiveserver', 'Shop', 'Passive Server', 'Passive servers'),
	(98, 'PassiveServerInfo', 'Shop', 'Unsere passiven Server schalten sich automatisch ab, wenn keine Spieler darauf spielen.', 'Our passive server get shut down automatically, if nobody uses it'),
	(99, 'now', 'Shop', 'Jetzt', 'Create'),
	(100, 'create', 'Shop', 'erstellen', 'server now'),
	(101, 'Upgrades', 'Shop', 'Upgrades', 'Upgrades'),
	(102, 'FTPAccount', 'Shop', 'FTP Zugänge', 'FTP accounts'),
	(103, 'FTPAccountInfo', 'Shop', 'Hole dir einen weiteren FTP Zugang für nur', 'Buy one more FTP account'),
	(104, 'orderNow', 'Shop', 'Jetzt Bestellen', 'Order now'),
	(105, 'MySQLAccount', 'Shop', 'MySQL Datenbanken', 'MySQL databases'),
	(106, 'MySQLAccountInfo', 'Shop', 'Hole Dir einen weiteren MySql Zugang für nur', 'Buy one more MySql account'),
	(107, 'GuestAccounts', 'Shop', 'Gast Zugänge', 'Guest accounts'),
	(108, 'GuestAccountsInfo', 'Shop', 'Hole Dir einen weiteren Gast Zugang für nur', 'Buy one more guest account'),
	(109, 'ExtraPassiveServer', 'Shop', 'Weitere Server', 'More servers'),
	(110, 'ExtraPassiveServerInfo', 'Shop', 'Hole Dir einen weitere Server für nur', 'Buy one more passive server'),
	(111, 'ExtraPassiveServerSlots', 'Shop', 'Weitere Server Slots', 'More server slots'),
	(112, 'ExtraPassiveServerSlotsInfo', 'Shop', 'Erhöhe jetzt Deine Maximale Slot anzahl schon ab', 'Buy one more slot for your passive servers'),
	(113, 'HeadShop', 'Shop', 'Shop', 'Shop'),
	(114, 'Slot', 'Shop', 'Slot', 'Slot'),
	(115, 'DatabaseAccounts', 'Shop', 'Datenbank Accounts', 'Database Accounts'),
	(116, 'FTPAccounts', 'Shop', 'FTP Accounts', 'FTP Accounts'),
	(117, 'PassiveOnline', 'Shop', 'Schaltet sich ab sobald keiner mehr Online ist', 'Turn offline if no player is online.'),
	(118, 'Createnow', 'Shop', 'Jetzt erstellen', 'Create now'),
	(119, 'ServerCreated', 'Shop', 'Gameserver wurde erfolgreich erstellt', 'Gameserver was created successfully'),
	(120, 'Slots', 'Shop', 'Slots', 'Slots'),
	(121, '24/7Online', 'Shop', '24/7 Online', '24/7 Online'),
	(122, 'Cost', 'Shop', 'Kosten:', 'Costs:'),
	(123, 'perMonth', 'Shop', 'im Monat', 'in a month'),
	(124, 'Buy now', 'Shop', 'Dieses Packet jetzt bestellen', 'Buy this package now'),
	(125, 'UpgradeBuyed', 'Shop', 'Upgrade gekauft', 'Upgrade bought'),
	(126, 'Costs', 'Shop', 'Kosten:', 'Costs:'),
	(127, 'Buynow', 'Shop', 'Jetzt kaufen', 'Buy now'),
	(128, 'BuyServerGPStatus', 'Shop', 'Server Kauf', 'Buy server'),
	(129, 'LoginToContinue', 'Shop', 'Bitte logge Dich ein um fortzufahren', 'Please log in to continue'),
	(130, 'AllServerInUse', 'Shop', 'Es sind zurzeit leider alle Server belegt', 'Sadly, at the moment all servers are used'),
	(131, 'TooMuchPassiveServers', 'Shop', 'Du hast zu viele Passive Server, bitte upgrade Deine Maximale Server Anzahl', 'You got to many passive servers, please upgrade the maximum server count'),
	(132, 'NotEnoughtGameserverPoints', 'Shop', 'Du hast nicht genügend Gameserver-Punkte', 'You don\'t have enough GPs'),
	(133, 'UpgradeGP', 'Shop', 'Deine GP Punkte reichen nicht für dieses Upgrade aus', 'You don\'t have enough GPs for buying this Upgrade'),
	(134, 'ConfirmFTP', 'Shop', 'Möchtest Du wirklich einen weiteren FTP Zugang kaufen?', 'Do you really want to buy another FTP Access?'),
	(135, 'ConfirmDB', 'Shop', 'Möchtest Du wirklich einen weiteren MySQL Zugang kaufen?', 'Do you really want to buy another MySQL Access?'),
	(136, 'ConfirmGuest', 'Shop', 'Möchtest Du wirklich einen weiteren Gastzugang Zugang kaufen?', 'Do you really want to buy another Guest Access?'),
	(137, 'ConfirmPassiveServer', 'Shop', 'Möchtest Du wirklich einen weiteren Passiven Server kaufen?', 'Do you really want to buy another passive server?'),
	(138, 'ConfirmPassiveServerSlot', 'Shop', 'Möchtest Du wirklich einen weiteren Passiven Server Slot kaufen?', 'Do you really want to buy another passive server slot?'),
	(139, 'AccountActivateInfo', 'Shop', 'You can only order active servers, if you have your account activated with sms', 'You can only order active servers, if you have your account activated with sms'),
	(140, 'InteractionActivateAccount', 'Shop', 'Go to account activation with sms', 'Go to account activation with sms'),
	(141, 'OpenTickets', 'Support', 'Offene Tickets', 'Open tickets'),
	(142, 'OpenedTickets', 'Support', 'Offene Tickets', 'Open tickets'),
	(143, 'ClosedTickets', 'Support', 'Geschlossene Tickets', 'Closed tickets'),
	(144, 'NewTicket', 'Support', 'Neues Ticket', 'New ticket'),
	(145, 'Question', 'Support', 'Frage', 'Question'),
	(146, 'CreatedAt', 'Support', 'Erstellt am', 'Created at'),
	(147, 'LastAnswerAt', 'Support', 'Letzte Antwort am', 'Last answer at'),
	(148, 'Title', 'Support', 'Titel', 'Title'),
	(149, 'Typ', 'Support', 'Typ', 'Type'),
	(150, 'Gameserver', 'Support', 'Gameserver', 'Gameserver'),
	(151, 'Create', 'Support', 'Erstellen', 'Create'),
	(152, 'TicketCreated', 'Support', 'Dein Ticket wurde erfolgreich angelegt', 'Your ticket was successfully created'),
	(153, 'Answer', 'Support', 'Antworten', 'Answer'),
	(154, 'Look', 'Support', 'Anschauen', 'View'),
	(155, 'CloseTicket', 'Support', 'Ticket schliessen', 'Close ticket'),
	(156, 'TicketClose', 'Support', 'Ticket wurde geschlossen', 'Ticket has been closed'),
	(157, 'TicketClosed', 'Support', 'Ticket wurde geschlossen', 'Ticket closed'),
	(158, 'SupportMessageSend', 'Support', 'Die Nachricht wurde erfolgreich versendet', 'The message has been sent successfully'),
	(159, 'LanguageHint', 'Support', 'Please create support tickets in english or german.', 'Please create support tickets in English or German.'),
	(160, 'Search', 'Navigation', 'Suche', 'Search'),
	(161, 'NotificationsCount', 'Navigation', 'Du hast zurzeit %noti% Benachrichtigungen', 'You currently have %noti% Notificaions'),
	(162, 'MarkAsRead', 'Navigation', 'Alle als gelesen markieren', 'Mark all as read'),
	(163, 'Languages', 'Navigation', 'Sprachen', 'Languages'),
	(164, 'Language_de', 'Navigation', 'Deutsch', 'German'),
	(165, 'Language_en', 'Navigation', 'Englisch', 'English'),
	(166, 'Language_pt', 'Navigation', 'Portugiesisch', 'Portuguese'),
	(167, 'MyProfile', 'Navigation', 'Mein Profil', 'My Profile'),
	(168, 'AccountSettings', 'Navigation', 'Account Einstellungen', 'Account settings'),
	(169, 'Logout', 'Navigation', 'Ausloggen', 'Log out'),
	(170, 'NoNotifications', 'Navigation', 'Es konnten keine Benachrichtigungen gefunden werden', 'There are no notifications'),
	(171, 'MarkAllAsRead', 'Navigation', 'Alle als gelesen makieren', 'Mark everything as read'),
	(172, 'OnPostAnswer', 'Notification', '<a href=\'%userLink%\'>%name%</a> hat auf dein Thema %thread% geantwortet', '<a href=\'%userLink%\'>%name%</a> answered on your Thread %thread%'),
	(173, 'HighlightBlogComment', 'Notification', '<a href=\'%userLink%\'>%name%</a> hat dich im Blog Beitrag <a href=\'%url%\'>%blog%</a> erwähnt', '<a href=\'%userLink%\'>%name%</a> mentioned you in a blog post <a href=\'%url%\'>%blog%</a>'),
	(174, 'TicketHeader', 'Notification', 'Neue Antwort auf dein Ticket', 'New answer for your Ticket'),
	(175, 'TicketText', 'Notification', 'Auf Dein Ticket bei Gameserver-Sponsor mit den Namen %name% wurde geantwortet', 'Your Ticket on gameserver-sponsor with name %name% has been answered'),
	(176, 'VerificationSuccess', 'Login', 'Dein Account wurde erfolgreich verifiziert, Du kannst Dich nun einloggen', 'Your account is now verified.'),
	(177, 'VerificationError', 'Login', 'Dein Aktivierungscode konnte nicht gefunden werden. Bitte fordere einen neuen an', 'Your code couldn\'t be found. Please get a new one.'),
	(178, 'Dashboard', 'Menu_Backend', 'Dashboard', 'Dashboard'),
	(179, 'user', 'Menu_Backend', 'Kunden', 'Kunden'),
	(180, 'Nachrichten', 'Menu_Backend', 'Blog', 'Blog'),
	(181, 'menu', 'Menu_Backend', 'Menü', 'Menü'),
	(182, 'products', 'Menu_Backend', 'Produkte', 'Produkte'),
	(183, 'support', 'Menu_Backend', 'Support', 'Support'),
	(184, 'cms', 'Menu_Backend', 'Cms', 'Cms'),
	(185, 'translate', 'Menu_Backend', 'Übersetzen', 'Translate'),
	(186, 'gameserver', 'Menu_Backend', 'Gameserver', 'Gameserver'),
	(187, 'Forum', 'Menu_Backend', 'Forum', 'Forum'),
	(188, 'server', 'Menu_Backend', 'Gameserver', 'Gameserver'),
	(189, 'InternalError', '500', 'Es ist ein Interner Fehler aufgetreten, aber unsere Affen werden sich schnell darum kümmern! Versprochen!', 'Internal Error! Our monkeys are going to get it repaired fast! Promised!'),
	(190, 'Dashboard', 'pageTitle', 'Dashboard', 'Dashboard'),
	(191, 'GP Übersicht', 'pageTitle', 'GP Übersicht', 'GP Overview'),
	(192, 'Gameserver Verwaltung', 'pageTitle', 'Gameserver Verwaltung', 'Gameserver administration'),
	(193, 'Blog', 'pageTitle', 'Blog', 'Blog'),
	(194, 'Forum', 'pageTitle', 'Forum', 'Forum'),
	(195, 'Server', 'pageTitle', 'Server', 'Server'),
	(196, 'Support', 'pageTitle', 'Support', 'Support'),
	(197, 'Verbesserungsvorschläge', 'pageTitle', 'Verbesserungsvorschläge', 'Suggestion'),
	(198, '404', 'pageTitle', '404', '404'),
	(199, 'Server Kaufen', 'pageTitle', 'Server Kaufen', 'Buy server'),
	(200, 'Beitrag bearbeiten', 'pageTitle', 'Beitrag bearbeiten', 'Edit post'),
	(201, 'Neues Thema erstellen', 'pageTitle', 'Neues Thema erstellen', 'Create Topic'),
	(202, 'Registrieren', 'pageTitle', 'Registrieren', 'Sign up'),
	(203, 'Mein Profil', 'pageTitle', 'Mein Profil', 'My profile'),
	(204, 'Internal Error', 'pageTitle', 'Internal Error', 'Internal Error'),
	(205, 'Ticket', 'pageTitle', 'Ticket', 'Ticket'),
	(206, 'Neuen Verbesserungsvorschlag einreichen', 'pageTitle', 'Neuen Verbesserungsvorschlag einreichen', 'Send a new suggestion'),
	(207, 'Server Variante wählen', 'pageTitle', 'Server Variante wählen', 'Choose server variant'),
	(208, 'Passwort vergessen?', 'pageTitle', 'Passwort vergessen?', 'Lost password?'),
	(209, 'Registrierung abschliessen', 'pageTitle', 'Registrierung abschliessen', 'Finish sign up'),
	(210, 'Wiki', 'pageTitle', 'Wiki', 'Wiki'),
	(211, 'Verification', 'pageTitle', 'Verification', 'Verification'),
	(212, 'SubdomainReady', 'Cloudflare', 'Die Subdomain ist nun in kürze verfügbar', 'Subdomain will be avaible in the next minutes'),
	(213, 'SubdomainError', 'Cloudflare', 'Es ist ein Fehler aufgetreten. Bitte versuche einen andere Subdomain', 'There is a problem with your subdomain name, please try another'),
	(214, 'SubdomainTaken', 'Cloudflare', 'Diese Subdomain ist bereits belegt', 'This name is already taken'),
	(215, 'ChangeDomain', 'Cloudflare', 'Domain setzen', 'Set custom domain'),
	(216, '404PageTitle', '404', '404 - Seite nicht gefunden', '404 - page not found'),
	(217, 'lost', '404', 'Oops! Hier bist du falsch.', 'Oops! You are at the wrong place.'),
	(218, 'pagenotfound', '404', 'Wir konnten die Seite, die Du aufrufen möchtest nicht finden.', 'We couldn\'t find the page you\'re looking for\''),
	(219, 'returnhome', '404', 'Zurück zur Startseite', 'Back to home.'),
	(220, 'Impress', 'Layout', 'Impressum', 'Imprint'),
	(221, 'Privacy', 'Layout', 'Datenschutzbestimmungen', 'Terms of use'),
	(222, 'WelcomeLogin', 'Layout', 'Willkommen. Bitte logge Dich ein', 'Welcome to Gameserver-Sponsor. Please Log in'),
	(223, 'ConnectToFacebook', 'Layout', 'Verbinde Dich mit Facebook', 'Connect using Facebook'),
	(224, 'UsernameOrEmail', 'Layout', 'Username oder Email-Adresse', 'Username or email-adress'),
	(225, 'Password', 'Layout', 'Passwort', 'Password'),
	(226, 'StayLoggedin', 'Layout', 'Eingeloggt bleiben', 'Stay logged in'),
	(227, 'ForgotPassword', 'Layout', 'Passwort Vergessen?', 'Forgot Password?'),
	(228, 'Login', 'Layout', 'Einloggen', 'Log in'),
	(229, 'RegisterText', 'Layout', 'Noch kein Mitglied? Registriere Dich jetzt!', 'No account yet? Get one!'),
	(230, 'NewTicketOffline', 'Layout', 'Neue Supportanfrage', 'Create new ticket'),
	(231, 'Name', 'Layout', 'Name', 'Name'),
	(232, 'Email', 'Layout', 'E-Mail', 'E-Mail'),
	(233, 'Message', 'Layout', 'Nachricht', 'Message'),
	(234, 'Send', 'Layout', 'Absenden', 'Send'),
	(235, 'ConnectToGoogle', 'Layout', 'Verbinde Dich mit Google', 'Connect using Google'),
	(236, 'Index', 'Search', 'Suchergebnisse', 'Search hits'),
	(237, 'Filter', 'Search', 'Filter', 'Filter'),
	(238, 'ProfileVisits', 'Search', 'Profilaufrufe:', 'Profile clicks:'),
	(239, 'Posts', 'Search', 'Beiträge:', 'Posts:'),
	(240, 'MinLength3Chars', 'Search', 'Deine Suche muss mindestens 3 Zeichen lang sein.', 'Your search request should contain at least 3 chars.'),
	(241, 'RegisterDate', 'Search', 'Registriert seit:', 'User since:'),
	(242, 'SubtitleForum', 'Search', 'von <a href=%userLink%>%user%</a>, vom %date%', 'from <a href=%userLink%>%user%</a>, at %date%'),
	(243, 'forumhead', 'Forum', 'Forum', 'Forum'),
	(244, 'topics', 'Forum', 'Themen', 'Discussions'),
	(245, 'posts', 'Forum', 'Beiträge:', 'Posts'),
	(246, 'Last5Posts', 'Forum', 'Die letzten 5 Beiträge', 'Last 5 posts'),
	(247, 'LatestThread', 'Forum', 'Aktuelle Themen', 'Newest discussions'),
	(248, 'Answer', 'Forum', 'Antworten', 'Answer'),
	(249, 'Requests', 'Forum', 'Zugriffe', 'Views'),
	(250, 'LastPostHead', 'Forum', 'Letzer Beitrag', 'Last post'),
	(251, 'Forum', 'Forum', 'Forum', 'Forum'),
	(252, 'Beitraege', 'Forum', 'Beiträge:', 'Posts:'),
	(253, 'Rank', 'Forum', 'Rank:', 'Rank:'),
	(254, 'CreateNewAnswer', 'Forum', 'Neue Antwort erstellen', 'Create new answer'),
	(255, 'ThreadClosed', 'Forum', 'Dieses Thema ist geschlossen. Weitere Antworten sind nicht mehr möglich', 'This thread is closed.'),
	(256, 'CreateNewThread', 'Forum', 'Neues Thema erstellen', 'Crew new thread'),
	(257, 'ThemaVerfasser', 'Forum', 'Thema / Verfasser', 'Discussions / Author'),
	(258, 'Antworten', 'Forum', 'Antworten', 'Answer'),
	(259, 'Ansichten', 'Forum', 'Ansichten', 'Views'),
	(260, 'CreatedFrom', 'Forum', 'Erstellt von', 'Created by'),
	(261, 'AdUser', 'Forum', 'Werbung', 'Advertising'),
	(262, 'AnswerPosted', 'Forum', 'Deine Antwort wurde erfolgreich gepostet', 'Your answer has been successfully posted'),
	(263, 'ThreadName', 'Forum', 'Name:', 'Name:'),
	(264, 'ThreadMessage', 'Forum', 'Nachricht:', 'Message:'),
	(265, 'Create', 'Forum', 'Erstellen', 'Create'),
	(266, 'ThreadCreated', 'Forum', 'Dein Thread wurde erfolgreich erstellt', 'Your Thread has been successfully created'),
	(267, 'Edit', 'Forum', 'Bearbeiten', 'Edit'),
	(268, 'LanguageHint', 'Forum', 'Please create forum posts in english or german.', 'Please create forum posts in english or german.'),
	(269, 'CloseTopic', 'Forum', 'Close topic', 'Close topic'),
	(270, 'Joined', 'Forum', 'Mitglied seit:', 'Joined:'),
	(271, 'Expire3Days', 'Mail', 'Hey, %username%, Dein Gameserver auf dem Port %port% läuft in 3 Tagen aus.<br>Bitte verlängere ihn oder lass ihn auslaufen.<br><br><span style=\'color: red\'>Nach dem Ablauf des Servers werden alle Daten gelöscht</span>', 'Hey %username, your game server with the %port% port expires in 3 days. Please extend your game server or let it expire.<br><br><span style=\'color: red\'>After your server expires, all files will be gone!</span>'),
	(272, 'ReminderSubject7', 'Mail', 'Server läuft in 7 Tagen ab', 'Server expires in seven days'),
	(273, 'Expire7Days', 'Mail', 'Hey, %username%, Dein Gameserver auf dem Port %port% läuft in 7 Tagen aus.<br>Bitte verlängere ihn oder lass ihn auslaufen.<br><br><span style=\'color: red\'>Nach dem Ablauf des Servers werden alle Daten gelöscht</span>', 'Hey, %username%, your game server with the %port% port expires in 7 days. Please extend your game server or let it expire.<br><br><span style=\'color: red\'>After your server expires, all files will be gone!</span>'),
	(274, 'RegisterHeader', 'Mail', 'Vielen Dank für Ihre Registrierung', 'Thank you for your registration'),
	(275, 'WelcomeMessage', 'Mail', 'Hey %username%, Deine Registrierung ist fast abgeschlossen', 'Hey %username%, Your registration is almost complete'),
	(276, 'VerifiyMailText', 'Mail', 'Bitte klicke unten auf dem Button um Deine E-Mail-Adresse zu verifizieren und Deine Registierung bei Gameserver-Sponsor abzuschließen', 'Please click on the button to complete your registration'),
	(277, 'ActiveAccount', 'Mail', 'Account aktivieren', 'Activate account'),
	(278, 'HeadTicketAnswer', 'Mail', 'Neue Antwort auf Ihr Ticket mit den Namen:', 'New answer for your ticket with name:'),
	(279, 'GoToTicket', 'Mail', 'Zum Ticket', 'Open ticket'),
	(280, 'UseTicketSystem', 'Mail', 'Bitte antworten Sie über unser Ticketsystem!', 'Please answer using our ticket system!'),
	(281, 'NewAnswerTicket', 'Mail', 'Neue Antwort auf Ihr Ticket', 'New answer for your ticket'),
	(282, 'dashboard', 'Menu', 'Dashboard', 'Dashboard'),
	(283, 'gp_overview', 'Menu', 'GP Übersicht', 'GP Overview'),
	(284, 'server', 'Menu', 'Gameserver', 'Gameserver'),
	(285, 'blog', 'Menu', 'Blog', 'Blog'),
	(286, 'forum', 'Menu', 'Forum', 'Forum'),
	(287, 'shop', 'Menu', 'Shop', 'Shop'),
	(288, 'support', 'Menu', 'Support', 'Support'),
	(289, 'bugs', 'Menu', 'Bug Tracker', 'Bug Tracker'),
	(290, 'support_uns', 'Menu', 'Unterstütze uns', 'Support Us!'),
	(291, 'supportModal', 'Menu', 'Support', 'Support'),
	(292, 'gameserver', 'Menu', 'Shop', 'Shop'),
	(293, 'status', 'Menu', 'Server Status', 'Server Status'),
	(294, 'partner', 'Menu', 'Partner', 'Partner'),
	(295, 'WebPush', 'RemindServerDeleteMessage', 'Dein Gameserver auf dem Port %d läuft in %d Tag(en) ab.', 'Your gameserver on port %d expires in %d days.'),
	(296, 'readMore', 'Blog', 'mehr lesen', 'read more'),
	(297, 'Like', 'Blog', 'Artikel gefällt mir', 'Like'),
	(298, 'Comment', 'Blog', 'kommentieren', 'comment'),
	(299, 'DoFirstAndWriteComment', 'Blog', 'Sei der erste und schreibe einen Kommentar', 'Write a comment'),
	(300, 'NewBlogItems', 'Blog', 'Übersicht neuester Artikel', 'Newest article overview'),
	(301, 'DeleteComment', 'Blog', 'löschen', 'delete'),
	(302, 'AnswerPlaceholder', 'Blog', 'Antworten...', 'Answer...'),
	(303, 'ArticlesToHistory', 'Blog', 'Artikel zum Thema', 'Articles from topic'),
	(304, 'CommentRequiredLength3', 'Blog', 'Dein Kommentar muss mindestens 3 Zeichen lang sein', 'Your comment must be minimum 3 characters long'),
	(305, 'InternalError', 'Error', 'Internal Error', 'Internal Error'),
	(306, 'Neues Ticket anlegen', 'subPageTitle', 'Neues Ticket anlegen', 'Create new ticket'),
	(307, 'Verbesserungsvorschlag', 'Bugs', 'Verbesserungsvorschlag einreichen', 'Send tip for improvment'),
	(308, 'OpenTickets', 'Bugs', 'Offene Vorschläge', 'Open tips'),
	(309, 'Typ', 'Bugs', 'Art', 'Type'),
	(310, 'Titel', 'Bugs', 'Titel', 'Title'),
	(311, 'CreatedAt', 'Bugs', 'Erstellt am', 'Created at'),
	(312, 'Comments', 'Bugs', 'Kommentare', 'Comments'),
	(313, 'Votes', 'Bugs', 'Votes', 'Votes'),
	(314, 'Feature', 'Bugs', 'Feature', 'Feature'),
	(315, 'Open', 'Bugs', 'Öffnen', 'Open'),
	(316, 'ClosedTickets', 'Bugs', 'Geschlossene Vorschläge', 'Closed requests'),
	(317, 'Status', 'Bugs', 'Status', 'Status'),
	(318, 'Bug', 'Bugs', 'Fehler', 'Bugs'),
	(319, 'DoneTickets', 'Bugs', 'Umgesetzt', 'Done'),
	(320, 'Vorschlag', 'Bugs', 'Vorschlag:', 'Suggestion:'),
	(321, 'Creator', 'Bugs', 'Autor', 'Author'),
	(322, 'Umgesetzt', 'Bugs', 'Umgesetzt', 'Implemented'),
	(323, 'Description', 'Bugs', 'Beschreibung', 'Description'),
	(324, 'WriteComment', 'Bugs', 'Schreibe einen Kommentar', 'Write a comment'),
	(325, 'DeleteComment', 'Bugs', 'löschen', 'delete'),
	(326, 'OpenStatus', 'Bugs', 'Offen', 'Outstanding'),
	(327, 'DoFirstAndWriteComment', 'Bugs', 'Sei der erste und schreibe ein Kommentar', 'Be the first to write a comment'),
	(328, 'TicketName', 'Bugs', '', ''),
	(329, 'TicketMessage', 'Bugs', '', ''),
	(330, 'Type', 'Bugs', 'Art', 'Type'),
	(331, 'Bugs', 'Bugs', 'Bugs', 'Bugs'),
	(332, 'SuccessCreated', 'Bugs', 'Dein Verbesserungsvorschlag wurde erfolgreich eingereicht', 'Your suggestion was sent successfully.'),
	(333, 'Closed', 'Bugs', 'Geschlossen', 'Closed'),
	(334, 'DefaultMetadescription', 'Head', 'Gameserver-Sponsor ist ein kostenloser Gameserver Anbieter und besteht schon seit April 2013. Außerdem nutzt Gameserver-Sponsor ein selbst entwickeltes Control Panel, welches stätig weiter entwickelt wird', 'Gameserver-Sponsor is a free gameserver provider and exists since April 2013. Gameserver-Sponsor is using a self-made Control Panel, which gets developed further every day.'),
	(335, 'MetaKeywords', 'Head', 'gameserver,free,csgo,css,mta,samp,minecraft,multitheftauto', 'gameserver,free,csgo,css,mta,samp,minecraft,multitheftauto'),
	(336, 'infosnewfeatures', 'Index', 'Willkommen bei Gameserver-Sponsor 3', 'Welcome to Gameserver-Sponsor 3'),
	(337, 'infonewpage', 'Index', 'Hallo, Du hast sicher gemerkt, dass sich einiges geändert hat. Damit Du unsere neue Seite verstehst und direkt loslegen kannst, hier eine Übersicht unser neuen Features.', 'Hello, you surely recognized that a lot has changed here. To understand our new webiste, you can find a summary of all new features here:'),
	(338, 'wasistneu', 'Index', 'Was ist neu bei Gameserver-Sponsor 3?', 'What\'s new on Gameserver-Sponsor 3?'),
	(339, 'newfeature', 'Index', 'Neues Forum', 'New Forum'),
	(340, 'newfeature2', 'Index', 'Support System nun über die Website', 'support system now running directly on our website'),
	(341, 'newfeature3', 'Index', 'Neue GP Übersicht', 'new GP overview'),
	(342, 'newfeature4', 'Index', 'Neue Gameserver wie Counter Strike, Garrys Mod , Minecraft und mehr', 'new Gameservers like Counter Strike, Garrys Mod , Minecraft and more'),
	(343, 'newfeature5', 'Index', 'Bugtracker für Feature requests und Bugs reporting', 'bugtracker for feature requests and bug reporting'),
	(344, 'newfeature6', 'Index', 'Account Upgrades für mehr Server Funktionen', 'account upgrades for more server features'),
	(345, 'newfeature7', 'Index', 'Serverfreigabe für andere Nutzer', 'server sharing for other users'),
	(346, 'newfeature8', 'Index', 'Leveling System', 'leveling system'),
	(347, 'newfeature9', 'Index', 'Aktive und Passive Server', 'active and passive servers'),
	(348, 'headaktiveserverpassiveserver', 'Index', 'Wo ist der Unterschied zwischen Aktiven und Passiven Servern?', 'What\'s the difference between active and passive servers?'),
	(349, 'headaktiveserver', 'Index', 'Aktive Server', 'active servers'),
	(350, 'infoaktiveserver', 'Index', 'Unsere aktiven Server funktionieren wie auf unser alten Webseite. Du hast GP und mietest Dir damit Deinen Server der 24/7 für Dich zur verfügung steht und Online ist. Für Deinen täglichen Login und Spieler auf dem Server erhälst Du GP, welche du wiederrum einsetzen kannst um Deinen Server zu verlängern oder Upgrades zu kaufen', 'Our active severs work just like on our old website. With GP you can rent your own sever that will be available for you 24/7. You get GP for your everyday login and for players on your server. You can extend the transit time of your server or buy upgrades for it with GP.'),
	(351, 'headpassiveserver', 'Index', 'Passive Server', 'passive servers'),
	(352, 'infopassiveserver', 'Index', 'Wir haben als neues Servermodel passive Server eingebaut. Diese kosten keine GP. Du erhälst allerdings auch keine GP für Spieler auf diesem Server. Passive Server sind einfach zu verstehen. Du erstellst Dir Deinen Server, richtest ihn ein und startest ihn. Allerdings ist der Server nur solange Online, wie auch Spieler darauf spielen. Sobald auf dem Server keine Spieler mehr sind, schaltet unser System ihn automatisch ab. Die Daten Deines Servers werden natürlich gespeichert und wenn Du weiterspielen möchtest kannst Du ihn wieder starten. Passive Server sind nicht für Projekte oder ähnliches gedacht. Wir haben sie eingebaut, damit man mit Freunden zocken kann.  ', 'As a new server model, we inserted passive servers. Those don\'t cost you any GP, but you won\'t gain GP for players on those servers either. Passive servers are easy to understand. You create your server, arrange it like you want and start it. These servers are just online, when players are using it. As soon as there are no players on a passive server, our system shuts it down. The data will, of course, be saved. When you want to continue playing on it, you can simply restart your server. Passive servers are not suitable for starting projects or something similar. We just inserted them, so you can have fun playing on them with your friends.'),
	(353, 'endtext', 'Index', 'Wir hoffen Dir gefallen unsere Erneuerungen. Wir freuen uns über Deine Rückmeldung. Nun aber genug. Viel Spaß beim nutzen unserer Server!  ', 'We hope you like our new features and would be happy to receive your feedback. But now, enough of that. Have fun using our servers! '),
	(354, 'Close', 'Index', 'Schliessen', 'Close'),
	(355, 'head_status', 'Index', 'Status', 'Status'),
	(356, 'running_servers', 'Index', 'laufende Server: ', 'Running servers:'),
	(357, 'ram', 'Index', 'Ram:', 'Ram:'),
	(358, 'head_shareus', 'Index', 'Teile uns!', 'Share us!'),
	(359, 'text_shareus', 'Index', 'Dir gefällt unser Projekt und Du möchtest uns unterstützen? Dann zeig es doch auch Deinen Freunden, indem Du unsere Seite auf Facebook teilst!', 'You like our project and want to support us? Share us on facebook to show it your friends.'),
	(360, 'Stats', 'Index', 'Statistiken', 'Statistics'),
	(361, 'users_registered', 'Index', 'Registrierte User:', 'Registered users:'),
	(362, 'active_servers', 'Index', 'Aktive Server:', 'Active servers:'),
	(363, 'passive_servers', 'Index', 'Passive Server:', 'Passive servers:'),
	(364, 'OurServers', 'Index', 'Our servers', 'Our servers'),
	(365, 'BreadcrumbTitle', 'GP', 'GP Übersicht', 'GP Overview'),
	(366, 'YouHaveGP', 'GP', 'Du hast zurzeit:', 'Your current account balance:'),
	(367, 'overviewhead', 'GP', 'Übersicht', 'Overview'),
	(368, 'ListAll', 'GP', 'Alle', 'All'),
	(369, 'earned', 'GP', 'Erhalten', 'Received'),
	(370, 'spend', 'GP', 'Ausgegeben', 'Spent'),
	(371, 'graphhead', 'GP', 'Graph', 'Graph'),
	(372, 'AdverseWithBanner', 'GP', 'Mit Bannern werben', 'Advert with banners'),
	(373, 'BannerInfo', 'GP', 'Hier kannst Du unsere Banner finden. Binde den Code in Deinem Forum, Deiner Website oder Deiner Signatur ein. Für jeden Klick auf den Banner erhälst Du ein paar GP für Deine Server.', 'Here, you can see our banners. Tie-in the code on your website, your forum or your signature. For every click on your banner you gain GP for your server.'),
	(374, 'Copy', 'GP', 'Kopieren', 'Copy'),
	(375, 'portlethead', 'Server', 'Gameserver-Verwaltung', 'Gameserver Administration'),
	(376, 'managment', 'Server', 'Verwaltung', 'Administration'),
	(377, 'console', 'Server', 'Konsole', 'Console'),
	(378, 'configeditor', 'Server', 'Editor', 'Editor'),
	(379, 'edit', 'Server', 'Bearbeite', 'Edit'),
	(380, 'FTP_MYSQL', 'Server', 'FTP und MySQL', 'FTP and MySQL'),
	(381, 'serverbanner', 'Server', 'Server Banner', 'Server banner'),
	(382, 'upgrade', 'Server', 'Upgrade', 'Upgrade'),
	(383, 'ServerRights', 'Server', 'Server Berechtigungen', 'Permissions'),
	(384, 'Online', 'Server', 'Dein Server ist momentan<strong> Online!</strong>', 'Your server is currently<strong> online!</strong>'),
	(385, 'start', 'Server', 'Server starten', 'Server start'),
	(386, 'restart', 'Server', 'Server neustarten', 'Server restart'),
	(387, 'stop', 'Server', 'Server stoppen', 'Server stop'),
	(388, 'update', 'Server', 'Server updaten', 'Update server'),
	(389, 'reinstall', 'Server', 'Server Neuinstallation', 'Reinstall server'),
	(390, 'ipv4', 'Server', 'IPv4-Adresse:', 'IPv4-Address:'),
	(391, 'purchaseDate', 'Server', 'Server bestellt am:', 'Server created at:'),
	(392, 'HostSystem', 'Server', 'Hostsystem:', 'Host:'),
	(393, 'Slots', 'Server', 'Slots:', 'Slots:'),
	(394, 'player', 'Server', 'Spieler', 'Players'),
	(395, 'Game', 'Server', 'Spiel:', 'Game:'),
	(396, 'GameserverVersion', 'Server', 'Server Version:', 'Server Version:'),
	(397, 'ServerType', 'Server', 'Servertyp:', 'Server Type:'),
	(398, 'Aktiveserver', 'Server', 'Aktiver Server', 'Active Server'),
	(399, 'duration', 'Server', 'Server läuft bis:', 'Server expires at:'),
	(400, 'buttonaddtime', 'Server', 'Tage Verbleibend | <strong>Verlängern</strong>', 'days remaining | <strong>extend</strong>'),
	(401, 'PressEnterConsoleSend', 'Server', 'Console: Drücken Sie Enter um ein Befehl abzusenden', 'Console: press enter to send your command'),
	(402, 'ConsolHelp', 'Server', 'Konsolen Hilfe', 'Console Help'),
	(403, 'FTP/DB_Info', 'Server', '<strong>Neu:</strong> Du kannst nun bis zu %mysql% MySQL und bis zu %ftp% FTP Konte(n) einrichten', '<strong>New:</strong> You can create up to %mysql% MySQL and up to %ftp% FTP accounts'),
	(404, 'MySQLAccounts', 'Server', 'MySQL Zugänge', 'MySQL accounts'),
	(405, 'accountname', 'Server', 'Zugangs Name:', 'Account name:'),
	(406, 'ipadress', 'Server', 'IP Adresse:', 'IP Address:'),
	(407, 'username', 'Server', 'User:', 'User:'),
	(408, 'description', 'Server', 'Beschreibung:', 'Description:'),
	(409, 'databaseedit', 'Server', 'Datenbank bearbeiten', 'Edit Database'),
	(410, 'databasedelete', 'Server', 'Datenbank löschen', 'Delete Database'),
	(411, 'createaccount', 'Server', 'Zugang anlegen', 'Create new'),
	(412, 'kind', 'Server', 'Art', 'Type'),
	(413, 'kindftp', 'Server', 'FTP', 'FTP'),
	(414, 'kindmysql', 'Server', 'MySQL', 'MySQL'),
	(415, 'createname', 'Server', 'Name', 'Name'),
	(416, 'createpassword', 'Server', 'Passwort', 'Password'),
	(417, 'createftppath', 'Server', 'FTP Pfad', 'FTP Path'),
	(418, 'createdescription', 'Server', 'Beschreibung', 'Description'),
	(419, 'createaccountbutton', 'Server', 'Zugang anlegen', 'Create new'),
	(420, 'YourBanner', 'Server', 'Dein Banner', 'Our Banner'),
	(421, 'BBCode', 'Server', 'BB Code', 'BB Code'),
	(422, 'HTMLCode', 'Server', 'HTML Code', 'HTML Code'),
	(423, 'Copy', 'Server', 'Kopieren', 'Copy'),
	(424, 'Slot', 'Server', 'Slot', 'Slot'),
	(425, 'DatabaseAccounts', 'Server', 'Datenbank Accounts', 'Database accounts'),
	(426, 'FTPAccounts', 'Server', 'FTP Accounts', 'FTP accounts'),
	(427, '24/7Online', 'Server', '24/7 Online', '24/7 Online'),
	(428, 'GuestAccounts', 'Server', 'Gastzugänge', 'Guestaccounts'),
	(429, 'ControlPanel', 'Server', 'Control Panel', 'Control Panel'),
	(430, 'Cost', 'Server', 'Kosten:', 'Costs:'),
	(431, 'perMonth', 'Server', 'im Monat', 'per month'),
	(432, 'ChangeVariant', 'Server', 'Zu dieser Variante wechseln', 'Change to this version'),
	(433, 'NewUser', 'Server', 'Neuen Benutzer hinzufügen', 'Add new user'),
	(434, 'User', 'Server', 'Benutzer', 'User'),
	(435, 'Delete', 'Server', 'Löschen', 'Delete'),
	(436, 'ServerUpdate', 'Server', 'Server Updaten', 'Update server'),
	(437, 'ServerVersion', 'Server', 'Server Version:', 'Server version:'),
	(438, 'ButtonUpdate', 'Server', 'Updaten', 'Update'),
	(439, 'Close', 'Server', 'Schliessen', 'Close'),
	(440, 'Modal/Head', 'Server', 'Server verlängern', 'Extend server time'),
	(441, 'Choosetime/Modal/Head', 'Server', 'Wähle eine Verlängerung', 'Choose an extension time'),
	(442, 'Choosetime/7days/select', 'Server', '7 Tage +(10%)', '7 days +(10%)'),
	(443, 'Choosetime/14days/select', 'Server', '14 Tage +(5%)', '14 days +(5%)'),
	(444, 'Choosetime/30days/select', 'Server', '30 Tage', '30 days'),
	(445, 'Choosetime/60days/select', 'Server', '60 Tage -(2%)', '60 days -(2%)'),
	(446, 'Choosetime/90days/select', 'Server', '90 Tage -(5%)', '90 days -(5%)'),
	(447, 'Timecost', 'Server', 'Verlängerung kostet: ', 'Extension costs:'),
	(448, 'YourGP', 'Server', 'Dein Kontostand:', 'Your current balance:'),
	(449, 'YourGPAfterBuy', 'Server', 'Dein Kontostand nach dem Kauf:', 'Your balance after purchase:'),
	(450, 'ServerRenew', 'Server', 'Server verlängern', 'Extend server time'),
	(451, 'Button/Close', 'Server', 'Schliessen', 'Close'),
	(452, 'ServerDelete', 'Server', 'Server löschen', 'Delete Server'),
	(453, 'SecureGameServerDelete', 'Server', 'Bist Du Dir sicher, dass Du Deinen Gameserver löschen möchtest?', 'Are you sure you want to delete your gameserver?'),
	(454, 'SecureGameServerNotUndo', 'Server', 'Dieser Vorgang kann nicht wieder rückgängig gemacht werden!', 'This step cant do undone.'),
	(455, 'ServerRightsEdit', 'Server', 'Server Rechte bearbeiten', 'Edit server rights'),
	(456, 'Consol', 'Server', 'Konsole', 'Console'),
	(457, 'Save', 'Server', 'Speichern', 'Save'),
	(458, 'editdatabse', 'Server', 'Datenbankzugang bearbeiten', 'Edit database account'),
	(459, 'editdatabasename', 'Server', 'Datenbank Name:', 'Database name:'),
	(460, 'editdatabasepassword', 'Server', 'Datenbank Passwort:', 'Database password'),
	(461, 'editdescription', 'Server', 'Beschreibung:', 'Description:'),
	(462, 'editdatabsesave', 'Server', 'Speichern', 'Save'),
	(463, 'editdatabasecancel', 'Server', 'Abbrechen', 'Cancel'),
	(464, 'ftpedit', 'Server', 'FTP bearbeiten', 'Edit FTP account'),
	(465, 'editftpname', 'Server', 'FTP Name:', 'FTP name:'),
	(466, 'editftppassword', 'Server', 'FTP Passwort:', 'FTP password:'),
	(467, 'editftppath', 'Server', 'FTP Pfad:', 'FTP path:'),
	(468, 'editftpdescription', 'Server', 'Beschreibung:', 'Description:'),
	(469, 'editftpsavebutton', 'Server', 'Speichern', 'Save'),
	(470, 'editftpcancelbutton', 'Server', 'Abbrechen', 'Cancel'),
	(471, 'Game_Options', 'Server', 'Spielspezifische Optionen', 'Specific options'),
	(472, 'Offline', 'Server', 'Dein Server ist momentan<strong> Offline!</strong>', 'Your server is currently<strong> offline!</strong>'),
	(473, 'Passiveserver', 'Server', 'Passiver Server', 'Passive server'),
	(474, 'lastconnect', 'Server', 'Letzte Verbindung:', 'Last connection:'),
	(475, 'ftpdelete', 'Server', 'FTP löschen', 'Delete FTP'),
	(476, 'MyAccount', 'Server', 'Mein Account', 'My account'),
	(477, 'currentGP', 'Server', 'Aktueller Guthabenstand:', 'Your current balance:'),
	(478, 'MyFeatures', 'Server', 'Meine Features', 'My features'),
	(479, 'MaxServer', 'Server', 'Maximale Passive Server:', 'Maximum passive servers:'),
	(480, 'MaxSlots', 'Server', 'Maximale Passive Slots:', 'Maximum passive slots:'),
	(481, 'MaxFTP', 'Server', 'Maximale FTP Zugänge:', 'Maximum FTP accounts:'),
	(482, 'MaxMySQL', 'Server', 'Maximale MySQL Zugänge:', 'Maximum MySQL accounts:'),
	(483, 'MaxGuest', 'Server', 'Maximale Gastzugänge:', 'Maximum Guestaccounts:'),
	(484, 'ConfigSaved', 'Server', 'Die Konfigurationsdatei wurde erfolgreich gespeichert.', 'The config file has been successfully saved.'),
	(485, 'restarting', 'Server', 'Dein Gameserver wird in Kürze restartet.', 'Your gameserver will be restarted soon.'),
	(486, 'starting', 'Server', 'Dein Gameserver wird in Kürze gestartet.', 'Your gameserver will be started soon.'),
	(487, 'LogNotFound', 'Server', 'Keine Log gefunden', 'No log file found'),
	(488, 'DeleteGameserver1', 'Server', 'Gameserver wird zum löschen vorbereitet', 'Preparing for next step'),
	(489, 'DeleteGameserver2', 'Server', 'Server wird gelöscht', 'Deleting server'),
	(490, 'FTPCreated', 'Server', 'Der FTP-Benutzer wurde erfolgreich angelegt', 'The FTP-access has been added successfully'),
	(491, 'DeleteGameserve31', 'Server', 'Spiel wird installiert', 'Game is installing'),
	(492, 'stoping', 'Server', 'Dein Gameserver wird in Kürze gestoppt.', 'Your gameserver will stop shortly.'),
	(493, 'DBCreated', 'Server', 'Die Datenbank wurde erfolgreich angelegt', 'Created database successfully'),
	(494, 'SuccessDeleteMessage', 'Server', 'Dein Gameserver wird in Kürze gelöscht', 'Your gameserver will be deleted soon'),
	(495, 'UserCouldNotFound', 'Server', 'Der Benutzer konnte nicht gefunden werden', 'This user could not be found'),
	(496, 'FTPAccountUpdated', 'Server', 'FTP Account aktualisiert', 'Updated FTP Access'),
	(497, 'updateing', 'Server', 'Dein Gameserver wird in kürze geupdatet.', 'Your gameserver will update shortly.'),
	(498, 'MaxDatabaseReached', 'Server', 'Du hast die maximale Anzahl der Datenbanken erreicht', 'You reached the maximum count of databases'),
	(499, 'ConfigCheckFailed', 'Server', 'Die Validierung der Configurationsdatei ist fehlgeschlagen.', 'The validation of the config failed.'),
	(500, 'MaxFTPUser', 'Server', 'Du hast die Maximale Anzahl der FTP Benutzer erreicht', 'You reached the maximum count of FTP users'),
	(501, 'NotEnoughtGPForDuration', 'Server', 'Du hast nicht genügend GP Punkte um den Gameserver zu verlängern', 'You don\'t have enough GP points to extend your server runtime.'),
	(502, 'SuccessUpgraded', 'Server', 'Dein Server wurde erfolgreich geupgradet', 'Your server has been upgraded successfully'),
	(503, 'ServerDurationAdded', 'Server', 'Server Verlängert', 'Server runtime extended'),
	(504, 'ServerDurationAddedSuccess', 'Server', 'Gameserver erfolgreich verlängert', 'Gameserver runtime extended'),
	(505, 'UserUpdated', 'Server', 'Der Benutzer wurde aktualisiert', 'The user was updated'),
	(506, 'UserAddedToGameserver', 'Server', 'Der Benutzer wurde zum Gameserver hinzugefügt', 'The user was added to gameserver'),
	(507, 'DatabaseUpdated', 'Server', 'Datenbank wurde erfolgreich aktualisiert', 'Database was updated successfully'),
	(508, 'PassiveOnline', 'Server', 'Schaltet sich ab sobald keiner mehr Online ist', 'Shutdown if nobody is online anymore'),
	(509, 'CPUUsageToHighToPassiveServer', 'Server', 'Die Auslastung des Servers ist zurzeit zu hoch um diesen Gameserver zu starten', 'The server usage is currently too high for letting your server start'),
	(510, 'DeleteGameserver', 'Server', 'Du kannst diesen Gameserver nicht löschen', 'You can not delete this gameserver'),
	(511, 'ServerBackGPInfo', 'Server', 'Du erhälst <b>95%</b> Deines Ausgegeben GPs zurück.', 'You get <b>95%</b> of your paid GPs back.'),
	(512, 'ServerBack', 'Server', 'Server zurückgegeben', 'Return server'),
	(513, 'save', 'Server', 'Speichern', 'Save'),
	(514, 'Start', 'Server', 'Start', 'Start'),
	(515, 'Restart', 'Server', 'Neustart', 'Restart'),
	(516, 'ConfigEditor', 'Server', 'Config-Editor', 'Config-Editor'),
	(517, 'Stop', 'Server', 'Stop', 'Stop'),
	(518, 'ftpaccounts', 'Server', 'FTP Zugänge', 'FTP accounts'),
	(519, 'delete', 'Server', 'Dein Gameserver wird in kürze gelöscht', 'Your gameserver will be deleted shortly'),
	(520, 'Edit', 'Server', 'Bearbeiten', 'Edit'),
	(521, 'Unknown', 'Server', 'Der Onlinestatus kann bei diesem Spiel nicht ermittelt werden', 'The online status of the game cannot be accessed'),
	(522, 'UpgradeServer', 'Server', 'Möchtest Du wirklich deinen Gameserver upgraden?', 'Do you really want to upgrade your gameserver?'),
	(523, 'faq', 'Menu', 'FAQ', 'FAQ'),
	(524, 'ServerReinstall', 'Server', 'Server Neuinstallieren', 'Server reinstall'),
	(525, 'ButtonReinstall', 'Server', 'Neuinstallieren', 'Server reinstall'),
	(526, 'DeniedServerUpgrade', 'Server', 'Du kannst diesen Server nicht upgraden', 'You cannot upgrade to this server'),
	(527, 'browse', 'Menu', 'Server Browser', 'Server Browser'),
	(528, 'browse', 'Menu', 'Server Browser', 'Server Browser'),
	(529, 'AccountNotActivated', 'Shop', 'You need to activate your account with sms at first.', 'You need to activate your account with sms at first.'),
	(530, 'Activate Account', 'pageTitle', 'Activate Account', 'Activate Account'),
	(531, 'SMSSend', 'User', 'SMS has been send', 'SMS has been send'),
	(532, 'RenameServer', 'Server', 'Server umbenennen', 'Server umbenennen'),
	(533, 'CreateGameserverTicket', 'Server', 'Create new Support Ticket', 'Create new Support Ticket'),
	(534, 'UserNotActivated', 'Login', 'Bitte aktiviere dein Benutzerkonto vorher', 'Bitte aktiviere dein Benutzerkonto vorher'),
	(535, 'Server Browse', 'pageTitle', 'Server Browse', 'Server Browse'),
	(536, 'blog', 'Menu_Backend', 'Blog', 'Blog'),
	(537, 'forumhead', 'forum', 'Forum', 'Forum'),
	(538, 'AccountActivated', 'User', 'Your Account has been activated', 'Your Account has been activated'),
	(539, 'Like', 'Forum', 'Like post', 'Like post'),
	(540, 'InvalidNumberOrInUse', 'User', 'This phonenumer is already in use or wrong.', 'This phonenumer is already in use or wrong.'),
	(541, 'Erinnerung', 'User', 'Erinnerung', 'Erinnerung'),
	(542, 'LikesThis', 'Forum', 'likes this', 'likes this'),
	(543, 'ResetMessage', 'Mail', 'Hey %username%, bitte klicke auf dem Link um dein Passwort wiederherzustellen.', 'Hey %username%, bitte klicke auf dem Link um dein Passwort wiederherzustellen.'),
	(544, 'FileIsNotUnicode', 'Server', 'Could not read file. Please use ftp to adjust your settings', 'Could not read file. Please use ftp to adjust your settings'),
	(545, 'SMSSendError', 'User', 'SMS couldnt send. Please try later again', 'SMS couldnt send. Please try later again'),
	(546, 'CodeInvalid', 'User', 'Your code is wrong', 'Your code is wrong');
/*!40000 ALTER TABLE `translation` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PasswordEncoder` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bcrypt',
  `Salt` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GP` int(11) NOT NULL DEFAULT 0,
  `RegisterDate` date DEFAULT NULL,
  `LastLogin` int(11) DEFAULT NULL,
  `MaxServer` int(11) DEFAULT 1,
  `MaxMySQL` int(11) DEFAULT 1,
  `MaxFTP` int(11) DEFAULT 1,
  `MaxGast` int(11) DEFAULT 1,
  `MaxSlots` int(11) DEFAULT 5,
  `Avatar` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Signatur` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Skype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `Inhibition` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Visits` int(11) DEFAULT 0,
  `RankPoints` int(11) DEFAULT 0,
  `Language` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'de',
  `Intro` tinyint(1) DEFAULT 0,
  `Permissions` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Europe/Berlin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Username` (`Username`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.users: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.users_notification
CREATE TABLE IF NOT EXISTS `users_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT 0,
  `fromUser` int(11) NOT NULL DEFAULT 0,
  `message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `read` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  CONSTRAINT `users_notification_userid` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.users_notification: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `users_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_notification` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.users_to_gameroot
CREATE TABLE IF NOT EXISTS `users_to_gameroot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `hostID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userID_hostID` (`userID`,`hostID`),
  KEY `users_to_gamroot_hostID` (`hostID`),
  CONSTRAINT `users_to_gameroot_userid` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_to_gamroot_hostID` FOREIGN KEY (`hostID`) REFERENCES `gameroot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.users_to_gameroot: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `users_to_gameroot` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_to_gameroot` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle shyimsql12.users_to_gameserver
CREATE TABLE IF NOT EXISTS `users_to_gameserver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `gameserverID` int(11) DEFAULT NULL,
  `Rights` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `gameserverID` (`gameserverID`),
  CONSTRAINT `FK_users_to_gameserver_gameserver` FOREIGN KEY (`gameserverID`) REFERENCES `gameserver` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle shyimsql12.users_to_gameserver: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `users_to_gameserver` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_to_gameserver` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
