<?php get_header() ?>

<div id=main-inner>
  <div id=full-width class=content-area>
    <div id=content class=site-content role=main>
      <?php while (have_posts()): the_post() ?>

        <?php get_template_part('content', 'business') ?>

        <?php mist_content_nav('nav-below') ?>

        <?php if (comments_open() || get_comments_number()): ?>
          <?php comments_template() ?>
        <?php endif; ?>

      <?php endwhile; ?>
    </div>
  </div>
</div>

<?php get_footer() ?>
