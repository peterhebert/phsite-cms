		<h2 class="first">Users</h2>
		<?php
		if(count($list) == 0) {
			$list[] = "No users found";
		}
		echo ul($list);
		echo "<p>". anchor("cms/user/edit","New User")."</p>";
		?>
