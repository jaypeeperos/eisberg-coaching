<?php
/**
 * Customer failed order email (Eisberg override, German "du" wording)
 *
 * Overrides woocommerce/templates/emails/customer-failed-order.php.
 * Subject and heading are set in WooCommerce → Settings → Emails → Failed order (Customer).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' )
	&& \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'email_improvements' );

$eisberg_shop_link  = '<a href="' . esc_url( home_url( '/' ) ) . '">Eisberg Coaching</a>';
$eisberg_mail_link  = '<a href="mailto:team@eisberg-coaching.com">team@eisberg-coaching.com</a>';

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
<p style="margin-top: 24px;">
<?php
if ( ! empty( $order->get_billing_first_name() ) ) {
	printf( 'Hallo %s,', esc_html( $order->get_billing_first_name() ) );
} else {
	echo 'Hallo,';
}
?>
</p>
<p>Wegen eines Problems mit der von dir gewählten Zahlungsart konnte deine Bestellung nicht durchgeführt werden.</p>
<p>Wenn du den Kauf fortsetzen möchtest, kehre bitte zu <?php echo wp_kses_post( $eisberg_shop_link ); ?> zurück und versuche es mit einer anderen Zahlungsart.</p>
<p>Hier die Details zu deiner Bestellung:</p>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
?>

<?php echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">' : ''; ?>
<p>Wenn du Hilfe bei deiner Bestellung benötigst, kannst du uns jederzeit unter <?php echo wp_kses_post( $eisberg_mail_link ); ?> kontaktieren.</p>
<?php
/**
 * Optional extra text from WooCommerce → Settings → Emails → Failed order → "Additional content".
 * Leave that field empty to avoid repeating the contact line above.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}
echo $email_improvements_enabled ? '</td></tr></table>' : '';

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
