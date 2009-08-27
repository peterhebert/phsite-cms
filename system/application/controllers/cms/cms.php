<?php
class Cms extends MY_Controller
{

	function Cms()
	{
		parent::MY_Controller();

		// load necessary resources
		$this->load->helper('form');
		$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->library('session');

		$this->_load_site_metadata();
		$this->_load_cms_labels();
	}
	
	function index()
	{
		//$this->_authenticate();

		// load list of content types
		$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		$this->Content_model->list_types();

		$this->data['type_list'] = $this->Content_model->type_list;
		$this->data['objects'] = $this->load->view('cms/content_types_list',$this->data, true);

		$form = "<h2>{$this->cms_labels['content']}</h2>\r";

		// get recently added pages
		$form .= "<ul>\r";
		$form .= "<li>".anchor("cms/content",$this->cms_labels['site_map'])."</li>\r";
		$this->Content_model->list_content(NULL,TRUE);

		if(count($this->Content_model->content_list) > 0)
		{

			$form_options = array();
			$form .= "<li>{$this->cms_labels['recent_content']}\r";
			foreach ($this->Content_model->content_list as $tc)
			{
				$link = 'cms/content/edit/id/'.$tc['content_id'].'/lang/'.$tc['lang_ISO_639_3'];
				$form_options[] = anchor($link, $tc['title'], array('title' => $this->cms_labels['edit']." '{$tc['title']}'"));
			}
			$form .= ul($form_options);
			$form .= "</li>\r";
		}
		$form .= "</ul>\r";


		$form_options = $this->Content_model->types_dropdown();
		$form .= "<p>Create New ";
		$form .= form_dropdown('new_content',$form_options).' ';
		$form_options = array(
			'name' => 'new_go',
			'id' => 'new_go',
			'content' => 'Go',
			'type' => 'button',
		);
		$form .= form_button($form_options);
		$form .= "</p>\r";

		$form .= "<h2>{$this->cms_labels['media']}</h2>\r";
		
		$media_links[] = anchor("cms/media/manage",$this->cms_labels['media_manage']);
		$media_links[] = anchor("cms/media/upload",$this->cms_labels['media_upload']);
		$form .= ul($media_links)."\r";

		$this->Content_model->temp_value = 'cms/setup/site_metadata';
		$site_metadata = $this->Content_model->lang_meta_menu();
		$this->data['sidebar'] = $this->load->view('cms/setup_menu',array('site_metadata' => $site_metadata,'cms_labels' => $this->cms_labels),true);

		$breadcrumb = anchor("",$this->cms_labels['home'],array('rel' => 'external','title' => $this->cms_labels['site_external_link'])).' : ';
		$breadcrumb .= anchor("cms",$this->cms_labels['phsite_cms']).' : '.$this->cms_labels['phsite_cms_home'];
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_h1'] = $this->cms_labels['phsite_cms_home'];
		
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