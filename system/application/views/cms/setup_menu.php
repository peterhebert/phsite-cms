<h2>Setup</h2>
<ul>
	<li><?php echo anchor("cms/setup/site_prefs",$cms_labels['site_prefs']); ?></li>
	<?php
	if (isset($site_metadata))
	{
		echo "<li>".$site_metadata."</li>\r";
	}
	?>
	<li><?php echo anchor("cms/setup/languages",$cms_labels['languages']); ?></li>
	<li><?php echo anchor("cms/setup/structure",$cms_labels['data_structure']); ?></li>
	<li><?php echo $cms_labels['users']; ?>
		<ul>
			<li><?php echo anchor("cms/user",$cms_labels['users_edit']); ?></li>
			<li><?php echo anchor("cms/user/user_meta_fields",$cms_labels['users_edit_meta']); ?></li>
		</ul>
	</li>
</ul>
