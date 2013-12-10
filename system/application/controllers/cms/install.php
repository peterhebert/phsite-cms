<?php
class Install extends Controller
{
	var $installer_step = 1;
	var $data_status = array('s_users' => FALSE, 's_site_prefs' => FALSE);

	var $data = array(), $status_labels = array(), $cms_labels = array(), $custom_js = array();
	var $messages = array(), $debugging = array();
	var $form_lang = 'english';
	var $form_lang_short = 'eng';
	var $installer_langs = array('english');
	var $all_langs = array();

	// db config variables
	var $db_hostname = 'localhost', $db_username, $db_password, $db_database, $db_driver = 'mysql', $db_prefix = 'phsite_';

	// site variables
	var $title, $subtitle, $keywords;
	var $site_id = 1, $site_admin_id = 1, $site_editor_id = 1;
	var $site_timezone = '', $theme = 'simple_em';
	var $site_default_lang = 'eng', $site_date_created, $site_date_updated;
	var $home_content_id = 0, $home_content_type = 0, $default_content_type = 1;
	var $img_size_th = 90, $img_size_sm = 180, $img_size_md = 320, $img_size_lg = 640, $limit_posts = 10, $limit_thumbs = 30;

	function Install()
	{
		parent::Controller();
		$this->load->helper('form');
		$this->config->load('installer');
		$this->config->load('lang-import');
		$this->load->model('User_model');
		
		$this->site_timezone = date_default_timezone_get();
	}
	
	function _db_test_connection($config = array())
	{
		$DB1 = $this->load->database($config, TRUE);
		return $DB1;	
	}

	function _db_test()
	{
		$db_file = 'system/application/config/database.php';
		$db_conf = FCPATH.$db_file;
		if(file_exists($db_conf)) {
			require_once($db_file);

			if(isset($db,$active_group) && array_key_exists($active_group,$db) && $active_group !='' && $db[$active_group]['hostname'] != "" && $db[$active_group]['username'] != "" && $db[$active_group]['password'] != "" && $db[$active_group]['database'] != "" && $db[$active_group]['dbdriver'] != "")
			{
				// test the supplied connection
				$DB1 = $this->_db_test_connection($db[$active_group]);
				if($DB1)
				{
					$this->installer_step = 2;

					// now query the tables to see if they have been created and are in tact
					$tables = $this->config->item('tables');
					
					if($tables)
					{
						$table_errors = 0;
						$sql_fields = array();
						foreach($tables as $table => $fields)
						{
							$fds = implode(', ',$fields);
							$DB1->select($fds);
							$DB1->limit(1);
	
							$query = $DB1->get($table);

							if(!$query) {
								$table_errors = $table_errors + 1;
								$this->debugging[] = "table '$table' does not exist";
							}
							else
							{

								if(($table == 's_users' || $table == 's_site_prefs') &&  $query->num_rows() > 0){
									$this->data_status[$table] = TRUE;
								}

							}
							$this->debugging[] = $DB1->last_query();

						}

						if( $table_errors == 0)
						{
							$this->installer_step = 3;
							if($this->data_status['s_site_prefs'] == TRUE)
							{
								$this->installer_step = 4;
								if($this->data_status['s_users'] == TRUE)
								{
									$this->installer_step = 5;
								}
							}
						}
					}
				// end connection test
				}
			}

		}
		$this->debugging[] = 'step '.$this->installer_step;

	}	

	function index()
	{

		$this->installer_langs = $this->config->item('installer_langs');
		$langs = $this->config->item('lang_import');
		foreach($langs as $lg)
		{
			$this->all_langs[$lg['ISO_639_3']] = $lg['Ref_Name'];
		}

		if($this->uri->segment(3))
		{
			$tmp_lang = $this->uri->segment(3);
			
			if(preg_match('#^([a-z]{3})$#', $tmp_lang) && array_key_exists($tmp_lang,$this->all_langs))
			{
				$this->site_default_lang = $tmp_lang;
			}
			
			// get lang for form and user
			if(preg_match('#^([a-z]{3})$#', $tmp_lang) && array_key_exists($tmp_lang,$this->installer_langs))
			{
				$this->form_lang = $this->installer_langs[$tmp_lang];
				$this->form_lang_short = $tmp_lang;
			}	
			
		}

		$this->all_langs = array();
		foreach($langs as $lg)
		{
			$tmp = $lg['Ref_Name'];

			if($lg['Native_Name'] != '')
			{
				$tmp = $lg['Native_Name'];
			}
			$this->all_langs[$lg['ISO_639_3']] = $tmp;
			
		}
		asort($this->all_langs);


		$this->lang->load('installer', $this->form_lang);
		$this->lang->load('cms', $this->form_lang);

		$this->cms_labels = array_merge($this->lang->line('cms_labels'), $this->lang->line('installer'));

		$this->data['cms_labels'] = $this->cms_labels;

		$this->data['title_h1'] = $this->cms_labels['install_phsite'];
		$this->data['title_tag'] = $this->cms_labels['install'];
		
		$this->_db_test();

		if($this->installer_step < 5)
		{
			$this->_process_install();
		}

		// Database config missing or invalid, start installer
		if($this->installer_step == 1)
		{
			$this->_form_install_db();
		}
		
		// db config exists but no tables - confirm creation of tables
		if($this->installer_step == 2)
		{
			$this->_form_confirm_install();
		}

		// tables exist, but no users or site prefs
		if($this->installer_step == 3)
		{
			$this->_form_install_prefs();
		}
		
		// site prefs exist but no users - create admin user
		if($this->installer_step == 4)
		{
			$this->_form_install_user();
		}

		if(!$this->input->post('submit-user') && $this->installer_step == 5)
		{
			$this->installer_step = 0;
		}

		// fully installed, do nothing
		if($this->installer_step == 5 || $this->installer_step == 0)
		{
			$this->debugging = array();
			$this->load->helper('html');
			$this->load->helper('url');
			$links = array();
			$links[] = anchor(BASE_PATH.'/', $this->cms_labels['home_page']);
			$links[] = anchor(BASE_PATH.'/cms', $this->cms_labels['login']);
			$this->data['form'] = ul($links);
		}

		//prepare steps list for view
		$steps = array(
		$this->cms_labels['db_exists'],
		$this->cms_labels['db_config'],
		$this->cms_labels['install_write_tables'],
		$this->cms_labels['site_prefs'],
		$this->cms_labels['create_admin_user'],
		$this->cms_labels['install_success']
		);

		switch($this->installer_step)
		{
		case 1:
		case 2:
		case 3:
		case 4:
			$this->data['step'] = sprintf($this->cms_labels['install_step'],$this->installer_step).' - '.$steps[$this->installer_step];
			break;
		default:
			$this->messages[] = $steps[$this->installer_step];
			break;
		}
		

		// prepare list of installer languages
		$this->load->helper('url');
		$this->load->helper('html');
		$sidebar = "<h3>".$this->cms_labels['installer_languages']."</h3>\r";
		$lang_links = array();
		foreach($this->installer_langs as $short => $long)
		{
			
			$link = '/cms/install/'.$short;
			if($short != $this->form_lang_short)
			{
				$lang_links[] = anchor($link, $long);
			}
			else
			{
				$lang_links[] = "<strong>$long</strong>";
			}
		}
		$sidebar .= ul($lang_links);
		$this->data['sidebar'] = $sidebar;
		
		unset($lang_links,$sidebar);

		if(DEV_MODE)
		{
			$this->messages = array_merge($this->messages,$this->debugging);
		}
		
		$msg = $this->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		$this->load->view('cms/install',$this->data);

	} 

