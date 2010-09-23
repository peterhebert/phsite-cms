<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['installer_langs'] = array(
'eng' => 'english',
'fra' => 'francais'
);

// default table definitions
$config['tables'] = array(
'c_content' => array('content_id', 'site_id', 'user_id', 'parent_id', 'type_id', 'type_uri_name', 'date_created', 'date_end_expiry', 'date_updated', 'publish'),
'c_media' => array('media_id', 'site_id', 'file_uri', 'external', 'mime_type', 'date_created', 'publish'),
'c_metadata' => array('meta_id', 'site_id', 'scope', 'rel_id', 'field_id', 'field_ref_name', 'value', 'lang_ISO_639_3', 'date_updated'),
'c_titles' => array('title_id', 'site_id', 'scope', 'rel_id', 'title', 'uri_title', 'subtitle', 'keywords', 'lang_ISO_639_3', 'title_updated'),
'ISO_639_LANG' => array('ISO_639_3', 'ISO_639_2B', 'ISO_639_2T', 'ISO_639_1', 'Scope', 'Type', 'Ref_Name', 'Inverted_Name', 'Native_Name', 'CI_Name', 'activated'),
'r_attachments' => array('a_id', 'content_id', 'media_id'),
'r_fields' => array('rf_id', 'scope', 'rf_rel_id', 'rf_rel_ref_name', 'field_id', 'field_ref_name', 'required', 'order'),
'r_list_vals' => array('val_id', 'list_id', 'list_ref_name', 'value', 'label', 'lang_ISO_639_3'),
'r_sites_users' => array('rel_id', 'site_id', 'user_id', 'admin'),
's_fields' => array('field_id', 'field_ref_name', 'field_input_type', 'field_validation', 'field_needs_lang', 'list_id', 'field_allow_multiple'),
's_labels' => array('label_id', 'scope', 'rel_id', 'rel_name', 'label', 'lang_ISO_639_3'),
's_lists' => array('list_id', 'list_ref_name'),
's_site_prefs' => array('site_id', 'theme', 'timezone', 'default_lang', 'default_content_type', 'date_created', 'date_updated', 'home_content_id', 'home_content_type', 'site_admin_id', 'site_editor_id', 'img_size_th', 'img_size_sm', 'img_size_md', 'img_size_lg', 'limit_posts', 'limit_thumbs'),
's_types' => array('type_id', 'scope', 'type_uri_name'),
's_users' => array('user_id', 'name_first', 'name_last', 'username', 'password', 'timezone', 'default_lang', 'email')
);

