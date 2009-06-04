<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

if ( ! function_exists('file_is_image'))
{
	function is_image($path) {
	
		$is_image = FALSE;

		$acceptable_types = array(
		'image/gif' => 'gif',  
		'image/jpeg' => 'jpeg',  
		'image/png' => 'png',  
		'application/x-shockwave-flash' => 'swf',  
		'image/psd' => 'psd',  
		'image/bmp' => 'bmp',  
		'image/tiff' => 'tiff',  
		'image/tiff' => 'tiff',  
		'image/jp2' => 'jp2',  
		'image/iff' => 'iff',  
		'image/vnd.wap.wbmp' => 'bmp',  
		'image/xbm' => 'xbm',  
		'image/vnd.microsoft.icon' => 'ico'
		); 

		if(file_exists($path))
		{
			$pathinfo = pathinfo($path);
			$file_info = getimagesize($path);
			if(in_array($pathinfo['extension'],$acceptable_types) && $fileinfo !== FALSE && array_key_exists($fileinfo['mime'],$acceptable_types))
			{
				$is_image = TRUE;
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
		$orig_image_path = $_SERVER["DOCUMENT_ROOT"].$path;
		$orig_image_path_rel = trim($path,'/');
		
		if(file_exists($orig_image_path)) {
			
			$orig_size = getimagesize($orig_image_path);
			//print_r( $orig_size);
			
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

if ( ! function_exists('flv_player'))
{

	function flv_player($file, $width = 640, $height = 360, $fullscreen = true, $flvars=array()) {

		$player = '/assets/osflv_player_v225/player.swf';
		$flash_vars[] = 'movie='.$file;
		
		foreach ($flvars as $param => $value) {
			$flash_vars[] = $param.'='.$value;
		}
		$FlashVars = implode('&',$flash_vars);
		
		$player_code = "<!-- begin OS FLV player -->\n";
		$player_code .= "<object type=\"application/x-shockwave-flash\" data=\"{$player}\" width=\"{$width}\" height=\"{$height}\">\n";
		$player_code .= "<param name=\"movie\" value=\"{$player}\" />\n";
		$player_code .= "<param name=\"allowFullScreen\" value=\"{$fullscreen}\">\n";
		$player_code .= "<param name=\"FlashVars\" value=\"{$FlashVars}\" />\n";
		$player_code .= "</object>\n";
		$player_code .= "<!-- end OS FLV player -->\n";

		return $player_code;
	}

}


/* End of file image_helper.php */
/* Location: ./system/application/helpers/image_helper.php */