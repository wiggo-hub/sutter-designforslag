<?php
/**
 * Editing in WordPress: page settings for categories, customer logos and site texts.
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Page settings box ---------- */
add_action( 'add_meta_boxes_page', function ( $post ) {
	add_meta_box( 'sutter_kategori', 'Kategoriside: farge, bilder og film', 'sutter_metabox_html', 'page', 'normal', 'high' );
	add_meta_box( 'sutter_seo', 'Søk (Google)', 'sutter_seo_box_html', 'page', 'side', 'default' );
} );

/* The classic editor box also shows in the block editor; only show it for the «Kategori» template */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	$is_page = $screen && 'page' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	$is_logos = ( 'appearance_page_sutter-logoer' === $hook );
	if ( ! $is_page && ! $is_logos ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'sutter-admin', SUTTER_URI . '/assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), filemtime( SUTTER_DIR . '/assets/js/admin.js' ), true );
	wp_add_inline_style( 'wp-color-picker', '
		.sutter-list{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0;padding:0;list-style:none;min-height:20px}
		.sutter-list li{position:relative;width:120px;margin:0;cursor:move;background:#f6f7f7;border:1px solid #dcdcde;padding:4px}
		.sutter-list img{display:block;width:100%;height:90px;object-fit:cover}
		.sutter-list.logos img{object-fit:contain;background:#fff}
		.sutter-list .cap{font-size:11px;line-height:1.3;margin-top:4px;max-height:2.6em;overflow:hidden}
		.sutter-list .film{position:absolute;left:6px;top:6px;background:#1d2327;color:#fff;font-size:10px;padding:1px 5px}
		.sutter-list .rm{position:absolute;right:2px;top:2px;border:0;background:#b32d2e;color:#fff;width:22px;height:22px;cursor:pointer;line-height:20px}
		.sutter-help{color:#50575e;max-width:720px}
	' );
} );

function sutter_list_items_html( $ids, $logos = false ) {
	$out = '';
	foreach ( $ids as $id ) {
		$a = get_post( $id );
		if ( ! $a ) {
			continue;
		}
		$is_video = 0 === strpos( $a->post_mime_type, 'video/' );
		$thumb    = $is_video ? get_the_post_thumbnail_url( $id, 'thumbnail' ) : wp_get_attachment_image_url( $id, $logos ? 'medium' : 'thumbnail' );
		$cap      = $logos ? get_post_meta( $id, '_wp_attachment_image_alt', true ) : ( $a->post_excerpt ? $a->post_excerpt : $a->post_title );
		$out     .= '<li data-id="' . (int) $id . '">' . ( $thumb ? '<img src="' . esc_url( $thumb ) . '" alt="">' : '<div style="height:90px;background:#ddd"></div>' )
			. ( $is_video ? '<span class="film">FILM</span>' : '' )
			. '<div class="cap">' . esc_html( $cap ) . '</div><button type="button" class="rm" aria-label="Fjern">×</button></li>';
	}
	return $out;
}

function sutter_metabox_html( $post ) {
	wp_nonce_field( 'sutter_meta', 'sutter_meta_nonce' );
	$accent  = get_post_meta( $post->ID, '_sutter_accent', true );
	$gallery = (string) get_post_meta( $post->ID, '_sutter_gallery', true );
	$ids     = array_filter( array_map( 'intval', explode( ',', $gallery ) ) );
	$contain = get_post_meta( $post->ID, '_sutter_contain', true );
	$topband = get_post_meta( $post->ID, '_sutter_topband', true );
	?>
	<p class="sutter-help">Gjelder sider med malen <strong>Kategori</strong> (velg under «Sideattributter» → «Mal»). Hovedbildet øverst er sidens <strong>fremhevet bilde</strong>, og teksten på farget bunn er selve sideinnholdet.</p>
	<p><label for="sutter_accent"><strong>Farge på tekstfeltet</strong></label><br>
	<input type="text" id="sutter_accent" name="sutter_accent" value="<?php echo esc_attr( $accent ); ?>" class="sutter-color" data-default-color="#1f5f86"></p>
	<p class="sutter-help">Velg en mørk farge, så den hvite teksten blir lett å lese.</p>
	<p><strong>Bilder og film</strong> – de fire første vises på siden. Dra for å endre rekkefølge. Bildeteksten hentes fra feltet «Bildetekst» i mediebiblioteket. En film får sitt stillbilde fra «Fremhevet bilde» på filmen.</p>
	<ul class="sutter-list" data-input="sutter_gallery"><?php echo sutter_list_items_html( $ids ); // phpcs:ignore ?></ul>
	<input type="hidden" id="sutter_gallery" name="sutter_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
	<p><button type="button" class="button sutter-add" data-target="sutter_gallery" data-type="image,video">Legg til bilder eller film</button></p>
	<p><label><input type="checkbox" name="sutter_topband" value="1" <?php checked( $topband ); ?>> Sort felt over hovedbildet (så ingenting kuttes øverst)</label><br>
	<label><input type="checkbox" name="sutter_contain" value="1" <?php checked( $contain ); ?>> Vis hele hovedbildet på mobil som holdes på høykant</label></p>
	<?php
}

function sutter_seo_box_html( $post ) {
	$t = get_post_meta( $post->ID, '_sutter_seo_title', true );
	$d = get_post_meta( $post->ID, '_sutter_seo_desc', true );
	?>
	<p><label for="sutter_seo_title">Tittel i Google (valgfritt)</label><input type="text" class="widefat" id="sutter_seo_title" name="sutter_seo_title" value="<?php echo esc_attr( $t ); ?>"></p>
	<p><label for="sutter_seo_desc">Kort beskrivelse (valgfritt, ca. 150 tegn)</label><textarea class="widefat" rows="4" id="sutter_seo_desc" name="sutter_seo_desc"><?php echo esc_textarea( $d ); ?></textarea></p>
	<p class="description">Står feltene tomme, brukes sidetittelen og starten av teksten.</p>
	<?php
}

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['sutter_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sutter_meta_nonce'] ) ), 'sutter_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	$accent = isset( $_POST['sutter_accent'] ) ? sanitize_hex_color( wp_unslash( $_POST['sutter_accent'] ) ) : '';
	update_post_meta( $post_id, '_sutter_accent', $accent ? $accent : '' );
	$gallery = isset( $_POST['sutter_gallery'] ) ? implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['sutter_gallery'] ) ) ) ) ) ) : '';
	update_post_meta( $post_id, '_sutter_gallery', $gallery );
	update_post_meta( $post_id, '_sutter_topband', empty( $_POST['sutter_topband'] ) ? '' : '1' );
	update_post_meta( $post_id, '_sutter_contain', empty( $_POST['sutter_contain'] ) ? '' : '1' );
	update_post_meta( $post_id, '_sutter_seo_title', isset( $_POST['sutter_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['sutter_seo_title'] ) ) : '' );
	update_post_meta( $post_id, '_sutter_seo_desc', isset( $_POST['sutter_seo_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sutter_seo_desc'] ) ) : '' );
} );

