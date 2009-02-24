<?php
class User extends Controller {

   function User()
   {
		parent::Controller();

		$this->load->model('User_model');
		$this->load->helper('url');

	} // end constructor

	function index()
	{
		$this->load->helper('html');
		$data['list'] = $this->User_model->get_list();

		$this->load->view('cms/user_list',$data);
	} // end index()

	function load()
	{
		if($this->User_model->id != '') {
			if($this->User_model->load()) {
				return TRUE;
			}
		}
		return FALSE;

	} // end load()


	function edit()
	{
		$this->load->helper('form');
		$data = array(
		'action' => 'cms/user/edit/',
		'hidden' => '',
		'name_first' => '',
		'name_last' => '',
		'username' => '',
		'timezone' => '',
		'admin' => '',
		'lang' => '',
		'email' => '',
		);

		$params = $this->uri->uri_to_assoc(4);

		if(array_key_exists('id',$params)) {
			$this->User_model->id = $params['id'];
			$this->User_model->load();

			$data = array(
			'action' => 'cms/user/edit/id/'.$params['id'],
			'hidden' => array('user_id' => $this->User_model->id),
			'name_first' => $this->User_model->name_first,
			'name_last' => $this->User_model->name_last,
			'username' => $this->User_model->username,
			'timezone' => $this->User_model->timezone,
			'admin' => $this->User_model->admin,
			'lang' => $this->User_model->lang,
			'email' => $this->User_model->email,
			);
		}

		$this->load->view('cms/user_edit',$data);


	} // end edit()

	function checkPassword($plainText,$pwd) {
	
		//extract the salt from the password
		$salt = substr ( $pwd, 0, SALT_LENGTH );
	
		$checkPwd = $salt.sha1($salt.$plainText);
	
		if ($checkPwd == $pwd) {
			return true;
		} else {
			return false;
		}
	}

}
?>