$config['tables_mssql'] = array(
'c_content' => "CREATE TABLE IF NOT EXISTS \"c_content\" (\"content_id\" int NOT NULL IDENTITY, \"site_id\" int NOT NULL DEFAULT '1', \"user_id\" int NOT NULL DEFAULT '1', \"parent_id\" int NOT NULL DEFAULT '0' COMMENT '0 is root, -1 is non-hierarchical', \"type_id\" int NOT NULL DEFAULT '1', \"type_uri_name\" ntext, \"date_created\" datetime NOT NULL, \"date_end_expiry\" datetime DEFAULT NULL, \"date_updated\" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, \"publish\" int NOT NULL DEFAULT '1', PRIMARY KEY (\"content_id\"))",
'c_media' => "CREATE TABLE IF NOT EXISTS \"c_media\" (\"media_id\" int NOT NULL IDENTITY, \"site_id\" int NOT NULL DEFAULT '1', \"file_uri\" ntext, \"external\" tinyint(1) NOT NULL DEFAULT '0', \"mime_type\" nvarchar(100) DEFAULT 'image/jpeg', \"date_created\" datetime NOT NULL, \"publish\" tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (\"media_id\"))",
'c_metadata' => "CREATE TABLE IF NOT EXISTS \"c_metadata\" (\"meta_id\" int NOT NULL IDENTITY, \"site_id\" int NOT NULL DEFAULT '1', \"scope\" ntext NOT NULL COMMENT 'metadata for content, user or site', \"rel_id\" int NOT NULL, \"field_id\" int NOT NULL, \"field_ref_name\" ntext NOT NULL, \"value\" nmediumtextNOT NULL, \"lang_ISO_639_3\" nvarchar(3) DEFAULT NULL, \"date_updated\" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (\"meta_id\"), FULLTEXT KEY \"value\" (\"value\"))",
'c_titles' => "CREATE TABLE IF NOT EXISTS \"c_titles\" (\"title_id\" int NOT NULL IDENTITY, \"site_id\" int NOT NULL DEFAULT '1', \"rel_id\" int NOT NULL COMMENT 'the id of the (content,media,site) being described', \"scope\" ntext NOT NULL COMMENT 'title is for content, media or site', \"title\" ntext NOT NULL, \"uri_title\" ntext NOT NULL, \"subtitle\" ntext, \"keywords\" ntext, \"lang_ISO_639_3\" nvarchar(3) NOT NULL, \"title_updated\" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (\"title_id\"), FULLTEXT KEY \"title\" (\"title\",\"subtitle\"), FULLTEXT KEY \"keywords\" (\"keywords\"))",
'ISO_639_LANG' => "CREATE TABLE IF NOT EXISTS \"ISO_639_LANG\" (\"ISO_639_3\" nchar(3) NOT NULL, \"ISO_639_2B\" nchar(3) DEFAULT NULL, \"ISO_639_2T\" nchar(3) DEFAULT NULL, \"ISO_639_1\" nchar(2) DEFAULT NULL, \"Scope\" nchar(1) NOT NULL COMMENT 'I(ndividual), M(acrolanguage), S(pecial)', \"Type\" nchar(1) NOT NULL COMMENT 'A(ncient), C(onstructed), E(xtinct), H(istorical), L(iving), S(pecial)', \"Ref_Name\" nvarchar(150) NOT NULL COMMENT 'Reference language name in English', \"Inverted_Name\" nvarchar(75) NOT NULL COMMENT 'inverted version of Ref_name for alpha sorting', \"Native_Name\" nvarchar(75) DEFAULT NULL, \"CI_Name\" nvarchar(150) DEFAULT NULL, \"activated\" tinyint(1) NOT NULL DEFAULT '0', KEY \"639_3\" (\"ISO_639_3\"))",
'r_attachments' => "CREATE TABLE IF NOT EXISTS \"r_attachments\" (\"a_id\" int NOT NULL IDENTITY, \"content_id\" int NOT NULL, \"media_id\" int NOT NULL, PRIMARY KEY (\"a_id\"))",
'r_fields' => "CREATE TABLE IF NOT EXISTS \"r_fields\" (\"rf_id\" int NOT NULL IDENTITY, \"scope\" ntext NOT NULL, \"rf_rel_id\" int DEFAULT NULL, \"rf_rel_ref_name\" ntext, \"field_id\" int NOT NULL, \"field_ref_name\" ntext, \"required\" tinyint(1) NOT NULL DEFAULT '0', \"order\" smallint(5) NOT NULL DEFAULT '0', PRIMARY KEY (\"rf_id\"))",
'r_list_vals' => "CREATE TABLE IF NOT EXISTS \"r_list_vals\" (\"val_id\" int NOT NULL IDENTITY, \"list_id\" int NOT NULL, \"list_ref_name\" ntext NOT NULL, \"value\" ntext NOT NULL, \"label\" ntext NOT NULL, \"lang_ISO_639_3\" nchar(3) DEFAULT NULL, PRIMARY KEY (\"val_id\"))",
'r_sites_users' => "CREATE TABLE IF NOT EXISTS \"r_sites_users\" (\"rel_id\" int NOT NULL IDENTITY, \"site_id\" int NOT NULL, \"user_id\" int NOT NULL, \"admin\" tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is user administrator', PRIMARY KEY (\"rel_id\"))",
's_fields' => "CREATE TABLE IF NOT EXISTS \"s_fields\" (\"field_id\" int NOT NULL IDENTITY, \"field_ref_name\" ntext NOT NULL, \"field_input_type\" nvarchar(9) NOT NULL, \"field_validation\" ntext, \"field_needs_lang\" tinyint(1) NOT NULL DEFAULT '1', \"list_id\" int NOT NULL, PRIMARY KEY (\"field_id\"))",
's_labels' => "CREATE TABLE IF NOT EXISTS \"s_labels\" (\"label_id\" int NOT NULL IDENTITY, \"scope\" ntext NOT NULL, \"rel_id\" int NOT NULL COMMENT 'primary id of related item', \"rel_name\" ntext COMMENT 'human-readable name of of related item', \"label\" ntext NOT NULL, \"lang_ISO_639_3\" nchar(3) NOT NULL DEFAULT 'eng', PRIMARY KEY (\"label_id\"))",
's_lists' => "CREATE TABLE IF NOT EXISTS \"s_lists\" (\"list_id\" int NOT NULL IDENTITY, \"list_ref_name\" ntext NOT NULL, PRIMARY KEY (\"list_id\"))",
's_site_prefs' => "CREATE TABLE IF NOT EXISTS \"s_site_prefs\" (\"site_id\" int NOT NULL IDENTITY, \"theme\" ntext, \"timezone\" nvarchar(100) NOT NULL DEFAULT 'America/Vancouver' COMMENT 'default time zone for site', \"default_lang\" nvarchar(3) NOT NULL DEFAULT 'eng', \"default_content_type\" int NOT NULL DEFAULT '1', \"date_created\" datetime NOT NULL, \"date_updated\" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, \"home_content_id\" int NOT NULL DEFAULT '0', \"site_admin_id\" int NOT NULL DEFAULT '1', \"site_editor_id\" int NOT NULL DEFAULT '1', \"img_size_th\" smallint(5) NOT NULL DEFAULT '120', \"img_size_sm\" smallint(5) NOT NULL DEFAULT '255', \"img_size_md\" smallint(5) NOT NULL DEFAULT '480', \"img_size_lg\" smallint(5) NOT NULL DEFAULT '795', \"limit_posts\" smallint(5) NOT NULL DEFAULT '5', \"limit_thumbs\" smallint(5) NOT NULL DEFAULT '30', PRIMARY KEY (\"site_id\"))",
's_types' => "CREATE TABLE IF NOT EXISTS \"s_types\" ( \"type_id\" int NOT NULL IDENTITY,  \"scope\" ntext NOT NULL,  \"type_uri_name\" ntext NOT NULL,  \"index_home\" tinyint  NOT NULL default '0', \"index_rss\" tinyint  NOT NULL default '1', \"menu_children\" tinyint  NOT NULL default '1', PRIMARY KEY (\"type_id\"))",
's_users' => "CREATE TABLE IF NOT EXISTS \"s_users\" (\"user_id\" int NOT NULL IDENTITY, \"name_first\" nvarchar(20) NOT NULL, \"name_last\" nvarchar(20) NOT NULL, \"username\" nvarchar(20) NOT NULL COMMENT 'username', \"password\" nvarchar(50) NOT NULL COMMENT 'hash of salt & password', \"timezone\" nvarchar(100) NOT NULL DEFAULT 'America/Vancouver' COMMENT 'time zone for user', \"default_lang\" nvarchar(3) NOT NULL DEFAULT 'eng', \"email\" ntext NOT NULL, PRIMARY KEY (\"user_id\"))",
);

