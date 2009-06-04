<?php

class Media extends Controller {
		
	function Media()
	{
		parent::Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->model('Site_model');
		$this->load->model('Media_model');
		$this->load->model('Language_model');
	}
	
	function authenticate()
	{
		// if not logged in, redirect to login form
		$this->load->library('session');
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
			// get site metadata
			$this->Site_model->site_lang = $this->session->userdata('default_lang');
			$this->Site_model->load_meta();
			$this->Site_model->load_prefs();
			$this->data['site_title'] = $this->Site_model->title;
		}

	}

	function index()
	{	
		$this->manage();
	}

	function upload()
	{	

		$this->authenticate();

		$success = FALSE;
		
		//get list of directories in media folder
		$media_root = $this->Media_model->dir_base;
		$dir_array[$media_root] = 'media root';

		$this->Media_model->list_dirs($media_root);
		foreach($this->Media_model->dir_list as $dir) {
			$dest_dir = str_replace($media_root,'',$dir);
			$dir_array[$dir] = $dest_dir;
		}
		$this->data['directories'] = $dir_array;

		//initialize the form values
		$this->data['dest_path'] = $media_root;
		$this->data['create_entry'] = FALSE;

		// get max upload size from php.ini settings
		$max_bytes = get_max_upload_size();
		$this->data['max_size'] = resize_bytes($max_bytes);
		$config['max_size'] = $max_bytes * 1024;

		$config['allowed_types'] = 'gif|jpg|png|flv|mov|mp4';
		$this->data['allowed_types'] = str_replace('|',', ',$config['allowed_types']);

		// process form if submitted
		if($this->input->post('submit') != '') {

			if($this->input->post('create_entry') == 1) {
				$this->data['create_entry'] = TRUE;
			}

			$config['upload_path'] = $this->input->post('dest_path');
			$this->load->library('upload', $config);
			
			$success = $this->upload->do_upload();
			
			if ($success === FALSE)
			{
				$this->data['msg'] = $this->upload->display_errors('<p>', '</p>');
			}	
			else
			{

				$this->data['upload_data'] = $this->upload->data();
				if($this->data['upload_data']['is_image'] == 1)
				{
					$img_path = str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$this->data['upload_data']['full_path']);
					$this->data['image_thumb'] = view_image($img_path,300);
				}
			}

		}

		if($success === TRUE) {

			if($this->data['create_entry'] === TRUE) {
				// create entry in database for media
				
				/*$this->data['object_id'] = 3;
				$this->load->view('cms/content_edit', $this->data);*/
			} else {
				$this->load->view('cms/upload_success', $this->data);
			}

		} else {
			$this->load->view('cms/upload_form', $this->data);
		}


	}

	function manage() {
		$this->authenticate();
		
		$this->Media_model->media_lang = $this->Site_model->default_lang;

		$uri = $this->uri->segment_array();
		$dirA = array_slice($uri, 3);
		$displayDir = implode('/',$dirA);
		$myDir = '/'.$displayDir;

		if($this->input->post('directory') != '') {
			$myDir = $this->input->post('directory');
		}
		//get list of directories in media folder
		$media_root = $this->Media_model->dir_base;
		$this->Media_model->list_dirs();

		$dir_array['/'] = 'Media root';
		foreach($this->Media_model->dir_list as $index => $dir)
		{
			$this->Media_model->dir_current = $dir;
			$dest_dir = str_replace($media_root,'',$dir);
			$dest_display_dir = str_replace($media_root.'/','',$dir);

			$dir_array[$dest_dir] = $dest_display_dir;
		}


		$files = '';
		
		$attributes = array('class' => 'thumbnails');

		$this->Media_model->dir_current = $media_root.$myDir;
		$this->Media_model->list_files_unique();

		$temp_file_list = $this->Media_model->file_list;

		if($displayDir == '') {
			$displayDir = 'Media Root';
		}
		$files .= "<h2>$displayDir</h2>\n";
		if(is_array($temp_file_list) && count($temp_file_list) > 0) {
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
					echo $this->Media_model->file_uri." exists - id {$this->Media_model->media_id}<br />\r";
					
					$meta_exists = $this->Media_model->get_meta();
					if($meta_exists !== FALSE) {
						//echo $this->Media_model->file_uri." HAS METADATA: {$this->Media_model->data_id} <br />\r";
					} else {
						//echo $this->Media_model->file_uri." DOES NOT HAVE META <br />\r";
						// insert metadata to database
						$this->get_file_metadata();
						$this->Media_model->update_meta();
					}
					
				} else {
					//echo $this->Media_model->file_uri." DOES NOT EXIST <br />\r";
					// load file metadata and insert to database
					$this->get_file_metadata();
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
				
				$temp_code = "<div class=\"thumb\"><a href=\"{$tag_link}\">{$img_tag}</a></div>\r";
			
				$files_clean[] = $temp_code;
			}
			$files .= ul($files_clean, $attributes);
			unset($files_clean);


		} else {
			$files .= "<p>no media in directory</p>\n";
		}
		$this->data['page'] = $this->config->item('base_url').'cms/media/manage';
		$this->data['files'] = $files;
		$this->data['size'] = $this->Site_model->img_size_thumb;
		$this->data['dirs'] = form_dropdown('directory', $dir_array, $myDir);
		$this->load->view('cms/media', $this->data);
	}

	function edit() {

		$this->authenticate();
		
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

	function get_langs()
	{
		// get list of enabled languages
		$languages = $this->Language_model->get_list();
		foreach($languages as $row) {
			if($row['Native_Name'] != '') {
				$langArray[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$langArray[$row['ISO_639_3']] = $row['Inverted_Name'];
			}
		}
		return $langArray;

	}

	function get_file_metadata()
	{
		$file = $this->Media_model->dir_base.$this->Media_model->file_uri;

		$bname = basename($file);
		$fileArr = explode('.',$bname);
		$ext = array_pop  ( $fileArr);
		$filename = implode('.',$fileArr);

		$this->Media_model->read_file_info();

		if(is_array($this->Media_model->iptc)) {
			if(array_key_exists('2#105', $this->Media_model->iptc)) {
				$this->Media_model->title = $this->Media_model->iptc['2#105'][0];
			}
			if(array_key_exists('2#120', $this->Media_model->iptc)) {
				$this->Media_model->description = $this->Media_model->iptc['2#120'][0];
			}
			if(array_key_exists('2#025', $this->Media_model->iptc)) {
				$this->Media_model->keywords = implode(', ',$this->Media_model->iptc['2#025']);
			}
			if(array_key_exists('2#080', $this->Media_model->iptc)) {
				$this->Media_model->creator = $this->Media_model->iptc['2#080'][0];
			}
			if(array_key_exists('2#090', $this->Media_model->iptc)) {
				$this->Media_model->city = $this->Media_model->iptc['2#090'][0];
			}
			if(array_key_exists('2#095', $this->Media_model->iptc)) {
				$this->Media_model->prov_state = $this->Media_model->iptc['2#095'][0];
			}
			if(array_key_exists('2#101', $this->Media_model->iptc)) {
				$this->Media_model->country = $this->Media_model->iptc['2#101'][0];
			}
			if(array_key_exists('2#116', $this->Media_model->iptc)) {
				$this->Media_model->copyright = $this->Media_model->iptc['2#116'][0];
			}
		}
		if(is_array($this->Media_model->exif)) {
			if(array_key_exists('MimeType', $this->Media_model->exif)) {
				$this->Media_model->mime = $this->Media_model->exif['MimeType'];
			}
			if(array_key_exists('DateTimeOriginal', $this->Media_model->exif)) {
				$this->Media_model->created = $this->Media_model->exif['DateTimeOriginal'];
			}
		}

		if($this->Media_model->title == '') {
			$this->Media_model->title = $filename;
		}

	}

}
?>