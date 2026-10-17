<?php
/**
 * Template Name: Kategori
 * Category page: full-width photo, then the text panel in the page colour and four pictures or films.
 *
 * @author Jan Wiggo Wiger
 */
get_header();
the_post();
$s_pid    = get_the_ID();
$s_title  = sutter_plain_title( get_the_title() );
$s_accent = get_post_meta( $s_pid, '_sutter_accent', true );
$s_accent = $s_accent ? $s_accent : '#1f5f86';
$s_cls    = array();
if ( get_post_meta( $s_pid, '_sutter_contain', true ) ) {
	$s_cls[] = 'contain-portrait';
}
if ( get_post_meta( $s_pid, '_sutter_topband', true ) ) {
	$s_cls[] = 'top-band';
}
$s_items = sutter_gallery( $s_pid );
$s_show  = array_slice( $s_items, 0, 4 );

/* Previous and next category */
$s_pages = sutter_category_pages();
$s_ids   = wp_list_pluck( $s_pages, 'ID' );
$s_pos   = array_search( $s_pid, $s_ids, true );
$s_prev  = ( false !== $s_pos && $s_pos > 0 ) ? $s_pages[ $s_pos - 1 ] : null;
$s_next  = ( false !== $s_pos && $s_pos < count( $s_pages ) - 1 ) ? $s_pages[ $s_pos + 1 ] : null;
?>
<main id="innhold" tabindex="-1">
<section class="hero cat <?php echo esc_attr( implode( ' ', $s_cls ) ); ?>" style="--accent:<?php echo esc_attr( $s_accent ); ?>">
  <?php echo sutter_hero_img( $s_pid ); // phpcs:ignore ?>
  <div class="inner wrap">
    <div><h1 id="sidetittel" class="sr-only"><?php echo esc_html( $s_title ); ?></h1><a class="btn" href="#bilder">Se mer <span aria-hidden="true">↓</span></a></div>
  </div>
</section>
<section class="page2" id="bilder" style="--accent:<?php echo esc_attr( $s_accent ); ?>" aria-labelledby="p2-tittel">
  <div class="p2-panel"><div class="p2-text"><h2 id="p2-tittel" class="sr-only"><?php echo esc_html( $s_title ); ?></h2><?php the_content(); ?></div><div class="noai"><p><?php echo esc_html( sutter_opt( 'noai' ) ); ?></p><?php if ( sutter_opt( 'copyright' ) ) { echo '<p><span class="c">©</span> ' . esc_html( preg_replace( '/^©\s*/u', '', sutter_opt( 'copyright' ) ) ) . '</p>'; } ?></div></div>
  <div class="p2-photos"><div class="squares-wrap" data-squares>
  <ul class="squares"><?php
	foreach ( $s_show as $k => $x ) {
		$play = ! empty( $x['video'] ) ? '<span class="play" aria-hidden="true">▶</span>' : '';
		$lab  = ( ! empty( $x['video'] ) ? 'Spill av film: ' : 'Vis stort: ' ) . $x['t'];
		printf(
			'<li><button class="sq" type="button" data-i="%d" aria-label="%s"><img src="%s" alt="" loading="lazy">%s</button><p class="sqcap">%s</p></li>',
			$k, esc_attr( $lab ), esc_url( $x['thumb'] ), $play, esc_html( $x['t'] )
		);
	}
	?></ul>
  <script type="application/json"><?php echo wp_json_encode( $s_show, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ); ?></script>
</div>
<p class="clickhint">Klikk for stort bilde</p></div>
</section>
<div class="wrap"><nav class="pager" aria-label="Bla mellom kategorier"><?php
	if ( $s_prev ) {
		printf( '<a class="prev" href="%s"><span>Forrige</span><b>← %s</b></a>', esc_url( get_permalink( $s_prev ) ), esc_html( sutter_plain_title( $s_prev->post_title ) ) );
	}
	if ( $s_next ) {
		$img = get_the_post_thumbnail_url( $s_next, 'medium_large' );
		printf( '<a class="next card" href="%s">%s<span>Neste</span><b>%s →</b></a>', esc_url( get_permalink( $s_next ) ), $img ? '<img src="' . esc_url( $img ) . '" alt="" loading="lazy">' : '', esc_html( sutter_plain_title( $s_next->post_title ) ) );
	} else {
		printf( '<a class="next" href="%s"><span>Neste</span><b>Kontakt →</b></a>', esc_url( sutter_contact_url() ) );
	}
	?></nav></div>
</main>
<?php
get_footer();