$config['tables_mysql'] = array(
'c_content' => "CREATE TABLE `c_content` ( `content_id` int(10) unsigned NOT NULL auto_increment, `user_id` int(10) unsigned NOT NULL default '1', `site_id` int(10) unsigned NOT NULL default '1', `parent_id` int(11) NOT NULL default '0' COMMENT '0 is root, -1 is non-hierarchical', `type_id` int(10) unsigned NOT NULL default '1', `type_uri_name` text, `date_created` datetime NOT NULL, `date_end_expiry` datetime default NULL, `date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, `publish` int(10) unsigned NOT NULL default '1', PRIMARY KEY (`content_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
'c_media' => "CREATE TABLE `c_media` ( `media_id` int(10) unsigned NOT NULL auto_increment, `site_id` int(10) unsigned NOT NULL default '1', `file_uri` text, `external` tinyint(1) NOT NULL default '0', `mime_type` varchar(100) default 'image/jpeg', `date_created` datetime NOT NULL, `publish` tinyint(1) NOT NULL default '0', PRIMARY KEY (`media_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='media file info'",
'c_metadata' => "CREATE TABLE `c_metadata` ( `meta_id` int(10) unsigned NOT NULL auto_increment, `site_id` int(10) unsigned NOT NULL default '1', `scope` text NOT NULL COMMENT 'metadata for content, user or site', `rel_id` int(10) unsigned NOT NULL, `field_id` int(10) unsigned NOT NULL, `field_ref_name` text NOT NULL, `value` mediumtext NOT NULL, `lang_ISO_639_3` varchar(3) default NULL, `date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, PRIMARY KEY (`meta_id`), FULLTEXT KEY `value` (`value`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
'c_titles' => "CREATE TABLE `c_titles` ( `title_id` int(10) unsigned NOT NULL auto_increment, `site_id` int(10) unsigned NOT NULL default '1', `scope` text NOT NULL COMMENT 'title is for content, media or site', `rel_id` int(10) unsigned NOT NULL COMMENT 'the id of the (content,media,site) being described', `title` text NOT NULL, `uri_title` text NOT NULL, `subtitle` text, `keywords` text, `lang_ISO_639_3` varchar(3) NOT NULL, `title_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, PRIMARY KEY (`title_id`), FULLTEXT KEY `title` (`title`,`subtitle`), FULLTEXT KEY `keywords` (`keywords`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
'ISO_639_LANG' => "CREATE TABLE `ISO_639_LANG` ( `ISO_639_3` char(3) NOT NULL, `ISO_639_2B` char(3) default NULL, `ISO_639_2T` char(3) default NULL, `ISO_639_1` char(2) default NULL, `Scope` char(1) NOT NULL COMMENT 'I(ndividual), M(acrolanguage), S(pecial)', `Type` char(1) NOT NULL COMMENT 'A(ncient), C(onstructed), E(xtinct), H(istorical), L(iving), S(pecial)', `Ref_Name` varchar(150) NOT NULL COMMENT 'Reference language name in English', `Inverted_Name` varchar(75) NOT NULL COMMENT 'inverted version of Ref_name for alpha sorting', `Native_Name` varchar(75) default NULL, `CI_Name` varchar(150) default NULL, `activated` tinyint(1) NOT NULL default '0', KEY `639_3` (`ISO_639_3`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
'r_attachments' => "CREATE TABLE `r_attachments` ( `a_id` int(10) unsigned NOT NULL auto_increment, `content_id` int(10) unsigned NOT NULL, `media_id` int(10) unsigned NOT NULL, PRIMARY KEY (`a_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='relate file attachments to content'",
'r_fields' => "CREATE TABLE `r_fields` ( `rf_id` int(10) unsigned NOT NULL auto_increment, `scope` text NOT NULL, `rf_rel_id` int(10) unsigned default NULL, `rf_rel_ref_name` text, `field_id` mediumint(8) unsigned NOT NULL, `field_ref_name` text, `required` tinyint(1) NOT NULL default '0', `order` smallint(5) NOT NULL default '0', PRIMARY KEY (`rf_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='relate fields to content, user, media, or site for metadata'",
'r_list_vals' => "CREATE TABLE `r_list_vals` ( `val_id` mediumint(8) unsigned NOT NULL auto_increment, `list_id` int(10) NOT NULL, `list_ref_name` text NOT NULL, `value` text NOT NULL, `label` text NOT NULL, `lang_ISO_639_3` char(3) default NULL, PRIMARY KEY (`val_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='store list values and language info'",
'r_sites_users' => "CREATE TABLE `r_sites_users` ( `rel_id` int(10) unsigned NOT NULL auto_increment, `site_id` int(10) unsigned NOT NULL, `user_id` int(10) unsigned NOT NULL, `admin` tinyint(1) NOT NULL default '0' COMMENT 'is user administrator', PRIMARY KEY (`rel_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='relate users to sites'",
's_fields' => "CREATE TABLE `s_fields` ( `field_id` int(10) unsigned NOT NULL auto_increment, `field_ref_name` text NOT NULL, `field_input_type` varchar(9) NOT NULL, `field_validation` text, `field_needs_lang` tinyint(1) unsigned NOT NULL default '1', `list_id` int(10) unsigned NOT NULL, `field_allow_multiple` tinyint(1) unsigned NOT NULL default '0', PRIMARY KEY (`field_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
's_labels' => "CREATE TABLE `s_labels` ( `label_id` int(10) unsigned NOT NULL auto_increment, `scope` text NOT NULL, `rel_id` int(10) unsigned NOT NULL COMMENT 'primary id of related item', `rel_name` text COMMENT 'human-readable name of of related item', `label` text NOT NULL, `lang_ISO_639_3` char(3) NOT NULL default 'eng', PRIMARY KEY (`label_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='labels for fields, types'",
's_lists' => "CREATE TABLE `s_lists` ( `list_id` mediumint(8) unsigned NOT NULL auto_increment, `list_ref_name` text NOT NULL, PRIMARY KEY (`list_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='lists for field values'",
's_site_prefs' => "CREATE TABLE `s_site_prefs` ( `site_id` int(10) unsigned NOT NULL auto_increment, `theme` mediumtext, `timezone` varchar(100) NOT NULL default 'America/Vancouver' COMMENT 'default time zone for site', `default_lang` varchar(3) NOT NULL default 'eng', `default_content_type` int(10) unsigned NOT NULL default '1', `date_created` datetime NOT NULL, `date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, `home_content_id` int(10) unsigned NOT NULL default '0', `home_content_type` int(10) unsigned NOT NULL default '0', `site_admin_id` int(10) unsigned NOT NULL default '1', `site_editor_id` int(10) unsigned NOT NULL default '1', `img_size_th` smallint(5) unsigned NOT NULL default '120', `img_size_sm` smallint(5) unsigned NOT NULL default '255', `img_size_md` smallint(5) unsigned NOT NULL default '480', `img_size_lg` smallint(5) unsigned NOT NULL default '795', `limit_posts` smallint(5) unsigned NOT NULL default '5', `limit_thumbs` smallint(5) unsigned NOT NULL default '30', PRIMARY KEY (`site_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='list of sites'",
's_types' => "CREATE TABLE `s_types` ( `type_id` int(10) unsigned NOT NULL auto_increment, `scope` text NOT NULL, `type_uri_name` text NOT NULL, `index_home` tinyint(1) unsigned NOT NULL default '0', `index_rss` tinyint(1) unsigned NOT NULL default '1', `menu_children` tinyint(1) unsigned NOT NULL default '1', PRIMARY KEY (`type_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
's_users' => "CREATE TABLE `s_users` ( `user_id` int(10) unsigned NOT NULL auto_increment, `name_first` varchar(20) NOT NULL, `name_last` varchar(20) NOT NULL, `username` varchar(20) NOT NULL COMMENT 'username', `password` varchar(50) NOT NULL COMMENT 'hash of salt & password', `timezone` varchar(100) NOT NULL default 'America/Vancouver' COMMENT 'time zone for user', `default_lang` varchar(3) NOT NULL default 'eng', `email` text NOT NULL, PRIMARY KEY (`user_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='list of users on site'",
);

