<?php

function my_enqueue_scripts() {
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

  wp_register_script('script', get_stylesheet_directory_uri() . '/script.js', array('underscore'));

  $l10n = array(
    'address' => __('Address'),
    'phone' => __('Phone'),
    'business-type' => __('Business Type'),
  );

  wp_localize_script('script', 'script', $l10n);

  wp_register_script('page-find', get_stylesheet_directory_uri() . '/page-find.js', array('script'));

  $l10n = array(
    'local-ingredients' => __('Local Ingredients'),
    'local-products' => __('Local Products'),
    'products' => __('Products'),
    'in-season' => __('In Season %s'),
    'we-serve' => __('We Serve Ingredients From'),
    'we-sell' => __('We Sell Products From'),
  );

  wp_localize_script('page-find', 'page_find', $l10n);
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');
