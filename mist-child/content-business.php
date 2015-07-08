<?php

$query = "
  SELECT ID
  FROM $wpdb->posts
    INNER JOIN $wpdb->terms as t
      ON t.name = post_title
    INNER JOIN $wpdb->term_taxonomy AS tt
      ON tt.term_id = t.term_id
    INNER JOIN $wpdb->term_relationships AS tr
      ON tr.term_taxonomy_id = tt.term_taxonomy_id
  WHERE tt.taxonomy = 'available-at'
    AND tr.object_id = %d
";
$query = $wpdb->prepare($query, $post->ID);
$available_at_data = array();
foreach ($wpdb->get_col($query) as $post_id) {
  $custom_fields = get_post_custom($post_id);
  list ($address) = $custom_fields['address'];
  list ($latlng) = $custom_fields['latlng'];
  list ($phone) = $custom_fields['phone'];

  if ($latlng) {
    $post = get_post($post_id);

    $data = array(
      'title' => my_html_entity_decode(get_the_title()),
      'permalink' => get_permalink(),
    );

    list ($data['lat'], $data['lng']) = explode(',', $latlng, 2);

    $data += array_filter(compact('address', 'phone'));

    $value = wp_get_object_terms($post->ID, 'business-type', array('fields' => 'names'));
    if ($value) {
      $value = array_map('my_html_entity_decode', array_map('wptexturize', $value));
      $data['business-type'] = count($value) == 1 ? $value[0] : $value;
    }

    $value = wp_get_object_terms($post->ID, 'farm-practices', array('fields' => 'names'));
    if ($value) {
      $value = array_map('my_html_entity_decode', array_map('wptexturize', $value));
      $data['farm-practices'] = count($value) == 1 ? $value[0] : $value;
    }

    $thumbnail_id = get_post_thumbnail_id($post->ID);
    if ($thumbnail_id) {
      list ($data['thumbnail-src'], $data['thumbnail-width'], $data['thumbnail-height']) = wp_get_attachment_image_src($thumbnail_id);
    }

    switch (true) {
      case has_term('Restaurant', 'business-type'):
        $data['icon'] = get_stylesheet_directory_uri() . '/maki/circle-18-1f77b4.png';

        break;

      case has_term('Retailer', 'business-type'):
        $data['icon'] = get_stylesheet_directory_uri() . '/maki/circle-18-2ca02c.png';

        break;

      default:
        $data['icon'] = get_stylesheet_directory_uri() . '/maki/circle-18-d62728.png';
    }

    $available_at_data[] = $data;
  }
}
wp_reset_postdata();

?>

<article id="post-<?php the_ID() ?>" <?php post_class() ?>>
  <header class=entry-header>
    <h1 class=entry-title><?php the_title() ?></h1>
  </header>
  <div class=entry-content>
    <div class=two_third>

      <?php the_content() ?>

      <dl>

        <?php require get_stylesheet_directory() . '/products.php' ?>

        <?php require get_stylesheet_directory() . '/products-from.php' ?>

        <?php $terms = get_the_terms($post, 'farm-practices') ?>
        <?php if ($terms): ?>
          </dl>

          <div class=farm-practices><i class="fa fa-certificate"></i> <strong><?php echo format_terms($terms) ?></strong></div>

          <dl>
        <?php endif; ?>

        <?php $terms = get_the_terms($post, 'business-type') ?>
        <?php if ($terms): ?>
          <dt><?php echo __('Business Type') ?></dt>
          <dd><?php echo format_terms($terms) ?></dd>
        <?php endif; ?>

        <?php if (!$available_at_data): ?>
          <?php $terms = get_the_terms($post, 'available-at') ?>
          <?php if ($terms): ?>
            <dt><?php echo __('Available at These Locations') ?></dt>
            <dd><?php echo format_terms($terms) ?></dd>
          <?php endif; ?>
        <?php endif; ?>

      </dl>

      <?php require get_stylesheet_directory() . '/gallery.php' ?>

    </div>
    <div class="one_third last">

      <?php if (!$available_at_data): ?>
        <?php require get_stylesheet_directory() . '/staticmap.php' ?>
      <?php endif; ?>

      <?php require get_stylesheet_directory() . '/address.php' ?>

      <?php if ($available_at_data): ?>
        </div><div class=clearboth></div><div class=one_half>

          <dl>
            <?php $terms = get_the_terms($post, 'available-at') ?>
            <?php if ($terms): ?>
              <dt><?php echo __('Available at These Locations') ?></dt>
              <dd><?php echo format_terms($terms) ?></dd>
            <?php endif; ?>
          </dl>

        </div>
        <div class="one_half last">

          <?php $all_data = $available_at_data ?>
          <?php require get_stylesheet_directory() . '/map.php' ?>

      <?php endif; ?>

    </div>
    <?php wp_link_pages(array('before' => '<div class=page-links>' . __('Pages:', 'mist'), 'after' => '</div>')) ?>
  </div>
  <?php edit_post_link(__('Edit', 'mist'), '<footer class=entry-meta><span class=edit-link>', '</span></footer>') ?>
</article>
