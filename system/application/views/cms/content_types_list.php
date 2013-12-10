<h3 class="first"><?php echo $cms_labels['content_types']; ?></h3>
<p><?php echo anchor("cms/content_types/edit",$cms_labels['new_content_type']); ?></p>
<ul><?php foreach($type_list as $index => $row) {?>
<li><?php
$uri = "cms/content_types/edit/id/".$row['type_id'];
if($row['label_id'] != '') {
	$uri .= "/lang/".$row['label_id'];
}

if($row['label'] != '') {
	$text = $row['label'];
	$attr = array('title' => "edit '".$row['label']."'");
} else {
	$text = $row['type_uri_name'];
	$attr = array('title' => "edit '".$row['type_uri_name']."'");
}

	echo anchor($uri,$text,$attr);
?></li>
<?php } ?>
</ul>
