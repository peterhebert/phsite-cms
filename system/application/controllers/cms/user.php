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

		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			if($this->User_model->user_id == '' && $this->input->post('user_id') != '') {
				$this->User_model->user_id = $this->input->post('user_id');
			}

			// set validation rules
			$this->form_validation->set_rules('name_first', 'First Name', 'required');
			$this->form_validation->set_rules('name_last', 'Last Name', 'required');
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[8]|alpha_dash');

			if($this->input->post('password') != '' || $this->User_model->user_id == '') {
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|alpha_dash');
				$this->form_validation->set_rules('password_confirm', 'Password confirm', 'required|min_length[8]|alpha_dash|matches[password]');
			}

			$this->form_validation->set_rules('timezone', 'Time Zone', 'required|is_timezone');
			$this->form_validation->set_rules('admin', 'User Type', 'required|is_natural');
			$this->form_validation->set_rules('lang', 'Default language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('email', 'Email address', 'required|valid_email');

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = validation_errors();
			} else {

				if($this->input->post('user_id') != '') {
					$this->User_model->user_id = $this->input->post('user_id');
				}
				$this->User_model->name_first = $this->input->post('name_first');
				$this->User_model->name_last = $this->input->post('name_last');
				$this->User_model->username = $this->input->post('username');
				$this->User_model->timezone = $this->input->post('timezone');
				$this->User_model->admin = $this->input->post('admin');
				$this->User_model->lang = $this->input->post('lang');
				$this->User_model->email = $this->input->post('email');

				if($this->input->post('password') != '') {
					$success = $this->User_model->update_user($this->input->post('password'));
				} else {
					$success = $this->User_model->update_user();
				}

				if($success) {
					$data['msg'] = "<p>User updated successfully</p>\n";

					if($this->input->post('user_id') == '') {
						if($this->User_model->email_user_info($this->input->post('password'),'new')) {
							$data['msg'] .= "<p>Confirmation email sent</p>\n";
						}
						
					} else if ($this->input->post('user_id') != '') {
						if($this->User_model->email_user_info($this->input->post('password'))) {
							$data['msg'] .= "<p>Confirmation email sent</p>\n";
						}
					}

				} else {
					$data['msg'] = "User could not be updated";
				}
			}

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

	function forgot_password()
	{
		$this->load->helper('form');
		$data['username'] = '';
		$data['email'] = '';
		$data['showform'] = TRUE;

		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			$data['username'] = $this->input->post('username');
			$data['email'] = $this->input->post('email');
			
			// set validation rules
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[8]|alpha_dash');
			$this->form_validation->set_rules('email', 'Email address', 'required|valid_email');

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = validation_errors();
			} else {

				$this->User_model->username = $this->input->post('username');
				$this->User_model->email = $this->input->post('email');
				if($this->User_model->reset_password()) {
					$data['msg'] = "Your reset password has been emailed to you";
					$data['showform'] = FALSE;
				} else {
					$data['msg'] = "Could not find a user matching the info provided";
				}
			}

		}

		$this->load->view('cms/user_forgot_pwd',$data);

	} // end forgot_password()


}
?>