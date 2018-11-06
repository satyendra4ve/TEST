<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure WooCommerce is active

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}
/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + Wallet gateway
 */
function mwb_wgm_wallet_gateway( $gateways ) 
{
	$gateways[] = 'WC_Wallet_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'mwb_wgm_wallet_gateway' );

/**
 * Wallet Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Wallet_Gateway
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		makewebbetter
*/

function mwb_wgm_wallet_gateway_init()
{
	class WC_Wallet_Gateway extends WC_Payment_Gateway {
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			 
			$this->id                 = 'wallet_gateway';
			$this->icon               = apply_filters('woocommerce_wallet_gateway_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'Wallet Payment', 'woocommerce-refund-and-exchange' );
			$this->method_description = __( 'This payment method is used for user who want to make payment from their Wallet.', 'woocommerce-refund-and-exchange' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		}


		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
			 
			$this->form_fields = apply_filters( 'wc_wallet_gateway_form_fields', array(

					'enabled' => array(
							'title'   => __( 'Enable/Disable', 'woocommerce-refund-and-exchange' ),
							'type'    => 'checkbox',
							'label'   => __( 'Enable Wallet Payment', 'woocommerce-refund-and-exchange' ),
							'default' => 'yes'
					),

					'title' => array(
							'title'       => __( 'Title', 'woocommerce-refund-and-exchange' ),
							'type'        => 'text',
							'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'woocommerce-refund-and-exchange' ),
							'default'     => __( 'Wallet Payment', 'woocommerce-refund-and-exchange' ),
							'desc_tip'    => true,
					),

					'description' => array(
							'title'       => __( 'Description', 'woocommerce-refund-and-exchange' ),
							'type'        => 'textarea',
							'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-refund-and-exchange' ),
							'default'     => __( 'Your amount is deducted from your wallet.', 'woocommerce-refund-and-exchange' ),
							'desc_tip'    => true,
					),

					'instructions' => array(
							'title'       => __( 'Instructions', 'woocommerce-refund-and-exchange' ),
							'type'        => 'textarea',
							'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce-refund-and-exchange' ),
							'default'     => '',
							'desc_tip'    => true,
					),
			) );
		}

		
		public function get_icon() 
		{
			$customer_id = get_current_user_id();
			if($customer_id > 0)
			{
				$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );
				if(!empty($walletcoupon) && isset($walletcoupon))
				{
					$the_coupon = new WC_Coupon( $walletcoupon );
					if( WC()->version < '3.0.0' )
					{
						if(isset($the_coupon->id))
						{
							$coupon_id = $the_coupon->id;
							$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
							return "<b>".__('[Your Amount :', 'woocommerce-refund-and-exchange')." ".wc_price($amount)."]"."</b>";
						}	

					}else{
						if($the_coupon->get_id() != '')
						{
							$coupon_id = $the_coupon->get_id();
							$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
							return "<b>".__('[Your Amount :', 'woocommerce-refund-and-exchange')." ".wc_price($amount)."]"."</b>";
						}
					}
				}	
			}	
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}

		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			
			$order = wc_get_order( $order_id );
			$order_total = $order->get_total();
			$customer_id = get_current_user_id();
			if($customer_id > 0)
			{
				$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );	
				if(!empty($walletcoupon) && isset($walletcoupon))
				{
					$the_coupon = new WC_Coupon( $walletcoupon );
					if(isset($the_coupon->id))
					{
						$coupon_id = $the_coupon->id;
						$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
						if($order_total > $amount)
						{
							$remaining_amount = 0;
						}	
						else
						{
							$remaining_amount = $amount - $order_total;
						}	
						update_post_meta( $coupon_id, 'coupon_amount', $remaining_amount );
					}
				}
			}

			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'processing', __( 'Awaiting Wallet payment', 'woocommerce-refund-and-exchange' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
					
			// Remove cart
			WC()->cart->empty_cart();
				
			// Return thankyou redirect
			return array(
					'result' 	=> 'success',
					'redirect'	=> $this->get_return_url( $order )
			);
		}
	} 
}
add_action( 'plugins_loaded', 'mwb_wgm_wallet_gateway_init', 11 );
?>