<?php
/**
 * One-time setup: fetches the pictures, films and logos and creates the pages, menu and front page.
 * Utseende → Sutter-oppsett. Safe to run again: existing files and pages are reused and updated.
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SUTTER_SEED_BASE' ) ) {
	define( 'SUTTER_SEED_BASE', 'https://wiggo-hub.github.io/sutter-designforslag/v3/' );
}

function sutter_seed() {
	static $seed = null;
	if ( null === $seed ) {
		$seed = json_decode( file_get_contents( SUTTER_DIR . '/inc/seed.json' ), true );
	}
	return $seed;
}

add_action( 'admin_menu', function () {
	add_theme_page( 'Sutter-oppsett', 'Sutter-oppsett', 'manage_options', 'sutter-oppsett', 'sutter_setup_page' );
} );

/* Point to the setup page right after the theme is activated */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'sutter_setup_done' ) ) {
		set_transient( 'sutter_setup_notice', 1, DAY_IN_SECONDS );
	}
} );
add_action( 'admin_notices', function () {
	if ( get_transient( 'sutter_setup_notice' ) && ! get_option( 'sutter_setup_done' ) && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-info"><p><strong>Sutter Reklamefoto:</strong> <a href="' . esc_url( admin_url( 'themes.php?page=sutter-oppsett' ) ) . '">Kjør oppsettet</a> for å hente bilder og opprette sidene.</p></div>';
	}
} );

function sutter_setup_page() {
	$seed  = sutter_seed();
	$total = count( $seed['files'] ) + 1;
	$done  = get_option( 'sutter_setup_done' );
	?>
	<div class="wrap">
	<h1>Sutter-oppsett</h1>
	<p style="max-width:720px">Oppsettet henter <?php echo (int) count( $seed['files'] ); ?> bilder, filmer og logoer til mediebiblioteket, og oppretter forsiden, de åtte kategorisidene, kontaktsiden og menyen. Det tar noen minutter. La siden stå åpen til det er ferdig.</p>
	<?php if ( $done ) : ?>
		<p><strong>Oppsettet er allerede kjørt.</strong> Kjører du det igjen, blir tekstene, fargene og bildevalget på sidene satt tilbake til utgangspunktet. Filer som allerede er hentet, hentes ikke på nytt.</p>
	<?php endif; ?>
	<p><button type="button" class="button button-primary" id="sutter-start"><?php echo $done ? 'Kjør oppsettet på nytt' : 'Start oppsettet'; ?></button></p>
	<div style="max-width:720px;background:#dcdcde;height:12px;border-radius:6px;overflow:hidden"><div id="sutter-bar" style="width:0;height:100%;background:#2271b1"></div></div>
	<p id="sutter-status" role="status" aria-live="polite"></p>
	<ul id="sutter-log" style="max-height:260px;overflow:auto;font-size:12px"></ul>
	<h2>Mapper i mediebiblioteket</h2>
	<?php if ( sutter_filebird_active() ) : ?>
		<p style="max-width:720px">FileBird er installert. Oppsettet legger bildene i én mappe per side. Har du installert FileBird etter at oppsettet ble kjørt, kan du sortere bildene her uten å endre noe annet.</p>
		<?php if ( isset( $_GET['mapper'] ) ) { echo '<p><strong>' . (int) $_GET['mapper'] . ' filer er lagt i mapper.</strong></p>'; } // phpcs:ignore ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="sutter_folders">
			<?php wp_nonce_field( 'sutter_folders' ); ?>
			<?php submit_button( 'Sorter bildene i mapper', 'secondary', 'submit', false ); ?>
		</form>
	<?php else : ?>
		<p style="max-width:720px">Installer tillegget FileBird Lite hvis du vil ha bildene sortert i mapper. Oppsettet lager da mappene automatisk.</p>
	<?php endif; ?>
	</div>
	<script>
	(function(){
	  var total=<?php echo (int) $total; ?>, nonce='<?php echo esc_js( wp_create_nonce( 'sutter_setup' ) ); ?>', btn=document.getElementById('sutter-start');
	  var bar=document.getElementById('sutter-bar'), st=document.getElementById('sutter-status'), log=document.getElementById('sutter-log');
	  function step(i,tries){
	    var fd=new FormData(); fd.append('action','sutter_setup_step'); fd.append('i',i); fd.append('_ajax_nonce',nonce);
	    fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){
	      if(!r.success){ throw new Error(r.data||'ukjent feil'); }
	      var li=document.createElement('li'); li.textContent=r.data.msg; log.prepend(li);
	      bar.style.width=Math.round((i+1)/total*100)+'%'; st.textContent='Steg '+(i+1)+' av '+total;
	      if(i+1<total){ step(i+1,0); } else { st.innerHTML='<strong>Ferdig!</strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Se nettsiden</a>'; btn.disabled=false; }
	    }).catch(function(e){
	      if(tries<2){ setTimeout(function(){ step(i,tries+1); },2000); return; }
	      st.textContent='Stoppet på steg '+(i+1)+': '+e.message+'. Trykk på knappen for å fortsette.'; btn.disabled=false; btn.dataset.from=i;
	    });
	  }
	  btn.addEventListener('click',function(){ btn.disabled=true; step(+(btn.dataset.from||0),0); });
	})();
	</script>
	<?php
}

