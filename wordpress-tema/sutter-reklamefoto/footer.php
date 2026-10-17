<?php
$s_logos = sutter_logos();
if ( $s_logos ) : ?>
<section class="refs" aria-labelledby="refs-tittel">
  <div class="wrap">
    <h2 id="refs-tittel"><?php echo esc_html( sutter_opt( 'refs_title' ) ); ?></h2>
    <ul class="refs-grid"><?php
	foreach ( $s_logos as $l ) {
		$tall = ( $l['h'] && $l['w'] / $l['h'] < 1.6 ) ? ' class="tall"' : '';
		printf( '<li><span class="cell"><img src="%s" alt="%s" width="%d" height="%d" loading="lazy"%s></span></li>', esc_url( $l['src'] ), esc_attr( $l['alt'] ), (int) $l['w'], (int) $l['h'], $tall );
	}
	?></ul>
  </div>
</section>
<?php endif; ?>
<div class="callbar" aria-label="Kontakt Paul"><a href="<?php echo esc_attr( sutter_opt( 'phone_href' ) ); ?>">Ring Paul</a><a href="<?php echo esc_url( sutter_contact_url() ); ?>">Send melding</a></div>
<footer class="foot">
  <div class="wrap">
    <div><?php echo sutter_lockup(); // phpcs:ignore ?></div>
    <div><h2>Kontakt</h2><ul><li>Mob: <a href="<?php echo esc_attr( sutter_opt( 'phone_href' ) ); ?>"><?php echo esc_html( sutter_opt( 'phone' ) ); ?></a></li><li>Mail: <a href="mailto:<?php echo esc_attr( sutter_opt( 'email' ) ); ?>"><?php echo esc_html( sutter_opt( 'email' ) ); ?></a></li></ul></div>
    <nav aria-label="Sider i bunnen"><h2>Sider</h2><ul><?php
	foreach ( sutter_menu_items( 'bunnmeny' ) as $it ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $it['url'] ), esc_html( sutter_plain_title( $it['title'] ) ) );
	}
	?></ul></nav>
    <p class="legal"><?php echo esc_html( sutter_opt( 'legal' ) ); ?><?php if ( sutter_opt( 'copyright' ) ) { echo ' · ' . esc_html( sutter_opt( 'copyright' ) ); } ?><?php
	$pp = get_privacy_policy_url();
	if ( $pp ) {
		echo ' · <a href="' . esc_url( $pp ) . '">Personvern</a>';
	}
	?></p>
  </div>
</footer>
<div class="lb" id="lb" hidden role="dialog" aria-modal="true" aria-label="Bilde i stort format">
  <div class="lb-media"></div>
  <button class="lb-prev" type="button" aria-label="Forrige bilde">‹</button>
  <button class="lb-next" type="button" aria-label="Neste bilde">›</button>
  <button class="close" type="button">Lukk ✕</button>
</div>
<?php wp_footer(); ?>
</body>
</html>
