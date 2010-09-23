<?php

class Test extends Controller {

	function Test()
	{
		parent::Controller();

		$this->load->helper('phsite');
	}
	
    function index()
    {
	echo "test";
	
		$email_params = array(
		'password' => 'password',
		'username' => 'username',
		'email' => 'email',
		);
		
		$sent = email_user_info($email_params);
		if($sent)
		{
			echo "sent to ".$email_params['email'];
		}

	}

}
?>
