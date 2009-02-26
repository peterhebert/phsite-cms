	<h3 class="first">Objects</h3>
	<p><?php echo anchor("cms/object/edit","New Object");  ?></p>
	<ul><?php foreach($list as $index => $row) {?>
	<li><?php
		$uri = "cms/object/edit/id/".$row['object_id'];
		$text = $row['name'];
		$attr = array('title' => "edit '".$row['name']."' template");
	
		echo anchor($uri,$text,$attr);
	?></li>
	<?php } ?>
	</ul>
