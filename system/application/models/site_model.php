<?php
class Site_model extends MY_Model {

	// list variables
	var $site_meta_fields;
	var $site_themes;
	
	var $site_id, $site_timezone, $site_admin_id, $theme, $site_default_lang, $site_date_created, $site_date_updated, $home_content_id;
	var $img_size_th, $img_size_sm, $img_size_md, $img_size_lg;
	var $limit_posts, $limit_thumbs;
	
	// title variables
	var $site_title_id, $title, $subtitle, $site_title_updated;

	// site metadata 
	var $site_current_lang, $site_metadata;

    function Site_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->config->load('phsite_prefs');
		$this->site_id = $this->config->item('site_id');
		$this->load_site();
    }

    // load the site data
	function load_site()
    {
		$query = $this->db->get_where('sites', array('site_id' => $this->site_id));

		if($row = $query->row_array()) {
			$this->site_timezone = $row['timezone'];
			$this->site_admin_id = $row['site_admin_id'];
			$this->theme = $row['theme'];
			$this->site_default_lang = $row['default_lang'];
			$this->site_date_created = $row['date_created'];
			$this->site_date_updated = $row['date_updated'];
			$this->home_content_id = $row['home_content_id'];
			$this->img_size_th = $row['img_size_th'];
			$this->img_size_sm = $row['img_size_sm'];
			$this->img_size_md = $row['img_size_md'];
			$this->img_size_lg = $row['img_size_lg'];
			$this->limit_posts = $row['limit_posts'];
			$this->limit_thumbs = $row['limit_thumbs'];
		}
    }

	function load_site_meta()
    {

		$this->db->join('fields', 'site_meta.field_id = fields.field_id', 'left');
		$this->db->where('site_id', $this->site_id);

		if($this->site_meta_id != '') {
			$this->db->where('site_meta_id', $this->site_meta_id);
		}
		if($this->site_current_lang != '') {
			$this->db->where('lang_ISO_639_3', $this->site_current_lang);
		} else {
			$this->db->where('lang_ISO_639_3',$this->site_default_lang);
		}

		$query = $this->db->get('site_meta');
		$this->site_metadata = $query->result_array();

    }

	function load_site_title()
    {
		$this->db->where('site_id', $this->site_id);
		if($this->site_title_id != '') {
			$this->db->where('site_title_id', $this->site_title_id);
		}
		if($this->site_current_lang != '') {
			$this->db->where('lang_ISO_639_3', $this->site_current_lang);
		} else {
			$this->db->where('lang_ISO_639_3',$this->site_default_lang);
		}
		$this->db->limit(1);
		$query = $this->db->get('site_titles');


    }

/*

	// site metadata 
	var $site_current_lang, $site_metadata;
	// title variables
	var $site_title_id, $title, $subtitle, $site_title_updated;


CREATE TABLE `site_titles` (
  `site_title_id` mediumint(9) unsigned NOT NULL auto_increment,
  `site_id` mediumint(9) unsigned NOT NULL,
  `title` mediumtext NOT NULL,
  `subtitle` mediumtext,
  `lang_ISO_639_3` varchar(3) NOT NULL,
  `date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`site_title_id`),
  FULLTEXT KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
*/


	function update_site()
    {
		$db_fields = array(
		'site_admin_id' => $this->site_admin_id,
		'theme' => $this->theme,
		'timezone' => $this->site_timezone,
		'default_lang' => $this->site_default_lang,
		'date_created' => $this->site_date_created,
		'home_content_id' => $this->home_content_id,
		'img_size_th' => $this->img_size_th,
		'img_size_sm' => $this->img_size_sm,
		'img_size_md' => $this->img_size_md,
		'img_size_lg' => $this->img_size_lg,
		'limit_posts' => $this->limit_posts,
		'limit_thumbs' => $this->limit_thumbs,
		);

		if($this->site_id == '')
		{
			// create new site
			if($this->db->insert('sites', $db_fields)) {
				$this->site_id = $this->db->insert_id();
				return TRUE;
			}

		} else {
			// update site
			if( $this->db->update('sites', $db_fields, array('site_id' => $this->site_id)) ) {
				return TRUE;
			}
		}
		return FALSE;

    }
	// end update_site()

    // get metadata fields
	function load_meta_fields()
    {

		$this->db->join('fields', 'r_sites_fields.field_id = fields.field_id', 'left');
		$this->db->where('site_id', $this->site_id);
		$this->db->order_by("order", "asc");
		$query = $this->db->get('r_sites_fields');

		$this->site_meta_fields = $query->result_array();

		
    }

}

?>
