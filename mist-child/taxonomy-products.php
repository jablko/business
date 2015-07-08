<?php get_header() ?>

<div id=main-inner>
  <section id=primary class=content-area>
    <div id=content class=site-content role=main>
      <?php if (have_posts()): ?>

        <header class=page-header>
          <?php the_archive_title('<h1 class=page-title>', '</h1>') ?>
          <?php the_archive_description('<div class=taxonomy-description>', '</div>') ?>
        </header>

        <table class=archive>
          <?php while (have_posts()): the_post() ?>
            <?php get_template_part('content', 'business-archive') ?>
          <?php endwhile; ?>
        </table>

        <?php mist_content_nav('nav-below') ?>

      <?php else: ?>
        <?php get_template_part('no-results', 'archive') ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php get_sidebar() ?>

<?php get_footer() ?>
