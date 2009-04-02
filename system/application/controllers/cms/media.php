<?php

class Media extends Controller {
		
	function Media()
	{
		parent::Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->library('session');
	}
	
	function index()
	{	
		// if not logged in, redirect to login form
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
		}

		//get list of directories in media folder
		$media_root = realpath($_SERVER['DOCUMENT_ROOT']).MEDIA_PATH_ORIG;
		$dir_files[$media_root] = $this->find_media($media_root);

		$subdirs = $this->findSubDirs($media_root);
		foreach($subdirs as $dir) {
			$dest_dir = str_replace($media_root,'',$dir);
			$dir_files[$dest_dir] = $this->find_media($dir);
		}

		$this->data['media_root'] = $media_root;
		$this->data['files'] = $dir_files;

		$this->load->view('cms/media',$this->data);
	}

	function upload()
	{	

		// if not logged in, redirect to login form
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
		}

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
		$max = ini_get('upload_max_filesize');
		$max = preg_replace("/^([0-9]+)([KMG]*)/", "$1/$2", $max);
		$max_size_array = explode('/',$max);
		
		switch($max_size_array[1]) {
		case 'K':
			$this->data['max_size'] = $max_size_array[0].' Kb';
			$config['max_size'] = $max_size_array[0];
			break;
		case 'M':
			$this->data['max_size'] = $max_size_array[0].' Mb';
			$config['max_size'] = $max_size_array[0] * 1024;
			break;
		case 'G':
			$this->data['max_size'] = $max_size_array[0].' Gb';
			$config['max_size'] = $max_size_array[0] * 1048576;
			break;
		default:
			$this->data['max_size'] = $max_size_array[0].' bytes';
			$config['max_size'] = $max_size_array[0] / 1024;
			break;
		}

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
			$this->load->view('cms/upload_success', $this->data);
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