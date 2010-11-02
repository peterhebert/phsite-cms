<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

if ( ! function_exists('file_is_image'))
{
	function is_image($path) {

		$is_image = FALSE;

		$acceptable_types = array(
		'bmp' => 'image/x-windows-bmp',
		'gif' => 'image/gif',
		'ico' => 'image/vnd.microsoft.icon',
		'j2k' => 'image/jp2',
		'jp2' => 'image/jp2',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpf' => 'image/jpx',
		'jpg' => 'image/jpeg',
		'jpm' => 'image/jpm',
		'jpx' => 'image/jpx',
		'pct' => 'image/x-pict',
		'pcx' => 'image/x-pcx',
		'png' => 'image/png',
		'svg' => 'image/svg+xml',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'wbmp' => 'image/vnd.wap.wbmp',
		'psd' => 'image/psd',
		'xbm' => 'image/xbm',
		); 

		if(file_exists($path))
		{
			$pathinfo = pathinfo($path);
			$file_info = getimagesize($path);
			if(!$file_info)
			{
				if(array_key_exists($pathinfo['extension'],$acceptable_types))
				{
					$is_image = TRUE;
				}
			
			}
			else
			{
				if( in_array($file_info['mime'],$acceptable_types) )
				{
					$is_image = TRUE;
				}
			}
		}
		
		return $is_image;

	}
}

