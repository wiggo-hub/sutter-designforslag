<?php
/**
 * Front page: big photo, the categories as tiles, contact.
 *
 * @author Jan Wiggo Wiger
 */
get_header();
$s_pid = get_queried_object_id();
$s_h1  = $s_pid ? sutter_plain_title( get_the_title( $s_pid ) ) : get_bloginfo( 'name' );
?>
<main id="innhold" tabindex="-1">
<section class="hero" style="--accent:#1d5a80" aria-labelledby="sidetittel">
  <?php echo $s_pid ? sutter_hero_img( $s_pid ) : ''; // phpcs:ignore ?>
  <span class="hero-c" aria-hidden="true">© Paul Sutter</span>
  <div class="inner wrap">
    <div><h1 id="sidetittel"><?php echo esc_html( $s_h1 ); ?></h1><a class="btn" href="#arbeider"><?php echo esc_html( sutter_opt( 'front_btn' ) ); ?> <span aria-hidden="true">↓</span></a></div>
    <div class="hero-foot"><?php echo sutter_lockup(); // phpcs:ignore ?></div>
  </div>
</section>
<section class="content" id="arbeider" aria-labelledby="arbeider-tittel">
  <div class="wrap">
    <div class="tiles-head"><h2 id="arbeider-tittel"><?php echo esc_html( sutter_opt( 'works' ) ); ?></h2></div>
    <ul class="tiles"><?php
	foreach ( sutter_category_pages() as $p ) {
		$img = get_the_post_thumbnail_url( $p, 'medium_large' );
		printf( '<li><a href="%s">%s<span>%s</span></a></li>', esc_url( get_permalink( $p ) ), $img ? '<img src="' . esc_url( $img ) . '" alt="" loading="lazy">' : '', esc_html( sutter_plain_title( $p->post_title ) ) );
	}
	?></ul>
  </div>
</section>
<section class="content" style="padding-top:0" aria-labelledby="kontakt-tittel">
  <div class="wrap">
    <div class="panel" style="display:flex;flex-wrap:wrap;gap:16px 40px;align-items:center;justify-content:space-between">
      <div><h2 id="kontakt-tittel" style="margin:0 0 .3em"><?php echo esc_html( sutter_opt( 'cta_title' ) ); ?></h2><p style="margin:0">Mob: <a href="<?php echo esc_attr( sutter_opt( 'phone_href' ) ); ?>"><?php echo esc_html( sutter_opt( 'phone' ) ); ?></a> · Mail: <a href="mailto:<?php echo esc_attr( sutter_opt( 'email' ) ); ?>"><?php echo esc_html( sutter_opt( 'email' ) ); ?></a></p></div>
      <a class="btn" style="--accent:#231f20;margin:0" href="<?php echo esc_url( sutter_contact_url() ); ?>"><?php echo esc_html( sutter_opt( 'cta_btn' ) ); ?></a>
    </div>
  </div>
</section>
</main>
<?php
get_footer();
