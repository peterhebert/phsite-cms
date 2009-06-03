<?php
class Media_model extends Model {

	var $dir_base;
	var $dir_current;

	var $dir_list;
	var $dir_list_rel;

	var $file_list;

	var $media_id;
	var $file_uri;
	var $external;
	var $mime;
	var $created;
	var $creator;

	var $data_id;
	var $media_lang;
	var $title;
	var $uri_title;
	var $description;
	var $keywords;
	var $copyright;
	var $city;
	var $prov_state;
	var $country;

	var $iptc;
	var $exif;


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

    function update_media()
    {

		$data = array(
		'file_uri' => $this->file_uri,
		'external' => $this->external,
		'mime' => $this->mime,
		'created' => $this->created,
		'creator' => $this->creator,
		);

		if($this->media_id == '') {
			// insert
			if($this->db->insert('media', $data)){
				$this->media_id = $this->db->insert_id();
				return TRUE;
			}
		} else {
			// update
			$this->db->where('media_id', $this->media_id);
			if($this->db->update('media', $data)){
				return TRUE;
			}
		}

		return FALSE;
	}

    function update_meta()
    {

		$data = array(
		'media_id' => $this->media_id,
		'media_lang' => $this->media_lang,
		'title' => $this->title,
		'uri_title' => convert_uri($this->title),
		'description' => $this->description,
		'keywords' => $this->keywords,
		'copyright' => $this->copyright,
		'city' => $this->city,
		'prov_state' => $this->prov_state,
		'country' => $this->country
		);
		if($this->data_id == '') {
			// insert
			if($this->db->insert('media_data', $data)){
				$this->data_id = $this->db->insert_id();
				return TRUE;
			}
		} else {
			// update
			$this->db->where('data_id', $this->data_id);
			if($this->db->update('media_data', $data)){
				return TRUE;
			}
		}

		return FALSE;
    }


    function get_media()
    {

		// first get data from 'media' table
		$query = $this->db->get_where('media', array('media_id' => $this->media_id));
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->file_uri = $row->file_uri;
			$this->external = $row->external;
			$this->mime = $row->mime;
			$this->created = $row->created;
			$this->creator = $row->creator;
			return TRUE;
		} else {
			return FALSE;
		}

	}

    function get_media_by_uri()
    {

		// first get data from 'media' table
		$query = $this->db->get_where('media', array('file_uri' => $this->file_uri));
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->media_id = $row->media_id;
			$this->external = $row->external;
			$this->mime = $row->mime;
			$this->created = $row->created;
			$this->creator = $row->creator;
			return TRUE;
		} else {
			return FALSE;
		}

	}

    function get_meta()
    {
		$this->db->select('*');
		$this->db->from('media_data');

		if($this->data_id != '') {
			$this->db->where('data_id', $this->data_id);
		} else {
			$this->db->where('media_id', $this->media_id);
			$this->db->where('media_lang', $this->media_lang);
		}
		
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		
		   $this->data_id = $row->data_id;
		   $this->media_lang = $row->media_lang;
		   $this->title = $row->title;
		   $this->uri_title = $row->uri_title;
		   $this->description = $row->description;
		   $this->keywords = $row->keywords;
		   $this->copyright = $row->copyright;
		   $this->city = $row->city;
		   $this->prov_state = $row->prov_state;
		   $this->country = $row->country;
		}
	}

    function read_file_info()
    {
		$file_full = $this->dir_base.$this->file_uri;
		
		if(file_exists($file_full) && !is_dir($file_full)) {

			$size = getimagesize($file_full, $info);
			if(isset($info['APP13']))
			{
				$this->iptc = iptcparse($info['APP13']);
			} else {
				$this->iptc = FALSE;
			}
			$this->exif = exif_read_data($file_full);
	
		}
		
	}
}

?>