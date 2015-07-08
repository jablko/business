<?php

$custom_fields = get_post_custom();
list ($address) = $custom_fields['address'];
list ($phone) = $custom_fields['phone'];
list ($email) = $custom_fields['email'];

?>

<address>

  <?php if ($address): ?>
    <div title="<?php echo __('Address') ?>"><i class="fa fa-map-marker"></i> <?php echo format_address($address) ?></div>
  <?php endif; ?>

  <?php if ($phone): ?>
    <div><a title="<?php echo __('Phone') ?>" href="tel:<?php echo telephone_subscriber($phone) ?>"><?php echo esc_html($phone) ?></a></div>
  <?php endif; ?>

  <?php if ($email): ?>
    <div><a title="<?php echo __('Email') ?>" href="mailto:<?php echo esc_attr($email) ?>"><?php echo esc_html($email) ?></a></div>
  <?php endif; ?>

  <?php $website = esc_url($custom_fields['website_up'][0]) ?>
  <?php if ($website): ?>
    <div><a title="<?php echo __('Website') ?>" href="<?php echo $website ?>"><?php echo format_website($website) ?></a></div>
  <?php endif; ?>

</address>
