<?php

$terms = get_the_terms($post, 'products');
if (!$terms) {
  return;
}

$groups = group_terms($terms);
if (count($groups) == 1) {
  $terms = current($groups);
  $groups = array();
} else {
  $terms = array();
  foreach ($groups as $group_term_id => $group_terms) {
    if (count($group_terms) <= ($group_terms[$group_term_id] ? 4 : 3)) {
      $terms[current($group_terms)] = $group_terms;
    }
  }

  if (count($terms) > 1) {
    ksort($terms);
    $terms = my_array_sum($terms);

    foreach ($groups as $group_term_id => $group_terms) {
      if (count($group_terms) <= ($group_terms[$group_term_id] ? 4 : 3)) {
        unset($groups[$group_term_id]);
      }
    }
  } else {
    $terms = current($terms);
    if (count($terms) == 1 && $groups[key($terms)]) {
      unset($groups[key($terms)]);
    } else {
      $terms = false;
    }
  }

  foreach ($groups as $group_term_id => $group_terms) {
    unset($groups[$group_term_id][$group_term_id]);

    $group_name = explode(' ', get_term($group_term_id, 'products')->name);
    foreach ($groups[$group_term_id] as $term_id => $name) {
      $name = explode(' ', $name);
      for ($i = 1; $i <= min(count($name), count($group_name)) && $name[count($name) - $i] == $group_name[count($group_name) - $i]; $i++);
      $groups[$group_term_id][$term_id] = implode(' ', array_slice($name, 0, count($name) - $i + 1));
    }
  }
}

?>

<?php if ($terms): ?>
  <dt><?php echo has_term('Restaurant', 'business-type') ? __('Local Ingredients') : (has_term('Retailer', 'business-type') ? __('Local Products') : __('Products')) ?></dt>
  <dd><?php echo format_products($terms) ?></dd>
<?php endif; ?>

<?php foreach ($groups as $group_term_id => $group_terms): ?>
  <dt><?php echo get_term($group_term_id, 'products')->name ?></dt>
  <dd><?php echo format_products($group_terms) ?></dd>
<?php endforeach; ?>
