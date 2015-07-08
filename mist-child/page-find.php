<?php

function my_head() {
  $upload_dir = wp_upload_dir();
  echo '<link rel=exhibit-data href="' . $upload_dir['baseurl'] . '/data.json">';
}
add_action('wp_head', 'my_head');

?>

<?php get_header() ?>

<div id=main-inner>
  <div id=full-width class=content-area>
    <div id=content class=site-content>

      <link rel=exhibit-extension href=//api.simile-widgets.org/exhibit/STABLE/extensions/map/map-extension.js>
      <div data-ex-role=collection data-ex-item-types=Item></div>

      <div class=three_fourth>

        <div data-ex-role=viewPanel>
          <div data-ex-role=view data-ex-label=List data-ex-show-toolbox=false<?php // data-ex-show-controls=false ?>></div>
          <div data-ex-role=view data-ex-view-class=Map data-ex-latlng=.latlng data-ex-map-constructor=map_constructor data-ex-autoposition=true data-ex-max-auto-zoom=14></div>
        </div>

      </div>
      <div class="one_fourth last">

        <div data-ex-role=facet data-ex-facet-class=HierarchicalFacet data-ex-expression=.products data-ex-uniform-grouping=.parent data-ex-facet-label="<?php echo __('Products') ?>" data-ex-show-missing=false></div>
        <div data-ex-role=facet data-ex-expression=!available-at data-ex-facet-label="<?php echo __('Products From') ?>" data-ex-show-missing=false></div>
        <div data-ex-role=facet data-ex-expression=.farm-practices data-ex-facet-label="<?php echo __('Farm Practices') ?>" data-ex-show-missing=false></div>
        <div data-ex-role=facet data-ex-expression=.business-type data-ex-facet-label="<?php echo __('Business Type') ?>" data-ex-show-missing=false></div>
        <div data-ex-role=facet data-ex-expression=.available-at data-ex-facet-label="<?php echo __('Available at These Locations') ?>" data-ex-show-missing=false></div>

      </div>

      <script src=//api.simile-widgets.org/exhibit/STABLE/exhibit-api.js></script>
      <?php wp_enqueue_script('page-find') ?>

      <?php while (have_posts()): the_post() ?>

        <?php if (comments_open() || get_comments_number()): ?>
          <?php comments_template() ?>
        <?php endif; ?>

      <?php endwhile; ?>

    </div>
  </div>
</div>

<?php get_footer() ?>
