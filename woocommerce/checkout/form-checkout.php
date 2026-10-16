<?php
/**
 * Checkout Form - Eisberg custom layout
 * Order summary on the right side
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="eisberg-checkout-grid">

			<!-- LEFT COLUMN: Billing + Additional -->
			<div class="eisberg-checkout-left">
				<div id="customer_details">
					<div class="woocommerce-billing-fields">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>

					<div class="woocommerce-shipping-fields">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>

					<div class="woocommerce-additional-fields">
						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
					</div>
				</div>
			</div>

			<!-- RIGHT COLUMN: Order Summary (sticky) -->
			<div class="eisberg-checkout-right">
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
				
				<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
				
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			</div>

		</div>

	<?php endif; ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>