<tr class=form-field>
  <th scope=row><label for=in-season><?php echo __('In Season') ?></label></th>
  <td>
    <?php $all_in_season = get_option('in_season') ?>
    <?php $value = $all_in_season[$term->term_id] ?>
    <input id=in-season name=in-season type=text value="<?php echo esc_attr($value) ?>">
    <div class=wp-slider id=slider></div>
  </td>
</tr>

<link rel=stylesheet href="<?php echo plugins_url('products-form-fields.css', __FILE__) ?>">
<?php wp_enqueue_script('jquery-ui-slider') ?>
<script src="<?php echo plugins_url('products-form-fields.js', __FILE__) ?>"></script>
