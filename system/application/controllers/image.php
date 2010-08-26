<?php

class Image extends MY_Controller {

	var $types = array();

	function Image()
	{
		parent::MY_Controller();	
		$this->load->model('File_model');
		$this->load->helper('image');
		$this->load->config('mime_info');

		$this->types = $this->config->item('playable_types');
	}
	
	function index()
	{
		echo "image controller";
	}

	function root()
	{
		echo BASE_PATH.MEDIA_PATH_ORIG;
	}
	function browser()
	{
		$this->_load_cms_labels();
		
		$this->data['filetree_root'] = BASE_PATH.MEDIA_PATH_ORIG;

		$this->load->view('filetree_browser',$this->data);
	}

	function embed()
	{
		//$file = urldecode($this->input->post('file'));
		$file = '/media/originals/RV8_28.ram';
		
		$fullpath = ROOT_REL.$file;
		$code = '';
		
		if(file_exists($fullpath))
		{
			$this->File_model->file_current = $fullpath;
			$this->File_model->read_file_info();
			
			$this->File_model->file_uri = preg_replace("#^".MEDIA_PATH_ORIG."(.*)$#","$1",$file);
			
			$this->File_model->find_media_by_uri();
			
			if($this->File_model->title != '')
			{
				$title = $this->File_model->title;
			}
			else
			{
				$title = basename($fullpath);
			}
			$filesize = resize_bytes(filesize($fullpath));
	
			$params = array(
			'id' => 'media-'.$this->File_model->media_id, 
			'rel' => $file, 
			);

			if($this->File_model->title != '')
			{
				$params['alt'] = $this->File_model->title;
			}

			if($this->File_model->mime_cat == 'image')
			{
				$size = $this->input->post('size');
				$code = view_image($fullpath,$size,$params);
			}
			else if(in_array($this->File_model->mime_type,$this->types))
			{
				$params['src'] = $file;
				$params[] = 'autostart';
				$code = media_player_object($params);
			}

			else
			{
				$code = "<a href=\"{$file}\" title=\"{$title}\">{$title}</a> ($filesize)";
			}			


		}

		echo $code;

	}

	function ajax_preview ()
	{
		$this->config->load('mime_info');
		$types = $this->config->item('playable_types');

		$file = urldecode($this->input->post('file'));

		$fullpath = ROOT_REL.$file;
		
		$code = '';
		if(file_exists($fullpath))
		{
			$this->File_model->file_current = $fullpath;
			$this->File_model->file_uri = preg_replace("#^".MEDIA_PATH_ORIG."(.*)$#","$1",$file);
			$this->File_model->external = 0;
			$this->File_model->publish = 1;

			$this->File_model->read_file_info();
			$this->File_model->update_file();
			
			if($this->File_model->mime_cat == 'image')
			{
				$size = $this->Site_model->img_size_th;
				
				$code .= '<div class="image-left">';
				$code .= view_image($fullpath,$size,array('id' => 'media-'.$this->File_model->media_id ));
				$code .= '</div>';
			}
			if($this->File_model->title != '')
			{
				$code .= '<p><strong>'.$this->File_model->title."</strong></p>\r";
			}


		} else {
				$code .= '<p><strong>could not find file : '.$file."</strong></p>\r";
		}

		echo $code;

	}

}
?>