/* ---------- Customer logos ---------- */
add_action( 'admin_menu', function () {
	add_theme_page( 'Kundelogoer', 'Kundelogoer', 'edit_theme_options', 'sutter-logoer', 'sutter_logos_page' );
} );

function sutter_logos_page() {
	if ( isset( $_POST['sutter_logos_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sutter_logos_nonce'] ) ), 'sutter_logos' ) && current_user_can( 'edit_theme_options' ) ) {
		$val = isset( $_POST['sutter_logos'] ) ? implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['sutter_logos'] ) ) ) ) ) ) : '';
		update_option( 'sutter_logos', $val );
		echo '<div class="notice notice-success"><p>Logoene er lagret.</p></div>';
	}
	$ids = array_filter( array_map( 'intval', explode( ',', (string) get_option( 'sutter_logos', '' ) ) ) );
	?>
	<div class="wrap">
	<h1>Kundelogoer</h1>
	<p class="sutter-help">Logoene vises nederst på alle sider. Dra for å endre rekkefølge. Firmanavnet leses opp for skjermlesere, så fyll ut «Alternativ tekst» med firmanavnet når du laster opp en ny logo. Bruk PNG med hvit eller gjennomsiktig bakgrunn.</p>
	<form method="post">
		<?php wp_nonce_field( 'sutter_logos', 'sutter_logos_nonce' ); ?>
		<ul class="sutter-list logos" data-input="sutter_logos"><?php echo sutter_list_items_html( $ids, true ); // phpcs:ignore ?></ul>
		<input type="hidden" id="sutter_logos" name="sutter_logos" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
		<p><button type="button" class="button sutter-add" data-target="sutter_logos" data-type="image">Legg til logoer</button></p>
		<?php submit_button( 'Lagre logoene' ); ?>
	</form>
	</div>
	<?php
}

/* ---------- Texts and contact details (Utseende → Tilpass) ---------- */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'sutter', array( 'title' => 'Sutter Reklamefoto', 'priority' => 30 ) );
	$fields = array(
		'phone'      => 'Telefon (slik det vises)',
		'phone_href' => 'Telefon for «Ring» (tel:+47…)',
		'email'      => 'E-post (vises på siden)',
		'form_to'    => 'Kontaktskjemaet sendes til',
		'slogan'     => 'Slagord under logoen',
		'noai'       => 'Linje om KI (kategorisidene)',
		'copyright'  => 'Linje om copyright',
		'legal'      => 'Tekst nederst',
		'refs_title' => 'Overskrift over kundelogoene',
		'front_btn'  => 'Knapp på forsiden',
		'works'      => 'Overskrift over kategoriene på forsiden',
		'cta_title'  => 'Kontaktboks på forsiden: overskrift',
		'cta_btn'    => 'Kontaktboks på forsiden: knapp',
	);
	$d = sutter_defaults();
	foreach ( $fields as $k => $label ) {
		$wp_customize->add_setting( 'sutter_' . $k, array( 'default' => $d[ $k ], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'sutter_' . $k, array( 'label' => $label, 'section' => 'sutter', 'type' => 'text' ) );
	}
} );
