<?php
/**
 * Contact form: sends the message to Paul with wp_mail(), with quiet spam protection
 * (hidden field, time check, link limit and a limit per visitor). No puzzles for real people.
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

/** A signed time stamp placed in the form when the page is shown. */
function sutter_form_token() {
	$t = time();
	return $t . '.' . substr( hash_hmac( 'sha256', (string) $t, wp_salt( 'nonce' ) ), 0, 20 );
}

/** Seconds since the form was shown, or -1 if the token is missing or forged. */
function sutter_form_age( $token ) {
	$parts = explode( '.', (string) $token );
	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
		return -1;
	}
	$ok = hash_equals( substr( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), 0, 20 ), $parts[1] );
	return $ok ? time() - (int) $parts[0] : -1;
}

function sutter_handle_contact() {
	$back = isset( $_POST['tilbake'] ) ? esc_url_raw( wp_unslash( $_POST['tilbake'] ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.NonceVerification
	$back = wp_validate_redirect( $back, home_url( '/' ) );
	$done = function ( $ok ) use ( $back ) {
		wp_safe_redirect( add_query_arg( $ok ? 'sendt' : 'feil', '1', $back ) . '#skjema-status' );
		exit;
	};

	// phpcs:disable WordPress.Security.NonceVerification -- public form, protected by a signed time token instead of a nonce (pages may be cached)
	$name    = isset( $_POST['navn'] ) ? sanitize_text_field( wp_unslash( $_POST['navn'] ) ) : '';
	$email   = isset( $_POST['epost'] ) ? sanitize_email( wp_unslash( $_POST['epost'] ) ) : '';
	$message = isset( $_POST['melding'] ) ? sanitize_textarea_field( wp_unslash( $_POST['melding'] ) ) : '';
	$trap    = isset( $_POST['nettside'] ) ? trim( (string) wp_unslash( $_POST['nettside'] ) ) : '';
	$age     = sutter_form_age( isset( $_POST['sutter_t'] ) ? sanitize_text_field( wp_unslash( $_POST['sutter_t'] ) ) : '' );
	// phpcs:enable

	// Real people must fill in all fields
	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		$done( false );
	}

	// Signs of a robot: hidden field filled in, sent faster than a person can type, old or forged form,
	// many links, or very long text. Robots are told it went well, so they do not try again.
	$links = preg_match_all( '#https?://|www\.#i', $message );
	if ( '' !== $trap || $age < 4 || $age > 7 * DAY_IN_SECONDS || $links > 2 || mb_strlen( $message ) > 5000 || mb_strlen( $name ) > 100 ) {
		$done( true );
	}

	// At most 5 messages per hour from the same visitor
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'sutter_rl_' . md5( $ip . wp_salt( 'nonce' ) );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		$done( true );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	$to      = sutter_opt( 'form_to' ) ? sutter_opt( 'form_to' ) : get_option( 'admin_email' );
	$subject = 'Melding fra nettsiden: ' . $name;
	$body    = "Navn: $name\nE-post: $email\n\n$message\n\n--\nSendt fra kontaktskjemaet på " . home_url( '/' );
	$headers = array( 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>' ), '', $name ) . ' <' . $email . '>' );
	$done( wp_mail( $to, $subject, $body, $headers ) );
}
add_action( 'admin_post_nopriv_sutter_kontakt', 'sutter_handle_contact' );
add_action( 'admin_post_sutter_kontakt', 'sutter_handle_contact' );
