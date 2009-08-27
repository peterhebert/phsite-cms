<?php

class Media extends MY_Controller {
		
	function Media()
	{
		parent::MY_Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->model('Media_model');
	}
	
	function index()
	{	
		echo "Media controller";
		
		//$this->manage();
/*		$this->_authenticate();

		$this->Media_model->media_lang = $this->Site_model->default_lang;
		$media_root = $this->Media_model->dir_base;

		//get list of directories in media folder
		$this->Media_model->list_dirs();
		$this->Media_model->list_files_unique($media_root);

		$this->Media_model->path_uri = '/';
		
		if($this->Media_model->get_path_by_uri() === FALSE) {
			$this->Media_model->path_title = 'Media root';
		} 
		$dirs[] = anchor('cms/media/manage/'.$this->Site_model->default_lang,$this->Media_model->path_title)." ({$this->Media_model->file_count})";
		
		foreach($this->Media_model->dir_list as $index => $dir)
		{
			$this->Media_model->dir_current = $dir;
			$this->Media_model->list_files_unique();
			
			$dest_dir = str_replace($media_root,'',$dir);

			$this->Media_model->path_uri = $dest_dir;

			$dest_display_dir = str_replace($media_root.'/','',$dir);
			$dest_link = 'cms/media/manage/'.$this->Site_model->default_lang.$dest_dir;
			
			if($this->Media_model->get_path_by_uri() === TRUE) {
				$dest_text = $this->Media_model->path_title;
			} else {
				$dest_text = $dest_display_dir;
			}
			$dirs[] = anchor($dest_link,$dest_text)." ({$this->Media_model->file_count})";
			
		}
		$this->data['dirs'] = $dirs;

		$this->load->view('cms/media', $this->data);
*/	}


