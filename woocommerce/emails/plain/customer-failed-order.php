<?php
/**
 * Customer failed order email (plain text) – Eisberg override, German "du" wording
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails\Plain
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( ! empty( $order->get_billing_first_name() ) ) {
	printf( "Hallo %s,\n\n", esc_html( $order->get_billing_first_name() ) );
} else {
	echo "Hallo,\n\n";
}

echo "Wegen eines Problems mit der von dir gewählten Zahlungsart konnte deine Bestellung nicht durchgeführt werden.\n\n";
echo 'Wenn du den Kauf fortsetzen möchtest, kehre bitte zu Eisberg Coaching (' . esc_url( home_url( '/' ) ) . ") zurück und versuche es mit einer anderen Zahlungsart.\n\n";
echo "Hier die Details zu deiner Bestellung:\n\n";

echo "\n----------------------------------------\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------------------------------------\n\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n----------------------------------------\n\n";

echo "Wenn du Hilfe bei deiner Bestellung benötigst, kannst du uns jederzeit unter team@eisberg-coaching.com kontaktieren.\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
