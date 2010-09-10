-- phpMyAdmin SQL Dump
-- version 3.3.2
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 12 Août 2010 à 12:25
-- Version du serveur: 5.1.41
-- Version de PHP: 5.3.2-1ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `framework`
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
  `username` varchar(16) NOT NULL,
  `password` varchar(16) NOT NULL,
  `password_crypt` varchar(64) NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `email` varchar(128) NOT NULL,
  `sid` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `actif` (`actif`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `_account`
--


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

--
-- Contenu de la table `_account_databank_perm`
--


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

--
-- Contenu de la table `_account_dataobject_perm`
--


-- --------------------------------------------------------

--
-- Structure de la table `_account_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_account_perm_ref` (
  `account_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_account_perm_ref`
--


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

--
-- Contenu de la table `_account_template_pref`
--


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

--
-- Contenu de la table `_databank_index`
--


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

--
-- Contenu de la table `_databank_perm_ref`
--


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

--
-- Contenu de la table `_databank_update`
--


-- --------------------------------------------------------

--
-- Structure de la table `_datamodel`
--

CREATE TABLE IF NOT EXISTS `_datamodel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `table` varchar(64) NOT NULL,
  `db_sync` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `library_id` (`library_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Contenu de la table `_datamodel`
--


-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  `defaultvalue` text,
  `opt` enum('','key','required','calculated') NOT NULL,
  `lang` tinyint(1) NOT NULL,
  `db_sync` tinyint(1) NOT NULL,
  PRIMARY KEY (`datamodel_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `_datamodel_fields`
--


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

--
-- Contenu de la table `_datamodel_fields_lang`
--


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

--
-- Contenu de la table `_datamodel_fields_opt`
--


-- --------------------------------------------------------

--
-- Structure de la table `_datamodel_fields_opt_lang`
--

CREATE TABLE IF NOT EXISTS `_datamodel_fields_opt_lang` (
  `datamodel_id` int(10) unsigned NOT NULL,
  `fieldname` varchar(64) NOT NULL,
  `opttype` enum('structure','db','form','disp') NOT NULL,
  `optname` varchar(64) NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `optvalue` text NOT NULL,
  PRIMARY KEY (`datamodel_id`,`fieldname`,`opttype`,`optname`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_datamodel_fields_opt_lang`
--


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

--
-- Contenu de la table `_datamodel_lang`
--


-- --------------------------------------------------------

--
-- Structure de la table `_dataobject_perm`
--

CREATE TABLE IF NOT EXISTS `_dataobject_perm` (
  `dataobject_id` int(10) unsigned NOT NULL,
  `name` varchar(16) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`dataobject_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_dataobject_perm`
--


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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

--
-- Contenu de la table `_datatype`
--

INSERT INTO `_datatype` (`id`, `name`, `extends`) VALUES
(1, 'string', ''),
(2, 'password', 'string'),
(3, 'integer', 'string'),
(4, 'float', 'string'),
(5, 'text', 'string'),
(6, 'richtext', 'text'),
(7, 'select', 'string'),
(8, 'date', 'string'),
(9, 'year', 'string'),
(10, 'time', 'string'),
(11, 'datetime', 'string'),
(12, 'list', ''),
(13, 'fromlist', 'list'),
(14, 'table', ''),
(15, 'file', ''),
(16, 'stream', ''),
(17, 'image', ''),
(18, 'audio', ''),
(19, 'video', ''),
(20, 'number', 'integer'),
(21, 'priority', 'number'),
(22, 'measure', 'float'),
(23, 'money', 'float'),
(24, 'id', 'number'),
(25, 'name', 'string'),
(26, 'description', 'text'),
(27, 'object', ''),
(28, 'agregat', 'object'),
(29, 'dataobject', 'agregat'),
(30, 'dataobject_select', 'agregat'),
(31, 'dataobject_list', 'list'),
(32, 'dataobject_list_ref', 'dataobject_list'),
(33, 'email', 'string'),
(34, 'url', 'string'),
(35, 'boolean', 'integer'),
(36, 'percent', 'float');

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

--
-- Contenu de la table `_datatype_lang`
--

INSERT INTO `_datatype_lang` (`datatype_id`, `lang_id`, `title`) VALUES
(1, 2, 'Chaîne de caractères'),
(2, 2, 'Mot de passe'),
(3, 2, 'Integer (nbre entier)'),
(4, 2, 'Float (nbre à virgule)'),
(5, 2, 'Texte'),
(6, 2, 'Texte enrichi (HTML)'),
(7, 2, 'Sélection'),
(8, 2, 'Date'),
(9, 2, 'Année'),
(10, 2, 'Durée'),
(11, 2, 'Date/Heure (datetime)'),
(12, 2, 'Liste'),
(13, 2, 'FromList'),
(14, 2, 'Tableau'),
(15, 2, 'Fichier quelconque (blob)'),
(16, 2, 'Flux (stream)'),
(17, 2, 'Image'),
(18, 2, 'Audio'),
(19, 2, 'Video'),
(20, 2, 'Nombre entier'),
(21, 2, 'Priorité (numérique)'),
(22, 2, 'Mesure'),
(23, 2, 'Montant monétaire'),
(24, 2, 'ID (identifiant)'),
(25, 2, 'Nom'),
(26, 2, 'Description'),
(27, 2, 'Objet'),
(28, 2, 'Agrégat de données'),
(29, 2, 'Dataobject (objet d''une databank)'),
(30, 2, 'Dataobject_select (objet d''une liste de databank)'),
(31, 2, 'Dataobject_list (Liste d''objets d''une databank)'),
(32, 2, 'Dataobject_list_ref'),
(33, 2, 'Email (Adresse)'),
(34, 2, 'URL (Adresse web avec protocole quelconque)'),
(35, 2, 'Booléen (OUI/NON)'),
(36, 2, 'Pourcentage');

-- --------------------------------------------------------

--
-- Structure de la table `_globals`
--

CREATE TABLE IF NOT EXISTS `_globals` (
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `value` tinytext CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_globals`
--


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

--
-- Contenu de la table `_lang`
--

INSERT INTO `_lang` (`id`, `code`, `name`) VALUES
(1, 'en', 'English'),
(2, 'fr', 'Français');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Contenu de la table `_library`
--


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

--
-- Contenu de la table `_library_lang`
--


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

--
-- Contenu de la table `_library_perm_ref`
--


-- --------------------------------------------------------

--
-- Structure de la table `_library_ref`
--

CREATE TABLE IF NOT EXISTS `_library_ref` (
  `parent_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`parent_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_library_ref`
--


-- --------------------------------------------------------

--
-- Structure de la table `_menu`
--

CREATE TABLE IF NOT EXISTS `_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `_menu`
--


-- --------------------------------------------------------

--
-- Structure de la table `_menu_lang`
--

CREATE TABLE IF NOT EXISTS `_menu_lang` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_menu_lang`
--


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

--
-- Contenu de la table `_menu_page_ref`
--


-- --------------------------------------------------------

--
-- Structure de la table `_menu_perm_ref`
--

CREATE TABLE IF NOT EXISTS `_menu_perm_ref` (
  `menu_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`menu_id`,`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_menu_perm_ref`
--


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

--
-- Contenu de la table `_module`
--


-- --------------------------------------------------------

--
-- Structure de la table `_page`
--

CREATE TABLE IF NOT EXISTS `_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('static_html','php','template','redirect','alias') NOT NULL DEFAULT 'template',
  `name` varchar(64) NOT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `description` text NOT NULL,
  `redirect_url` varchar(256) DEFAULT NULL,
  `alias_page_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

--
-- Contenu de la table `_page`
--


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

--
-- Contenu de la table `_page_lang`
--


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

--
-- Contenu de la table `_page_params`
--


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

--
-- Contenu de la table `_page_params_lang`
--


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

--
-- Contenu de la table `_page_perm_ref`
--


-- --------------------------------------------------------

--
-- Structure de la table `_page_scripts`
--

CREATE TABLE IF NOT EXISTS `_page_scripts` (
  `page_id` int(10) unsigned NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  `script_name` varchar(128) NOT NULL,
  PRIMARY KEY (`page_id`,`pos`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_page_scripts`
--


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

--
-- Contenu de la table `_perm`
--

INSERT INTO `_perm` (`id`, `name`, `description`) VALUES
(1, 'anonymous', 'Utilisateur anonyme'),
(2, 'root', 'super administrateur'),
(3, 'all', 'Tous les utilisateurs'),
(4, 'registered', 'Utilisateur enregistrés'),
(5, 'dev', 'Parties du site en développement'),
(6, 'admin', 'Administrateur');

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

--
-- Contenu de la table `_perm_lang`
--


-- --------------------------------------------------------

--
-- Structure de la table `_template`
--

CREATE TABLE IF NOT EXISTS `_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('container','inc','datamodel','page') NOT NULL DEFAULT 'page',
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `cache_mintime` tinyint(4) NOT NULL DEFAULT '60',
  `cache_maxtime` int(10) unsigned NOT NULL DEFAULT '300',
  `login_dependant` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=69 ;

--
-- Contenu de la table `_template`
--


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

--
-- Contenu de la table `_template_cache`
--


-- --------------------------------------------------------

--
-- Structure de la table `_template_inclusion`
--

CREATE TABLE IF NOT EXISTS `_template_inclusion` (
  `template_id` int(10) unsigned NOT NULL,
  `template_include_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`template_id`,`template_include_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_template_inclusion`
--


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

--
-- Contenu de la table `_template_lang`
--


-- --------------------------------------------------------

--
-- Structure de la table `_template_library_ref`
--

CREATE TABLE IF NOT EXISTS `_template_library_ref` (
  `template_id` int(10) unsigned NOT NULL,
  `library_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`template_id`,`library_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_template_library_ref`
--


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

--
-- Contenu de la table `_template_params`
--


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

--
-- Contenu de la table `_template_params_lang`
--


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

--
-- Contenu de la table `_template_params_opt`
--


-- --------------------------------------------------------

--
-- Structure de la table `_template_scripts`
--

CREATE TABLE IF NOT EXISTS `_template_scripts` (
  `template_id` int(10) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`template_id`,`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `_template_scripts`
--

