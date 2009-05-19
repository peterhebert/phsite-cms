<?php

class Media extends Controller {
		
	function Media()
	{
		parent::Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->model('Site_model');
		$this->load->model('Media_model');
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
			$this->data['site_title'] = $this->Site_model->title;
		}

	}

	function index()
	{	
		$this->authenticate();

		//get list of directories in media folder
		$media_root = $this->Media_model->dir_base;
		$this->Media_model->list_dirs();
		$this->data['dir_list'] = $this->Media_model->dir_list;
		$this->data['media_root'] = $media_root;

		$this->load->view('cms/media',$this->data);
	}

	function media_list()
	{	
		$this->authenticate();

		//get list of directories in media folder
		$media_root = $this->Media_model->dir_base;
		$this->Media_model->list_dirs();
		foreach($this->Media_model->dir_list as $index => $dir)
		{
			$this->Media_model->dir_current = $dir;
			$this->Media_model->list_files();
			$dest_dir = str_replace($media_root,'',$dir);
			
			$dir_files[$dest_dir] = $this->Media_model->file_list;
		}

		$this->data['media_root'] = $media_root;
		$this->data['files'] = $dir_files;

		$this->load->view('cms/media',$this->data);
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

}
?>