if ( ! function_exists('url_exists'))
{
	// does remote file exist?
	function url_exists($url) {
		if(@file_get_contents($url,0,NULL,0,1))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}

if ( ! function_exists('view_image'))
{

	function view_image($path,$size,$img_tag_attr = array(),$square=FALSE,$linkonly=FALSE) {

		/* 
		parameters for function
		
		path			path to image
		size			size to fit image to by largest dimension
		img_tag_attr	array of attributes for image tag : ('alt' => 'image alt text', 'id' => 'image tag id')
		square			force ratio of resized image to square
		linkonly		return only link to scaled file and not img tag
		
		NOTE: USES CONSTANTS 'MEDIA_PATH_ORIG','MEDIA_PATH_CACHE' for determining where to store image
		*/

		// Path to original image and to resized version
		$orig_image_path = $path;

		$path_rel = str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$path);
		$orig_image_path_rel = trim($path_rel,'/');
		
		$img_tag_attr = array('src' => '');
		
		if(file_exists($orig_image_path)) {
			
			$orig_size = getimagesize($orig_image_path);
			
			if($orig_size !== FALSE) {
	
				$orig_width = $orig_size[0];
				$orig_height = $orig_size[1];
				$orig_ratio = $orig_width / $orig_height;
		
				if($square === TRUE) {
					// force crop to fit square
					
					// first determine offsets for crop of orig
					if ($orig_ratio > 1) {
		
						// LANDSCAPE - lop off sides

						$crop_height = $orig_height;
						$crop_width = round( $orig_height * (1/$crop_ratio) );
						
						$offset_x = ceil(($orig_width - $crop_width) / 2 );
						$offset_y = 0;
	
					} else if ($orig_ratio < 1) {
		
						// PORTRAIT  - lop off top and bottom

						$crop_width = $orig_width;
						$crop_height = round( $orig_width * (1/$crop_ratio) );
						$offset_x = 0;
						$offset_y = ceil(($orig_height - $crop_height) / 2 );
		
						
					} else { // ORIGINAL IS SQUARE = no cropping
						$offset_x = 0;
						$offset_y = 0;
						$crop_width = $orig_width;
						$crop_height = $orig_height;
					}
		
					$new_width = $size;
					$new_height = $size;
				
				} else { // scale proportionally
					$offset_x = 0;
					$offset_y = 0;
		
					// now determine width and height
		
					if ($orig_ratio > 1) { // landscape
						$new_width = $size;
						$new_height = round($size * (1/$orig_ratio));
					} else if ($orig_ratio < 1) { // portrait
						$new_width = round($size * $orig_ratio);
						$new_height = $size;
					} else { // square
						$new_width = $size;
						$new_height = $size;
					}

		
				}
				
				// determine maximum side length of original
				if ($orig_ratio > 1) { // landscape
					$max_orig = $orig_width;
				} else { // portrait or square
					$max_orig = $orig_height;
				}
	
				// determine file name of scaled image
				$pi = pathinfo($orig_image_path);
				if(strstr($orig_image_path,MEDIA_PATH_ORIG))
				{
					$dest_dir = str_replace(MEDIA_PATH_ORIG,MEDIA_PATH_CACHE,$pi['dirname']);
				}
				else if(strstr($orig_image_path,'/system/application/views/themes/'))
				{
					$dest_dir = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_CACHE.'/theme_screenshots';
				}
				else
				{
					$dest_dir = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_CACHE.'/system';
				}

				if($square === TRUE && $orig_ratio != 1) {
					$fn_copy_short = $pi['filename'].'_'.$size.'_'.$crop_ratio.'.jpg';
				} else {
					$fn_copy_short = $pi['filename'].'_'.$size.'.jpg';
				}
				$scaled_image_path = $dest_dir.'/'.$fn_copy_short;

				$scaled_image_path_rel = str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$scaled_image_path);
				$scaled_image_path_rel = trim($scaled_image_path_rel,'/');
				
				if(!file_exists ( $dest_dir ) ){
					if(!mkdir($dest_dir,0777,TRUE)) {
						$result = "<p>cache directory '".$dest_dir."' does not exist and cannot be written</p>\r";
					}
				}
				
				if(!file_exists($scaled_image_path) && $size != '' && $size <= $max_orig) {
					
					// Get the CodeIgniter super object
					$CI =& get_instance();
					
					// LOAD LIBRARY
					$CI->load->library('image_lib');
					
					// CONFIGURE IMAGE LIBRARY
					$config['source_image']		= $orig_image_path;
					$config['new_image']		= $scaled_image_path;
	
					if($offset_x == 0 && $offset_y == 0 ) {
						// no cropping
						$config['width']			= $new_width;
						$config['height']			= $new_height;
						$CI->image_lib->initialize($config);
						if($CI->image_lib->resize() !== FALSE){
							echo $CI->image_lib->display_errors();
						}
						$CI->image_lib->clear();
					} else {
						// do cropping
						$config['width']			= $crop_width;
						$config['height']			= $crop_height;
						$config['maintain_ratio']	= FALSE;
						$config['x_axis'] = $offset_x;
						$config['y_axis'] = $offset_y;
						$CI->image_lib->initialize($config);
						$CI->image_lib->crop();
						$CI->image_lib->clear();
						// now scale
						$config['source_image']		= $scaled_image_path;
						$config['width']			= $new_width;
						$config['height']			= $new_height;
						$CI->image_lib->initialize($config);
						if($CI->image_lib->resize() !== FALSE){
							echo $CI->image_lib->display_errors();
						}
						$CI->image_lib->clear();
					}
					
				}
				
				if(file_exists($scaled_image_path)) {
					$img_tag_attr['src'] = $scaled_image_path_rel;
					$img_tag_attr['width'] = $new_width;
					$img_tag_attr['height'] = $new_height;
				} else {
					$img_tag_attr['src'] = $orig_image_path_rel;
					$img_tag_attr['width'] = $orig_width;
					$img_tag_attr['height'] = $orig_height;
				}
				
				$result = img($img_tag_attr);
			
			} else {
				$result = FALSE;
			}
		} else {
			$result = FALSE;
		}
		if($linkonly)
		{
			$result = $img_tag_attr['src'];
		}

		return $result;
	
	} // end view_image()
}

if ( ! function_exists('get_metadata'))
{

	function get_metadata($path) {
		$server_path = realpath($_SERVER["DOCUMENT_ROOT"]).$path;
		$size = getimagesize($server_path, $info);
		if(isset($info['APP13']))
		{
			$iptc = iptcparse($info['APP13']);
		} else {
			$iptc = FALSE;
		}
		$exif = exif_read_data($server_path);
		$metadata = array('iptc' =>$iptc,'exif' => $exif);
		return $metadata;
	}

}

