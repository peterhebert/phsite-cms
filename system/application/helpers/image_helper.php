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

if ( ! function_exists('view_image'))
{

	function view_image($path,$size,$img_tag_attr = '',$square=FALSE ) {

		/* 
		parameters for function
		
		src				path to image
		size			size to fit image to by largest dimension
		ratio			force ratio of resized image
		img_tag_attr	array of attributes for image tag : ('alt' => 'image alt text', 'id' => 'image tag id')
		
		NOTE: USES CONSTANTS 'MEDIA_PATH_ORIG','MEDIA_PATH_CACHE' for determining where to store image
		*/

		if($img_tag_attr == '') {
			$img_tag_attr = array();
		}

		// Path to original image and to resized version
		$orig_image_path = $path;
		$path_rel = str_replace($_SERVER["DOCUMENT_ROOT"],'',$path);

		$orig_image_path_rel = trim($path,'/');
		
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
				} else if ($orig_ratio < 1) { // portrait or square
					$max_orig = $orig_height;
				}
	
				// determine file name of scaled image
				$pi = pathinfo($orig_image_path);
				$dest_dir = str_replace(MEDIA_PATH_ORIG,MEDIA_PATH_CACHE,$pi['dirname']);

				if($square === TRUE && $orig_ratio != 1) {
					$fn_copy_short = $pi['filename'].'_'.$size.'_'.$crop_ratio.'.jpg';
				} else {
					$fn_copy_short = $pi['filename'].'_'.$size.'.jpg';
				}
				$scaled_image_path = $dest_dir.'/'.$fn_copy_short;

				$scaled_image_path_rel = str_replace($_SERVER["DOCUMENT_ROOT"],'',$scaled_image_path);
				$scaled_image_path_rel = trim($scaled_image_path_rel,'/');
				
				if(!file_exists ( $dest_dir ) ){
					if(!mkdir($dest_dir)) {
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
				$result = "file is not an image";
			}
		} else {
			$result = "no image found";
		}

		return $result;
	
	} // end view_image()
}

