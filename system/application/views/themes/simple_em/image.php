<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body class="gallery">
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo $breadcrumb; ?></div>
	<div class="page-wrap block">
		<h1><?php echo $title; ?></h1>
		<p>Posted <?php echo $created ?> by <?php echo $author['name_first'].' '.$author['name_last']; ?></p>
		<?php
		if (isset($image)) {
			echo "<div class=\"image\">$image</div>\r"; 
		}?>
		<p>Keywords: <?php echo $content['data']['keywords']; ?>
		<pre><?php
		print_r($content['data']); 
		
		
		if (isset($image_meta) && is_array($image_meta)) {
		print_r($image_meta);
		} ?></pre>
	</div>
	<div class="page-wrap block" id="footer"><?php echo $site['copyright']; ?></div>
</div>
</body>
</html>

<?php
/*Array
(
    [iptc] => Array
        (
            [2#000] => Array
                (
                    [0] => 
                )

            [2#025] => Array
                (
                    [0] => film
                    [1] => Lucky - or Not?
                    [2] => production
                )

            [2#090] => Array
                (
                    [0] => Ottawa
                )

            [2#095] => Array
                (
                    [0] => Ontario
                )

            [2#101] => Array
                (
                    [0] => Canada
                )

            [2#105] => Array
                (
                    [0] => breaktime
                )

            [2#120] => Array
                (
                    [0] => Joyce MacPhee talks with cast and crew during a break in shooting
                )

        )

    [exif] => Array
        (
            [FileName] => 20030426_film-Lucky-or-Not_0097.JPG
            [FileDateTime] => 1235454864
            [FileSize] => 102391
            [FileType] => 2
            [MimeType] => image/jpeg
            [SectionsFound] => ANY_TAG, IFD0, EXIF
            [COMPUTED] => Array
                (
                    [html] => width="767" height="573"
                    [Height] => 573
                    [Width] => 767
                    [IsColor] => 1
                    [ByteOrderMotorola] => 1
                    [ApertureFNumber] => f/2.6
                )

            [Make] => PENTAX Corporation 
            [Model] => PENTAX Optio S 
            [Orientation] => 1
            [XResolution] => 72/1
            [YResolution] => 72/1
            [ResolutionUnit] => 2
            [Software] => Adobe Photoshop 7.0
            [DateTime] => 2003:04:27 15:54:56
            [YCbCrPositioning] => 1
            [Exif_IFD_Pointer] => 278
            [UndefinedTag:0xC4A5] => PrintIM0250
            [ExposureTime] => 1/40
            [FNumber] => 26/10
            [ExposureProgram] => 2
            [ExifVersion] => 0220
            [DateTimeOriginal] => 2003:04:26 13:59:53
            [DateTimeDigitized] => 2003:04:26 13:59:53
            [ComponentsConfiguration] => 
            [CompressedBitsPerPixel] => 307200/786432
            [ExposureBiasValue] => 0/3
            [MaxApertureValue] => 28/10
            [MeteringMode] => 5
            [LightSource] => 0
            [Flash] => 73
            [FocalLength] => 580/100
            [FlashPixVersion] => 0100
            [ColorSpace] => 1
            [ExifImageWidth] => 767
            [ExifImageLength] => 573
            [FileSource] => 
            [CustomRendered] => 0
            [ExposureMode] => 0
            [WhiteBalance] => 0
            [DigitalZoomRatio] => 0/0
            [FocalLengthIn35mmFilm] => 35
            [SceneCaptureType] => 0
            [GainControl] => 0
            [Contrast] => 0
            [Saturation] => 0
            [Sharpness] => 0
            [SubjectDistanceRange] => 3
        )

)*/

?>