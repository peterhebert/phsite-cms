<?php
class Setup extends MY_Controller
{

	function Setup()
	{
		parent::MY_Controller();

		$this->load->helper('form');

		// load objects
		$this->load->model('Content_model');
		$this->load->model('Content_type_model');
		$this->load->model('Field_model');
		$this->load->model('User_model');

		//$this->_authenticate();
		$this->_load_site_metadata();
	}
	
	function index()
	{
		if($this->session->userdata('default_lang'))
		{
			$this->data['site_meta_lang'] = $this->session->userdata('default_lang');
		}
		else
		{
			$this->data['site_meta_lang'] = $this->Site_model->site_default_lang;
		}
		
		$this->data['site_metadata'] =  $this->Language_model->site_meta_menu();

		$this->load->view('cms/setup',$this->data);
	}

	function structure()
	{
	
		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : 	Data Structure';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Data Structure | Setup ';
		$this->data['title_h1'] = 'Data Structure';

		$form = "<p>You can define what types of content your CMS stores, and the individual data fields. Fields can be assinged to any content type.</p>\r";

		$form .= "\t<div class=\"column w2\">";

		// load list of content types
		$this->Content_type_model->get_list();
		$d2['list'] = $this->Content_type_model->types;
		$form .= $this->load->view('cms/content_types_list',$d2, true);

		$form .= "</div>\r";
		$form .= "\t<div class=\"float w2\">";

		// load list of fields
		$d2['list'] = $this->Field_model->get_list();
		$form .= $this->load->view('cms/field_list',$d2, true);

		$form .= "</div>\r";

		$site_metadata = "Site metadata\r";
		$site_metadata .=  $this->Language_model->site_meta_menu();

		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata), true);

		$this->data['form'] = $form;

		// now load view
		$this->load->view('cms/cms_form',$this->data);
	}
	
	function languages()
	{

		if($this->input->post('submit')) {
			$enabled_langs = explode(',',$this->input->post('enabled_langs'));
			$sel_lang = $this->input->post('lang');

			$new_lang = $this->input->post('new_lang');

			if(is_array($sel_lang)) {
				foreach($enabled_langs as $lang) {
					if(in_array($lang,$sel_lang)) {
						$activated = 1;
					} else {
						$activated = 0;
					}
					$this->Language_model->ISO_639_3 = $lang;
					$this->Language_model->set_active($activated);
				}
			}

			if(is_array($new_lang)) {
				foreach($new_lang as $lang) {
					$this->Language_model->ISO_639_3 = $lang;
					$this->Language_model->set_active(1);
				}
			}

		}
		if($this->input->post('search_submit')) {
			$matches_label = "Matches for '".$this->input->post('find')."'";
			$matches = $this->Language_model->find_matches($this->input->post('find'));
		}

		$enabled = $this->Language_model->get_list();
		$action = site_url('cms/setup/languages');
		$attributes = array('name' => 'search_langs');

		// search for matches
		$form = form_open($action, $attributes);

		$form .= "<p>";
		$form .= form_label('Type to find languages', 'search');
		$params = array(
		'name'        => 'find',
		'id'          => 'find',
		);
		$form .= form_input($params);
		$form .= "</p>\n";

		$form .= "<p>";
		$form .= form_submit('search_submit', 'Search');
		$form .= "</p>\n";
		$form .= form_close();

		// enable or disable - list of enabled languages
		$attributes = array('name' => 'langform');
		$active = array();
		foreach($enabled as $lang) {
			$active[] = $lang['ISO_639_3'];
		}
		$hidden_list = implode(',',$active);
		$hidden = array(
			'enabled_langs' => $hidden_list,
		);
		
		$form .= form_open($action, $attributes,$hidden);

		if(isset($matches)) {
			$form .= "<p>";
			$form .= form_label($matches_label);
			$form .= "<span class=\"radiogroup w4\">";

			foreach($matches as $lang) {
				$params = array(
				'name'        => "new_lang[]",
				'id'        => "new_lang[]",
				'value'       => $lang['ISO_639_3'],
				'checked'     => $lang['activated'],
				);
				$form .= "<label>";
				$form .= form_checkbox($params);
				$form .= ' '.$lang['Inverted_Name'];
				if($lang['Native_Name'] != '') {
					$form .= " ({$lang['Native_Name']})";
				}
				$form .= "</label> ";
			}
			$form .= "</span></p>\n";
		}

		if(count($active) > 0) {
			$form .= "<p>";
			$form .= form_label('Activated Languages');
			$form .= "<span class=\"radiogroup w4\">";

			foreach($enabled as $lang) {
				$params = array(
				'name'        => "lang[]",
				'id'        => "lang[]",
				'value'       => $lang['ISO_639_3'],
				'checked'     => TRUE,
				);
				$form .= "<label>";
				$form .= form_checkbox($params);
				$form .= ' '.$lang['Inverted_Name'];
				if($lang['Native_Name'] != '') {
					$form .= " ({$lang['Native_Name']})";
				}
				$form .= "</label> ";
			}
			$form .= "</span></p>\n";
		} else {
			$form .= "<p>No Activated Languages</p>\n";
		}

		$form .= "<p>";
		$form .= form_submit('submit', 'Save');
		$form .= "</p>\n";
		$form .= form_close();

		$this->data['form'] = $form;

		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : 	Languages';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Languages | Setup ';
		$this->data['title_h1'] = 'Languages';

		$site_metadata = "Site metadata\r";
		$site_metadata .=  $this->Language_model->site_meta_menu();
		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata),true);

		$this->load->view('cms/cms_form',$this->data);
	
	} // end languages()

    // update site prefs
	function site_prefs()
    {

		$this->_process_prefs();		

		$this->Site_model->load_site();
		$this->data['form'] = $this->_form_prefs();

		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : 	Site preferences';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Site preferences | Setup ';
		$this->data['title_h1'] = 'Site preferences';
		
		$msg = $this->Site_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$site_metadata = "Site metadata\r";
		$site_metadata .=  $this->Language_model->site_meta_menu();
		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata),true);

		$this->load->view('cms/cms_form',$this->data);
	}
    // end site prefs

    // update site metadata
	function site_meta()
    {

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->Site_model->site_title_id = $params['id'];
		}
		$this->Site_model->reset_meta();
		if(array_key_exists('lang',$params))
		{
			$this->Site_model->site_meta_lang = $params['lang'];
		}
		if($this->Site_model->site_meta_lang == '')
		{
			$this->Site_model->site_meta_lang = $this->Site_model->site_default_lang;
		}


		$this->_process_meta();

		$this->Site_model->load_site_title();
		$this->Site_model->load_site_meta();

		$this->data['form'] =  $this->_form_meta();

		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : 	Site metadata';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Site metadata | Setup ';
		$this->data['title_h1'] = 'Site metadata';
		
		$msg = $this->Site_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$site_metadata = "Site metadata\r";
		$site_metadata .=  $this->Language_model->site_meta_menu();
		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata),true);

		$this->load->view('cms/cms_form',$this->data);
	}
    // end site metadata

    // generate form for site preferences
	function _form_prefs()
    {
		$action = 'cms/setup/site_prefs';
		$hidden = array('site_id' => $this->Site_model->site_id);
		$attributes = array('name' => 'site_prefs','id' => 'site_prefs');

		$form = form_open($action, $attributes, $hidden);

		// get list of top level pages for home page
		$form_options = array();
		$form_options['0'] = 'Auto-generate';

		$this->Content_model->list_content(0,'','asc',0);
		
		$homePages = $this->Content_model->content_list;
		foreach($homePages as $row) {
			$form_options["{$row['content_id']}"] = $row['title'];
		}
		
		$form .= "<p>";
		$form .= form_label('Home Page', 'home_content_id');
		$form .= form_dropdown('home_content_id', $form_options, $this->Site_model->home_content_id);		
		$form .= "</p>\r";

		$this->Site_model->list_themes();
		$form_options = $this->Site_model->site_themes;
		$form .= "<p>";
		$form .= form_label('Theme', 'theme');
		$form .= form_dropdown('theme', $form_options, $this->Site_model->theme);		
		$form .= "</p>\r";

		$form_options = timezone_dropdown();
		$form .= "<p>";
		$form .= form_label('Time zone', 'site_timezone');
		$form .= form_dropdown('site_timezone', $form_options, $this->Site_model->site_timezone);		
		$form .= "</p>\r";

		$form_options = $this->Language_model->get_list_dropdown();
		$form .= "<p>";
		$form .= form_label('Default language', 'site_default_lang');
		$form .= form_dropdown('site_default_lang', $form_options, $this->Site_model->site_default_lang);		
		$form .= "</p>\r";

		$form_options = $this->Content_type_model->get_list_dropdown();
		$form .= "<p>";
		$form .= form_label('Default Content Type', 'default_content_type');
		$form .= form_dropdown('default_content_type', $form_options, $this->Site_model->default_content_type);		
		$form .= "</p>\r";

		$form_options = $this->User_model->get_list_dropdown(0,1);
		$form .= "<p>";
		$form .= form_label('Webmaster', 'site_admin_id');
		$form .= form_dropdown('site_admin_id', $form_options, $this->Site_model->site_admin_id);		
		$form .= "</p>\r";

		$form .= "<p>";
		$form .= form_label('Editor', 'site_editor_id');
		$form .= form_dropdown('site_editor_id', $form_options, $this->Site_model->site_editor_id);		
		$form .= "</p>\r";

		$form_options = array(
		'class'        => 'small_input',
		'name'        => 'site_date_created',
		'id'          => 'site_date_created',
		'value'       => $this->Site_model->site_date_created
		);
		$form .= "<p>";
		$form .= form_label('Date site created', 'site_date_created');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'limit_posts',
		'id'          => 'limit_posts',
		'value'       => $this->Site_model->limit_posts
		);
		$form .= "<p>";
		$form .= form_label('Posts per page (blogs)', 'limit_posts');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'limit_thumbs',
		'id'          => 'limit_thumbs',
		'value'       => $this->Site_model->limit_thumbs
		);
		$form .= "<p>";
		$form .= form_label('Thumbnails per page (galleries)', 'limit_thumbs');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_th',
		'id'          => 'img_size_th',
		'value'       => $this->Site_model->img_size_th
		);
		$form .= "<p>";
		$form .= form_label('Thumbnail image size', 'img_size_th');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_md',
		'id'          => 'img_size_md',
		'value'       => $this->Site_model->img_size_md
		);
		$form .= "<p>";
		$form .= form_label('Medium image size', 'img_size_md');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_lg',
		'id'          => 'img_size_lg',
		'value'       => $this->Site_model->img_size_lg
		);
		$form .= "<p>";
		$form .= form_label('Large image size', 'img_size_lg');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form .= "<p>";
		$form .= form_submit('submit', 'Save');
		$form .= "</p>\n";

		$form .= form_close();
		return $form;

	}

	function _process_prefs()
	{
		if( $this->input->post('submit') != '' )
		{
			$this->load->library('form_validation');		
			// set validation rules
			$this->form_validation->set_rules('home_content_id', 'Home Page', 'required|is_natural');
			$this->form_validation->set_rules('site_admin_id', 'Webmaster', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('site_editor_id', 'Editor', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('site_default_lang', 'Default Language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('default_content_type', 'Default Content Type', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('theme', 'Theme', 'required|alpha_dash');
			$this->form_validation->set_rules('site_timezone', 'Time Zone', 'required');
			$this->form_validation->set_rules('limit_posts', 'Posts per page (blogs)', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('limit_thumbs', 'Images per page (galleries)', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_th', 'Thumbnail image  size', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_md', 'Medium image size', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_lg', 'Large image size', 'required|is_natural_no_zero');

			// validate
			if ($this->form_validation->run() == FALSE)
			{
				$this->Site_model->messages[] = validation_errors();
			} else {

				if($this->input->post('site_date_created') != '' && $this->input->post('site_date_created') != '0000-00-00 00:00:00')
				{
					$this->Site_model->site_date_created = $this->input->post('site_date_created');
				}
				else
				{
					$this->Site_model->site_date_created = date('Y-m-d H:i:s');
				}

				if($this->input->post('site_id') != '')
				{
					$this->Site_model->site_id = $this->input->post('site_id');
				}

				$this->Site_model->home_content_id = $this->input->post('home_content_id');
				$this->Site_model->site_timezone = $this->input->post('site_timezone');
				$this->Site_model->theme = $this->input->post('theme');
				$this->Site_model->site_default_lang = $this->input->post('site_default_lang');
				$this->Site_model->default_content_type = $this->input->post('default_content_type');
				$this->Site_model->site_admin_id = $this->input->post('site_admin_id');
				$this->Site_model->site_editor_id = $this->input->post('site_editor_id');
				$this->Site_model->limit_posts = $this->input->post('limit_posts');
				$this->Site_model->limit_thumbs = $this->input->post('limit_thumbs');
				$this->Site_model->img_size_th = $this->input->post('img_size_th');
				$this->Site_model->img_size_md = $this->input->post('img_size_md');
				$this->Site_model->img_size_lg = $this->input->post('img_size_lg');

				$success = $this->Site_model->update_site();
				if($success)
				{
					$this->Site_model->messages[] = 'Site preferences updated successfully';
				}

			} // end validation
		} // end form processing

	} // end _process_prefs()

	function _form_meta()
	{
		$action = 'cms/setup/site_meta';
		$hidden = array('site_id' => $this->Site_model->site_id, 'site_title_id' => $this->Site_model->site_title_id);
		$attributes = array('name' => 'site_meta','id' => 'site_meta');

		$form = form_open($action, $attributes, $hidden);

		$form_options = $this->Language_model->get_list_dropdown();
		$form .= "<p>\r";
		$form .= form_label('Language', 'site_meta_lang');
		$form .= form_dropdown('site_meta_lang', $form_options, $this->Site_model->site_meta_lang);		
		$form .= "</p>\r";

		$form_options = array(
		'class'        => 'text',
		'name'        => 'title',
		'id'          => 'title',
		'value'       => $this->Site_model->title
		);
		$form .= "<p>";
		$form .= form_label('Title', 'title');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'class'        => 'text',
		'name'        => 'subtitle',
		'id'          => 'subtitle',
		'value'       => $this->Site_model->subtitle
		);
		$form .= "<p>";
		$form .= form_label('Subtitle', 'subtitle');
		$form .= form_input($form_options);
		$form .= "</p>\n";

		$fd_meta = $this->Site_model->site_metadata;

		foreach($fd_meta as $index => $fd)
		{

			// hidden fields
			$form_options = array(
			'site_meta_id[]' => $fd['site_meta_id'],
			'field_id[]' =>  $index,
			'field_name[]' =>  $fd['field_name'],
			'required[]' =>  $fd['required'],
			);

			$form .= form_hidden($form_options);

			$tmp_label = $fd['field_ref_name'];
			if($fd['field_name'] != '')
			{
				$tmp_label = $fd['field_name'];
			}
			$form .= "<p>\r";
			$form .= form_label($tmp_label, $fd['field_ref_name']);
			$form_options = array(
			'id' => $fd['field_ref_name'],
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $fd['field_input_type']
			);
			switch($fd['field_input_type']) {

			case 'directory':
				$this->load->model('Media_model');
				$this->Media_model->list_dirs();
				$media_root = $this->Media_model->dir_base;
				$dir_list_temp = $this->Media_model->dir_list;
				$dir_vals[''] = 'media root';
				
				foreach($dir_list_temp as $the_dir) {
					$temp_dir_val = str_replace($media_root,'',$the_dir);
					$dir_vals[$temp_dir_val] = $temp_dir_val;
				}
				
				$form .= form_dropdown($form_options['name'], $dir_vals, $fd['value']);
				break;
			case 'file':
				$this->load->model('Media_model');
				$this->Media_model->list_dirs();
				$media_root = $this->Media_model->dir_base;
				$dir_files = array('' => 'no file');
				foreach($this->Media_model->dir_list as $index => $dir)
				{
					$this->Media_model->dir_current = $dir;
					$dest_dir = str_replace($media_root,'',$dir);

					$this->Media_model->list_files();
					foreach($this->Media_model->file_list as $file_path) {
						$file_rel = str_replace($media_root,'',$file_path);
						$pinfo = pathinfo($file_path);
						$dir_files[$file_rel] = $pinfo['basename'];
					}
					
				}

				$form .= form_dropdown($form_options['name'], $dir_files, $fd['value']);
				break;
			case 'textarea':
				$form_options['cols'] = 38;
				$form .=  form_textarea($form_options);
				break;
			case 'richtext':
				$form_options['cols'] = 38;
				$form .=  form_textarea($form_options);
				break;
			default:
				$form .=  form_input($form_options);
			}
			$form .= "</p>\r";
		}
		$form .= form_fieldset_close();

		$form .= "<p>";
		$form .= form_submit('submit', 'Save');
		$form .= "</p>\n";

		$form .= form_close();
		return $form;
	}
	
	function _process_meta()
    {

		// process form
		if( $this->input->post('submit') != '' )
		{
			// validate
			$this->load->library('form_validation');		

			// set validation rules
			$this->form_validation->set_rules('site_meta_lang', 'Language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('title', 'Site Title', 'required');

			// load custom field list and add validation rules
			$this->Site_model->load_meta_fields();
			
			$meta_fields = $this->Site_model->site_meta_fields;

			foreach($meta_fields as $fd)
			{
				$tmp_valid = array();

				if($fd['required'] == 1)
				{
					$tmp_valid[] = 'required';
				}

				if($fd['field_validation'] != '')
				{
					$tmp_valid[] = $fd['field_validation'];
				}
				$concat_valid = implode('|', $tmp_valid);

				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd['field_id'], $fd['field_name'], $concat_valid);
				}

			}

			// run validation and load field values
			$form_is_valid = $this->form_validation->run();

			$this->Site_model->site_id = $this->input->post('site_id');
			$this->Site_model->site_title_id = $this->input->post('site_title_id');
			$this->Site_model->site_meta_lang = $this->input->post('site_meta_lang');
			$this->Site_model->title = $this->input->post('title');
			$this->Site_model->subtitle = $this->input->post('subtitle');

			$tmp_field_id = $this->input->post('field_id');
			$tmp_field_name = $this->input->post('field_name');
			$tmp_meta_id = $this->input->post('site_meta_id');
			$tmp_required = $this->input->post('required');
			$tmp_field_needs_lang = $this->input->post('field_needs_lang');
			
			$form_meta = array();
			foreach($tmp_field_id as $index => $tmp_id)
			{
				$val_lbl = 'value_'.$tmp_id;
				$value = $this->input->post($val_lbl);
				
				$this->Field_model->field_id = $tmp_id;
				$this->Field_model->load();
				
				if($this->Field_model->field_ref_name == 'keywords')
				{
					$value = csv_normalize($value);
				}
				
				$tmp_meta = array(
				'site_meta_id' => $tmp_meta_id[$index],
				'field_id' => $tmp_id,
				'field_name' => $tmp_field_name[$index],
				'value' => $value,
				'required' => $tmp_required[$index],
				'field_ref_name' => $this->Field_model->field_ref_name,
				'field_needs_lang' => $tmp_field_needs_lang[$index],
				);

				$form_meta["$tmp_id"] = $tmp_meta;

			}
			$this->Site_model->site_metadata = $form_meta;

			// on valid, write metadata
			if ($form_is_valid)
			{

				// update site metadata 
				$this->Site_model->update_title();
				$this->Site_model->update_meta();

				if(count($this->Site_model->messages) == 0)
				{
					$this->Site_model->messages[] = "site metadata updated successfully";
				}


			} else {
				$this->Site_model->messages[] = validation_errors();
			} // end validation

		} // end form processing

	} // end _process_meta()

	function site_meta_fields()
	{
		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : Site metadata fields';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Site metadata fields | Setup ';
		$this->data['title_h1'] = 'Site metadata fields';

		$this->_process_meta_fields();
		
		$this->data['form'] = $this->_form_meta_fields();

		$msg = $this->Site_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$site_metadata = "Site metadata\r";
		$site_metadata .=  $this->Language_model->site_meta_menu();
		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata),true);

		$this->load->view('cms/cms_form',$this->data);
	}

	function _form_meta_fields()
	{

		$action = "/cms/setup/site_meta_fields";
		$hidden = array('site_id' => $this->Site_model->site_id);
		$attributes = array('name' => 'site_meta_fields','id' => 'site_meta_fields');

		$form = form_open($action, $attributes, $hidden);

		// fields
		$form .= form_fieldset('Fields');
		$form .= "\r";

		$form .= "<table id=\"properties\">\r";
		$form .= "<thead>\r";
		$form .= "<tr>\r";
		$form .= "<th scope=\"col\" id=\"hd_name\">field name</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_exists\">use for site metadata?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_req\">required?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_order\">order</th>\r";
		$form .= "</tr>\r";
		$form .= "</thead>\r";
		$form .= "<tbody>\r";

		$this->Site_model->list_fields();
		
		$fieldCount = count($this->Site_model->site_meta_fields);
		$fieldNo = 1;

		foreach($this->Site_model->site_meta_fields as $field)
		{

			if($field['field_name'] != '') {
				$temp_name = $field['field_name'];
			} else {
				$temp_name = $field['field_ref_name'];
			}

			if($fieldNo < $fieldCount) {
			$form .= "<tr>\n";
			} else {
			$form .= "<tr class=\"last\">\n";
			}
			$fieldNo = $fieldNo + 1;
	
			$form .= form_hidden('field_id[]', $field['field_id']);
	
			if($field['rel_id'] != '') {
				$exists = true;
			} else {
				$exists = false;
			}
			$form .= form_hidden('rel_id[]', $field['rel_id']);

			$params = array(
			'name'        => 'exists[]',
			'id'          => 'exists[]',
			'value'       => $field['field_id'],
			'checked'     => $exists
			);
	
			$form .= "<th headers=\"hd_name\" id=\"{$temp_name}\" scope=\"row\">{$temp_name}</th>\n";
			$form .= "<td headers=\"hd_exists\" class=\"align-center\">".form_checkbox($params)."</td>\n";

			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $field['field_id'],
			'checked'     => $field['required']
			);

			$form .= "<td headers=\"{$temp_name} hd_req\" class=\"align-center\">".form_checkbox($params)."</td>\n";

			$params = array(
			'name'        => "order[]",
			'id'          => "order[]",
			'value'       => $field['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			$form .= "<td headers=\"{$temp_name} hd_order\">".form_input($params)."</td>\n";


			$form .= "</tr>\n";

		}

		$form .= "</tbody></table>\n";


		$form .= form_fieldset_close("\r");
		$form .= form_submit('submit', 'Save');
		$form .= form_close();
		return $form;
	}


	function _process_meta_fields()
	{

		if($this->input->post('submit') != '')
		{
			// get values from form post data
			$this->Site_model->site_id = $this->input->post('site_id');
			
			$temp_fields = $this->input->post('field_id');
			$temp_rels = $this->input->post('rel_id');
			$temp_exists = $this->input->post('exists');
			$temp_required = $this->input->post('required');
			$temp_order = $this->input->post('order');

			$field_updates = array();
			foreach($temp_fields as $index => $id)
			{
				$temp_field = array(
				'field_id' => $id,
				'rel_id' => $temp_rels[$index],
				'exists' => FALSE,
				'required' => 0,
				'order' => $temp_order[$index],
				);
				
				if(is_array($temp_exists) && in_array($id,$temp_exists)){
					$temp_field['exists'] = TRUE;
				}

				if(is_array($temp_required) && in_array($id,$temp_required)){
					$temp_field['required'] = 1;
				}
				
				$field_updates[] = $temp_field;
			}
			$this->Site_model->site_meta_fields = $field_updates;
			$this->Site_model->update_meta_fields();

		}
		
	}// end _process_meta_fields()


}
?>