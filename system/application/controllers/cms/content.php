<?php
class Content extends Controller {

       function Content()
       {
            parent::Controller();

			$this->load->model('Content_model');
			$this->load->scaffolding('content');
			$this->load->helper('url');
		}

		function index()
		{
			$data['q'] = $this->Content_model->get_list(10);
	
			$this->load->view('cms/content_list',$data);
		}


		function edit()
		{

			if(is_numeric($this->uri->segment(4)) and $this->uri->segment(4) > 0) {
				$this->Content_model->load($this->uri->segment(4));
			} else if ($this->uri->segment(4) == 'type') {
				$this->Content_model->object_id = $this->uri->segment(5);
			}	


			// get all needed fields for object type

			$this->load->view('cms/content_edit',$this->Content_model);
		
		}



}
?>
