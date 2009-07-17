<?php

class Tests extends MY_Controller {

	function Tests()
	{
		parent::MY_Controller();	
	}
	
    function index()
    {
		echo "<p>site id: ";
		echo $this->Site_model->site_id;
		echo "</p>\r";

		echo "<p>install path: ";
		echo $this->Site_model->install_path;
		echo "</p>\r";

		$this->_load_site_metadata();
		
		echo "<pre>";
		print_r($this->data);
		echo "</pre>\r";

    }

}
?>