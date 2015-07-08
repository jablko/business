<div class=form-field>
  <label for=in-season><?php echo __('In Season') ?></label>
  <input id=in-season name=in-season type=text>
  <div class=wp-slider id=slider></div>
</div>

<link rel=stylesheet href="<?php echo plugins_url('products-form-fields.css', __FILE__) ?>">
<?php wp_enqueue_script('jquery-ui-slider') ?>
<script src="<?php echo plugins_url('products-form-fields.js', __FILE__) ?>"></script>
