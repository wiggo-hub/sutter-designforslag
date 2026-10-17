<?php
/**
 * Sutter Reklamefoto – theme functions.
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

define( 'SUTTER_DIR', get_template_directory() );
define( 'SUTTER_URI', get_template_directory_uri() );

require SUTTER_DIR . '/inc/helpers.php';
require SUTTER_DIR . '/inc/admin.php';
require SUTTER_DIR . '/inc/contact.php';
require SUTTER_DIR . '/inc/setup.php';
require SUTTER_DIR . '/inc/site.php';

/* ---------- Theme setup ---------- */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_post_type_support( 'attachment:video', 'thumbnail' );
	register_nav_menus( array(
		'hovedmeny' => 'Hovedmeny (toppen)',
		'bunnmeny'  => 'Sider i bunnen',
	) );
} );

/* Videos can have a poster image (featured image) in the media library */
add_action( 'init', function () {
	add_post_type_support( 'attachment:video', 'thumbnail' );
} );

/* ---------- Styles and scripts ---------- */
add_action( 'wp_enqueue_scripts', function () {
	$css = SUTTER_DIR . '/assets/css/site.css';
	$js  = SUTTER_DIR . '/assets/js/site.js';
	wp_enqueue_style( 'sutter', SUTTER_URI . '/assets/css/site.css', array(), filemtime( $css ) );
	wp_enqueue_script( 'sutter', SUTTER_URI . '/assets/js/site.js', array(), filemtime( $js ), array( 'in_footer' => true ) );
} );

/* Preload the regular font so text does not jump */
add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( SUTTER_URI . '/assets/fonts/lato-latin-400-normal.woff2' ) );
}, 2 );

/* Keep Harry's and Paul's texts exactly as written (no automatic dash and quote changes) */
add_filter( 'run_wptexturize', '__return_false' );

/* WordPress' own front-end styles are not needed by this design */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}, 100 );

/* Less clutter in <head> */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

/* ---------- Titles and search ---------- */
add_filter( 'document_title_separator', function () {
	return '–';
} );

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( is_front_page() ) {
		return 'Sutter Reklamefoto – Paul Sutter, bedriftsfotograf i Vestfold';
	}
	if ( is_page() ) {
		$t = get_post_meta( get_the_ID(), '_sutter_seo_title', true );
		if ( ! $t ) {
			$t = wp_strip_all_tags( sutter_plain_title( get_the_title() ) );
		}
		return $t . ' – Sutter Reklamefoto, fotograf i Sandefjord';
	}
	return $title;
} );

add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return; // an SEO plugin takes over
	}
	$desc = sutter_meta_description();
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
	echo '<meta property="og:locale" content="nb_NO">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( is_front_page() ? home_url( '/' ) : get_permalink() ) );
	if ( is_singular() && has_post_thumbnail() ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( get_the_post_thumbnail_url( null, 'large' ) ) );
	}
	if ( is_front_page() ) {
		$ld = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'LocalBusiness',
			'name'        => 'Sutter Reklamefoto',
			'description' => 'Fotograf for bedrifter i Vestfold: foto, video og drone.',
			'url'         => home_url( '/' ),
			'telephone'   => sutter_opt( 'phone_href' ) ? str_replace( 'tel:', '', sutter_opt( 'phone_href' ) ) : '',
			'email'       => sutter_opt( 'email' ),
			'address'     => array( '@type' => 'PostalAddress', 'addressLocality' => 'Sandefjord', 'addressCountry' => 'NO' ),
			'areaServed'  => 'Vestfold',
			'founder'     => array( '@type' => 'Person', 'name' => 'Paul Sutter' ),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}, 3 );

/* Pages are edited in the simpler classic editor, with the page settings right below the text */
add_filter( 'use_block_editor_for_post_type', function ( $use, $type ) {
	return 'page' === $type ? false : $use;
}, 10, 2 );

/* No public author archives or comments on a portfolio site */
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
