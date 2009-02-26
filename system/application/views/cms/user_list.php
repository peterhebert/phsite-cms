		<h2 class="first">Users</h2>
		<?php
		if(count($list) > 0) {
			echo "<p>".anchor("cms/user/edit","New User")."</p>\n";
			echo ul($list);
		} else {
			echo "<p>No users found</p>";
			echo "<p>".anchor("cms/user/edit","New User")."</p>\n";
		}
		?>
