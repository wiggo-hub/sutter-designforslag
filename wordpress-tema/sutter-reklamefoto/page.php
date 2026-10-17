<?php
/**
 * Ordinary text page (for example the privacy page).
 *
 * @author Jan Wiggo Wiger
 */
get_header();
?>
<main id="innhold" tabindex="-1">
<section class="content">
  <div class="wrap prose">
<?php while ( have_posts() ) : the_post(); ?>
    <h1><?php echo esc_html( sutter_plain_title( get_the_title() ) ); ?></h1>
    <?php the_content(); ?>
<?php endwhile; ?>
  </div>
</section>
</main>
<?php
get_footer();
