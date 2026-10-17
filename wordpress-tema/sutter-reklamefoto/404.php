<?php
/**
 * Page not found.
 *
 * @author Jan Wiggo Wiger
 */
get_header();
?>
<main id="innhold" tabindex="-1">
<section class="content">
  <div class="wrap prose">
    <h1>Fant ikke siden</h1>
    <p>Siden finnes ikke, eller den har fått ny adresse.</p>
    <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Gå til forsiden</a> eller <a href="<?php echo esc_url( sutter_contact_url() ); ?>">kontakt Paul</a>.</p>
  </div>
</section>
</main>
<?php
get_footer();
