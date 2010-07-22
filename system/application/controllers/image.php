<?php

class Image extends MY_Controller {

	function Image()
	{
		parent::MY_Controller();	
		$this->load->model('File_model');
		$this->load->helper('image');
	}
	
	function index()
	{

		$list = $this->File_model->get_new_files(true);
/*		echo "<p>files</p>\r";
		echo "<pre>";
		print_r($this->File_model->file_list);
		echo "</pre>\r";

		echo "<p>directories</p>\r";
		echo "<pre>";
		print_r($this->File_model->dir_list);
		echo "</pre>\r";
*/
		echo "<p>new files</p>\r";
		echo "<pre>";
		print_r($this->File_model->new_file_list);
		echo "</pre>\r";
	}

	function browser()
	{

		$this->_load_cms_labels();

		$this->data['filetree_root'] = BASE_PATH.MEDIA_PATH_ORIG;

		$this->load->view('filetree_browser',$this->data);


	}

	function embed()
	{
		$file = urldecode($this->input->post('file'));
		$fullpath = ROOT_REL.$file;
		
		if(file_exists($fullpath))
		{
			$this->File_model->file_current = $fullpath;
			$this->File_model->read_file_info();
			
			if($this->File_model->title != '')
			{
				$title = $this->File_model->title;
			}
			else
			{
				$title = basename($fullpath);
			}
			$filesize = resize_bytes(filesize($fullpath));
	
			$embed = "<a href=\"{$file}\" title=\"{$title}\">{$title}</a> ($filesize)";
			
			if($this->File_model->mime_cat == 'image')
			{
				$size = $this->input->post('size');
				
				$embed = view_image($fullpath,$size,array('id' => 'media-'.$this->File_model->media_id ));
			}
		}
		else
		{
			$embed = '';
		}
		

		echo $embed;

	}

	function ajax_preview ()
	{

		$file = urldecode($this->input->post('file'));
		$fullpath = ROOT_REL.$file;
		
		$embed = '';
		if(file_exists($fullpath))
		{
			$this->File_model->file_current = $fullpath;
			$this->File_model->file_uri = preg_replace("#^".MEDIA_PATH_ORIG."(.*)$#","$1",$file);

			$this->File_model->read_file_info();
			$this->File_model->update_file();
			
			if($this->File_model->mime_cat == 'image')
			{
				$size = $this->Site_model->img_size_th;
				
				$embed .= '<div class="image-left">';
				$embed .= view_image($fullpath,$size,array('id' => 'media-'.$this->File_model->media_id ));
				$embed .= '</div>';
			}
			if($this->File_model->title != '')
			{
				$embed .= '<p><strong>'.$this->File_model->title."</strong></p>\r";
			}


		}

		echo $embed;

	}

}
?>
