<h2>Setup</h2>
<ul>
	<li><?php echo anchor("cms/setup/site_prefs","Site preferences"); ?></li>
	<?php
	if (isset($site_metadata))
	{
		echo "<li>".$site_metadata."</li>\r";
	}
	?>
	<li><?php echo anchor("cms/setup/languages","Languages"); ?></li>
	<li><?php echo anchor("cms/setup/structure","Data Structure"); ?></li>
	<li>Users
		<ul>
			<li><?php echo anchor("cms/user","Edit Users"); ?></li>
			<li><?php echo anchor("cms/user/user_meta_fields","Edit user metadata fields"); ?></li>
		</ul>
	</li>
</ul>
