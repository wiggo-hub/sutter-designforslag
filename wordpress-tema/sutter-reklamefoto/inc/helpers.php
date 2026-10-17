<?php
/**
 * Small helpers used by the templates.
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

/** Settings from Utseende → Tilpass → Sutter Reklamefoto, with sensible defaults. */
function sutter_defaults() {
	return array(
		'phone'      => '+47 906 86 047',
		'phone_href' => 'tel:+4790686047',
		'email'      => 'paulsutter48@gmail.com',
		'form_to'    => 'paulsutter48@gmail.com',
		'slogan'     => '- kvalitetsfotografering i over 50 år!',
		'noai'       => 'Alle bilder er tatt uten bruk av KI.',
		'copyright'  => 'Alle bilder: Paul Sutter',
		'legal'      => '© Sutter Reklamefoto',
		'refs_title' => 'Oppdragsgivere gjennom årene',
		'front_btn'  => 'Se arbeidene',
		'works'      => 'Arbeider',
		'cta_title'  => 'Kontakt meg for avtale, pris eller befaring',
		'cta_btn'    => 'Til kontaktskjemaet',
	);
}

function sutter_opt( $key ) {
	$d = sutter_defaults();
	return get_theme_mod( 'sutter_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/** The logo as inline SVG (colour follows the text colour). */
function sutter_logo() {
	static $svg = null;
	if ( null === $svg ) {
		$svg = file_get_contents( SUTTER_DIR . '/assets/logo.svg' );
	}
	return $svg;
}

function sutter_lockup( $cls = '' ) {
	return '<div class="lockup ' . esc_attr( $cls ) . '" aria-hidden="true">' . sutter_logo() . '<p class="slogan">' . esc_html( sutter_opt( 'slogan' ) ) . '</p></div>';
}

/** A title may contain «|» to choose where the menu text breaks. */
function sutter_plain_title( $t ) {
	return trim( preg_replace( '/\s*\|\s*/', ' ', $t ) );
}

function sutter_menu_label( $t ) {
	$t     = html_entity_decode( $t, ENT_QUOTES, 'UTF-8' );
	$split = array(
		'Droneopptak ute og inne'    => array( 'Droneopptak', 'ute og inne' ),
		'Interiør- og eksteriørfoto' => array( 'Interiør- og', 'eksteriørfoto' ),
		'Modeller og portretter'     => array( 'Modeller og', 'portretter' ),
		'SNF/Gründeriet'             => array( 'SNF/', 'Gründeriet' ),
	);
	if ( false !== strpos( $t, '|' ) ) {
		$p = array_map( 'trim', explode( '|', $t, 2 ) );
	} elseif ( isset( $split[ $t ] ) ) {
		$p = $split[ $t ];
	} else {
		return '<span>' . esc_html( $t ) . '</span>';
	}
	$sep = ( '/' === substr( $p[0], -1 ) ) ? '' : ' ';
	return '<span>' . esc_html( $p[0] . $sep ) . '</span><span>' . esc_html( $p[1] ) . '</span>';
}

/** Pages using the «Kategori» template, in menu order. */
function sutter_category_pages() {
	return get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'templates/kategori.php',
	) );
}

function sutter_contact_page() {
	$p = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'templates/kontakt.php',
	) );
	return $p ? $p[0] : null;
}

function sutter_contact_url() {
	$c = sutter_contact_page();
	return $c ? get_permalink( $c ) : home_url( '/' );
}

/** Menu items as [url, title, object_id] – from the WordPress menu, or the pages if no menu is set. */
function sutter_menu_items( $location ) {
	$out       = array();
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations[ $location ] ) ) {
		$items = wp_get_nav_menu_items( $locations[ $location ] );
		if ( $items ) {
			foreach ( $items as $it ) {
				if ( (int) $it->menu_item_parent ) {
					continue;
				}
				$out[] = array( 'url' => $it->url, 'title' => $it->title, 'id' => (int) $it->object_id );
			}
			return $out;
		}
	}
	foreach ( sutter_category_pages() as $p ) {
		$out[] = array( 'url' => get_permalink( $p ), 'title' => $p->post_title, 'id' => $p->ID );
	}
	$c = sutter_contact_page();
	if ( $c ) {
		$out[] = array( 'url' => get_permalink( $c ), 'title' => $c->post_title, 'id' => $c->ID );
	}
	return $out;
}

/** Description for search engines: own field, excerpt, or the start of the text. */
function sutter_meta_description() {
	if ( is_front_page() ) {
		$d = get_post_meta( get_queried_object_id(), '_sutter_seo_desc', true );
		return $d ? $d : 'Paul Sutter er bedriftsfotograf i Sandefjord og Vestfold. Foto, video og drone – kvalitetsfotografering i over 50 år.';
	}
	if ( ! is_singular() ) {
		return '';
	}
	$id = get_queried_object_id();
	$d  = get_post_meta( $id, '_sutter_seo_desc', true );
	if ( $d ) {
		return $d;
	}
	$txt = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $id ) ) ) ) );
	if ( mb_strlen( $txt ) > 155 ) {
		$txt = mb_substr( $txt, 0, 155 );
		$txt = mb_substr( $txt, 0, mb_strrpos( $txt, ' ' ) ) . '…';
	}
	return $txt;
}

/** The gallery of a category page: images and films chosen in «Bilder og film». */
function sutter_gallery( $page_id ) {
	$ids  = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $page_id, '_sutter_gallery', true ) ) ) );
	$data = array();
	foreach ( $ids as $id ) {
		$a = get_post( $id );
		if ( ! $a || 'attachment' !== $a->post_type ) {
			continue;
		}
		$t = trim( $a->post_excerpt ) ? trim( $a->post_excerpt ) : $a->post_title;
		if ( 0 === strpos( $a->post_mime_type, 'video/' ) ) {
			$poster = get_post_thumbnail_id( $id );
			$data[] = array(
				't'      => $t,
				'video'  => wp_get_attachment_url( $id ),
				'poster' => $poster ? wp_get_attachment_image_url( $poster, 'full' ) : '',
				'thumb'  => $poster ? wp_get_attachment_image_url( $poster, 'medium_large' ) : '',
				'alt'    => 'Film: ' . $t,
			);
		} else {
			$full = wp_get_attachment_image_src( $id, 'full' );
			if ( ! $full ) {
				continue;
			}
			$alt    = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
			$data[] = array(
				't'     => $t,
				'src'   => $full[0],
				'thumb' => wp_get_attachment_image_url( $id, 'medium_large' ),
				'w'     => $full[1],
				'h'     => $full[2],
				'alt'   => $alt ? $alt : $t,
			);
		}
	}
	return $data;
}

/** Customer logos chosen under Utseende → Kundelogoer. */
function sutter_logos() {
	$ids = array_filter( array_map( 'intval', explode( ',', (string) get_option( 'sutter_logos', '' ) ) ) );
	$out = array();
	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( ! $src ) {
			continue;
		}
		$alt   = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		$out[] = array(
			'src' => $src[0],
			'w'   => $src[1],
			'h'   => $src[2],
			'alt' => $alt ? $alt : get_the_title( $id ),
		);
	}
	return $out;
}

/** Full-width main photo of a page (its featured image). */
function sutter_hero_img( $page_id ) {
	$id = get_post_thumbnail_id( $page_id );
	if ( ! $id ) {
		return '';
	}
	return wp_get_attachment_image( $id, 'full', false, array(
		'sizes'         => '100vw',
		'fetchpriority' => 'high',
		'loading'       => false,
		'class'         => '',
		'decoding'      => 'async',
	) );
}
