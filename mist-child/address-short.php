<?php

$custom_fields = get_post_custom();
list ($address) = $custom_fields['address'];
list ($phone) = $custom_fields['phone'];

?>

<address>

  <?php if ($address): ?>
    <div title="<?php echo __('Address') ?>"><i class="fa fa-map-marker"></i> <?php echo format_address($address) ?></div>
  <?php endif; ?>

  <?php if ($phone): ?>
    <div><a title="<?php echo __('Phone') ?>" href="tel:<?php echo telephone_subscriber($phone) ?>"><?php echo esc_html($phone) ?></a></div>
  <?php endif; ?>

</address>
