<?php
class File_model extends MY_Model {

	// media variables
	var $media_id, $file_uri, $external, $mime_type, $date_created, $publish;

	var $date_updated;

	// misc utility variables
	var $media_list = array(), $media_metadata = array(), $media_meta_fields = array();
	var $mime_categories = array('image','audio','video','text','application'), $mime_cat;
	var $mime_types = array(), $image_types = array(), $playable_types = array();
	var $cms_labels;
	var $max_upload_bytes;
	var $max_upload_display;

	// iptc / exif metadata
	var $iptc, $exif;

	// directory / file managment 
	var $dir_base, $dir_current, $dir_list = array(), $dir_list_rel, $file_current, $file_list = array(), $file_count;
	var $new_file_list = array(), $new_file_count;

	// track db entries
	var $exists = FALSE;

    function File_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->dir_base = realpath(ROOT_REL).MEDIA_PATH_ORIG;
		$this->scope = 'media';

		$this->load->helper(array('directory','file'));

		$this->config->load('mime_info');
		$this->mime_types = $this->config->item('mime_types');
		$this->image_types = $this->config->item('image_types');
		$this->playable_types = $this->config->item('playable_types');

		$this->scope = 'media';
		
	}
	
	function get_new_files($recursive=FALSE,$unique=TRUE)
	{
		$this->list_media();
		$old_files = $this->media_list;
		
		$this->list_files($recursive,$unique);
		
		$newfiles = array();
		foreach($this->file_list as $path => $fileinfo)
		{
			if(!array_key_exists($path,$old_files))
			{
				$newfiles[$path] = $fileinfo;
			}
			
		}
		
		uksort($newfiles,"cmp_dirs");

		$this->new_file_list = $newfiles;
		$this->new_file_count = count($newfiles);

	}

	// list directory contents, update db if necessary
	function list_files($recursive=FALSE,$unique=TRUE,$dirs=FALSE) {

		if($this->dir_current == '')
		{
			$this->dir_current = $this->dir_base;
		}
		$filepath = $this->dir_current;

		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$files = array();
		$dirs = array();
		
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = str_replace('//','/',$filepath.'/'.$file);
			
			// ignore all hidden files and directory navigation (.*)
			$ignore = preg_match("#^\..*$#",$file);
			if($ignore <= 0) {
				
				$this->reset_media_vars();
				$this->file_uri = preg_replace("#^".$this->dir_base."(.*)$#","$1",$newpath);
				$file_index = $this->file_uri;
				
				// get file information
				$fileinfo = array(
				'name' => substr(strrchr($newpath, DIRECTORY_SEPARATOR), 1),
				'filename' => basename($this->file_uri),
				'server_path' => $newpath,
				'server_directory' => dirname($newpath),
				'external' => 0,
				'file_uri' => $this->file_uri,
				'size' => filesize($newpath),
				'date' => filectime($newpath),
				'readable' => is_readable($newpath),
				'writable' => is_writable($newpath),
				'executable' => is_executable($newpath),
				'fileperms' => fileperms($newpath),
				);
				
				if(!is_dir($newpath))
				{

					if($dirs === FALSE)
					{
						$fileinfo['extension'] = preg_replace("#^(.*)\.([a-zA-Z0-9]+)$#","$2",$file);
						$fileinfo['path_basename'] = preg_replace("#^(.*)\.([a-zA-Z0-9]+)$#","$1",$newpath);
	
						$files[$file_index] = $fileinfo;
					}
				}
				else
				{

					if($recursive)
					{
						// get subdirectory contents
						$this->dir_current = $newpath;
						$subdirs = $this->list_files(TRUE);

						$files = array_merge($files,$subdirs['files']);
						$dirs = array_merge($dirs,$subdirs['dirs']);

					}

					$dirs[$file_index] = $fileinfo;
				}

			}

		}
		//closing the directory
		closedir($dir_handle);
		unset($fileinfo);

		// get unique basenames from list
		// if two files in same directory with same basename, image will be ignored
		// image will be used only for thumbnail of file
		// ie. mymovie.mp4 could have thumbnail mymovie.jpg
		
		if($unique)
		{
			$only_files = array();
			$fnames = array();
			$exts = array();
			$new_list = array();
			$imageTypes = array("jpg","gif","png");
	
			foreach ($files as $fpath => $finfo) {
				$fnames[] = $finfo['path_basename'];
				$exts[] = strtolower($finfo['extension']);
				$paths[] = $fpath;
			}
	
			$unique_names = array_count_values($fnames);
			
			$unique_list = array();
			foreach($unique_names as $uname => $times) {
	
				$matching_keys = array_keys( $fnames, $uname);
				
				if($times > 1) {
					foreach($matching_keys as $key) {
						if(!in_array($exts[$key],$imageTypes)) {
							$newindex = $paths[$key];
							$newfile = $files[$newindex];
						}
					}
					if(!isset($newfile)) {
						$newindex = $paths[$matching_keys[0]];
						$newfile = $files[$newindex];
					}
	
				} else {
					$newindex = $paths[$matching_keys[0]];
					$newfile = $files[$newindex];
				}
				$unique_list[$newindex] = $newfile;
				unset($newfile);
			}
			
			$files = $unique_list;
			unset($unique_list);
		}
		// end filter duplicate basenames
		
		$updated_files = array();
		
		// now go through list and update db
		foreach ($files as $fpath => $finfo) {
			$this->reset_media_vars();

			$fileinfo =& $finfo;
			
			$this->file_current = $finfo['server_path'];
			$this->file_uri = $finfo['file_uri'];
			$this->read_file_info();

			// if file in db, then load; otherwise get info from file and insert to db
			$this->update_file();

			$fileinfo['media_id'] = $this->media_id;
			$fileinfo['mime_type'] = $this->mime_type;
			$fileinfo['mime_cat'] = $this->mime_cat;
			$fileinfo['title_id'] = $this->title_id;
			$fileinfo['title'] = $this->title;
			$fileinfo['uri_title'] = $this->uri_title;
			$fileinfo['subtitle'] = $this->subtitle;
			$fileinfo['keywords'] = $this->keywords;
			$fileinfo['title_updated'] = $this->title_updated;
			$fileinfo['date_created'] = $this->date_created;

			if(is_array($this->metadata) && count($this->metadata) > 0)
			{
				foreach($this->metadata as $index => $info)
				{
					$fileinfo[$info['field_ref_name']] = $info['value'];
				}
	
			}

			$updated_files[$fpath] = $fileinfo;

		}
		
		$files = $updated_files;
		
		uksort($files,"cmp_dirs");
		uksort($dirs,"cmp_dirs");

		$this->file_count = count($files);
		$this->file_list = $files;
		$this->dir_list = $dirs;

		$this->reset_media_vars();

		return array('files' => $files, 'dirs' => $dirs);
	}

	function update_file() // if current file in db, load; if not in db, insert
	{
		
		if($this->find_media_by_uri())
		{
			$this->scope = 'media';
			$this->temp_id = $this->media_id;
			$this->load_title();

			$this->scope = 'media';
			$this->temp_id = $this->media_id;
			$this->load_metadata();

		}
		else
		{
			// write file info to db
			// media
			if($this->update_media())
			{
				// tile
				$this->scope = 'media';
				$this->title_rel_id = $this->media_id;
				
				if($this->title == '')
				{
					$this->title = basename($this->file_current);
				}
				
				if($this->temp_lang == '')
				{
					$this->temp_lang = $this->config->item('language');
					$this->lang_find_by_CI_Name();
				}
				$this->update_title();

				// metadata
				$this->scope = 'media';
				$this->temp_id = $this->media_id;
		
				if(is_array($this->metadata) && count($this->metadata) > 0)
				{
					$tmp_meta = array();

					foreach($this->metadata as $index => $info)
					{
						$zz =& $info;
						$zz['rel_id'] = $this->media_id;
						if($info['value'] != '')
						{
							$tmp_meta[$index] = $zz;
						}
						unset($zz);
					}
					$this->metadata = $tmp_meta;
					unset($tmp_meta);
					$this->update_metadata();
				}

			}
		
		}

	}

	function list_media()
	{
		$where = array();

		// match to directory
		if ($this->temp_value != '')
		{
			switch ($this->db->platform())
			{
				case 'mysql': 
					$where[] = "`file_uri` REGEXP '^{$this->temp_value}([^/]+)$'";
					break;
				case 'postgre': 
					$where[] = "`file_uri` ~ '^{$this->temp_value}([^/]+)$'";
					break;
				case 'oci8': 
					$where[] = "`file_uri` REGEXP_LIKE '^{$this->temp_value}([^/]+)$'";
					break;
				default:
					$where[] = "`file_uri` LIKE '{$this->temp_value}%'";
			}



		}

		// date filter
		if ($this->date_filter != '')
		{
			$filter = parse_date_filter($this->date_filter);
			if($filter)
			{
				$where[] = parse_date_filter($this->date_filter);
			}

		}

		// mime type restriction
		if ($this->mime_type != '')
		{
			$where[] = "`mime_type` LIKE '{$this->mime_type}%'";
		}
		
		// limit
		$limit = '';
		if ($this->limit > 0)
		{
			$limit .= " LIMIT ";
			if($this->page > 1)
			{
				$start = ($this->page * $this->limit) - $this->limit;
				$limit .= $start.', '.$this->limit;
			}
			else
			{
				$limit .= $this->limit;
			}
		}
		
		$sql = "SELECT * FROM c_media";
		if(count($where) > 0)
		{
			$sql .= " WHERE ".implode(' AND ',$where);
		}
		$sql .= " ORDER BY 'file_uri' ASC".$limit;
		
		$query = $this->db->query($sql);

		$this->debugging[] = $this->db->last_query();
		
		$file_list = array();
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$this->reset_media_vars();
				$tmp_media =& $row;

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

				$file_list[$row['file_uri']] = $tmp_media;

			}

		}
		$this->media_list = $file_list;

		$this->file_count = count($this->media_list);
	}

	function update_media()
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

    function read_file_info()
    {

		if($this->file_current == '')
		{
			$this->file_current = $this->dir_base.$this->file_uri;
		}
		
		if(file_exists($this->file_current) && !is_dir($this->file_current)) {

			// exif and iptc info
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
			
			// creation date
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
				$this->date_created = date('Y-m-d H:i:s', filectime($this->file_current));
			}

			// mime type
			$imagetype = @exif_imagetype($filename);

			if ($imagetype)
			{
				switch ($imagetype)
				{
					case 1: // IMAGETYPE_GIF
						$this->mime_type = 'image/gif';
						break;
					case 2: // 	IMAGETYPE_JPEG
						$this->mime_type = 'image/jpeg';
						break;
					case 3: // 	IMAGETYPE_PNG
						$this->mime_type = 'image/png';
						break;
					case 4: // 	IMAGETYPE_SWF
						$this->mime_type = 'application/x-shockwave-flash';
						break;
					case 5: // 	IMAGETYPE_PSD
						$this->mime_type = 'image/psd';
						break;
					case 6: // 	IMAGETYPE_BMP
						$this->mime_type = 'image/x-windows-bmp';
						break;
					case 7: // 	IMAGETYPE_TIFF_II (intel byte order)
					case 8: // 	IMAGETYPE_TIFF_MM (motorola byte order)
						$this->mime_type = 'image/tiff';
						break;
					case 10: // 	IMAGETYPE_JP2
						$this->mime_type = 'image/jp2';
						break;
					case 13: // 	IMAGETYPE_SWC
						$this->mime_type = 'application/x-shockwave-flash';
						break;
					case 14: // 	IMAGETYPE_IFF
						$this->mime_type = 'image/iff';
						break;
					case 15: // 	IMAGETYPE_WBMP
						$this->mime_type = 'image/vnd.wap.wbmp';
						break;
					case 16: // 	IMAGETYPE_XBM
						$this->mime_type = 'image/xbm';
						break;
					default:
						$imagetype = FALSE;
				}
			}

			if ($imagetype == FALSE)
			{
				$fileinfo = pathinfo($this->file_current);
				if($fileinfo)
				{
					$ext = strtolower($fileinfo['extension']);
					if(array_key_exists($ext,$this->mime_types))
					{
						$this->mime_type = $this->mime_types[$ext];
						$imagetype = TRUE;
					}
				}
			}

			if ($imagetype == FALSE)
			{
				$this->mime_type = 'application/octet-stream';
			}
			
			// file metadata
			$this->mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->mime_type);

			$field_name_to_iptc = array(
			'copyright' => '2#116',
			'creator' => '2#080',
			'description' => '2#120',
			'location' => '2#092',
			'city' => '2#090',
			'province_state' => '2#095',
			'country' => '2#101',
			);
	
			$this->_load_meta_fields();
			
			$meta = $this->field_list;
			
			if(is_array($meta) && count($meta) > 0)
			{
				foreach($meta as $index => $info)
				{
					
					if(array_key_exists($info['field_ref_name'],$field_name_to_iptc))
					{
						$iptc_key = $field_name_to_iptc[$info['field_ref_name']];
						if(is_array($this->iptc) && array_key_exists($iptc_key, $this->iptc)) {
							$meta[$index]['value'] = $this->iptc[$iptc_key][0];
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
		
	}

	function _load_meta_fields()
	{
		$this->db->where('scope','media');
		$this->db->where('type_uri_name',$this->mime_cat);
		$query = $this->db->get('s_types');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->type_id = $row->type_id;
			$this->scope = $row->scope;
			$this->type_uri_name = $row->type_uri_name;
		}

		$this->scope = 'types';
		$this->temp_id = $this->type_id;
		$this->list_fields_related();
	}

	// existing functions

	function find_media_by_uri($published=FALSE)
	{
		$this->db->where('file_uri',$this->file_uri);
		if ($published)
		{
			$this->db->where('publish',1);
		}
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

			
			$this->temp_id = $row->media_id;
			$this->load_title();
		
			return TRUE;
			exit();
		}

		return FALSE;
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

	function count_media_all()
	{

		$where = array();

		// match to directory
		if ($this->temp_value != '')
		{
			switch ($this->db->platform())
			{
				case 'mysql': 
					$where[] = "`file_uri` REGEXP '^{$this->temp_value}([^/]+)$'";
					break;
				case 'postgre': 
					$where[] = "`file_uri` ~ '^{$this->temp_value}([^/]+)$'";
					break;
				case 'oci8': 
					$where[] = "`file_uri` REGEXP_LIKE '^{$this->temp_value}([^/]+)$'";
					break;
				default:
					$where[] = "`file_uri` LIKE '{$this->temp_value}%'";
			}



		}
		// date filter
		if ($this->date_filter != '')
		{
			$filter = parse_date_filter($this->date_filter);
			if($filter)
			{
				$where[] = parse_date_filter($this->date_filter);
			}

		}
		// mime type restriction
		if ($this->mime_type != '')
		{
			$where[] = "`mime_type` LIKE '{$this->mime_type}%'";
		}

		// exclude certain ids if supplied
		if(is_array($this->media_list) && count($this->media_list) > 0)
		{
			foreach($this->media_list as $id)
			{
				$where[] = "`media_id` != {$id}";
			}
		}

		$sql = "SELECT * FROM c_media";
		if(count($where) > 0)
		{
			$sql .= " WHERE ".implode(' AND ',$where);
		}
		$sql .= " ORDER BY 'file_uri' ASC";
		
		$query = $this->db->query($sql);
		if($query)
		{
			return $query->num_rows();
		}

		return 0;
		
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
			if(is_file($full_path))
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
		$deleted = $this->db->delete('c_titles');
		if(!$deleted)
		{
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			$this->debugging[] = $this->db->last_query();
			return FALSE;
		}

		$this->db->where('scope', 'media');
		$this->db->where('rel_id', $this->media_id);
		$deleted = $this->db->delete('c_metadata');
		if(!$deleted)
		{
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			$this->debugging[] = $this->db->last_query();
			return FALSE;
		}
		
		// delete all attached instances of media and media entry
		$this->db->where('media_id', $this->media_id);
		$deleted = $this->db->delete('r_attachments');
		if(!$deleted)
		{
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			$this->debugging[] = $this->db->last_query();
			return FALSE;
		}

		$this->db->where('media_id', $this->media_id);
		$deleted = $this->db->delete('c_media');
		if(!$deleted)
		{
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			$this->debugging[] = $this->db->last_query();
			return FALSE;
		}

		$this->messages[] = sprintf($this->cms_labels['delete_success'],$name);
		$this->mime_type = '';
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

	function load_media()
	{
		$this->db->where('media_id',$this->temp_id);
		$this->db->where('site_id',$this->site_id);
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

	function media_search_keywords()
	{
		$results = $this->keyword_search();

		$media = array();
		
		if (count($results) > 0)
		{
			foreach ($results as $row)
			{
				$info =& $row;
				
				$this->temp_id = $row['rel_id'];
				$this->load_media();
				
				$info['media_id'] = $this->media_id;
				$info['file_uri'] = $this->file_uri;
				$info['external'] = $this->external;
				$info['mime_type'] = $this->mime_type;
				$info['date_created'] = $this->date_created;
				$info['publish'] = $this->publish;
				
				$media[] = $info;
			}
		} 		

		return $media;		
	}

	function list_dirs_display()
	{

		$this->list_files(TRUE,TRUE,TRUE);
		
		$dirs = $this->dir_list;
		$dirs_rel = array();
		
		$media_root = $this->dir_base;
		
		$dirs_rel['/'] = $this->cms_labels['media_root'];
		if(count($dirs) > 0)
		{
			// get relative paths for each directory
			foreach($dirs as $path_rel => $dir) {
				$dirs_rel[$path_rel] = $path_rel;
			}

		}

		$this->dir_list_rel = $dirs_rel;

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
	
	function cache_empty($recursive=TRUE)
	{
		if($this->dir_current == '')
		{
			$this->dir_current = realpath(ROOT_REL).MEDIA_PATH_CACHE;
		}
		$filepath = $this->dir_current;

		//using the opendir function
		$err = sprintf($this->cms_labels['path_open_error'],$filepath);
		$dir_handle = @opendir($filepath) or die($err);
		
		// loop through files and directories
		while ($file = readdir($dir_handle)) {
			$newpath = str_replace('//','/',$filepath.'/'.$file);
			
			// ignore all hidden files and directory navigation (.*)
			$ignore = preg_match("#^\..*$#",$file);
			if($ignore <= 0) {
				
				if(!is_dir($newpath))
				{
					// attempt to delete file
					if(!unlink($newpath))
					{
						$this->messages[] = sprintf($this->cms_labels['path_delete_error'],$newpath);
						return FALSE;
					}
				}
				else
				{

					if($recursive)
					{
						$mypath = $newpath;
						// get subdirectory contents
						$this->dir_current = $newpath;
						$empty = $this->cache_empty(TRUE);
						// if directory empty, delete it
						if($empty)
						{
							if(!rmdir($mypath))
							{
								$this->messages[] = sprintf($this->cms_labels['path_delete_error'],$mypath);
								return FALSE;
							}
							
						}
						else
						{
							return FALSE;
						}


					}

				}

			}

		}
		//closing the directory
		closedir($dir_handle);
		
		return TRUE;
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
	
}

?>