if ( ! function_exists('get_metadata'))
{

	function get_metadata($path) {
		$server_path = $_SERVER["DOCUMENT_ROOT"].$path;
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

if ( ! function_exists('media_player'))
{
	// bakes up various embedded media players using valid xhtml or html5 markup
	
	function media_player($src,$width,$height,$controls = true,$loop = false,$autoplay = false,$misc_vars=array())
	{
		$code = 'media player';
		$params_outer = array();
		$other_outer = '';
		$fallback_text = '';
		$tag_outer = 'object';

		$inner = true;
		$tag_inner = 'object';
		$params_inner = array();
		$other_inner = '';
		
		$pinfo = pathinfo($src);
		$ext = $pinfo['extension'];

		switch ($ext)
		{
		// Flash video
		case 'flv':
		case 'f4v':
			$height = $height + 40;
			$type = 'application/x-shockwave-flash';
			$player = '/assets/osflv_player_v3.swf';
			$other_outer = " type=\"{$type}\" data=\"{$player}\"";
			$params_outer[] = array('name' => 'movie', 'value' => $player);
			
			if(array_key_exists('allowFullScreen',$misc_vars))
			{
				$params_outer[] = array('name' => 'allowFullScreen', 'value' => $misc_vars['allowFullScreen']);
			}

			$flash_vars[] = 'movie='.$src;
			if(array_key_exists('FlashVars',$misc_vars))
			{
				foreach ($misc_vars['FlashVars'] as $param => $value) {
					$flash_vars[] = $param.'='.$value;
				}
			}
			$FlashVars = implode('&',$flash_vars);
			$params_outer[] = array('name' => 'FlashVars', 'value' => $FlashVars);

			$params_outer[] = array('name' => '', 'value' => '');

			$inner = false;
			break;

		// Ogg video
		case 'ogv':
		case 'ogm':
			$tag_outer = 'video';
			$tag_inner = 'applet';
			$type = 'video/ogg';

			$fallback_text = "\t<p>You need a browser that supports <a href=\"http://en.wikipedia.org/wiki/Theora#Browser_plugins\">Ogg Theora video</a> or a valid <a href=\"http://java.com/en/download/manual.jsp\">Java runtime</a> in order to play this content.</p>\r";
			
			$other_outer .= " src=\"{$src}\"";
			$other_inner .= " code=\"com.fluendo.player.Cortado.class\" archive=\"http://theora.org/cortado.jar\"";

			if($controls)
			{
				$other_outer .= ' controls';
			}
			if($autoplay)
			{
				$other_outer .= ' autoplay';
			}
			if($loop)
			{
				$other_outer .= ' loop';
			}
			if(array_key_exists('poster',$misc_vars))
			{
				$other_outer .= " poster=\"{$misc_vars['poster']}\"";
			}

			$params_inner[] = array('name' => 'url', 'value' => $src);

			break;
		}

		// now begin generating player code
		$code = "<!-- begin media player embed -->\n";
		$code .= "<{$tag_outer} width=\"{$width}\" height=\"{$height}\"{$other_outer}>\r";

		foreach($params_outer as $param)
		{
			$code .= "\t<param name=\"".$param['name']."\" value=\"".$param['value']."\" />\r";
		}

		if($inner)
		{
			$code .= "\t<!--[if !IE]>-->\r";
			$code .= "\t<{$tag_inner} width=\"{$width}\" height=\"{$height}\"{$other_inner}>\r";

			foreach($params_inner as $param)
			{
				$code .= "\t\t<param name=\"{$param['name']}\" value=\"{$param['value']}\" />\r";
			}

			$code .= "\t".$fallback_text;
			$code .= "\t</{$tag_inner}>\r";
			$code .= "\t<!--<![endif]-->\r";
		}
		else
		{
			$code .= $fallback_text;
		}

		$code .= "</{$tag_outer}>\r";
		$code .= "<!-- end media player embed -->\n";
		

		return $code;
	}
}

/*if ( ! function_exists('media_player'))
{
		
			// Real video
			case 'rv':
			case 'rm':
			case 'ram':
				$fallback_text = "\t<p>You need <a href=\"http://www.real.com/\" rel=\"external\">RealPlayer</a> in order to play this content</p>\r";
				if($ext == 'rv')
				{
					$type = 'video/vnd.rn-realvideo';
				}
				else
				{
					$type = 'audio/vnd.rn-realaudio';
				}
				$other_outer = "classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\"";

				$params_inner[] = array('name' => 'src', 'value' => $src);
				$params_inner[] = array('name' => 'controls', 'value' => 'imagewindow');
				$params_inner[] = array('name' => 'console', 'value' => 'video');
				if($autoplay)
				{
					$params_inner[] = array('name' => 'autostart', 'value' => 'true');
				}
				else
				{
					$params_inner[] = array('name' => 'autostart', 'value' => 'false');
				}
				if($loop)
				{
					$params_inner[] = array('name' => 'loop', 'value' => 'true');
				}
				else
				{
					$params_inner[] = array('name' => 'loop', 'value' => 'false');
				}
				break;
			// QuickTime / MP4
			case 'mov':
			case 'qt':
			case 'm4u':
			case 'm4v':
			case 'mp4':
			case 'mpeg4':
				$other_outer = "classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\"";
				break;
			// Windows Media Player
			case 'asf':
			case 'asx':
			case 'wmv':
			case 'wmx':
			case 'wvx':
				if($ext == 'wmv')
				{
					$type = 'video/x-ms-wmv';
				}
				else
				{
					$type = 'video/x-ms-asf';
				}
				$other_outer = "classid=\"CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\"";

				break;
		}

		$code = "<!-- begin media player embed -->\n";
		$code .= "<{$tag_outer} width=\"{$width}\" height=\"{$height}\" {$other_outer}>\r";

		if(count($params_outer) > 0)
		{
			$foreach($params_outer as $param)
			{
				$code .= "\t<param name=\"".$param['name']."\" value=\"".$param['value']."\" />\r";
			}
		}

		if($inner)
		{
			$code .= "\t<!--[if !IE]>-->\r";
			$code .= "\t<{$tag_inner} type=\"{$type}\" width=\"{$width}\" height=\"{$height}\" {$other_inner}>\r";
			if(count($params_inner) > 0)
			{
				$foreach($params_inner as $param)
				{
					$code .= "\t<param name=\"{$param['name']}\" value=\"{$param['value']}\" />\r";
				}
			}
			$code .= $fallback_text;
			$code .= "\t</{$tag_inner}>\r";
			$code .= "\t<!--<![endif]-->\r";
		}
		else
		{
			$code .= $fallback_text;
		}

		$code .= "</{$tag_outer}>\r";
		$code .= "<!-- end media player embed -->\n";


		return $code;
	}

}

*/

if ( ! function_exists('flv_player'))
{

	function flv_player($file, $width = 640, $height = 360, $fullscreen = true, $flvars=array()) {

		$player = '/assets/osflv_player_v225/player.swf';
		$flash_vars[] = 'movie='.$file;
		
		foreach ($flvars as $param => $value) {
			$flash_vars[] = $param.'='.$value;
		}
		$FlashVars = implode('&',$flash_vars);
		
		$code = "<!-- begin OS FLV player -->\n";
		$code .= "<object type=\"application/x-shockwave-flash\" data=\"{$player}\" width=\"{$width}\" height=\"{$height}\">\n";
		$code .= "<param name=\"movie\" value=\"{$player}\" />\n";
		$code .= "<param name=\"allowFullScreen\" value=\"{$fullscreen}\">\n";
		$code .= "<param name=\"FlashVars\" value=\"{$FlashVars}\" />\n";
		$code .= "</object>\n";
		$code .= "<!-- end OS FLV player -->\n";

		return $code;
	}

}

if ( ! function_exists('embed_ogg_video'))
{
	function embed_ogg_video($src,$width,$height,$boolVars=array('controls'),$poster='')
	{
		$code = "<!-- begin HTML5 ogg video tag with Cortado fallback -->\n";
		$code .= "<video src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"";

		if(count($boolVars)>0)
		{
			foreach ($boolVars as $val) {
				$code .= " {$val}";
			}
		}
		if($poster != '')
		{
			$code .= " poster=\"{$poster}\"";
		}

		$code .= ">\n";
		$code .= "<applet code=\"com.fluendo.player.Cortado.class\" archive=\"http://theora.org/cortado.jar\" width=\"{$width}\" height=\"{$height}\">\n";
		$code .= "<param name=\"url\" value=\"{$src}\"/>\n";
		$code .= "</applet>\n";
		$code .= "</video>\n";
		$code .= "<!-- end Ogg video player -->\n";

		return $code;
	}
}

if ( ! function_exists('embed_quicktime'))
{
	function embed_quicktime($src,$width,$height,$controller=true,$autoplay=false,$loop=false)
	{
		if($controller)
		{
			$height = $height + 16;
		}

		$code = "<!-- begin QuickTime video embed -->\n";
		$code .= "<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" width=\"{$width}\" height=\"{$height}\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\">\r";
		$code .= "\t<param name=\"src\" value=\"{$src}\" />\r";
		$code .= "\t<param name=\"type\" value=\"video/quicktime\" />\r";
		if($autoplay)
		{
			$code .= "\t<param name=\"autoplay\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"autoplay\" value=\"false\" />\r";
		}
		if($controller)
		{
			$code .= "\t<param name=\"controller\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"controller\" value=\"false\" />\r";
		}
		if($loop)
		{
			$code .= "\t<param name=\"loop\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"loop\" value=\"false\" />\r";
		}

		$code .= "\t<!--[if !IE]>-->\r";

		$code .= "\t<object data=\"{$src}\" type=\"video/quicktime\" width=\"{$width}\" height=\"{$height}\">\r";
		if($autoplay)
		{
			$code .= "\t\t<param name=\"autoplay\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t\t<param name=\"autoplay\" value=\"false\" />\r";
		}
		if($controller)
		{
			$code .= "\t\t<param name=\"controller\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t\t<param name=\"controller\" value=\"false\" />\r";
		}
		$code .= "\t</object>\r";
		$code .= "\t<!--<![endif]-->\r";

		$code .= "</object>\r";
		$code .= "<!-- end QuickTime video embed -->\n";
		return $code;
	}

}

if ( ! function_exists('embed_wmv'))
{
	function embed_wmv($src,$width,$height,$controller=true,$autoplay=false)
	{
		if($controller)
		{
			$height = $height + 64;
		}

		$code = "<!-- begin Windows Media video embed -->\n";
		$code .= "<object classid=\"CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\" id=\"player\" width=\"{$width}\" height=\"{$height}\">\n";
		$code .= "\t<param name=\"url\" value=\"{$src}\" />\n";
		$code .= "\t<param name=\"src\" value=\"{$src}\" />\n";

		if($autoplay)
		{
			$code .= "\t<param name=\"autostart\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"autostart\" value=\"false\" />\r";
		}
		if($controller)
		{
			$code .= "\t<param name=\"controller\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"controller\" value=\"false\" />\r";
		}


		$code .= "\t<!--[if !IE]>-->\n";
		$code .= "\t<object type=\"video/x-ms-wmv\" data=\"{$src}\" width=\"{$width}\" height=\"{$height}\">\n";
		$code .= "\t\t<param name=\"src\" value=\"{$src}\" />\n";

		if($autoplay)
		{
			$code .= "\t<param name=\"autostart\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"autostart\" value=\"false\" />\r";
		}
		if($controller)
		{
			$code .= "\t<param name=\"controller\" value=\"true\" />\r";
		}
		else
		{
			$code .= "\t<param name=\"controller\" value=\"false\" />\r";
		}

		$agent = $_SERVER['HTTP_USER_AGENT'];
		// Platforms and browsers
		if (strpos($agent, 'Macintosh')) {
			$platform = 'Macintosh';
			$code .= "\t\t<p>You need the <a href=\"http://www.telestream.net/flip4mac-wmv/overview.htm\" rel=\"external\">Flip4Mac WMV QuickTime component</a> in order to play this content.</p>\n";
		} elseif (strpos($agent, 'Windows')) {
			$platform = 'Windows';
			$code .= "\t\t<p>You need <a href=\"http://www.microsoft.com/windows/windowsmedia/player/download/download.aspx\" rel=\"external\">Windows Media Player</a> in order to play this content.</p>\n";
		} elseif (strpos($agent, 'Linux')) {
			$platform = 'Linux';
			$code .= "\t\t<p>You need <a href=\"http://ffmpeg.org\" rel=\"external\">FFmpeg</a> or <a href=\"http://www.mplayerhq.hu/design7/dload.html\" rel=\"external\">Mplayer + Mencoder</a> in order to play this content.</p>\n";
		}

		$code .= "\t</object>\n";
		$code .= "\t<!--<![endif]-->\n";
		$code .= "</object>\n";
		$code .= "<!-- end Windows Media video embed -->\n";
		return $code;
	}

}

/*




if($_SERVER['HTTP_USER_AGENT'])
{
}

  ereg(,$reg);


// Simple browser detection
$is_lynx = $is_gecko = $is_winIE = $is_macIE = $is_opera = $is_NS4 = $is_safari = $is_chrome = false;

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false) {
	$is_lynx = true;
} elseif ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome') !== false ) {
	$is_chrome = true;
} elseif ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'webkit') !== false ) {
	$is_safari = true;
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false) {
	$is_gecko = true;
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Win') !== false) {
	$is_winIE = true;
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false) {
	$is_macIE = true;
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) {
	$is_opera = true;
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false) {
	$is_NS4 = true;
}

$is_IE = ( $is_macIE || $is_winIE );*/

/* End of file image_helper.php */
/* Location: ./system/application/helpers/image_helper.php */