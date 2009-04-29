<?php
class Cms extends Controller
{

	function Cms()
	{
		parent::Controller();
		$this->load->helper('form');

		// load objects
		$this->load->model('Field_model');
		$this->load->model('Object_model');
		$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->model('Site_model');

		$this->load->library('session');
		
		// get site prefs & meta
		$this->Site_model->load_prefs();

		// get site metadata
		$this->Site_model->load_meta();
		$this->data['site_title'] = $this->Site_model->title;
	}
	
	function authenticate()
	{
		// if not logged in, redirect to login form
		$this->load->library('session');
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
			// get site metadata
			$this->Site_model->site_lang = $this->session->userdata('default_lang');
			$this->Site_model->load_meta();
			$this->data['site_title'] = $this->Site_model->title;
		}

	}

	function index()
	{
		$this->authenticate();

		$params = $this->uri->uri_to_assoc(3);

		if(array_key_exists('lang',$params)) {
			$this->Content_model->content_lang = $params['lang'];
		}

		// load list of fields
		$d2['list'] = $this->Field_model->get_list();
		$this->data['fields'] = $this->load->view('cms/field_list',$d2, true);
	
		// load list of objects
		$d2['list'] = $this->Object_model->get_list();
		$this->data['new_objects'] = $d2['list'];
		$this->data['objects'] = $this->load->view('cms/object_list',$d2, true);

		// get recently added pages
		$this->data['content'] = $this->recent();
	
		// now load view
		$this->load->view('cms/index',$this->data);
	}
	
	function login()   
	{  
		$this->load->helper('form');
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
				$auth = $this->User_model->authenticate($this->input->post('username'),$this->input->post('password'));

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


	function recent()
	{

		// get recently added pages
		$content = $this->Content_model->list_recent();
		
		foreach($content as $page) {
			$link = "cms/content/edit/id/".$page['content_id'];
			if($page['content_lang'] != '') {
				$link .= "/lang/".$page['content_lang'];
			}

			$list[] = anchor($link, $page['title'], array('title' => "edit '{$page['title']}'"));
			
		}

		if(isset($list)) {
			$contentdata['title'] = "Recently Added";
			$contentdata['list'] = ul($list);
		}
		$recent = $this->load->view('cms/content_list',$contentdata, true);
		return $recent;

	}



}
?>