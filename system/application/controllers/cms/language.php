<?php
class Language extends Controller {

   function Language()
   {
		parent::Controller();

		$this->load->model('Language_model');
	}

	function index()
	{
		$this->load->helper('form');
		$this->load->helper('test');
		$data['languages'] = $this->Language_model->get_list();
		
		$this->load->library('table');
		$this->load->view('cms/lang_list',$data);
	}




	function edit()
	{
		$this->load->helper('form');

		if($this->input->post('submit')) {
			$enabled_langs = explode(',',$this->input->post('enabled_langs'));
			$sel_lang = $this->input->post('lang');
			foreach($enabled_langs as $lang) {
				if(in_array($lang,$sel_lang)) {
					$activated = 1;
				} else {
					$activated = 0;
				}
				$this->Language_model->ISO_639_3 = $lang;
				$this->Language_model->set_active($activated);
			}
		}

		$data['enabled'] = $this->Language_model->get_list();
		
		$this->load->view('cms/lang_edit',$data);
	}

}
?>