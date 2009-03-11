<?php
class Setup extends Controller {

	function Setup()
	{
		parent::Controller();
		$this->load->helper('form');

		// load objects
		$this->load->model('Field_model');
		$this->load->model('Object_model');
		$this->load->model('Language_model');
		$this->load->model('User_model');
	}
	
	function index()
	{
		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data['list'] = $this->Object_model->get_list();
		$index['new_objects'] = $data['list'];
		$index['objects'] = $this->load->view('cms/object_list',$data, true);

		// load list of activated languages
		$data['list'] = $this->Language_model->get_list();
		$index['languages'] = $this->load->view('cms/lang_list',$data, true);

		// load list of users
		$build_list = array();
		$userlist = $this->User_model->get_list();
		foreach($userlist as $user) {
			$user_edit_link = "cms/user/edit/id/".$user['user_id'];
			$name = $user['name_last'].", ".$user['name_first'];
			$build_list[] = anchor($user_edit_link,$name);
		}

		$data['list'] = $build_list;
		$index['users'] = $this->load->view('cms/user_list',$data, true);

		// now load view
		$this->load->view('cms/setup',$index);
	}

	function structure()
	{
		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data['list'] = $this->Object_model->get_list();
		$index['new_objects'] = $data['list'];
		$index['objects'] = $this->load->view('cms/object_list',$data, true);

		// now load view
		$this->load->view('cms/setup_structure',$index);
	}
	

}
?>