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

	}
	
	function index()
	{
		$this->_authenticate();

		// load list of content types
		$this->Content_model->temp_lang = $this->Site_model->temp_lang;

		$html = "";

		// get recently added pages
		
		$this->Content_model->limit = 5;
		$this->Content_model->page = 1;
		$this->Content_model->list_content();

		if(count($this->Content_model->content_list) > 0)
		{

			$html .= "<h2>{$this->cms_labels['recent_content']}</h2>\r";
			// generate result table
			$html .= "<table class=\"dotted\">\r";
			$html .= "\t<thead>\r";
			$html .= "\t\t<tr>\r";
			$html .= "\t\t\t<th scope=\"col\" id=\"content-ids\">{$this->cms_labels['title']}</th>\r";
			$html .= "\t\t\t<th scope=\"col\" id=\"types\">{$this->cms_labels['content_type']}</th>\r";
			$html .= "\t\t\t<th scope=\"col\" id=\"authors\">{$this->cms_labels['author']}</th>\r";
			$html .= "\t\t</tr>\r";
			$html .= "\t</thead>\r";
			$html .= "\t<tbody>\r";
			
			foreach($this->Content_model->content_list as $item)
			{
	
				// determine edit uri
				$edit_uri = 'cms/content/edit/id/'.$item['content_id'];
				if($this->Content_model->temp_lang != '')
				{
					$edit_uri .= '/lang/'.$this->Content_model->temp_lang;
				}
				$cid = $item['content_id'];
	
				$html .= "\t\t\t<th class=\"align-left\" id=\"content-{$cid}\" headers=\"content-ids\">";
				$html .= anchor($edit_uri, $item['title'], array('title' => sprintf($this->cms_labels['edit_this'],$item['title'])));
				$html .= "</th>\r";
	
				$type_name = $item['type_uri_name'];
				if($item['type_label'] != '')
				{
					$type_name = $item['type_label'];
				}
				$html .= "\t\t\t<td headers=\"content-{$cid} types\">".$type_name."</td>\r";
	
				// author info
				$this->User_model->temp_id = $item['user_id'];
				$this->User_model->load_user();
				$name = $this->User_model->name_last.', '.$this->User_model->name_first;

				if($this->session->userdata('admin') == 1)
				{
					$html .= "\t\t\t<td headers=\"content-{$cid} authors\">".anchor('cms/user/edit/id/'.$item['user_id'],$name,array('title' => $this->cms_labels['user_edit']))."</td>\r";
				}
				else
				{
					$html .= "\t\t\t<td headers=\"content-{$cid} authors\">".$name."</td>\r";
				}
	
				$html .= "\t\t</tr>\r";
			}
	
			$html .= "\t</tbody>\r";
			$html .= "</table>\r";
		}

		$this->data['form'] = $html;

		$this->_load_cms_menus();

		$breadcrumb = anchor("",$this->cms_labels['home'],array('rel' => 'external','title' => $this->cms_labels['site_external_link'])).' : ';
		$breadcrumb .= anchor("cms",$this->cms_labels['phsite_cms']).' : '.$this->cms_labels['phsite_cms_home'];
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_h1'] = $this->cms_labels['phsite_cms_home'];
		
		$this->load->view('cms/cms_form_2col',$this->data);
	}

	
	function login()   
	{  

		// if already logged in, redirect
		if($this->session->userdata('user_id'))
		{
			redirect('cms');
			exit();
		}

		$this->_load_cms_labels();
		$this->_load_site_metadata();

		$this->load->library('form_validation');

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
				$this->User_model->messages[] = validation_errors();
			} else {
				// perform authentication
				$this->User_model->temp_value = $this->input->post('username');
				$this->User_model->tmp_pwd = $this->input->post('password');
				
				$auth = $this->User_model->authenticate_user();

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

					$this->User_model->messages[] = $this->cms_labels['login_failed'];
				}

			}

		}

		$this->data['showform'] = TRUE;
		if($attempts <= 0)
		{
		}
		elseif($attempts < 3)
		{
			$this->User_model->messages[] = sprintf($this->cms_labels['login_nth_attempt'],$attempts,3 - $attempts);
		}
		else
		{
			$this->session->sess_destroy();
			$sess = array('attempts' => $attempts);
			$this->session->set_userdata($sess);

			$this->User_model->messages[] = sprintf($this->cms_labels['login_lockout'],$attempts,2);

			$this->data['showform'] = FALSE;
		}

		$msg = $this->User_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$this->data['action'] = site_url("cms/login");

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['login'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);
		
		$this->load->view('cms/login',$this->data);  
	} 

	function logout()   
	{  

		$attempts = $this->session->userdata('attempts');
		if($attempts === FALSE || $attempts < 3)
		{
			$this->session->sess_destroy();
		}

		redirect('');
		exit();
	} 

}
?>