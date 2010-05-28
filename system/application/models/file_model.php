<?php
class File_model extends MY_Model {

	// media variables
	var $media_id, $file_uri, $external, $mime_type, $date_created, $publish;

	var $date_updated;

	// misc utility variables
	var $media_list = array(), $media_metadata = array(), $media_meta_fields = array();
	var $mime_categories = array('image','audio','video','text','application'), $mime_cat;
	var $cms_labels;
	var $max_upload_bytes;
	var $max_upload_display;

	// iptc / exif metadata
	var $iptc, $exif;

	// directory / file managment 
	var $dir_base, $dir_current, $dir_list, $dir_list_rel, $file_current, $file_list, $file_count;

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
		$this->scope = 'media';
		
	}
	
	// list files in directory (DEFAULT is both files and subdirs; 'files' = only files; 'dirs' = only dirs)
	function list_dir_contents($mode = 'both',$recursive=FALSE) {

		if($this->dir_current == '')
		{
			$this->dir_current = $this->dir_base;
		}
		$filepath = $this->dir_current;

		//using the opendir function
		$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
		$files = array();
		$dirs = array();

		switch($mode)
		{
			case 'files': 
				$do_files = TRUE;
				$do_dirs = FALSE;
				break;
			case 'dirs': 
				$do_files = FALSE;
				$do_dirs = TRUE;
				break;
			default:
				$do_files = TRUE;
				$do_dirs = TRUE;
		}
		
		//running the while loop
		while ($file = readdir($dir_handle)) {
			$newpath = str_replace('//','/',$filepath.'/'.$file);
			
			// ignore all hidden files and directory navigation (.*)
			$ignore = preg_match("#^\..*$#",$file);
			if($ignore <= 0) {
				
				$this->reset_media_vars();
				$this->file_uri = preg_replace("#^".$this->dir_base."(.*)$#","$1",$newpath);
				
				// get file information
				$fileinfo = array(
				'name' => substr(strrchr($newpath, DIRECTORY_SEPARATOR), 1),
				'server_path' => $newpath,
				'file_uri' => $this->file_uri,
				'size' => filesize($newpath),
				'date' => filectime($newpath),
				'readable' => is_readable($newpath),
				'writable' => is_writable($newpath),
				'executable' => is_executable($newpath),
				'fileperms' => fileperms($newpath),
				);

				
				if($do_dirs && is_dir($newpath))
				{
					if($recursive)
					{
						$this->dir_current = $newpath;
						$subdirs = $this->list_dir_contents($mode,TRUE);
						if(count($subdirs) > 0)
						{
							$fileinfo['subdirs'] = $subdirs;
						}
					}
					$dirs[$file] = $fileinfo;
				}

				if($do_files && !is_dir($newpath))
				{
					$fileinfo['server_directory'] = dirname($newpath);
					$fileinfo['extension'] = preg_replace("#^(.*)\.([a-zA-Z0-9]+)$#","$2",$file);
					$files[] = $fileinfo;
				}

			}

		}
		//closing the directory
		closedir($dir_handle);

		$this->file_count = count($files);
		$this->file_list = $files;
		$this->dir_list = $dirs;
		$this->reset_media_vars();
		return $dirs;
	}


	// list files in directory (0 = both files and subdirs; 1 = only files; 2 = only dirs)
	function list_files() {

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
				
				// get file information
				$fileinfo = array(
				'name' => substr(strrchr($newpath, DIRECTORY_SEPARATOR), 1),
				'server_path' => $newpath,
				'server_directory' => dirname($newpath),
				'file_uri' => $this->file_uri,
				'size' => filesize($newpath),
				'date' => filectime($newpath),
				'readable' => is_readable($newpath),
				'writable' => is_writable($newpath),
				'executable' => is_executable($newpath),
				'fileperms' => fileperms($newpath),
				'extension' => preg_replace("#^(.*)\.([a-zA-Z0-9]+)$#","$2",$file),
				);
				
				if(!is_dir($newpath))
				{
					$this->read_file_info();
					
					// now put file info in array
					$this->file_current = $newpath;
					
					// if file in db, then load; otherwise get info from file and insert to db
					$this->update_file();
						
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
			
					$files[$file] = $fileinfo;
				}
				else
				{
					$dirs[] = $newpath;
				}

			}

		}
		//closing the directory
		closedir($dir_handle);

		$this->file_count = count($files);
		$this->file_list = $files;
		$this->dir_list = $dirs;
		$this->reset_media_vars();
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
					$this->title = basename($newpath);
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
			
					foreach($this->metadata as $index => $info)
					{
						$this->metadata[$index]['rel_id'] = $this->media_id;
					}
		
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
			$where[] = parse_date_filter($this->date_filter);
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
		
		$sql = "SELECT * FROM c_media WHERE ".implode(' AND ',$where);
		$sql .= " ORDER BY 'file_uri' ASC".$limit;
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			$this->file_list = $query->result_array();

		}

		$this->file_count = $query->num_rows();
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
			
			$this->temp_id = $row->media_id;
			$this->load_title();
		
			return TRUE;
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

}

?>