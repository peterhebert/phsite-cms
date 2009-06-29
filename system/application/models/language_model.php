<?php
class Language_model extends MY_Model {

	var $ISO_639_3;
	var $ISO_639_2B;
	var $ISO_639_2T;
	var $ISO_639_1;
	var $Scope;
	var $Type;
	var $Ref_Name;
	var $Inverted_Name;
	var $Native_Name;
	var $activated;

    function Language_model()
    {
        // Call the Model constructor
        parent::MY_Model();
    }

    // get list of languages
	function get_list($activeOnly = TRUE,$limit=0)
    {
		if($activeOnly == TRUE) {
			$this->db->where('activated', 1);
		}
		$this->db->order_by("Inverted_Name", "asc");
		if($limit > 0) {
			$query = $this->db->get('ISO_639_LANG', $limit);
		} else {
			$query = $this->db->get('ISO_639_LANG');
		}

		return $query->result_array();
	}

    // get list of active languages for form drop-down
	function get_list_dropdown()
    {
		$active = $this->get_list();
		$list = array();
		
		foreach($active as $row)
		{
			if($row['Native_Name'] != '')
			{
				$list[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$list[$row['ISO_639_3']] = $row['Ref_Name'];
			}
		}

		return $list;
	}

    // find matching languages
	function find_matches($str)
    {
		$match = $this->input->xss_clean($str);
		
		$sql = "SELECT * FROM `ISO_639_LANG` WHERE (
		`Ref_Name` LIKE '%{$match}%' OR
		`Inverted_Name` LIKE '%{$match}%' OR
		`Native_Name` LIKE '%{$match}%' 
		) AND `activated` = 0";

		$query = $this->db->query($sql);
		return $query->result_array();
	}

    // load the language based upon 639-3 key
	function load($key)
    {
		$query = $this->db->get_where('ISO_639_LANG', array('ISO_639_3' => $key));
 
 		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->ISO_639_3 = $row['ISO_639_3'];
			$this->ISO_639_2B = $row['ISO_639_2B'];
			$this->ISO_639_2T = $row['ISO_639_2T'];
			$this->ISO_639_1 = $row['ISO_639_1'];
			$this->Scope = $row['Scope'];
			$this->Type = $row['Type'];
			$this->Ref_Name = $row['Ref_Name'];
			$this->Inverted_Name = $row['Inverted_Name'];
			$this->Native_Name = $row['Native_Name'];
			$this->activated = $row['activated'];
			return TRUE;
		} else {
			$this->ISO_639_3 = '';
			$this->ISO_639_2B = '';
			$this->ISO_639_2T = '';
			$this->ISO_639_1 = '';
			$this->Scope = '';
			$this->Type = '';
			$this->Ref_Name = '';
			$this->Inverted_Name = '';
			$this->Native_Name = '';
			$this->activated = '';
			return FALSE;
		}

    }

	function set_active($val)
    {

		$data = array(
		'activated' => $val,
		);
		
		$this->db->where('ISO_639_3', $this->ISO_639_3);
		if($this->db->update('ISO_639_LANG', $data)) {
			$this->activated = $val;
		} 
    }

}

?>
