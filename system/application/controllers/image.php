<?php

class Image extends MY_Controller {

	function Image()
	{
		parent::MY_Controller();	
		$this->load->model('File_model');
	}
	
	function index()
	{
		//$this->File_model->list_special();


		$this->File_model->list_dir_contents('dirs',TRUE);
		echo "<p>files</p>\r";
		echo "<pre>";
		print_r($this->File_model->file_list);
		echo "</pre>\r";

		echo "<p>directories</p>\r";
		echo "<pre>";
		print_r($this->File_model->dir_list);
		echo "</pre>\r";
	
	}

}
?>