$config['tables_mysqli'] = array(
'c_content' => "",
'c_media' => "",
'c_metadata' => "",
'c_titles' => "",
'ISO_639_LANG' => "",
'r_attachments' => "",
'r_fields' => "",
'r_list_vals' => "",
'r_sites_users' => "",
's_fields' => "",
's_labels' => "",
's_lists' => "",
's_site_prefs' => "",
's_types' => "",
's_users' => "",
);
$config['tables_oci8'] = array(
'c_content' => "",
'c_media' => "",
'c_metadata' => "",
'c_titles' => "",
'ISO_639_LANG' => "",
'r_attachments' => "",
'r_fields' => "",
'r_list_vals' => "",
'r_sites_users' => "",
's_fields' => "",
's_labels' => "",
's_lists' => "",
's_site_prefs' => "",
's_types' => "",
's_users' => "",
);
$config['tables_odbc'] = array(
'c_content' => "",
'c_media' => "",
'c_metadata' => "",
'c_titles' => "",
'ISO_639_LANG' => "",
'r_attachments' => "",
'r_fields' => "",
'r_list_vals' => "",
'r_sites_users' => "",
's_fields' => "",
's_labels' => "",
's_lists' => "",
's_site_prefs' => "",
's_types' => "",
's_users' => "",
);
$config['tables_postgre'] = array(
'c_content' => "",
'c_media' => "",
'c_metadata' => "",
'c_titles' => "",
'ISO_639_LANG' => "",
'r_attachments' => "",
'r_fields' => "",
'r_list_vals' => "",
'r_sites_users' => "",
's_fields' => "",
's_labels' => "",
's_lists' => "",
's_site_prefs' => "",
's_types' => "",
's_users' => "",
);
$config['tables_sqlite'] = array(
'c_content' => "",
'c_media' => "",
'c_metadata' => "",
'c_titles' => "",
'ISO_639_LANG' => "",
'r_attachments' => "",
'r_fields' => "",
'r_list_vals' => "",
'r_sites_users' => "",
's_fields' => "",
's_labels' => "",
's_lists' => "",
's_site_prefs' => "",
's_types' => "",
's_users' => "",
);

