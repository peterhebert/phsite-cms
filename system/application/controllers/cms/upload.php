<?php

class Upload extends Controller {
	
	function Upload()
	{
		parent::Controller();
		$this->load->helper(array('form','url','file','image'));
	}
	
	function index()
	{	

		$media_root = $_SERVER["DOCUMENT_ROOT"].MEDIA_PATH_ORIG;
		$dir_array[$media_root] = MEDIA_PATH_ORIG;

		
		$subdirs = $this->findSubDirs($media_root);
		
		
		foreach($subdirs as $dir) {
			$dest_dir = str_replace($_SERVER["DOCUMENT_ROOT"],'',$dir);
			$dir_array[$dir] = $dest_dir;
		}

		$data['directories'] = $dir_array;
		$max = ini_get('upload_max_filesize');
		$max = preg_replace("/^([0-9]+)([KMG]*)/", "$1/$2", $max);
		$max_size_array = explode('/',$max);
		
		switch($max_size_array[1]) {
		case 'K':
			$max_size = $max_size_array[0] * 1024;
			break;
		case 'M':
			$max_size = $max_size_array[0] * 1048576;
			break;
		case 'G':
			$max_size = $max_size_array[0] * 1073741824;
			break;
		default:
			$max_size = $max_size_array[0];
			break;
		}
		
		$data['max_size'] = $max_size;
		$data['msg'] = "Note: The maximum file upload size is ".;

		$this->load->view('cms/upload',$data);
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

	function do_upload()
	{
		//get max file size
		$max = ini_get('upload_max_filesize');
		$max = preg_replace("/^([0-9]+)([KMG]*)/", "$1/$2", $max);
		$max_size_array = explode('/',$max);
		
		switch($max_size_array[1]) {
		case 'K':
			$max_size = $max_size_array[0] * 1024;
			break;
		case 'M':
			$max_size = $max_size_array[0] * 1048576;
			break;
		case 'G':
			$max_size = $max_size_array[0] * 1073741824;
			break;
		default:
			$max_size = $max_size_array[0];
			break;
		}
		
		$data['max_size'] = $max_size;










		$config['upload_path'] = $this->input->post('dest_path');
		$config['allowed_types'] = 'gif|jpg|png|flv|mov|mp4';
		$config['max_size']	= ini_get('upload_max_filesize');
		$config['max_width']  = '1024';
		$config['max_height']  = '768';
		
		$this->load->library('upload', $config);
	
		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());
			
			$this->load->view('upload_form', $error);
		}	
		else
		{
			$data = array('upload_data' => $this->upload->data());
			
			$this->load->view('upload_success', $data);
		}
	}	
}
?>