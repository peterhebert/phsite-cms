<?php
class Field extends MY_Controller
{

	function Field()
	{
		parent::Controller();

		$this->load->model('Field_model');
		$this->load->model('Site_model');

		// if not logged in, redirect to login form
		//$this->_authenticate();
		$this->_load_site_metadata();
	}

	function index()
	{
		$this->Field_model->field_lang = $this->Site_model->site_default_lang;
		$list = $this->Field_model->get_list();
		$this->data['list'] = $list;

		$this->load->view('cms/field_list',$this->data);
	}

	function edit()
	{

	
		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : ';
		$breadcrumb .= anchor("cms/setup/structure","Data Structure").' : ';
		$breadcrumb .= 'Field editor';

		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Field editor | Data Structure | Setup ';
		$this->data['title_h1'] = 'Field editor';

		$this->load->library('form_validation');
		$params = $this->uri->uri_to_assoc(4);

		if(array_key_exists('id',$params)) {
			$this->Field_model->field_id = $params['id'];
		}	

		if(array_key_exists('lang',$params)) {
			if(is_numeric($params['lang'])) {
				$this->Field_model->field_meta_id = $params['lang'];
			} else {
				$this->Field_model->field_lang = $params['lang'];
			}
		}	

		$this->_process_form();

		$this->Field_model->load();
		$this->Field_model->load_meta();

		$this->data['form'] = $this->_edit_form();

		$msg = $this->Field_model->messages;
		if(is_array($msg) && count($msg) > 0)
		{
			$this->data['msg'] = "<p>";
			$this->data['msg'] .= implode("</p>\r<p>", $msg);
			$this->data['msg'] .= "</p>";
		}

		// list active languages
		$lang_links = array();
		$langs = $this->Language_model->get_list();
		foreach($langs as $llang)
		{
			$temp_link = 'cms/field/edit/id/'.$this->Field_model->field_id.'/lang/'.$llang['ISO_639_3'];
			if($llang['Native_Name'] != '')
			{
				$temp_txt = $llang['Native_Name'];
			}
			else
			{
				$temp_txt = $llang['Ref_Name'];
			}
			$lang_links[] = anchor($temp_link,$temp_txt);
		}
		
		$sidebar = "<h2>Field metadata</h2>\r";
		$sidebar .= ul($lang_links);
		
		$sidebar .= $this->load->view('cms/setup_menu',array(), true);
		$this->data['sidebar'] = $sidebar;

		// now load view
		$this->load->view('cms/cms_form',$this->data);

		//$this->load->view('cms/field_edit',$this->data);
	}
	
	function _process_form()
	{
		// do validation if form submitted
		if($this->input->post('submit') != '') {
			
			// set validation rules
			$this->form_validation->set_rules('field_ref_name', 'Field Reference Name', 'required');
			$this->form_validation->set_rules('field_input_type', 'Input Type', 'required|callback_valid_input');

			$this->form_validation->set_rules('validation_string', 'Value type', 'callback_valid_valid');
			$this->form_validation->set_rules('validation_exact', 'Exact Length', 'is_natural_no_zero');
			$this->form_validation->set_rules('validation_min', 'Minimum Length', 'is_natural_no_zero');
			$this->form_validation->set_rules('validation_max', 'Maximum Length', 'is_natural_no_zero');

			$this->form_validation->set_rules('field_name', 'Field Name', 'required');
			$this->form_validation->set_rules('field_lang', 'Language', 'required|is_lang');

			$form_is_valid = $this->form_validation->run();

			$this->Field_model->field_id = $this->input->post('field_id');
			$this->Field_model->field_ref_name = $this->input->post('field_ref_name');	
			$this->Field_model->field_input_type = $this->input->post('field_input_type');
			$this->Field_model->field_needs_lang = $this->input->post('field_needs_lang');

			$this->Field_model->validation_string = $this->input->post('validation_string');	
			$this->Field_model->validation_exact = $this->input->post('validation_exact');	
			$this->Field_model->validation_min = $this->input->post('validation_min');	
			$this->Field_model->validation_max = $this->input->post('validation_max');	

			$this->Field_model->field_meta_id = $this->input->post('field_meta_id');	
			$this->Field_model->field_name = $this->input->post('field_name');
			$this->Field_model->field_lang = $this->input->post('field_lang');	
		
			// run validation rules
			if ($form_is_valid === FALSE)
			{
				$this->Field_model->messages[] = validation_errors(); 
			}
			else
			{
				$success_field = $this->Field_model->update_field();
				
				if($success_field)
				{
					$success_meta = $this->Field_model->update_field_meta();
				}
				if($success_field === TRUE && $success_meta === TRUE)
				{
					$this->Field_model->messages[] = "'{$this->Field_model->field_ref_name}' has been successfully updated";
				}
				else
				{
					$this->Field_model->messages[] = "'{$this->Field_model->field_ref_name}' could not be updated";
				}

			}

		}
	}

