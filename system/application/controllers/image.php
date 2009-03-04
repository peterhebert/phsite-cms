<?php

class Image extends Controller {

	function Image()
	{
		parent::Controller();	
		$this->load->helper('file');
	}
	
	function index()
	{
		echo "image controller";

	}

	function view()
	{

		$url_raw = $this->uri->uri_string();
		$url_params =  str_replace( '/image/view/', '', $url_raw);

		$param_keys = array('src','size','ratio','alt','name','title');
		$tag_keys = array('src','alt','name','title');
		
		$pattern = "/(src|size|ratio|alt|name|title)/";
		$params = preg_split($pattern, $url_params, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$image = array();
		$img_tag_prop = array();
		
		
		foreach($params as $index => $key)
		{
			if($index < count($params)-1) {
				$rawval = $params[$index+1];
			} else {
				$rawval = '';
			}

			
			if(in_array($key,$param_keys)) {
				if($key == 'src') {
					$myval = $_SERVER["DOCUMENT_ROOT"].rtrim($rawval,'/');
				} else {
					$myval = trim($rawval,'/');
				}
				$image[$key] = $myval;
			}

			if(in_array($key,$tag_keys)) {
				if($key == 'src') {
					$myval = rtrim($rawval,'/');
				} else {
					$myval = trim($rawval,'/');
				}
				$img_tag_prop[$key] = $myval;
			}

		}

		/*	echo "<pre>";
			print_r($image);
			echo "</pre>\n";

		if(file_exists($image['src']) ) {
			echo "<p>file found</p>\n";
		}*/


		if(array_key_exists('src',$image) === TRUE && file_exists($image['src'])) {
		
			if(array_key_exists('size',$image) === TRUE) {
				$config['width'] = $image['size'];
				$config['height'] = $image['size'];
				
				$config['source_image'] = $image['src'];
				
				$pi = pathinfo($image['src']);
				$dest_dir = str_replace(MEDIA_PATH_ORIG,MEDIA_PATH_CACHE,$pi['dirname']);
				$fn_copy_short = $pi['filename'].'_'.$image['size'].'.jpg';
				$config['new_image'] = $dest_dir.'/'.$fn_copy_short;

			}

			$success = 0;
			if(is_array($config)) {
				if(!file_exists($config['new_image'])) {
					$this->load->library('image_lib');
					$this->image_lib->initialize($config);
					if($this->image_lib->resize()) {
						$success = 1;
					} else {
						echo $this->image_lib->display_errors('<p>', '</p>');
					}
				} else {
					$success = 1;
				}
			}
			
			if($success === 1) {
				$img_tag_prop['src'] = $config['new_image'];
				$size = getimagesize($config['new_image']);
			} else {
				$size = getimagesize($image['src']);
				echo "resized image not found";
			}
			$img_tag_prop['width'] = $size[0];
			$img_tag_prop['height'] = $size[1];

			// print image
			echo img($img_tag_prop);
		
		} else {
			echo "no image found";
			echo "<pre>";
			print_r($image);
			echo "</pre>";
		}
		

	}

}
?>
