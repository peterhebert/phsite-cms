<?php
class Media_model extends MY_Model {

	// media variables
	var $media_id, $file_uri, $external, $mime_type, $date_created, $publish;

	//var $media_meta_id, $field_id, $field_name, $value;
	var $date_updated, $media_lang;

	// misc utility variables
	var $media_list = array(), $media_metadata = array(), $media_meta_fields = array();
	var $mime_categories = array('application','audio','image','text','video');
	var $cms_labels;

	// iptc / exif metadata
	var $iptc, $exif;

	// directory / file managment 
	var $dir_base, $dir_current, $dir_list, $dir_list_rel, $file_current, $file_list, $file_count;

    function Media_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->dir_base = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG;
		$this->dir_current = $this->dir_base;

		$this->config->load('phsite_prefs');
		$this->mime_types = $this->config->item('mime_types');
		$this->scope = 'media';

		$this->load_cms_labels();
	}
	
	function load_cms_labels() {
		$this->lang_load();
		$this->lang->load('phsite', $this->lang_CI_Name);
		$this->cms_labels = $this->lang->line('cms_labels');
	}
	
	// get list of subdirectories
	function list_dirs() {
		$filepath = $this->dir_current;
		$dirs = array();
		$dirs_rel = array();
		
		$dir_handle = @opendir($filepath) or die('cannot open '.$filepath);
		if($dir_handle)
		{

			//running the while loop
			while ($file = readdir($dir_handle)) {

				$newpath = $filepath.'/'.$file;
				$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
				if(!in_array($file,$ignore) && is_dir($newpath)) {
					$dirs[] = $newpath;
					$this->dir_current = $newpath;
					$newdirs = $this->list_dirs();
					if(is_array($newdirs) && count($newdirs) > 0) {
						$dirs = array_merge($dirs,$newdirs);
						unset($newdirs);
					}
				}
			}
			// closing the directory
			closedir($dir_handle);

		
			// get relative paths for each directory
	
			foreach($dirs as $dir) {
				$dest_dir = str_replace($_SERVER["DOCUMENT_ROOT"],'',$dir);
				$dirs_rel[$dir] = $dest_dir;
			}
	
			$this->dir_list = $dirs;
			$this->dir_list_rel = $dirs_rel;
		}
		else
		{
			$this->messages[] = sprintf($this->cms_labels['file_open_failure'],$filepath);
		}

	}

	// list files in directory
	function list_files() {

		$filepath = $this->dir_current;

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

	function list_files_unique() {
		
		$this->list_files();

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

		if($this->file_current == '')
		{
			$this->file_current = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG.$this->file_uri;
		}
		if(file_exists($this->file_current) && !is_dir($this->file_current)) {

			$size = getimagesize($this->file_current, $info);

			if(isset($info['APP13']))
			{
				$this->iptc = iptcparse($info['APP13']);
			} else {
				$this->iptc = FALSE;
			}
			$test_exif = @exif_read_data($this->file_current);
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
			$imagetype = exif_imagetype($filename);

			if ($imagetype)
			{
				switch ($imagetype)
				{
					case 1: // IMAGETYPE_GIF
						return 'image/gif';
						break;
					case 2: // 	IMAGETYPE_JPEG
						return 'image/jpeg';
						break;
					case 3: // 	IMAGETYPE_PNG
						return 'image/png';
						break;
					case 4: // 	IMAGETYPE_SWF
						return 'application/x-shockwave-flash';
						break;
					case 5: // 	IMAGETYPE_PSD
						return 'image/psd';
						break;
					case 6: // 	IMAGETYPE_BMP
						return 'image/x-windows-bmp';
						break;
					case 7: // 	IMAGETYPE_TIFF_II (intel byte order)
					case 8: // 	IMAGETYPE_TIFF_MM (motorola byte order)
						return 'image/tiff';
						break;
					case 10: // 	IMAGETYPE_JP2
						return 'image/jp2';
						break;
					case 13: // 	IMAGETYPE_SWC
						return 'application/x-shockwave-flash';
						break;
					case 14: // 	IMAGETYPE_IFF
						return 'image/iff';
						break;
					case 15: // 	IMAGETYPE_WBMP
						return 'image/vnd.wap.wbmp';
						break;
					case 16: // 	IMAGETYPE_XBM
						return 'image/xbm';
						break;
					default:
						$imagetype = FALSE;
						break;
				}
			}

			if (!$imagetype)
			{
				$fileinfo = pathinfo($filename);
				if($fileinfo)
				{
					$ext = $fileinfo['extension'];
					if(array_key_exists($ext,$this->mime_types))
					{
						return $this->mime_types[$ext];
					}
				}
			}
		}
		return 'application/octet-stream';
	}

	// database functions
	function update_media_batch()
	{
		foreach($this->file_list as $file)
		{
			$this->media_id = '';
			$this->date_created = '';

			$this->file_uri = str_replace($this->dir_base,'',$file);
			$file_type = $this->get_mime($file);

			$this->file_current = $file;
			$this->read_file_info();
			if(is_array($this->exif) && array_key_exists('DateTimeOriginal', $this->exif)) {
				$this->date_created = $this->exif['DateTimeOriginal'];
			}
			else
			{
				$this->date_created = date('Y-m-d H:i:s');
			}

			// if file not in database, add to database
			if(!$this->find_media_by_uri())
			{
				$this->external = 0;
				$this->mime_type = $file_type;
				$this->publish = 1;
			}

			$this->update_media_single();

		}
	}

	function update_media_single()
	{

		$data = array(
		'file_uri' => $this->file_uri,
		'external' => $this->external,
		'mime_type' => $this->mime_type,
		'date_created' => $this->date_created,
		'publish' => $this->publish,
		);

		if( $this->media_id != '' )
		{
			// update
			$this->db->where('media_id', $this->media_id);
			if($this->db->update('c_media', $data))
			{
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('c_media', $data))
			{
				$this->media_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

	}

	// functions to deal with database entries for media
	function find_media_by_uri()
	{
		$this->db->where('file_uri',$this->file_uri);
		$this->db->limit(1);
		$query = $this->db->get('c_media');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->media_id = $row->media_id;
			$this->file_uri = $row->file_uri;
			$this->external = $row->external;
			$this->mime_type = $row->mime_type;
			$this->date_created = $row->date_created;
			$this->publish = $row->publish;
			return TRUE;
		}

		return FALSE;
	}

	function list_media($limit=FALSE)
	{
		$list = array();
		
		if ($limit)
		{
			$this->db->limit('c_media');
		}
		$query = $this->db->get('c_media');
		
		if ($query->num_rows() > 0)
		{

			foreach ($query->result_array() as $row)
			{
				$tmp_media = array();

				$tmp_media['media_id'] = $row['media_id'];
				$tmp_media['file_uri'] = $row['file_uri'];
				$tmp_media['external'] = $row['external'];
				$tmp_media['mime_type'] = $row['mime_type'];
				$tmp_media['date_created'] = $row['date_created'];
				$tmp_media['publish'] = $row['publish'];

				$this->scope = 'media';
				$this->rel_id = $row['media_id'];
				$this->load_title();
				
				$tmp_media['title_id'] = $this->title_id;
				$tmp_media['title'] = $this->title;
				$tmp_media['uri_title'] = $this->uri_title;
				$tmp_media['subtitle'] = $this->subtitle;
				$tmp_media['keywords'] = $this->keywords;
				$tmp_media['lang_ISO_639_3'] = $this->temp_lang;
				

				$this->temp_id = $row['media_id'];
				$tmp_media['attached_to'] = $this->media_attached_to();

				$list[] = $tmp_media;

			}
		}

		$this->media_list = $list;
		$this->file_count = $query->num_rows();
	}

	function media_attached_to()
	{
		$attached_to = array();
		
		$this->db->where('media_id',$this->temp_id);
		$query = $this->db->get('r_attachments');
		
		if ($query->num_rows() > 0)
		{
			$attached_to = $query->result_array();
		}

		return $attached_to;
	}

	function count_media()
	{
		$this->file_count = $this->db->count_all('c_media');
	}

	function load_media()
	{
		$this->db->where('media_id',$this->temp_id);
		$query = $this->db->get('c_media');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->media_id = $row->media_id;
			$this->file_uri = $row->file_uri;
			$this->external = $row->external;
			$this->mime_type = $row->mime_type;
			$this->date_created = $row->date_created;
			$this->publish = $row->publish;
			return TRUE;
		}

		return FALSE;
	}

/*

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
			
//			
//			
//			if(array_key_exists('2#080', $this->iptc)) {
//				$this->creator = $this->iptc['2#080'][0];
//			}
//
//			if(array_key_exists('2#120', $this->iptc)) {
//				$this->description = $this->iptc['2#120'][0];
//			}
//			if(array_key_exists('2#025', $this->iptc)) {
//				$this->keywords = implode(', ',$this->iptc['2#025']);
//			}
//			if(array_key_exists('2#090', $this->iptc)) {
//				$this->city = $this->iptc['2#090'][0];
//			}
//			if(array_key_exists('2#095', $this->iptc)) {
//				$this->prov_state = $this->iptc['2#095'][0];
//			}
//			if(array_key_exists('2#101', $this->iptc)) {
//				$this->country = $this->iptc['2#101'][0];
//			}
//			if(array_key_exists('2#116', $this->iptc)) {
//				$this->copyright = $this->iptc['2#116'][0];
//			}
//			
//			
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


	function load_media()
    {

		$this->db->where('media_id', $this->temp_id);
		$query = $this->db->get('c_media');
 
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->media_id = $row->media_id;
			$this->file_uri = $row->file_uri;
			$this->external = $row->external;
			$this->mime_type = $row->mime_type;
			$this->date_created = $row->date_created;
			$this->publish = $row->publish;
			return TRUE;
		} 
		return FALSE;

    }
*/	
/*




    function load_content()
    {
		$this->load_content_object();
		$this->temp_id = $this->content_id;
		$this->load_title();
		
		if($this->temp_value != '')
		{
			$this->type_id = $this->temp_value;
		}
		
		$this->temp_id = $this->type_id;
		$this->scope = 'types';
		
		$this->list_fields_related();
		$this->scope = 'content';
		$this->temp_id = $this->content_id;
		$this->load_metadata();

	}
*/	

	// list files in directory
	function list_recursive() {

		$filepath = $this->dir_current;
		$dirs = array();
		$dirs_rel = array();
		
		$dir_handle = @opendir($filepath) or die('cannot open '.$filepath);
		if($dir_handle)
		{

			while ($file = readdir($dir_handle)) {

				$newpath = $filepath.'/'.$file;

				$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
				if(!in_array($file,$ignore) ) {

					if(is_dir($newpath))
					{

						$this->dir_current = $newpath;
						$newdirs = $this->list_recursive();
						$dirs[$newpath] = $newdirs;
						unset($newdirs);
					}
					else
					{
						$dirs[$newpath] = $file;
					}


				}
			}
			// closing the directory
			closedir($dir_handle);

		
			// get relative paths for each directory
	
			foreach($dirs as $dir) {
				$dest_dir = str_replace($_SERVER["DOCUMENT_ROOT"],'',$dir);
				//$dirs_rel[$dir] = $dest_dir;
			}
	
		}

		return $dirs;

	}


}

?>