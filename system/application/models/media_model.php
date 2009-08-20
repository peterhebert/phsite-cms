<?php
class Media_model extends MY_Model {

	// media variables
	var $media_id, $file_uri, $external, $mime_type, $date_created, $creator;
	var $media_title_id, $title, $uri_title, $subtitle;
	//var $media_meta_id, $field_id, $field_name, $value;
	var $date_updated, $media_lang;

	// misc utility variables
	var $media_list = array(), $media_metadata = array(), $media_meta_fields = array();
	var $mime_categories = array('application','audio','image','text','video');

	// iptc / exif metadata
	var $iptc, $exif;

	// directory / file managment 
	var $dir_base, $dir_current, $dir_list, $dir_list_rel, $file_list, $file_count;

    function Media_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->dir_base = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG;
		$this->dir_current = $this->dir_base;

		$this->config->load('phsite_prefs');
		$this->mime_types = $this->config->item('mime_types');

    }
	
// directory / file functions
	
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
		
		$this->file_count = count($files);
		$this->file_list = $files;

	}

	function list_files_unique($filepath='') {
		
		$this->list_files($filepath);

		$files = $this->file_list;
		$new_list = array();
		$imageTypes = array("jpg","gif","png");
	
		$fnames = array();
		$exts = array();

		//running the while loop
		foreach ($files as $file) {
			$pi = pathinfo($file);
			$fnames[] = $pi['filename'];
			$exts[] = strtolower($pi['extension']);
		}
		
		$unique_names = array_count_values($fnames);
		
		foreach($unique_names as $uname => $times) {
			$matching_keys = array_keys( $fnames, $uname);
			if($times > 1) {
				foreach($matching_keys as $key) {
					
					if(!in_array($exts[$key],$imageTypes)) {
						$newfile = $files[$key];
					}
				}
				if(!isset($newfile)) {
					$newfile = $files[$matching_keys[0]];
				}

			} else {
				$newfile = $files[$matching_keys[0]];
			}
			$new_list[] = $newfile;
			unset($newfile);
		}

		$this->file_count = count($new_list);
		$this->file_list = $new_list;
	}

    function read_file_info()
    {

		$file_full = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG.$this->file_uri;
		if(file_exists($file_full) && !is_dir($file_full)) {

			$size = getimagesize($file_full, $info);

			if(isset($info['APP13']))
			{
				$this->iptc = iptcparse($info['APP13']);
			} else {
				$this->iptc = FALSE;
			}
			$test_exif = @exif_read_data($file_full);
			if(is_array($test_exif)) {
				$this->exif = $test_exif;
			} else {
				$this->exif = FALSE;
			}
		}
		
	}

	function get_mime($filename) {
		if(file_exists($filename) && !is_dir($filename))
		{

			$mime = exif_imagetype($filename);

			$ext = strrchr($filename, '.');
			$ext = substr($ext,1,strlen($ext)-1);
			$ext = strtolower($ext);

			if(array_key_exists($ext,$this->media_types))
			{
				$mime_ext = $this->media_types[$ext];

				if ($mime != FALSE )
				{
					return $mime;
				} else {
					return $mime_ext;
				}
				
			} else {
				if ($mime != FALSE)
				{
					return $mime;
				} else {
					return 'application/octet-stream';
				}
			}

		}

		return FALSE;
	}

	function get_file_metadata()
	{
		$this->read_file_info();

		if(is_array($this->iptc)) {

			if(array_key_exists('2#005', $this->iptc)) {
				$this->title = $this->iptc['2#005'][0];
			}
			else if(array_key_exists('2#105', $this->iptc))
			{
				$this->title = $this->iptc['2#105'][0];
			}
			
			if(array_key_exists('2#080', $this->iptc)) {
				$this->creator = $this->iptc['2#080'][0];
			}

			/*if(array_key_exists('2#120', $this->iptc)) {
				$this->description = $this->iptc['2#120'][0];
			}
			if(array_key_exists('2#025', $this->iptc)) {
				$this->keywords = implode(', ',$this->iptc['2#025']);
			}
			if(array_key_exists('2#090', $this->iptc)) {
				$this->city = $this->iptc['2#090'][0];
			}
			if(array_key_exists('2#095', $this->iptc)) {
				$this->prov_state = $this->iptc['2#095'][0];
			}
			if(array_key_exists('2#101', $this->iptc)) {
				$this->country = $this->iptc['2#101'][0];
			}
			if(array_key_exists('2#116', $this->iptc)) {
				$this->copyright = $this->iptc['2#116'][0];
			}*/
		}
		
		if(is_array($this->exif)) {
			if(array_key_exists('MimeType', $this->exif)) {
				$this->mime_type = $this->exif['MimeType'];
			}
			if(array_key_exists('DateTimeOriginal', $this->exif)) {
				$this->created = $this->exif['DateTimeOriginal'];
			}
		}

		if($this->title == '') {
			$this->title = basename($this->file_uri);
		}
		
		if($this->date_created == '') {
			$this->date_created = date('Y-m-d H:i:s');
		}

	}



// database functions
	function update_media_db()
	{
		foreach($this->file_list as $file)
		{
			$this->file_uri = str_replace($this->dir_base,'',$dir);
			// if file not in database, add to database
			if(!$this->find_media_by_uri())
			{
				$this->external = 0;
				$this->mime_type = $this->get_mime($file);
				$this->date_created = $row->date_created;
				$this->creator = $row->creator;
			}

		}
	}

	function find_media_by_uri()
	{
		$this->db->where('file_uri',$this->file_uri);
		$this->db->limit(1);
		$query = $this->db->get('media');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->media_id = $row->media_id;
			$this->file_uri = $row->file_uri;
			$this->external = $row->external;
			$this->mime_type = $row->mime_type;
			$this->date_created = $row->date_created;
			$this->creator = $row->creator;
			return TRUE;
		} 

		return FALSE;
	}
	
    // list all available fields and see if they are assigned as media metadata
	function list_meta_fields()
    {

		$list = array();
		
		// get lst of all fields
		$query = $this->db->get('fields');
		$field_list = $query->result_array();


/*
CREATE TABLE `fields` (
  `field_id` mediumint(9) unsigned NOT NULL auto_increment,
  `field_ref_name` text NOT NULL,
  `field_input_type` varchar(9) NOT NULL,
  `field_validation` text,
  `field_needs_lang` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8
*/
		foreach($this->mime_categories as $mime_cat)
		{
			$this->db->where('mime_category',$mime_cat);
		}
/*CREATE TABLE `r_mime_fields` (
  `mime_category` varchar(12) default 'image/jpeg',
  `field_id` mediumint(8) unsigned NOT NULL,
  `required` tinyint(1) NOT NULL default '0',
  `order` smallint(5) default '0',
  KEY `mime_category` (`mime_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='media file info'
*/
		$this->media_meta_fields = $list;

	}

	
}

?>