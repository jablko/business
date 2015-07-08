<?php

$custom_fields = get_post_custom();
list ($address) = $custom_fields['address'];
list ($latlng) = $custom_fields['latlng'];

if (!$latlng) {
  return;
}

list ($lat, $lng) = explode(',', $latlng, 2);
$query_data = array(
  'zoom' => $lat > 49.0847671 && $lat < 49.1330039 && $lng > -116.5391438 && $lng < -116.491847 ? 14 : 12,
  'size' => '333x160',
  'markers' => $latlng,
);

switch (true) {
  case has_term('Restaurant', 'business-type'):
    $query_data['markers'] = 'icon:' . get_stylesheet_directory_uri() . "/maki/marker-36-1f77b4.png|$query_data[markers]";

    break;

  case has_term('Retailer', 'business-type'):
    $query_data['markers'] = 'icon:' . get_stylesheet_directory_uri() . "/maki/marker-36-2ca02c.png|$query_data[markers]";

    break;

  default:
    $query_data['markers'] = 'icon:' . get_stylesheet_directory_uri() . "/maki/marker-36-d62728.png|$query_data[markers]";
}

?>

<div class=staticmap>
  <a href="https://www.google.com/maps/place/<?php echo urlencode($address) ?>">
    <img title="<?php echo esc_attr(sprintf(__('Map of %s'), get_the_title())) ?>" src="//maps.googleapis.com/maps/api/staticmap?<?php echo http_build_query($query_data) ?>">
  </a>
</div>
