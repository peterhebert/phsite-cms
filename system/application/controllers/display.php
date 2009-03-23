<?php

class Display extends Controller {

	function Display()
	{
		parent::Controller();	
		$this->load->model('Content_model');
		$this->load->model('Object_model');
		$this->load->model('Site_model');
		$this->Site_model->load_prefs();
	}
	
	function index()
	{
		$errors = FALSE;
		$uri_array = $this->uri->segment_array();
		$q = array_shift($uri_array);
		$q = array_shift($uri_array);
		
		$temp_parent = 0;
		$temp_uri = 'display/index';
		$pathcount = count($uri_array);
		$current_path = 1;
		$blinks = array(anchor($temp_uri,'Home'));
		$lang = '';

		foreach ($uri_array as $path) {
			$path_clean = $this->input->xss_clean($path);
			if($lang != '') {
				$info = $this->Content_model->find_by_uri_parent($path_clean,$temp_parent,$lang);
			} else {
				$info = $this->Content_model->find_by_uri_parent($path_clean,$temp_parent);
			}
			if($info) {
				$temp_uri .= '/'.$path;
				$temp_parent = $info->content_id;
				$lang = $info->lang;

				if ($current_path < $pathcount) {
					$blinks[] = anchor($temp_uri, $info->title);
				} else {
					$blinks[] = $info->title;
				}
				$current_path = $current_path + 1;
			} else {
				$errors = TRUE;
				$msg[] = $path;
			}


		}

		if(!$errors) {
			$data['breadcrumb'] = implode(' : ', $blinks);
	
			$this->Content_model->content_id = $temp_parent;
			$this->Content_model->get_content();
	
			$data['created'] = $this->Content_model->created;
			$data['updated'] = $this->Content_model->updated;
			$data['title'] = $this->Content_model->title;
			$data['content_data'] = $this->Content_model->content_data;

			$this->Object_model->object_id = $this->Content_model->object_id;
			$this->Object_model->load_object();

			$view = 'themes/'.$this->Site_model->theme.'/'.$this->Object_model->name;

			$this->load->view('display',$data);
		} else {
			$data['message'] = implode(', ',$msg);

			$this->output->set_status_header('404');
			$this->load->view('error_404',$data);
		}

	}


}
?>
