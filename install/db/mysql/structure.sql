SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `framework_project`
--

-- --------------------------------------------------------

--
-- Structure de la table `_account`
--

CREATE TABLE IF NOT EXISTS `_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime NOT NULL,
  `update_datetime` datetime NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `actif_hash` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(16) NOT NULL,
  `password_crypt` varchar(64) NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sid` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_datamodel_perm`
--

CREATE TABLE IF NOT EXISTS `_account_datamodel_perm` (
  `account_id` int(10) unsigned NOT NULL,
  `datamodel_id` int(10) unsigned NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`account_id`,`datamodel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_dataobject_perm`
--

CREATE TABLE IF NOT EXISTS `_account_dataobject_perm` (
  `account_id` int(10) unsigned NOT NULL,
  `databank_id` int(10) unsigned NOT NULL,
  `dataobject_id` int(10) unsigned NOT NULL,
  `perm` set('l','i','r','u','d','a') NOT NULL,
  PRIMARY KEY (`account_id`,`databank_id`,`dataobject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_page_pref`
--

CREATE TABLE IF NOT EXISTS `_account_page_pref` (
  `account_id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`account_id`,`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_perm`
--

CREATE TABLE IF NOT EXISTS `_account_perm` (
  `account_id` int(10) unsigned NOT NULL,
  `type` enum('library','datamodel','template','page','menu') NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  UNIQUE KEY `account_id` (`account_id`,`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_account_perm_ref` (
  `account_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_template_pref`
--

CREATE TABLE IF NOT EXISTS `_account_template_pref` (
  `account_id` int(10) unsigned NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  `widget_id` int(10) unsigned NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`account_id`,`template_id`,`widget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel`
--

CREATE TABLE IF NOT EXISTS `_datamodel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `db_sync` tinyint(1) NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  `dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `db` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `defaultvalue` text ,
  `opt` enum('','key','required','calculated') NOT NULL,
  `update` enum('','readonly','auto') DEFAULT NULL,
  `query` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Index Fulltext',
  `lang` tinyint(1) NOT NULL,
  `db_sync` tinyint(1) NOT NULL,
  PRIMARY KEY (`datamodel_id`,`name`),
  UNIQUE KEY `datamodel_id` (`datamodel_id`,`pos`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields_lang`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields_lang` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `fieldname` varchar(32) NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `label` varchar(64) NOT NULL,
  PRIMARY KEY (`datamodel_id`,`lang_id`,`fieldname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields_opt`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields_opt` (
  `datamodel_id` int(6) unsigned NOT NULL,
  `fieldname` varchar(32) NOT NULL,
  `opt_name` varchar(32) NOT NULL,
  `opt_value` text NOT NULL,
  PRIMARY KEY (`datamodel_id`,`fieldname`,`opt_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields_opt_lang`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields_opt_lang` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `fieldname` varchar(32) NOT NULL,
  `optname` varchar(32) NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `optvalue` text NOT NULL,
  PRIMARY KEY (`datamodel_id`,`lang_id`,`fieldname`,`optname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_lang`
--

CREATE TABLE IF NOT EXISTS `_datamodel_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(6) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_datamodel_perm_ref` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`datamodel_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_update`
--

CREATE TABLE IF NOT EXISTS `_datamodel_update` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `dataobject_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `action` char(1) NOT NULL,
  `datetime` datetime NOT NULL,
  KEY `databank_id` (`datamodel_id`,`dataobject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_dataobject_action`
--

CREATE TABLE IF NOT EXISTS `_dataobject_action` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `dataobject_id` int(10) unsigned NOT NULL,
  `action` varchar(32) NOT NULL,
  `datetime` datetime NOT NULL,
  `detail` text NOT NULL,
  KEY `datamodel_id` (`datamodel_id`,`dataobject_id`,`action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_dataobject_perm`
--

CREATE TABLE IF NOT EXISTS `_dataobject_perm` (
  `dataobject_id` int(10) unsigned NOT NULL,
  `perm_id` tinyint(3) unsigned NOT NULL,
  `perm` varchar(8) NOT NULL,
  PRIMARY KEY (`dataobject_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_dataobject_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_dataobject_perm_ref` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `perm_id` tinyint(3) unsigned NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`datamodel_id`,`object_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datatype`
--

CREATE TABLE IF NOT EXISTS `_datatype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `extends` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datatype_lang`
--

CREATE TABLE IF NOT EXISTS `_datatype_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_globals`
--

CREATE TABLE IF NOT EXISTS `_globals` (
  `name` varchar(32) NOT NULL,
  `value` tinytext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_lang`
--

CREATE TABLE IF NOT EXISTS `_lang` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_lang_lang`
--

CREATE TABLE IF NOT EXISTS `_lang_lang` (
  `id` tinyint(3) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_library`
--

CREATE TABLE IF NOT EXISTS `_library` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_library_lang`
--

CREATE TABLE IF NOT EXISTS `_library_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_library_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_library_perm_ref` (
  `library_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`library_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_library_ref`
--

CREATE TABLE IF NOT EXISTS `_library_ref` (
  `parent_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`parent_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_menu`
--

CREATE TABLE IF NOT EXISTS `_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_menu_lang`
--

CREATE TABLE IF NOT EXISTS `_menu_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_menu_page_ref`
--

CREATE TABLE IF NOT EXISTS `_menu_page_ref` (
  `menu_id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`menu_id`,`page_id`),
  KEY `menu_id` (`menu_id`,`pos`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_menu_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_menu_perm_ref` (
  `menu_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`menu_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_mime`
--

CREATE TABLE IF NOT EXISTS `_mime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('application','audio','image','text','video') NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_module`
--

CREATE TABLE IF NOT EXISTS `_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` tinytext NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page`
--

CREATE TABLE IF NOT EXISTS `_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('static_html','php','template','redirect','alias') NOT NULL DEFAULT 'template',
  `name` varchar(32) NOT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `redirect_url` varchar(256) DEFAULT NULL,
  `alias_page_id` int(10) unsigned DEFAULT NULL,
  `perm` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_lang`
--

CREATE TABLE IF NOT EXISTS `_page_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `shortlabel` varchar(64) NOT NULL,
  `url` varchar(128) NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_params`
--

CREATE TABLE IF NOT EXISTS `_page_params` (
  `page_id` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `datatype` varchar(32) NOT NULL,
  `value` text,
  `update_pos` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`page_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_params_lang`
--

CREATE TABLE IF NOT EXISTS `_page_params_lang` (
  `page_id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`page_id`,`lang_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_params_opt`
--

CREATE TABLE IF NOT EXISTS `_page_params_opt` (
  `page_id` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `optname` varchar(32) NOT NULL,
  `optvalue` text NOT NULL,
  PRIMARY KEY (`page_id`,`name`,`optname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_page_perm_ref` (
  `page_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`page_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_template`
--

CREATE TABLE IF NOT EXISTS `_page_template` (
  `page_id` int(10) unsigned NOT NULL,
  `vue_name` varchar(32) NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  `params` text,
  PRIMARY KEY (`page_id`,`vue_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_template_params`
--

CREATE TABLE IF NOT EXISTS `_page_template_params` (
  `page_id` int(10) unsigned NOT NULL,
  `vue_name` varchar(32) NOT NULL,
  `param_name` varchar(32) NOT NULL,
  `param_value` text,
  `param_map` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`page_id`,`vue_name`,`param_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_permission`
--

CREATE TABLE IF NOT EXISTS `_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_permission_lang`
--

CREATE TABLE IF NOT EXISTS `_permission_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template`
--

CREATE TABLE IF NOT EXISTS `_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('container','inc','datamodel','page') NOT NULL DEFAULT 'page',
  `name` varchar(32) NOT NULL,
  `mime` varchar(64) NOT NULL DEFAULT 'text/html',
  `cache_mintime` tinyint(4) NOT NULL DEFAULT '60',
  `cache_maxtime` int(10) unsigned NOT NULL DEFAULT '300',
  `login_dependant` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`type`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_cache`
--

CREATE TABLE IF NOT EXISTS `_template_cache` (
  `template_id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `params` text NOT NULL,
  `datetime` datetime NOT NULL,
  `hash` varchar(32) NOT NULL,
  `content` text NOT NULL,
  UNIQUE KEY `template_id` (`template_id`,`lang_id`,`account_id`,`hash`),
  KEY `datetime` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_lang`
--

CREATE TABLE IF NOT EXISTS `_template_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` tinytext NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_library_ref`
--

CREATE TABLE IF NOT EXISTS `_template_library_ref` (
  `template_id` int(10) unsigned NOT NULL,
  `library_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`template_id`,`library_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_params`
--

CREATE TABLE IF NOT EXISTS `_template_params` (
  `template_id` int(10) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `datatype` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`template_id`,`name`),
  UNIQUE KEY `template_id` (`template_id`,`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_params_lang`
--

CREATE TABLE IF NOT EXISTS `_template_params_lang` (
  `template_id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `label` varchar(128) NOT NULL,
  PRIMARY KEY (`template_id`,`lang_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_params_opt`
--

CREATE TABLE IF NOT EXISTS `_template_params_opt` (
  `template_id` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `optname` varchar(32) NOT NULL,
  `optvalue` text NOT NULL,
  PRIMARY KEY (`template_id`,`name`,`optname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_template_perm_ref` (
  `template_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('l','r','i','u','d','a') NOT NULL,
  PRIMARY KEY (`template_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

