-- phpMyAdmin SQL Dump
-- version 3.3.2
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 04 Juin 2010 à 01:09
-- Version du serveur: 5.1.41
-- Version de PHP: 5.3.2-1ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Structure de la table `_account`
--

CREATE TABLE IF NOT EXISTS `_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime NOT NULL,
  `update_datetime` datetime NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `username` varchar(16) NOT NULL,
  `password` varchar(16) NOT NULL,
  `password_crypt` varchar(64) NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `email` varchar(128) NOT NULL,
  `sid` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Structure de la table `_account_databank_perm`
--

CREATE TABLE IF NOT EXISTS `_account_databank_perm` (
  `account_id` int(10) unsigned NOT NULL,
  `databank_id` int(10) unsigned NOT NULL,
  `perm` set('a','d','u','i','r') CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`account_id`,`databank_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_account_dataobject_perm`
--

CREATE TABLE IF NOT EXISTS `_account_dataobject_perm` (
  `account_id` int(10) unsigned NOT NULL,
  `databank_id` int(10) unsigned NOT NULL,
  `dataobject_id` int(10) unsigned NOT NULL,
  `perm` set('a','d','u','i','r') CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`account_id`,`databank_id`,`dataobject_id`)
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
  PRIMARY KEY (`account_id`,`template_id`,`widget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_databank_index`
--

CREATE TABLE IF NOT EXISTS `_databank_index` (
  `databank_id` int(10) unsigned NOT NULL,
  `dataobject_id` int(10) unsigned NOT NULL,
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `keywords` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`databank_id`,`dataobject_id`),
  FULLTEXT KEY `keywords` (`keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_databank_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_databank_perm_ref` (
  `databank_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('r','i','u','d','a') CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`databank_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_databank_update`
--

CREATE TABLE IF NOT EXISTS `_databank_update` (
  `databank_id` int(10) unsigned NOT NULL,
  `dataobject_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `action` char(1) NOT NULL,
  `datetime` datetime NOT NULL,
  KEY `databank_id` (`databank_id`,`dataobject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel`
--

CREATE TABLE IF NOT EXISTS `_datamodel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `table` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `library_id` (`library_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  `defaultvalue` text,
  `opt` enum('','key','required','calculated') NOT NULL,
  `lang` tinyint(1) NOT NULL,
  `db_sync` tinyint(1) NOT NULL,
  PRIMARY KEY (`datamodel_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields_lang`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields_lang` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `fieldname` varchar(32) NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `label` varchar(64) NOT NULL,
  PRIMARY KEY (`datamodel_id`,`fieldname`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields_opt`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields_opt` (
  `datamodel_id` varchar(64) NOT NULL,
  `fieldname` varchar(64) NOT NULL,
  `opt_type` enum('structure','db','disp','form') NOT NULL,
  `opt_name` varchar(64) NOT NULL,
  `opt_value` text NOT NULL,
  PRIMARY KEY (`datamodel_id`,`fieldname`,`opt_type`,`opt_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_lang`
--

CREATE TABLE IF NOT EXISTS `_datamodel_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(6) unsigned NOT NULL,
  `label` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_dataobject_perm`
--

CREATE TABLE IF NOT EXISTS `_dataobject_perm` (
  `dataobject_id` int(10) unsigned NOT NULL,
  `name` varchar(16) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`dataobject_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_datatype`
--

CREATE TABLE IF NOT EXISTS `_datatype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `extends` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Structure de la table `_datatype_lang`
--

CREATE TABLE IF NOT EXISTS `_datatype_lang` (
  `datatype_id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`datatype_id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_globals`
--

CREATE TABLE IF NOT EXISTS `_globals` (
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `value` tinytext CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_lang`
--

CREATE TABLE IF NOT EXISTS `_lang` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `_library`
--

CREATE TABLE IF NOT EXISTS `_library` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `description` tinytext CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `_library_lang`
--

CREATE TABLE IF NOT EXISTS `_library_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_library_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_library_perm_ref` (
  `library_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('r','i','u','d','a') CHARACTER SET latin1 NOT NULL,
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
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `_menu_page_ref`
--

CREATE TABLE IF NOT EXISTS `_menu_page_ref` (
  `menu_id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`menu_id`,`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_menu_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_menu_perm_ref` (
  `menu_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`menu_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_module`
--

CREATE TABLE IF NOT EXISTS `_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `description` tinytext CHARACTER SET latin1 NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `_page`
--

CREATE TABLE IF NOT EXISTS `_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `_page_cache`
--

CREATE TABLE IF NOT EXISTS `_page_cache` (
  `page_id` int(10) unsigned NOT NULL,
  `datetime` datetime NOT NULL,
  `hash` varchar(64) NOT NULL,
  PRIMARY KEY (`page_id`,`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_page_lang`
--

CREATE TABLE IF NOT EXISTS `_page_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `titre` varchar(64) NOT NULL,
  `titre_court` varchar(32) NOT NULL,
  `url` varchar(128) NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_params`
--

CREATE TABLE IF NOT EXISTS `_page_params` (
  `page_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` text,
  `update_pos` tinyint(3) unsigned DEFAULT NULL,
  UNIQUE KEY `menu_id` (`page_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_page_params_lang`
--

CREATE TABLE IF NOT EXISTS `_page_params_lang` (
  `page_id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`page_id`,`lang_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_page_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_page_perm_ref` (
  `page_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  `perm` set('a','i','u','d') NOT NULL,
  PRIMARY KEY (`page_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_perm`
--

CREATE TABLE IF NOT EXISTS `_perm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `description` tinytext CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `_perm_lang`
--

CREATE TABLE IF NOT EXISTS `_perm_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_template`
--

CREATE TABLE IF NOT EXISTS `_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Structure de la table `_template_cache`
--

CREATE TABLE IF NOT EXISTS `_template_cache` (
  `filename` varchar(64) NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  `datetime` datetime NOT NULL,
  UNIQUE KEY `filename` (`filename`),
  KEY `template_id` (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_lang`
--

CREATE TABLE IF NOT EXISTS `_template_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
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
  `datatype` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `defaultvalue` text NOT NULL,
  PRIMARY KEY (`template_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `_template_params_lang`
--

CREATE TABLE IF NOT EXISTS `_template_params_lang` (
  `template_id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(64) NOT NULL,
  PRIMARY KEY (`template_id`,`lang_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `_template_params_opt`
--

CREATE TABLE IF NOT EXISTS `_template_params_opt` (
  `template_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `opttype` enum('structure','db','disp','form') NOT NULL,
  `optname` varchar(64) NOT NULL,
  `optvalue` text NOT NULL,
  PRIMARY KEY (`template_id`,`name`,`optname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

