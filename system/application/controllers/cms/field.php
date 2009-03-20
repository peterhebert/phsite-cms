<?php
class Field extends Controller {

	var $data = array();
	
	function Field()
	{
		parent::Controller();

		// if not logged in, redirect to login form
		$this->load->library('session');
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
		}

		$this->load->model('Field_model');
		$this->input_types = array(
		'text' => 'text',
		'textarea' => 'textarea',
		'richtext' => 'rich text editor',
		'checkbox' => 'checkbox',
		'radio' => 'radio',
		'password' => 'password',
		'file' => 'file');
	}

	function index()
	{
		$this->data['list'] = $this->Field_model->get_list();

		$this->load->view('cms/field_list',$this->data);
	}

	function edit()
	{
		$this->load->library('form_validation');
		$params = $this->uri->uri_to_assoc(4);
		
		$this->data['msg'] = '';
		$this->data['field_id'] = '';
		$this->data['field_name'] = '';
		$this->data['input_type'] = '';
		$this->data['types'] = $this->input_types;

		// do validation if form submitted
		if($this->input->post('submit') != '') {
			
			if($this->input->post('field_id') != '') {
				$this->form_validation->set_rules('field_id', 'Field ID', 'required|integer');
			}
	
			$this->form_validation->set_rules('field_name', 'Field Name', 'required|xss_clean');
			$this->form_validation->set_rules('input_type', 'Input Type', 'required|callback_valid_input|xss_clean');
	
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors(); 
				$this->data['field_id'] = $this->input->post('field_id');
				$this->data['field_name'] = $this->input->post('field_name');
				$this->data['input_type'] = $this->input->post('input_type');	


			}
			else
			{
				
				if($this->input->post('field_id') != '') {
					$this->Field_model->field_id = $this->input->post('field_id');	
					$this->Field_model->field_name = $this->input->post('field_name');	
					$this->Field_model->input_type = $this->input->post('input_type');
					
					$updated = $this->Field_model->update_field();
				} else {
					$updated = $this->Field_model->new_field($this->input->post('field_name'),$this->input->post('input_type'));
				}
	
				if($updated == TRUE) {
					$this->data['msg'] = "field has been updated";
				}
			}
		}

		if($this->Field_model->field_id == '' && array_key_exists('id',$params)) {
			$this->Field_model->field_id = $params['id'];
		}	

		$this->data['field_id'] = $this->Field_model->field_id;
		
		if($this->Field_model->field_id != '') {
			$this->Field_model->load();
			$this->data['field_name'] = $this->Field_model->field_name;
			$this->data['input_type'] = $this->Field_model->input_type;
		}

		$this->load->view('cms/field_edit',$this->data);
	}

	function valid_input($value)
	{
		return array_key_exists($value,$this->input_types);
	}

}
?>