add_action( 'wp_ajax_sutter_setup_step', function () {
	check_ajax_referer( 'sutter_setup' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Mangler tilgang' );
	}
	@set_time_limit( 300 ); // phpcs:ignore
	$seed = sutter_seed();
	$i    = isset( $_POST['i'] ) ? absint( $_POST['i'] ) : 0;
	if ( $i < count( $seed['files'] ) ) {
		$r = sutter_setup_file( $seed['files'][ $i ] );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( $seed['files'][ $i ]['key'] . ': ' . $r->get_error_message() );
		}
		wp_send_json_success( array( 'msg' => $r ) );
	}
	sutter_setup_content();
	update_option( 'sutter_setup_done', time() );
	delete_transient( 'sutter_setup_notice' );
	wp_send_json_success( array( 'msg' => 'Sider, meny, forside og logoer er satt opp.' ) );
} );

/** Attachment ID for a seed file key, if it is already in the media library. */
function sutter_media_id( $key ) {
	$map = get_option( 'sutter_setup_map', array() );
	if ( ! empty( $map[ $key ] ) && get_post( $map[ $key ] ) ) {
		return (int) $map[ $key ];
	}
	return 0;
}

function sutter_setup_file( $f ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$id = sutter_media_id( $f['key'] );
	if ( ! $id ) {
		$tmp = download_url( SUTTER_SEED_BASE . $f['path'], 300 );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}
		$id = media_handle_sideload( array( 'name' => ! empty( $f['name'] ) ? $f['name'] : $f['key'], 'tmp_name' => $tmp ), 0, $f['title'] );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore
			return $id;
		}
		$map              = get_option( 'sutter_setup_map', array() );
		$map[ $f['key'] ] = $id;
		update_option( 'sutter_setup_map', $map, false );
		$msg = 'Hentet ' . $f['key'];
	} else {
		$msg = 'Fantes fra før: ' . $f['key'];
	}
	wp_update_post( array( 'ID' => $id, 'post_title' => $f['title'], 'post_excerpt' => $f['caption'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $f['alt'] );
	if ( ! empty( $f['poster'] ) && sutter_media_id( $f['poster'] ) ) {
		set_post_thumbnail( $id, sutter_media_id( $f['poster'] ) );
	}
	return $msg;
}

function sutter_blocks( $paras ) {
	$out = '';
	foreach ( (array) $paras as $p ) {
		$out .= "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	}
	return trim( $out );
}

function sutter_upsert_page( $slug, $title, $content, $template, $order ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$args     = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
		'menu_order'   => $order,
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $args ) );
	} else {
		$id = wp_insert_post( wp_slash( $args ) );
	}
	update_post_meta( $id, '_wp_page_template', $template );
	return $id;
}

