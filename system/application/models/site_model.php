<?php
class Site_model extends MY_Model {

	// list variables
	var $site_meta_fields;
	var $site_themes;
	
	var $site_id, $site_timezone, $theme, $site_default_lang, $site_date_created, $site_date_updated, $home_content_id;
	var $default_content_type;
	var $site_admin_id, $site_editor_id;
	var $img_size_th, $img_size_sm, $img_size_md, $img_size_lg;
	var $limit_posts, $limit_thumbs;
	// title variables
	var $site_title_id, $title, $subtitle, $site_title_updated;

	// site metadata 
	var $site_meta_lang, $site_metadata;

    function Site_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->config->load('phsite_prefs');
		$this->site_id = $this->config->item('site_id');
		$this->load_site();
    }

    // load the site data
	function reset_meta()
    {
		$this->site_title_id = '';
		$this->title = '';
		$this->subtitle = '';
		$this->site_title_updated = '';
		$this->site_meta_lang = '';
		$this->site_metadata = array();
		$this->site_meta_fields = array();
	}
	
    // load the site data
	function load_site()
    {
		$query = $this->db->get_where('sites', array('site_id' => $this->site_id));

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
		$this->load_meta_fields();

		$site_meta_temp = $this->site_meta_fields;

		$this->db->join('fields', 'site_meta.field_id = fields.field_id', 'left');
		//$this->db->where('site_id', $this->site_id);
		
		if($this->site_meta_lang != '')
		{
			$where = "site_id = {$this->site_id} AND (lang_ISO_639_3 = '{$this->site_meta_lang}' OR lang_ISO_639_3 = '')";
		}
		else
		{
			$where = "site_id = {$this->site_id} AND lang_ISO_639_3 = ''";
		}

		$this->db->where($where);

		$query = $this->db->get('site_meta');
		
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
			  $site_meta_temp["{$row->field_id}"]['site_meta_id'] = $row->site_meta_id;
			  $site_meta_temp["{$row->field_id}"]['value'] = $row->value;
		   }
		}
				
		$this->site_metadata = $site_meta_temp;

    }

	function load_site_title()
    {

		$this->db->where('site_id', $this->site_id);
		$this->db->where('lang_ISO_639_3', $this->site_meta_lang);
		if($this->site_title_id != '') {
			$this->db->where('site_title_id', $this->site_title_id);
		}

		$this->db->limit(1);
		$query = $this->db->get('site_titles');

		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		   
		   $this->site_title_id = $row->site_title_id;
		   $this->title = $row->title;
		   $this->subtitle = $row->subtitle;
		   $this->site_title_updated = $row->date_updated;
		}

    }

	function update_site()
    {
		$db_fields = array(
		'site_admin_id' => $this->site_admin_id,
		'site_editor_id' => $this->site_editor_id,
		'theme' => $this->theme,
		'timezone' => $this->site_timezone,
		'default_lang' => $this->site_default_lang,
		'default_content_type' => $this->default_content_type,
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

    // update title
    function update_title()
    {

		// try to find existing title id
		if($this->site_title_id == '') {

			$title_query = $this->db->get_where('site_titles', array('site_id' => $this->site_id, 'lang_ISO_639_3' => $this->site_meta_lang));
			if ($title_query->num_rows() > 0)
			{
			   $trow = $title_query->row();
			   $this->site_title_id = $trow->site_title_id;
			} 

		}

		$data = array(
		'site_id' => $this->site_id,
		'title' => $this->title,
		'subtitle' => $this->subtitle,
		'lang_ISO_639_3' => $this->site_meta_lang
		);

		// update or insert title
		if($this->site_title_id == '') {
			// insert
			if($this->db->insert('site_titles', $data)){
				$this->site_title_id = $this->db->insert_id();
				return TRUE;
			}
			
		} else {
			// update
			$this->db->where('site_title_id', $this->site_title_id);
			if($this->db->update('site_titles', $data)){
				return TRUE;
			}

		}
		return FALSE;

    }



	function update_meta()
    {

		foreach($this->site_metadata as $meta)
		{

			$db_fields = array(
			'site_id' => $this->site_id,
			'field_id' => $meta['field_id'],
			'field_name' => $meta['field_ref_name'],
			'value' => $meta['value'],
			'lang_ISO_639_3' => '',
			);

			if($meta['field_needs_lang'] = 1)
			{
				$data['lang_ISO_639_3'] = $this->site_meta_lang;
			}
	
			if($meta['site_meta_id'] == '')
			{
				if($meta['required'] == 1 || $meta['value'] != '')
				{
					// create new metadata
					if(!$this->db->insert('site_meta', $db_fields) )
					{
						$this->messages[] = "could not insert metadata '{$meta['field_name']}'";
					}
				}
	
			}
			else
			{

				if($meta['required'] == 1 || $meta['value'] != '')
				{
					// update metadata
					if(!$this->db->update('site_meta', $db_fields, array('site_meta_id' => $meta['site_meta_id'])) ) {
						$this->messages[] = "could not update metadata '{$meta['field_name']}'";
					}
				}
				else
				{
					// delete metadata
					if(!$this->db->delete('site_meta', array('site_meta_id' => $meta['site_meta_id'])) ) {
						$this->messages[] = "could not delete metadata '{$meta['field_name']}'";
					}
				}

			}
		
		}


    } // end update_meta()

    // get metadata fields
	function load_meta_fields()
    {
		$temp_meta_fields = array();
		
		$this->db->where('site_id', $this->site_id);
		$this->db->join('fields', 'r_sites_fields.field_id = fields.field_id', 'left');
		$this->db->order_by("order", "asc");

		$query = $this->db->get('r_sites_fields');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$temp_field = array(
				'field_id' => $row['field_id'],
				'field_ref_name' => $row['field_ref_name'],
				'field_input_type' => $row['field_input_type'],
				'field_validation' => $row['field_validation'],
				'field_needs_lang' => $row['field_needs_lang'],
				'site_rel_id' => $row['rel_id'],
				'required' => $row['required'],
				'order' => $row['order'],
				'site_meta_id' => '',
				'value' => '',
				'field_name' => ''
				);

				//now get names
				$this->db->where('field_id', $row['field_id']);
				$this->db->where('lang_ISO_639_3', $this->site_meta_lang);
				$this->db->limit(1);
				$query2 = $this->db->get('fields_meta');
				
				if ($query2->num_rows() > 0)
				{
				   $row2 = $query2->row(); 
				
				   $temp_field['field_name'] = $row2->field_name;
				}
		
				$temp_meta_fields["{$row['field_id']}"] = $temp_field;
			}
		}

		$this->site_meta_fields = $temp_meta_fields;

    }

	// get list of themes
	function list_themes()
    {

		$themeArray = array();
		// get document directory of index.php
		$theme_dir = $this->install_path.'system/application/views/themes';

		if (is_dir($theme_dir)) {

			if ($dh = opendir($theme_dir)) {
				while (($file = readdir($dh)) !== false) {
					if($file != '.svn' && $file != '.' && $file != '..' && is_dir($theme_dir.'/'.$file)) {
						$themeArray[$file] = str_replace('_',' ',$file);
					}
				}
				closedir($dh);
			}
		}
		asort($themeArray);

		$this->site_themes = $themeArray;
    }

    // list the available fields and see if they are assigned as site maetadata
	function list_fields()
    {

		$list = array();
		$this->db->order_by('field_ref_name', 'asc');
		$query = $this->db->get('fields');

		foreach ($query->result() as $row)
		{

			$temp_field =  array(
			'field_id' => $row->field_id,
			'field_ref_name' => $row->field_ref_name,
			'field_name' => '',
			'rel_id' => '',
			'required' => '',
			'order' => ''
			);

			// get name if exists
			$this->db->where('field_id', $row->field_id);
			if($this->site_meta_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->site_meta_lang);
			}
			$this->db->limit(1);
			$query2 = $this->db->get('fields_meta');
		   
			if ($query2->num_rows() > 0)
			{
				$row2 = $query2->row();
				$temp_field['field_name'] = $row2->field_name;
			}

			// get data if in this content type
			$this->db->where('site_id', $this->site_id);
			$this->db->where('field_id', $row->field_id);
			$this->db->limit(1);
			$query3 = $this->db->get('r_sites_fields');
		   
			if ($query3->num_rows() > 0)
			{
				$row3 = $query3->row();
				$temp_field['rel_id'] = $row3->rel_id;
				$temp_field['required'] = $row3->required;
				$temp_field['order'] = $row3->order;
			}

		   $list[] = $temp_field;

		}

		$this->site_meta_fields = $list;

	}

    function update_meta_fields()
    {
		
		foreach($this->site_meta_fields as $field)
		{

			$db_fields = array(
			'site_id' => $this->site_id,
			'field_id' => $field['field_id'],
			'required' => $field['required'],
			'order' => $field['order']
			);
			
			if($field['exists'] === TRUE)
			{
				// update field

				if($field['rel_id'] == '')
				{
					// create new metadata
					if(!$this->db->insert('r_sites_fields', $db_fields) )
					{
						$this->messages[] = "could not create relationship for field {$field['field_id']}";
					}
		
				}
				else
				{
					// update metadata field
					if(!$this->db->update('r_sites_fields', $db_fields, array('rel_id' => $field['rel_id'] )) ) {
						$this->messages[] = "could not update relationship for field {$field['field_id']}";
					}
				}

			}
			else
			{
				if($field['rel_id'] != '')
				{
					// remove relationship
					$this->db->where('rel_id', $field['rel_id']);
					if(!$this->db->delete('r_sites_fields')) {
						$this->messages[] = "could not remove relationship for field {$field['field_id']}";
					}
				} 

			}

		} // end foreach()

		if(count($this->messages) == 0)
		{
			$this->messages[] = "site metadata field info successfully updated";
		}

    }


}

?>
