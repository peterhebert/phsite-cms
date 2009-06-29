<?php
class Content_types extends MY_Controller
{

	function Content_types()
	{
		parent::MY_Controller();

		$this->load->model('Content_type_model');
		$this->load->model('Field_model');
		$this->load->helper('form');
		//$this->_authenticate();
	}

	function index()
	{
		$this->Content_type_model->get_list();
		$this->data['list'] = $this->Content_type_model->types;

		$this->load->view('cms/content_types_list',$this->data);
	}

	function edit()
	{
		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : ';
		$breadcrumb .= anchor("cms/setup/structure","Data Structure").' : Content type editor';
		$this->data['breadcrumb'] = $breadcrumb;

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->Content_type_model->type_id = $params['id'];
		}
		if(array_key_exists('lang',$params))
		{
			if(is_numeric($params['lang']))
			{
				$this->Content_type_model->type_meta_id = $params['lang'];
			} else {
				$this->Content_type_model->type_lang = $params['lang'];
			}
		}
		$this->_process_form();
		$this->Content_type_model->load_type();
		$this->Content_type_model->load_meta();
		
		$this->data['form'] = $this->_edit_form();

		$msg = $this->Content_type_model->messages;
		if(is_array($msg) && count($msg) > 0)
		{
			$this->data['msg'] = "<p>";
			$this->data['msg'] .= implode("</p>\r<p>", $msg);
			$this->data['msg'] .= "</p>";
		}
		$this->load->view('cms/content_types_edit',$this->data);

	}

	function _edit_form()
	{
		$langs = $this->Language_model->get_list_dropdown();

		$action = "/cms/content_types/edit";

		if($this->Content_type_model->type_id != '')
		{
			$action .= "/id/".$this->Content_type_model->type_id;
		}

		if($this->Content_type_model->type_meta_id != '')
		{
			$action .= "/lang/".$this->Content_type_model->type_meta_id;
		} else if($this->Content_type_model->type_lang != '') {
			$action .= "/lang/".$this->Content_type_model->type_lang;
		} else {
		}

		$form = "<form method=\"post\" action=\"{$action}\" name=\"type_editor\" id=\"type_editor\" />\r";
		$form .= "<input type=\"hidden\" name=\"type_id\" value=\"{$this->Content_type_model->type_id}\" />\r";
		$form .= "<input type=\"hidden\" name=\"type_meta_id\" value=\"{$this->Content_type_model->type_meta_id}\" />\r";

		// main data
		$form .= "<p class=\"nomargin\">\r";
		$form .= form_label('URI name', 'type_name_uri');
		$form .= form_input('type_name_uri',$this->Content_type_model->type_name_uri); 
		$form .= "</p>\r";
		$form .= "<p class=\"indent\">Note: changing this URI will mean you need to change the names of your template file for this content type as well.</p>\r";

		// metadata
		$form .= form_fieldset('Content type name');
		$form .= "\r";
		$form .= "<p>\r";
		$form .= form_label('Language', 'type_lang');
		$form .= form_dropdown('type_lang', $langs, $this->Content_type_model->type_lang); 
		$form .= "</p>\r";
		$form .= "<p>\r";
		$form .= form_label('Name', 'type_name');
		$form .= form_input('type_name',$this->Content_type_model->type_name); 
		$form .= "</p>\r";
		$form .= form_fieldset_close("\r");

		// fields
		$form .= form_fieldset('Fields');
		$form .= "\r";

		$form .= "<table id=\"properties\">\r";
		$form .= "<thead>\r";
		$form .= "<tr>\r";
		$form .= "<th scope=\"col\" id=\"hd_name\">field name</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_exists\">in object?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_req\">required?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_order\">order</th>\r";
		$form .= "</tr>\r";
		$form .= "</thead>\r";
		$form .= "<tbody>\r";

		$this->Content_type_model->list_fields();
		
		$fieldCount = count($this->Content_type_model->fields);
		$fieldNo = 1;

		foreach($this->Content_type_model->fields as $field)
		{

			if($field['field_name'] != '') {
				$temp_name = $field['field_name'];
			} else {
				$temp_name = $field['field_ref_name'];
			}


			if($fieldNo < $fieldCount) {
			$form .= "<tr>\n";
			} else {
			$form .= "<tr class=\"last\">\n";
			}
			$fieldNo = $fieldNo + 1;
	
			$form .= form_hidden('field_id[]', $field['field_id']);
	
			if($field['rel_id'] != '') {
				$exists = true;
			} else {
				$exists = false;
			}
			$form .= form_hidden('rel_id[]', $field['rel_id']);

			$params = array(
			'name'        => 'exists[]',
			'id'          => 'exists[]',
			'value'       => $field['field_id'],
			'checked'     => $exists
			);
	
			$form .= "<th headers=\"hd_name\" id=\"{$temp_name}\" scope=\"row\">{$temp_name}</th>\n";
			$form .= "<td headers=\"hd_exists\" class=\"align-center\">".form_checkbox($params)."</td>\n";

			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $field['field_id'],
			'checked'     => $field['required']
			);

			$form .= "<td headers=\"{$temp_name} hd_req\" class=\"align-center\">".form_checkbox($params)."</td>\n";

			$params = array(
			'name'        => "order[]",
			'id'          => "order[]",
			'value'       => $field['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			$form .= "<td headers=\"{$temp_name} hd_order\">".form_input($params)."</td>\n";


			$form .= "</tr>\n";

		}

		$form .= "</tbody></table>\n";


		$form .= form_fieldset_close("\r");


		$form .= form_submit('submit', 'Save');


		$form .= "</form>\r";

		return $form;
	}

	function _process_form()
	{

		if($this->input->post('submit') != '')
		{
			// set validation rules
			$this->load->library('form_validation');

			$this->form_validation->set_rules('type_name_uri', 'Content Type URI', 'required|alpha_num_spaces');
			$this->form_validation->set_rules('type_name', 'Content Type name', 'required');

			// get values from form post data
			$this->Content_type_model->type_id = $this->input->post('type_id');
			$this->Content_type_model->type_name_uri = $this->input->post('type_name_uri');
			$this->Content_type_model->type_meta_id = $this->input->post('type_meta_id');
			$this->Content_type_model->type_name = $this->input->post('type_name');
			$this->Content_type_model->type_lang = $this->input->post('type_lang');
			
			$temp_fields = $this->input->post('field_id');
			$temp_rels = $this->input->post('rel_id');
			$temp_exists = $this->input->post('exists');
			$temp_required = $this->input->post('required');
			$temp_order = $this->input->post('order');

			$form_vals = array();
			foreach($temp_fields as $index => $id)
			{
				$temp_field = array(
				'field_id' => $id,
				'rel_id' => $temp_rels[$index],
				'exists' => FALSE,
				'required' => 0,
				'order' => $temp_order[$index],
				);
				
				if(is_array($temp_exists) && in_array($id,$temp_exists)){
					$temp_field['exists'] = TRUE;
				}

				if(is_array($temp_required) && in_array($id,$temp_required)){
					$temp_field['required'] = 1;
				}
				
				$form_vals[] = $temp_field;
			}
			$form_is_valid = $this->form_validation->run();
			// run validation rules
			if ($form_is_valid === FALSE)
			{
				$this->Content_type_model->messages[] = validation_errors(); 
			}
			else
			{
				// update content type 

				if($this->Content_type_model->update_type())
				{
					$this->Content_type_model->update_type_metadata();
					
					// update related fields
					foreach($form_vals as $temp_field)
					{

//echo "<pre>";
//print_r($temp_field);
//echo "</pre>\r";

						$this->Content_type_model->rel_id = $temp_field['rel_id'];
						$this->Content_type_model->field_id = $temp_field['field_id'];
						$this->Content_type_model->required = $temp_field['required'];
						$this->Content_type_model->order = $temp_field['order'];

						if($temp_field['exists'] === FALSE)
						{
							if($temp_field['rel_id'] != '')
							{
								$this->Content_type_model->remove_field();
							}
						} else {
							$this->Content_type_model->update_field();
						}
					}
				} // end updates
			}//end validation

		}// end form processing
	}// end _process_form()

}
?>