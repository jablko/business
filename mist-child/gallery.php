<?php

$args = array(
  'post_parent' => $post->ID,
  'post_type' => 'attachment',
  'order' => 'ASC',
);

$attachments = get_children($args);
if (!$attachments) {
  return;
}

$attr = array(
  'include' => array_slice(array_keys($attachments), 0, 2),
);

echo gallery_shortcode($attr);

$all_data = array();
foreach ($attachments as $attachment_id => $attachment) {
  $data = array();
  list ($data['href'], $width, $height) = wp_get_attachment_image_src($attachment_id, 'full');
  list ($data['thumbnail'], $width, $height) = wp_get_attachment_image_src($attachment_id);
  $all_data[] = $data;
}

?>

<link rel=stylesheet href="<?php echo get_stylesheet_directory_uri() ?>/fancybox/jquery.fancybox.css">
<link rel=stylesheet href="<?php echo get_stylesheet_directory_uri() ?>/fancybox/helpers/jquery.fancybox-thumbs.css">
<script src="<?php echo get_stylesheet_directory_uri() ?>/fancybox/jquery.fancybox.pack.js"></script>
<script src="<?php echo get_stylesheet_directory_uri() ?>/fancybox/helpers/jquery.fancybox-thumbs.js"></script>
<script>
  <?php require get_stylesheet_directory() . '/gallery.js.php' ?>
</script>
