<?php
class User_model extends MY_Model {

	// users table
	var $user_id, $name_first, $name_last, $username, $password, $timezone, $default_lang, $email;

	// r_sites_users - relate users to sites
	var $site_rel_id, $site_id, $admin;
	
	// r_sites_fields - create custom profile fields for users 
	var $field_rel_id, $field_id, $required, $order;
	
	// users_meta table - other custom profile fields
	var $user_meta_id, $value, $meta_lang, $date_updated;
	
	// misc variables
	var $metadata, $user_list, $field_list;

    function User_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->config->load('phsite_prefs');
		$this->site_id = $this->config->item('site_id');
    }

    // list users for the current site
	function get_list()
    {

		$this->db->join('r_sites_users', 'users.user_id = r_sites_users.user_id', 'left');
		$this->db->where('site_id', $this->site_id);

		$this->db->order_by('name_last','asc');
		$this->db->order_by('name_first','asc');
		
		$query = $this->db->get('users');
		$this->user_list = $query->result_array();
	}

    // list custom fields for user profiles
	function list_user_fields()
    {
		$sql = "SELECT * FROM fields LEFT JOIN 
		(fields_meta LEFT JOIN r_sites_fields ON fields_meta.field_id = r_sites_fields. field_id) 
		ON fields. field_id = fields_meta.field_id 
		WHERE r_sites_fields.site_id = ".$this->site_id;
		$query = $this->db->query($sql);
		$this->field_list = $query->result_array();
	}

   // get list of users for form drop-down
	function get_list_dropdown($mode=0)
    {
		$this->get_list();
		
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
	function load()
    {

		$this->db->where('user_id', $this->user_id);

		$query = $this->db->get('users');
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
	
			$this->name_first = $row->name_first;
			$this->name_last = $row->name_last;
			$this->username = $row->username;
			$this->password = $row->password;
			$this->timezone = $row->timezone;
			$this->default_lang = $row->default_lang;
			$this->email = $row->email;
		
		} 		

		$this->db->where('user_id', $this->user_id);
		$this->db->where('site_id', $this->site_id);

		$query = $this->db->get('r_sites_users');
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
	
			$this->site_rel_id = $row->rel_id;
			$this->admin = $row->admin;
		
		} 		

    }

	function load_meta()
    {

		$this->list_user_fields();
		$meta = array();
		
		foreach($this->field_list as $fd)
		{
			$temp = $fd;

			$this->db->join('fields', 'users_meta.field_id = fields.field_id', 'left');
			$this->db->where('users_meta.field_id', $fd['field_id']);
			$this->db->where('user_id', $this->user_id);
			if($this->meta_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->meta_lang);
			}
			$this->db->limit(1);
			$query = $this->db->get('users_meta');
	
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$temp['user_meta_id'] = $row->user_meta_id;
				$temp['user_id'] = $row->user_id;
				$temp['value'] = $row->value;
				$temp['meta_lang'] = $row->lang_ISO_639_3;

			} else {
				$temp['user_meta_id'] = '';
				$temp['user_id'] = '';
				$temp['value'] = '';
				$temp['meta_lang'] = '';
			}

			$meta[$fd['field_id']] = $temp;
		}

		
		$this->metadata = $meta;
    }

    // update user
	function update_user($newpwd='')
    {

		if($this->user_id == '' || $newpwd != '') {
			$tmpwd = $this->hash_password($newpwd);
			$data['password'] = $tmpwd[0];
		}
		$data['name_first'] = $this->name_first;
		$data['name_last'] = $this->name_last;
		$data['username'] = $this->username;
		$data['timezone'] = $this->timezone;
		$data['default_lang'] = $this->default_lang;
		$data['email'] = $this->email;

		if($this->user_id != '') {
			$this->db->where('user_id', $this->user_id);
			if($this->db->update('users', $data)) {
				return TRUE;
			}
		} else {
			if($this->db->insert('users', $data)) {
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
		'admin' => $this->admin
		);

		if($this->site_rel_id != '') {
			$this->db->where('rel_id', $this->site_rel_id);
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

	function update_user_field()
	{
		$data = array(
		'site_id' => $this->site_id,
		'field_id' => $this->field_id,
		'required' => $this->required,
		'order' => $this->order
		);

		if($this->field_rel_id != '') {
			$this->db->where('rel_id', $this->field_rel_id);
			if($this->db->update('r_sites_fields', $data)) {
				return TRUE;
			}
		} else {
			if($this->db->insert('r_sites_fields', $data)) {
				$this->field_rel_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

	}

	function update_meta()
	{

		$data = array(
		'user_id' => $this->user_id,
		'field_id' => $this->field_id,
		'value' => $this->value,
		'lang_ISO_639_3' => $this->meta_lang
		);

		if($this->user_meta_id != '') {
			$this->db->where('user_meta_id', $this->user_meta_id);
			if($this->db->update('users_meta', $data)) {
				return TRUE;
			}
		} else {
			if($this->db->insert('users_meta', $data)) {
				$this->user_meta_id = $this->db->insert_id();
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
		$query = $this->db->get_where('users', $where);
		if($this->db->count_all_results() > 0) {
			$row = $query->row_array();
			$this->user_id = $row['user_id'];
			$pwdArray = $this->hash_password();
			
			$data = array('password' => $pwdArray[0]);
			$this->db->where('user_id', $this->user_id);
			if($this->db->update('users', $data)) {
				$this->email_user_info($pwdArray[0],'reset');
				return TRUE;
			}
		}
		
		return FALSE;

    }

    // authenticate user
	function authenticate_user($usr = NULL,$pwd = NULL)
    {
		if(isset($usr) && isset($pwd))
		{

			$this->db->join('r_sites_users', 'users.user_id = r_sites_users.user_id', 'left');
			$this->db->where('user_id', $this->user_id);
			$this->db->where('site_id', $this->site_id);
			$this->db->limit(1);
		
			$query = $this->db->get('users');
			
			if ($query->num_rows() > 0)
			{
				$row = $query->row_array();
				
				if($this->checkPassword($pwd,$row['password']))
				{
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

	//	remove custom field from site
	function remove_user_field()
	{

		$this->db->where('field_id', $this->field_id);
		$this->db->where('site_id', $this->site_id);

		if($this->field_rel_id != '') {
			$this->db->where('rel_id', $this->field_rel_id);
		}

		if($this->db->delete('r_sites_fields'))
		{
			return true;
		} else {
			return false;
		}

	}


}

?>