function sutter_setup_content() {
	$seed = sutter_seed();

	// Front page
	$front = sutter_upsert_page( 'forside', $seed['front']['title'], '', 'default', 0 );
	if ( sutter_media_id( $seed['front']['hero'] ) ) {
		set_post_thumbnail( $front, sutter_media_id( $seed['front']['hero'] ) );
	}
	update_post_meta( $front, '_sutter_seo_desc', $seed['front']['desc'] );

	// Category pages
	$menu_pages = array();
	foreach ( $seed['pages'] as $n => $p ) {
		$id = sutter_upsert_page( $p['slug'], $p['title'], sutter_blocks( $p['text'] ), 'templates/kategori.php', $n + 1 );
		if ( sutter_media_id( $p['hero'] ) ) {
			set_post_thumbnail( $id, sutter_media_id( $p['hero'] ) );
		}
		$g = array();
		foreach ( $p['gallery'] as $k ) {
			if ( sutter_media_id( $k ) ) {
				$g[] = sutter_media_id( $k );
			}
		}
		update_post_meta( $id, '_sutter_gallery', implode( ',', $g ) );
		update_post_meta( $id, '_sutter_accent', $p['accent'] );
		update_post_meta( $id, '_sutter_contain', $p['contain'] ? '1' : '' );
		update_post_meta( $id, '_sutter_topband', $p['topband'] ? '1' : '' );
		update_post_meta( $id, '_sutter_seo_title', $p['seo_title'] );
		update_post_meta( $id, '_sutter_seo_desc', $p['desc'] );
		$menu_pages[] = array( $id, $p['title'] );
	}

	// Contact page
	$c   = $seed['contact'];
	$cid = sutter_upsert_page( $c['slug'], $c['title'], sutter_blocks( array( $c['text'] ) ), 'templates/kontakt.php', 20 );
	if ( sutter_media_id( $c['image'] ) ) {
		set_post_thumbnail( $cid, sutter_media_id( $c['image'] ) );
	}
	update_post_meta( $cid, '_sutter_seo_title', $c['seo_title'] );
	update_post_meta( $cid, '_sutter_seo_desc', $c['desc'] );
	$menu_pages[] = array( $cid, $c['menu'] );

	// Menu (used both at the top and in the footer)
	$menu = wp_get_nav_menu_object( 'Hovedmeny' );
	$mid  = $menu ? $menu->term_id : wp_create_nav_menu( 'Hovedmeny' );
	foreach ( (array) wp_get_nav_menu_items( $mid ) as $it ) {
		wp_delete_post( $it->ID, true );
	}
	foreach ( $menu_pages as $pos => $mp ) {
		wp_update_nav_menu_item( $mid, 0, array(
			'menu-item-title'     => $mp[1],
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $mp[0],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $pos + 1,
		) );
	}
	$loc              = get_theme_mod( 'nav_menu_locations', array() );
	$loc['hovedmeny'] = $mid;
	$loc['bunnmeny']  = $mid;
	set_theme_mod( 'nav_menu_locations', $loc );

	// Logos
	$logos = array();
	foreach ( $seed['logos'] as $k ) {
		if ( sutter_media_id( $k ) ) {
			$logos[] = sutter_media_id( $k );
		}
	}
	update_option( 'sutter_logos', implode( ',', $logos ) );

	// Site settings
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front );
	update_option( 'blogname', 'Sutter Reklamefoto' );
	update_option( 'blogdescription', 'Bedriftsfotograf i Sandefjord og Vestfold' );
	update_option( 'timezone_string', 'Europe/Oslo' );
	update_option( 'date_format', 'j. F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();

	// Media folders, when FileBird is installed
	sutter_setup_folders();

	// Remove the WordPress sample content
	foreach ( array( 'sample-page', 'eksempelside' ) as $s ) {
		$p = get_page_by_path( $s );
		if ( $p ) {
			wp_trash_post( $p->ID );
		}
	}
	$hello = get_posts( array( 'post_type' => 'post', 'name' => 'hello-world', 'posts_per_page' => 1 ) );
	if ( ! $hello ) {
		$hello = get_posts( array( 'post_type' => 'post', 'name' => 'hei-verden', 'posts_per_page' => 1 ) );
	}
	if ( $hello ) {
		wp_trash_post( $hello[0]->ID );
	}
}

