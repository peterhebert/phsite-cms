	<h2 class="first">Languages</h2>
	<?php
	
if(count($list) > 0) {
	echo "<p>The following languages are currently active:</p>\n";
	foreach($list as $row) {
	
		$temp = $row['Ref_Name'];
		if ($row['Native_Name'] != '') {
			$temp .= " (".$row['Native_Name'].")";
		}
		$active[] = $temp;
	
	}
	echo ul($active);
} else {
	echo "<p>No languages are currently active</p>\n";
}
	echo "<p>".anchor("cms/language","Edit Languages")."</p>\n";

		?>