	function _process_install()
	{
		$this->load->library('form_validation');
		// see if form is submitted
		if($this->input->post('submit-db'))
		{
			// set validation rules

			$this->form_validation->set_rules('db_hostname', $this->cms_labels['db_hostname'], 'required');
			$this->form_validation->set_rules('db_username', $this->cms_labels['db_username'], 'required');
			$this->form_validation->set_rules('db_password', $this->cms_labels['db_password'], 'required');
			$this->form_validation->set_rules('db_database', $this->cms_labels['db_database'], 'required');
			$this->form_validation->set_rules('db_driver', $this->cms_labels['db_driver'], 'required');
			$this->form_validation->set_rules('db_prefix', $this->cms_labels['db_prefix'], 'alpha_dash');

			// run form validation
			$form_valid = $this->form_validation->run();

			$this->db_hostname = $this->input->post('db_hostname');
			$this->db_username = $this->input->post('db_username');
			$this->db_password = $this->input->post('db_password');
			$this->db_database = $this->input->post('db_database');
			$this->db_driver = $this->input->post('db_driver');
			$this->db_prefix = $this->input->post('db_prefix');
			
			if ($form_valid)
			{
				$this->messages[] = "db setup form is valid";

				$config = array(
				'hostname' => $this->db_hostname,
				'username' => $this->db_username,
				'password' => $this->db_password,
				'database' => $this->db_database,
				'dbdriver' => $this->db_driver,
				'dbprefix' => $this->db_prefix,
				'pconnect' => TRUE,
				'db_debug' => FALSE,
				'cache_on' => FALSE,
				'cachedir' => "",
				'char_set' => "utf8",
				'dbcollat' => "utf8_general_ci",
				);
				$DB1 = $this->_db_test_connection($config);
				if($DB1)
				{
					// write .htaccess file
					$this->_write_htaccess();
					// write config files
					if($this->_write_db_config())
					{
						$this->installer_step = 2;
						$this->messages[] = $this->cms_labels['db_config_success'];
					}
					$this->_write_ci_config();
				}

			}
			else
			{
				$this->messages[] = validation_errors();
			}
		}

		if($this->input->post('submit-prefs'))
		{
			// set validation rules
			$this->form_validation->set_rules('title', $this->cms_labels['title'], 'required');
			$this->form_validation->set_rules('home_content_id', $this->cms_labels['home_page'], 'required|is_natural');
			$this->form_validation->set_rules('home_content_type', $this->cms_labels['home_content_type'], 'required|is_natural');
			$this->form_validation->set_rules('site_admin_id', $this->cms_labels['webmaster'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('site_editor_id', $this->cms_labels['editor'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('site_default_lang', $this->cms_labels['default_lang'], 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('default_content_type', $this->cms_labels['default_content_type'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('theme', $this->cms_labels['theme'], 'required|alpha_dash');
			$this->form_validation->set_rules('site_timezone', $this->cms_labels['timezone'], 'required');
			$this->form_validation->set_rules('limit_posts', $this->cms_labels['limit_posts'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('limit_thumbs', $this->cms_labels['limit_thumbs'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_th', $this->cms_labels['img_size_th'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_sm', $this->cms_labels['img_size_sm'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_md', $this->cms_labels['img_size_md'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_lg', $this->cms_labels['img_size_lg'], 'required|is_natural_no_zero');

			// run form validation
			$form_valid = $this->form_validation->run();
			
			$this->title = $this->input->post('title');
			$this->subtitle = $this->input->post('subtitle');
			$this->keywords = csv_normalize($this->input->post('keywords'));

			$this->home_content_id = $this->input->post('home_content_id');
			$this->home_content_type = $this->input->post('home_content_type');
			$this->site_timezone = $this->input->post('site_timezone');
			$this->theme = $this->input->post('theme');
			$this->site_default_lang = $this->input->post('site_default_lang');
			$this->default_content_type = $this->input->post('default_content_type');
			$this->site_admin_id = $this->input->post('site_admin_id');
			$this->site_editor_id = $this->input->post('site_editor_id');
			$this->limit_posts = $this->input->post('limit_posts');
			$this->limit_thumbs = $this->input->post('limit_thumbs');
			$this->img_size_th = $this->input->post('img_size_th');
			$this->img_size_sm = $this->input->post('img_size_sm');
			$this->img_size_md = $this->input->post('img_size_md');
			$this->img_size_lg = $this->input->post('img_size_lg');
			$this->site_date_created = $this->input->post('site_date_created');

			if($this->input->post('site_id') != '')
			{
				$this->site_id = $this->input->post('site_id');
			}

			if ($form_valid)
			{
	
				if($this->input->post('site_date_created') == '' || $this->input->post('site_date_created') == '0000-00-00 00:00:00')
				{
					$this->site_date_created = date('Y-m-d H:i:s');
				}

				$success = $this->_write_site_prefs();
				if($success)
				{
					$this->messages[] = $this->cms_labels['site_prefs_update_success'];
				}
			}
			else
			{
				$this->messages[] = validation_errors();
			}

		}

		// confirm table creation
		if($this->input->post('submit-confirm-install'))
		{
			if($this->input->post('confirm') == 1)
			{
				$this->_create_tables();
			}
			
		}


		// user form
		if($this->input->post('submit-user'))
		{
			// set validation rules
	
			$this->form_validation->set_rules('name_first', $this->cms_labels['name_first'], 'required');
			$this->form_validation->set_rules('name_last', $this->cms_labels['name_last'], 'required');
			$this->form_validation->set_rules('username', $this->cms_labels['username'], 'required|min_length[8]|alpha_dash');
			$this->form_validation->set_rules('email', $this->cms_labels['email'], 'required|valid_email');
			$this->form_validation->set_rules('timezone', $this->cms_labels['timezone'], 'required|is_timezone');
			$this->form_validation->set_rules('admin', $this->cms_labels['user_type'], 'required|is_natural');
			$this->form_validation->set_rules('default_lang', $this->cms_labels['default_lang'], 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('password', $this->cms_labels['password'], 'required|min_length[8]|alpha_dash');
			$this->form_validation->set_rules('password_confirm', $this->cms_labels['password_confirm'], 'required|min_length[8]|alpha_dash|matches[password]');

			$this->load->database();
			// run form validation
			$form_valid = $this->form_validation->run();
			
			$this->User_model->name_first = $this->input->post('name_first');
			$this->User_model->name_last = $this->input->post('name_last');
			$this->User_model->username = $this->input->post('username');
			$this->User_model->tmp_pwd = $this->input->post('password');
			$this->User_model->tmp_pwd_conf = $this->input->post('password_confirm');
			$this->User_model->timezone = $this->input->post('timezone');
			$this->User_model->default_lang = $this->input->post('default_lang');
			$this->User_model->email = $this->input->post('email');

			$this->site_timezone = $this->input->post('timezone');
			$this->site_default_lang = $this->input->post('default_lang');
			
			if ($form_valid)
			{
				// update user
				$updated = $this->User_model->update_user();

				if($updated)
				{
					$this->User_model->admin = 1;
					$this->User_model->site_id = $this->site_id;

					if($this->User_model->update_site_user()) {
						$this->messages[] = $this->cms_labels['admin_user_success'];
						$this->installer_step = 5;
					}

				}
				else
				{
					$this->messages = array_merge($this->messages,$this->User_model->messages);
				}
						
			}
			else
			{
				$this->messages[] = validation_errors();
				$this->form_success = -1;
			}

		}

	}

	function _form_install_db()
	{
		$action = site_url('cms/install');
		$attributes = array(
		'id' => 'install-form'
		);
		$hidden = array(
		'lang' => $this->form_lang
		);
		$html = form_open($action, $attributes, $hidden);

		$html .= "<div class=\"align-labels label-wide\">\n";

		$form_options = $this->lang->line('db_drivers');

		$html .= "<p>\n";
		$html .= form_label($this->cms_labels['db_driver'], 'db_driver');
		$html .= form_dropdown('db_driver', $form_options,$this->db_driver);		
		$html .= "</p>\n";

		$form_options = array(
		'name'        => 'db_hostname',
		'class'        => 'med_input',
		'value'        => $this->db_hostname,
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['db_hostname'], 'db_hostname');
		$html .=  form_input($form_options);
		$html .= "</p>\n";
		$html .= "<p class=\"indent-wide\">".$this->cms_labels['hint_hostname']."</p>\n";

		$form_options = array(
		'name'        => 'db_username',
		'class'        => 'med_input',
		'value'        => $this->db_username,
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['db_username'], 'db_username');
		$html .=  form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'name'        => 'db_password',
		'class'        => 'password',
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['db_password'], 'db_password');
		$html .=  form_password($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'name'        => 'db_database',
		'class'        => 'med_input',
		'value'        => $this->db_database,
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['db_database'], 'db_database');
		$html .=  form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'name'        => 'db_prefix',
		'class'        => 'med_input',
		'value'        => $this->db_prefix,
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['db_prefix'], 'db_prefix');
		$html .=  form_input($form_options);
		$html .= "</p>\n";
		$html .= "<p class=\"indent-wide\">".$this->cms_labels['hint_prefix']."</p>\n";

		$html .= "</div>\n";

		$html .= "<p class=\"indent-wide\">";
		$html .= form_submit('submit-db', $this->cms_labels['save']);
		$html .= "</p>\n";

		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _form_install_prefs()
	{
		$this->load->database();
		$this->load->helper('timezone');

		$action = site_url('cms/install');
		$attributes = array(
		'id' => 'install-prefs'
		);
		$hidden = array(
		'lang' => $this->form_lang,
		'home_content_id' => $this->home_content_id,
		'home_content_type' => $this->home_content_type,
		'theme' => $this->theme,
		'default_content_type' => $this->default_content_type,
		'site_admin_id' => $this->site_admin_id,
		'site_editor_id' => $this->site_editor_id,
		);
		$html = form_open($action, $attributes, $hidden);

		$html .= "<div class=\"align-labels label-normal\">";

		$form_options = array(
		'class'        => 'text',
		'name'        => 'title',
		'id'          => 'title',
		'value'       => $this->title
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['title'], 'title');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'text',
		'name'        => 'subtitle',
		'id'          => 'subtitle',
		'value'       => $this->subtitle
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['subtitle'], 'subtitle');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'id' => 'keywords',
		'name' => 'keywords',
		'class' => 'textarea',
		'value' => $this->keywords
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['keywords'],'keywords');
		$html .=  form_textarea($form_options);
		$html .= "</p>\r";

		$html .= "<p>";
		$html .= form_label($this->cms_labels['default_lang'], 'site_default_lang');
		$html .= form_dropdown('site_default_lang', $this->all_langs, $this->site_default_lang);		
		$html .= "</p>\r";

		$form_options = timezone_dropdown();
		$html .= "<p>";
		$html .= form_label($this->cms_labels['timezone'], 'site_timezone');
		$html .= form_dropdown('site_timezone', $form_options, $this->site_timezone);		
		$html .= "</p>\r";

		$form_options = array(
		'class'	=> 'med_input',
		'name'        => 'site_date_created',
		'id'          => 'site_date_created',
		'value'       => $this->site_date_created
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['site_date_created'], 'site_date_created');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'limit_posts',
		'id'          => 'limit_posts',
		'value'       => $this->limit_posts
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['limit_posts'], 'limit_posts');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'limit_thumbs',
		'id'          => 'limit_thumbs',
		'value'       => $this->limit_thumbs
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['limit_thumbs'], 'limit_thumbs');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_th',
		'id'          => 'img_size_th',
		'value'       => $this->img_size_th
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_th'], 'img_size_th');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_sm',
		'id'          => 'img_size_sm',
		'value'       => $this->img_size_sm
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_sm'], 'img_size_sm');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_md',
		'id'          => 'img_size_md',
		'value'       => $this->img_size_md
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_md'], 'img_size_md');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_lg',
		'id'          => 'img_size_lg',
		'value'       => $this->img_size_lg
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_lg'], 'img_size_lg');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$html .= "</div>\r";

		$html .= "<p class=\"indent-wide\">";
		$html .= form_submit('submit-prefs', $this->cms_labels['save']);
		$html .= "</p>\n";

		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _form_confirm_install()
	{

		$action = site_url('cms/install');
		$attributes = array(
		'id' => 'install-confirm-install'
		);
		$hidden = array(
		'lang' => $this->form_lang
		);
		$html = form_open($action, $attributes, $hidden);
		$html .= "<p>".$this->cms_labels['confirm_install_explain']."</p>\r";
		$html .= "<div class=\"label-margins\">\n";

		$form_options = array(
		'name'        => 'confirm',
		'id'          => 'confirm',
		'value'          => 1,
		);
		$html .= "<p>";
		$html .= form_checkbox($form_options);
		$html .= form_label($this->cms_labels['confirm_install'], 'confirm');
		$html .= "</p>\r";
		$html .= "</div>\r";

		$html .= "<p>";
		$html .= form_submit('submit-confirm-install', $this->cms_labels['submit']);
		$html .= "</p>\n";

		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _form_install_user()
	{

		$action = site_url('cms/install');
		$attributes = array(
		'id' => 'install-user'
		);
		$hidden = array(
		'lang' => $this->form_lang,
		'db_installed' => 1,
		'admin' => 1
		);
		$html = form_open($action, $attributes, $hidden);

		$html .= "<div class=\"align-labels label-wide\">\n";
		$html .= "<p>".$this->cms_labels['user_info_fields_req']."</p>\r";
		
		$form_options = array(
		'name'        => 'name_first',
		'id'          => 'name_first',
		'value'       => $this->User_model->name_first,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['name_first'], 'name_first');
		$html .= form_input($form_options);
		$html .= "</p>\r";

		$form_options = array(
		'name'        => 'name_last',
		'id'          => 'name_last',
		'value'       => $this->User_model->name_last,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['name_last'], 'name_last');
		$html .= form_input($form_options);
		$html .= "</p>\r";

		$form_options = array(
		'name'        => 'username',
		'id'          => 'username',
		'value'       => $this->User_model->username,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['username'], 'username');
		$html .= form_input($form_options);
		$html .= "</p>\r";

		$form_options = array(
		'name'        => 'email',
		'id'          => 'email',
		'value'       => $this->User_model->email,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['email'], 'email');
		$html .= form_input($form_options);
		$html .= "</p>\r";

		$zones = timezone_dropdown();
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['timezone'], '');
		$html .= form_dropdown('timezone', $zones, $this->site_timezone);		
		$html .= "</p>\r";

		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['default_lang'], 'default_lang');
		$html .= form_dropdown('default_lang', $this->all_langs, $this->site_default_lang);		
		$html .= "</p>\r";

		$form_options = array(
		'name'        => 'password',
		'id'          => 'password',
		'value'          => ''
		);
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['password_choose'], 'password');
		$html .= form_password($form_options);
		$html .= "</p>\r";
		
		$form_options = array(
		'name'        => 'password_confirm',
		'id'          => 'password_confirm',
		'value'          => ''
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['password_confirm'], 'password_confirm');
		$html .= form_password($form_options);
		$html .= "</p>\r";

		$html .= "</div>\r";

		$html .= "<p class=\"indent-wide\">";
		$html .= form_submit('submit-user', $this->cms_labels['save']);
		$html .= "</p>\n";

		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _output_messages()
	{
		if(count($this->messages) > 0)
		{
			$tmp_msg = "<p>";
			$tmp_msg .= implode("</p>\r<p>",$this->messages);
			$tmp_msg .= "</p>";
			return $tmp_msg;
		}
		return FALSE;
	}
	
	function _write_db_config()
	{
		$config  = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\r";
		$config .= "// -------------------------------------------------------------------\r";
		$config .= "// DATABASE CONNECTIVITY SETTINGS\r";
		$config .= "// -------------------------------------------------------------------\r";
		$config .= "// This file will contain the settings needed to access your database.\r\r";
		$config .= "// For complete instructions please consult the \"Database Connection\"\r";
		$config .= "// page of the User Guide.\r\r";
		$config .= "// -------------------------------------------------------------------\r";
		$config .= "// EXPLANATION OF VARIABLES\r";
		$config .= "// -------------------------------------------------------------------\r\r";
		$config .= "//	['hostname'] The hostname of your database server.\r";
		$config .= "//	['username'] The username used to connect to the database\r";
		$config .= "//	['password'] The password used to connect to the database\r";
		$config .= "//	['database'] The name of the database you want to connect to\r";
		$config .= "//	['dbdriver'] The database type. ie: mysql.  Currently supported:\r";
		$config .= "//				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8\r";
		$config .= "//	['dbprefix'] You can add an optional prefix, which will be added\r";
		$config .= "//				 to the table name when using the  Active Record class\r";
		$config .= "//	['pconnect'] TRUE/FALSE - Whether to use a persistent connection\r";
		$config .= "//	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.\r";
		$config .= "//	['cache_on'] TRUE/FALSE - Enables/disables query caching\r";
		$config .= "//	['cachedir'] The path to the folder where cache files should be stored\r";
		$config .= "//	['char_set'] The character set used in communicating with the database\r";
		$config .= "//	['dbcollat'] The character collation used in communicating with the database\r\r";
		$config .= "// The \$active_group variable lets you choose which connection group to\r";
		$config .= "// make active.  By default there is only one group (the \"default\" group).\r\r";
		$config .= "// The \$active_record variables lets you determine whether or not to load\r";
		$config .= "// the active record class\r\r";
		$config .= "\$active_group = \"default\";\r";
		$config .= "\$active_record = TRUE;\r\r";
		$config .= "\$db['default']['hostname'] = \"{$this->db_hostname}\";\r";
		$config .= "\$db['default']['username'] = \"{$this->db_username}\";\r";
		$config .= "\$db['default']['password'] = \"{$this->db_password}\";\r";
		$config .= "\$db['default']['database'] = \"{$this->db_database}\";\r";
		$config .= "\$db['default']['dbdriver'] = \"{$this->db_driver}\";\r";
		$config .= "\$db['default']['dbprefix'] = \"{$this->db_prefix}\";\r";
		$config .= "\$db['default']['pconnect'] = TRUE;\r";
		$config .= "\$db['default']['db_debug'] = FALSE;\r";
		$config .= "\$db['default']['cache_on'] = FALSE;\r";
		$config .= "\$db['default']['cachedir'] = \"\";\r";
		$config .= "\$db['default']['char_set'] = \"utf8\";\r";
		$config .= "\$db['default']['dbcollat'] = \"utf8_general_ci\";\r\r\r";
		$config .= "// End of file database.php \r";
		$config .= "// Location: ./system/application/config/database.php \r";

		$this->load->helper('file');
		$db_config_filename = ROOT_REL.'/system/application/config/database.php';
		
		$written = write_file($db_config_filename, $config);
		return $written;
	}

	function _create_tables()
	{
		$this->load->database();
		$this->load->dbforge();
		$table_def = 'tables_'.$this->db->dbdriver;
		$tables = $this->config->item($table_def);
		$table_count = 0;
		
		foreach($tables as $table => $sql)
		{
			
			$this->dbforge->drop_table($table);
			
			$table_sql = sprintf($sql,$this->db->dbprefix.$table);
			$this->debugging[] = $table_sql;
			
			$query = $this->db->query($table_sql);

			if($query)
			{
				$table_count = $table_count + 1;
			}
			else
			{
				$this->debugging[] = "could not create table '{$table}'";
				$this->debugging[] = $this->db->last_query();
			}

		}
		if($table_count === 15)
		{
			$this->installer_step = 3;
			$this->debugging[] = "successfully created all tables";
			
			$data = $this->config->item('default_data');
			$errors = 0;
			foreach($data as $table => $sql)
			{
				if(function_exists('utf8_encode'))
				{
					$sql = utf8_encode($sql);
				}
				$query = $this->db->query($sql);
				if(!$query)
				{
					$errors = $errors + 1;
					$this->debugging[] = "could not insert data for table '{$table}'";
					$this->debugging[] = $this->db->last_query();
				}
			}
			$langs = $this->config->item('lang_import');
			foreach($langs as $lg)
			{
				if(!$this->db->insert('ISO_639_LANG', $lg))
				{
					$errors = $errors + 1;
					$this->debugging[] = "could not insert language '{$lg['CI_Name']}'";
				}
			}

			if($errors == 0)
			{
				$this->debugging[] = "successfully inserted default data";
			}
		}

	}
	
	function _write_site_prefs()
    {
		$this->load->database();
		$db_fields = array(
		'theme' => $this->theme,
		'timezone' => $this->site_timezone,
		'default_lang' => $this->site_default_lang,
		'default_content_type' => $this->default_content_type,
		'date_created' => $this->site_date_created,
		'home_content_id' => $this->home_content_id,
		'home_content_type' => $this->home_content_type,
		'site_admin_id' => $this->site_admin_id,
		'site_editor_id' => $this->site_editor_id,
		'img_size_th' => $this->img_size_th,
		'img_size_sm' => $this->img_size_sm,
		'img_size_md' => $this->img_size_md,
		'img_size_lg' => $this->img_size_lg,
		'limit_posts' => $this->limit_posts,
		'limit_thumbs' => $this->limit_thumbs,
		);

		// create new site
		if($this->db->insert('s_site_prefs', $db_fields)) {
			$this->site_id = $this->db->insert_id();
			if($this->_write_phsite_prefs())
			{
				$this->installer_step = 4;
				return TRUE;
			}
		}
		return FALSE;

    }

	function _write_phsite_prefs()
	{
		$config  = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\r\r";
		$config .= "// phSite Global Preferences\r";
		$config .= "// date: {$this->site_date_created}\r\r";
		$config .= "// ID of the site to display\r";
		$config .= "\$config['site_id'] = {$this->site_id};\r\r";

		$config .= "// End of file phsite_prefs.php \r";
		$config .= "// Location: ./system/application/config/phsite_prefs.php \r";

		$this->load->helper('file');
		$db_config_filename = ROOT_REL.'/system/application/config/phsite_prefs.php';
		
		$written = write_file($db_config_filename, $config);
		return $written;
	}

	function _write_htaccess()
	{

		// intialize htaccess variable
		$txt = '';

		// first add HTML 5 mime type definitions
		$this->config->load('mime_info');
		$html5_types = $this->config->item('html5_types');

		foreach($html5_types as $ext => $type)
		{
			$txt .= "AddType {$type} .{$ext}\r";
		}

		// now add routing directives
		$txt  .= "<IfModule mod_rewrite.c>\r\r";
		$txt  .= "\tRewriteEngine On\r\r";
		$txt  .= "\t# Removes access to the system folder by users.\r";
		$txt  .= "\t# Additionally this will allow you to create a System.php controller,\r";
		$txt  .= "\t# previously this would not have been possible.\r";
		$txt  .= "\t# 'system' can be replaced if you have renamed your system folder.\r\r\t";
		$txt  .= 'RewriteCond %{REQUEST_URI} ^system.*'."\r\t";
		$txt  .= 'RewriteRule ^(.*)$ /index.php?/$1 [L]'."\r\r";
		$txt  .= "\t# Checks to see if the user is attempting to access a valid file,\r";
		$txt  .= "\t# such as an image or css document, if this isn't true it sends the\r";
		$txt  .= "\t# request to index.php\r\r\t";
		$txt  .= 'RewriteCond %{REQUEST_FILENAME} !-f'."\r\t";
		$txt  .= 'RewriteCond %{REQUEST_FILENAME} !-d'."\r\t";
		$txt  .= 'RewriteRule ^(.*)$ ./index.php?/$1 [L]'."\r\r";
		$txt  .= "</IfModule>\r\r";
		$txt  .= "<IfModule !mod_rewrite.c>\r";
		$txt  .= "\t# If we don't have mod_rewrite installed, all 404 errors\r";
		$txt  .= "\t# can be sent to index.php, and everything works as normal.\r\r";
		$txt  .= "\tErrorDocument 404 /index.php\r\r";
		$txt  .= "</IfModule>\r\r";

		$this->load->helper('file');
		$filename = ROOT_REL.'/.htaccess';

		if(!file_exists($filename))
		{
			$written = write_file($filename, $txt);
			if($written)
			{
				$this->debugging[] = $this->cms_labels['htaccess_success'];
			}
			else
			{
				$this->debugging[] = $this->cms_labels['htaccess_failure'];
			}
			return $written;
		}
		else
		{
			$this->debugging[] = $this->cms_labels['htaccess_exists'];
			return FALSE;
		}
	}

	function _write_ci_config()
	{
		// test if mod_rewrite module exists
		// if yes, then remove 'index.php'
		if (function_exists('apache_get_modules')) {
			$modules = apache_get_modules();
			$mod_rewrite = in_array('mod_rewrite', $modules);
		} else {
			$mod_rewrite =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;
		}

		if($mod_rewrite)
		{
			$config  = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\r\r";
			$config  = '';
			$config .= "/*\r";
			$config .= "|--------------------------------------------------------------------------\r";
			$config .= "| Base Site URL\r";
			$config .= "|--------------------------------------------------------------------------\r";
			$config .= "|\r";
			$config .= "| URL to your CodeIgniter root. Typically this will be your base URL,\r";
			$config .= "| WITH a trailing slash:\r";
			$config .= "|\r";
			$config .= "|	http://example.com/\r";
			$config .= "|\r";
			$config .= "*/\r\r";
			$config .= '$protocol = "http" . ((isset($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\'] == "on") ? "s" : "") . "://";'."\r";
			$config .= '$server = isset($_SERVER[\'HTTP_HOST\']) ? $_SERVER[\'HTTP_HOST\'] : $_SERVER[\'SERVER_NAME\'];'."\r";
			$config .= '$dir = preg_replace(\'@/+$@\',\'\',dirname($_SERVER[\'SCRIPT_NAME\'])).\'/\';'."\r";
			$config .= '$config[\'base_url\'] = $protocol . $server . $dir;'."\r";
			$config .= 'unset($protocol,$server,$dir);'."\r\r";
	
			$config .= "|--------------------------------------------------------------------------\r";
			$config .= "| Index File\r";
			$config .= "|--------------------------------------------------------------------------\r";
			$config .= "|\r";
			$config .= "| Typically this will be your index.php file, unless you've renamed it to\r";
			$config .= "| something else. If you are using mod_rewrite to remove the page set this\r";
			$config .= "| variable so that it is blank.\r";
			$config .= "|\r";
			$config .= "*/\r";
			$config .= '$config[\'index_page\'] = "";'."\r\r";
	
			$config .= "/*\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= "| URI PROTOCOL\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= "|\r";
			$config .= '| This item determines which server global should be used to retrieve the'."\r";
			$config .= '| URI string.  The default setting of "AUTO" works for most servers.'."\r";
			$config .= '| If your links do not seem to work, try one of the other delicious flavors:'."\r";
			$config .= "|\r";
			$config .= '| \'AUTO\'		Default - auto detects'."\r";
			$config .= '| \'PATH_INFO\'		Uses the PATH_INFO'."\r";
			$config .= '| \'QUERY_STRING\'	Uses the QUERY_STRING'."\r";
			$config .= '| \'REQUEST_URI\'		Uses the REQUEST_URI'."\r";
			$config .= '| \'ORIG_PATH_INFO\'	Uses the ORIG_PATH_INFO'."\r";
			$config .= "|\r";
			$config .= '*/'."\r";
			$config .= '$config[\'uri_protocol\'] = "REQUEST_URI";'."\r\r";
	
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| URL suffix'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| This option allows you to add a suffix to all URLs generated by CodeIgniter.'."\r";
			$config .= '| For more information please see the user guide:'."\r";
			$config .= '|'."\r";
			$config .= '| http://codeigniter.com/user_guide/general/urls.html'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'url_suffix\'] = "";'."\r\r";
	
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Default Language'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| This determines which set of language files should be used. Make sure'."\r";
			$config .= '| there is an available translation if you intend to use something other'."\r";
			$config .= '| than english.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'language\'] = "english";'."\r\r";
	
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Default Character Set'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| This determines which character set is used by default in various methods'."\r";
			$config .= '| that require a character set to be provided.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'charset\'] = "UTF-8";'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Enable/Disable System Hooks'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| If you would like to use the "hooks" feature you must enable it by'."\r";
			$config .= '| setting this variable to TRUE (boolean).  See the user guide for details.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'enable_hooks\'] = FALSE;'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Class Extension Prefix'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| This item allows you to set the filename/classname prefix when extending'."\r";
			$config .= '| native libraries.  For more information please see the user guide:'."\r";
			$config .= '|'."\r";
			$config .= '| http://codeigniter.com/user_guide/general/core_classes.html'."\r";
			$config .= '| http://codeigniter.com/user_guide/general/creating_libraries.html'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'subclass_prefix\'] = \'MY_\';'."\r\r";
	
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Allowed URL Characters'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| This lets you specify with a regular expression which characters are permitted'."\r";
			$config .= '| within your URLs.  When someone tries to submit a URL with disallowed'."\r";
			$config .= '| characters they will get a warning message.'."\r";
			$config .= '|'."\r";
			$config .= '| As a security measure you are STRONGLY encouraged to restrict URLs to'."\r";
			$config .= '| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-'."\r";
			$config .= '|'."\r";
			$config .= '| Leave blank to allow all characters -- but only if you are insane.'."\r";
			$config .= '|'."\r";
			$config .= '| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-+\';'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Enable Query Strings'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| By default CodeIgniter uses search-engine friendly segment based URLs:'."\r";
			$config .= '| example.com/who/what/where/'."\r";
			$config .= '|'."\r";
			$config .= '| You can optionally enable standard query string based URLs:'."\r";
			$config .= '| example.com?who=me&what=something&where=here'."\r";
			$config .= '|'."\r";
			$config .= '| Options are: TRUE or FALSE (boolean)'."\r";
			$config .= '|'."\r";
			$config .= '| The other items let you set the query string "words" that will'."\r";
			$config .= '| invoke your controllers and its functions:'."\r";
			$config .= '| example.com/index.php?c=controller&m=function'."\r";
			$config .= '|'."\r";
			$config .= '| Please note that some of the helpers won\'t work as expected when'."\r";
			$config .= '| this feature is enabled, since CodeIgniter is designed primarily to'."\r";
			$config .= '| use segment based URLs.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'enable_query_strings\'] = FALSE;'."\r";
			$config .= '$config[\'controller_trigger\'] 	= \'c\';'."\r";
			$config .= '$config[\'function_trigger\'] 	= \'m\';'."\r";
			$config .= '$config[\'directory_trigger\'] 	= \'d\'; // experimental not currently in use'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Error Logging Threshold'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| If you have enabled error logging, you can set an error threshold to '."\r";
			$config .= '| determine what gets logged. Threshold options are:'."\r";
			$config .= '| You can enable error logging by setting a threshold over zero. The'."\r";
			$config .= '| threshold determines what gets logged. Threshold options are:'."\r";
			$config .= '|'."\r";
			$config .= '|	0 = Disables logging, Error logging TURNED OFF'."\r";
			$config .= '|	1 = Error Messages (including PHP errors)'."\r";
			$config .= '|	2 = Debug Messages'."\r";
			$config .= '|	3 = Informational Messages'."\r";
			$config .= '|	4 = All Messages'."\r";
			$config .= '|'."\r";
			$config .= '| For a live site you\'ll usually only enable Errors (1) to be logged otherwise'."\r";
			$config .= '| your log files will fill up very fast.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'log_threshold\'] = 0;'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Error Logging Directory Path'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| Leave this BLANK unless you would like to set something other than the default'."\r";
			$config .= '| system/logs/ folder.  Use a full server path with trailing slash.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'log_path\'] = \'\';'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Date Format for Logs'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| Each item that is logged has an associated date. You can use PHP date'."\r";
			$config .= '| codes to set your own date formatting'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'log_date_format\'] = \'Y-m-d H:i:s\';'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Cache Directory Path'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| Leave this BLANK unless you would like to set something other than the default'."\r";
			$config .= '| system/cache/ folder.  Use a full server path with trailing slash.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'cache_path\'] = '."'';\r\r";
	
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Encryption Key'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| If you use the Encryption class or the Sessions class with encryption'."\r";
			$config .= '| enabled you MUST set an encryption key.  See the user guide for info.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'encryption_key\'] = "";'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Session Variables'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| \'session_cookie_name\' = the name you want for the cookie'."\r";
			$config .= '| \'encrypt_sess_cookie\' = TRUE/FALSE (boolean).  Whether to encrypt the cookie'."\r";
			$config .= '| \'session_expiration\'  = the number of SECONDS you want the session to last.'."\r";
			$config .= '|  by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.'."\r";
			$config .= '| \'time_to_update\'		= how many seconds between CI refreshing Session Information'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'sess_cookie_name\']	= \'ci_session\';'."\r";
			$config .= '$config[\'sess_expiration\']	= 7200;'."\r";
			$config .= '$config[\'sess_encrypt_cookie\']	= FALSE;'."\r";
			$config .= '$config[\'sess_use_database\']	= FALSE;'."\r";
			$config .= '$config[\'sess_table_name\']	= \'ci_sessions\';'."\r";
			$config .= '$config[\'sess_match_ip\']	= FALSE;'."\r";
			$config .= '$config[\'sess_match_useragent\']	= TRUE;'."\r";
			$config .= '$config[\'sess_time_to_update\'] 	= 300;'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Cookie Related Variables'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| \'cookie_prefix\' = Set a prefix if you need to avoid collisions'."\r";
			$config .= '| \'cookie_domain\' = Set to .your-domain.com for site-wide cookies'."\r";
			$config .= '| \'cookie_path\'   =  Typically will be a forward slash'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'cookie_prefix\']	= "";'."\r";
			$config .= '$config[\'cookie_domain\']	= "";'."\r";
			$config .= '$config[\'cookie_path\']		= "/";'."\r\r";
	
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Global XSS Filtering'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| Determines whether the XSS filter is always active when GET, POST or'."\r";
			$config .= '| COOKIE data is encountered'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'global_xss_filtering\'] = TRUE;'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Output Compression'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| Enables Gzip output compression for faster page loads.  When enabled,'."\r";
			$config .= '| the output class will test whether your server supports Gzip.'."\r";
			$config .= '| Even if it does, however, not all browsers support compression'."\r";
			$config .= '| so enable only if you are reasonably sure your visitors can handle it.'."\r";
			$config .= '|'."\r";
			$config .= '| VERY IMPORTANT:  If you are getting a blank page when compression is enabled it'."\r";
			$config .= '| means you are prematurely outputting something to your browser. It could'."\r";
			$config .= '| even be a line of whitespace at the end of one of your scripts.  For'."\r";
			$config .= '| compression to work, nothing can be sent before the output buffer is called'."\r";
			$config .= '| by the output class.  Do not "echo" any values with compression enabled.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'compress_output\'] = FALSE;'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Master Time Reference'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| Options are "local" or "gmt".  This pref tells the system whether to use'."\r";
			$config .= '| your server\'s local time as the master "now" reference, or convert it to'."\r";
			$config .= '| GMT.  See the "date helper" page of the user guide for information'."\r";
			$config .= '| regarding date handling.'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'time_reference\'] = \'local\';'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Rewrite PHP Short Tags'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| If your PHP installation does not have short tag support enabled CI'."\r";
			$config .= '| can rewrite the tags on-the-fly, enabling you to utilize that syntax'."\r";
			$config .= '| in your view files.  Options are TRUE or FALSE (boolean)'."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'rewrite_short_tags\'] = FALSE;'."\r\r";
			
			$config .= '/*'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '| Reverse Proxy IPs'."\r";
			$config .= '|--------------------------------------------------------------------------'."\r";
			$config .= '|'."\r";
			$config .= '| If your server is behind a reverse proxy, you must whitelist the proxy IP'."\r";
			$config .= '| addresses from which CodeIgniter should trust the HTTP_X_FORWARDED_FOR'."\r";
			$config .= '| header in order to properly identify the visitor\'s IP address.'."\r";
			$config .= '| Comma-delimited, e.g. \'10.0.1.200,10.0.1.201\''."\r";
			$config .= '|'."\r";
			$config .= '*/'."\r";
			$config .= '$config[\'proxy_ips\'] = '."'';\r\r";
			
			$config .= "\r";
			$config .= "// End of file config.php \r";
			$config .= "// Location: ./system/application/config/config.php \r";
	
			$this->load->helper('file');
			$config_filename = ROOT_REL.'/system/application/config/config.php';
			
			$written = write_file($config_filename, $config);
		}
		else
		{
			$written = FALSE;
		}
		
		if($written)
		{
			$this->debugging[] = $this->cms_labels['ci_config_success'];
		}
		else
		{
			$this->debugging[] = $this->cms_labels['ci_config_failure'];
		}
	}

}
?>