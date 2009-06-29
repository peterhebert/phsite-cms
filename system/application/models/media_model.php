<?php
class Media_model extends Model {

	/* directory / file managment */
	var $dir_base, $dir_current, $dir_list, $dir_list_rel, $file_list, $file_count;

	/* folder info */
	var $path_id, $path_uri, $path_title, $path_lang;

	/* media info */
	var $media_id, $file_uri, $external, $mime, $created, $creator;

	/* media metadata */
	var $data_id, $media_lang, $title, $uri_title, $description, $keywords, $copyright, $city, $prov_state, $country;
	var $iptc, $exif;

	var $media_types = array(
	'dcr' => array('application/x-director','Shockwave Director'),
	'swf' => array('application/x-shockwave-flash','Flash'),
	'flac' => array('audio/flac','<acronym title="Free Lossless Audio Codec">FLAC</acronym> audio'),
	'mp3' => array('audio/mpeg3','MP3 audio'),
	'oga' => array('audio/ogg','Ogg audio'),
	'ogg' => array('audio/ogg','Ogg Vorbis audio'),
	'gif' => array('image/gif','<acronym title="Graphical Interchange Format">GIF</acronym>'),
	'jpg' => array('image/jpeg','<acronym title="Joint Photographic Experts Group">JPEG</acronym> image'),
	'png' => array('image/png','<acronym title="Portable Network Graphics">PNG</acronym> image'),
	'mpeg' => array('video/mpeg','<acronym title="Moving Picture Experts Group">MPEG</acronym> video'),
	'mpg' => array('video/mpeg','<acronym title="Moving Picture Experts Group">MPEG</acronym> video'),
	'ogv' => array('video/ogg','Ogg video'),
	'mov' => array('video/quicktime','QuickTime video'),
	'flv' => array('video/x-flv','Flash video'),
	'tgz' => array('application/gnutar','Tar Gzip archive'),
	'doc' => array('application/msword','Microsoft Word document'),
	'pdf' => array('application/pdf','Portable Document Format'),
	'rtf' => array('application/rtf','Rich text format'),
	'xls' => array('application/x-msexcel','Microsoft Excel spreadsheet'),
	'ppt' => array('application/x-mspowerpoint','Microsoft PowerPoint presentation'),
	'zip' => array('application/zip','Zip archive'),
	'txt' => array('text/plain','plain text'),
	'odt' => array('application/vnd.oasis.opendocument.text','OpenDocument Text'),
	'odp' => array('application/vnd.oasis.opendocument.presentation','OpenDocument Presentation'),
	'ods' => array('application/vnd.oasis.opendocument.spreadsheet','OpenDocument Spreadsheet'),
	);

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

	/* update folder info */
    function update_path()
    {
		$data = array(
		'path_uri' => $this->path_uri,
		'path_title' => $this->path_title,
		'path_lang' => $this->path_lang
		);

		if($this->media_id == '') {
			// insert
			if($this->db->insert('media_paths', $data)){
				$this->path_id = $this->db->insert_id();
				return TRUE;
			}
		} else {
			// update
			$this->db->where('path_id', $this->path_id);
			if($this->db->update('media_paths', $data)){
				return TRUE;
			}
		}

		return FALSE;
	}

