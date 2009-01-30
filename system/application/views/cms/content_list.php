<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Content</title>
</head>

<body>
	<h1>Content</h1>
	
	<p>Here is a list of all content in the database. Click a link to edit the content.</p>

<ul>
<?php 
	foreach ($q as $content) {
		$this->Content_model->load($content['content_id']);
			//print_r($this->Content_model->content_data);

		if($langContent = $this->Content_model->getLang('eng')) {
			?>
				<li><?php
		$uri = site_url("cms/content/edit/".$content['content_id']);
		if(array_key_exists  ('title', $langContent)) {
			$text = $langContent['title'];
		} else {
			$text = 'ID '.$content['field_id'];
		}
		$attr = array('title' => "edit '{$text}'");
	
		echo anchor($uri,$text,$attr);
	?></li>

			<?php
		}
	}
	
	?>
</ul>	


</body>
</html>