	function media_fields()
	{	

		$breadcrumb = array(
		anchor("cms","phSite CMS"),
		anchor("cms/media","Media"),
		'Edit media metadata fields'
		);
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$this->data['title_tag'] = 'Edit media metadata fields ';
		$this->data['title_h1'] = 'Edit media metadata fields';
		

		$this->load->view('cms/cms_form',$this->data);

	}

/*	function _form_meta_fields()
	{

		$action = "/cms/user/user_meta_fields";
		$hidden = array('site_id' => $this->Media_model->site_id);
		$attributes = array('name' => 'user_meta_fields','id' => 'user_meta_fields');

		$form = form_open($action, $attributes, $hidden);

		// fields

		$form .= "<table id=\"properties\">\r";
		$form .= "<thead>\r";
		$form .= "<tr>\r";
		$form .= "<th scope=\"col\" id=\"hd_name\">field name</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_exists\">use for user metadata?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_req\">required?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_order\">order</th>\r";
		$form .= "</tr>\r";
		$form .= "</thead>\r";
		$form .= "<tbody>\r";

		$this->Media_model->list_fields();
		
		$fieldCount = count($this->Media_model->field_list);
		$fieldNo = 1;

		foreach($this->Media_model->field_list as $field)
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
		$form .= form_submit('submit', 'Save');
		$form .= form_close();
		return $form;
	}
*/


	
	function manage() {

		$this->data['msg'] = '';

		$this->Media_model->media_lang = $this->Site_model->site_default_lang;
		$media_root = $this->Media_model->dir_base;
		
		$this->_get_dir();
		$display_dir = $this->Media_model->path_uri;

		/* update directory info in database */

		if($this->input->post('directory') != '') {
			$myDir = $this->input->post('directory');
		}

		if($this->input->post('path_title') != '') {
			if($this->input->post('path_id') != '') {
				$this->Media_model->path_id = $this->input->post('path_id');
			}
			$this->Media_model->path_title = $this->input->post('path_title');
			$this->Media_model->path_lang = $this->input->post('path_lang');
			$this->Media_model->update_path();
		}

		// delete all selected files
		if($this->input->post('delete') != '') {

			$del_ids = $this->input->post('delete');
			$unsuccessfull = array();
			
			foreach($del_ids as $del_file_id) {
			
				$del_db = FALSE;
				$this->Media_model->media_id = $del_file_id;
				$del_media_exists = $this->Media_model->get_media();

				if($del_media_exists === TRUE && $this->Media_model->external == 0) {
				
					$del_filename = $media_root.$this->Media_model->file_uri;
					$ext = strrchr($this->Media_model->file_uri, '.');
					$jpg_alt = str_replace($ext,'.jpg',$del_filename);
					
					// try deleting
					if(file_exists($del_filename)) {
						if(@unlink($del_filename) === TRUE) {
							@unlink($jpg_alt);
							$del_db = TRUE;
						} else {
							$unsuccessfull[] = basename($this->Media_model->file_uri);
						}
					}
					
				} else {
					$del_db = TRUE;
				}
				
				if($del_db === TRUE) {
					$this->Media_model->delete_media();
				}
			}
			
			 //success message
			if( count($unsuccessfull) == 0 ) {
				$this->data['msg'] = "<p>All files were successfully deleted</p>\r";
			}
			 //error message
			if( count($unsuccessfull) > 0 ) {
				$this->data['msg'] = "<p>The following files could not be deleted:<br />".implode(', ',$unsuccessfull)."</p>\r";
			}

		}
		
		//get list of directories in media folder
		$this->Media_model->list_dirs();

		if($this->Media_model->path_lang != '')
		{
			$dir_array['/'.$this->Media_model->path_lang] = 'Media root';
		} else {
			$dir_array[''] = 'Media root';
		}
		foreach($this->Media_model->dir_list as $index => $dir)
		{
			$this->Media_model->dir_current = $dir;
			$dest_dir = str_replace($media_root,'',$dir);
			$dest_display_dir = str_replace($media_root.'/','',$dir);
			if($this->Media_model->path_lang != '')
			{
				$dest_dir = '/'.$this->Media_model->path_lang.$dest_dir;
			}
			$dir_array[$dest_dir] = $dest_display_dir;
		}

		$dirs = "<p>";
		$dirs .= form_label('Select directory','directory');
		if($this->Media_model->path_lang != '')
		{
			$dirs .= form_dropdown('directory', $dir_array, '/'.$this->Media_model->path_lang.$display_dir);
		} else {
			$dirs .= form_dropdown('directory', $dir_array, $display_dir);
		}
		$dirs .= "</p>\n";

		$this->data['dirs'] = $dirs;

		$files = '';

		$other_langs = $this->Media_model->get_path_langs();
		if($other_langs)
		{
			foreach($other_langs as $temp_lang)
			{
				$this->Language_model->load($temp_lang);
				$other_list[] = anchor('cms/media/manage/'.$temp_lang.$display_dir, $this->Language_model->Ref_Name);
			}

			$files .= "<div class=\"block\">";
			$files .= "<div class=\"column w4\">";
		}
		
		$attributes = array('class' => 'thumbnails');

		$this->Media_model->dir_current = $media_root.$display_dir;
		$this->Media_model->list_files_unique();

		$temp_file_list = $this->Media_model->file_list;

		$this->Media_model->path_uri = $display_dir;

		$files .=  form_open(current_url());
		if($this->Media_model->get_path_by_uri()) {
			$files .= form_hidden('path_id',$this->Media_model->path_id);
		}


		$files .= form_fieldset('Path info');
		$field_params = array(
		'name'        => 'path_title',
		'id'          => 'path_title',
		'class'	=> 'text',
		'value' => $this->Media_model->path_title,
		);
		$files .= "<p>";
		$files .= form_label('Title','path_title');
		$files .=  form_input($field_params);
		$files .= "</p>\n";

		$langArray = $this->get_langs();

		$files .= "<p>";
		$files .=  form_label('Language', 'path_lang');
		$files .=  form_dropdown('path_lang', $langArray, $this->Media_model->path_lang);
		$files .= "</p>\n";

		$files .= form_fieldset_close();

		if($other_langs)
		{
			$files .= "</div>";
			$files .= "<div class=\"float w2\">";
			$files .= "<h4>Other languages</h4>";
			$files .= ul($other_list);
			$files .= "</div>";
			$files .= "</div>";
		}



		if(is_array($temp_file_list) && count($temp_file_list) > 0) {
			$files .= form_fieldset('Files');
			$files .= "<p>Click thumbnails to edit, select checkboxes to delete files</p>\n";
			$files .= "<p><a href=\"#\" id=\"select_all\" class=\"mg-right\">Select All</a> ";
			$files .= "<a href=\"#\" id=\"deselect_all\" class=\"mg-right\">Deselect All</a></p>\n";
			$this->Media_model->external = 0;
			foreach($temp_file_list as $file) {

				$this->Media_model->media_id = '';
				$this->Media_model->file_uri = '';
				$this->Media_model->created = '';
				$this->Media_model->creator = '';
				$this->Media_model->data_id = '';
				$this->Media_model->title = '';
				$this->Media_model->description = '';
				$this->Media_model->keywords = '';
				$this->Media_model->copyright = '';
				$this->Media_model->city = '';
				$this->Media_model->prov_state = '';
				$this->Media_model->country = '';
			
				$file_link = str_replace($_SERVER["DOCUMENT_ROOT"],'',$file);
				
				$this->Media_model->file_uri = str_replace($media_root,'',$file);
				$media_exists = $this->Media_model->get_media_by_uri();
				
				if($media_exists) {
					
					$meta_exists = $this->Media_model->get_meta();
					if($meta_exists === FALSE) {
						// insert metadata to database
						$this->Media_model->get_file_metadata();
						$this->Media_model->update_meta();
					}
					
				} else {
					// load file metadata and insert to database
					$this->Media_model->get_file_metadata();

					$media_ins = $this->Media_model->update_media();
					if($media_ins) {
						$meta_ins = $this->Media_model->update_meta();
						if($meta_ins) {
							$this->Media_model->update_meta();
						}
					}
				}
				$tag_link = '/cms/media/edit/id/'.$this->Media_model->media_id.'/lang/'.$this->Media_model->media_lang;
				
				$fA = explode('.',$file);
				$last = count($fA) - 1;
				$fA[$last] = 'jpg';
				$poster_name = implode('.',$fA);
				
				if (is_image($file)) {
					$img_tag = view_image($file_link,$this->Site_model->img_size_thumb,'',array('alt' => 'image: '.basename($file)));
				} else if (file_exists($poster_name)) {
					$poster_link = str_replace($_SERVER["DOCUMENT_ROOT"],'',$poster_name);
					$img_tag = view_image($poster_link,$this->Site_model->img_size_thumb,'',array('alt' => 'image: '.basename($file)));
				} else {
					
					$img_tag = basename($file);
				}

				$chk = array(
				'name'        => 'delete[]',
				'value'       => $this->Media_model->media_id,
				'checked'     => FALSE,
				);

				if ($this->Media_model->title != '') {
					$chk_lbl = $this->Media_model->title;
				} else {
					$chk_lbl = basename($this->Media_model->file_uri);
				}

				$temp_code = "<div class=\"thumb\"><a href=\"{$tag_link}\" alt=\"edit '{$this->Media_model->title}'\">{$img_tag}</a></div>\r";
				$temp_code .= "<div class=\"title\"><label>".form_checkbox($chk)." {$chk_lbl}</label><br />\r";
				$temp_code .= $this->Media_model->get_mime($media_root.$this->Media_model->file_uri,1)."</div>\r";

				$files_clean[] = $temp_code;
			}
			$files .= ul($files_clean, $attributes);
			unset($files_clean);
			$files .= form_fieldset_close();

		} else {
			$files .= "<p>no media in directory</p>\n";
		}
		$files .= '<p>'.form_submit('submit', 'Save')."</p>\n";
		$files .=  form_close();

		$this->data['page'] = site_url('cms/media/manage');
		$this->data['files'] = $files;
		$this->data['size'] = $this->Site_model->img_size_thumb;

		$this->load->view('cms/media_manage', $this->data);
	}