	function _edit_form()
	{

		$this->Field_model->parse_validation();

		$action = "cms/field/edit/id/".$this->Field_model->field_id;
		if($this->Field_model->field_meta_id != '') {
			$action .= "/lang/".$this->Field_model->field_meta_id;
		} else if($this->Field_model->field_lang != '') {
			$action .= "/lang/".$this->Field_model->field_lang;
		} else {
		}
		
		$attributes = array('name' => "field_editor", 'id' => "field_editor");
		$hidden = array(
		'field_id'  => $this->Field_model->field_id,
		'field_meta_id'  => $this->Field_model->field_meta_id
		);

		$form = form_open($action,$attributes,$hidden)."\r";

		// main data
		$form .= form_fieldset('Field info');
		$form .= "\r";
		
		$form .= "<p>\r";
		$form .= form_label('Reference name', 'field_ref_name');
		$form .= form_input('field_ref_name',$this->Field_model->field_ref_name); 
		$form .= "</p>\r";
		
		$form .= "<p>\r";
		$form .= form_label('Input type', 'field_input_type');
		$form .= form_dropdown('field_input_type', $this->Field_model->input_types, $this->Field_model->field_input_type); 
		$form .= "</p>\r";

		$form_options = array(0 => 'No', 1 => 'Yes');
		$form .= "<p>\r";
		$form .= form_label('Field value requires Language', 'field_needs_lang');
		$form .= form_dropdown('field_needs_lang', $form_options, $this->Field_model->field_needs_lang); 
		$form .= "</p>\r";
		$form .= form_fieldset_close("\r");
		
		// metadata
		$form .= form_fieldset('Field metatdata');
		$form .= "\r";

		$form_options = $this->Language_model->get_list_dropdown();
		$form .= "<p>\r";
		$form .= form_label('Language', 'field_lang');
		$form .= form_dropdown('field_lang', $form_options, $this->Field_model->field_lang); 
		$form .= "</p>\r";
		$form .= "<p>\r";
		$form .= form_label('Field name', 'field_name');
		$form .= form_input('field_name',$this->Field_model->field_name); 
		$form .= "</p>\r";
		$form .= form_fieldset_close("\r");

		// validation
		$form .= form_fieldset('Validation');
		$form .= "\r";
		$form .= "<p>\r";
		$form .= form_label('Field value is', 'validation_string');
		$form .= form_dropdown('validation_string', $this->Field_model->validation_rules, $this->Field_model->validation_string); 
		$form .= "</p>\r";
		$form .= "<p>\r";
		$form .= form_label('Exact Length', 'validation_exact');
		$form .= form_input('validation_exact',$this->Field_model->validation_exact); 
		$form .= "</p>\r";
		$form .= "<p>\r";
		$form .= form_label('Minimum Length', 'validation_min');
		$form .= form_input('validation_min',$this->Field_model->validation_min); 
		$form .= "</p>\r";
		$form .= "<p>\r";
		$form .= form_label('Maximum Length', 'validation_max');
		$form .= form_input('validation_max',$this->Field_model->validation_max); 
		$form .= "</p>\r";
		$form .= form_fieldset_close("\r");

		$form .= "<p>\r";
		$submit_data = array(
		'name'  => 'submit',
		'id'  => 'submit'
		);
		$form .= form_submit($submit_data, 'save');
		$form .= "</p>\r";
		$form .= form_close("\r");

		return $form;
	}


	function valid_input($value)
	{
		return array_key_exists($value,$this->Field_model->input_types);
	}

	function valid_valid($value)
	{
		return array_key_exists($value,$this->Field_model->validation_rules);
	}


}
?>