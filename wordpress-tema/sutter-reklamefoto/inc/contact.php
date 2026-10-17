<?php
/**
 * Contact form: sends the message to Paul with wp_mail().
 *
 * @author Jan Wiggo Wiger
 */

defined( 'ABSPATH' ) || exit;

function sutter_handle_contact() {
	$back = isset( $_POST['tilbake'] ) ? esc_url_raw( wp_unslash( $_POST['tilbake'] ) ) : home_url( '/' );
	$back = wp_validate_redirect( $back, home_url( '/' ) );

	if ( ! isset( $_POST['sutter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sutter_nonce'] ) ), 'sutter_kontakt' ) ) {
		wp_safe_redirect( add_query_arg( 'feil', '1', $back ) . '#skjema-status' );
		exit;
	}
	// Robots fill in the hidden field: pretend all went well.
	if ( ! empty( $_POST['nettside'] ) ) {
		wp_safe_redirect( add_query_arg( 'sendt', '1', $back ) . '#skjema-status' );
		exit;
	}
	$name    = isset( $_POST['navn'] ) ? sanitize_text_field( wp_unslash( $_POST['navn'] ) ) : '';
	$email   = isset( $_POST['epost'] ) ? sanitize_email( wp_unslash( $_POST['epost'] ) ) : '';
	$message = isset( $_POST['melding'] ) ? sanitize_textarea_field( wp_unslash( $_POST['melding'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		wp_safe_redirect( add_query_arg( 'feil', '1', $back ) . '#skjema-status' );
		exit;
	}

	$to      = sutter_opt( 'form_to' ) ? sutter_opt( 'form_to' ) : get_option( 'admin_email' );
	$subject = 'Melding fra nettsiden: ' . $name;
	$body    = "Navn: $name\nE-post: $email\n\n$message\n\n--\nSendt fra kontaktskjemaet på " . home_url( '/' );
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$ok      = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( $ok ? 'sendt' : 'feil', '1', $back ) . '#skjema-status' );
	exit;
}
add_action( 'admin_post_nopriv_sutter_kontakt', 'sutter_handle_contact' );
add_action( 'admin_post_sutter_kontakt', 'sutter_handle_contact' );
