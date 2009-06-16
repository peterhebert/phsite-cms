<h2><?php echo $title; ?></h2>
<p>Posted <?php echo $created ?> by <?php echo $author['name_first'].' '.$author['name_last']; ?></p>
<p><?php echo $content_data['teaser']; ?></p>
<p><?php echo anchor($uri,'Read More'); ?></p>