if ( ! function_exists('media_player_object'))
{
	// bakes up various embedded media players using valid xhtml or html5 markup
	function media_player_object($data)
	{
		// convert $data to array if string
		if(!is_array($data))
		{
			$src = $data;
			$data = array('src' => $src);
			unset($src);
		}

		// if source is array, take first source in list to test mime type
		if(is_array($data['src']))
		{
			$src = $data['src'][0];
		}
		else
		{
			$src = $data['src'];
		}

		// get file extension
		if(!preg_match('#^http://.*$#',$src))
		{
			$path_full = ROOT_REL.$src;
			$pinfo = pathinfo($path_full);
			$ext = $pinfo['extension'];
		}
		else
		{
			$ext = preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$src);
		}

		// initialize tag, attribute and param arrays, for outer, inner tags and possible external controller
		$tag = array('outer' => 'object', 'inner' => 'object','controller' => 'object');
		$attrib = array('outer' => array(), 'inner' => array(), 'controller' => array());
		$param = array('outer' => array(), 'inner' => array(), 'controller' => array());
		$sources = array();
		$fallback_text = '';

		// variables to determine whether to create inner fallback tag, and external controller
		$inner = true;
		$external_controller = false;
		
		// list of common boolean paramaters, assume true if in $data array
		$bool = array('autoplay','autostart','allowFullScreen','controls','loop','showcontrols');
		foreach ($bool as $att)
		{
			// check keys, set to value
			if(array_key_exists($att,$data))
			{
				$param['outer'][$att] = $data[$att];
				$param['inner'][$att] = $data[$att];
			}

			// check values
			if(in_array($att,$data))
			{
				$param['outer'][$att] = 'true';
				$param['inner'][$att] = 'true';
				if($att == 'controls')
				{
					$attrib['outer'][$att] = 'controls';
				}

				if($att == 'showcontrols')
				{
					$param['inner']['controller'] = 'true';
				}


			}
		}

		// set width and height
		$size_test = getimagesize(ROOT_REL.$data['src']);
		if($size_test)
		{
			$data['width'] = $size_test[0];
			$data['height'] = $size_test[1];
		}
		if(array_key_exists('height',$data))
		{
			$attrib['outer']['height'] = $data['height'];
			$attrib['inner']['height'] = $data['height'];
		}
		if(array_key_exists('width',$data))
		{
			$attrib['outer']['width'] = $data['width'];
			$attrib['inner']['width'] = $data['width'];
		}

		// get language files
		$CI =& get_instance();
		$CI->lang->load('mime');
		$fallbacks = $CI->lang->line('fallbacks');
	
		// get list of supported MIME types
		$CI->config->load('mime_info');
		$types = $CI->config->item('playable_types');
		if(array_key_exists($ext,$types))
		{
			$attrib['outer']['type'] = $types[$ext];
		}
		
		// set tag, attributes and params by file type
		switch ($ext)
		{
		// QuickTime
		case 'mov':
			$fallback_text = $fallbacks['quicktime'];
			$attrib['outer']['classid'] = 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B';
			$attrib['outer']['codebase'] = 'http://www.apple.com/qtactivex/qtplugin.cab';

			// set source attributes and params
			$param['outer']['src'] = $data['src'];
			$attrib['inner']['data'] = $data['src'];

			$param['outer']['controller'] = 'true';
			$param['inner']['controller'] = 'true';
			
			if(!array_key_exists('autoplay',$data))
			{
				$param['outer']['autoplay'] = 'false';
				$param['inner']['autoplay'] = 'false';
			}
			if(array_key_exists('height',$data))
			{
				$attrib['outer']['height'] = $data['height'] + 20;
				$attrib['inner']['height'] = $data['height'] + 20;
			}

			$attrib['inner']['type'] = 'video/quicktime';
			$attrib['inner']['class'] = 'nested';

			break;
		// MPEG-4 video
		case 'm4v':
		case 'mp4':
		case 'mpeg4':
			$tag['outer'] = 'video';
			$inner = false;

			// set source attributes and params
			$attrib['outer']['src'] = $data['src'];

			$fallback_text = $fallbacks['mp4'];
			if(!array_key_exists('controls',$data))
			{
				$attrib['outer']['controls'] = 'controls';
			}

			break;
		// Ogg Theora video
		case 'ogm':
		case 'ogv':
			$tag['outer'] = 'video';

			// set source attributes and params
			$attrib['outer']['src'] = $data['src'];
			$attrib['outer']['controls'] = 'controls';

			$fallback_text = $fallbacks['theora'];
			
			$attrib['inner']['type'] = 'application/x-java-applet';

			$param['inner']['archive'] = ROOT_REL.'/assets/cortado-ovt-stripped-0.2.2.jar';
			$param['inner']['code'] = 'com.fluendo.player.Cortado.class';
			$param['inner']['url'] = $data['src'];
			$param['inner']['keepAspect'] = 'true';
			break;
		// Ogg Vorbis or FLAC audio
		case 'flac':
		case 'oga':
		case 'ogg':
		case 'spx':
			$tag['outer'] = 'audio';

			// set source attributes and params
			$attrib['outer']['src'] = $data['src'];
			$attrib['outer']['controls'] = 'controls';

			$fallback_text = $fallbacks['ogg'];
			
			$attrib['inner']['type'] = 'application/x-java-applet';

			$param['inner']['archive'] = ROOT_REL.'/assets/cortado-ovt-stripped-0.2.2.jar';
			$param['inner']['code'] = 'com.fluendo.player.Cortado.class';
			$param['inner']['url'] = $data['src'];
			$param['inner']['keepAspect'] = 'true';
			break;

		// Windows Media
		case 'asf':
		case 'asx':
		case 'wm':
		case 'wma':
		case 'wmv':
		case 'wmx':
		case 'wvx':
			// Platforms and browsers
			$agent = $_SERVER['HTTP_USER_AGENT'];
			if (strpos($agent, 'Macintosh')) {
				$platform = 'mac';
			} elseif (strpos($agent, 'Windows')) {
				$platform = 'win';
			} elseif (strpos($agent, 'Linux')) {
				$platform = 'lin';
			}
	

			$fallback_text = $fallbacks['wmv_'.$platform];

			if($ext == 'wmv')
			{
				$type = 'video/x-ms-wmv';
			}
			else
			{
				$type = 'video/x-ms-asf';
			}

			$attrib['outer']['classid'] = 'CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6';
			if(array_key_exists('height',$data))
			{
				$attrib['outer']['height'] = $data['height'] + 64;
				$attrib['inner']['height'] = $data['height'] + 64;
			}

			$param['outer']['url'] = $data['src'];
			$param['outer']['src'] = $data['src'];
			$param['outer']['showcontrols'] = 'true';
	
			$attrib['inner']['type'] = $type;
			$attrib['inner']['data'] = $data['src'];
			$param['inner']['src'] = $data['src'];
			$param['inner']['controller'] = 'true';

			if(array_key_exists('autostart',$data) || in_array('autostart',$data)
			|| array_key_exists('autoplay',$data) || in_array('autoplay',$data))
			{
				$param['outer']['autostart'] = 'true';
				$param['inner']['autostart'] = 'true';
			}


			break;

		// Flash video
		case 'f4v':
		case 'flv':
			$fallback_text = $fallbacks['flash'];
			$inner = false;
			$attrib['outer']['type'] = 'application/x-shockwave-flash';
			if(array_key_exists('height',$data))
			{
				$attrib['outer']['height'] = $data['height'] + 25;
			}

			$param['outer']['allowFullScreen'] = 'true';
			$param['outer']['allowScriptAccess'] = 'always';
			$vars = array(
			'movie' => $data['src'],
			'btncolor' => '0xeeeeee',
			'accentcolor' => '0xcccccc',
			'txtcolor' => '0xeeeeee',
			'volume' => 50,
			'autoload' => 'on',
			'autoplay' => 'off',
			);
			if(array_key_exists('title',$data))
			{
				$vars['vTitle'] = $data['title'];
				$vars['showTitle'] = 'yes';
			}
			if(array_key_exists('autoplay',$data) || in_array('autoplay',$data))
			{
				$vars['autoplay'] = 'on';
			}
			$varPairs = array();
			foreach($vars as $key => $val)
			{
				$varPairs[] = $key.'='.$val;
			}
			$flashVars = implode('&amp;',$varPairs);

			$param['outer']['movie'] = '/assets/OSFlvPlayer_v4.2/OSplayer.swf?'.$flashVars;
			break;
		// Flash
		case 'swf':
			$fallback_text = $fallbacks['flash'];
			$inner = false;
			$attrib['outer']['type'] = 'application/x-shockwave-flash';
			$attrib['outer']['data'] = $data['src'];
			$param['outer']['movie'] = $data['src'];
			if(array_key_exists('FlashVars',$data))
			{
				$param['outer']['FlashVars'] = $data['FlashVars'];
			}
			break;
		// WebM (VP8) video
		case 'webm':
			$tag['outer'] = 'video';
			$inner = false;
			$fallback_text = $fallbacks['webm_VP8'];

			// set source attributes and params
			$attrib['outer']['src'] = $data['src'];
			$attrib['outer']['controls'] = 'controls';

			break;
		// Scaleable Vector Graphics
		case 'svg':
			$attrib['outer']['classid'] = 'clsid:78156a80-c6a1-4bbf-8e6a-3cd390eeb4e2';
			$param['outer']['src'] = $data['src'];
			$fallback_text = $fallbacks['svg'];
			break;
		// MPEG audio
		case 'm4a':
		case 'm4b':
		case 'mp3':
			$tag['outer'] = 'audio';

			// set source attributes and params
			$attrib['outer']['src'] = $data['src'];
			$attrib['outer']['controls'] = 'controls';

			$inner = false;
			$fallback_text = $fallbacks[$ext];
			break;
		}

		// now begin generating player code

		$code = '<'.$tag['outer'];
		
		foreach($attrib['outer'] as $attr => $val)
		{
			$code .= " {$attr}=\"{$val}\"";
		}
		$code .= ">\r";

		// sources for audio or video tags
		foreach($sources as $src)
		{
			$code .= "\t<source src='{$src['src']}' type='{$src['type']}; codecs=\"{$src['codecs']}\"' />\r";
		}
		// params for object tag
		foreach($param['outer'] as $name => $value)
		{
			$code .= "\t<param name=\"{$name}\" value=\"{$value}\" />\r";
		}

		if($inner)
		{
			$code .= "\t<!--[if gte IE 7]>-->\r";

			$code .= '<'.$tag['inner'];
			
			foreach($attrib['inner'] as $attr => $val)
			{
				$code .= " {$attr}=\"{$val}\"";
			}
			$code .= ">\r";

			foreach($param['inner'] as $name => $value)
			{
				$code .= "\t\t<param name=\"{$name}\" value=\"{$value}\" />\r";
			}

			$code .= "\t<p>".$fallback_text."</p>\r";
			$code .= "\t</{$tag['inner']}>\r";
			$code .= "\t<!--<![endif]-->\r";
		}
		else
		{
			$code .= $fallback_text;
		}
		$code .= "</{$tag['outer']}>\r";
		
		// do external controller if requested
		if($external_controller)
		{

			$code .= '<'.$tag['controller'];
			
			foreach($attrib['controller'] as $attr => $val)
			{
				$code .= " {$attr}=\"{$val}\"";
			}
			$code .= ">\r";

			foreach($param['controller'] as $name => $value)
			{
				$code .= "\t\t<param name=\"{$name}\" value=\"{$value}\" />\r";
			}

			$code .= "\t<p>".$fallback_text."</p>\r";
			$code .= "\t</{$tag['controller']}>\r";
		}

		return $code;
	}
}

