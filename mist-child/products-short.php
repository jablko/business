<?php

$terms = get_the_terms($post, 'products');
if (!$terms) {
  return;
}

$opts = array(
  'short' => true,
);

$groups = group_terms($terms, $opts);
$terms = array();
foreach ($groups as $group_term_id => $group_terms) {
  if ($group_terms[$group_term_id] || count($group_terms) > 3) {
    $group_name = get_term($group_term_id, 'products')->name;
    $terms[$group_term_id] = $group_name;
  } else {
    $terms += $group_terms;
  }
}

?>

<dt><?php echo has_term('Restaurant', 'business-type') ? __('Local Ingredients') : (has_term('Retailer', 'business-type') ? __('Local Products') : __('Products')) ?></dt>
<dd><?php echo format_products($terms) ?></dd>
