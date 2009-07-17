<?php
class Cms extends MY_Controller
{

	function Cms()
	{
		parent::MY_Controller();

		// load necessary resources
		$this->load->helper('form');
		$this->load->model('Field_model');
		$this->load->model('Content_type_model');
		//$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->library('session');
	}
	
	function index()
	{
		//$this->_authenticate();




/*		// get recently added pages


		// load list of content types
		$this->Content_type_model->get_list();
		$d2['list'] = $this->Content_type_model->types;
		$this->data['objects'] = $this->load->view('cms/content_types_list',$d2, true);
*/

		$form = "<h2>Content</h2>\r";
		$form .= "<p>".anchor("cms/content","Site Map")."</p>\r";


		$form .= "<h2>Recently Added</h2>\r";
		//$form .= $this->Content_model->site_map(1,10);

//		$form_options = $this->Content_type_model->get_list_dropdown_create();
//		$form .= "<p>Create New ";
//		$form .= form_dropdown('new_content',$form_options).' ';
//		$form_options = array(
//			'name' => 'new_go',
//			'id' => 'new_go',
//			'content' => 'Go',
//			'type' => 'button',
//		);
//		$form .= form_button($form_options);
//		$form .= "</p>\r";
		$form .= "<h2>Media</h2>\r";
		
		$media_links[] = anchor("cms/media/manage","manage media");
		$media_links[] = anchor("cms/media/upload","upload media");
		$form .= ul($media_links)."\r";


		$breadcrumb = anchor("cms","phSite CMS").' : CMS home';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_h1'] = 'phSite CMS home';

		$site_metadata = "Site metadata\r";
		$site_metadata .=  $this->Language_model->site_meta_menu();
		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata),true);

		$this->data['form'] = $form;

		$this->load->view('cms/cms_form',$this->data);
	}

	
	function login()   
	{  
		$this->load->library('form_validation');

		// if already logged in, redirect
		if($this->session->userdata('user_id'))
		{
			redirect('cms');
			exit();
		}

		if(!$this->session->userdata('attempts'))
		{
			$sess = array('attempts' => 0);
			$this->session->set_userdata($sess);
		}
		$attempts = $this->session->userdata('attempts');

		// if form submitted, process data
		if($this->input->post('submit') != '')
		{

			// set validation rules
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[8]|alpha_dash');
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|alpha_dash');

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors();
			} else {
				// perform authentication
				$auth = $this->User_model->authenticate_user($this->input->post('username'),$this->input->post('password'));

				if($auth === TRUE)
				{
					// Login successful
					$sess = array(
					'attempts' => 0,
					'user_id' => $this->User_model->user_id,
					'username' => $this->User_model->username,
					'name_first' => $this->User_model->name_first,
					'name_last' => $this->User_model->name_last,
					'timezone' => $this->User_model->timezone,
					'admin' => $this->User_model->admin,
					'default_lang' => $this->User_model->default_lang,
					'email' => $this->User_model->email
					);
					$this->session->set_userdata($sess);

					redirect('cms');
					exit();
				}
				else
				{
					$attempts = $attempts +1;
					$sess = array('attempts' => $attempts);
					$this->session->set_userdata($sess);
					switch($attempts)
					{
					case 1:
						$numtxt =  " (1st attempt - 2 remaining)";
						break;
					case 2:
						$numtxt =  " (2nd attempt - 1 remaining)";
						break;
					default:
						$numtxt =  "";
						break;
					}
					$this->data['msg'] = "Login failed{$numtxt}, please try again";
				}

			}

		}

		$this->data['showform'] = TRUE;

		if ($attempts >= 3) {
			$this->session->sess_destroy();
			$sess = array('attempts' => $attempts);
			$this->session->set_userdata($sess);
			$this->data['msg'] = '3 attempts to login have been made. Access is denied for the next 2 hours';
			$this->data['showform'] = FALSE;
		}

		$this->data['action'] = site_url("cms/login");

		$this->load->view('cms/login',$this->data);  
	} 





	function logout()   
	{  
		$this->session->sess_destroy();
		redirect('');
		exit();
	} 

}
?>