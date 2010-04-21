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

	function view_image($path,$size,$img_tag_attr = array(),$square=FALSE ) {

		/* 
		parameters for function
		
		path			path to image
		size			size to fit image to by largest dimension
		img_tag_attr	array of attributes for image tag : ('alt' => 'image alt text', 'id' => 'image tag id')
		ratio			force ratio of resized image to square
		
		NOTE: USES CONSTANTS 'MEDIA_PATH_ORIG','MEDIA_PATH_CACHE' for determining where to store image
		*/

		// Path to original image and to resized version
		$orig_image_path = $path;

		$path_rel = str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$path);
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
				if(strstr($orig_image_path,MEDIA_PATH_ORIG))
				{
					$dest_dir = str_replace(MEDIA_PATH_ORIG,MEDIA_PATH_CACHE,$pi['dirname']);
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

if ( ! function_exists('media_player'))
{
	// bakes up various embedded media players using valid xhtml or html5 markup
	
	function media_player($src,$width,$height,$controls = true,$loop = false,$autoplay = false,$misc_vars=array())
	{
		$code = 'media player';
		$params_outer = array();
		$other_outer = '';
		$fallback_text = '';
		$external_controller = '';

		$inner = true;
		$params_inner = array();
		$other_inner = '';
		
		$pinfo = pathinfo($src);
		$ext = $pinfo['extension'];
		$is_IE = false;

		$agent = $_SERVER['HTTP_USER_AGENT'];
		// Platforms and browsers
		if (strpos($agent, 'Macintosh')) {
			$platform = 'Macintosh';
		} elseif (strpos($agent, 'Windows')) {
			$platform = 'Windows';
		} elseif (strpos($agent, 'Linux')) {
			$platform = 'Linux';
		}
		if (strpos($agent, 'MSIE')) {
			$is_IE = true;
		}

		switch ($ext)
		{
		// Flash video
		case 'flv':
		case 'f4v':
			$height = $height + 40;
			$type = 'application/x-shockwave-flash';
			$player = '/assets/osflv_player_v3.swf';
			$other_outer = " type=\"{$type}\" data=\"{$player}\"";

			$params_outer = array(
			'movie' => $player,
			'allowFullScreen' => 'true',
			);

			if(array_key_exists('allowFullScreen',$misc_vars))
			{
				$params_outer['allowFullScreen'] = $misc_vars['allowFullScreen'];
			}

			$flash_vars[] = 'movie='.$src;
			if(array_key_exists('FlashVars',$misc_vars))
			{
				foreach ($misc_vars['FlashVars'] as $param => $value) {
					$flash_vars[] = $param.'='.$value;
				}
			}
			$params_outer['FlashVars'] = implode('&',$flash_vars);


			$inner = false;
			break;

		// Ogg video
		case 'ogv':
		case 'ogm':
			$type = 'video/ogg';
			$inner = false;

			$fallback_text = "\t<p>You need a browser that supports <a href=\"http://en.wikipedia.org/wiki/Theora#Browser_plugins\">Ogg Theora video</a> or a valid <a href=\"http://java.com/en/download/manual.jsp\">Java runtime</a> in order to play this content.</p>\r";
			
			$other_outer .= " src=\"{$src}\"";
			$other_inner .= " type=\"application/x-java-applet\"";

			$params_inner = array(
			'archive' => '/assets/cortado-ovt-stripped-0.2.2.jar',
			'code' => 'com.fluendo.player.Cortado.class',
			'url' => $src,
			'keepAspect' => 'true'
			);

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

			break;
		// QuickTime / MP4
		case 'mov':
		case 'qt':
		case 'm4u':
		case 'm4v':
		case 'mp4':
		case 'mpeg4':

			switch ($ext)
			{
			case 'm4u':
				$type = 'video/x-mpegurl';
				break;
			case 'm4v':
				$type = 'video/mp4';
				break;
			case 'mp4':
			case 'mpeg4':
				$type = 'video/mpeg';
				break;
			default:
				$type = 'video/quicktime';
			}

			$other_outer = " classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\"";
			$other_inner = "  data=\"{$src}\" type=\"{$type}\"";

			$params_outer = array(
			'src' => $src,
			'type' => $type,
			'controller' => 'false',
			'autoplay' => 'false',
			'loop' => 'false',
			);
			$params_inner = array(
			'controller' => 'false',
			'autoplay' => 'false',
			'loop' => 'false',
			);

			if($controls)
			{
				$params_outer['controller'] = 'true';
				$params_inner['controller'] = 'true';
			}
			if($autoplay)
			{
				$params_outer['autoplay'] = 'true';
				$params_inner['autoplay'] = 'true';
			}
			if($loop)
			{
				$params_outer['loop'] = 'true';
				$params_inner['loop'] = 'true';
			}
			$height = $height + 16;

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
			$other_outer = " classid=\"CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\"";
			$other_inner = "  data=\"{$src}\" type=\"{$type}\"";
			//$height = $height + 64;
			switch ($platform)
			{
			case 'Windows':
				$fallback_text = "\t\t<p>You need <a href=\"http://www.microsoft.com/windows/windowsmedia/player/download/download.aspx\" rel=\"external\">Windows Media Player</a> in order to play this content.</p>\n";
				break;
			case 'Macintosh':
				$fallback_text = "\t\t<p>You need the <a href=\"http://www.telestream.net/flip4mac-wmv/overview.htm\" rel=\"external\">Flip4Mac WMV QuickTime component</a> in order to play this content.</p>\n";
				break;
			case 'Linux':
				$fallback_text = "\t\t<p>You need <a href=\"http://ffmpeg.org\" rel=\"external\">FFmpeg</a> or <a href=\"http://www.mplayerhq.hu/design7/dload.html\" rel=\"external\">Mplayer + Mencoder</a> in order to play this content.</p>\n";
				break;
			}

			$params_outer = array(
			'url' => $src,
			'src' => $src,
			'showcontrols' => 'false',
			'autostart' => 'false',
			);
			$params_inner = array(
			'src' => $src,
			'controller' => 'false',
			'autoplay' => 'false',
			'loop' => 'false',
			);

			if($controls)
			{
				$params_outer['showcontrols'] = 'true';
				$params_inner['controller'] = 'true';
			}
			if($autoplay)
			{
				$params_outer['autostart'] = 'true';
				$params_inner['autostart'] = 'true';
			}
			if($loop)
			{
				$params_outer['loop'] = 'true';
				$params_inner['loop'] = 'true';
			}

			break;
		// Real Player
		case 'rv':
		case 'ra':
		case 'rm':
		case 'ram':
			$type = 'application/vnd.rn-realmedia';
			$other_outer = " classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\"";
			$other_inner = "  data=\"{$src}\" type=\"{$type}\"";

			$params_outer = array(
			'src' => $src,
			'type' => $type,
			'controls' => 'All',
			'autostart' => 'false',
			'loop' => 'false',
			'console' => 'video',
			);
			$params_inner = array(
			'src' => $src,
			'controls' => 'All',
			'autostart' => 'false',
			'loop' => 'false',
			'console' => 'video',
			);
			if($loop)
			{
				$params_outer['loop'] = 'true';
				$params_inner['loop'] = 'true';
			}

			if($controls)
			{
				$external_controller .= "<object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"{$width}\" height=\"30\">\r";
				$external_controller .= "\t<param name=\"src\" value=\"{$src}\" />\r";
				$external_controller .= "\t<param name=\"controls\" value=\"ControlPanel\" />\r";
				$external_controller .= "\t<param name=\"console\" value=\"video\" />\r";
				if($autoplay)
				{
					$external_controller .= "\t<param name=\"autostart\" value=\"true\" />\r";
				}
				else
				{
					$external_controller .= "\t<param name=\"autostart\" value=\"false\" />\r";
				}
				$external_controller .= "</object>\r";
			}

			$fallback_text = "\t\t<p>You need <a href=\"http://www.real.com\" rel=\"external\">RealPlayer</a> in order to play this content.</p>\n";

			break;
		}

		// now begin generating player code

		$code = "<!-- begin media player embed -->\n";
		$code .= "<object width=\"{$width}\" height=\"{$height}\"{$other_outer}>\r";

		foreach($params_outer as $name => $value)
		{
			$code .= "\t<param name=\"{$name}\" value=\"{$value}\" />\r";
		}

		if($inner)
		{
			$code .= "\t<!--[if gte IE 7]>-->\r";
			$code .= "\t<object width=\"{$width}\" height=\"{$height}\"{$other_inner}>\r";

			foreach($params_inner as $name => $value)
			{
				$code .= "\t\t<param name=\"{$name}\" value=\"{$value}\" />\r";
			}

			$code .= "\t".$fallback_text;
			$code .= "\t</object>\r";
			$code .= "\t<!--<![endif]-->\r";
		}
		else
		{
			$code .= $fallback_text;
		}

		$code .= "</object>\r";
		$code .= $external_controller;
		$code .= "<!-- end media player embed -->\n";
		

		return $code;
	}
}

if ( ! function_exists('video_for_everybody'))
{
/*
based upon : http://camendesign.com/code/video_for_everybody
note: $src parameter is without a file extension, and would require 
all versions of the video to have the same basename.
I have modified the poster so it does not need to be a JPEG but any image type (provide full url with extension)
*/
	function video_for_everybody($src,$width,$height,$flv_player='/assets/osflv_player_v3.swf',$poster=false) {
		$ht_qt = $height + 15;
		$ht_fl = $height + 20;
		$code = "<!-- 'Video for Everybody' v0.2 by Kroc Camen <camendesign.com> cc-by -->\r";
		$code .= "<video width=\"{$width}\" height=\"{$height}\" controls=\"controls\"";

		if($poster)
		{
			$code .= " poster=\"{$poster}\"";
		}
		$code .= ">\r";

		$code .= "<source src=\"{$src}.ogv\" type=\"video/ogg\" />\r";
		$code .= "<source src=\"{$src}.mp4\" type=\"video/mp4\" />\r";
		$code .= "<!--[if gt IE 6]>\r";
		$code .= "<object width=\"{$width}\" height=\"{$ht_qt}\" classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\"><!\r";
		$code .= "[endif]-->\r";
		$code .= "<!--[if !IE]><!-->\r";
		$code .= "<object width=\"{$width}\" height=\"{$ht_qt}\" type=\"video/quicktime\" data=\"{$src}.mp4\">\r";
		$code .= "<!--<![endif]-->\r";
		$code .= "<param name=\"src\" value=\"{$src}.mp4\" />\r";
		$code .= "<param name=\"autoplay\" value=\"false\" />\r";
		$code .= "<param name=\"showlogo\" value=\"false\" />\r";
		$code .= "<object width=\"{$width}\" height=\"{$ht_fl}\" type=\"application/x-shockwave-flash\" data=\"{$flv_player}.swf?image={$poster}&amp;file={$src}.mp4\">\r";
		if($poster)
		{
			$code .= "<param name=\"movie\" value=\"{$flv_player}.swf?image={$poster}&amp;file={$src}.mp4\" />\r";
			$code .= "<img src=\"{$poster}\" width=\"{$width}\" height=\"{$height}\" />\r";
		}
		else
		{
			$code .= "<param name=\"movie\" value=\"{$flv_player}.swf?file={$src}.mp4\" />\r";
		}
		$code .= "<p><strong>No video playback capabilities detected.</strong> Why not try to download the file instead?<br />\r";
		$code .= "<a href=\"{$src}.mp4\">MPEG4 / H.264 &quot;.mp4&quot; (Windows / Mac)</a> | <a href=\"{$src}.ogv\">Ogg Theora &amp; Vorbis &quot;.ogv&quot; (Linux)</a></p>\r";
		$code .= "<p>To play the video here in the webpage, please install one of the following:</p>\r";
		$code .= "<ul>\r";
		$code .= "<li>the latest  <a href=\"http://getfirefox.com\">Firefox</a> or <a href=\"http://apple.com/safari\">Safari</a> web browser</li>\r";
		$code .= "<li><a href=\"http://get.adobe.com/flashplayer/\">Adobe Flash Player</a></li>\r";
		$code .= "<li><a href=\"http://apple.com/quicktime/download/\">Apple QuickTime</a></li>\r";
		$code .= "</ul>\r";
		$code .= "</object><!--[if gt IE 6]><!-->\r";
		$code .= "</object><!--<![endif]-->\r";
		$code .= "</video>\r";
		$code .= "<!-- end 'Video for Everybody' code -->\r";

		return $code;

	}
}

/* End of file image_helper.php */
/* Location: ./system/application/helpers/image_helper.php */