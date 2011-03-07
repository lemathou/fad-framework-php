SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `framework_project`
--

--
-- Contenu de la table `_account`
--

INSERT INTO `_account` (`id`, `create_datetime`, `update_datetime`, `actif`, `actif_hash`, `email`, `password`, `password_crypt`, `lang_id`, `sid`) VALUES
(1, '2010-11-24 02:38:47', '0000-00-00 00:00:00', 1, '', 'email@domain.com', 'password', '', 2, '');

--
-- Contenu de la table `_account_perm_ref`
--

INSERT INTO `_account_perm_ref` (`account_id`, `perm_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5);

--
-- Contenu de la table `_datatype`
--

INSERT INTO `_datatype` (`id`, `name`, `extends`) VALUES
(1, 'string', NULL),
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
(12, 'list', NULL),
(13, 'fromlist', 'list'),
(14, 'table', NULL),
(15, 'file', NULL),
(16, 'stream', NULL),
(17, 'image', 'file'),
(18, 'audio', 'file'),
(19, 'video', 'file'),
(20, 'percent', 'float'),
(21, 'priority', 'integer'),
(22, 'measure', 'float'),
(23, 'money', 'float'),
(24, 'id', 'integer'),
(25, 'name', 'string'),
(26, 'description', 'text'),
(27, 'object', NULL),
(28, 'url', 'string'),
(29, 'dataobject', 'object'),
(30, 'dataobject_select', 'object'),
(31, 'dataobject_list', 'list'),
(32, 'boolean', 'integer'),
(33, 'email', 'string');

--
-- Contenu de la table `_datatype_lang`
--

INSERT INTO `_datatype_lang` (`id`, `lang_id`, `label`, `description`) VALUES
(1, 2, 'Chaîne de caractères', ''),
(2, 2, 'Mot de passe', ''),
(3, 2, 'Nombre entier (integer)', ''),
(4, 2, 'Nombre à virgule (float)', ''),
(5, 2, 'Texte', ''),
(6, 2, 'Texte enrichi (HTML)', ''),
(7, 2, 'Sélection', ''),
(8, 2, 'Date', ''),
(9, 2, 'Année', ''),
(10, 2, 'Durée', ''),
(11, 2, 'Date/Heure (datetime)', ''),
(12, 2, 'Liste', ''),
(13, 2, 'FromList', ''),
(14, 2, 'Tableau', ''),
(15, 2, 'Fichier quelconque (blob)', ''),
(16, 2, 'Flux (stream)', ''),
(17, 2, 'Image', ''),
(18, 2, 'Audio', ''),
(19, 2, 'Video', ''),
(20, 2, 'Pourcentage', ''),
(21, 2, 'Priorité (numérique)', ''),
(22, 2, 'Nombre quantitatif (mesure)', ''),
(23, 2, 'Montant monétaire', ''),
(24, 2, 'ID (identifiant)', ''),
(25, 2, 'Nom / Label', ''),
(26, 2, 'Description', ''),
(27, 2, 'Objet', ''),
(28, 2, 'URL (Adresse web avec protocole quelconque)', ''),
(29, 2, 'Objet d''un datamodel', ''),
(30, 2, 'Dataobject_select (objet d''une liste de databank)', ''),
(31, 2, 'Liste d''objets d''un datamodel', ''),
(32, 2, 'Booléen (OUI/NON)', ''),
(33, 2, 'Email (Adresse)', '');

--
-- Contenu de la table `_lang`
--

INSERT INTO `_lang` (`id`, `code`, `name`) VALUES
(1, 'en', 'English'),
(2, 'fr', 'Français');

--
-- Contenu de la table `_lang_lang`
--

INSERT INTO `_lang_lang` (`id`, `lang_id`, `label`) VALUES
(1, 1, 'English'),
(1, 2, 'Anglais'),
(2, 1, 'French'),
(2, 2, 'Français');

--
-- Contenu de la table `_page`
--

INSERT INTO `_page` (`id`, `type`, `name`, `template_id`, `redirect_url`, `alias_page_id`, `perm`) VALUES
(1, 'template', 'home', 1, NULL, NULL, 1);

--
-- Contenu de la table `_page_lang`
--

INSERT INTO `_page_lang` (`id`, `lang_id`, `label`, `description`, `shortlabel`, `url`) VALUES
(1, 2, 'Accueil', '', 'Accueil', 'Accueil');

--
-- Contenu de la table `_permission`
--

INSERT INTO `_permission` (`id`, `name`) VALUES
(1, 'root'),
(2, 'admin'),
(3, 'dev'),
(4, 'registered'),
(5, 'writer');

--
-- Contenu de la table `_permission_lang`
--

INSERT INTO `_permission_lang` (`id`, `lang_id`, `label`, `description`) VALUES
(1, 2, 'root', 'alias Super-Administrateur'),
(2, 2, 'Administrateur', ''),
(3, 2, 'Développement', 'Pour accéder aux données en cours de développement'),
(4, 2, 'Utilisateur enregistré', 'Depreacated ?'),
(5, 2, 'Redacteur', '');

--
-- Contenu de la table `_template`
--

INSERT INTO `_template` (`id`, `type`, `name`, `mime`, `cache_mintime`, `cache_maxtime`, `login_dependant`) VALUES
(1, 'container', 'home', 'text/html', 60, 300, 0);

--
-- Contenu de la table `_template_lang`
--

INSERT INTO `_template_lang` (`id`, `lang_id`, `label`, `description`, `details`) VALUES
(1, 2, 'Page d''accueil', '', '');

