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

			if(isset($db,$active_group) && array_key_exists($active_group,$db))
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
					$this->messages[] = "attempt writing to database config file";
					if($this->_write_db_config())
					{
						$this->installer_step = 2;
						$this->messages[] = "database config file successfully created";
					}
				}

			}
			else
			{
				$this->messages[] = validation_errors();
			}
		}

		if($this->input->post('submit-prefs'))
		{
			$this->messages[] = "site preferences form submitted";
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
			if($this->input->post('confirm-install') === 1)
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
			$this->form_validation->set_rules('username', $this->cms_labels['username'], 'required');
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
						$this->messages[] = "admin user created";
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
		$action = 'cms/install';
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

		$action = 'cms/install';
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

		$action = 'cms/install';
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

		$action = 'cms/install';
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
				//$sql = ;
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

}
?>