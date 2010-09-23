<?php

class Test extends Controller {

	function Test()
	{
		parent::Controller();

		$this->load->helper('phsite');
	}
	
    function index()
    {
	echo "test";
	
		$email_params = array(
		'password' => 'password',
		'username' => 'username',
		'email' => 'email',
		);
		
		$sent = email_user_info($email_params);
		if($sent)
		{
			echo "sent to ".$email_params['email'];
		}

	}

	function write_htaccess()
	{

		// first add HTML 5 mime type definitions
		$this->config->load('mime_info');
		$html5_types = $this->config->item('html5_types');
		$config['html5_types'] = array('flac' => 'audio/flac', 'm4a' => 'audio/mp4', 'm4b' => 'audio/mp4', 'mp3' => 'audio/mpeg3', 'oga' => 'audio/ogg', 'ogg' => 'audio/ogg', 'spx' => 'audio/ogg', 'f4v' => 'video/x-flv', 'flv' => 'video/x-flv', 'm4u' => 'video/x-mpegurl', 'm4v' => 'video/mp4', 'mkv' => 'video/x-matroska', 'mov' => 'video/quicktime', 'mp4' => 'video/mp4', 'mpeg4' => 'video/mp4', 'ogm' => 'video/ogg', 'ogv' => 'video/ogg', 'webm' => 'video/x-webm');

		$config = '';
		foreach($html5_types as $ext => $type)
		{
			$config .= "AddType {$type} .{$ext}\r";
		}

		// now add routing directives
		$config  .= "\rRewriteEngine On\r\r";
		$config  .= "# Removes access to the system folder by users.\r";
		$config  .= "# Additionally this will allow you to create a System.php controller,\r";
		$config  .= "# previously this would not have been possible.\r";
		$config  .= "# 'system' can be replaced if you have renamed your system folder.\r\r";
		$config  .= 'RewriteCond %{REQUEST_URI} ^system.*'."\r";
		$config  .= 'RewriteRule ^(.*)$ /index.php/$1 [L]'."\r\r";
		$config  .= "# Checks to see if the user is attempting to access a valid file,\r";
		$config  .= "# such as an image or css document, if this isn't true it sends the\r";
		$config  .= "# request to index.php\r\r";
		$config  .= 'RewriteCond %{REQUEST_FILENAME} !-f'."\r";
		$config  .= 'RewriteCond %{REQUEST_FILENAME} !-d'."\r";
		$config  .= 'RewriteRule ^(.*)$ ./index.php/$1 [L]'."\r\r";
		$config  .= "RewriteEngine Off\r";

		echo "<pre>";
		print_r($config);
		echo "</pre>\r";

/*		$this->load->helper('file');
		$filename = ROOT_REL.'/.htaccess';

		if(!file_exists($filename))
		{
			$written = write_file($filename, $config);
			return $written;
		}
		else
		{
			echo $filename." exists";
			return FALSE;
		}
*/	}

}
?>
