<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-ui-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-easing.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery.url.packed.js"></script>

<?php
$min = '';
if (DEV_MODE == FALSE)
{
	$min = '-min';
}
?>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/installer-ui<?php echo $min; ?>.js"></script>