	function edit() {

		$this->_authenticate();
		
		$media_root = $this->Media_model->dir_base;
		$dir = str_replace($_SERVER["DOCUMENT_ROOT"],'',$media_root);

		// if form submitted, process data
		if($this->input->post('submit') != '') {
			$this->load->library('form_validation');		

	
			// set validation rules
			if($this->input->post('media_id') != '') {
				$this->form_validation->set_rules('media_id', 'Media ID', 'is_natural_no_zero');
			}
			if($this->input->post('data_id') != '') {
				$this->form_validation->set_rules('data_id', 'Metadata ID', 'is_natural_no_zero');
			}
			$this->form_validation->set_rules('file_uri', 'File', 'required|path_file');
			$this->form_validation->set_rules('created', 'Date Created', 'required|is_date');
			$this->form_validation->set_rules('media_lang', 'Language', 'required|is_lang');
			$this->form_validation->set_rules('title', 'Title', 'required');
			$this->form_validation->set_rules('description', 'Description', 'required');
	
			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors();
			} else {
				// check if file exists in media directory - if not mark as external
				$this->Media_model->file_uri = $this->input->post('file_uri');
				$file_full = $this->Media_model->dir_base.$this->input->post('file_uri');
				if(file_exists($file_full)) {
					$this->Media_model->external = 0;
				} else {
					$this->Media_model->external = 1;
				}
				// get mime type
				if($this->input->post('mime') == '') {
					$this->Media_model->mime = $this->Media_model->get_mime($file_full);
				} else {
					$this->Media_model->mime = $this->input->post('mime');
				}
				// normalize keywords
				$this->Media_model->keywords = csv_normalize($this->input->post('keywords'));

				$this->Media_model->media_id = $this->input->post('media_id');
				$this->Media_model->data_id = $this->input->post('data_id');
				
				// normalize date
				if($this->input->post('created') == '' || $this->input->post('created') == '0000-00-00 00:00:00') {
					$this->Media_model->created = date('Y-m-d H:i:s');
				} else {
					$date_raw = $this->input->post('created');
					$this->Media_model->created = format_dates($date_raw);
				}

				$this->Media_model->creator = $this->input->post('creator');
				$this->Media_model->media_lang = $this->input->post('media_lang');
				$this->Media_model->title = $this->input->post('title');
				$this->Media_model->description = $this->input->post('description');
				$this->Media_model->copyright = $this->input->post('copyright');
				$this->Media_model->city = $this->input->post('city');
				$this->Media_model->prov_state = $this->input->post('prov_state');
				$this->Media_model->country = $this->input->post('country');

				$media_success = $this->Media_model->update_media();
				if($media_success === TRUE) {
					$meta_success = $this->Media_model->update_meta();
					if($meta_success === TRUE) {
						$this->data['msg'] = "media info updated successfully";
					} else {
						$this->data['msg'] = "could not update metadata";
					}
				} else {
					$this->data['msg'] = "could not update media info";
				}
			}
	
		} // end form processing

