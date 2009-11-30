<h2>Setup</h2>
<ul>
	<li><?php echo $cms_labels['data_structure']; ?>
		<ul>
			<li><?php echo anchor("cms/setup/content_types",$cms_labels['content_types']); ?></li>
			<li><?php echo anchor("cms/setup/media_types",$cms_labels['media_types']); ?></li>
			<li><?php echo anchor("cms/setup/fields",$cms_labels['fields']); ?></li>
			<li><?php echo anchor("cms/setup/lists",$cms_labels['lists']); ?></li>
		</ul>
	</li>
	<li><?php echo $cms_labels['site'] ?>
		<ul>
			<li><?php echo anchor("cms/setup/site_preferences",$cms_labels['site_prefs']); ?></li>
			<?php
			if (isset($site_metadata))
			{
				echo "<li>".$cms_labels['site_metadata']."\r";
				echo $site_metadata."</li>\r";
			}
			?>
			<li><?php echo anchor("cms/setup/site_meta_fields",$cms_labels['site_meta_fields']); ?></li>
		</ul>
	</li>
	<li><?php echo anchor("cms/setup/languages",$cms_labels['languages']); ?></li>
	<li><?php echo $cms_labels['users']; ?>
		<ul>
			<li><?php echo anchor("cms/user",$cms_labels['users_edit']); ?></li>
			<li><?php echo anchor("cms/user/user_meta_fields",$cms_labels['users_meta_fields']); ?></li>
		</ul>
	</li>

</ul>
