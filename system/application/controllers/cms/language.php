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

		if($this->input->post('submit')) {
			$enabled_langs = explode(',',$this->input->post('enabled_langs'));
			$sel_lang = $this->input->post('lang');

			$new_lang = $this->input->post('new_lang');

			if(is_array($sel_lang)) {
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

			if(is_array($new_lang)) {
				foreach($new_lang as $lang) {
					$this->Language_model->ISO_639_3 = $lang;
					$this->Language_model->set_active(1);
				}
			}

		}
		if($this->input->post('search_submit')) {
			$data['matches_label'] = "Matches for '".$this->input->post('find')."'";
			$data['matches'] = $this->Language_model->find_matches($this->input->post('find'));
		}

		$data['enabled'] = $this->Language_model->get_list();
		
		$this->load->view('cms/lang_edit',$data);
	}

}
?>