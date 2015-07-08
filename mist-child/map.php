<?php

list ($latlng) = get_post_meta($post->ID, 'latlng');
if ($latlng) {
  $data = array(
    'title' => my_html_entity_decode(get_the_title()),
    'clickable' => false,
  );

  list ($data['lat'], $data['lng']) = explode(',', $latlng, 2);

  switch (true) {
    case has_term('Restaurant', 'business-type'):
      $data['icon'] = get_stylesheet_directory_uri() . '/maki/marker-36-1f77b4.png';

      break;

    case has_term('Retailer', 'business-type'):
      $data['icon'] = get_stylesheet_directory_uri() . '/maki/marker-36-2ca02c.png';

      break;

    default:
      $data['icon'] = get_stylesheet_directory_uri() . '/maki/marker-36-d62728.png';
  }

  $all_data[] = $data;
}

?>

<div id=map-canvas></div>

<script src=//maps.googleapis.com/maps/api/js></script>
<?php wp_enqueue_script('script') ?>
<script>
  <?php require get_stylesheet_directory() . '/map.js.php' ?>
</script>
