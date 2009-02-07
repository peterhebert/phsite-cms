<h2><?php echo $title; ?></h2>
<ul>
<?php 

		if(count($list) > 0) {
			$links = array();
		} else {
			$links[] = 'No content found';
		}

		foreach ($list as $content) {
			$this->Content_model->load($content['content_id']);
	
			if($langContent = $this->Content_model->getLang($lang)) {
			
				$uri = "cms/content/edit/".$content['content_id'];
				if(array_key_exists  ('title', $langContent)) {
					$text = $langContent['title'];
				} else {
					$text = 'ID '.$content['field_id'];
				}
				$attr = array('title' => "edit '{$text}'");
			
				$links[] = anchor($uri,$text,$attr);
			}

		}
		
		echo ul($links);

?>