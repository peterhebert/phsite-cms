<?php

class Tests extends MY_Controller {

	function Tests()
	{
		parent::Controller();	
	}
	
    function index()
    {
		echo "<p>site id: ";
		echo $this->Site_model->site_id;
		echo "</p>\r";

		echo "<p>install path: ";
		echo $this->Site_model->install_path;
		echo "</p>\r";

		$this->Site_model->load_site();
		$this->Site_model->load_meta_fields();

		echo "<p>site timezone: ";
		echo $this->Site_model->site_timezone;
		echo "</p>\r";
		
		echo "<pre>";
		print_r($this->Site_model->site_meta_fields);
		echo "</pre>\r";



    }

}
?>