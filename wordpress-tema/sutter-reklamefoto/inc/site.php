<?php
/**
 * Site hygiene: old addresses, search engines, security headers and the site icon.
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

/** Addresses from the old website and where they now belong. */
function sutter_old_addresses() {
	return array(
		'home'                             => '/',
		'om-paul-sutter'                   => '/pauls-historie/',
		'produktfoto'                      => '/produkter/',
		'webfoto'                          => '/interior-og-eksteriorfoto/',
		'matfoto'                          => '/mat/',
		'interiorfoto'                     => '/interior-og-eksteriorfoto/',
		'eksteriorfoto'                    => '/interior-og-eksteriorfoto/',
		'modellfoto'                       => '/modeller-og-portretter/',
		'diverse-foto'                     => '/diverse/',
		'siste-nytt'                       => '/',
		'priser'                           => '/kontakt/',
		'dronefoto'                        => '/droneopptak-ute-og-inne/',
		'drone-video'                      => '/droneopptak-ute-og-inne/',
		'bilder-til-den-store-vaffelboka'   => '/mat/',
		'bilder-til-den-store-vaffelboka-2' => '/mat/',
		'grunderstjerne-avdukning'         => '/snf-grunderiet/',
		'hjem'                             => '/',
	);
}

/* Old pages, blog posts, categories, author pages and search all lead to the right new page */
add_action( 'template_redirect', function () {
	if ( in_array( get_query_var( 'sitemap' ), array( 'users', 'taxonomies' ), true ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}
	if ( is_admin() || is_preview() || is_robots() || is_feed() || is_favicon() || get_query_var( 'sitemap' ) || get_query_var( 'sitemap-stylesheet' ) ) {
		return;
	}
	$map  = sutter_old_addresses();
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' ); // phpcs:ignore
	$slug = basename( $path );
	if ( '' !== $slug && isset( $map[ $slug ] ) && $path === $slug ) {
		wp_safe_redirect( home_url( $map[ $slug ] ), 301 );
		exit;
	}
	if ( is_author() || is_category() || is_tag() || is_date() || is_search() || ( is_home() && ! is_front_page() ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
	if ( is_singular( 'post' ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
	if ( is_attachment() ) {
		$parent = wp_get_post_parent_id( get_queried_object_id() );
		wp_safe_redirect( $parent ? get_permalink( $parent ) : home_url( '/' ), 301 );
		exit;
	}
}, 1 );

/* Search engine map: only the pages that are in use */
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return in_array( $name, array( 'users', 'taxonomies' ), true ) ? false : $provider;
}, 10, 2 );
add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	return array_intersect_key( $types, array( 'page' => true ) );
} );
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
	if ( 'page' !== $type ) {
		return $args;
	}
	$skip = array();
	foreach ( array_keys( sutter_old_addresses() ) as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			$skip[] = $p->ID;
		}
	}
	$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $skip );
	return $args;
}, 10, 2 );

/* User names are not shown to visitors */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );
add_filter( 'xmlrpc_enabled', '__return_false' );

/* Basic security headers */
add_action( 'send_headers', function () {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
} );

/* Site icon: the theme's own icon until one is chosen in Utseende → Tilpass → Nettstedsidentitet */
function sutter_site_icon_ok() {
	$id = (int) get_option( 'site_icon' );
	return $id && file_exists( (string) get_attached_file( $id ) );
}
add_action( 'wp_head', function () {
	if ( sutter_site_icon_ok() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( SUTTER_URI . '/assets/icon-32.png' ) );
	printf( '<link rel="icon" href="%s" sizes="192x192">' . "\n", esc_url( SUTTER_URI . '/assets/icon-512.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( SUTTER_URI . '/assets/icon-180.png' ) );
}, 4 );
add_action( 'init', function () {
	// A site icon whose file was deleted shows as a broken picture: drop it so the theme icon is used.
	if ( get_option( 'site_icon' ) && ! sutter_site_icon_ok() ) {
		delete_option( 'site_icon' );
	}
} );

/* og:site_name for link previews */
add_action( 'wp_head', function () {
	echo '<meta property="og:site_name" content="Sutter Reklamefoto">' . "\n";
}, 3 );

/* WordPress' global block styles are not used by this design */
add_action( 'after_setup_theme', function () {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
} );