if ( ! function_exists('html5_media'))
{
	function html5_media ($source, $params = NULL)
	{
		// get language files
		$CI =& get_instance();
		$CI->lang->load('mime');
		$fallbacks = $CI->lang->line('fallbacks');
	
		// get list of supported MIME types
		$CI->config->load('mime_info');
		$types = $CI->config->item('html5_types');

		$sources = array();
		$tracks = array();
		$links = array();
		
		// test if source is an array
		if(!is_array($source))
		{
			$source = array('src' => $source);
		}
		// test if there is a single source, if so add to list
		if(array_key_exists('src',$source))
		{
			$sources[] = $source;
		}
	
		// determine media type of first source in list, set tag to use
		if(array_key_exists('type', $sources[0]))
		{
			$type = $sources[0]['type'];
		}
		else
		{
			$ext = preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$sources[0]['src']);
			$type = $types[$ext];
		}
	
		$tag = preg_replace('#^([a-z]+)/(.*)$#','$1',$type);
		unset($ext,$type);
	
		// now begin generating player code
		// opening tag
		$code = '<'.$tag;
		if($params != NULL && is_array($params))
		{
			foreach($params as $attr => $val)
			{
				$code .= " {$attr}=\"{$val}\"";
			}
		}
		$code .= ">\r";
	
		// source list
		foreach($sources as $src)
		{
			$code .= "\t<source src='".$src['src']."' type='";
			if(array_key_exists('type',$src))
			{
				$type = $src['type'];
			}
			else
			{
				$ext = preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$src['src']);
				$type = $types[$ext];
			}
			$code .= $type;
			if(array_key_exists('codecs',$src))
			{
				$code .= "; codecs=\"{$src['codecs']}\"";
			}
			$code .= "' />\r";
		
			$format = preg_replace('#^([a-z]+)/(.*)$#','$2',$type);
			$links[] = '<a href="'.$src['src'].'">'.$format.'</a>';
			unset($ext,$type,$format);
		}
		
		// track list if exists
		if(array_key_exists('track',$params))
		{
		
			// test if there is a single source, if so add to list
			if(array_key_exists('src',$params['track']))
			{
				$tracks[] = $params['track'];
			}
		
			foreach($tracks as $trk)
			{
				$code .= "\t<track src='".$trk['src']."' kind='".$trk['kind']."' />\r";
			}
		}
	
		// fallback text
		$code .= "\t<p>".sprintf($fallbacks['html5_media'],$tag);
		$code .= implode(' | ',$links);
		$code .= "</p>\r";
		$code .= "</{$tag}>\r";
		
		return $code;	
	}
}


