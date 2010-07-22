<?php
class Media_model extends MY_Model {

	// media variables
	var $media_id, $file_uri, $external, $mime_type, $date_created, $publish;

	var $date_updated;

	// misc utility variables
	var $media_list = array(), $media_metadata = array(), $media_meta_fields = array();
	var $mime_categories = array('image','audio','video','text','application');
	var $cms_labels;
	var $max_upload_bytes;
	var $max_upload_display;

	// iptc / exif metadata
	var $iptc, $exif;

	// directory / file managment 
	var $dir_base, $dir_current, $dir_list, $dir_list_rel, $file_current, $file_list, $file_count;

	// track db entries
	var $exists = FALSE;

    function Media_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->dir_base = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG;
		$this->dir_current = $this->dir_base;

		$this->config->load('mime_info');
		$this->mime_types = $this->config->item('mime_types');
		$this->scope = 'media';

		$this->load_cms_labels();
	}

	function calculate_max_size()
	{
		$this->lang->load('mime', $this->lang_CI_Name);
		$abbr = $this->lang->line('file_size_abbr');

		$max = ini_get('upload_max_filesize');
		$max = preg_replace("/^([0-9]+)([KMGTPEZY]*)/", "$1/$2", $max);
		$max_size_array = explode('/',$max);
		
		switch($max_size_array[1]) {
		case 'K':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Ki'];
			$this->max_upload_bytes = $max_size_array[0];
			break;
		case 'M':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Mi'];
			$this->max_upload_bytes = $max_size_array[0] * 1024;
			break;
		case 'G':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Gi'];
			$this->max_upload_bytes = $max_size_array[0] * pow(1024, 2);
			break;
		case 'T':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Ti'];
			$this->max_upload_bytes = $max_size_array[0] * pow(1024, 3);
			break;
		case 'P':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Pi'];
			$this->max_upload_bytes = $max_size_array[0] * pow(1024, 4);
			break;
		case 'E':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Ei'];
			$this->max_upload_bytes = $max_size_array[0] * pow(1024, 5);
			break;
		case 'Z':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Zi'];
			$this->max_upload_bytes = $max_size_array[0] * pow(1024, 6);
			break;
		case 'Y':
			$this->max_upload_display = $max_size_array[0].' '.$abbr['Yi'];
			$this->max_upload_bytes = $max_size_array[0] * pow(1024, 7);
			break;
		default:
			$this->max_upload_display = $max_size_array[0].' '.$abbr['bytes'];
			$this->max_upload_bytes = $max_size_array[0] / 1024;
			break;
		}

	}
	
	function load_cms_labels() {
		$this->lang_load();
		$this->lang->load('cms', $this->lang_CI_Name);
		$this->cms_labels = $this->lang->line('cms_labels');
	}
	
	// get list of subdirectories
	function list_dirs() {
		if($this->dir_current == '')
		{
			$this->dir_current = $this->dir_base;
		}
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
			
			return $dirs;
	
		}
		else
		{
			$this->messages[] = sprintf($this->cms_labels['file_open_failure'],$filepath);
			return FALSE;
		}

	}

	function list_dirs_display()
	{
		$dirs = $this->list_dirs();
		$dirs_rel = array();
		
		$media_root = $this->dir_base;
		
		$dirs_rel['/'] = $this->cms_labels['media_root'];
		if($dirs)
		{
			// get relative paths for each directory
			foreach($dirs as $dir) {
				$dest_dir = str_replace($media_root,'',$dir);
				$dirs_rel[$dest_dir] = $dest_dir;
			}

			$this->dir_list = $dirs;

		}
		else
		{
			$this->dir_list = array($media_root);
		}
		$this->dir_list_rel = $dirs_rel;

	}

	// list files in directory
	function list_files($include_dirs=FALSE) {

		$filepath = $this->dir_current;

		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$files = array();
		
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = str_replace('//','/',$filepath.'/'.$file);
			$ignore = array(".","..",".DS_Store",".localized",".htaccess",".svn");
			if(!in_array($file,$ignore)) {

//			$ignore = preg_match("#^\..*$#",$file);
//			if($ignore > 0) {

				if($include_dirs)
				{
					$files[] = $newpath;
				}
				else
				{
					if(!is_dir($newpath)) {
						$files[] = $newpath;
					}
				}
			}

		}
		//closing the directory
		closedir($dir_handle);

		$this->file_count = count($files);
		$this->file_list = $files;

	}

	function list_files_unique($include_dirs=FALSE) {
		
		if($include_dirs)
		{
			$this->list_files(TRUE);
		}
		else
		{
			$this->list_files();
		}

		$files = $this->file_list;
		$new_list = array();
		$imageTypes = array("jpg","gif","png");
	
		$fnames = array();
		$exts = array();

		//running the while loop
		foreach ($files as $file) {
			if(is_dir($file))
			{
				$new_list[] = $file;
			}
			else
			{
				$pi = pathinfo($file);
				$only_files[] = $file;
				$fnames[] = $pi['filename'];
				$exts[] = strtolower($pi['extension']);
			}
		}

		$unique_names = array_count_values($fnames);
		
		foreach($unique_names as $uname => $times) {
			$matching_keys = array_keys( $fnames, $uname);
			if($times > 1) {
				foreach($matching_keys as $key) {
					if(!in_array($exts[$key],$imageTypes)) {
						$newfile = $only_files[$key];
					}
				}
				if(!isset($newfile)) {
					$newfile = $only_files[$matching_keys[0]];
				}

			} else {
				$newfile = $only_files[$matching_keys[0]];
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
			$this->file_current = realpath(ROOT_REL).MEDIA_PATH_ORIG.$this->file_uri;
		}
		if(file_exists($this->file_current) && !is_dir($this->file_current)) {

			$size = @getimagesize($this->file_current, $info);

			if(isset($info['APP13']))
			{
				$this->iptc = @iptcparse($info['APP13']);
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

	function get_mime() {
		$filename = $this->file_current;
		if(file_exists($filename) && !is_dir($filename))
		{
			$imagetype = @exif_imagetype($filename);

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
				}
			}

			if ($imagetype == FALSE)
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
			$this->title_id = '';
			$this->title = '';
			$this->title_rel_id = '';

			$this->file_uri = str_replace($this->dir_base,'',$file);
			$file_type = $this->get_mime($file);

			$this->file_current = $file;
			$this->update_media_from_file();
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

	function list_media()
	{
		$list = array();
		
		if ($this->limit > 0)
		{
			if($this->page > 1)
			{
				$start = ($this->page * $this->limit) - $this->limit;
				$this->db->limit($this->limit,$start);
			}
			else
			{
				$this->db->limit($this->limit);
			}
		}

		if ($this->date_filter != '')
		{
			$filter = parse_date_filter($this->date_filter);
			if($filter)
			{
				$this->db->where($filter);
			}
		}

		if ($this->temp_value != '')
		{
			$this->db->like('mime_type', $this->temp_value, 'after');
		}

		// exclude certain ids if supplied
		if(is_array($this->media_list) && count($this->media_list) > 0)
		{
			foreach($this->media_list as $id)
			{
				$this->db->where('media_id !=', $id);
			}
		}

		$this->db->order_by("file_uri", "asc");
		$query = $this->db->get('c_media');
		
		if ($query->num_rows() > 0)
		{

			foreach ($query->result_array() as $row)
			{
				$this->reset_media_vars();
				$tmp_media = array();

				$tmp_media['media_id'] = $row['media_id'];
				$tmp_media['file_uri'] = $row['file_uri'];
				$tmp_media['external'] = $row['external'];
				$tmp_media['mime_type'] = $row['mime_type'];
				$tmp_media['date_created'] = $row['date_created'];
				$tmp_media['publish'] = $row['publish'];

				$this->scope = 'media';
				$this->temp_id = $row['media_id'];
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

	function count_media_all()
	{
		
		if ($this->date_filter != '')
		{
			$filter = parse_date_filter($this->date_filter);
			if($filter)
			{
				$this->db->where($filter);
			}
		}

		if ($this->temp_value != '')
		{
			$this->db->like('mime_type', $this->temp_value, 'after');
		}

		// exclude certain ids if supplied
		if(is_array($this->media_list) && count($this->media_list) > 0)
		{
			foreach($this->media_list as $id)
			{
				$this->db->where('media_id !=', $id);
			}
		}

		$query = $this->db->get('c_media');
		
		return $query->num_rows();
		
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

	function load_type_by_mime()
	{
		$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->mime_type);

		$this->db->where('scope','media');
		$this->db->where('type_uri_name',$mime_cat);
		$query = $this->db->get('s_types');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->type_id = $row->type_id;
			$this->scope = $row->scope;
			$this->type_uri_name = $row->type_uri_name;
			return TRUE;
		}

		return FALSE;
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

	function get_file_metadata()
	{
		// first load metadata fields by mime type
		$this->load_type_by_mime();

		$field_name_to_iptc = array(
		'copyright' => '2#116',
		'creator' => '2#080',
		'description' => '2#120',
		'location' => '2#092',
		'city' => '2#090',
		'province_state' => '2#095',
		'country' => '2#101',
		);

		$this->scope = 'types';
		$this->temp_id = $this->type_id;
		$this->list_fields_related();
		
		$meta = array();
		if($this->media_id != '')
		{
			$this->scope = 'media';
			$this->temp_id = $this->media_id;
			$this->load_metadata();
			$meta = $this->metadata;
		}
		else
		{
			$meta = $this->field_list;
		}
		
		if(is_array($meta) && count($meta) > 0)
		{
			foreach($meta as $index => $info)
			{
				
				if(array_key_exists($info['field_ref_name'],$field_name_to_iptc))
				{
					$iptc_key = $field_name_to_iptc[$info['field_ref_name']];
					if(is_array($this->iptc) && array_key_exists($iptc_key, $this->iptc)) {
						$meta[$index]['value'] = $this->iptc[$iptc_key][0];
						if($this->media_id != '')
						{
							$meta[$index]['rel_id'] = $this->media_id;
						}
					}
				}
	
			}
		}


		$this->metadata = $meta;
		
		// now get title and keywords
		if(is_array($this->iptc) && array_key_exists('2#005', $this->iptc)) {
			$this->title = $this->iptc['2#005'][0];
		}
		else if(is_array($this->iptc) && array_key_exists('2#105', $this->iptc))
		{
			$this->title = $this->iptc['2#105'][0];
		}
		
		if(is_array($this->iptc) && array_key_exists('2#025', $this->iptc)) {
			$kw = $this->iptc['2#025'];
			natcasesort($kw);
			$kw_unique = array_unique($kw);
			$this->keywords = implode(', ',$kw_unique);
		}
				
	}

	// list all files, and add to db if not present
	function list_media_recursive($dir=NULL) {
		if(isset($dir))
		{
			$this->dir_current = $dir;
		}
		else
		{
			$this->dir_current = $this->dir_base;
		}

		$list = array();
		$new_list = array();

		// get files in this directory (unique)
		$this->list_files_unique(TRUE);
		$files = $this->file_list;
		foreach($files as $file)
		{
			$this->reset_media_vars();

			$tmp_file_info = array('type' => 'file');
			$path_rel = str_replace($this->dir_base,'',$file);
			
			if(is_dir($file))
			{
				$tmp_file_info['type'] = 'dir';
				$tmp_file_info['files'] = $this->list_media_recursive($file);
			}
			else
			{
				$tmp_file_info['type'] = 'file';
				$this->file_current = $file;
				$this->file_uri = $path_rel;
				$this->update_media_from_file();
				$tmp_file_info['media_id'] = $this->media_id;
				$tmp_file_info['file_uri'] = $this->file_uri;
				$tmp_file_info['external'] = 0;
				$tmp_file_info['mime_type'] = $this->mime_type;
				$tmp_file_info['mime_cat'] = preg_replace("#^([a-z]+)/.*$#","$1",$this->mime_type);

				//$tmp_file_info[''] = $this->;
				//$tmp_file_info[''] = $this->;
				$tmp_file_info['title'] = $this->title;

				$tmp_file_info['uri_title'] = $this->uri_title;
				$tmp_file_info['subtitle'] = $this->subtitle;
				$tmp_file_info['keywords'] = $this->keywords;
/*

            [ext] => jpg
            [filename] => 20030101_Friday-night-hockey_Goalie-Pete-6.JPG
            [date_created] => 2003-01-01 12:06:29
            [publish] => 1
            [title] => Goalie Pete 6
            [uri_title] => goalie-pete-6
            [subtitle] => 
            [keywords] => goalie, Hockey, Peter Hebert, sports
*/

			}

			if(!$this->exists)
			{
				// add new id to list
				$new_list[$path_rel] = $tmp_file_info;
			}
			
			$list[$path_rel] = $tmp_file_info;
			unset($tmp_file_info);
		}
		$this->file_list = $new_list;
		$this->file_count = count($new_list);
		return $list;
	}

	// list all files, and add to db if not present
	// flat list of files only
	function list_flat($dir=NULL) {
		if(isset($dir))
		{
			$this->dir_current = $dir;
		}
		else
		{
			$this->dir_current = $this->dir_base;
		}

		$list = array();

		// get files in this directory (unique)
		$this->list_files_unique(TRUE);
		$files = $this->file_list;
		foreach($files as $file)
		{
			$this->reset_media_vars();

			$tmp_file_info = array();
			$path_rel = str_replace($this->dir_base,'',$file);
			
			if(is_dir($file))
			{
				$subdir = $this->list_flat($file);
				$list = array_merge($list,$subdir);
			}
			else
			{
				$this->file_current = $file;
				$this->file_uri = $path_rel;
				$this->update_media_from_file();
				$tmp_file_info['media_id'] = $this->media_id;
				$tmp_file_info['mime_type'] = $this->mime_type;
				$tmp_file_info['title'] = $this->title;

				$list[$path_rel] = $tmp_file_info;
				unset($tmp_file_info);
			}
			
		}
		ksort($list);

		return $list;
	}

	function reset_media_vars()
	{
		$this->media_id = '';
		$this->file_uri = '';
		$this->external = 0;
		$this->mime_type = '';
		$this->date_created = '';
		$this->date_updated = '';
		$this->publish = 1;
		$this->iptc = FALSE;
		$this->exif = FALSE;
		$this->metadata = array();
		$this->title_id = '';
		$this->title_rel_id = '';
		$this->title = '';
		$this->uri_title = '';
		$this->subtitle = '';
		$this->keywords = '';
		$this->title_updated = '';
	}

	function update_media_from_file()
	{
		$this->read_file_info();
		if(is_array($this->exif) && array_key_exists('DateTimeOriginal', $this->exif)) {
			$this->date_created = $this->exif['DateTimeOriginal'];
		}
		else if(is_array($this->exif) && array_key_exists('FileDateTime',$this->exif))
		{
			$timestamp = intval($this->exif['FileDateTime']);
			$this->date_created = date('Y-m-d H:i:s',$timestamp);
		}
		else
		{
			$this->date_created = date('Y-m-d H:i:s');
		}

		$this->exists = FALSE;
		// if file not in database, add to database
		if(!$this->find_media_by_uri())
		{
			$this->external = 0;
			$this->publish = 1;
			
		}
		else
		{
			// file is already in db
			$this->exists = TRUE;
		}
		
		$this->mime_type = $this->get_mime($this->file_current);

		$this->get_file_metadata();

		if($this->title == '') {
			$this->title = basename($this->file_uri);
		}

		$this->update_media_single();

		// find title if it exists, then update
		$this->scope = 'media';
		$this->temp_id = $this->media_id;
		$this->title_exists();
		
		$this->title_rel_id = $this->media_id;
		$this->update_title();

		$this->scope = 'media';
		$this->temp_id = $this->media_id;
		

		if(is_array($this->metadata) && count($this->metadata) > 0)
		{
	
			foreach($this->metadata as $index => $info)
			{
				$this->metadata[$index]['rel_id'] = $this->media_id;
			}

			$this->update_metadata();
		}

	}

	function remove_media_cache($dir=NULL) {

		if(isset($dir))
		{
			$this->dir_current = $dir;
		}
		else
		{
			$this->dir_current = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_CACHE;
		}
		$list = array();

		// get files in this directory (unique)
		$this->list_files(TRUE);
		$files = $this->file_list;
		foreach($files as $file)
		{
			
			$path_rel = str_replace($this->dir_base,'',$file);

			if(is_dir($file))
			{
				$this->remove_media_cache($file);
				if(!rmdir($file))
				{
					$this->messages[] = "could not remove path '{$path_rel}'";
				}
			}
			else
			{
				if(!unlink($file))
				{
					$this->messages[] = "could not delete file '{$path_rel}'";
				}
			}
			
		}
		
	}

	function update_publish()
	{

		$data = array(
		'publish' => $this->publish,
		);

		if(is_array($this->temp_value) && count($this->temp_value) > 0)
		{

			foreach($this->temp_value as $mid)
			{
				$this->db->or_where('media_id', $mid);
			}
			// update
			if($this->db->update('c_media', $data))
			{
				return TRUE;
			}

		}

		return FALSE;

	}

	function delete_media()
	{
		
		// first load to get file URI
		$this->load_media();
		$this->load_title();

		$name = $this->file_uri;
		if($this->title != '')
		{
			$name = $this->title;
		}
		
		// if local then delete
		if($this->external == 0)
		{
			$full_path = $this->dir_base.$this->file_uri;
			if(file_exists($full_path))
			{
				if(!unlink($full_path))
				{
					$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
					return FALSE;
				}
			}
		}

		// delete all titles, metadata
		$this->db->where('scope', 'media');
		$this->db->where('rel_id', $this->media_id);
		if(!$this->db->delete('c_titles'))
		{
			echo "<p>".$this->db->last_query()."</p>\r";
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}

		$this->db->where('scope', 'media');
		$this->db->where('rel_id', $this->media_id);
		if(!$this->db->delete('c_metadata'))
		{
			echo "<p>".$this->db->last_query()."</p>\r";
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}
		
		// delete all attached instances of media and media entry
		$this->db->where('media_id', $this->media_id);
		if(!$this->db->delete('r_attachments'))
		{
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}

		$this->db->where('media_id', $this->media_id);
		if(!$this->db->delete('c_media'))
		{
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}

		$this->messages[] = sprintf($this->cms_labels['delete_success'],$name);
		$this->file_uri = '';
		$this->title = '';
		return TRUE;
	}

	function delete_batch()
	{
		if(is_array($this->temp_value) && count($this->temp_value) > 0)
		{

			foreach($this->temp_value as $mid)
			{
				$this->temp_id = $mid;
				$this->delete_media();
			}

		}
	}
}

?>