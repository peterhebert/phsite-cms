DROP TABLE IF EXISTS `c_content`;

CREATE TABLE `c_content` (
`content_id` mediumint(9) unsigned NOT NULL auto_increment,
`user_id` mediumint(9) unsigned NOT NULL default '1',
`parent_id` mediumint(9) NOT NULL default '0' COMMENT '0 is root, -1 is non-hierarchical',
`type_id` mediumint(9) unsigned NOT NULL default '1',
`type_uri_name` text,
`date_created` datetime NOT NULL,
`date_end_expiry` datetime default NULL,
`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
`publish` mediumint(9) unsigned NOT NULL default '1',
PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `c_media`;

CREATE TABLE `c_media` (
`media_id` mediumint(9) unsigned NOT NULL auto_increment,
`file_uri` text,
`external` tinyint(1) NOT NULL default '0',
`mime_type` varchar(100) default 'image/jpeg',
`date_created` datetime NOT NULL,
`publish` tinyint(1) NOT NULL default '0',
PRIMARY KEY  (`media_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='media file info';

DROP TABLE IF EXISTS `c_metadata`;

CREATE TABLE `c_metadata` (
`meta_id` mediumint(9) unsigned NOT NULL auto_increment,
`scope` text NOT NULL COMMENT 'metadata for content, user or site',
`rel_id` mediumint(9) unsigned NOT NULL,
`field_id` mediumint(9) unsigned NOT NULL,
`field_ref_name` text NOT NULL,
`value` mediumtext NOT NULL,
`lang_ISO_639_3` varchar(3) default NULL,
`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
PRIMARY KEY  (`meta_id`),
FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `c_titles`;

CREATE TABLE `c_titles` (
`title_id` mediumint(9) unsigned NOT NULL auto_increment,
`scope` text NOT NULL COMMENT 'title is for content, media or site',
`rel_id` mediumint(9) unsigned NOT NULL COMMENT 'the id of the (content,media,site) being described',
`title` text NOT NULL,
`uri_title` text NOT NULL,
`subtitle` text,
`keywords` text,
`lang_ISO_639_3` varchar(3) NOT NULL,
`title_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
PRIMARY KEY  (`title_id`),
FULLTEXT KEY `title` (`title`,`subtitle`),
FULLTEXT KEY `keywords` (`keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ISO_639_LANG`;

CREATE TABLE `ISO_639_LANG` (
`ISO_639_3` char(3) NOT NULL,
`ISO_639_2B` char(3) default NULL,
`ISO_639_2T` char(3) default NULL,
`ISO_639_1` char(2) default NULL,
`Scope` char(1) NOT NULL COMMENT 'I(ndividual), M(acrolanguage), S(pecial)',
`Type` char(1) NOT NULL COMMENT 'A(ncient), C(onstructed), E(xtinct), H(istorical), L(iving), S(pecial)',
`Ref_Name` varchar(150) NOT NULL COMMENT 'Reference language name in English',
`Inverted_Name` varchar(75) NOT NULL COMMENT 'inverted version of Ref_name for alpha sorting',
`Native_Name` varchar(75) default NULL,
`CI_Name` varchar(150) default NULL,
`activated` tinyint(1) NOT NULL default '0',
KEY `639_3` (`ISO_639_3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `r_attachments`;

CREATE TABLE `r_attachments` (
`a_id` mediumint(9) unsigned NOT NULL auto_increment,
`content_id` mediumint(9) unsigned NOT NULL,
`media_id` mediumint(9) unsigned NOT NULL,
PRIMARY KEY  (`a_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='relate file attachments to content';

DROP TABLE IF EXISTS `r_fields`;

CREATE TABLE `r_fields` (
`rf_id` mediumint(9) unsigned NOT NULL auto_increment,
`scope` text NOT NULL,
`rf_rel_id` mediumint(9) unsigned default NULL,
`rf_rel_ref_name` text,
`field_id` mediumint(8) unsigned NOT NULL,
`field_ref_name` text,
`required` tinyint(1) NOT NULL default '0',
`order` smallint(5) NOT NULL default '0',
PRIMARY KEY  (`rf_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='relate fields to content, user, media, or site for metadata';

DROP TABLE IF EXISTS `r_list_vals`;

CREATE TABLE `r_list_vals` (
`val_id` mediumint(8) unsigned NOT NULL auto_increment,
`list_id` mediumint(9) NOT NULL,
`list_ref_name` text NOT NULL,
`value` text NOT NULL,
`label` text NOT NULL,
`lang_ISO_639_3` char(3) default NULL,
PRIMARY KEY  (`val_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='store list values and language info';

DROP TABLE IF EXISTS `r_sites_users`;

CREATE TABLE `r_sites_users` (
`rel_id` mediumint(9) unsigned NOT NULL auto_increment,
`site_id` mediumint(9) unsigned NOT NULL,
`user_id` mediumint(9) unsigned NOT NULL,
`admin` tinyint(1) NOT NULL default '0' COMMENT 'is user administrator',
PRIMARY KEY  (`rel_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='relate users to sites';

DROP TABLE IF EXISTS `s_fields`;

CREATE TABLE `s_fields` (
`field_id` mediumint(9) unsigned NOT NULL auto_increment,
`field_ref_name` text NOT NULL,
`field_input_type` varchar(9) NOT NULL,
`field_validation` text,
`field_needs_lang` tinyint(1) unsigned NOT NULL default '1',
`list_id` mediumint(9) unsigned NOT NULL,
`field_allow_multiple` tinyint(1) unsigned NOT NULL default '0',
PRIMARY KEY  (`field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `s_labels`;

CREATE TABLE `s_labels` (
`label_id` mediumint(9) unsigned NOT NULL auto_increment,
`scope` text NOT NULL,
`rel_id` mediumint(9) unsigned NOT NULL COMMENT 'primary id of related item',
`rel_name` text COMMENT 'human-readable name of of related item',
`label` text NOT NULL,
`lang_ISO_639_3` char(3) NOT NULL default 'eng',
PRIMARY KEY  (`label_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='labels for fields, types';

DROP TABLE IF EXISTS `s_lists`;

CREATE TABLE `s_lists` (
`list_id` mediumint(8) unsigned NOT NULL auto_increment,
`list_ref_name` text NOT NULL,
PRIMARY KEY  (`list_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='lists for field values';

DROP TABLE IF EXISTS `s_site_prefs`;

CREATE TABLE `s_site_prefs` (
`site_id` mediumint(9) unsigned NOT NULL auto_increment,
`theme` mediumtext,
`timezone` varchar(100) NOT NULL default 'America/Vancouver' COMMENT 'default time zone for site',
`default_lang` varchar(3) NOT NULL default 'eng',
`default_content_type` mediumint(9) unsigned NOT NULL default '1',
`date_created` datetime NOT NULL,
`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
`home_content_id` mediumint(9) unsigned NOT NULL default '0',
`home_content_type` mediumint(9) unsigned NOT NULL default '0',
`site_admin_id` mediumint(9) unsigned NOT NULL default '1',
`site_editor_id` mediumint(9) unsigned NOT NULL default '1',
`img_size_th` smallint(5) unsigned NOT NULL default '120',
`img_size_sm` smallint(5) unsigned NOT NULL default '255',
`img_size_md` smallint(5) unsigned NOT NULL default '480',
`img_size_lg` smallint(5) unsigned NOT NULL default '795',
`limit_posts` smallint(5) unsigned NOT NULL default '5',
`limit_thumbs` smallint(5) unsigned NOT NULL default '30',
PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='list of sites';

DROP TABLE IF EXISTS `s_types`;

CREATE TABLE `s_types` (
`type_id` mediumint(9) unsigned NOT NULL auto_increment,
`scope` text NOT NULL,
`type_uri_name` text NOT NULL,
PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `s_users`;

CREATE TABLE `s_users` (
`user_id` mediumint(9) unsigned NOT NULL auto_increment,
`name_first` varchar(20) NOT NULL,
`name_last` varchar(20) NOT NULL,
`username` varchar(20) NOT NULL COMMENT 'username',
`password` varchar(50) NOT NULL COMMENT 'hash of salt & password',
`timezone` varchar(100) NOT NULL default 'America/Vancouver' COMMENT 'time zone for user',
`default_lang` varchar(3) NOT NULL default 'eng',
`email` text NOT NULL,
PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='list of users on site';
