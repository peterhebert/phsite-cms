<?php
class User_model extends Model {

	var $user_id;
	var $name_first;
	var $name_last;
	var $username;
	var $password;
	var $timezone;
	var $admin;
	var $lang;
	var $email;
	
    function User_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // list users
	function get_list($admin = 0,$limit='')
    {
		if($admin === 1) {
			$this->db->where('admin', 1);
		}
		if($limit != '' && intval($limit) > 0) {
			$this->db->limit(intval($limit));
		}

		$this->db->order_by('name_last','asc');
		$this->db->order_by('name_first','asc');
		
		$query = $this->db->get('users');
		return $query->result_array();
	}

    // load user
	function load()
    {
		$query = $this->db->get_where('users', array('user_id' => $this->user_id));
		$row = $query->row_array();
		$this->name_first = $row['name_first'];
		$this->name_last = $row['name_last'];
		$this->username = $row['username'];
		$this->password = $row['password'];
		$this->timezone = $row['timezone'];
		$this->admin = $row['admin'];
		$this->lang = $row['lang'];
		$this->email = $row['email'];
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
		$data['admin'] = $this->admin;
		$data['lang'] = $this->lang;
		$data['email'] = $this->email;

		if($this->user_id != '') {
			$this->db->where('user_id', $this->user_id);
			if($this->db->update('users', $data)) {
				return TRUE;
			}
		} else {
			if($this->db->insert('users', $data)) {
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
	function authenticate($usr = NULL,$pwd = NULL)
    {
		if(!isset($usr) || !isset($pwd))
		{
			return FALSE;
		}
		$query = $this->db->get_where('users', array('username' => $usr));
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			
			if($this->checkPassword($pwd,$row['password']))
			{
				$this->user_id = $row['user_id'];
				$this->name_first = $row['name_first'];
				$this->name_last = $row['name_last'];
				$this->username = $row['username'];
				$this->password = $row['password'];
				$this->timezone = $row['timezone'];
				$this->admin = $row['admin'];
				$this->lang = $row['lang'];
				$this->email = $row['email'];
				return TRUE;
			}
		}
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


}

?>