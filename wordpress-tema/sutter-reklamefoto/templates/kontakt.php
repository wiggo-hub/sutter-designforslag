<?php
/**
 * Template Name: Kontakt
 * Contact page: form on the left, the drawing of Paul on the right.
 *
 * @author Jan Wiggo Wiger
 */
nocache_headers();
get_header();
the_post();
$s_status = '';
$s_cls    = '';
if ( isset( $_GET['sendt'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$s_status = 'Takk! Meldingen er sendt, og Paul svarer deg så snart han kan.';
	$s_cls    = ' ok';
} elseif ( isset( $_GET['feil'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$s_status = 'Meldingen ble ikke sendt. Fyll ut alle feltene, eller ring Paul på ' . sutter_opt( 'phone' ) . '.';
}
?>
<main id="innhold" tabindex="-1">
<section class="contact">
  <div class="wrap">
    <h1><?php echo esc_html( sutter_plain_title( get_the_title() ) ); ?></h1>
    <div class="contact-grid">
      <div class="left">
      <form class="form" id="kontaktskjema" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
        <?php the_content(); ?>
        <p class="req" style="margin:0">Felt merket med <span class="star" aria-hidden="true">*</span> må fylles ut.</p>
        <input type="hidden" name="action" value="sutter_kontakt">
        <input type="hidden" name="tilbake" value="<?php echo esc_url( get_permalink() ); ?>">
        <input type="hidden" name="sutter_t" value="<?php echo esc_attr( sutter_form_token() ); ?>">
        <div style="position:absolute;left:-9999px" aria-hidden="true"><label for="nettside">La dette feltet stå tomt</label><input id="nettside" name="nettside" tabindex="-1" autocomplete="off"></div>
        <label for="navn">Navn <span class="star" aria-hidden="true">*</span></label>
        <input id="navn" name="navn" autocomplete="name" required aria-describedby="navn-feil">
        <p class="err" id="navn-feil" hidden>Skriv inn navnet ditt.</p>
        <label for="epost">E-post <span class="star" aria-hidden="true">*</span></label>
        <input id="epost" name="epost" type="email" autocomplete="email" required aria-describedby="epost-feil">
        <p class="err" id="epost-feil" hidden>Skriv inn en gyldig e-postadresse, for eksempel navn@firma.no.</p>
        <label for="melding">Melding <span class="star" aria-hidden="true">*</span></label>
        <textarea id="melding" name="melding" rows="6" required aria-describedby="melding-feil"></textarea>
        <p class="err" id="melding-feil" hidden>Skriv en melding.</p>
        <button type="submit">Send melding</button>
        <p class="status<?php echo esc_attr( $s_cls ); ?>" id="skjema-status" role="status"><?php echo esc_html( $s_status ); ?></p>
      </form>
        <dl class="direct2"><div><dt>Mob:</dt><dd><a href="<?php echo esc_attr( sutter_opt( 'phone_href' ) ); ?>"><?php echo esc_html( sutter_opt( 'phone' ) ); ?></a></dd></div><div><dt>Mail:</dt><dd><a href="mailto:<?php echo esc_attr( sutter_opt( 'email' ) ); ?>"><?php echo esc_html( sutter_opt( 'email' ) ); ?></a></dd></div></dl>
      </div>
      <?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'full', array( 'class' => 'paul', 'loading' => 'lazy', 'sizes' => '(max-width:900px) 100vw, 620px' ) );
		}
		?>
    </div>
  </div>
</section>
</main>
<?php
get_footer();
