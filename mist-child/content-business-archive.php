<tr>
  <td colspan=2>
    <header class=entry-header>
      <?php the_title('<h2 class=entry-title><a rel=bookmark href="' . get_permalink() . '">', '</a></h2>') ?>
    </header>
  </td>
</tr>
<tr>
  <td>
    <?php require get_stylesheet_directory() . '/address-short.php' ?>
  </td>
  <td>

    <?php if (has_post_thumbnail()): ?>
      <a href="<?php the_permalink() ?>">
        <?php the_post_thumbnail('post-thumbnail', array('class' => 'archivePostThumb')) ?>
      </a>
    <?php endif; ?>

    <dl>

      <?php require get_stylesheet_directory() . '/products-short.php' ?>

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

    </dl>

  </td>
</tr>
