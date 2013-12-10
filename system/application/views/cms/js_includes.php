<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-ui-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-easing.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery.url.packed.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>


<?php
$min = '';
if (DEV_MODE == FALSE)
{
	$min = '-min';
}
?>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/admin-ui<?php echo $min; ?>.js"></script>
