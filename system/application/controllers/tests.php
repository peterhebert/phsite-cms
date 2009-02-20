<?php

class Tests extends Controller {

	function Tests()
	{
		parent::Controller();	
		$this->load->helper('file');
		$this->load->helper('url');
		$this->load->helper('test');
	}
	
	function index()
	{
			$data['result'] = "add parameters to URI to test";

		$this->load->view('test',$data);

	}
	function lang()
	{
		$lang = $this->uri->segment(3);
		
		if($lang != '') {
			$data['result'] = "'$lang' is ";
			if(is_lang($lang) == false) {
				$data['result'] .= "NOT ";
			} 
			$data['result'] .= "a language";
		} else {
			$data['result'] = "add parameters to URI to test";
		}

		$this->load->view('test',$data);

	}

	function date()
	{
		$dates = array(
		'Thursday, February 12, 2009',
		'20 Jun 1972',
		'Oct 2, 2009',
		'29/01/72',
		'1972\1\01 1:42 pm',
		'1478\1\01 1:42 a.m.',
		'2009-01-29 16:44:11',
		'2009-01-29',
		);
		$data['result'] = '';
		foreach($dates as $theDate) {
			$data['result'] .= "<p>'$theDate' ";
			if($newDate = convert_date($theDate)) {
				$data['result'] .= "is a valid date";
				$data['result'] .= " - converts to : ".$newDate;
			
			} else {
				$data['result'] .= " IS NOT a valid date";
			} 
			$data['result'] .= "</p>\n";
		}

		$this->load->view('test',$data);

	}

	function is_path ()
	{
		$pattern = '^([A-Z]:){0,1}[A-Za-z0-9\\\-_\./ ]+$';

		$values = array(
		'C:\system files\file\path',
		'\\\\folder\\path\\system',
		'/test/this/path',
		'./system/path/file.ext',
		'C:\system files\file\path.extension',
		);

		$data['result'] = '';


		foreach($values as $value) {
			if(ereg($pattern, $value) == true)
			{
				$data['result'] .= "<p>'$value' is a valid path</p>";
			} else {
				$data['result'] .= "<p>'$value' is NOT a valid path</p>";
			}
		}

		$this->load->view('test',$data);

	}

	function parseme ()
	{
		$raw = 'john|doe|bob|exact_length[2]|max_length[7]';
		
		$data['main'] = '';
		$data['exact'] = '';
		$data['min'] = '';
		$data['max'] = '';

		$string_rules = array(
		'alpha',
		'alpha_numeric',
		'alpha_dash',
		'alpha_num_spaces',
		'is_date',
		'valid_email',
		'integer',
		'numeric',
		'is_natural',
		'is_natural_no_zero',
		'file_path',
		'valid_ip'
		);
		
		$valid = explode('|',$raw);
		if ( is_array($valid) && count($valid > 0) ) {
			foreach($valid as $rule) {
				if(in_array($rule,$string_rules,true)){
					$data['main'] = $rule;
				}
				
			}
		}
		
		if(ereg ( 'exact_length\[([0-9]+)\]', $raw)) {
			$pattern = '^(.*)exact_length\[([0-9]+)\](.*)$';
			$data['exact'] = ereg_replace($pattern,'\\2',$raw);
		}

		if(ereg ( 'min_length\[([0-9]+)\]', $raw)) {
			$pattern = '^(.*)min_length\[([0-9]+)\](.*)$';
			$data['min'] = ereg_replace($pattern,'\\2',$raw);
		}

		if(ereg ( 'max_length\[([0-9]+)\]', $raw)) {
			$pattern = '^(.*)max_length\[([0-9]+)\](.*)$';
			$data['max'] = ereg_replace($pattern,'\\2',$raw);
		}

		$this->load->view('test',$data);

	}


}
?>