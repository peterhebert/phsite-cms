<?php
class User extends Controller {

   function User()
   {
		parent::Controller();

		$this->load->model('User_model');
	} // end constructor

	function index()
	{
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
		'user_id' => '',
		'name_first' => '',
		'name_last' => '',
		'username' => '',
		'timezone' => '',
		'admin' => '',
		'lang' => '',
		'email' => '',
		);

		$params = $this->uri->uri_to_assoc(4);

		if($this->User_model->user_id == '' && array_key_exists('id',$params)) {
			$this->User_model->user_id = $params['id'];
		}

		if($this->User_model->user_id != '') {
			$this->User_model->load();

			$data = array(
			'action' => 'cms/user/edit/id/'.$params['id'],
			'hidden' => array('user_id' => $this->User_model->user_id),
			'user_id' => $this->User_model->id,
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

    function update_password($new='') {

		// generate new password here if not supplied
		if($new == '') {
			$new = gen_md5_password();
		}
		// hash password for database
		$hpwd = generateHash($password_new);
		
		$data = array(
		'password' => $hpwd,
		);
		
		$this->db->where('user_id', $this->User_model->id);

		if ($this->db->update('users', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}

	} // end update_password()

    function email_user_info($pwd,$action='') {
		$this->load->library('email');

		switch ($action) {
		case 'new':
			$subject = "New phSite account";
			$message = "You have created a new account for phSite\n";
			$message .= "Your login info is:\n";
			$message .= "Username: ".$this->User_model->username."\n";
			$message .= "Password: ".$pwd."\n";
			break;
		case 'reset':
			$subject = "Password reset for phSite installation";
			$message = "Your password has been reset.\n";
			$message .= "Your new login info is:\n";
			$message .= "Username: ".$this->User_model->username."\n";
			$message .= "Password: ".$pwd."\n";
			break;
		default:
			$subject = "Your login info for phSite";
			$message = "You have changed your login password for phSite\n";
			$message .= "Your new login info is:\n";
			$message .= "Username: ".$this->User_model->username."\n";
			$message .= "Password: ".$pwd."\n";
			break;
		}

		$this->email->from('phsite@host.com', 'phSite CMS');
		$this->email->to($this->User_model_email); 
		$this->email->subject($subject);
		$this->email->message($message);	
				
		if($this->email->send()) {
			return TRUE;
		} else {
			return FALSE;
		}

	} // end email_user_info()

}
?>