<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'CED_rnx_order_exchange' ) ){

	/**
	 * This is class for managing exchange process at front end.
	 *
	 * @name    CED_rnx_order_exchange
	 * @category Class
	 * @author   makewebbetter <webmaster@makewebbetter.com>
	 */
	class CED_rnx_order_exchange{
		
		/**
		 * This function is for construct of class
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function __construct(){
			
			add_action( 'woocommerce_order_details_after_order_table',array($this, 'ced_rnx_order_exchange_button'));
			add_action( 'wp_ajax_ced_rnx_exchange_products', array($this, 'ced_rnx_exchange_products_callback'));
			add_action( 'wp_ajax_nopriv_ced_rnx_exchange_products', array($this, 'ced_rnx_exchange_products_callback'));
			add_action( 'wp_ajax_ced_set_exchange_session', array($this, 'ced_rnx_set_exchange_session'));
			add_action( 'wp_ajax_nopriv_ced_set_exchange_session', array($this, 'ced_rnx_set_exchange_session'));
			add_action( 'woocommerce_after_shop_loop_item', array($this, 'ced_rnx_add_exchange_products'), 8);
			add_action( 'wp_ajax_ced_rnx_add_to_exchange', array($this, 'ced_rnx_add_to_exchange_callback'));
			add_action( 'wp_ajax_nopriv_ced_rnx_add_to_exchange', array($this, 'ced_rnx_add_to_exchange_callback'));
			add_action( 'wp_ajax_ced_rnx_exchnaged_product_remove', array($this, 'ced_rnx_exchnaged_product_remove_callback'));
			add_action( 'wp_ajax_nopriv_ced_rnx_exchnaged_product_remove', array($this, 'ced_rnx_exchnaged_product_remove_callback'));
			add_action( 'woocommerce_after_add_to_cart_form', array($this, 'ced_rnx_exchnaged_product_add_button'));
			add_action( 'wp_ajax_ced_rnx_submit_exchange_request', array($this, 'ced_rnx_submit_exchange_request_callback'));
			add_action( 'wp_ajax_nopriv_ced_rnx_submit_exchange_request', array($this, 'ced_rnx_submit_exchange_request_callback'));
			add_action( 'woocommerce_thankyou', array($this, 'ced_rnx_exchange_pay_cancel'), 10, 1);
		
		add_filter( 'woocommerce_my_account_my_orders_actions', array($this,'ced_rnx_my_account_my_orders_actions'), 100, 2 );
		}


		/**
		 * This function is to remove cancel button from my order detail page.
		 *
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_my_account_my_orders_actions( $actions, $order ) 
		{
			if($order->get_status() == 'exchange-approve')
			{
				unset($actions['cancel']);
			}
			return $actions;
			
		}
		
		/**
		 * This function is to add pay or cancel button for guest user
		 *
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_exchange_pay_cancel($order_id)
		{
			$order = wc_get_order($order_id);
			$payment_url = $order->get_checkout_payment_url();
			$cancel_url = $order->get_cancel_order_url( wc_get_page_permalink( 'myaccount' ) );
			if ( $order->needs_payment() ) {
			?>
				<a class="button pay" href="<?php echo $payment_url;?>"><?php _e('Pay', 'woocommerce-refund-and-exchange');?></a>
			<?php 
			}
		}

		/**
		 * This function is to submit exchange product request
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_submit_exchange_request_callback()
		{
			$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
			if ( $check_ajax )
			{
				$order_id = $_POST['orderid'];
				$current_user = wp_get_current_user();
				$user_email = $current_user->user_email;
				$user_name = $current_user->display_name;
				$order_id = $_POST['orderid'];
				$subject = $_POST['subject'];
				$reason = $_POST['reason'];
				
				//Save Exchange Request Product
				
				$products = array();
				if(isset($_SESSION['exchange_requset']))
				{
					$products = $_SESSION['exchange_requset'];
				}	

				$pending = true;
				if(isset($products) && !empty($products))
				{
					foreach($products as $date=>$product)
					{
						if($product['status'] == 'pending')
						{
							$products[$date]['orderid'] = $_POST['orderid'];
							$products[$date]['subject'] = $_POST['subject'];
							$products[$date]['reason'] = $_POST['reason'];
							$products[$date]['status'] = 'pending'; //update requested products
							$pending = false;
							break;
						}
					}
				}
				if($pending)
				{
					$date = date("d-m-Y");
					$products = array();
					$products[$date]['orderid'] = $_POST['orderid'];
					$products[$date]['subject'] = $_POST['subject'];
					$products[$date]['reason'] = $_POST['reason'];
					$products[$date]['status'] = 'pending';
				}
					
				update_post_meta($order_id, 'ced_rnx_exchange_product', $products);
				
				$exchange_subject = $subject;
				$mail_header = stripslashes(get_option('ced_rnx_notification_mail_header', false));
				$mail_footer = stripslashes(get_option('ced_rnx_notification_mail_footer', false));
				
				$message = '<html>
								<body>
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
								<div class="header" style="text-align: center; padding: 10px;">
									'.$mail_header.'
								</div>
								<div class="header">
									<h2>'.$exchange_subject.'</h2>
								</div>
								<div class="content">
									<div class="reason">
										<h4>'.__('Reason of Exchange', 'woocommerce-refund-and-exchange').'</h4>
										<p>'.$reason.'</p>
									</div>
									<div class="Order">
										<h4>Order #'.$order_id.'</h4>
										<h4>'.__('Exchanged From', 'woocommerce-refund-and-exchange').'</h4>
										<table>
											<tbody>
												<tr>
													<th>'.__('Product', 'woocommerce-refund-and-exchange').'</th>
													<th>'.__('Quantity', 'woocommerce-refund-and-exchange').'</th>
													<th>'.__('Price', 'woocommerce-refund-and-exchange').'</th>
												</tr>';
								$order = wc_get_order($order_id);
								$requested_products = $products[$date]['from'];
								if(ced_rnx_wc_vendor_addon_enable())
								{
									$ced_vendor_emails = array();
								}
								
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
								$message .= '
											<tr>
												<th colspan="2">'.__('Total', 'woocommerce-refund-and-exchange').':</th>
												<td>'.ced_rnx_format_price($total).'</td>
											</tr>
										</tbody>
									</table>	
									<h4>'.__('Exchanged To', 'woocommerce-refund-and-exchange').'</h4>
									<table>
										<tbody>
											<tr>
												<th>'.__('Product', 'woocommerce-refund-and-exchange').'</th>
												<th>'.__('Quantity', 'woocommerce-refund-and-exchange').'</th>
												<th>'.__('Price', 'woocommerce-refund-and-exchange').'</th>
											</tr>';
									$exchanged_to_products = $products[$date]['to'];
									$total_price = 0;
									if(isset($exchanged_to_products) && !empty($exchanged_to_products))
									{
										foreach($exchanged_to_products as $key=>$exchanged_product)
										{
											$variation_attributes = array();
											if(isset($exchanged_product['variation_id']))
											{
												if($exchanged_product['variation_id'])
												{
													$variation_product = new WC_Product_Variation($exchanged_product['variation_id']);
													$variation_attributes = $variation_product->get_variation_attributes();
													$variation_labels = array();
													foreach ($variation_attributes as $label => $value)
													{
														if(is_null($value) || $value == ''){
															$variation_labels[] = $label;
														}
													}
													if(isset($exchanged_product['variations']) && !empty($exchanged_product['variations']))
													{
														$variation_attributes = $exchanged_product['variations'];
													}	
												}
											}
			
											if(isset($exchanged_product['p_id']))
											{
												if($exchanged_product['p_id'])
												{
													$grouped_product = new WC_Product_Grouped($exchanged_product['p_id']);
													$grouped_product_title = $grouped_product->get_title();
												}
											}
		
											$pro_price = $exchanged_product['qty']*$exchanged_product['price'];
											$total_price += $pro_price;
											$product = new WC_Product($exchanged_product['id']);
											$title = "";
											if(isset($exchanged_product['p_id']))
											{
												$title .= $grouped_product_title.' -> ';
											}
											$title .=$product->get_title();
	
											if(isset($variation_attributes) && !empty($variation_attributes))
											{
												$title .= wc_get_formatted_variation( $variation_attributes );
											}
											$message .= '<tr>
															<td>'.$title.'</td>
															<td>'.$exchanged_product['qty'].'</td>
															<td>'.ced_rnx_format_price($pro_price).'</td>
														</tr>';
										}
									}
									$message .= '<tr>
													<th colspan="2">'.__('Total', 'woocommerce-refund-and-exchange').':</th>
													<td>'.ced_rnx_format_price($total_price).'</td>
												</tr>
											</tbody>
										</table>
									</div>';
									if($total_price - $total > 0)
									{
										$extra_amount = $total_price - $total;
										$message .= '<h2>'.__('Extra Amount', 'woocommerce-refund-and-exchange').' : '.ced_rnx_format_price($extra_amount).'</h2>';							
									}		
									$message .= ' <div class="Customer-detail">
														<h4>'.__('Customer details', 'woocommerce-refund-and-exchange').'</h4>
														<ul>
															<li><p class="info">
																	<span class="bold">'.__('Email','woocommerce-refund-and-exchange').': </span>'.get_post_meta($order_id, '_billing_email', true).'
																</p></li>
															<li><p class="info">
																	<span class="bold">'.__('Tel','woocommerce-refund-and-exchange').': </span>'.get_post_meta($order_id, '_billing_phone', true).'
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
												<div class="footer" style="text-align: center; padding: 10px;">
													'.$mail_footer.'
												</div>
											</body>
											</html>';
				
				//Send mail to merchant
				
				$headers = array();
				
				$headers[] = "Content-Type: text/html; charset=UTF-8";
				$to = get_option('ced_rnx_notification_from_mail');
				$subject = get_option('ced_rnx_notification_merchant_exchange_subject');
				$subject = str_replace('[order]', "#".$order_id, $subject);
				
				
				wc_mail( $to, $subject, $message, $headers );
				if(ced_rnx_wc_vendor_addon_enable())
				{
					if(isset($ced_vendor_emails) && is_array($ced_vendor_emails) && !empty($ced_vendor_emails))
					{
						foreach ($ced_vendor_emails as $vendor_email) {
							wc_mail( $vendor_email, $subject, $message, $headers );
						}
					}
				}
								
				//Send mail to User that we recieved your request
				
				$fname = get_option('ced_rnx_notification_from_name');
				$fmail = get_option('ced_rnx_notification_from_mail');
				$to = get_post_meta($order_id, '_billing_email', true);;
				$headers[] = "From: $fname <$fmail>";
				$headers[] = "Content-Type: text/html; charset=UTF-8";
				$subject = get_option('ced_notification_exchange_subject');
				$message = stripslashes(get_option('ced_notification_exchange_rcv'));
				
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
				
				$fullname = $fname." ".$lname;
				
				$message = str_replace('[username]', $fullname, $message);
				$message = str_replace('[order]', "Order #".$order_id, $message);
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

				$subject = str_replace('[username]', $fullname, $subject);
				$subject = str_replace('[order]', "Order #".$order_id, $subject);
				$subject = str_replace('[siteurl]', home_url(), $subject);
				
				$mail_header = str_replace('[username]', $fullname, $mail_header);
				$mail_header = str_replace('[order]', "Order #".$order_id, $mail_header);
				$mail_header = str_replace('[siteurl]', home_url(), $mail_header);
				
				$template = get_option('ced_notification_exchange_template','no');

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
										<body>
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
			
				update_post_meta($order_id, "ced_rnx_request_made", true);
				
				$order = new WC_Order($order_id);
				$order->update_status('wc-exchange-request', __('User Request to Exchange Product','woocommerce-refund-and-exchange'));
				unset($_SESSION['exchange_requset']);
				unset($_SESSION['ced_rnx_exchange']);
				$response['msg'] = __('Message send successfully.You have received a notification mail regarding this, Please check your mail. Soon You redirect to My Account Page. Thanks', 'woocommerce-refund-and-exchange');
				echo json_encode($response);
				die;
			}
		}
		
		/**
		 * This function is to add button on order detail page
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_exchnaged_product_add_button()
		{
			global $product;
			$sku = $product->get_sku();
			if( WC()->version < "3.0.0" )
			{
				$product_id = $product->id;
				$product_type=$product->product_type;
				$price = $product->get_display_price();
			}
			else
			{	
				$product_id = $product->get_id();
				$product_type=$product->get_type();
				$price = wc_get_price_including_tax($product);
			}
			
			$ced_rnx_exchange_variation_enable = get_option('ced_rnx_exchange_variation_enable', false);

			if($ced_rnx_exchange_variation_enable == 'yes')
			{
				if(  $product_type == 'variable' && $product -> is_in_stock())
				{
					if(isset($_SESSION['ced_rnx_exchange_variable_product']))
					{
						foreach ($_SESSION['ced_rnx_exchange_variable_product'] as $key => $value) {
							if($value == get_the_ID()){
							?>
							<div class="ced_rnx_exchange_wrapper">
								<button  data-product_id="<?php echo $product_id?>" class="ced_rnx_add_to_exchanged_detail_variable button alt">
									<?php echo apply_filters('ced_rnx_exchange_product_button', __('Exchange','woocommerce-refund-and-exchange'));?>
								</button>
							</div>
							<?php
							}
						}
					}
				}
				if(  $product_type == 'simple' && $product -> is_in_stock())
				{
					if(isset($_SESSION['ced_rnx_exchange_variable_product']))
					{
						foreach ($_SESSION['ced_rnx_exchange_variable_product'] as $key => $value) {
							if($value == get_the_ID()){
							?>
							<div class="ced_rnx_exchange_wrapper"><button data-price="<?php echo $price; ?>" data-product_sku="<?php echo $sku?>" data-product_id="<?php echo $product_id?>" class="ced_rnx_add_to_exchanged_detail button alt"><?php echo apply_filters('ced_rnx_exchange_product_button', __('Exchange','woocommerce-refund-and-exchange'));?></button></div>
							<?php
							}
						}
					}
				}
			}
			else if(isset($_SESSION['ced_rnx_exchange']))
			{
				if(  $product_type == 'simple'  && $product -> is_in_stock())
				{
					if(isset($_SESSION['ced_rnx_exchange']))
					{	
						?>
						<div class="ced_rnx_exchange_wrapper"><button data-price="<?php echo $price; ?>" data-product_sku="<?php echo $sku?>" data-product_id="<?php echo $product_id?>" class="ced_rnx_add_to_exchanged_detail button alt"><?php echo apply_filters('ced_rnx_exchange_product_button', __('Exchange','woocommerce-refund-and-exchange'));?></button></div>
						<?php
					}
				}
				if(  $product_type == 'variable'  && $product -> is_in_stock())
				{
					
					if(isset($_SESSION['ced_rnx_exchange']))
					{
						?>
						<div class="ced_rnx_exchange_wrapper">
							<button  data-product_id="<?php echo $product_id?>" class="ced_rnx_add_to_exchanged_detail_variable button alt">
								<?php echo apply_filters('ced_rnx_exchange_product_button', __('Exchange','woocommerce-refund-and-exchange'));?>
							</button>
						</div>
						<?php
					}
				}
				if(  $product_type == 'grouped'  && $product -> is_in_stock())
				{
					if(isset($_SESSION['ced_rnx_exchange']))
					{
						?>
						<div class="ced_rnx_exchange_wrapper"><button data-price="<?php echo $price; ?>" data-product_sku="<?php echo $sku?>" data-product_id="<?php echo $product_id?>" class="ced_rnx_add_to_exchanged_detail button alt"><?php echo apply_filters('ced_rnx_exchange_product_button', __('Exchange','woocommerce-refund-and-exchange'));?></button></div>
						<?php
					}
				}
			}		
		}
		
		/**
		 * This function is to remove exchange product
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_exchnaged_product_remove_callback()
		{
			$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
			if ( $check_ajax )
			{
				$order_id = $_POST['orderid'];
				$key = $_POST['id'];
				
				$exchange_details = array();
				if(isset($_SESSION['exchange_requset']))
				{
					$exchange_details = $_SESSION['exchange_requset'];
				}
				
				if(isset($exchange_details) && !empty($exchange_details))
				{
					foreach($exchange_details as $date=>$exchange_detail)
					{
						if($exchange_detail['status'] == 'pending')
						{
							$exchange_products = $exchange_detail['to'];
							unset($exchange_products[$key]);
							$exchange_details[$date]['to'] = $exchange_products;
							break;
						}
					}
				}	

				$_SESSION['exchange_requset'] = $exchange_details;
				$exchange_details = array();
				if(isset($_SESSION['exchange_requset']))
				{
					$exchange_details = $_SESSION['exchange_requset'];
				}	
				
				$total_price = 0;
				
				if(isset($exchange_products) && !empty($exchange_products))
				{
					foreach($exchange_products as $key=>$exchanged_product)
					{
						$pro_price = $exchanged_product['qty']*$exchanged_product['price'];
						$total_price += $pro_price;
					}	
				}
				$response['response'] = 'success';
				$response['total_price'] = $total_price;
				echo json_encode($response);
				die;
			}
		}
		
		/**
		 * This function is to add exchange product
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_add_to_exchange_callback()
		{
			$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
			if ( $check_ajax ) 
			{
				$products = array();
				$order_id = $_SESSION['ced_rnx_exchange'];
				$exchange_product = $_POST['products'];
				$product_id = $exchange_product['id'];
				
				//Start for variation
				$adding_to_cart      = wc_get_product( $product_id );
				
				if(isset($exchange_product['variation_id']))
				{
					$product_variation = new WC_Product_Variation($exchange_product['variation_id']);
					if( WC()->version < "3.0.0" )
					{
						$exchange_product['price'] = $product_variation->get_display_price();
					}
					else
					{
						$exchange_product['price'] =wc_get_price_including_tax($product_variation);;
					}
				}	
					
				$exchange_details = array();
				if(isset($_SESSION['exchange_requset']))
				{
					$exchange_details = $_SESSION['exchange_requset'];
				}	
				
				$pending = true;
				
				if(isset($exchange_details) && !empty($exchange_details))
				{
					foreach($exchange_details as $date=>$exchange_detail)
					{
						if($exchange_detail['status'] == 'pending')
						{
							$pending_key = $date;
							if(isset($exchange_detail['to']))
							{
								$exchange_products = $exchange_detail['to'];
							}
							else
							{
								$exchange_products = array();
							}	
							$pending = false;
							break;
						}
					}
				}
				
				if($pending)
				{
					$exchange_products = array();
				}
				
				if(isset($exchange_product['grouped']))
				{
					$exchange_pro = array();
					foreach($exchange_product['grouped'] as $k=>$val)
					{
						$g_child = array();
						$child_product = new WC_Product($k);
						$g_child['id'] = $k;
						$g_child['qty'] = $val;
						$g_child['sku'] = $child_product->get_sku();
						if( WC()->version < "3.0.0" )
						{
							$g_child['price'] = $child_product->get_display_price();
						}
						else
						{
							$g_child['price'] =$child_product->get_price();
						}
						$exchange_pro[] = $g_child;
					}
					$exchange_product = $exchange_pro;
				}
				// print_r($exchanged_products);die;
				if(isset($exchange_products) && !empty($exchange_products))
				{
					foreach($exchange_products as $key=>$product )
					{
						$exist = true;
						if(!isset($exchange_product['id']))
						{
							if(is_array($exchange_product))
							{
								foreach($exchange_product as $a=>$exchange_pro)
								{
									if($product['id'] == $exchange_pro['id'] && $product['sku'] == $exchange_pro['sku'])
									{
										$count++;
										$exist = false;
										$exchange_products[$key]['qty'] += $exchange_pro['qty'];
										unset($exchange_product[$a]);
									}	
								}	
							}
						}
						elseif($product['id'] == $exchange_product['id'] )
						{
							if(isset($exchange_product['variation_id']))
							{
								if($product['variation_id'] == $exchange_product['variation_id'])
								{
									$var_matched = true;
									if(isset($exchange_product['variations']) && !empty($exchange_product['variations']))
									{
										$saved_product_variations = $exchange_product['variations'];
										foreach($saved_product_variations as $saved_product_key=>$saved_product_variation)
										{
											if(array_key_exists($saved_product_key,$product['variations']))
											{
												if($product['variations'][$saved_product_key] != $saved_product_variation)
												{
													$var_matched = false;
												}
											}	
										}	
									}
									
									if($var_matched)
									{	
										$exist = false;
										$exchange_products[$key]['qty'] += $exchange_product['qty'];
										break;
									}
								}	
							}
							else 
							{
								$exist = false;
								$exchange_products[$key]['qty'] += $exchange_product['qty'];
								break;
							}	
						}	
					}
					
					if(isset($exchange_product))
					{
						if(!isset($exchange_product['id']))
						{
							if(is_array($exchange_product))
							{
								foreach($exchange_product as $a=>$exchange_pro)
								{
									$exchange_products[] = $exchange_pro;
									unset($exchange_product[$a]);
								}	
							}
						}	
					}
					if($exist)
					{
						if(!empty($exchange_product))
						{
							$exchange_products[] = $exchange_product;
						}	
					}
				}
				else 
				{
					if(isset($exchange_product))
					{
						if(!isset($exchange_product['id']))
						{
							if(is_array($exchange_product))
							{
								foreach($exchange_product as $a=>$exchange_pro)
								{
									$exchange_products[] = $exchange_pro;
								}	
							}
						}
						elseif(!empty($exchange_product))
						{
							$exchange_products[] = $exchange_product;
						}	
					}
				}	
				
				if($pending)
				{
					$exchange_details = array();
					$date = date("d-m-Y");
					$exchange_details[$date]['to'] = $exchange_products;
				}
				else
				{
					$exchange_details[$pending_key]['to'] = $exchange_products;
				}
				
				$_SESSION['exchange_requset'] = $exchange_details;
				
				$response['response'] = 'success';
				$response['message'] = apply_filters('ced_rnx_product_exchanged_message', __('View Order', 'woocommerce-refund-and-exchange'));
				$ced_rnx_pages = get_option('ced_rnx_pages', false);
				$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
				$exchange_url = get_permalink($page_id);
				$response['url'] = add_query_arg('order_id',$order_id,$exchange_url);
				echo json_encode($response);
				die;
			}
			
		}
		/**
		 * This function is add exchange button on product detail page
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		
		function ced_rnx_add_exchange_products()
		{
			global $product;
			if( WC()->version < "3.0.0" )
			{
				$product_type=$product->product_type;
				$product_id=$product->id;
				$price = $product->get_display_price();
			}
			else
			{	
				$product_id=$product->get_id();
				$product_type=$product->get_type();
				$price=wc_get_price_including_tax($product);
			}
			$ced_rnx_exchange_variation_enable = get_option('ced_rnx_exchange_variation_enable', false);

			$ced_rnx_add_to_cart_enable = get_option('ced_rnx_add_to_cart_enable','no');
			if($ced_rnx_exchange_variation_enable == 'yes' && isset($_SESSION['ced_rnx_exchange_variable_product']))
			{
				if($ced_rnx_add_to_cart_enable != 'yes')
				{
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
				}
			}
			else if(isset($_SESSION['ced_rnx_exchange']))
			{ 
				if($ced_rnx_add_to_cart_enable != 'yes')
				{
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
				}
				if($product_type == 'simple' && $product -> is_in_stock())
				{
					?>
					<div class="ced_rnx_exchange_wrapper"><a class="button ced_rnx_ajax_add_to_exchange" data-product_sku="<?php echo $product->get_sku();?>" data-product_id="<?php echo $product_id;?>" data-quantity="1" data-price="<?php echo $price;?>"><?php echo apply_filters('ced_rnx_exchange_product_button', __('Exchange','woocommerce-refund-and-exchange'));?></a></div>
					<?php
				}
			}
		}
		
		/**
		 * This function is used to set session for exchange request
		 *  
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_set_exchange_session()
		{
			$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
			if ( $check_ajax ) 
			{
				if(isset($_POST['products']))
				{
					$orderid = $_POST['orderid'];
					$_SESSION['ced_rnx_exchange'] = $orderid;
					$ced_rnx_exchange_variation_enable = get_option('ced_rnx_exchange_variation_enable', false);

					if($ced_rnx_exchange_variation_enable == 'yes')
					{
						$product_ids = array();
						foreach ($_POST['products'] as $key => $value) {
							$product_ids[]=$value['product_id'];
						}
						$_SESSION['ced_rnx_exchange_variable_product'] = $product_ids;
					}
					
					return 'true';
					wp_die();
				}
			}
		}
		/**
		 * This function is used to save selected exchange products
		 *  
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_exchange_products_callback()
		{
			$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
			if ( $check_ajax ) 
			{
				if(isset($_POST['orderid']))
				{
					$orderid = $_POST['orderid'];
					$mwb_rnx_obj = wc_get_order( $orderid );
					$mwb_cpn_dis = $mwb_rnx_obj->get_discount_total();
					$mwb_cpn_tax = $mwb_rnx_obj->get_discount_tax();
					$mwb_dis_tot = $mwb_cpn_dis + $mwb_cpn_tax;
					$mwb_dis_tot = 0;
					if(isset($_POST['products']))
					{
						if ( strpos($_SERVER['REQUEST_URI'],'/exchange-request-form/') >= 0 ) {
						unset( $_SESSION['ced_rnx_exchange'] );
						}
						$products  = array();
						if(isset($_SESSION['exchange_requset']))
						{
							$products = $_SESSION['exchange_requset'];
						}	
						
						$pending = true;
						if(isset($products) && !empty($products))
						{
							foreach($products as $date=>$product)
							{
								if($product['status'] == 'pending')
								{
									$products[$date]['status'] = 'pending'; //update requested products
									$products[$date]['from'] = $_POST['products'];
									$pending = false;
									break;
								}
							}
						}
						if($pending)
						{
							$date = date("d-m-Y");
							$products = array();
							$products[$date]['status'] = 'pending';
							$products[$date]['from'] = $_POST['products'];
						}
						
						$_SESSION['exchange_requset'] = $products;
						
						$response['response'] = 'success';
						$response['mwb_coupon_amt'] = 0;
						echo json_encode($response);
						die;
					}
					else
					{
						$response['mwb_coupon_amt'] = 0;
						echo json_encode($response);
						die;
					}
				}
			}
		}
		/**
		 * This function is to add exchange button and Show exchange products
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function  ced_rnx_order_exchange_button($order)
		{
			$ced_rnx_show_exchange_button = true;
			$ced_rnx_next_exchange = true;
			$items = $order->get_items();
			$ced_rnx_catalog=get_option('catalog',array());
			if(is_array($ced_rnx_catalog) && !empty($ced_rnx_catalog) )
			{	
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
									$ced_rnx_catalog_exchange[]=$value['exchange'];
								}
								
							}
						}
					}
				}
				if(is_array($ced_rnx_catalog_exchange) && !empty($ced_rnx_catalog_exchange))
				{
					$ced_rnx_catalog_exchange_days=max($ced_rnx_catalog_exchange);
				}
			}
			$ced_rnx_enable = get_option('ced_rnx_return_exchange_enable', false);
			
			if( WC()->version < "3.0.0" )
			{
				$order_id = $order->id;
			}
			else
			{
				$order_id = $order->get_id();
			}
			if($ced_rnx_enable == 'yes')
			{
				$ced_rnx_made = get_post_meta($order_id, "ced_rnx_request_made", true);
				if(isset($ced_rnx_made) && !empty($ced_rnx_made))
				{
					$ced_rnx_next_exchange = false;
				}
			}
			
			$ced_rnx_exchange = get_option('ced_rnx_exchange_enable', false);
			if($ced_rnx_exchange == 'yes')
			{
				$statuses = get_option('ced_rnx_exchange_order_status', array());
				$order_status ="wc-".$order->get_status();
				$exchanged_details = get_post_meta($order_id, 'ced_rnx_exchange_product', true);
				if(isset($exchanged_details) && !empty($exchanged_details))
				{
					foreach($exchanged_details as $key=>$exchanged_detail)
					{
						if(isset($exchanged_detail['from']) && isset($exchanged_detail['to']) && isset($exchanged_detail['orderid']))
						{
							$selected_total_price = 0;
							$date=date_create($key);
							$date_format = get_option('date_format');
							$date=date_format($date,$date_format);
							?>
							<p><?php _e( 'Following product exchange request is made on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $date?>.</b></p>
							<?php 
							$exchanged_products = $exchanged_detail['from'];
							$exchanged_to_products = $exchanged_detail['to'];
							
							if(isset($exchanged_detail['fee']))
								$exchanged_fees = $exchanged_detail['fee'];
							else 
								$exchanged_fees = array();
							?>
							<h2><?php _e( 'Exchange Requested Product', 'woocommerce-refund-and-exchange' ); ?></h2>
							<table class="shop_table order_details">
								<thead>
									<tr>
										<th class="product-name"><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
										<th class="product-total"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
									</tr>
								</thead>
								<tbody>
								<?php 
								foreach( $order->get_items() as $item_id => $item )
								{
									foreach($exchanged_products as $key=>$exchanged_product)
									{
										if($exchanged_product['item_id'] == $item_id)
										{	
											$pro_price = $exchanged_product['qty']*$exchanged_product['price'];
											$selected_total_price += $pro_price;
											?>
											<tr>
												<td class="product-name">
												<?php
													$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
													$is_visible        = $product && $product->is_visible();
													$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
													
													echo $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'];
													echo '<strong class="product-quantity">' . sprintf( '&times; %s', $exchanged_product['qty'] ) . '</strong>';
													
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
												<td class="product-total"><?php echo ced_rnx_format_price($pro_price);?></td>
											</tr>
												<?php 
												}
											}
										}		
										?>
										<tr>
											<th><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
											<th><?php echo ced_rnx_format_price($selected_total_price); ?></th>
										</tr>
									</tbody>
								</table>
								<h2><?php _e( 'Exchanged Product', 'woocommerce-refund-and-exchange' ); ?></h2>
								<table class="shop_table order_details">
									<thead>
										<tr>
											<th class="product-name"><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
											<th class="product-total"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php 
									$total_price = 0;
									foreach($exchanged_to_products as $key=>$exchanged_product)
									{
										$variation_attributes = array();
										if(isset($exchanged_product['variation_id']))
										{
											if($exchanged_product['variation_id'])
											{
												$variation_product = new WC_Product_Variation($exchanged_product['variation_id']);
												$variation_attributes = isset($exchanged_product['variations']) ? $exchanged_product['variations'] : $variation_product->get_variation_attributes();
												$variation_labels = array();
												foreach ($variation_attributes as $label => $value){
													if(is_null($value) || $value == ''){
														$variation_labels[] = $label;
													}
												}
												if(count($variation_labels)){
													$all_line_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
													$var_attr_info = array();
													foreach($all_line_items as $ear_item){
														$variationID = isset($ear_item['item_meta']['_variation_id']) ? $ear_item['item_meta']['_variation_id'][0] : 0;
															
														if($variationID && $variationID == $exchanged_product['variation_id']){
															$itemMeta = isset($ear_item['item_meta']) ? $ear_item['item_meta'] : array();
																
															foreach($itemMeta as $metaKey=>$metaInfo){
																$metaName = 'attribute_'. sanitize_title( $metaKey );
																if(in_array($metaName, $variation_labels)){
																	$variation_attributes[$metaName] = isset( $term->name ) ? $term->name : $metaInfo[0];
																}
															}
														}
													}
												}								
															}	
										}
					
										if(isset($exchanged_product['p_id']))
										{
											if($exchanged_product['p_id'])
											{
												$grouped_product = new WC_Product_Grouped($exchanged_product['p_id']);
												$grouped_product_title = $grouped_product->get_title();
											}
										}
										
										$pro_price = $exchanged_product['qty']*$exchanged_product['price'];
										$total_price += $pro_price;
										$product = new WC_Product($exchanged_product['id']);
										?>
										<tr>
											<td>
											<?php 
												if(isset($exchanged_product['p_id']))
												{
													echo $grouped_product_title.' -> ';
												}
												echo $product->get_title(); 
												echo '<b> × '.$exchanged_product['qty'].'</b>';
												if(isset($variation_attributes) && !empty($variation_attributes))
												{
													echo wc_get_formatted_variation( $variation_attributes );
												}
												?>
											</td>
											<td><?php echo ced_rnx_format_price($pro_price);?></td>
										</tr>
										<?php 
									}
									if(isset($exchanged_fees))
									{
										if(is_array($exchanged_fees))
										{
											?>
											<tr>
												<th colspan="2"><?php _e('Extra Cost', 'woocommerce-refund-and-exchange') ?></th>
											</tr>
											<?php 
											foreach($exchanged_fees as $fee)
											{
												?>
												<tr>
													<th><?php echo $fee['text'];?></th>
													<td><?php echo ced_rnx_format_price($fee['val']);?></td>
												</tr>
												<?php 
												$total_price += $fee['val'];
											}	
										}
									}	
									?>
									<tr>
										<th><?php _e( 'Total Amount', 'woocommerce-refund-and-exchange' ); ?></th>
										<th><?php echo ced_rnx_format_price($total_price); ?></th>
									</tr>
								</tbody>
							</table>
							<table class="shop_table order_details">
								<?php if($total_price - $selected_total_price > 0) 
								{ ?>
									<tr>
										<th class="product-name"><?php _e( 'Pay Amount', 'woocommerce-refund-and-exchange' ); ?></th>
										<th class="product-total"><?php echo ced_rnx_format_price($total_price - $selected_total_price); ?></th>
									</tr>
								<?php }else{ ?>
									<tr>
										<th class="product-name"><?php _e( 'Refundable Amount', 'woocommerce-refund-and-exchange' ); ?></th>
										<th class="product-total"><?php echo ced_rnx_format_price($selected_total_price - $total_price); ?></th>
									</tr>
								<?php } ?>
							</table>	
							<p>
							<?php 
							if($exchanged_detail['status'] == 'complete')
							{
								$approve_date=date_create($exchanged_detail['approve']);
								$date_format = get_option('date_format');
								$approve_date=date_format($approve_date,$date_format);
								
								_e( 'Above product exchange request is approved on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b>
							<?php
							}
							if($exchanged_detail['status'] == 'cancel')
							{
								$approve_date=date_create($exchanged_detail['cancel_date']);
								$approve_date=date_format($approve_date,"F d, Y");
									
								_e( 'Above product exchange request is cancelled on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b>
							<?php
							}
							?>
						</p><?php 	
						}					
					}	
				}
				if(in_array($order_status, $statuses))
				{
					if( WC()->version < "3.0.0" )
					{
						$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
					}
					else
					{	
						$order=new WC_Order($order);
						$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
					}
					$today_date = date_i18n( 'F j, Y' );
				    $order_date = strtotime($order_date);
				    $today_date = strtotime($today_date);
					$days = $today_date - $order_date;
					$day_diff = floor($days/(60*60*24));
					$day_allowed = get_option('ced_rnx_exchange_days', false);
					if(isset($ced_rnx_catalog_exchange_days)&& $ced_rnx_catalog_exchange_days != 0)
					{	
						if($ced_rnx_catalog_exchange_days>=$day_diff )
						{ 
						$ced_rnx_pages= get_option('ced_rnx_pages');
						$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
						$exchange_url = get_permalink($page_id);
						$order_total = $order->get_total();
						$exchange_min_amount = get_option('ced_rnx_exchange_minimum_amount', false);
						if(isset($exchange_min_amount) && !empty($exchange_min_amount))
						{
							if( WC()->version < "3.0.0" )
							{
								$order_id=$order->id;
							}
							else
							{
								$order_id=$order->get_id();
							}
							if($exchange_min_amount <= $order_total)
							{
								?>
								<form action="<?php echo add_query_arg('order_id',$order_id,$exchange_url)?>" method="post">
									<input type="hidden" value="<?php echo $order_id?>" name="order_id">
									<p><?php 
									if($order_status == 'Exchange Requested')
									{	
									?>
										<input type="submit" class="btn button" value="<?php _e('Update Exchange Request','woocommerce-refund-and-exchange')?>"></p>
									<?php 
									}else{
										if($ced_rnx_next_exchange)
										{	
									?>	
										<input type="submit" class="btn button" value="<?php _e('Exchange Product','woocommerce-refund-and-exchange')?>"></p>
									<?php 
										}
									}
									?></p>
								</form>
								<?php 
							}
						}	
						else
						{	
							
							if( WC()->version < "3.0.0" )
							{
								$order_id=$order->id;
							}
							else
							{
								$order_id=$order->get_id();
							}
							?>
							<form action="<?php echo add_query_arg('order_id',$order_id,$exchange_url)?>" method="post">
								<input type="hidden" value="<?php echo $order_id?>" name="order_id">
								<p><?php 
								if($order_status == 'Exchange Requested')
								{	
								?>
									<input type="submit" class="btn button" value="<?php _e('Update Exchange Request','woocommerce-refund-and-exchange')?>"></p>
								<?php 
								}else{
									if($ced_rnx_next_exchange)
									{	
								?>	
									<input type="submit" class="btn button" value="<?php _e('Exchange Product','woocommerce-refund-and-exchange')?>"></p>
								<?php 
									}
								}
								?></p>
							</form>
							<?php 
							}	
						}
					}
					else
					{	
						if($day_allowed >=$day_diff && $day_allowed != 0)
						{
							$ced_rnx_pages= get_option('ced_rnx_pages');
							$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
							$exchange_url = get_permalink($page_id);
							$order_total = $order->get_total();
							$exchange_min_amount = get_option('ced_rnx_exchange_minimum_amount', false);
							if(isset($exchange_min_amount) && !empty($exchange_min_amount))
							{
								if( WC()->version < "3.0.0" )
								{
									$order_id = $order->id;
								}
								else
								{
									$order_id = $order->get_id();
								}
					
								if($exchange_min_amount <= $order_total)
								{
									?>
									<form action="<?php echo add_query_arg('order_id',$order_id,$exchange_url)?>" method="post">
										<input type="hidden" value="<?php echo $order_id?>" name="order_id">
										<p><?php 
										if($order_status == 'Exchange Requested')
										{	
										?>
											<input type="submit" class="btn button" value="<?php _e('Update Exchange Request','woocommerce-refund-and-exchange')?>"></p>
										<?php 
										}else{
											if($ced_rnx_next_exchange)
											{	
										?>	
											<input type="submit" class="btn button" value="<?php _e('Exchange Product','woocommerce-refund-and-exchange')?>"></p>
										<?php 
											}
										}
										?></p>
									</form>
									<?php 
								}
							}	
							else
							{	
								if( WC()->version < "3.0.0" )
								{
									$order_id=$order->id;
								}
								else
								{
									$order_id=$order->get_id();
								}
								
								?>
								<form action="<?php echo add_query_arg('order_id',$order_id,$exchange_url)?>" method="post">
									<input type="hidden" value="<?php echo $order_id?>" name="order_id">
									<p><?php 
									if($order_status == 'Exchange Requested')
									{	
									?>
										<input type="submit" class="btn button" value="<?php _e('Update Exchange Request','woocommerce-refund-and-exchange')?>"></p>
									<?php 
									}else{
										if($ced_rnx_next_exchange)
										{	
									?>	
										<input type="submit" class="btn button" value="<?php _e('Exchange Product','woocommerce-refund-and-exchange')?>"></p>
									<?php 
										}
									}
									?></p>
								</form>
								<?php 
							}	
						}
					}
				}
				
			}	
		}
	}
	new CED_rnx_order_exchange();
}
?>