<?php
class Content extends Controller {

       function Content()
       {
            parent::Controller();

			$this->load->model('Content_model');
			$this->load->scaffolding('content');
		}

		function index()
		{
			$data['q'] = $this->Content_model->get_list();
			$this->load->library('table');
	
			$this->load->view('cms/content_list',$data);
		}
//
//		function edit()
//		{
//
//			if(is_numeric($this->uri->segment(3)) and $this->uri->segment(3) > 0) {
//				$this->Content_model->content_id = $this->uri->segment(3);
//				$this->Content_model->load();
//			}	
//			$this->load->view('cms/content_edit',$this);
//		
//		}


}
?>
