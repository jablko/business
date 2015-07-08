<?php

$tax_query = array(
  array(
    'taxonomy' => 'available-at',
    'field' => 'slug',
    'terms' => $post->post_name,
  ),
);

$args = array(
  'tax_query' => $tax_query,
);

$query = new WP_Query;
$posts = $query->query($args);
if (!$posts) {
  return;
}

?>

<dt><?php echo has_term('Restaurant', 'business-type') ? __('We Serve Ingredients From') : __('We Sell Products From') ?></dt>
<dd><?php echo format_posts($posts) ?></dd>
