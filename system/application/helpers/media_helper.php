<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

if ( ! function_exists('embed_media'))
{
	function embed_media($path,$params=array()) {

		// get accepted mime types from config file
		$CI =& get_instance();
		$mimes = $CI->config->item('mime_types');
		$mime_categories = $CI->config->item('mime_categories');

		// check parameters for mime type
		if(array_key_exists('mime_type',$params))
		{
			$mime_type = $params['mime_type'];
		}


		// first check if file exists
		if(file_exists($path))
		{
			$file_location = 'local';
			$pathinfo = pathinfo($path);
			$file_info = getimagesize($path);
			if(!$file_info)
			{
				if(!isset($mime_type) && array_key_exists($pathinfo['extension'],$mimes))
				{
					$mime_type = $mimes[$pathinfo['extension']];
				}
			
			}
			else
			{
				if(!isset($mime_type) && in_array($file_info['mime'],$mimes) )
				{
					$mime_type = $file_info['mime'];
				}

			}
			
			// set web root absolute path
			$web_path = str_replace(ABSOLUTE_INSTALL_PATH.MEDIA_PATH_ORIG,'',$path);

		}
		else if ( url_exists($path) )
		{

			// remote file, try to determine from extension
			$ext = preg_replace('|^(.*)\.([a-zA-Z0-9]{2,5})$|','$2',$path);
			if(array_key_exists($ext,$mimes))
			{
				$mime_type = $mimes[$ext];
			}
			$web_path = $path;
		}
		else
		{
			return FALSE;
		}

		// check parameters for mime type
		if(array_key_exists('title',$params))
		{
			$title = $params['title'];
		}
		else
		{
			$title = basename($path);
		}

		$embed = '';
		// call embed function based upon mime category
		if(isset($mime_type))
		{
			$cat = preg_replace('|^([a-zA-Z0-9]+)/(.*)$|','$1',$mime_type);

			switch ($cat)
			{
			case 'image':
				break;
			case 'video':
				break;
			case 'audio':
				break;
			case 'text':
				break;
			case 'application':
				break;
			default:
				$embed = "<a href=\"{$web_path}\" title=\"\"></a>";
			}
			
			return $embed;

		}
		else
		{
			echo "cannot determine mime type, or unacceptable mime type";
			return FALSE;
		}


	}
}


/* End of file media_helper.php */
/* Location: ./system/application/helpers/media_helper.php */