    function get_path_by_uri()
    {

		$this->db->where('path_uri', $this->path_uri);
		if($this->path_lang != '') {
			$this->db->where('path_lang', $this->path_lang);
		}
		$this->db->limit(1);
		
		$query = $this->db->get('media_paths');
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->path_id = $row->path_id;
			$this->path_uri = $row->path_uri;
			$this->path_title = $row->path_title;
			$this->path_lang = $row->path_lang;
			return TRUE;
		} else {
			$this->path_id = '';
			$this->path_uri = '';
			$this->path_title = '';
			$this->path_lang = '';
			return FALSE;
		}

	}

    function get_path_langs()
    {

		$this->db->where('path_uri', $this->path_uri);
		if($this->path_lang != '') {
			$this->db->where('path_lang !=', $this->path_lang);
		}
		
		$query = $this->db->get('media_paths');
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$other_langs[] = $row->path_lang;
			}
			return $other_langs;
		} else {
			return FALSE;
		}

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
		$success = FALSE;

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
			$success = TRUE;
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
		
		return $success;
	}

    // return other languages than current language
    function get_other_langs()
    {
		
		$otherlangs = array();

		$this->db->select('*');
		$this->db->from('media_data');

		$this->db->where('media_id', $this->media_id);
		$this->db->where('media_lang !=', $this->media_lang);
		
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
			   $otherlangs[] = $row['content_lang'];
			}
		}
		return $otherlangs;
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

	function get_file_metadata()
	{
		$this->read_file_info();

		if(is_array($this->iptc)) {
			if(array_key_exists('2#105', $this->iptc)) {
				$this->title = $this->iptc['2#105'][0];
			}
			if(array_key_exists('2#120', $this->iptc)) {
				$this->description = $this->iptc['2#120'][0];
			}
			if(array_key_exists('2#025', $this->iptc)) {
				$this->keywords = implode(', ',$this->iptc['2#025']);
			}
			if(array_key_exists('2#080', $this->iptc)) {
				$this->creator = $this->iptc['2#080'][0];
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
			}
		}
		
		if(is_array($this->exif)) {
			if(array_key_exists('MimeType', $this->exif)) {
				$this->mime = $this->exif['MimeType'];
			}
			if(array_key_exists('DateTimeOriginal', $this->exif)) {
				$this->created = $this->exif['DateTimeOriginal'];
			}
		}

		if($this->title == '') {
			$this->title = basename($this->file_uri);
		}
		
		if($this->created == '') {
			$this->created = date('Y-m-d H:i:s');
		}

	}

	function get_mime($filename,$mode=0) {
		if(file_exists($filename) && !is_dir($filename))
		{

			$mime = exif_imagetype($filename);

			$ext = strrchr($filename, '.');
			$ext = substr($ext,1,strlen($ext)-1);
			$ext = strtolower($ext);

			if(array_key_exists($ext,$this->media_types))
			{
				$mime_ext = $this->media_types[$ext][$mode];

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

    function delete_media()
    {
		$tables = array('media', 'media_data');
		$this->db->where('media_id', $this->media_id);
		$this->db->delete($tables);
    }

    function embed_media($id,$size='thumb')
    {
		$this->media_id = $id;
		$this->get_media();
		$this->get_meta();
		
		$file_path = MEDIA_PATH_ORIG.$this->file_uri;
		
		switch ($size) {
		case 0 || 'thumb':
			$size = $this->Site_model->img_size_thumb;
			break;
		case 'display':
			$size = $this->Site_model->img_size_display;
			break;
		default:
			if(is_numeric($size)) {
				$size = intval($size);
			} else {
				$size = $this->Site_model->img_size_display;
			}
			break;
		}
		
		switch ($this->mime) {
		case '':
			break;
		case 'image/gif' || 'image/jpeg' || 'image/png':
			$embed = view_image($file_path,$size, array ('alt' => $this->title) );
			break;
		default:
			
			$embed = anchor(MEDIA_PATH_ORIG.$this->file_uri,$this->title);
			break;
		}
		
		return $embed;
/*

	var $iptc;
	var $exif;

	'flac' => array('audio/flac','<acronym title="Free Lossless Audio Codec">FLAC</acronym> audio'),
	'mp3' => array('audio/mpeg3','MP3 audio'),
	'oga' => array('audio/ogg','Ogg audio'),
	'ogg' => array('audio/ogg','Ogg Vorbis audio'),

	'mpeg' => array('video/mpeg','<acronym title="Moving Picture Experts Group">MPEG</acronym> video'),
	'mpg' => array('video/mpeg','<acronym title="Moving Picture Experts Group">MPEG</acronym> video'),
	'ogv' => array('video/ogg','Ogg video'),
	'mov' => array('video/quicktime','QuickTime video'),
	'flv' => array('video/x-flv','Flash video'),

	'dcr' => array('application/x-director','Shockwave Director'),
	'swf' => array('application/x-shockwave-flash','Flash'),
	'tgz' => array('application/gnutar','Tar Gzip archive'),
	'doc' => array('application/msword','Microsoft Word document'),
	'pdf' => array('application/pdf','Portable Document Format'),
	'rtf' => array('application/rtf','Rich text format'),
	'xls' => array('application/x-msexcel','Microsoft Excel spreadsheet'),
	'ppt' => array('application/x-mspowerpoint','Microsoft PowerPoint presentation'),
	'zip' => array('application/zip','Zip archive'),
	'txt' => array('text/plain','plain text'),
	'odt' => array('application/vnd.oasis.opendocument.text','OpenDocument Text'),
	'odp' => array('application/vnd.oasis.opendocument.presentation','OpenDocument Presentation'),
	'ods' => array('application/vnd.oasis.opendocument.spreadsheet','OpenDocument Spreadsheet'),
*/
    }

}

?>