<?php
class User_model extends MY_Model {

	// users table
	var $user_id, $name_first, $name_last, $username, $password, $timezone, $default_lang, $email;

	// r_sites_users - relate users to sites
	var $site_rel_id, $site_id, $admin;
	
	// r_users_fields - create custom profile fields for users 
	//var $field_rel_id, $field_id, $required, $order;
	
	// users_meta table - other custom profile fields
	var $meta_lang, $date_updated;
	
	// temp password fields
	var $tmp_pwd, $tmp_pwd_conf;

	// misc variables
	var $metadata = array(), $user_list = array(), $field_list = array();

    function User_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->config->load('phsite_prefs');
		$this->site_id = $this->config->item('site_id');
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

    // list custom fields for user profiles
	function list_user_fields()
    {
		$sql = "SELECT * FROM (fields LEFT JOIN r_users_fields ON fields.field_id = r_users_fields.field_id) 
		LEFT JOIN fields_meta ON fields. field_id = fields_meta.field_id 
		WHERE r_users_fields.site_id = ".$this->site_id;
		$query = $this->db->query($sql);
		$this->field_list = $query->result_array();
	}

    // list all available fields and see if they are assigned as user metadata
	function list_fields()
    {

		$list = array();
		$this->db->order_by('field_ref_name', 'asc');
		$query = $this->db->get('fields');

		foreach ($query->result() as $row)
		{

			$temp_field =  array(
			'field_id' => $row->field_id,
			'field_ref_name' => $row->field_ref_name,
			'field_input_type' => $row->field_input_type,
			'field_validation' => $row->field_validation,
			'field_needs_lang' => $row->field_needs_lang,
			'field_name' => '',
			'rel_id' => '',
			'required' => '',
			'order' => '',
			);

			// get name if exists
			$this->db->where('field_id', $row->field_id);
			if($this->meta_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->meta_lang);
			}
			$this->db->limit(1);
			$query2 = $this->db->get('fields_meta');
		   
			if ($query2->num_rows() > 0)
			{
				$row2 = $query2->row();
				$temp_field['field_name'] = $row2->field_name;
			}

			// get data if in this content type
			$this->db->where('site_id', $this->site_id);
			$this->db->where('field_id', $row->field_id);
			$this->db->limit(1);
			$query3 = $this->db->get('r_users_fields');
		   
			if ($query3->num_rows() > 0)
			{
				$row3 = $query3->row();
				$temp_field['rel_id'] = $row3->rel_id;
				$temp_field['required'] = $row3->required;
				$temp_field['order'] = $row3->order;
			}

		   $list[] = $temp_field;

		}

		$this->field_list = $list;

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
			$temp = array(
			'field_id' => $fd['field_id'],
			'field_ref_name' => $fd['field_ref_name'],
			'field_name' => $fd['field_name'],
			'field_input_type' => $fd['field_input_type'],
			'field_validation' => $fd['field_validation'],
			'field_needs_lang' => $fd['field_needs_lang'],
			'required' => $fd['required'],
			'order' => $fd['order']
			);

			$this->db->where('users_meta.field_id', $fd['field_id']);
			$this->db->where('user_id', $this->user_id);

			if($fd['field_needs_lang'] == 1 && $this->meta_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->meta_lang);
			}
			$this->db->limit(1);
			$query = $this->db->get('users_meta');

			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$temp['user_meta_id'] = $row->user_meta_id;
				$temp['value'] = $row->value;
			} else {
				$temp['user_meta_id'] = '';
				$temp['value'] = '';
			}

			$meta["{$fd['field_id']}"] = $temp;
		}

		$this->metadata = $meta;
		
    }

    // update user
	function update_user()
    {

		if($this->user_id == '' || $this->tmp_pwd != '')
		{
			
			if($this->tmp_pwd === $this->tmp_pwd_conf)
			{
				$tmpwd = $this->hash_password($this->tmp_pwd);
				$data['password'] = $tmpwd[0];
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

	function update_meta()
	{
		foreach($this->metadata as $fd_id => $row)
		{
			$data = array(
			'user_id' => $this->user_id,
			'field_id' => $row['field_id'],
			'field_name' => $row['field_name'],
			'value' => $row['value'],
			'lang_ISO_639_3' => $row['lang_ISO_639_3'],
			);
			
			if($row['required'] == 0 && $row['value'] == '' && $row['user_meta_id'] != '')
			{
				// not required, delete meta
				$this->db->where('user_meta_id',$row['user_meta_id']);
				if(!$this->db->delete('users_meta'))
				{
					$this->messages[] = "field '{$row['field_name']}' could not be deleted";
				}
			}
			else
			{
				if($row['user_meta_id'] != '')
				{
					// update
					$this->db->where('user_meta_id',$row['user_meta_id']);
					if(!$this->db->update('users_meta', $data))
					{
						$this->messages[] = "field '{$row['field_name']}' could not be updated";
					}
				}
				else
				{
					// insert
					if(!$this->db->insert('users_meta', $data))
					{
						$this->messages[] = "field '{$row['field_name']}' could not be inserted";
					}
				}
			}
		}
		
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

			$this->db->join('r_sites_users', 's_users.user_id = r_sites_users.user_id', 'left');
			$this->db->where('s_users.user_id', $this->user_id);
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

    function update_meta_fields()
    {
		
		foreach($this->field_list as $field)
		{

			$db_fields = array(
			'site_id' => $this->site_id,
			'field_id' => $field['field_id'],
			'required' => $field['required'],
			'order' => $field['order']
			);
			
			if($field['exists'] === TRUE)
			{
				// update field

				if($field['rel_id'] == '')
				{
					// create new metadata
					if(!$this->db->insert('r_users_fields', $db_fields) )
					{
						$this->messages[] = "could not create relationship for field {$field['field_id']}";
					}
		
				}
				else
				{
					// update metadata field
					if(!$this->db->update('r_users_fields', $db_fields, array('rel_id' => $field['rel_id'] )) ) {
						$this->messages[] = "could not update relationship for field {$field['field_id']}";
					}
				}

			}
			else
			{
				if($field['rel_id'] != '')
				{
					// remove relationship
					$this->db->where('rel_id', $field['rel_id']);
					if(!$this->db->delete('r_users_fields')) {
						$this->messages[] = "could not remove relationship for field {$field['field_id']}";
					}
				}

			}

		} // end foreach()

		if(count($this->messages) == 0)
		{
			$this->messages[] = "user metadata field info successfully updated";
		}

    }


}

?>