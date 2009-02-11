<?php
class Cms extends Controller {

	function Cms()
	{
		parent::Controller();
		$this->load->helper('url');
		$this->load->helper('html');
		// load objects
		$this->load->model('Field_model');
		$this->load->model('Object_model');
		$this->load->model('Content_model');
	}
	
	function index()
	{
		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data2['q'] = $this->Object_model->get_list();
		$index['objects'] = $this->load->view('cms/object_list',$data2, true);

		// get recently added pages
		$contentdata['title'] = "Recently Added";
		$content = $this->Content_model->get_list(10,'desc');

		foreach($content as $row)
		{

			$this->Content_model->getContent($row['content_id']);

			$details = array();
			$content_data = $this->Content_model->content_data;
			foreach($content_data as $field)
			{
				$details[$field['field_name']] = $field['value'];
			}

			if(array_key_exists('title', $details)) {
				$list[$row['content_id']] = anchor("cms/content/edit/id/".$row['content_id'], $details['title'], array('title' => "edit '{$details['title']}'"));
			} else {
				$list[$row['content_id']] = anchor("cms/content/edit/id/".$row['content_id'], 'ID '.$row['content_id'], array('title' => "edit ID ".$row['content_id']));
			}

		}

		$contentdata['list'] = ul($list);

		$index['content'] = $this->load->view('cms/content_list',$contentdata, true);

	
		// now load view
		$this->load->view('cms/index',$index);
	}
	

}
?>