/* ---------- FileBird folders ---------- */
function sutter_filebird_active() {
	global $wpdb;
	$active = false;
	foreach ( (array) get_option( 'active_plugins', array() ) as $pl ) {
		if ( 0 === strpos( $pl, 'filebird' ) ) {
			$active = true;
		}
	}
	if ( ! $active ) {
		return false;
	}
	$t = $wpdb->prefix . 'fbv';
	return $t === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) );
}

/** Find or create a top-level FileBird folder and return its id. */
function sutter_fb_folder( $name, $ord ) {
	global $wpdb;
	$t  = $wpdb->prefix . 'fbv';
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t} WHERE name = %s AND parent = 0 AND created_by = 0 LIMIT 1", $name ) ); // phpcs:ignore
	if ( $id ) {
		return (int) $id;
	}
	$wpdb->insert( $t, array( 'name' => $name, 'parent' => 0, 'type' => 0, 'ord' => $ord, 'created_by' => 0 ) );
	return (int) $wpdb->insert_id;
}

/** Put the files from the setup into one folder per page. Returns the number of files placed. */
function sutter_setup_folders() {
	global $wpdb;
	if ( ! sutter_filebird_active() ) {
		return 0;
	}
	$seed   = sutter_seed();
	$groups = array( 'Forside og kontakt' => array( $seed['front']['hero'], $seed['contact']['image'] ) );
	foreach ( $seed['pages'] as $p ) {
		$groups[ sutter_plain_title( $p['title'] ) ] = array_merge( array( $p['hero'] ), $p['gallery'] );
	}
	$groups['Kundelogoer'] = $seed['logos'];
	// Film posters go in the same folder as their film
	$posters = array();
	foreach ( $seed['files'] as $f ) {
		if ( ! empty( $f['poster'] ) ) {
			$posters[ $f['key'] ] = $f['poster'];
		}
	}
	$rel   = $wpdb->prefix . 'fbv_attachment_folder';
	$count = 0;
	$ord   = 0;
	foreach ( $groups as $name => $keys ) {
		$folder = sutter_fb_folder( $name, $ord++ );
		if ( ! $folder ) {
			continue;
		}
		foreach ( $keys as $k ) {
			foreach ( array_filter( array( $k, isset( $posters[ $k ] ) ? $posters[ $k ] : '' ) ) as $kk ) {
				$aid = sutter_media_id( $kk );
				if ( ! $aid ) {
					continue;
				}
				$wpdb->delete( $rel, array( 'attachment_id' => $aid ) );
				$wpdb->insert( $rel, array( 'folder_id' => $folder, 'attachment_id' => $aid ) );
				$count++;
			}
		}
	}
	return $count;
}

add_action( 'admin_post_sutter_folders', function () {
	check_admin_referer( 'sutter_folders' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Mangler tilgang' );
	}
	$n = sutter_setup_folders();
	wp_safe_redirect( admin_url( 'themes.php?page=sutter-oppsett&mapper=' . $n ) );
	exit;
} );

/** Put every file that is not in a FileBird folder into «Gammel nettside». Returns the number moved. */
function sutter_folder_unsorted( $name = 'Gammel nettside' ) {
	global $wpdb;
	if ( ! sutter_filebird_active() ) {
		return 0;
	}
	$folder = sutter_fb_folder( $name, 99 );
	$rel    = $wpdb->prefix . 'fbv_attachment_folder';
	$ids    = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} p WHERE p.post_type = 'attachment' AND NOT EXISTS ( SELECT 1 FROM {$rel} r WHERE r.attachment_id = p.ID )" ); // phpcs:ignore
	foreach ( $ids as $aid ) {
		$wpdb->insert( $rel, array( 'folder_id' => $folder, 'attachment_id' => (int) $aid ) );
	}
	return count( $ids );
}

add_action( 'admin_post_sutter_folder_unsorted', function () {
	check_admin_referer( 'sutter_folders' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Mangler tilgang' );
	}
	$n = sutter_folder_unsorted();
	wp_safe_redirect( admin_url( 'themes.php?page=sutter-oppsett&mapper=' . $n ) );
	exit;
} );
