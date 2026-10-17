<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#innhold">Hopp til innhold</a>
<header class="bar">
  <div class="wrap">
    <a class="home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="sr-only">Sutter Reklamefoto – til forsiden</span><?php echo sutter_logo(); // phpcs:ignore -- static SVG from the theme ?></a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="hovedmeny">Meny</button>
    <nav class="nav" id="hovedmeny" aria-label="Hovedmeny"><ul><?php
	$cur = get_queried_object_id();
	foreach ( sutter_menu_items( 'hovedmeny' ) as $it ) {
		printf(
			'<li><a href="%s"%s>%s</a></li>',
			esc_url( $it['url'] ),
			( $it['id'] && $it['id'] === $cur ) ? ' aria-current="page"' : '',
			sutter_menu_label( $it['title'] ) // escaped inside
		);
	}
	?></ul></nav>
  </div>
</header>
