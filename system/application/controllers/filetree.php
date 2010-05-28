<?php

class Filetree extends Controller {

	function Filetree()
	{
		parent::Controller();	
	}
	
	function index()
	{
		

		$root = '/'.trim(FCPATH,'/');

		$dir = urldecode($this->input->post('dir'));

		$filepath = $root . $dir;
		
		
		$filetree = '';
		
		if( file_exists($filepath) )
		{
			$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
			while ($file = readdir($dir_handle))
			{
				$newpath = str_replace('//','/',$filepath.'/'.$file);
				
				// ignore all hidden files and directory navigation (.*)
				$ignore = preg_match("#^\..*$#",$file);
				if($ignore <= 0)
				{
					$rel = $dir .'/'. $file;
					$rel = str_replace('//','/',$rel);
					$rel = htmlentities($rel) ;

					if(is_dir($newpath))
					{
						$filetree .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"{$rel}/\">" . htmlentities($file) . "</a></li>\r";
					}
					else
					{
						$class = $this->_get_class_by_extension($newpath);
						$filetree .= "<li class=\"{$class}\"><a href=\"#\" rel=\"{$rel}\">" . htmlentities($file) . "</a></li>\r";
					}
					
				}
			}
		}
		if($filetree != '')
		{
			$filetree = "<ul class=\"jqueryFileTree\">{$filetree}</ul>\r";
		}
		echo $filetree;

	}
	
	function _get_class_by_extension($file)
	{
		
		$path_parts = @pathinfo($file);
		$ext = strtolower($path_parts['extension']);
		
		$this->load->config('mime_info');
		
		$mime_icons = $this->config->item('mime_icons');
		$mime_types = $this->config->item('mime_types');
		
		if(in_array($ext,$mime_icons))
		{
			$class = 'file ext_'.$ext;
		}
		else if(array_key_exists($ext,$mime_types))
		{
			$icon = preg_replace("#^([a-z]+)/.*$#","$1",$mime_types[$ext]);
			$class = 'file icon_'.$icon;
		}
		else
		{
			$class = 'file';
		}
		
		return $class;
	}
	
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */