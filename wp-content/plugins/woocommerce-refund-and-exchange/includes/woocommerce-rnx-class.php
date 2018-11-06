<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'Ced_refund_and_exchange' ) )
{
	/**
	 * This is class for managing order status and other functionalities .
	 *
	 * @name    Ced_refund_and_exchange
	 * @category Class
	 * @author   makewebbetter <webmaster@makewebbetter.com>
	 */
	
	class Ced_refund_and_exchange{
		

		/**
		 * This is construct of class
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function __construct() 
		{
			$ced_rnx_hide_sidebar_forever = get_option('ced_rnx_hide_sidebar_forever','no'); 
			$ced_rnx_license_hash = get_option('ced_rnx_license_hash');
			$ced_rnx_license_key = get_option('ced_rnx_license_key');
			$ced_rnx_license_plugin = get_option('ced_rnx_plugin_name');
			$ced_rnx_hash = md5($_SERVER['HTTP_HOST'].$ced_rnx_license_plugin.$ced_rnx_license_key);
			$ced_rnx_activation_date = get_option('ced_rnx_activation_date',false);
			$ced_rnx_after_month = strtotime('+30 days', $ced_rnx_activation_date);
			$ced_rnx_currenttime = current_time('timestamp');
			$ced_rnx_time_difference = $ced_rnx_after_month - $ced_rnx_currenttime;
			$ced_rnx_days_left = floor($ced_rnx_time_difference/(60*60*24));
			if( $ced_rnx_license_hash == $ced_rnx_hash || $ced_rnx_days_left >= 0 )
			{
				add_filter( 'template_include', array($this, 'ced_product_return_template'));
				add_filter( 'template_include', array($this, 'ced_product_exchange_template'));
				add_filter( 'template_include', array($this, 'ced_product_cancel_template'));
				add_action( 'wp_enqueue_scripts', array($this, 'ced_rnx_scripts'), 10);
				add_action( 'init', array($this, 'ced_rnx_register_custom_order_status'));
				add_filter( 'wc_order_statuses', array($this, 'ced_rnx_add_custom_order_status'));
				add_action( 'woocommerce_product_options_advanced', array($this, 'ced_rnx_product_enable_rnx'));
				add_action( 'save_post', array($this, 'ced_rnx_save_product_meta'));
				add_filter( 'woocommerce_my_account_my_orders_actions', array($this, 'ced_rnx_refund_exchange_button'),10, 2 );
				add_action( 'woocommerce_product_meta_end', array($this, 'ced_rnx_show_note_on_product'));
				add_filter( 'template_include', array($this, 'ced_rnx_request_form'));
				add_action( 'woocommerce_after_my_account', array($this, 'ced_rnx_woocommerce_after_my_account'));
				add_shortcode( 'ced_rnx_customer_wallet', array( $this, 'ced_rnx_add_customer_wallet_frontend' )  );
				add_filter( 'woocommerce_my_account_my_orders_actions', array($this, 'ced_rnx_add_order_cancel_button'),10, 2 );
				add_action('woocommerce_checkout_update_order_meta',array($this, 'ced_rnx_woocommerce_checkout_update_order_meta'));
				$ced_rnx_enable_price_policy = get_option( 'ced_rnx_enable_price_policy' , 'no' );
				if($ced_rnx_enable_price_policy == 'on')
				{
					$ced_rnx_show_refund_policy_on_product_page = get_option( 'ced_rnx_show_refund_policy_on_product_page', 'no' );
					if($ced_rnx_show_refund_policy_on_product_page == 'on')
					{
						add_filter('woocommerce_product_tabs',array($this, 'ced_rnx_woocommerce_product_tabs'),99,1);
					}
					add_shortcode( 'ced_rnx_refund_policy', array( $this, 'ced_rnx_new_product_tab_content' )  );
				}
			}
		}


		/**
		 *show product waranty on single page.
		 *
		 * @name ced_rnx_woocommerce_before_add_to_cart_button
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_woocommerce_product_tabs($tabs=array())
		{

			$tabs['ced_rnx_product_refund_policy'] = array(
				'title'     => __( 'Product Refund Policy', 'woocommerce-refund-and-exchange' ),
				'priority'  => 50,
				'callback'  => array($this,'ced_rnx_new_product_tab_content'),
				);	
			return $tabs;
		}

		/**
		 *show product waranty tab content on single page.
		 *
		 * @name ced_rnx_new_product_tab_content
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_new_product_tab_content()  {
				
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
			echo '<h2>'. __('Product Refund Policy', 'woocommerce-refund-and-exchange' ).'</h2>';
			?>
			<table border="1px" class="ced_rnx_product_warranty_table">
				<tr>
					<th>
						<label ><?php _e('Refund Request Days', 'woocommerce-refund-and-exchange' ); ?></label>
					</th>
					<th>
						<label ><?php _e('Price Deduction', 'woocommerce-refund-and-exchange' ); ?></label>
					</th>
				</tr>
				<?php $key_value = 0;
				foreach ($ced_rnx_price_policy_array as $days => $price) {
					?>
					<tr>
						<td>
							<?php
							
							echo $key_value.' - '.$days; 
							
							?>
						</td>
						<td>
							<?php echo $price.' %'; ?>
						</td>
					</tr>
					<?php
					$key_value = $days+1;
				}
				?>
			</table>
			<?php	
		}

		/**
		 *function for wallet amount deduction when wallet coupon is used in order.
		 *
		 * @name woocommerce_checkout_update_order_meta
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_woocommerce_checkout_update_order_meta($order_id)
		{
			$order = wc_get_order($order_id);
			$coupons = $order->get_items( array( 'coupon' ) );
			if(isset($coupons) && !empty($coupons))
			{
				foreach ($coupons as  $coupon) {

					$coupon =$coupon->get_data();
					$coupon_code = strtolower($coupon['code']);
					$customer_id = get_current_user_id();
					$wallet_coupon_code = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon',false);
					$wallet_coupon_code = strtolower($wallet_coupon_code[0]);
					
					if($wallet_coupon_code == $coupon_code)
					{
						$the_coupon = new WC_Coupon( $coupon_code );
						$customer_coupon_id = $the_coupon->get_id();

						if( isset($the_coupon) && $the_coupon != '' )
						{
							$amount = get_post_meta( $customer_coupon_id, 'coupon_amount', false );
							
							$discount = $coupon['discount']+$coupon['discount_tax'];
							$discount = sprintf("%.4f", $discount);
							if($discount > $amount[0])
							{
								$amount = 0;
							}
							else
							{
								$amount = $amount[0] - $discount;
							}
							update_post_meta( $customer_coupon_id, 'coupon_amount', $amount );
						}
					}	
				}
			}
		}
		
		/**
		 * Shortcode for Customer Wallet to be displayed.
		 *
		 * @name ced_rnx_add_customer_wallet_frontend
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_add_customer_wallet_frontend()
		{
			$customer_id = get_current_user_id();
			if($customer_id > 0)
			{
				$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );
				if(!empty($walletcoupon) && isset($walletcoupon))
				{
					$the_coupon = new WC_Coupon( $walletcoupon );
					$coupon_id = $the_coupon->get_id();
					if(isset($coupon_id))
					{
						$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
						echo "<p class='button ced_rnx_wallet' style='text-align:center;'><b>".__('My Wallet', 'woocommerce-refund-and-exchange').":   ".$walletcoupon." ( ".wc_price($amount)." )"."</b></p><p class='regenerate_coupon_code'><a class='button' data-id=".$coupon_id." href='javascript:void(0)' id='ced_rnx_coupon_regenertor'>".__('Regenerate Coupon Code' , 'woocommerce-refund-and-exchange')."</a><img class='regenerate_coupon_code_image' src = ".CED_REFUND_N_EXCHANGE_URL."assets/images/loading.gif width='20px' style='display:none;'></p>";
					}	
				}	
			}
		}



		function ced_rnx_add_order_cancel_button( $actions, $order ){
			$cancel_enable = get_option( 'ced_rnx_cancel_enable' , FALSE );
			$ced_rnx_cancel_order_product_enable = get_option( 'ced_rnx_cancel_order_product_enable' , FALSE );
			if( WC()->version < "3.0.0" )
			{
				$order_id = $order->id;
			}
			else
			{
				$order_id = $order->get_id();
			}
			$order_status_array = get_option('ced_rnx_cancel_order_status' , array());
			$the_order = wc_get_order($order_id);
			$order_status='wc-'.$the_order->get_status();
			
			if ($cancel_enable == 'yes' && $ced_rnx_cancel_order_product_enable == 'yes') {
				$ced_rnx_pages= get_option('ced_rnx_pages');
				$page_id = $ced_rnx_pages['pages']['ced_cancel_request_from'];
				if (in_array($order_status, $order_status_array)) {
					$ced_rnx_product_cancel_text = get_option('ced_rnx_product_cancel_text' ,__('Cancel Product', 'woocommerce-refund-and-exchange') );
					$actions['ced_rnx_cancel_order_product']['name'] = $ced_rnx_product_cancel_text;				
					$cancel_url = get_permalink($page_id);
					$cancel_url = add_query_arg('order_id',$order_id,$cancel_url);
					$actions['ced_rnx_cancel_order_product']['url'] = $cancel_url;
					
					return $actions;
				}
			}
			else if($cancel_enable == 'yes') {
				
				if (in_array($order_status, $order_status_array)) {
					$ced_rnx_order_cancel_text = get_option('ced_rnx_order_cancel_text' ,__('Cancel Order', 'woocommerce-refund-and-exchange') );
			
					$actions['ced_rnx_cancel_order']['name'] =  $ced_rnx_order_cancel_text;				
					$actions['ced_rnx_cancel_order']['url'] = $order_id;
					
					return $actions;
				}
			}
			else if($ced_rnx_cancel_order_product_enable == 'yes') {
				$ced_rnx_pages= get_option('ced_rnx_pages');
				$page_id = $ced_rnx_pages['pages']['ced_cancel_request_from'];
				if (in_array($order_status, $order_status_array)) {
					$ced_rnx_product_cancel_text = get_option('ced_rnx_product_cancel_text' ,__('Cancel Product', 'woocommerce-refund-and-exchange') );
					$actions['ced_rnx_cancel_order_product']['name'] = $ced_rnx_product_cancel_text;				
					$cancel_url = get_permalink($page_id);
					$cancel_url = add_query_arg('order_id',$order_id,$cancel_url);
					$actions['ced_rnx_cancel_order_product']['url'] = $cancel_url;
					
					return $actions;
				}
			}
			return $actions;

		}


		/**
		 * Displays Customer Wallet on My Account (Dashboard Page)
		 *
		 * @name ced_rnx_woocommerce_after_my_account
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_woocommerce_after_my_account()
		{
			$customer_id = get_current_user_id();
			if($customer_id > 0)
			{
				$walletcoupon = get_post_meta( $customer_id, 'ced_rnx_refund_wallet_coupon', true );
				if(!empty($walletcoupon) && isset($walletcoupon))
				{
					$the_coupon = new WC_Coupon( $walletcoupon );
					
					$coupon_id = $the_coupon->get_id();

					if(isset($coupon_id))
					{
						$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
						echo "<p class='button ced_rnx_wallet' style='text-align:center;'><b>".__('My Wallet', 'woocommerce-refund-and-exchange').": </br>".__('Coupon Code', 'woocommerce-refund-and-exchange').": ".$walletcoupon."<br>".__('Wallet Amount', 'woocommerce-refund-and-exchange').":  ".wc_price($amount)."</b></p><p class='regenerate_coupon_code'><a class='button' data-id=".$coupon_id." href='javascript:void(0)' id='ced_rnx_coupon_regenertor'>".__('Regenerate Coupon Code' , 'woocommerce-refund-and-exchange')."</a><img class='regenerate_coupon_code_image' src = ".CED_REFUND_N_EXCHANGE_URL."assets/images/loading.gif width='20px' style='display:none;'></p>";
					}		
				}	
			}	
		}
		
		/**
		 * include custom template for customer's 'lay-buys installment report' page
		 */
		
		function ced_rnx_request_form($template)
		{
			$ced_rnx_pages= get_option('ced_rnx_pages');
			$page_id = $ced_rnx_pages['pages']['ced_request_from'];
			if(is_page($page_id))
			{
				$located = locate_template('woocommerce-refund-and-exchange/template/ced-guest-request-form.php');
				if ( !empty( $located ) ) {

					$new_template =wc_get_template('woocommerce-refund-and-exchange/template/ced-guest-request-form.php');
				}
				else
				{
					$new_template = CED_REFUND_N_EXCHANGE_DIRPATH. 'template/ced-guest-request-form.php';
				}
				
				$template =  $new_template;
			}
			return $template;
		}
		
		function ced_rnx_refund_exchange_button($actions, $order)
		{
			$order=new WC_Order($order);
			$items = $order->get_items();
			$ced_rnx_catalog=get_option('catalog',array());
			if(is_array($ced_rnx_catalog) && !empty($ced_rnx_catalog) )
			{	
				$ced_rnx_catalog_refund=array();
				$ced_rnx_catalog_exchange=array();
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
									$ced_rnx_catalog_exchange[]=$value['exchange'];
								}
								
							}
						}
					}
				}
				if(is_array($ced_rnx_catalog_refund) && !empty($ced_rnx_catalog_refund))
				{
					$ced_rnx_catalog_refund_days=max($ced_rnx_catalog_refund);
				}
				if(is_array($ced_rnx_catalog_exchange) && !empty($ced_rnx_catalog_exchange))
				{
					$ced_rnx_catalog_exchange_days=max($ced_rnx_catalog_exchange);
				}
			}
			$ced_rnx_next_return = true;
			$ced_rnx_enable = get_option('ced_rnx_return_exchange_enable', false);
			if($ced_rnx_enable == 'yes')
			{
				$order_id = $order->get_id();
				$ced_rnx_made = get_post_meta($order_id, "ced_rnx_request_made", true);
				if(isset($ced_rnx_made) && !empty($ced_rnx_made))
				{
					$ced_rnx_next_return = false;
				}
			}
			$ced_rnx_exchange_approved_enable = get_option('ced_rnx_exchange_approved_enable', false);
			$ced_rnx_exchange_approved_enabled_order = false;
			$ced_rnx_myaccount_order_id = get_post_meta( $order->get_id(), 'ced_rnx_exchange_order',true );
			if($ced_rnx_myaccount_order_id > 0)
			{ 
				$ced_rnx_exchange_approved_enabled_order = true;
			}
			if($ced_rnx_exchange_approved_enabled_order)
			{
				if($ced_rnx_exchange_approved_enable == 'yes')
				{
					$ced_rnx_next_return = true;
				}
				else
				{
					$ced_rnx_next_return = false;
				}
				
			}
			
			if($ced_rnx_next_return)
			{
				$ced_rnx_exchange = get_option('ced_rnx_exchange_enable', false);
				if($ced_rnx_exchange == 'yes')
				{
					$statuses = get_option('ced_rnx_exchange_order_status', array());
					$order_status ="wc-".$order->get_status();
					if(in_array($order_status, $statuses))
					{
						if( WC()->version < "3.0.0" )
						{
							$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
						}
						else
						{
							$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()) );
						}
						$today_date = date_i18n( 'F j, Y' );
						$order_date = strtotime($order_date);
						$today_date = strtotime($today_date);
						$days = $today_date - $order_date;
						$day_diff = floor($days/(60*60*24));
						$day_allowed = get_option('ced_rnx_exchange_days', false);
						$exchange_button_text = get_option( 'ced_rnx_exchange_button_text' , '' );
						if ($exchange_button_text == '') {
							$exchange_button_text = 'Exchange';
						}
						if(isset($ced_rnx_catalog_exchange_days)&& $ced_rnx_catalog_exchange_days != 0)
						{
							if($ced_rnx_catalog_exchange_days >= $day_diff)
							{
								$ced_rnx_pages= get_option('ced_rnx_pages');
								$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
								$exchange_url = get_permalink($page_id);
								$order_id = $order->get_id();
								$actions['exchange']['url'] = add_query_arg('order_id',$order_id,$exchange_url);
								$actions['exchange']['name'] = $exchange_button_text;
							}
						}
						else
						{		
							if($day_allowed >= $day_diff && $day_allowed != 0)
							{
								$ced_rnx_pages= get_option('ced_rnx_pages');
								$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
								$exchange_url = get_permalink($page_id);
								if( WC()->version < "3.0.0" )
								{
									$order_id=$order->id;
								}
								else
								{
									$order_id=$order->get_id();
								}
								$actions['exchange']['url'] =add_query_arg('order_id',$order_id,$exchange_url);
								$actions['exchange']['name'] = $exchange_button_text;
							}
						}	
					}
				}
				
				$order_total = $order->get_total();
				$return_min_amount = get_option('ced_rnx_return_minimum_amount', false);

				//Return Request at order detail page
				$ced_rnx_return = get_option('ced_rnx_return_enable', false);
				if($ced_rnx_return == 'yes')
				{

					$statuses = get_option('ced_rnx_return_order_status', array());
					$order_status ="wc-".$order->get_status();
					$ced_rnx_enable_time_policy = get_option( 'ced_rnx_enable_time_policy', 'no' );
					$ced_rnx_from_time = get_option( 'ced_rnx_return_from_time', '' );
					$ced_rnx_to_time = get_option( 'ced_rnx_return_to_time', '' );

					if(in_array($order_status, $statuses))
					{
						if( WC()->version < "3.0.0" )
						{
							$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
						}
						else
						{
							$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
						}
						$today_date = date_i18n('F j, Y');
						$order_date = strtotime($order_date);
						$today_date = strtotime($today_date);
						$days = $today_date - $order_date;
						$day_diff = floor($days/(60*60*24));
						
						$day_allowed = get_option('ced_rnx_return_days', false);

						$return_button_text = get_option( 'ced_rnx_return_button_text' , '' );
						if ($return_button_text == '') {
							$return_button_text = 'Refund';
						}
						if(isset($ced_rnx_catalog_refund_days)&& $ced_rnx_catalog_refund_days != 0)
						{
							if($ced_rnx_catalog_refund_days >= $day_diff)
							{	
								if ( $ced_rnx_enable_time_policy == 'on' ) 
								{
									if(strtotime(current_time('h:i A')) >= strtotime($ced_rnx_from_time) && strtotime(current_time('h:i A')) <= strtotime($ced_rnx_to_time))
									{
										$ced_rnx_pages= get_option('ced_rnx_pages');
										$page_id = $ced_rnx_pages['pages']['ced_return_from'];
										$return_url = get_permalink($page_id);
										$order_id = $order->get_id();
										$actions['return']['url'] = add_query_arg('order_id',$order_id,$return_url);
										$actions['return']['name'] = $return_button_text;
									}
								}	
								else
								{
									$ced_rnx_pages= get_option('ced_rnx_pages');
									$page_id = $ced_rnx_pages['pages']['ced_return_from'];
									$return_url = get_permalink($page_id);
									$order_id = $order->get_id();
									$actions['return']['url'] = add_query_arg('order_id',$order_id,$return_url);
									$actions['return']['name'] = $return_button_text;
								}
							}		
						}
						else
						{	
							if($day_allowed >= $day_diff && $day_allowed != 0)
							{
								if ( $ced_rnx_enable_time_policy == 'on' ) 
								{
									if(strtotime(current_time('h:i A')) >= strtotime($ced_rnx_from_time) && strtotime(current_time('h:i A')) <= strtotime($ced_rnx_to_time))
									{
										$ced_rnx_pages= get_option('ced_rnx_pages');
										$page_id = $ced_rnx_pages['pages']['ced_return_from'];
										$return_url = get_permalink($page_id);
										$order_id = $order->get_id();
										$actions['return']['url'] = add_query_arg('order_id',$order_id,$return_url);
										$actions['return']['name'] = $return_button_text;
									}
								}
								else
								{
									$ced_rnx_pages= get_option('ced_rnx_pages');
									$page_id = $ced_rnx_pages['pages']['ced_return_from'];
									$return_url = get_permalink($page_id);
									if( WC()->version < "3.0.0" )
									{
										$order_id=$order->id;
									}
									else
									{
										$order_id=$order->get_id();
									}
									$actions['return']['url'] = add_query_arg('order_id',$order_id,$return_url);
									$actions['return']['name'] = $return_button_text;
								}
							}	
						}
					}
				}
			}
			return $actions;
		}
		
		function ced_rnx_show_note_on_product()
		{
			global $post, $product;
			$product_id=$product->get_id();	
			//Return Product
			
			$return_enable = get_option('ced_rnx_return_enable', false);
			if(isset($return_enable) && !empty($return_enable))
			{
				if($return_enable == 'yes')
				{
					// print_r("expression");die;
					$show = true;
					$pro_categories = get_the_terms( $product_id, 'product_cat' );
					$productdata = new WC_Product($product_id);
					
					//Return Product
					$ced_rnx_ex_cats = get_option('ced_rnx_return_ex_cats', array());
					$ced_rnx_sale = get_option('ced_rnx_return_sale_enable', false);
					$sale_enable = false;
					
					if($ced_rnx_sale == 'yes')
					{
						$sale_enable = true;
					}
					$disable_product = get_post_meta($product_id, 'ced_rnx_disable_refund', true);

					if(isset($disable_product) && !empty($disable_product))
					{
						if($disable_product == "open")
						{
							$show = false;
						}
					}
					
					if(isset($pro_categories) && !empty($pro_categories))
					{
						foreach($pro_categories as $k=>$cat)
						{
							$cat = (array)$cat;

							if(in_array($cat['term_id'], $ced_rnx_ex_cats))
							{
								$show = false;
							}
						}
					}
					
					if($show)
					{
						if($sale_enable)
						{
							$show = true;
						}
						else
						{
							if($productdata->is_on_sale())
							{
								$show = false;
							}
						}
					}
						// print_r($show);die;

					if(!$show)
					{
						$ced_rnx_return_note_enable = get_option('ced_rnx_return_note_enable', false);
						$ced_rnx_return_note_message = get_option('ced_rnx_return_note_message', false);
						if(isset($ced_rnx_return_note_enable) && !empty($ced_rnx_return_note_enable))
						{
							if($ced_rnx_return_note_enable == 'yes')
							{
								?>
								<p><?php echo $ced_rnx_return_note_message?></p>
								<?php 
							}	
						}	
					}	
				}
			}
			
			//Exchange Product
			
			$exchange_enable = get_option('ced_rnx_exchange_enable', false);
			if(isset($exchange_enable) && !empty($exchange_enable))
			{
				if($exchange_enable == 'yes')
				{
					$show = true;
					$ced_rnx_ex_cats = get_option('ced_rnx_exchange_ex_cats', false);
					$ced_rnx_sale = get_option('ced_rnx_exchange_sale_enable', false);
					$sale_enable = false;

					if($ced_rnx_sale == 'yes')
					{
						$sale_enable = true;
					}
					
					$disable_product = get_post_meta($product_id, 'ced_rnx_disable_exchange', true);
					
					if(isset($disable_product) && !empty($disable_product))
					{
						if($disable_product == "open")
						{
							$show = false;
						}
					}

					if(isset($pro_categories) && !empty($pro_categories))
					{
						foreach($pro_categories as $k=>$cat)
						{
							$cat = (array)$cat;

							if(in_array($cat['term_id'], $ced_rnx_ex_cats))
							{
								$show = false;
							}
						}
					}

					if($show)
					{
						if($sale_enable)
						{
							$show = true;
						}
						else
						{
							if($productdata->is_on_sale())
							{
								$show = false;
							}
						}
					}
					if(!$show)
					{
						$ced_rnx_exchange_note_enable = get_option('ced_rnx_exchange_note_enable', false);
						$ced_rnx_exchange_note_message = get_option('ced_rnx_exchange_note_message', false);
						if(isset($ced_rnx_exchange_note_enable) && !empty($ced_rnx_exchange_note_enable))
						{
							if($ced_rnx_exchange_note_enable == 'yes')
							{
								?>
								<p><?php echo $ced_rnx_exchange_note_message?></p>
								<?php 
							}	
						}
					}
				}	
			}	
		}
		
		/**
		 * This is function to save meta field value for product
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		
		public function ced_rnx_save_product_meta()
		{
			global $post;
			if(isset($post->ID))
			{	
				$post_id = $post->ID;
				if(isset($_POST['ced_rnx_disable_refund']))
				{
					update_post_meta($post_id, 'ced_rnx_disable_refund', $_POST['ced_rnx_disable_refund']);
				}
				else 
				{
					update_post_meta($post_id, 'ced_rnx_disable_refund', false);
				}	
				if(isset($_POST['ced_rnx_disable_exchange']))
				{
					update_post_meta($post_id, 'ced_rnx_disable_exchange', $_POST['ced_rnx_disable_exchange']);
				}
				else
				{
					update_post_meta($post_id, 'ced_rnx_disable_exchange', false);
				}	
			}
		}
		
		/**
		 * This is function to add meta field for product
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_product_enable_rnx()
		{
			global $post;
			?>
			<div class="options_group reviews">
				<?php
				woocommerce_wp_checkbox( array( 'id' => 'ced_rnx_disable_refund', 'label' => __( 'Disable Refund', 'woocommerce-refund-and-exchange' ), 'cbvalue' => 'open', 'value' => esc_attr( $post->ced_rnx_disable_refund ) ) );
				?>
			</div>
			<div class="options_group reviews">
				<?php
				woocommerce_wp_checkbox( array( 'id' => 'ced_rnx_disable_exchange', 'label' => __( 'Disable Exchange', 'woocommerce-refund-and-exchange' ), 'cbvalue' => 'open', 'value' => esc_attr( $post->ced_rnx_disable_exchange ) ) );
				?>
			</div>
			<?php 
		}


		/**
		 * This function is to create template for Cancel Request
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 * @return string
		 */
		public function ced_product_cancel_template($template)
		{
			$ced_rnx_pages= get_option('ced_rnx_pages');
			$page_id = $ced_rnx_pages['pages']['ced_cancel_request_from'];
			if(is_page($page_id) && $page_id != '')
			{
				$located = locate_template('woocommerce-refund-and-exchange/template/ced-cancel-request-form.php');
				if ( !empty( $located ) ) {

					$new_template =wc_get_template('woocommerce-refund-and-exchange/template/ced-return-request-form.php');
				}
				else
				{
					$new_template = CED_REFUND_N_EXCHANGE_DIRPATH. 'template/ced-cancel-request-form.php';
				}
				$template =  $new_template;
			}
			return $template;
		}
		
		/**
		 * This function is to create template for Return Request
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 * @return string
		 */
		public function ced_product_return_template($template)
		{
			$ced_rnx_pages= get_option('ced_rnx_pages');
			$page_id = $ced_rnx_pages['pages']['ced_return_from'];
			if(is_page($page_id) && $page_id != '')
			{
				$located = locate_template('woocommerce-refund-and-exchange/template/ced-return-request-form.php');
				if ( !empty( $located ) ) {

					$new_template =wc_get_template('woocommerce-refund-and-exchange/template/ced-return-request-form.php');
				}
				else
				{
					$new_template = CED_REFUND_N_EXCHANGE_DIRPATH. 'template/ced-return-request-form.php';
				}
				$template =  $new_template;
			}
			return $template;
		}
		

		/**
		 * This function is to create template for Exchange Request
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 * @return string
		 */
		public function ced_product_exchange_template($template)
		{	
			$ced_rnx_pages= get_option('ced_rnx_pages');
			$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
			if(is_page($page_id) && $page_id != '')
			{
				$located = locate_template('woocommerce-refund-and-exchange/template/ced-exchange-request-form.php');
				if ( !empty( $located ) ) {

					$new_template =wc_get_template('woocommerce-refund-and-exchange/template/ced-exchange-request-form.php');
				}
				else
				{
					$new_template = CED_REFUND_N_EXCHANGE_DIRPATH. 'template/ced-exchange-request-form.php';
				}
				$template =  $new_template;
				unset($_SESSION['ced_rnx_exchange']);
				unset($_SESSION['ced_rnx_exchange_variable_product']);
			}
			return $template;
		}
		
		/**
		 * This function is to include CSS and js
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */ 
		public function ced_rnx_scripts()
		{
			$url = plugins_url();
			
			wp_enqueue_script( 'ced-rnx-script-select2', $url.'/woocommerce/assets/js/select2/select2.min.js', array('jquery'), CED_REFUND_N_EXCHANGE_VERSION, true );
			wp_register_script('ced-rnx-script-front', CED_REFUND_N_EXCHANGE_URL.'assets/js/ced-rnx-script.js', array('jquery', 'ced-rnx-script-select2') , CED_REFUND_N_EXCHANGE_VERSION, true );
			$ajax_nonce = wp_create_nonce( "ced-rnx-ajax-seurity-string" );
			$upload_url = home_url();
			
			$user_id = get_current_user_id();
			
			
			if($user_id > 0)
			{
				$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
				$myaccount_page_url = get_permalink( $myaccount_page );
			}
			else
			{
				$ced_rnx_pages= get_option('ced_rnx_pages');
				$page_id = $ced_rnx_pages['pages']['ced_request_from'];
				$myaccount_page_url = get_permalink($page_id);
			}	
			
			$return_auto_accept = get_option('ced_rnx_return_autoaccept_enable');
			if( WC()->version < "3.0.0" )
			{
				$shop_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
			}
			else
			{
				$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
			}

			$ced_rnx_exchange_variation_enable = get_option('ced_rnx_exchange_variation_enable', false);

			if($ced_rnx_exchange_variation_enable == 'yes')
			{
				$ced_rnx_exchange_variation_enable = true;
			}
			$ced_rnx_exchnage_with_same_product_text =get_option('ced_rnx_exchnage_with_same_product_text',__('Click on product(s) to exchange with selected product(s) or its variation(s).','woocommerce-refund-and-exchange'));
			$ced_rnx_price_deduct_message = get_option( 'ced_rnx_price_deduct_message', 'Price Deducted due to late refund' );
			$ced_rnx_enable_price_policy =  get_option( 'ced_rnx_enable_price_policy',false) ;
			// print_r($ced_rnx_price_deduct_message);die;
			if($ced_rnx_enable_price_policy != 'on')
			{
				$ced_rnx_price_deduct_message = __('Total Refund Amount','woocommerce-refund-and-exchange');
			}
			else
			{
				$ced_rnx_price_deduct_message = __('Total Refund Amount','woocommerce-refund-and-exchange') . '( ' .  $ced_rnx_price_deduct_message . ' )';
			}
			$ced_rnx_add_to_cart_enable = get_option('ced_rnx_add_to_cart_enable','no');
			$wallet_messege = __('My Wallet','woocommerce-refund-and-exchange');
			$translation_array = array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'ced_rnx_nonce'	=>	$ajax_nonce,
				'wallet_msg' => $wallet_messege,
				'myaccount_url' => $myaccount_page_url,
				'auto_accept' => $return_auto_accept,  
				'shop_url'    => $shop_url,
				'exchange_text' => __('Exchange','woocommerce-refund-and-exchange'),
				'ced_rnx_price_deduct_message' =>$ced_rnx_price_deduct_message,
				'select_product_msg' => __( 'Please select product you want to refund.', 'woocommerce-refund-and-exchange' ),
				'return_subject_msg' => __( 'Please enter refund subject.', 'woocommerce-refund-and-exchange' ),
				'return_reason_msg'	=> __( 'Please enter refund reason.', 'woocommerce-refund-and-exchange' ),
				'correct_quantity'	=> __( 'Please enter correct quantity.', 'woocommerce-refund-and-exchange' ),
				'select_product_msg_exchange' => __( 'Please select product you want to exchange.', 'woocommerce-refund-and-exchange' ),
				'exchange_subject_msg' => __( 'Please enter exchange subject.', 'woocommerce-refund-and-exchange' ),
				'exchange_reason_msg'	=> __( 'Please enter exchange reason.', 'woocommerce-refund-and-exchange' ),
				'before_submit_exchange'	=> __( 'Choose exchange products before submit request.', 'woocommerce-refund-and-exchange' ),
				'left_amount_msg'	=> __( 'Left Amount After Exchange.', 'woocommerce-refund-and-exchange' ),
				'extra_amount_msg' => __('Extra Amount Need to Pay', 'woocommerce-refund-and-exchange'),
				'exchange_session' => isset( $_SESSION['ced_rnx_exchange'] ) ? 1 : 0,
				'ced_rnx_exchange_variation_enable' => $ced_rnx_exchange_variation_enable,
				'ced_rnx_exchnage_with_same_product_text' => $ced_rnx_exchnage_with_same_product_text,
				'price_decimal_separator' =>  wc_get_price_decimal_separator(),
				'price_thousand_separator' => wc_get_price_thousand_separator(),
				'ced_rnx_add_to_cart_enable' => $ced_rnx_add_to_cart_enable,
				);
			wp_localize_script( 'ced-rnx-script-front', 'global_rnx', $translation_array );
			$ced_rnx_session_running = isset($_SESSION['ced_rnx_exchange']) ? 1 : 0 ;
			$redirect_uri = $_SERVER['REQUEST_URI'];

			if (strpos($redirect_uri, 'return-request-form') !== false || strpos($redirect_uri, 'exchange-request-form') !== false || $ced_rnx_session_running == 1 || strpos($redirect_uri, 'my-account') !== false || strpos($redirect_uri, 'product-cancel-request-form') !== false) 
			{
				wp_enqueue_script( 'ced-rnx-script-front' );
			}
			if (strpos($redirect_uri, 'return-request-form') !== false || strpos($redirect_uri, 'exchange-request-form') !== false || strpos($redirect_uri, 'product-cancel-request-form') !== false) 
			{
				wp_enqueue_style( 'ced-rnx-style-front', CED_REFUND_N_EXCHANGE_URL.'assets/css/ced-rnx-front.css' );
				wp_enqueue_style('ced-rnx-style-select2', $url.'/woocommerce/assets/css/select2.css');
			}
		}

		/**
		 * This function is to add custom order status for return and exchange
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_register_custom_order_status()
		{
			register_post_status( 'wc-return-requested', array(
				'label'                     => 'Refund Requested',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Refund Requested <span class="count">(%s)</span>', 'Refund Requested <span class="count">(%s)</span>' )
			) );

			register_post_status( 'wc-return-approved', array(
				'label'                     => 'Refund Approved',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Refund Approved <span class="count">(%s)</span>', 'Refund Approved <span class="count">(%s)</span>' )
			) );

			register_post_status( 'wc-return-cancelled', array(
				'label'                     => 'Refund Cancelled',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Refund Cancelled <span class="count">(%s)</span>', 'Refund Cancelled <span class="count">(%s)</span>' )
			) );
			
			register_post_status( 'wc-exchange-request', array(
				'label'                     => 'Exchange Requested',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Exchange Requested <span class="count">(%s)</span>', 'Exchange Requested <span class="count">(%s)</span>' )
			) );
			
			register_post_status( 'wc-exchange-approve', array(
				'label'                     => 'Exchange Approved',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Exchange Approved <span class="count">(%s)</span>', 'Exchange Approved <span class="count">(%s)</span>' )
			) );
			
			register_post_status( 'wc-exchange-cancel', array(
				'label'                     => 'Exchange Cancelled',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Exchange Cancelled <span class="count">(%s)</span>', 'Exchange Cancelled <span class="count">(%s)</span>' )
			) );

			register_post_status( 'wc-partial-cancel', array(
				'label'                     => 'Partially Cancelled',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Partially Cancelled <span class="count">(%s)</span>', 'Partially Cancelled <span class="count">(%s)</span>' )
			) );
			
		}
		
		/**
		 * This function is to register custom order status
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $order_statuses
		 * @return multitype:string unknown
		 */
		function ced_rnx_add_custom_order_status($order_statuses)
		{
			$new_order_statuses = array();
			foreach ( $order_statuses as $key => $status ) {

				$new_order_statuses[ $key ] = $status;

				if ( 'wc-completed' === $key ) {
					$new_order_statuses['wc-return-requested'] = __('Refund Requested','woocommerce-refund-and-exchange');
					$new_order_statuses['wc-return-approved']  = __('Refund Approved','woocommerce-refund-and-exchange');
					$new_order_statuses['wc-return-cancelled'] = __('Refund Cancelled','woocommerce-refund-and-exchange');
					$new_order_statuses['wc-exchange-request'] = __('Exchange Requested','woocommerce-refund-and-exchange');
					$new_order_statuses['wc-exchange-approve'] = __('Exchange Approved','woocommerce-refund-and-exchange');
					$new_order_statuses['wc-exchange-cancel'] = __('Exchange Cancelled','woocommerce-refund-and-exchange');
					$new_order_statuses['wc-partial-cancel'] = __('Partially Cancelled','woocommerce-refund-and-exchange');
				}
			}

			return $new_order_statuses;
		}
		
	}
	new Ced_refund_and_exchange();
}
?>