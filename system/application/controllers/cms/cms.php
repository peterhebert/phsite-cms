<?php
class Cms extends Controller {

	function Cms()
	{
		parent::Controller();
		$this->load->helper('url');
		$this->load->helper('html');
		$this->load->helper('form');
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
		$index['new_objects'] = $data2['q'];
		$index['objects'] = $this->load->view('cms/object_list',$data2, true);

		// get recently added pages
		$contentdata['title'] = "Recently Added";
		$content = $this->Content_model->get_list(10,'desc');

		$list = array();
		foreach($content as $row)
		{

			$this->Content_model->getContent($row['content_id']);

			$content_data = $this->Content_model->content_data;

			$mytitle = '';
			foreach($content_data as $field)
			{
				if($field['field_name'] == 'title') {
					$mytitle = $field['value'];
				}

			}

			if($mytitle != '') {
				$list[$row['content_id']] = anchor("cms/content/edit/id/".$row['content_id'], $mytitle, array('title' => "edit '{$mytitle}'"));
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