		$file_link = '';
		$file = '';
		$db_exists = FALSE;

		$uri_params = $this->uri->uri_to_assoc(4);

		if($this->Media_model->media_lang == '' && array_key_exists('lang',$uri_params)) {
			$this->Media_model->media_lang = $uri_params['lang'];
		} else {
			$this->Media_model->media_lang = $this->Site_model->default_lang;
		}

		if(array_key_exists('id',$uri_params)) {
			$this->Media_model->media_id = intval($uri_params['id']);
			$db_exists = $this->Media_model->get_media();
			$this->Media_model->get_meta();
			if($db_exists === TRUE) {
				$file_link = $dir.$this->Media_model->file_uri;
				$file = $media_root.$this->Media_model->file_uri;
			}
		}

		if( file_exists($file) ) {
			$this->Media_model->read_file_info();
			$this->Media_model->external = 0;
			if($file_link != '') {
				$this->data['thumb'] = view_image($file_link,$this->Site_model->img_size_thumb,'',array('alt' => 'image: '.basename($file)));
			}
		} else {
			$this->Media_model->file_uri = '';
			$this->Media_model->external = 1;
		}
		

		// start form
		$attributes = array('id' => 'media_edit');
		$content = form_open($this->uri->uri_string(), $attributes);

		// hidden fields
		$content .= form_hidden('media_id', $this->Media_model->media_id);
		$content .= form_hidden('data_id', $this->Media_model->data_id);
		$content .= form_hidden('mime', $this->Media_model->mime);