if ( ! function_exists('is_video_link'))
{
	// test link for matching video sites (YouTube, Vimeo, etc)
	function is_video_link($url)
	{
		$matches = preg_match('#^http://([a-z\.]*)([youtube|vimeo]{1})\.([a-z]*)/?#',$url);
		return $matches;
	}
}

if ( ! function_exists('external_player'))
{
	// player for external sites (YouTube, Vimeo, etc)
	function external_player($url,$params=array())
	{
		$info = parse_url($url);
		$hostA = explode('.',$info['host']);
		$host = '';
		$id = '';
		$html = '';
		$valid_hosts = array('youtube','vimeo');
		foreach($hostA as $part)
		{
			$match = strtolower($part);
			if(in_array($match,$valid_hosts))
			{
				$host = $match;
			}
		}
		
		switch($host)
		{
		// YouTube
		case 'youtube':
			if($info['path'] == '/watch')
			{
				$id = preg_replace('#^(.*)v=([^&]+)(.*)$#','$2',$info['query']);
			}
			else
			{
				$id = preg_replace('#^/v/([^&]+)(.*)$#','$1',$info['path']);
			}
			
			if(!array_key_exists('width',$params))
			{
				$params['width'] = 640;
			}
			if(!array_key_exists('height',$params))
			{
				$params['height'] = 385;
			}
			else
			{
				$params['height'] = $params['height'] + 25;

			}
			$params['src'] = 'http://www.youtube.com/embed/'.$id;
			$params['frameborder'] = 0;
			$params['type'] = 'text/html';
			$params['class'] = 'youtube-player';
			
			break;
		case 'vimeo':
			$path = trim($info['path'],'/');
			if(is_numeric($path))
			{
				$id = $path;
			}
			else if(array_key_exists('fragment',$info) && is_numeric($info['fragment']))
			{
				$id = $info['fragment'];
			}
			else
			{
				$id = preg_replace('#^(.*)/([0-9]*)$#','$2',$info['path']);
			}

			$params['src'] = 'http://player.vimeo.com/video/'.$id;
			$params['frameborder'] = 0;

			if(!array_key_exists('width',$params))
			{
				$params['width'] = 400;
			}
			if(!array_key_exists('height',$params))
			{
				$params['height'] = 225;
			}
			
			break;
		default:
			break;
		}

		if(count($params) > 0)
		{
			$html = '<iframe';
			foreach($params as $param => $val)
			{
				$html .= " {$param}=\"{$val}\"";
			}
			$html .= "></iframe>\r";
		}
		
		return $html;
	}
}


/*
<iframe 
src="http://player.vimeo.com/video/9436189" 
width="400" height="225" frameborder="0"></iframe>

http://vimeo.com/16094710

Array
(
    [scheme] => http
    [host] => vimeo.com
    [path] => /16094710
)

http://vimeo.com/staffpicks#16027313
Array
(
    [scheme] => http
    [host] => vimeo.com
    [path] => /staffpicks
    [fragment] => 16027313
)

http://vimeo.com/groups/stopmotion/videos/16101179
Array
(
    [scheme] => http
    [host] => vimeo.com
    [path] => /groups/stopmotion/videos/16101179
)




<iframe class="youtube-player" type="text/html" width="640" height="385" src="http://www.youtube.com/embed/VIDEO_ID" frameborder="0">
</iframe>
*/


/* End of file image_helper.php */
/* Location: ./system/application/helpers/image_helper.php */