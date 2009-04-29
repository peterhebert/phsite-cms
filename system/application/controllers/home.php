<?php

class Home extends Controller {
	
	function Home()
	{
		parent::Controller();	
		$this->load->model('Content_model');
		$this->load->model('Object_model');
		$this->load->model('Site_model');
		$this->load->model('User_model');
		$this->Site_model->load_prefs();

		$base_raw = base_url();
		$base_array = explode('/',$base_raw);
		$base_array = removeEmptyArrayElements($base_array);	
		array_shift($base_array);	
		array_shift($base_array);	
		$base = implode('/',$base_array);
		
		if($base != '') {
			$base = '/'.$base;
		}
		$this->data['base_path'] = $base;
		$this->data['theme_path'] = $base.'/system/application/views/themes/'.$this->Site_model->theme.'/';
	}
	
	function index()
	{
		$uri_raw = $this->uri->uri_string();
		$uri = preg_replace('|^/home/index/(.*)$|','$1',$uri_raw);

		$created = $this->Content_model->get_site_creation_date();
		if($created !== FALSE) {
			$this->data['site_meta_created'] = $created;
			$this->data['site_meta_created_W3C'] = substr($created,0,10);
		}

		if($uri == '') {
		
			if($this->Site_model->home_page_id == 0) {
				$view = 'themes/'.$this->Site_model->theme.'/home';
				
				$menu_links = $this->Content_model->get_list(0,'',0);
				
				foreach($menu_links as $link) {
					$zzz_menu[] = anchor($link['uri_title'], $link['title']);
				}
	
				$this->Site_model->load_meta($this->Site_model->default_lang);
				$this->data['site_meta_title'] = $this->Site_model->title;
				$this->data['site_meta_subtitle'] = $this->Site_model->subtitle;
				$this->data['site_meta_description'] = $this->Site_model->description;
				$this->data['site_meta_keywords'] = $this->Site_model->keywords;
				$this->data['site_meta_copyright'] = convert_entities($this->Site_model->copyright);
				$this->data['site_meta_creator'] = $this->Site_model->creator;
				$this->data['site_meta_updated'] = $this->Site_model->updated;
				$this->data['site_meta_updated_W3C'] = substr($this->Site_model->updated,0,10);
				$this->data['site_meta_lang'] = $this->Site_model->site_lang;
				
				$this->data['menu'] = $zzz_menu;
				$this->load->view($view,$this->data);
			} else {
				
				$this->Content_model->content_id = $this->Site_model->home_page_id;
				$this->Content_model->content_lang = $this->Site_model->default_lang;
				$this->Content_model->get_content_object();
				$this->Content_model->get_title();
			
				$uri = $this->Content_model->get_uri();
				$this->process_uri($uri);
			}
		} else {
			$this->process_uri($uri);
		}
	}
	
	function process_uri($uri)
	{
		$errors = FALSE;

		// get page num if exists
		$page = 1;
		$pcount = $this->uri->total_segments();
		$params = $this->uri->uri_to_assoc($pcount-2);
		if(array_key_exists('page',$params)) {
			$page = $params['page'];
		}

		$uri_array = explode('/',$uri);
		
		$temp_parent = 0;
		$temp_uri = '';
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
				$lang = $info->content_lang;

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
			$this->data['breadcrumb'] = implode(' : ', $blinks);
	
			$this->Content_model->content_id = $temp_parent;
			$this->Content_model->get_content();

			$this->data['content_id'] = $this->Content_model->content_id;
			$this->data['parent_id'] = $this->Content_model->parent_id;
			$this->data['object_id'] = $this->Content_model->object_id;
			$this->data['user_id'] = $this->Content_model->user_id;
			$this->data['created'] = $this->Content_model->created;
			$this->data['updated'] = $this->Content_model->updated;
			$this->data['title'] = $this->Content_model->title;

			
			foreach($this->Content_model->content_data as $field) {
				$this->data['content_data'][$field['field_name']] = $field['value'];
				if($field['updated'] > $this->data['updated']) {
					$this->data['updated'] = $field['updated'];
				}
			}

			// get author info
			$this->User_model->user_id = $this->Content_model->user_id;
			$this->User_model->load();
			$this->data['author']['name_first'] = $this->User_model->name_first;
			$this->data['author']['name_last'] = $this->User_model->name_last;
			$this->data['author']['username'] = $this->User_model->username;
			$this->data['author']['email'] = $this->User_model->email;

			// get object info
			$this->Object_model->object_id = $this->Content_model->object_id;
			$this->Object_model->load_object();
			
			// if image, generate image tag and get metadata
			if($this->Object_model->name == 'image')
			{
			}

			// prepare child content for display
			$children = $this->Content_model->get_list(10,'',$this->Content_model->content_id,$page);
			if(count($children) > 0 )
			{
				$this->data['children'] = $this->get_child_data($children);
				
			}
			
			// get site metadata
			$this->Site_model->load_meta($this->Content_model->content_lang);
			$this->data['site_meta_title'] = $this->Site_model->title;
			$this->data['site_meta_subtitle'] = $this->Site_model->subtitle;
			$this->data['site_meta_description'] = $this->Site_model->description;
			$this->data['site_meta_keywords'] = $this->Site_model->keywords;
			$this->data['site_meta_copyright'] = convert_entities($this->Site_model->copyright);
			$this->data['site_meta_creator'] = $this->Site_model->creator;
			$this->data['site_meta_updated'] = $this->Site_model->updated;

			$view = 'themes/'.$this->Site_model->theme.'/'.$this->Object_model->name_uri;

			$this->load->view($view,$this->data);

		} else {
			$this->data['message'] = implode(', ',$msg);

			$this->output->set_status_header('404');
			$this->load->view('error_404',$this->data);
		}

	}

	function get_child_data($child_array)
	{
		foreach($child_array as $child)
		{
			$this->Content_model->content_id = $child['content_id'];
			$this->Content_model->get_content();
			
			$updated = $this->Content_model->updated;

			foreach($this->Content_model->content_data as $field) {
				$content_data[$field['field_name']] = $field['value'];
				if($field['updated'] > $updated) {
					$updated = $field['updated'];
				}
			}
			
			$child_data[] = array(
			'content_id' => $this->Content_model->content_id,
			'object_id' => $this->Content_model->object_id,
			'user_id' => $this->Content_model->user_id,
			'created' => $this->Content_model->created,
			'updated' => $updated,
			'title' => $this->Content_model->title,
			'content_data' => $this->Content_model->content_data,
			);
			unset($content_data);
		}
		return $child_data;
	}

	function view_image($src,$size,$ratio,$attr)
	{

		$this->load->helper('file');
		$this->load->helper('image');

		$image_html = view_image($src,$size,$ratio,$attr);
		echo $image_html;
		$metadata = get_metadata($src);
		echo "<br />\n<pre>";
		print_r ($metadata);
		echo "</pre>";


	}

}
?>