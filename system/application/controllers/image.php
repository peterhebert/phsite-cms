<?php

class Image extends Controller {

	function Image()
	{
		parent::Controller();	
		$this->load->helper('file');
	}
	
	function index()
	{

		$this->load->helper('image');

		$uri_raw = $this->uri->uri_string();
		$url_params =  str_replace( '/image/', '', $uri_raw);
		$uri = preg_replace('|^/image/(.*)$|','$1',$uri_raw);
		
		if($uri != '')
		{
			$param_keys = array('src','size','ratio','alt','name','title');
			
			$pattern = "/(src|size|ratio|alt|name|title)/";
			$params = preg_split($pattern, $url_params, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$attr = array();
			$src = '';
			$size = '';
			$ratio = '';
			
			foreach($params as $index => $key)
			{
				if($index < count($params)-1) {
					$rawval = $params[$index+1];
				} else {
					$rawval = '';
				}
				
				if(in_array($key,$param_keys)) {
					$myval = trim($rawval,'/');
					switch($key)  {
					case 'src':
						$src = rtrim($rawval,'/');
						break;
					case 'size':
						$size = $myval;
						break;
					case 'ratio':
						$ratio = $myval;
						break;
					default:
						$attr[$key] = $myval;
						break;
					}
				}
	
	
			}
	
			$image_html = view_image($src,$size,$ratio,$attr);
			echo $image_html;
			$metadata = get_metadata($src);
			echo "<br />\n<pre>";
			print_r ($metadata);
			echo "</pre>";

		} else {
			return "no image specified";
		}

	}

}
?>
