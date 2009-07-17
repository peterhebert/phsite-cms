<h3 class="first">Content types</h3>
<p><?php echo anchor("cms/content_types/edit","New Content type");  ?></p>
<ul><?php foreach($list as $index => $row) {?>
<li><?php
$uri = "cms/content_types/edit/id/".$row['type_id'];

if($row['type_meta_id'] != '') {
	$uri .= "/lang/".$row['type_meta_id'];
}

if($row['type_name'] != '') {
	$text = $row['type_name'];
	$attr = array('title' => "edit '".$row['type_name']."'");
} else {
	$text = $row['type_name_uri'];
	$attr = array('title' => "edit '".$row['type_name_uri']."'");
}

	echo anchor($uri,$text,$attr);
?></li>
<?php } ?>
</ul>
