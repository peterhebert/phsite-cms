<?php
class Media_model extends Model {

	var $dir_base;
	var $dir_current;

	var $dir_list;
	var $dir_list_rel;

	var $file_list;
	var $mime;

    function Media_model()
    {
        // Call the Model constructor
        parent::Model();

		$this->dir_base = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG;
		$this->dir_current = $this->dir_base;
    }

	// get list of subdirectories
	function list_dirs($filepath='') {
		if($filepath == '')
		{
			$filepath = $this->dir_base;
		}
		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$dirs = array();
		$dirs_rel = array();
	
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = $filepath.'/'.$file;
			$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
			if(!in_array($file,$ignore) && is_dir($newpath)) {
				$dirs[] = $newpath;
				$newdirs = $this->list_dirs($newpath);
				if(is_array($newdirs) && count($newdirs) > 0) {
					$dirs = array_merge($dirs,$newdirs);
					unset($newdirs);
				}
			}
		}
		//closing the directory
		closedir($dir_handle);

		foreach($dirs as $dir) {
			$dest_dir = str_replace($_SERVER["DOCUMENT_ROOT"],'',$dir);
			$dirs_rel[$dir] = $dest_dir;
		}

		$this->dir_list = $dirs;
		$this->dir_list_rel = $dirs_rel;
	}

	// list files in directory
	function list_files($filepath='') {

		if($filepath == '')
		{
			$filepath = $this->dir_current;
		}

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

		$this->file_list = $files;

	}

	function list_files_unique($filepath='') {
		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$bases = array();
		$files = array();
	
		$imageTypes = array("jpg","gif","png");
	
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = $filepath.'/'.$file;
	
			$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
			if(!in_array($file,$ignore) && !is_dir($newpath)) {
				$pi = pathinfo($file);
				$fnames[] = $pi['filename'];
				$exts[] = $pi['extension'];
				$files[] = $file;
			}
		}
		//closing the directory
		closedir($dir_handle);
		
		if(is_array($fnames)) {
			$count = array_count_values($fnames);
			foreach($count as $fname => $times) {
		
				$duplicates = array_keys( $fnames, $fname );
				if($times > 1) {
					foreach($duplicates as $key) {
						if(!in_array($exts[$key],$imageTypes)) {
							$insertfiles[] = $files[$key];
						}
					}
				} else {
					$insertfiles[] = $files[$duplicates[0]];
				}
		
			}
		}
		
		$this->file_list = $insertfiles;
	}





}

?>
