<?php
class User_model extends MY_Model {

	// users table
	var $user_id, $name_first, $name_last, $username, $password, $timezone, $default_lang, $email;

	// r_sites_users - relate users to sites
	var $user_rel_id, $admin;
	
	// temp password fields
	var $tmp_pwd, $tmp_pwd_conf;

	// misc variables
	var $user_list = array();

    function User_model()
    {
        // Call the Model constructor
        parent::MY_Model();

    }

    // list users for the current site
	function get_list($admin=0)
    {

		$this->db->join('r_sites_users', 's_users.user_id = r_sites_users.user_id', 'left');
		$this->db->where('site_id', $this->site_id);
		if($admin != 0)
		{
			$this->db->where('admin', 1);
		}
		$this->db->order_by('name_last','asc');
		$this->db->order_by('name_first','asc');
		
		$query = $this->db->get('s_users');
		$this->user_list = $query->result_array();
	}

	// get list of users for form drop-down
	function get_list_dropdown($mode=0,$admin=0)
    {
		$this->get_list($admin);
		
		$list = array();
		
		foreach($this->user_list as $row)
		{
			if($mode === 0)
			{
				$key = $row['user_id'];
			} else {
				$key = 'cms/user/edit/id/'.$row['user_id'];
			}
			$list[$key] = $row['name_last'].', '.$row['name_first'];
		}

		return $list;
	}

    // load user
	function load_user()
    {
		$this->db->where('user_id', $this->temp_id);

		$query = $this->db->get('s_users');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();

			$this->user_id = $row->user_id;
			$this->name_first = $row->name_first;
			$this->name_last = $row->name_last;
			$this->username = $row->username;
			$this->password = $row->password;
			$this->timezone = $row->timezone;
			$this->default_lang = $row->default_lang;
			$this->email = $row->email;
		
			$this->db->where('user_id', $this->user_id);
			$this->db->where('site_id', $this->site_id);

			$q2 = $this->db->get('r_sites_users');
			if ($q2->num_rows() > 0)
			{
				$r2 = $q2->row();
		
				$this->user_rel_id = $r2->rel_id;
				$this->admin = $r2->admin;
	
			}
			return TRUE; 		
		}		
		return FALSE; 		

    }

    // update user
	function update_user()
    {

		if($this->user_id == '' || $this->tmp_pwd != '')
		{
			
			if($this->tmp_pwd === $this->tmp_pwd_conf)
			{
				$data['password'] = generateHash($this->tmp_pwd);
			}
			else
			{
				$this->messages[] = 'password and confirm password fields must be identical';
				return FALSE;
			}
		}
		$data['name_first'] = $this->name_first;
		$data['name_last'] = $this->name_last;
		$data['username'] = $this->username;
		$data['timezone'] = $this->timezone;
		$data['default_lang'] = $this->default_lang;
		$data['email'] = $this->email;

		if($this->user_id != '') {
			$this->db->where('user_id', $this->user_id);
			if($this->db->update('s_users', $data)) {
				return TRUE;
			}
		} else {
			if($this->db->insert('s_users', $data)) {
				$this->user_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

    }

	function update_site_user()
	{
		$data = array(
		'site_id' => $this->site_id,
		'user_id' => $this->user_id,
		'admin' => $this->admin
		);

		if($this->user_rel_id != '') {
			$this->db->where('rel_id', $this->user_rel_id);
			if($this->db->update('r_sites_users', $data)) {
				return TRUE;
			}
		} else {
			if($this->db->insert('r_sites_users', $data)) {
				$this->site_rel_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

	}

    function hash_password($str='') {

		// generate new password here if not supplied
		if($str == '') {
			$str = gen_md5_password();
		}
		// hash password for database
		$hpwd = generateHash($str);
		
		return array($hpwd,$str);
	} // end hash_password()

    // reset password
	function reset_password()
    {
		// first find user
		$where = array('username' => $this->username, 'email' => $this->email);
		$query = $this->db->get_where('s_users', $where);
		if($this->db->count_all_results() > 0) {
			$row = $query->row_array();
			$this->user_id = $row['user_id'];
			$pwdArray = $this->hash_password();
			
			$data = array('password' => $pwdArray[0]);
			$this->db->where('user_id', $this->user_id);
			if($this->db->update('s_users', $data)) {
				$this->email_user_info($pwdArray[0],'reset');
				return TRUE;
			}
		}
		
		return FALSE;

    }

    // authenticate user
	function authenticate_user()
    {

		if($this->temp_value != '' && $this->tmp_pwd != '')
		{

			$this->db->join('r_sites_users', 's_users.user_id = r_sites_users.user_id', 'left');
			$this->db->where('s_users.username', $this->temp_value);
			$this->db->where('site_id', $this->site_id);
			$this->db->limit(1);
		
			$query = $this->db->get('s_users');
			
			if ($query->num_rows() > 0)
			{
				$row = $query->row_array();
				
				if($this->checkPassword($this->tmp_pwd,$row['password']))
				{
					$this->user_id = $row['user_id'];
					$this->name_first = $row['name_first'];
					$this->name_last = $row['name_last'];
					$this->username = $row['username'];
					$this->password = $row['password'];
					$this->timezone = $row['timezone'];
					$this->default_lang = $row['default_lang'];
					$this->email = $row['email'];
				
					$this->rel_id = $row['rel_id'];
					$this->admin = $row['admin'];
					return TRUE;
				}
			}

		}

		return FALSE;
 
    }

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

	//	delete user
	function delete_user() {
		$tables = array('r_sites_users','users','users_meta');
		$this->db->where('user_id', $this->user_id);
		if($this->db->delete('r_sites_users'))
		{
			return true;
		} else {
			return false;
		}
	}

	//	remove user from site
	function remove_site_user() {

		$this->db->where('user_id', $this->user_id);
		$this->db->where('site_id', $this->site_id);

		if($this->site_rel_id != '') {
			$this->db->where('rel_id', $this->site_rel_id);
		}

		if($this->db->delete('r_sites_users'))
		{
			return true;
		} else {
			return false;
		}
	}

    function list_user_meta_fields()
    {

		$this->list_fields();
		$list = $this->field_list;
		$this->scope = 'users';
		
		$field_info = array();
		
		foreach ($list as $fd)
		{
			$temp_field = array(
			'field_id' => $fd['field_id'],
			'field_ref_name' => $fd['field_ref_name'],
			'field_input_type' => $fd['field_input_type'],
			'field_validation' => $fd['field_validation'],
			'field_needs_lang' => $fd['field_needs_lang'],
			'rf_id' => '',
			'required' => '',
			'order' => ''
			);
	
			$this->db->where('scope',$this->scope);
			$this->db->where('field_id',$fd['field_id']);
			$query = $this->db->get('r_fields');

			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$temp_field['rf_id'] = $row->rf_id;
				$temp_field['required'] = $row->required;
				$temp_field['order'] = $row->order;
			}
			$field_info[] = $temp_field;
			 
		}

		$this->field_list = $field_info;

	}


    function load_user_metadata()
    {

		$this->scope = 'users';
		$this->temp_id == '';
		$this->list_fields_related();

		$this->temp_id = $this->user_id;
		$this->load_metadata();

	}

}

?>