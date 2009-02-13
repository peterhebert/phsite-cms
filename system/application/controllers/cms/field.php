<?php
class Field extends Controller {

   function Field()
   {
		parent::Controller();

		$this->load->model('Field_model');
		$this->load->helper('url');
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
		$data['list'] = $this->Field_model->get_list();

		$this->load->view('cms/field_list',$data);
	}

	function edit()
	{
		$this->load->library('form_validation');
		
		$data['msg'] = '';
		$data['field_id'] = '';
		$data['field_name'] = '';
		$data['input_type'] = '';
		$data['types'] = $this->input_types;
		$data['exact_length'] = '';	
		$data['min_length'] = '';	
		$data['max_length'] = '';	
		$data['string'] = '';	

		$data['string_rules'] = array(
		'' => 'any value',
		'alpha' => 'Alphabetical',
		'alpha_numeric' => 'Alpha-numeric',
		'alpha_dash' => 'Alpha-numeric &amp; dashes',
		'alpha_num_spaces' => 'Alpha-numeric, dashes &amp; spaces',
		'is_date' => 'Date',
		'valid_email' => 'Email address',
		'integer' => 'Integer',
		'numeric' => 'Numeric',
		'is_natural' => 'Natural numbers (0, 1, 2, 3, etc.)',
		'is_natural_no_zero' => 'Natural numbers/no zeroes (1, 2, 3, etc.)',
		'file_path' => 'file path',
		'valid_ip' => 'Internet Protocol (IP) address'
		);

		// do validation if form submitted
		if($this->input->post('submit') != '') {
			
			if($this->input->post('field_id') != '') {
				$this->form_validation->set_rules('field_id', 'Field ID', 'required|integer');
			}
	
			$this->form_validation->set_rules('field_name', 'Field Name', 'required|xss_clean');
			$this->form_validation->set_rules('input_type', 'Input Type', 'required|callback_valid_input|xss_clean');
			$this->form_validation->set_rules('exact_length', 'Exact Length', 'is_natural_no_zero');
			$this->form_validation->set_rules('min_length', 'Minimum Length', 'is_natural_no_zero');
			$this->form_validation->set_rules('max_length', 'Maximum Length', 'is_natural_no_zero');
	
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = validation_errors(); 
				$data['field_id'] = $this->input->post('field_id');
				$data['field_name'] = $this->input->post('field_name');
				$data['input_type'] = $this->input->post('input_type');	

				$data['exact_length'] = $this->input->post('exact_length');	
				$data['min_length'] = $this->input->post('min_length');	
				$data['max_length'] = $this->input->post('max_length');	
				$data['string'] = $this->input->post('string');	

			}
			else
			{
				// process the validation fields
				$my_exact = $this->input->post('exact_length');	
				$my_min = $this->input->post('min_length');	
				$my_max = $this->input->post('max_length');
				
				$domin = false;
				$domax = false;	
				$valArray = array();
				$valArray[] = $this->input->post('string');
				if($my_exact != '') {
					$valArray[] = "exact_length[{$my_exact}]";
				} else {
					if($my_min != '') {
						if ($my_max != '' && $my_min < $my_max) {
							$domin = true;
						}
						if ($my_max == '') {
							$domin = true;
						}
					}

					if($my_max != '') {
						if ($my_min != '' && $my_min < $my_max) {
							$domax = true;
						}
						if ($my_min == '') {
							$domax = true;
						}
					}

					if($my_max != '' && $my_min != '' && $my_min == $my_max) {
						$domin = false;
						$domax = false;	
						$valArray[] = "exact_length[{$my_min}]";
					}

				}

				if($domin == true) {
					$valArray[] = "min_length[{$my_min}]";
				}

				if($domax == true) {
					$valArray[] = "max_length[{$my_max}]";
				}

				$valid1 = array_unique($valArray);
				$valid2 = array_filter($valid1);

				$validation = implode('|',$valid2);

				
				if($this->input->post('field_id') != '') {
					$this->Field_model->field_id = $this->input->post('field_id');	
					$this->Field_model->field_name = $this->input->post('field_name');	
					$this->Field_model->input_type = $this->input->post('input_type');
					$this->Field_model->validation = $validation;
					
					$updated = $this->Field_model->update_field();
				} else {
					$updated = $this->Field_model->new_field($this->input->post('field_name'),$this->input->post('input_type'),$valid_concat);
				}
	
				if($updated == TRUE) {
					$data['msg'] .= "<p><strong>field has been updated</strong></p>";
				}
			}
		}

		if($this->Field_model->field_id == '') {
			$this->Field_model->field_id = $this->uri->segment(4);
		}	

		if($this->Field_model->field_id != '') {
			$this->Field_model->load();
			$data['field_id'] = $this->Field_model->field_id;
			$data['field_name'] = $this->Field_model->field_name;
			$data['input_type'] = $this->Field_model->input_type;
			
			// now parse validation / convert to form values
			if($this->Field_model->validation != '') {

				$validation = $this->Field_model->validation;
		
				if(ereg ( 'exact_length\[([0-9]+)\]', $validation)) {
					$pattern = '^(.*)exact_length\[([0-9]+)\](.*)$';
					$data['exact_length'] = ereg_replace($pattern,'\\2',$validation);
				}
				if(ereg ( 'min_length\[([0-9]+)\]', $validation)) {
					$pattern = '^(.*)min_length\[([0-9]+)\](.*)$';
					$data['min_length'] = ereg_replace($pattern,'\\2',$validation);
				}
				if(ereg ( 'max_length\[([0-9]+)\]', $validation)) {
					$pattern = '^(.*)max_length\[([0-9]+)\](.*)$';
					$data['max_length'] = ereg_replace($pattern,'\\2',$validation);
				}

				$valArray = explode('|',$validation);
				$string_rules = array(
				'alpha',
				'alpha_numeric',
				'alpha_dash',
				'alpha_num_spaces',
				'is_date',
				'valid_email',
				'integer',
				'numeric',
				'is_natural' ,
				'is_natural_no_zero' ,
				'file_path' ,
				'valid_ip' 
				);
		
				$data['string'] = '';
				
				foreach ($valArray as $theval) {
					if(in_array($theval,$string_rules,true)) {
						$data['string'] = $theval;
					}
				}

			}
		}	

		$this->load->view('cms/field_edit',$data);
	}

	function valid_input($value)
	{
		return array_key_exists($value,$this->input_types);
	}

}
?>