$config['default_data'] = array(
'r_fields' => "INSERT INTO `r_fields` (`scope`,`rf_rel_id`,`rf_rel_ref_name`,`field_id`,`field_ref_name`,`required`,`order`) VALUES ('site',1,'',5,'copyright',0,3), ('site',1,'',8,'description',1,1), ('types',1,'static_page',2,'body',1,1), ('types',1,'static_page',8,'description',0,2), ('types',2,'blog_entry',11,'excerpt',0,2), ('types',2,'blog_entry',2,'body',1,0), ('types',2,'blog_entry',2,'body',1,1), ('types',3,'blog',8,'description',1,2), ('types',4,'gallery',13,'image_poster',0,3), ('types',4,'gallery',8,'description',1,2), ('types',4,'gallery',9,'directory',1,1), ('types',5,'application',8,'description',0,1), ('types',6,'video',8,'description',0,0), ('types',8,'image',15,'location',0,0), ('types',8,'image',16,'province_state',0,0), ('types',8,'image',5,'copyright',0,0), ('types',8,'image',7,'creator',0,0), ('types',8,'image',8,'description',1,0), ('types',9,'text',8,'description',0,0), ('users',1,'',1,'biography',0,1), ('users',1,'',14,'website_url',0,3)",
'r_list_vals' => "INSERT INTO `r_list_vals` (`list_id`,`list_ref_name`,`value`,`label`,`lang_ISO_639_3`) VALUES (1,'canada-province','AB','Alberta','eng'), (1,'canada-province','AB','Alberta','fra'), (1,'canada-province','BC','British Columbia','eng'), (1,'canada-province','BC','Colombie-Britannique','fra'), (1,'canada-province','MB','Manitoba','eng'), (1,'canada-province','MB','Manitoba','fra'), (1,'canada-province','NB','New Brunswick','eng'), (1,'canada-province','NB','Nouveau-Brunswick','fra'), (1,'canada-province','NL','Newfoundland and Labrador','eng'), (1,'canada-province','NL','Terre-Neuve-et-Labrador','fra'), (1,'canada-province','NS','Nova Scotia','eng'), (1,'canada-province','NS','Nouvelle-Écosse','fra'), (1,'canada-province','NT','Northwest Territories','eng'), (1,'canada-province','NT','Territoires du Nord-Ouest','fra'), (1,'canada-province','NU','Nunavut','eng'), (1,'canada-province','NU','Nunavut','fra'), (1,'canada-province','ON','Ontario','eng'), (1,'canada-province','ON','Ontario','fra'), (1,'canada-province','PE','Prince Edward Island','eng'), (1,'canada-province','PE','Île-du-Prince-Édouard','fra'), (1,'canada-province','QC','Québec','eng'), (1,'canada-province','QC','Québec','fra'), (1,'canada-province','SK','Saskatchewan','eng'), (1,'canada-province','SK','Saskatchewan','fra'), (1,'canada-province','YT','Yukon','eng'), (1,'canada-province','YT','Yukon','fra'), (2,'us-states','AL','Alabama','eng'), (2,'us-states','AK','Alaska','eng'), (2,'us-states','AZ','Arizona ','eng'), (2,'us-states','AR','Arkansas','eng'), (2,'us-states','CA','California ','eng'), (2,'us-states','CO','Colorado ','eng'), (2,'us-states','CT','Connecticut','eng'), (2,'us-states','DE','Delaware','eng'), (2,'us-states','DC','District of Columbia','eng'), (2,'us-states','FL','Florida','eng'), (2,'us-states','GA','Georgia','eng'), (2,'us-states','HI','Hawaii','eng'), (2,'us-states','ID','Idaho','eng'), (2,'us-states','IL','Illinois','eng'), (2,'us-states','IN','Indiana','eng'), (2,'us-states','IA','Iowa','eng'), (2,'us-states','KS','Kansas','eng'), (2,'us-states','KY','Kentucky','eng'), (2,'us-states','LA','Louisiana','eng'), (2,'us-states','ME','Maine','eng'), (2,'us-states','MD','Maryland','eng'), (2,'us-states','MA','Massachusetts','eng'), (2,'us-states','MI','Michigan','eng'), (2,'us-states','MN','Minnesota','eng'), (2,'us-states','MS','Mississippi','eng'), (2,'us-states','MO','Missouri','eng'), (2,'us-states','MT','Montana','eng'), (2,'us-states','NE','Nebraska','eng'), (2,'us-states','NV','Nevada','eng'), (2,'us-states','NH','New Hampshire','eng'), (2,'us-states','NJ','New Jersey','eng'), (2,'us-states','NM','New Mexico','eng'), (2,'us-states','NY','New York','eng'), (2,'us-states','NC','North Carolina','eng'), (2,'us-states','ND','North Dakota','eng'), (2,'us-states','OH','Ohio','eng'), (2,'us-states','OK','Oklahoma','eng'), (2,'us-states','OR','Oregon','eng'), (2,'us-states','PA','Pennsylvania','eng'), (2,'us-states','RI','Rhode Island','eng'), (2,'us-states','SC','South Carolina','eng'), (2,'us-states','SD','South Dakota','eng'), (2,'us-states','TN','Tennessee','eng'), (2,'us-states','TX','Texas','eng'), (2,'us-states','UT','Utah','eng'), (2,'us-states','VT','Vermont','eng'), (2,'us-states','VA','Virginia ','eng'), (2,'us-states','WA','Washington','eng'), (2,'us-states','WV','West Virginia','eng'), (2,'us-states','WI','Wisconsin','eng'), (2,'us-states','WY','Wyoming','eng'), (2,'us-states','AL','Alabama','fra'), (2,'us-states','AK','Alaska','fra'), (2,'us-states','AZ','Arizona','fra'), (2,'us-states','AR','Arkansas','fra'), (2,'us-states','CA','Californie','fra'), (2,'us-states','CO','Colorado','fra'), (2,'us-states','CT','Connecticut','fra'), (2,'us-states','DE','Delaware','fra'), (2,'us-states','DC','District de Columbia','fra'), (2,'us-states','FL','Floride','fra'), (2,'us-states','GA','Géorgie','fra'), (2,'us-states','HI','Hawaï','fra'), (2,'us-states','ID','Idaho','fra'), (2,'us-states','IL','Illinois','fra'), (2,'us-states','IN','Indiana','fra'), (2,'us-states','IA','Iowa','fra'), (2,'us-states','KS','Kansas','fra'), (2,'us-states','KY','Kentucky','fra'), (2,'us-states','LA','Louisiane','fra'), (2,'us-states','ME','Maine','fra'), (2,'us-states','MD','Maryland','fra'), (2,'us-states','MA','Massachusetts','fra'), (2,'us-states','MI','Michigan','fra'), (2,'us-states','MN','Minnesota','fra'), (2,'us-states','MS','Mississippi','fra'), (2,'us-states','MO','Missouri','fra'), (2,'us-states','MT','Montana','fra'), (2,'us-states','NE','Nebraska','fra'), (2,'us-states','NV','Nevada','fra'), (2,'us-states','NH','New Hampshire','fra'), (2,'us-states','NJ','New Jersey','fra'), (2,'us-states','NM','Nouveau-Mexique','fra'), (2,'us-states','NY','New York','fra'), (2,'us-states','NC','Caroline du Nord','fra'), (2,'us-states','ND','Dakota du Nord','fra'), (2,'us-states','OH','Ohio','fra'), (2,'us-states','OK','Oklahoma','fra'), (2,'us-states','OR','Oregon','fra'), (2,'us-states','PA','Pennsylvanie','fra'), (2,'us-states','RI','Rhode Island','fra'), (2,'us-states','SC','Caroline du Sud','fra'), (2,'us-states','SD','Dakota du Sud','fra'), (2,'us-states','TN','Tennessee','fra'), (2,'us-states','TX','Texas','fra'), (2,'us-states','UT','Utah','fra'), (2,'us-states','VT','Vermont','fra'), (2,'us-states','VA','Virginie','fra'), (2,'us-states','WA','Washington','fra'), (2,'us-states','WV','Virginie-Occidentale','fra'), (2,'us-states','WI','Wisconsin','fra'), (2,'us-states','WY','Wyoming','fra')",
's_fields' => "INSERT INTO `s_fields` (`field_ref_name`,`field_input_type`,`field_validation`,`field_needs_lang`,`list_id`,`field_allow_multiple`) VALUES ('biography','richtext','',1,0,0), ('body','richtext','',1,0,0), ('city','text','',1,0,0), ('contributor','text','',1,0,0), ('copyright','text','',1,0,0), ('country','text','',1,0,0), ('creator','text','',1,0,0), ('description','textarea','',1,0,0), ('directory','directory','path_dir',0,0,0), ('e-mail','text','valid_email ',0,0,0), ('excerpt','textarea','max_length[512]',1,0,0), ('image_banner','file','path_file',0,0,0), ('image_poster','file','is_natural',0,0,0), ('location','text','',1,0,0), ('province_canada','list','',0,1,0), ('transcript','richtext','',1,0,0), ('website_url','text','is_uri',0,0,0)",
's_labels' => "INSERT INTO `s_labels` (`label_id`,`scope`,`rel_id`,`rel_name`,`label`,`lang_ISO_639_3`) VALUES (1,'field',1,'biography',CONVERT(_latin1'biographie' USING utf8),'fra'), (2,'field',1,'biography','Biography','eng'), (3,'field',2,'body','Body','eng'), (4,'field',3,'city','City','eng'), (5,'field',4,'contributor','Contributor','eng'), (6,'field',5,'copyright','Copyright','eng'), (7,'field',6,'country','Country','eng'), (8,'field',7,'creator','Creator','eng'), (9,'field',8,'description','Description','eng'), (10,'field',9,'directory','Directory','eng'), (11,'field',10,'e-mail','E-mail','eng'), (12,'field',11,'excerpt','Excerpt','eng'), (13,'field',12,'image_banner','Image Banner','eng'), (14,'field',13,'image_poster','Image Poster','eng'), (15,'field',14,'location','Location','eng'), (16,'field',15,'province_canada','Province (Canada)','eng'), (17,'field',16,'transcript','Transcript','eng'), (18,'field',17,'website_url','Website URL','eng'), (19,'list',1,'canada-province','Province (Canada)','eng'), (20,'list',1,'canada-province','Province (Canada)','fra'), (21,'list',2,'us-states',CONVERT(_latin1'État (États-Unis)' USING utf8),'fra'), (22,'list',2,'us-states','State (USA)','eng'), (23,'types',1,'static_page','Contenu statique','fra'), (24,'types',1,'static_page','Static Page','eng'), (25,'types',2,'blog_entry','Blog Entry','eng'), (26,'types',2,'blog_entry','Billet de blogue','fra'), (27,'types',3,'blog','Blog','eng'), (28,'types',3,'blog','blogue','fra'), (29,'types',4,'gallery','Gallery','eng'), (30,'types',4,'gallery','galerie','fra'), (31,'types',5,'application','application (autres types de fichiers)','fra'), (32,'types',5,'application','application (other file types)','eng'), (33,'types',6,'video','video','eng'), (34,'types',6,'video',CONVERT(_latin1'vidéo' USING utf8),'fra'), (35,'types',8,'image','image','eng'), (36,'types',8,'image','image','fra'), (37,'types',9,'text','text','eng'), (38,'types',9,'text','texte','fra')",
's_lists' => "INSERT INTO `s_lists` (`list_id`,`list_ref_name`) VALUES (1,'canada-province'),(2,'us-states')",
's_types' => "INSERT INTO `s_types` (`type_id`,`scope`,`type_uri_name`,`index_home`,`index_rss`,`menu_children`) VALUES (1,'content','static_page',0,1,1), (2,'content','blog_entry',1,1,1), (3,'content','blog',0,0,0), (4,'content','gallery',0,0,0), (5,'media','application',1,1,1), (6,'media','video',1,1,1), (7,'media','audio',1,1,1), (8,'media','image',1,1,1), (9,'media','text',1,1,1)"
);
