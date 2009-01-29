<?php
class Cms extends Controller {

       function Cms()
       {
            parent::Controller();

		}

		function index()
		{
		
			$this->load->view('cms/index');
		}



}
?>