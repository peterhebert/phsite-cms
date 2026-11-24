<?php
class Site_model extends MY_Model {

	// list variables
	var $site_themes, $theme_info;
	
	// site variables
	var $site_timezone, $theme, $site_default_lang, $site_date_created, $site_date_updated, $home_content_id, $home_content_type;
	var $default_content_type;
	var $site_admin_id, $site_editor_id;
	var $img_size_th, $img_size_sm, $img_size_md, $img_size_lg;
	var $limit_posts, $limit_thumbs;

    function Site_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->site_id = $this->config->item('site_id');
		$this->load_site();
		$this->load_theme_info();
    }

    // load the site data
	function load_site()
    {
		$query = $this->db->get_where('s_site_prefs', array('site_id' => $this->site_id));

		if($row = $query->row_array()) {
			$this->site_timezone = $row['timezone'];
			$this->site_admin_id = $row['site_admin_id'];
			$this->site_editor_id = $row['site_editor_id'];
			$this->theme = $row['theme'];
			$this->site_default_lang = $row['default_lang'];
			$this->default_content_type = $row['default_content_type'];
			$this->site_date_created = $row['date_created'];
			$this->site_date_updated = $row['date_updated'];
			$this->home_content_id = $row['home_content_id'];
			$this->home_content_type = $row['home_content_type'];
			$this->img_size_th = $row['img_size_th'];
			$this->img_size_sm = $row['img_size_sm'];
			$this->img_size_md = $row['img_size_md'];
			$this->img_size_lg = $row['img_size_lg'];
			$this->limit_posts = $row['limit_posts'];
			$this->limit_thumbs = $row['limit_thumbs'];
		}
    }

	function load_theme_info()
    {
		if($this->temp_lang == '')
		{
			$this->temp_lang = $this->site_default_lang;
		}
		$this->lang_load();
		
		$theme_info_file = 'system/application/views/themes/'.$this->theme.'/_theme_info/'.$this->lang_CI_Name.'.txt';

		$theme_info = array();
		$test = file($theme_info_file);
		foreach($test as $line)
		{
			$temp = preg_replace("#:([\t]+)#","::",$line);
			$vals = explode("::",$temp);
			$theme_info[$vals[0]] = $vals[1];
		}
		
		$this->theme_info = $theme_info;
    }

	// get list of themes
	function list_themes()
    {

		if($this->temp_lang == '')
		{
			$this->temp_lang = $this->site_default_lang;
		}
		$this->lang_load();
		
		$theme_folder = ROOT_REL.'/system/application/views/themes';
		$filepath = $theme_folder;

		$theme_list = array();
		$dirs = array();
		
		$dir_handle = @opendir($filepath);
		if($dir_handle)
		{
			//running the while loop
			while ($file = readdir($dir_handle)) {

				$newpath = $filepath.'/'.$file;
				$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
				if(!in_array($file,$ignore) && is_dir($newpath)) {
					$theme_uri = basename($newpath);
					$dirs[] = $newpath;
					$theme_info_file = $newpath.'/_theme_info/'.$this->lang_CI_Name.'.txt';

					if(file_exists($theme_info_file))
					{
						$theme_info = array();
						$readfile = file($theme_info_file);
						foreach($readfile as $line)
						{
							$temp = preg_replace("#:([\t]+)#","::",$line);
							$vals = explode("::",$temp);
							$theme_info[$vals[0]] = $vals[1];
						}
						$theme_list[$theme_uri] = $theme_info;
						unset($theme_info);
					}
				}
			}
			// closing the directory
			closedir($dir_handle);
		}

		$this->site_themes = $theme_list;
		
    }

	function load_site_metadata()
    {

		$this->temp_id = $this->site_id;
		$this->scope = 'site';
		$this->list_fields_related();
		
		if($this->temp_lang == '')
		{
			$this->temp_lang = $this->site_default_lang;
		}

		$this->load_metadata();
	}

    function list_site_meta_fields()
    {

		$this->list_fields();
		$list = $this->field_list;
		$this->scope = 'site';
		
		$field_info = array();
		
		foreach ($list as $fd)
		{
			$temp_field = array(
			'field_id' => $fd['field_id'],
			'field_ref_name' => $fd['field_ref_name'],
			'field_input_type' => $fd['field_input_type'],
			'field_validation' => $fd['field_validation'],
			'field_needs_lang' => $fd['field_needs_lang'],
			'rf_id' => '',
			'required' => 0,
			'sort_order' => 0
			);
	
			$this->db->where('scope',$this->scope);
			$this->db->where('field_id',$fd['field_id']);
			$query = $this->db->get('r_fields');

			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$temp_field['rf_id'] = $row->rf_id;
				$temp_field['required'] = $row->required;
				$temp_field['sort_order'] = $row->sort_order;
			}
			$field_info[] = $temp_field;
			 
		}

		$this->field_list = $field_info;

	}

	function update_site()
    {
		$db_fields = array(
		'theme' => $this->theme,
		'timezone' => $this->site_timezone,
		'default_lang' => $this->site_default_lang,
		'default_content_type' => $this->default_content_type,
		'date_created' => $this->site_date_created,
		'home_content_id' => $this->home_content_id,
		'home_content_type' => $this->home_content_type,
		'site_admin_id' => $this->site_admin_id,
		'site_editor_id' => $this->site_editor_id,
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
			if($this->db->insert('s_site_prefs', $db_fields)) {
				$this->site_id = $this->db->insert_id();
				return TRUE;
			}

		} else {
			// update site
			if( $this->db->update('s_site_prefs', $db_fields, array('site_id' => $this->site_id)) ) {
				return TRUE;
			}
		}
		return FALSE;

    }
	// end update_site()

}

?>
