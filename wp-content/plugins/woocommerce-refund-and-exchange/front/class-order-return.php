<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CED_rnx_order_return
 */
if( !class_exists( 'CED_rnx_order_return' ) ){

	/**
	 * This is class for managing return process at front end.
	 *
	 * @name    CED_rnx_order_return
	 * @category Class
	 * @author   makewebbetter <webmaster@makewebbetter.com>
	 */
	class CED_rnx_order_return{
		
		/**
		 * Construct of class
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		
		public function __construct(){
			
			add_action('plugins_loaded',array($this,'mwb_wpr_load_woocommerce'));    
      	}

		function mwb_wpr_load_woocommerce()
        {
            if(function_exists('WC'))
            {
                
                 $this->add_hooks_and_filters();
            }
        }

		function add_hooks_and_filters()
		{
			add_action( 'woocommerce_order_details_after_order_table',array($this, 'ced_rnx_order_return_button'));
			add_action( 'wp_ajax_ced_rnx_return_upload_files', array($this, 'ced_rnx_order_return_attach_files'));
			add_action( 'wp_ajax_nopriv_ced_rnx_return_upload_files', array($this, 'ced_rnx_order_return_attach_files'));
			add_action( 'wp_ajax_ced_rnx_return_product_info', array($this, 'ced_rnx_return_product_info_callback'));
			add_action( 'wp_ajax_nopriv_ced_rnx_return_product_info', array($this, 'ced_rnx_return_product_info_callback'));
			if( WC()->version < "3.0.0" )
			{
				add_action( 'woocommerce_order_add_coupon', array ( $this, 'ced_rnx_woocommerce_order_add_coupon' ), 10, 5 );
			}
			else
			{
				add_action( 'woocommerce_new_order_item', array ( $this, 'ced_rnx_woocommerce_order_add_coupon' ), 10, 3 );
			}
			add_action( 'woocommerce_after_checkout_validation', array ( $this, 'ced_rnx_woocommerce_after_checkout_validation' ));
			add_filter( 'woocommerce_available_payment_gateways', array ( $this, 'ced_rnx_woocommerce_available_payment_gateways'), 5, 1 );
			add_action( 'woocommerce_before_pay_action' , array( $this, 'ced_rnx_pay_order_validation' ),10 );
			add_action( 'wp_ajax_ced_rnx_calculate_price_deduct_on_return' , array( $this, 'ced_rnx_calculate_price_deduct_on_return' ) );
			add_action( 'wp_ajax_nopriv_ced_rnx_calculate_price_deduct_on_return' , array( $this, 'ced_rnx_calculate_price_deduct_on_return' ) );

		}

		
		function ced_rnx_calculate_price_deduct_on_return()
		{
			$product_qty = $_POST['product_qty'];
			$product_total = $_POST['product_total'];
			$ced_rnx_enable_price_policy = get_option( 'ced_rnx_enable_price_policy', 'no' );
			if ( $ced_rnx_enable_price_policy == 'no' ) {
				echo $product_total;wp_die();
			}
			$ced_rnx_price_policy_array=array();
			$ced_rnx_number_of_days = get_option( 'ced_rnx_number_of_days', array() );
			$ced_rnx_price_reduced = get_option( 'ced_rnx_price_reduced', array() );
			foreach ($ced_rnx_number_of_days as $key => $value) {
				foreach ($ced_rnx_price_reduced as $key1 => $value1) {
					if($key1===$key)
					{
						$ced_rnx_price_policy_array[$value]=$value1;
					}
				}
			}
			ksort($ced_rnx_price_policy_array);
			$order_id = $_POST['order_id'];
			if ( !empty( $ced_rnx_number_of_days ) ) {
				$order = wc_get_order($order_id);
				$order_date = $order->order_date;
				$order_date = strtotime( $order_date );
				$current_date = strtotime( current_time('Y-m-d h:i:s') );
				$date_dif = $current_date - $order_date;
				$date_dif = floor($date_dif/(60*60*24));
				foreach ($ced_rnx_price_policy_array as $key => $value) {
					if ($date_dif > $key) {
						continue;
					}else{
						$product_total = $product_total - $product_total*$value/100;
						break;
					}
				}
			}
			echo $product_total;
			wp_die();
		}
		
		/**
		 * Manage wallet payment gateway avaliability
		 *
		 * @name ced_rnx_woocommerce_available_payment_gateways
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_woocommerce_available_payment_gateways($payment_gateways)
		{
			if(ced_rnx_wallet_feature_enable())
			{
				$customer_id = get_current_user_id();
				if($customer_id > 0)
				{
					$customer_coupon_id = 0;
					$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );
					if(!empty($walletcoupon) && isset($walletcoupon))
					{
						$the_coupon = new WC_Coupon( $walletcoupon );
						
						$customer_coupon_id=$the_coupon->get_id();
						if(isset($customer_coupon_id))
						{
							$amount = get_post_meta( $customer_coupon_id, 'coupon_amount', true );
							if($amount <= 0)
							{
								if(isset($payment_gateways['wallet_gateway']))
								{
									unset($payment_gateways['wallet_gateway']);
									return $payment_gateways;
								}
							}	
						}
						if(isset(WC()->cart) && !empty(WC()->cart))
						{
							$applied_coupon_ids = array();
							$applied_coupons = WC()->cart->applied_coupons;
							foreach($applied_coupons as $applied_coupon)
							{
								$the_coupon = new WC_Coupon( $applied_coupon );
								$coupon_id=$the_coupon->get_id();
								if(isset($coupon_id))
								{
									$applied_coupon_ids[] = $coupon_id;
								}
							}	
						}
				
						if(in_array($customer_coupon_id, $applied_coupon_ids))
						{
							if(isset($payment_gateways['wallet_gateway']))
							{
								unset($payment_gateways['wallet_gateway']);
							}
						}
					}	
					else 
					{
						if(isset($payment_gateways['wallet_gateway']))
						{
							unset($payment_gateways['wallet_gateway']);
						}
					}		
				}
				else
				{
					if(isset($payment_gateways['wallet_gateway']))
					{
						unset($payment_gateways['wallet_gateway']);
					}	
				}
			}
			else 
			{
				if(isset($payment_gateways['wallet_gateway']))
				{
					unset($payment_gateways['wallet_gateway']);
				}
			}		
			return $payment_gateways;
		}

		/**
		 * This function is used to validate the wallet amount in respect to cart total
		 * 
		 * @name ced_rnx_woocommerce_after_checkout_validation
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_woocommerce_after_checkout_validation($posted)
		{
			if(ced_rnx_wallet_feature_enable())
			{
				if(isset($posted['payment_method']))
				{
					$payment_type = $posted['payment_method'];
					if($payment_type == "wallet_gateway")
					{
						$customer_id = get_current_user_id();
						if($customer_id > 0)
						{
							global $woocommerce;
							$carttotal = floatval( preg_replace( '#[^\d.]#', '', $woocommerce->cart->total) );
							$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );
							if(!empty($walletcoupon) && isset($walletcoupon))
							{
								$the_coupon = new WC_Coupon( $walletcoupon );
 								$coupon_id=$the_coupon->get_id();
								if(isset($coupon_id))
								{
									$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
									if($carttotal > $amount)
									{
										wc_add_notice( sprintf(__( "Your Wallet doesn't have Sufficient amount to place order. For using Wallet amount use Coupon Code : %s %s %s", 'woocommerce-refund-and-exchange' ),"<b>", $walletcoupon,"</b>"), 'error' );
									}	
								}
							}
						}
					}	
				}
			}	
		}
		
		/**
		 * This function is used to validate the wallet amount in respect to cart total on the pay order page
		 * 
		 * @name ced_rnx_woocommerce_after_checkout_validation
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_pay_order_validation($posted)
		{
			if(ced_rnx_wallet_feature_enable())
			{
				if($_POST['payment_method'])
				{
					$payment_type = $_POST['payment_method'];
					if($payment_type == "wallet_gateway")
					{
						$customer_id = get_current_user_id();
						if($customer_id > 0)
						{
							// global $woocommerce;
							// $carttotal = floatval( preg_replace( '#[^\d.]#', '', $woocommerce->cart->get_cart_total() ) );
							$cart_total = get_post_meta( $posted->id , '_order_total', true );
							$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );
							if(!empty($walletcoupon) && isset($walletcoupon))
							{
								$the_coupon = new WC_Coupon( $walletcoupon );
 								if( WC()->version < "3.0.0" )
 								{
 									$coupon_id=$the_coupon->id;
 								}
 								else
 								{
 									$coupon_id=$the_coupon->get_id();
 								}
								if(isset($coupon_id))
								{
									$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
									if($cart_total > $amount)
									{
										wc_add_notice( sprintf(__( "Your Wallet doesn't have Sufficient amount to place order. Please Select any other payment method", 'woocommerce' )), 'error' );
										return;
									}	
								}
							}
						}
					}	
				}
			}	
		}

		/**
		 * This function is to update wallet coupon amount
		 *
		 * @name ced_rnx_woocommerce_order_add_coupon
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_woocommerce_order_add_coupon($order_id, $item_id, $coupon_code)
		{
			if(ced_rnx_wallet_feature_enable())
			{
				$the_coupon = new WC_Coupon( $coupon_code );
 				$coupon_id=$the_coupon->get_id();
				if(isset($coupon_id))
				{
					$rnx_coupon = get_post_meta( $coupon_id, 'rnxwallet', true );
					if($rnx_coupon)
					{	
						$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
						$remaining_amount = $amount - $discount_amount;
						update_post_meta( $coupon_id, 'coupon_amount', $remaining_amount );
					}
				}
			}
		}
		
		/**
		 * This function is to save return request
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_return_product_info_callback()
		{
			$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
			if ( $check_ajax ) 
			{
				$current_user = wp_get_current_user();
				$user_email = $current_user->user_email;
				$user_name = $current_user->display_name;
				$order_id = $_POST['orderid'];
				$subject = $_POST['subject'];
				$reason = $_POST['reason'];
				$ced_rnx_refund_method = $_POST['refund_method'];

				update_post_meta($order_id,'ced_rnx_refund_method' ,$ced_rnx_refund_method);

				$products = get_post_meta($order_id, 'ced_rnx_return_product', true);
				$pending = true;
				if(isset($products) && !empty($products))
				{
					foreach($products as $date=>$product)
					{
						if($product['status'] == 'pending')
						{
							$products[$date] = $_POST;
							$products[$date]['status'] = 'pending'; //update requested products
							$pending = false;
							break;
						}	
					}
				}
				if($pending)
				{
					if(!is_array($products))
					{
						$products = array();
					}

					$date = date("d-m-Y");
					$products[$date] = $_POST;
					$products[$date]['status'] = 'pending';
				}	
				
				update_post_meta($order_id, "ced_rnx_request_made", true);
				
				update_post_meta($order_id, 'ced_rnx_return_product', $products);
				
				//Send mail to merchant
				$subject = str_replace('[order]', "#".$order_id, $subject);
				
				$reason_subject = $subject;
				
				$mail_header = stripslashes(get_option('ced_rnx_notification_mail_header', false));
				$mail_footer = stripslashes(get_option('ced_rnx_notification_mail_footer', false));
				
				$message = '<html>
								<body>
						'.do_action('wrnx_return_request_before_mail_content', $order_id).'
								<style>
								body {
								    box-shadow: 2px 2px 10px #ccc;
								    color: #767676;
								    font-family: Arial,sans-serif;
								    margin: 80px auto;
								    max-width: 700px;
								    padding-bottom: 30px;
								    width: 100%;
								}
								
								h2 {
									font-size: 30px;
									margin-top: 0;
									color: #fff;
									padding: 40px;
									background-color: #557da1;
								}
								
								h4 {
									color: #557da1;
									font-size: 20px;
									margin-bottom: 10px;
								}
								
								.content {
									padding: 0 40px;
								}
								
								.Customer-detail ul li p {
									margin: 0;
								}
								
								.details .Shipping-detail {
									width: 40%;
									float: right;
								}
								
								.details .Billing-detail {
									width: 60%;
									float: left;
								}
								
								.details .Shipping-detail ul li,.details .Billing-detail ul li {
									list-style-type: none;
									margin: 0;
								}
								
								.details .Billing-detail ul,.details .Shipping-detail ul {
									margin: 0;
									padding: 0;
								}
								
								.clear {
									clear: both;
								}
								
								table,td,th {
									border: 2px solid #ccc;
									padding: 15px;
									text-align: left;
								}
								
								table {
									border-collapse: collapse;
									width: 100%;
								}
								
								.info {
									display: inline-block;
								}
								
								.bold {
									font-weight: bold;
								}
								
								.footer {
									margin-top: 30px;
									text-align: center;
									color: #99B1D8;
									font-size: 12px;
								}
							dl.variation dd {
							    font-size: 12px;
							    margin: 0;
								}
								</style>
								<div class="header" style="text-align:center;padding: 10px;">
									'.$mail_header.'
									</div>	
								<div class="header">
									<h2>'.$reason_subject.'</h2>
								</div>
								<div class="content">
										
									<div class="reason">
										<h4>'.__('Reason of Refund', 'woocommerce-refund-and-exchange').'</h4>
										<p>'.$reason.'</p>
									</div>
									<div class="Order">
										<h4>Order #'.$order_id.'</h4>
										<table>
											<tbody>
												<tr>
													<th>'.__('Product', 'woocommerce-refund-and-exchange').'</th>
													<th>'.__('Quantity', 'woocommerce-refund-and-exchange').'</th>
													<th>'.__('Price', 'woocommerce-refund-and-exchange').'</th>
												</tr>';
					$order = new WC_Order($order_id);
					$requested_products = $products[$date]['products'];

					$ced_vendor_emails = array();
					if(isset($requested_products) && !empty($requested_products))
					{
						$total = 0;
						foreach( $order->get_items() as $item_id => $item )
						{
							$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
							foreach($requested_products as $requested_product)
							{
								if(isset($requested_product['item_id']))
								{	
									if($item_id == $requested_product['item_id'])
									{
										if(isset($requested_product['variation_id']) && $requested_product['variation_id'] > 0)
										{
											$prod = wc_get_product($requested_product['variation_id']);

										}
										else
										{
											$prod = wc_get_product($requested_product['product_id']);
										}
										if(ced_rnx_wc_vendor_addon_enable())
										{
											$post 			= get_post( $prod->get_id()); 
											$vendor_id 		= $post->post_author; 
											$ced_vendor_data = get_userdata( $vendor_id );
											$ced_vendor_emails[] = $ced_vendor_data->data->user_email;
										}
										if(ced_rnx_wc_dokan_addon_enable()){
											$author = get_post_field( 'post_author',$requested_product['product_id'] );
											if ( ! user_can( $author, 'dokandar' ) ) {
										        $is_seller = false;
										    }
										    else{
										    	$is_seller = true;
										    }
										    if($is_seller) {
										    	$seller_data = get_userdata($author);
												$ced_vendor_emails[] = $seller_data->user_email;
										    }
										}
										$subtotal = $requested_product['price']*$requested_product['qty'];
										$total += $subtotal;
										if( WC()->version < "3.1.0" )
										{
											$item_meta      = new WC_Order_Item_Meta( $item, $_product );
											$item_meta_html = $item_meta->display( true, true );
										}
										else
										{
											$item_meta      = new WC_Order_Item_Product( $item, $_product );
											$item_meta_html = wc_display_item_meta($item_meta,array('echo'=> false));
										}
									
										$message .= '<tr>
														<td>'.$item['name'].'<br>';
											$message .= '<small>'.$item_meta_html.'</small>
														<td>'.$requested_product['qty'].'</td>
														<td>'.ced_rnx_format_price($requested_product['price']*$requested_product['qty']).'</td>
													</tr>';
									}
								}
							}	
						}	
					}
					$message .= '<tr>
									<th colspan="2">'.__('Refund Total', 'woocommerce-refund-and-exchange').':</th>
									<td>'.ced_rnx_format_price($total).'</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="Customer-detail">
						<h4>'.__('Customer details', 'woocommerce-refund-and-exchange').'</h4>
						<ul>
							<li><p class="info">
									<span class="bold">'.__('Email', 'woocommerce-refund-and-exchange').': </span>'.get_post_meta($order_id, '_billing_email', true).'
								</p></li>
							<li><p class="info">
									<span class="bold">'.__('Tel', 'woocommerce-refund-and-exchange').': </span>'.get_post_meta($order_id, '_billing_phone', true).'
								</p></li>
						</ul>
					</div>
					<div class="details">
						<div class="Shipping-detail">
							<h4>'.__('Shipping Address', 'woocommerce-refund-and-exchange').'</h4>
							'.$order->get_formatted_shipping_address().'
						</div>
						<div class="Billing-detail">
							<h4>'.__('Billing Address', 'woocommerce-refund-and-exchange').'</h4>
							'.$order->get_formatted_billing_address().'
						</div>
						<div class="clear"></div>
					</div>
					
				</div>
				<div class="footer" style="text-align:center;padding: 10px;">
					'.$mail_footer.'
				</div>
									
			</body>
			</html>';
				
				$headers = array();
				$headers[] = "Content-Type: text/html; charset=UTF-8";
				$to = get_option('ced_rnx_notification_from_mail');
				$subject = get_option('ced_rnx_notification_merchant_return_subject');
				$subject = str_replace('[order]', "#".$order_id, $subject);
				
				wc_mail( $to, $subject, $message, $headers );
				
			    if(isset($ced_vendor_emails) && is_array($ced_vendor_emails) && !empty($ced_vendor_emails))
			    {
			        $requested_products = $products[$date]['products'];          
			        do_action('ced_rnx_customer_refund_request_mail_for_vendor',$ced_vendor_emails,$order_id,$reason,$reason_subject,$requested_products);
			    }
				
				//Send mail to User that we recieved your request
				
				$fname = get_option('ced_rnx_notification_from_name');
				$fmail = get_option('ced_rnx_notification_from_mail');
				
				$to = get_post_meta($order_id, '_billing_email', true);
				$headers = array();
				$headers[] = "From: $fname <$fmail>";
				$headers[] = "Content-Type: text/html; charset=UTF-8";
				$subject = get_option('ced_rnx_notification_return_subject');
				$subject = str_replace('[order]', "#".$order_id, $subject);
				$message = stripslashes(get_option('ced_rnx_notification_return_rcv'));

				////////////////shortcode replace variable start//////////////////////

				$fname = get_post_meta($order_id, '_billing_first_name', true);
				$lname = get_post_meta($order_id, '_billing_last_name', true);
				$billing_company = get_post_meta($order_id, '_billing_company', true);
				$billing_email = get_post_meta($order_id, '_billing_email', true);
				$billing_phone = get_post_meta($order_id, '_billing_phone', true);
				$billing_country = get_post_meta($order_id, '_billing_country', true);
				$billing_address_1 = get_post_meta($order_id, '_billing_address_1', true);
				$billing_address_2 = get_post_meta($order_id, '_billing_address_2', true);
				$billing_state = get_post_meta($order_id, '_billing_state', true);
				$billing_postcode = get_post_meta($order_id, '_billing_postcode', true);
				$shipping_first_name = get_post_meta($order_id, '_shipping_first_name', true);
				$shipping_last_name = get_post_meta($order_id, '_shipping_last_name', true);
				$shipping_company = get_post_meta($order_id, '_shipping_company', true);
				$shipping_country = get_post_meta($order_id, '_shipping_country', true);
				$shipping_address_1 = get_post_meta($order_id, '_shipping_address_1', true);
				$shipping_address_2 = get_post_meta($order_id, '_shipping_address_2', true);
				$shipping_city = get_post_meta($order_id, '_shipping_city', true);
				$shipping_state = get_post_meta($order_id, '_shipping_state', true);
				$shipping_postcode = get_post_meta($order_id, '_shipping_postcode', true);
				$payment_method_tittle = get_post_meta($order_id, '_payment_method_tittle', true);
				$order_shipping = get_post_meta($order_id, '_order_shipping', true);
				$order_total = get_post_meta($order_id, '_order_total', true);
				$refundable_amount = get_post_meta($order_id, 'refundable_amount', true);

				/////////////////////shortcode replace variable end///////////////////

				$fullname = $fname." ".$lname;
				
				$message = str_replace('[username]', $fullname, $message);
				$message = str_replace('[order]', "#".$order_id, $message);
				$message = str_replace('[siteurl]', home_url(), $message);
				$message = str_replace('[_billing_company]', $billing_company, $message);
				$message = str_replace('[_billing_email]', $billing_email, $message);
				$message = str_replace('[_billing_phone]', $billing_phone, $message);
				$message = str_replace('[_billing_country]', $billing_country, $message);
				$message = str_replace('[_billing_address_1]', $billing_address_1, $message);
				$message = str_replace('[_billing_address_2]', $billing_address_2, $message);
				$message = str_replace('[_billing_state]', $billing_state, $message);
				$message = str_replace('[_billing_postcode]', $billing_postcode, $message);
				$message = str_replace('[_shipping_first_name]', $shipping_first_name, $message);
				$message = str_replace('[_shipping_last_name]', $shipping_last_name, $message);
				$message = str_replace('[_shipping_company]', $shipping_company, $message);
				$message = str_replace('[_shipping_country]', $shipping_country, $message);
				$message = str_replace('[_shipping_address_1]', $shipping_address_1, $message);
				$message = str_replace('[_shipping_address_2]', $shipping_address_2, $message);
				$message = str_replace('[_shipping_city]', $shipping_city, $message);
				$message = str_replace('[_shipping_state]', $shipping_state, $message);
				$message = str_replace('[_shipping_postcode]', $shipping_postcode, $message);
				$message = str_replace('[_payment_method_tittle]', $payment_method_tittle, $message);
				$message = str_replace('[_order_shipping]', $order_shipping, $message);
				$message = str_replace('[_order_total]', $order_total, $message);
				$message = str_replace('[_refundable_amount]', $refundable_amount, $message);
				$message = str_replace('[formatted_shipping_address]', $order->get_formatted_shipping_address(), $message);
				$message = str_replace('[formatted_billing_address]', $order->get_formatted_billing_address(), $message);
				
				$mail_header = stripslashes(get_option('ced_rnx_notification_mail_header', false));
				$mail_footer = stripslashes(get_option('ced_rnx_notification_mail_footer', false));

				$mail_header = str_replace('[username]', $fullname, $mail_header);
				$mail_header = str_replace('[order]', "#".$order_id, $mail_header);
				$mail_header = str_replace('[siteurl]', home_url(), $mail_header);

				$subject = str_replace('[username]', $fullname, $subject);
				$subject = str_replace('[order]', "#".$order_id, $subject);
				$subject = str_replace('[siteurl]', home_url(), $subject);
				
				$template = get_option('ced_rnx_notification_return_template','no');
				if(isset($template) && $template == 'on')
				{
					$html_content = $message;
				}
				else
				{
					$html_content = '<html>
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
										<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
									</head>
									<body style="margin: 1% 0 0; padding: 0;">
										<table cellpadding="0" cellspacing="0" width="100%">
											<tr>
												<td style="text-align: center; margin-top: 30px; padding: 10px; color: #99B1D8; font-size: 12px;">
													'.$mail_header.'
												</td>
											</tr>
											<tr>
												<td>
													<table align="center" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-family:Open Sans; max-width: 600px; width: 100%;">
														<tr>
															<td style="padding: 36px 48px; width: 100%; background-color:#557DA1;color: #fff; font-size: 30px; font-weight: 300; font-family:helvetica;">'.$subject.'</td>
														</tr>
														<tr>
															<td style="width:100%; padding: 36px 48px 10px; background-color:#fdfdfd; font-size: 14px; color: #737373;">'.$message.'</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style="text-align: center; margin-top: 30px; color: #99B1D8; font-size: 12px;">
													'.$mail_footer.'
												</td>
											</tr>				
										</table>
																
									</body>
								</html>';
				}
				
				
				wc_mail($to, $subject, $html_content, $headers );
				
				$order = new WC_Order($order_id);
				$order->update_status('wc-return-requested', 'User Request to Refund Product');
				$response['msg'] = __('Message send successfully.You have received a notification mail regarding this, Please check your mail. Soon You redirect to My Account Page. Thanks', 'woocommerce-refund-and-exchange');
				if( WC()->version < "3.0.0" )
				{
					$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
				}
				else
				{
					$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
				}
				$today_date = time(); // or your date as well
				$order_date = strtotime($order_date);
				$days = $today_date - $order_date;
				$day_diff = floor($days/(60*60*24));
				$day_allowed = get_option('ced_rnx_auto_return_days', false);
				
				if($day_allowed >= $day_diff && $day_allowed != 0)
				{
					$response['auto_accept'] = true;
				}
				
				echo json_encode($response);
				die;
			}
		}
		
		/**
		 * This function is to save return request Attachment
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_order_return_attach_files()
		{
			if(isset($_FILES['ced_rnx_return_request_files']))
			{
				if(isset($_FILES['ced_rnx_return_request_files']['tmp_name']))
				{
					$filename = array();
					$order_id = $_POST['ced_rnx_return_request_order'];
					$count = sizeof($_FILES['ced_rnx_return_request_files']['tmp_name']);
					for($i=0;$i<$count;$i++)
					{
						if(isset($_FILES['ced_rnx_return_request_files']['tmp_name'][$i]))
						{	
							$directory = ABSPATH.'wp-content/attachment';
							if (!file_exists($directory)) 
							{
								mkdir($directory, 0755, true);
							}
							
							$sourcePath = $_FILES['ced_rnx_return_request_files']['tmp_name'][$i];
							$targetPath = $directory.'/'.$order_id.'-'.$_FILES['ced_rnx_return_request_files']['name'][$i];
							
							$filename[] = $order_id.'-'.$_FILES['ced_rnx_return_request_files']['name'][$i];
							move_uploaded_file($sourcePath,$targetPath) ;
						}
					}
					
					$request_files = get_post_meta($order_id, 'ced_rnx_return_attachment', true);
					
					$pending = true;
					if(isset($request_files) && !empty($request_files))
					{
						foreach($request_files as $date=>$request_file)
						{
							if($request_file['status'] == 'pending')
							{
								unset($request_files[$date][0]);
								$request_files[$date]['files'] = $filename;
								$request_files[$date]['status'] = 'pending';
								$pending = false;
								break;
							}
						}
					}
					
					if($pending)
					{	
						$request_files = array();
						$date = date("d-m-Y");
						$request_files[$date]['files'] = $filename;
						$request_files[$date]['status'] = 'pending';
					}
					
					update_post_meta($order_id, 'ced_rnx_return_attachment', $request_files);
					echo 'success';
				}
			}
			die;
		}
		
		/**
		 * This function is to add Return button and Show return products
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function  ced_rnx_order_return_button($order)
		{
			$ced_rnx_return_button_show = true;
			$ced_rnx_next_return = true;
			$items = $order->get_items();
			$ced_rnx_catalog=get_option('catalog',array());
			if(is_array($ced_rnx_catalog) && !empty($ced_rnx_catalog) )
			{	
				$ced_rnx_catalog_refund=array();
				foreach ( $items as $item ) {
				    $product_id = $item['product_id'];
				    if(is_array($ced_rnx_catalog) && !empty($ced_rnx_catalog) )
					{
						foreach ($ced_rnx_catalog as $key => $value) {
							if(is_array($value['products']))
							{
								if(in_array($product_id, $value['products']))	
								{
									$ced_rnx_catalog_refund[]=$value['refund'];
								}
								
							}
						}
					}
				}
				if(is_array($ced_rnx_catalog_refund) && !empty($ced_rnx_catalog_refund))
				{
					$ced_rnx_catalog_refund_days=max($ced_rnx_catalog_refund);
				}
			}
			$ced_rnx_enable = get_option('ced_rnx_return_exchange_enable', false);
			$ced_rnx_enable_time_policy = get_option( 'ced_rnx_enable_time_policy', 'no' );
			$ced_rnx_from_time = get_option( 'ced_rnx_return_from_time', '' );
			$ced_rnx_to_time = get_option( 'ced_rnx_return_to_time', '' );
			if($ced_rnx_enable == 'yes')
			{
				$order_id = $order->get_id();
				$ced_rnx_made = get_post_meta($order_id, "ced_rnx_request_made", true);
				if(isset($ced_rnx_made) && !empty($ced_rnx_made))
				{
					$ced_rnx_next_return = false;
				}
			}
			
			$order_total = $order->get_total();
			$return_min_amount = get_option('ced_rnx_return_minimum_amount', false);
			
			//Return Request at order detail page
			$ced_rnx_return = get_option('ced_rnx_return_enable', false);
			if($ced_rnx_return == 'yes')
			{
				if ( $ced_rnx_enable_time_policy == 'on' ) 
				{
					if(strtotime(current_time('h:i A')) < strtotime($ced_rnx_from_time) || strtotime(current_time('h:i A')) > strtotime($ced_rnx_to_time))
					{
						return ;
					}
					
				}
				if( WC()->version < "3.0.0" )
				{
					$order_id=$order->id;
				}
				else
				{
					$order_id=$order->get_id();
				}
				$statuses = get_option('ced_rnx_return_order_status', array());
				$order_status ="wc-".$order->get_status();
				$product_datas = get_post_meta($order_id, 'ced_rnx_return_product', true);
				if(isset($product_datas) && !empty($product_datas))
				{
					?>
					<h2><?php _e( 'Refund Requested Product', 'woocommerce-refund-and-exchange' ); ?></h2>
					<?php 
					
					$request_status = true;
					foreach($product_datas as $key=>$product_data)
					{
						$date=date_create($key);
						$date_format = get_option('date_format');
						$date=date_format($date,$date_format);
						?>
						<p><?php _e( 'Following product Refund request made on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $date?>.</b></p>
						<table class="shop_table order_details">
							<thead>
								<tr>
									<th class="product-name"><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
									<th class="product-total"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php 
							$return_products = $product_data['products'];
							foreach( $order->get_items() as $item_id => $item ) 
							{
								foreach($return_products as $return_product)
								{
									if(isset($return_product['item_id']))
									{	
										if($return_product['item_id'] == $item_id)
										{
										?><tr>
											<td class="product-name">
											<?php 
											$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
											$is_visible        = $product && $product->is_visible();
											$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
											
											echo $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'];
											echo '<strong class="product-quantity">' . sprintf( '&times; %s', $return_product['qty'] ) . '</strong>';
											
											do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );
											
											if( WC()->version < "3.0.0" )
											{
												$order->display_item_meta( $item );
												$order->display_item_downloads( $item );
											}
											else
											{
												wc_display_item_meta( $item );
												wc_display_item_downloads( $item );
											}
											
											do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
											?>
											</td>
											<td class="product-total"><?php 
											echo ced_rnx_format_price($return_product['price']*$return_product['qty']);
											?></td>
											</tr>
										<?php 
										}
									}
								}	
							}
							?>
							<tr>
								<th scope="row"><?php _e('Refund Amount', 'woocommerce-refund-and-exchange') ?></th>
								<th><?php echo ced_rnx_format_price($product_data['amount']); ?></th>
							</tr>
							<?php 
							$added_fees = get_post_meta($order_id, 'ced_rnx_return_added_fee', true);
							if(isset($added_fees))
							{
								if(is_array($added_fees))
								{
									foreach($added_fees as $da=>$added_fee)
									{
										if(!empty($added_fee))
										{
											if($da == $key)
											{
												?>
												<tr>
													<th colspan="2"><?php _e('Extra Cost', 'woocommerce-refund-and-exchange') ?></th>
												</tr>
												<?php 
												foreach($added_fee as $fee)
												{
													?>
													<tr>
														<th><?php echo $fee['text'];?></th>
														<td><?php echo ced_rnx_format_price($fee['val']);?></td>
													</tr>
													<?php 
													$product_data['amount'] -= $fee['val'];
												}	
											}
										}
									}
									?>
									<tr>
								<th scope="row"><?php _e('Total Refund Amount', 'woocommerce-refund-and-exchange') ?></th>
								<th><?php echo ced_rnx_format_price($product_data['amount']); ?></th>
							</tr>
								<?php 
								}
							}		
							?>
							</tbody>
						</table>	
						<?php 
						
						
						if(in_array($order_status, $statuses))
						{
							if($product_data['status'] == 'pending')
							{
								$request_status = false;
								if( WC()->version < "3.0.0" )
								{
									$order_id = $order->id;
									$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
								}
								else
								{
									$order=new WC_Order($order);
									$order_id = $order->get_id();
									$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
								}
								$today_date = time(); // or your date as well
								$order_date = strtotime($order_date);
								$days = $today_date - $order_date;
								$day_diff = floor($days/(60*60*24));
								$day_allowed = get_option('ced_rnx_return_days', false);
								if(isset($ced_rnx_catalog_refund_days)&& $ced_rnx_catalog_refund_days != 0)
								{
									if($ced_rnx_catalog_refund_days >= $day_diff)
									{
										if($ced_rnx_return_button_show)
										{
											$ced_rnx_return_button_show = false;
											$ced_rnx_pages= get_option('ced_rnx_pages');
											$page_id = $ced_rnx_pages['pages']['ced_return_from'];
											$return_url = get_permalink($page_id);
											?>
											<form action="<?php echo $return_url.$order_id?>" method="post">
												<input type="hidden" value="<?php echo $order_id?>" name="order_id">
												<p>
													<input type="submit" class="btn button" value="<?php _e('Update Request','woocommerce-refund-and-exchange');?>" name="ced_update_return_request">
												</p>
											</form>
											<?php 
										}
									}
								}
								else
								{		
									if($day_allowed >= $day_diff && $day_allowed != 0)	
									{
										if($ced_rnx_return_button_show)
										{
											$ced_rnx_return_button_show = false;
											$ced_rnx_pages= get_option('ced_rnx_pages');
											$page_id = $ced_rnx_pages['pages']['ced_return_from'];
											$return_url = get_permalink($page_id);
											?>
											<form action="<?php echo $return_url.$order_id?>" method="post">
												<input type="hidden" value="<?php echo $order_id?>" name="order_id">
												<p>
													<input type="submit" class="btn button" value="<?php _e('Update Request','woocommerce-refund-and-exchange');?>" name="ced_update_return_request">
												</p>
											</form>
											<?php 
										}
									}
								}
							}	
						}
						if($product_data['status'] == 'complete')
						{
							$appdate=date_create($product_data['approve_date']);
							$format = get_option('date_format');
							$appdate=date_format($appdate,$format);
							?>
							<p><?php _e('Above product Refund request is approved on','woocommerce-refund-and-exchange');?> <b><?php echo $appdate?>.</b></p>
							<?php 
						}
	
						if($product_data['status'] == 'cancel')
						{
							$appdate=date_create($product_data['cancel_date']);
							$format = get_option('date_format');
							$appdate=date_format($appdate,$format);
							?>
							<p><?php _e('Above product Refund request is cancelled on','woocommerce-refund-and-exchange');?> <b><?php echo $appdate?>.</b></p>
							<?php 
						}
					}
					
					$statuses = get_option('ced_rnx_return_order_status', array());
					$order_status ="wc-".$order->get_status();
					if(in_array($order_status, $statuses))
					{
						if($request_status)
						{
							if( WC()->version < "3.0.0" )
							{
								$order_id = $order->id;
								$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
							}
							else
							{
								$order_id = $order->get_id();
								$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
							}
							$today_date = time(); // or your date as well
							$order_date = strtotime($order_date);
							$days = $today_date - $order_date;
							$day_diff = floor($days/(60*60*24));
							$day_allowed = get_option('ced_rnx_return_days', false);
							if(isset($ced_rnx_catalog_refund_days)&& $ced_rnx_catalog_refund_days != 0)
							{ 
								if($ced_rnx_catalog_refund_days >= $day_diff)
								{
									$ced_rnx_pages= get_option('ced_rnx_pages');
									$page_id = $ced_rnx_pages['pages']['ced_return_from'];
									$return_url = get_permalink($page_id);
									if(isset($return_min_amount) && !empty($return_min_amount))
									{
										if($return_min_amount <= $order_total)
										{
											if($ced_rnx_next_return)
											{
												if($ced_rnx_return_button_show)
												{
													
													$ced_rnx_return_button_show = false;
													?>
													<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
														<input type="hidden" value="<?php echo $order_id?>" name="order_id">
														<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
													</form>
													<?php 
												}	
											}
										}
									}
									else
									{
										if($ced_rnx_next_return)
										{
											if($ced_rnx_return_button_show)
											{
												
												$ced_rnx_return_button_show = false;
												?>
												<form action="<?php echo add_query_arg('order_id',$order_id,$return_url) ?>" method="post">
													<input type="hidden" value="<?php echo $order_id?>" name="order_id">
													<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
												</form>
												<?php 
											}	
										}
									}	
								}

							}
							else
							{		
								if($day_allowed >= $day_diff && $day_allowed != 0)
								{
									$ced_rnx_pages= get_option('ced_rnx_pages');
									$page_id = $ced_rnx_pages['pages']['ced_return_from'];
									$return_url = get_permalink($page_id);
									if(isset($return_min_amount) && !empty($return_min_amount))
									{
										if($return_min_amount <= $order_total)
										{
											if($ced_rnx_next_return)
											{
												if($ced_rnx_return_button_show)
												{
													
													$ced_rnx_return_button_show = false;
													?>
													<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
														<input type="hidden" value="<?php echo $order_id?>" name="order_id">
														<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
													</form>
													<?php 
												}	
											}
										}
									}
									else
									{
										if($ced_rnx_next_return)
										{
											if($ced_rnx_return_button_show)
											{
												
												$ced_rnx_return_button_show = false;
												?>
												<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
													<input type="hidden" value="<?php echo $order_id?>" name="order_id">
													<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
												</form>
												<?php 
											}	
										}
									}	
								}
							}
						}
					}	
				}
				
				if(in_array($order_status, $statuses))
				{
					if( WC()->version < "3.0.0" )
					{
						$order_id = $order->id;
						$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
					}
					else
					{
						$order_id = $order->get_id();
						$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
					}
					$today_date = date_i18n( 'F j, Y' );
     				$order_date = strtotime($order_date);
     				$today_date = strtotime($today_date);
					
					$days = $today_date - $order_date;
					$day_diff = floor($days/(60*60*24));
					$day_allowed = get_option('ced_rnx_return_days', false);
					if(isset($ced_rnx_catalog_refund_days)&& $ced_rnx_catalog_refund_days != 0)
					{ 
						if($ced_rnx_catalog_refund_days >= $day_diff)
						{
							$ced_rnx_pages= get_option('ced_rnx_pages');
							$page_id = $ced_rnx_pages['pages']['ced_return_from'];
							$return_url = get_permalink($page_id);
							if(isset($return_min_amount) && !empty($return_min_amount))
							{
								if($return_min_amount <= $order_total)
								{
									if($ced_rnx_next_return)
									{
										if($ced_rnx_return_button_show)
										{
											
											$ced_rnx_return_button_show = false;
											?>
											<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
												<input type="hidden" value="<?php echo $order_id?>" name="order_id">
												<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
											</form>
											<?php 
										}
									}
								}
							}
							else 
							{
								if($ced_rnx_next_return)
								{
									if($ced_rnx_return_button_show)
									{
										$ced_rnx_return_button_show = false;
										?>
										<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
											<input type="hidden" value="<?php echo $order_id?>" name="order_id">
											<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
										</form>
										<?php 
									}
								}
							}	
						}
					}
					else
					{
						if($day_allowed >= $day_diff && $day_allowed != 0)	
						{
							$ced_rnx_pages= get_option('ced_rnx_pages');
							$page_id = $ced_rnx_pages['pages']['ced_return_from'];
							$return_url = get_permalink($page_id);
							if(isset($return_min_amount) && !empty($return_min_amount))
							{
								if($return_min_amount <= $order_total)
								{
									if($ced_rnx_next_return)
									{
										if($ced_rnx_return_button_show)
										{
											
											$ced_rnx_return_button_show = false;
											?>
											<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
												<input type="hidden" value="<?php echo $order_id?>" name="order_id">
												<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
											</form>
											<?php 
										}
									}
								}
							}
							else 
							{
								if($ced_rnx_next_return)
								{
									if($ced_rnx_return_button_show)
									{
										$ced_rnx_return_button_show = false;
										?>
										<form action="<?php echo add_query_arg('order_id',$order_id,$return_url)?>" method="post">
											<input type="hidden" value="<?php echo $order_id?>" name="order_id">
											<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woocommerce-refund-and-exchange');?>" name="ced_new_return_request"></p>
										</form>
										<?php 
									}
								}
							}	
						}
					}		
				}
			}  
		}
	}
	new CED_rnx_order_return();
}

?>