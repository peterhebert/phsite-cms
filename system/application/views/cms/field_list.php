	<h3 class="first">Fields</h3>
	<p><?php echo anchor("cms/field/edit","New field");  ?></p>
	<ul><?php
	foreach($list as $row) {
	
	?>
	
	<li><?php 
	$uri = "cms/field/edit/id/".$row['field_id'];

	if($row['field_meta_id'] != '') {
		$uri .= "/lang/".$row['field_meta_id'];
	}

	if($row['field_name'] != '') {
		$text = $row['field_name'];
		$attr = array('title' => "edit '".$row['field_name']."' field");
	} else {
		$text = $row['field_ref_name'];
		$attr = array('title' => "edit '".$row['field_ref_name']."' field");
	}
	
	echo anchor($uri,$text,$attr);
	?></li>
	
	
	<?php } ?>
	</ul>
