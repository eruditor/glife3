
CREATE TABLE IF NOT EXISTS `rr_glfamilies` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `FD` tinyint(3) UNSIGNED NOT NULL,
  `RB` tinyint(3) UNSIGNED NOT NULL,
  `Rgeom` int(11) NOT NULL,
  `Rsymm` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=cp1251;

INSERT INTO `rr_glfamilies` (`id`, `name`, `FD`, `RB`, `Rgeom`, `Rsymm`) VALUES
(1, 'Conway', 1, 2, 18, 85),
(3, 'Conway3D', 0, 2, 182, 85),
(10, 'Langton', 2, 5, 142, 47);


CREATE TABLE IF NOT EXISTS `rr_glifetris` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `family_id` tinyint(4) NOT NULL,
  `notaset` varchar(88) NOT NULL,
  `mutaset` varchar(500) NOT NULL,
  `named` varchar(50) NOT NULL,
  `typed` varchar(50) NOT NULL,
  `found_dt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `frm` (`family_id`,`notaset`,`mutaset`),
  KEY `named` (`named`),
  KEY `typed` (`typed`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;


CREATE TABLE IF NOT EXISTS `rr_glifetriruns` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gl_id` int(10) UNSIGNED NOT NULL,
  `ver` tinyint(4) NOT NULL,
  `dt` datetime NOT NULL,
  `rseed` bigint(20) UNSIGNED NOT NULL,
  `fseed` bigint(20) UNSIGNED NOT NULL,
  `FW` smallint(5) UNSIGNED NOT NULL DEFAULT '600',
  `FH` smallint(5) UNSIGNED NOT NULL DEFAULT '350',
  `LF` tinyint(3) UNSIGNED NOT NULL DEFAULT '90',
  `rating` int(11) NOT NULL DEFAULT '0',
  `stopped_at` varchar(32) NOT NULL,
  `stopped_nturn` int(10) UNSIGNED NOT NULL,
  `orgasum` int(11) NOT NULL DEFAULT '-1',
  `records` text NOT NULL,
  `context` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gl_id` (`gl_id`),
  KEY `atnt` (`stopped_at`,`stopped_nturn`),
  KEY `orgasum` (`orgasum`),
  KEY `rating` (`rating`),
  KEY `ver` (`ver`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;


CREATE TABLE IF NOT EXISTS `rr_glogs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `glife_id` int(10) UNSIGNED NOT NULL,
  `usr_id` int(10) UNSIGNED NOT NULL,
  `dt` datetime NOT NULL,
  `val0` text NOT NULL,
  `val1` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `glife_id` (`glife_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;