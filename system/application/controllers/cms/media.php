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
		$media_root = $_SERVER["DOCUMENT_ROOT"].MEDIA_PATH_ORIG;
		$dir_array[$media_root] = MEDIA_PATH_ORIG;
		$subdirs = $this->findSubDirs($media_root);
		foreach($subdirs as $dir) {
			$dest_dir = str_replace($_SERVER["DOCUMENT_ROOT"],'',$dir);
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

	// find subdirectories
	function findSubDirs($filepath) {
		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$dirs = array();
	
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = $filepath.'/'.$file;
			$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
			if(!in_array($file,$ignore) && is_dir($newpath)) {
				$dirs[] = $newpath;
				$newdirs = $this->findSubDirs($newpath);
				if(is_array($newdirs) && count($newdirs) > 0) {
					$dirs = array_merge($dirs,$newdirs);
					unset($newdirs);
				}
			}
		}
		//closing the directory
		closedir($dir_handle);
		return $dirs;
	}

	// find media
	function find_media($filepath) {
		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$files = array();
	
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = $filepath.'/'.$file;
			$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");

			if(!in_array($file,$ignore) && !is_dir($newpath)) {
				$files[] = $newpath;
			}
		}
		//closing the directory
		closedir($dir_handle);
		return $files;
	}

	function manage() {
		$this->authenticate();
		
		$uri = $this->uri->segment_array();
		$dirA = array_slice($uri, 3);
		$myDir = '/'.implode('/',$dirA);

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

			$dir_array[$dest_dir] = $dest_dir;
		}


		$files = '';
		
		$attributes = array('class' => 'thumbnails');

		$this->Media_model->dir_current = $media_root.$myDir;
		$this->Media_model->list_files();

		$temp_file_list = $this->Media_model->file_list;

		if($myDir == '/') {
			$myDir = 'Media Root';
		}
		$files .= "<h2>$myDir</h2>\n";
		if(is_array($temp_file_list) && count($temp_file_list) > 0) {
			foreach($temp_file_list as $file) {
				$file_link = str_replace($_SERVER["DOCUMENT_ROOT"],'',$file);
				$tag_link = str_replace($media_root,'/cms/media/edit',$file);
				$img_tag = view_image($file_link,$this->Site_model->img_size_thumb,'',array('alt' => 'image: '.basename($file)));
				
				$temp_code = "<div class=\"thumb\"><a href=\"{$tag_link}\">{$img_tag}</a></div>\r";
			
				$files_clean[] = $temp_code;
			}
			$files .= ul($files_clean, $attributes);
			unset($files_clean);


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

		$raw_uri = $this->uri->uri_string();
		$processed_uri = str_replace('/cms/media/edit/','',$raw_uri);

		$file_link = '';
		$file = '';

		if(is_numeric($processed_uri)) {
			$this->Media_model->media_id = intval($processed_uri);
			$db_exists = $this->Media_model->get_media();

			if($db_exists === TRUE) {
				$file_link = $dir.$this->Media_model->file_uri;
				$file = $media_root.$this->Media_model->file_uri;
			}

		} else {

			$file_link = $dir.'/'.$processed_uri;
			$file = $media_root.'/'.$processed_uri;

			if( file_exists($file) ) {
				$this->Media_model->file_uri = '/'.$processed_uri;
				$db_exists = $this->Media_model->get_media_by_uri();
			}

		}
		if( file_exists($file) ) {
			$this->Media_model->read_file_info();
		} else {
			$this->Media_model->file_uri = '';
		}
		
		if($db_exists === TRUE) {
			$this->Media_model->get_meta();
		} else {
			$this->Media_model->media_id = '';
			
			if(file_exists($file) && is_array($this->Media_model->iptc)) {
	
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
			if(file_exists($file) && is_array($this->Media_model->exif)) {
				if(array_key_exists('MimeType', $this->Media_model->exif)) {
					$this->Media_model->mime = $this->Media_model->exif['MimeType'];
				}
				if(array_key_exists('DateTimeOriginal', $this->Media_model->exif)) {
					$this->Media_model->created = $this->Media_model->exif['DateTimeOriginal'];
				}
			}
			
			$this->Media_model->external = 0;
			$this->Media_model->media_lang = $this->Site_model->default_lang;
		}

		// start form
		$attributes = array('id' => 'media_edit');
		$content = form_open($raw_uri, $attributes);

		// hidden fields
		$content .= form_hidden('media_id', $this->Media_model->media_id);
		$content .= form_hidden('data_id', $this->Media_model->data_id);

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
		'name'        => 'creator',
		'id'          => 'creator',
		'class'	=> 'text',
		'value' => $this->Media_model->creator,
		);
		$content .= "<p>";
		$content .=  form_label('Creator', 'creator');
		$content .=  form_input($attributes);
		$content .= "</p>\n";

		$content .= form_fieldset_close(); 
		$content .= form_fieldset('Metadata');

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

		

		$content .= form_fieldset_close(); 
		$content .= form_close(); 

/*

	var $description;
	var $keywords;
	var $copyright;
	var $city;
	var $prov_state;
	var $country;


*/

		$this->data['form'] = $content;
		if( file_exists($file) ) {
			$this->data['thumb'] = view_image($file_link,$this->Site_model->img_size_thumb,'',array('alt' => 'image: '.basename($file)));
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

}
?>