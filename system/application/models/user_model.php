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
	function get_list($limit='')
    {
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
		$query = $this->db->get_where('users', array('field_id' => $this->user_id));
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
	function update_user()
    {

		if($this->username != '' && $this->password != '') {
			$data['username'] = $this->username;
			$data['password'] = $this->password;
		}
		$data['name_first'] = $this->name_first;
		$data['name_last'] = $this->name_last;
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

}

?>