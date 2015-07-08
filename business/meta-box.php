<?php

$custom_fields = get_post_custom();
list ($address) = $custom_fields['address'];
list ($phone) = $custom_fields['phone'];
list ($email) = $custom_fields['email'];
list ($website) = $custom_fields['website'];

?>

<div class=form-field>
  <label for=address><?php echo __('Address') ?></label>
  <input id=address name=address type=text value="<?php echo esc_attr($address) ?>">
</div>

<div class=form-field>
  <label for=phone><?php echo __('Phone') ?></label>
  <input id=phone name=phone type=tel value="<?php echo esc_attr($phone) ?>">
</div>

<div class=form-field>
  <label for=email><?php echo __('Email') ?></label>
  <input id=email name=email type=email value="<?php echo esc_attr($email) ?>">
</div>

<div class=form-field>
  <label for=website><?php echo __('Website') ?></label>
  <input id=website name=website type=text value="<?php echo esc_attr($website) ?>">
</div>

<div class=form-field>
  <label for=products><?php echo __('Products') ?></label>
  <?php $value = implode("\n", (array) wp_get_object_terms($post->ID, 'products', array('fields' => 'names'))) ?>
  <textarea id=products name=products rows=5><?php echo esc_textarea($value) ?></textarea>
</div>

<div class=form-field>
  <label for=farm-practices><?php echo __('Farm Practices') ?></label>
  <?php $value = implode("\n", (array) wp_get_object_terms($post->ID, 'farm-practices', array('fields' => 'names'))) ?>
  <textarea id=farm-practices name=farm-practices rows=5><?php echo esc_textarea($value) ?></textarea>
</div>

<div class=form-field>
  <label for=business-type><?php echo __('Business Type') ?></label>
  <?php $value = implode("\n", (array) wp_get_object_terms($post->ID, 'business-type', array('fields' => 'names'))) ?>
  <textarea id=business-type name=business-type rows=5><?php echo esc_textarea($value) ?></textarea>
</div>

<div class=form-field>
  <label for=available-at><?php echo __('Available at These Locations') ?></label>
  <?php $value = implode("\n", (array) wp_get_object_terms($post->ID, 'available-at', array('fields' => 'names'))) ?>
  <textarea id=available-at name=available-at rows=5><?php echo esc_textarea($value) ?></textarea>
</div>

<link rel=stylesheet href="<?php echo plugins_url('meta-box.css', __FILE__) ?>">
<script src=//maps.googleapis.com/maps/api/js?libraries=places></script>
<script src="<?php echo plugins_url('meta-box.js', __FILE__) ?>"></script>
