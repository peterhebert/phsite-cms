<?php
class Object extends Controller {

       function Object()
       {
            parent::Controller();

			$this->load->model('Object_model');
			$this->load->scaffolding('object');
		}

		function listing()
		{
		$data['q'] = $this->Object_model->get_list();

		$this->load->view('object_list',$data);
		}
}
?>