		// visible form inputs
		$content .= form_fieldset('File info');

		$attributes = array(
		'name'        => 'file_uri',
		'id'          => 'file_uri',
		'class'	=> 'large_input',
		'value' => $this->Media_model->file_uri,
		);
		$content .= "<p>";
		$content .=  form_label('File', 'file_uri');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'created',
		'id'          => 'created',
		'class'	=> 'small_input',
		'value' => $this->Media_model->created,
		);
		$content .= "<p>";
		$content .=  form_label('Created', 'created');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$content .= form_fieldset_close(); 
		$content .= form_fieldset('Metadata');

		$attributes = array(
		'name'        => 'creator',
		'id'          => 'creator',
		'class'	=> 'text',
		'value' => $this->Media_model->creator,
		);
		$content .= "<p>";
		$content .=  form_label('Creator', 'creator');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$langArray = $this->get_langs();

		$content .= "<p>";
		$content .=  form_label('Language', 'media_lang');
		$content .=  form_dropdown('media_lang', $langArray, $this->Media_model->media_lang);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'title',
		'id'          => 'title',
		'class'	=> 'text',
		'value' => $this->Media_model->title,
		);
		$content .= "<p>";
		$content .=  form_label('Title', 'title');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'description',
		'id'          => 'description',
		'class'	=> 'textarea',
		'cols'	=> 38,
		'value' => $this->Media_model->description,
		);
		$content .= "<p>";
		$content .=  form_label('Description', 'description');
		$content .=  form_textarea($attributes);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'keywords',
		'id'          => 'keywords',
		'class'	=> 'text',
		'value' => $this->Media_model->keywords,
		);
		$content .= "<p>";
		$content .=  form_label('Keywords', 'keywords');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'copyright',
		'id'          => 'copyright',
		'class'	=> 'text',
		'value' => $this->Media_model->copyright,
		);
		$content .= "<p>";
		$content .=  form_label('Copyright', 'copyright');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		
		$attributes = array(
		'name'        => 'city',
		'id'          => 'city',
		'class'	=> 'text',
		'value' => $this->Media_model->city,
		);
		$content .= "<p>";
		$content .=  form_label('City', 'city');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'prov_state',
		'id'          => 'prov_state',
		'class'	=> 'text',
		'value' => $this->Media_model->prov_state,
		);
		$content .= "<p>";
		$content .=  form_label('Province / State', 'prov_state');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$attributes = array(
		'name'        => 'country',
		'id'          => 'country',
		'class'	=> 'text',
		'value' => $this->Media_model->country,
		);
		$content .= "<p>";
		$content .=  form_label('Country', 'country');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$content .= form_fieldset_close(); 

		$content .= "<p>";
		$content .= form_submit('submit', 'Save');
		$content .= "</p>\n";
		
		$content .= form_close(); 

		$this->data['form'] = $content;
		
		// load list of other languages
		$other_langs = $this->Media_model->get_other_langs();
		$langlist = array();
		foreach($other_langs as $zlang) {
			$this->Language_model->load($zlang);
			if($this->Language_model->Native_Name != '') {
				$myLang = $this->Language_model->Native_Name;
			} else {
				$myLang = $this->Language_model->Ref_Name;
			}

			$langlist[] = anchor('cms/media/edit/id/'.$this->Media_model->media_id.'/lang/'.$zlang, $myLang);
		}
		if(count($langlist) > 0) {
			$this->data['langlist'] = ul($langlist);
		}
		
		$this->load->view('cms/media_edit', $this->data);

	}

	function upload()
	{	

		$this->_authenticate();

		$success = FALSE;
		
		//get list of directories in media folder
		$media_root = $this->Media_model->dir_base;
		$dir_array[''] = 'media root';

		$this->Media_model->list_dirs($media_root);
		foreach($this->Media_model->dir_list as $dir) {
			$dest_dir = str_replace($media_root,'',$dir);
			$dir_array[$dest_dir] = $dest_dir;
		}
		$this->data['directories'] = $dir_array;

		//initialize the form values
		$this->data['dest_path'] = $media_root;
		$this->data['create_entry'] = FALSE;

		// get max upload size from php.ini settings
		$max_bytes = get_max_upload_size();
		$this->data['max_size'] = resize_bytes($max_bytes);
		$config['max_size'] = $max_bytes * 1024;

		$my_types = array_keys($this->Media_model->media_types);
		$config['allowed_types'] = implode('|',$my_types);

		foreach($this->Media_model->media_types as $ext => $type) {
			$arr_allowed[] = $type[1]." (.{$ext})";
		}
		$this->data['allowed_types'] = implode("<br />\n",$arr_allowed);

		// process form if submitted
		if($this->input->post('submit') != '') {
			$this->data['msg'] = '';

			if($this->input->post('new_path') != '') {
				$new_path = convert_uri($this->input->post('new_path'),TRUE);
				$new_path = '/'.trim($new_path,'/');
				$full_path = $media_root.$new_path;

				if (!file_exists($full_path) || !is_dir($full_path) ) {
					if(!mkdir($full_path, 0755))
					{
					$this->data['msg'] .= "<p>the path '{$this->input->post('new_path')}' could not be created</p>\r";
					}
				}

			} else {
				$full_path = $media_root.$this->input->post('dest_path');
			}

			$config['upload_path'] = $full_path;
			$this->load->library('upload', $config);
			
			$success = $this->upload->do_upload();
			
			if ($success === FALSE)
			{
				$this->data['msg'] .= $this->upload->display_errors('<p>', '</p>');
			}	
			else
			{

				$this->data['upload_data'] = $this->upload->data();
				if($this->data['upload_data']['is_image'] == 1)
				{
					$img_path = str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$this->data['upload_data']['full_path']);
					$this->data['image_thumb'] = view_image($img_path,$this->Site_model->img_size_thumb);
				}
			}

		}

		if($success === TRUE) {

			$this->data['msg'] = "<p>File uploaded successfully</p>\r";

			$this->Media_model->file_uri = '/'.trim($img_path,'/');
			$this->Media_model->get_file_metadata();
			$this->Media_model->external = 0;
		
			$this->Media_model->media_lang = $this->Site_model->default_lang;

			if ($this->Media_model->update_media() == TRUE)
			{
				if ($this->Media_model->update_meta() == TRUE)
				{
					$this->data['msg'] = "<p>Metadata added to database</p>\r";
				} else {
				}
			} else {
			}
			$this->load->view('cms/upload_success', $this->data);

		} else {
			$this->load->view('cms/upload_form', $this->data);
		}


	}

	function _get_dir() {
		$raw_uri = $this->uri->segment_array();
		$uri_array = array_slice($raw_uri, 3);
		

		if($this->uri->total_segments() <= 3) {
			$this->Media_model->path_lang = $this->Site_model->site_default_lang;
			$dir = '/';
		} else {
			if($this->Language_model->load($uri_array[0]) === TRUE) {
				$this->Media_model->path_lang = array_shift($uri_array);
			}	
			$dir = '/'.implode('/',$uri_array);
		}

		$this->Media_model->path_uri = $dir;
	}

}
?>