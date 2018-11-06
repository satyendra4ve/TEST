<?php  
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed = true;

//Product Return request form
$current_user_id = get_current_user_id();   //check user is logged in or not

if($allowed)
{
	$subject = "";
	$reason = "";
	if(isset($_POST['order_id']))
	{
		$order_id = $_POST['order_id'];
	}
	elseif (isset($_GET['order_id'])) {
		$order_id = $_GET['order_id'];
	}
	else 
	{
		
		$url = strtok($_SERVER['REQUEST_URI'], '?');
		$link_array = explode('/',$url);
		if(empty($link_array[count($link_array)-1]))
		{
			$order_id = $link_array[count($link_array)-2];
		}	
		else
		{
			$order_id = $link_array[count($link_array)-1];
		}	
		
	}
	
	//check order id is valid
	
	if(!is_numeric($order_id))
	{
		
		if(get_current_user_id() > 0)
		{
			$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = get_permalink( $myaccount_page );
		}
		else
		{
			$ced_rnx_pages= get_option('ced_rnx_pages');
			$page_id = $ced_rnx_pages['pages']['ced_request_from'];
			$myaccount_page_url = get_permalink( $page_id );
		}
		$allowed = false;
		$reason = __('Please choose an Order.','woocommerce-refund-and-exchange').'<a href="'.$myaccount_page_url.'">'.__('Click Here','woocommerce-refund-and-exchange').'</a>';
		$reason = apply_filters('ced_rnx_cancel_choose_order', $reason);
	}
	else 
	{
		$order_customer_id = get_post_meta($order_id, '_customer_user', true);
		
		if($current_user_id > 0)    // check order associated to customer account or not for registered user
		{
			
			if($order_customer_id != $current_user_id)
			{
				$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
				$myaccount_page_url = get_permalink( $myaccount_page );
				$allowed = false;
				$reason = __("This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>",'woocommerce-refund-and-exchange' );
				$reason = apply_filters('ced_rnx_cancel_choose_order', $reason);
			}	

		}
		else						// check order associated to customer account or not for guest user
		{
			if(isset($_SESSION['ced_rnx_email']))
			{
				$user_email = $_SESSION['ced_rnx_email'];
				$order_email = get_post_meta($order_id, '_billing_email', true);
				
				if($user_email != $order_email)
				{
					$allowed = false;
					$ced_rnx_pages= get_option('ced_rnx_pages');
					$page_id = $ced_rnx_pages['pages']['ced_request_from'];
					$myaccount_page_url = get_permalink( $page_id );
					$reason = __("This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>",'woocommerce-refund-and-exchange' );
					$reason = apply_filters('ced_rnx_cancel_choose_order', $reason);
				}

			}	
			else
			{
				$allowed = false;
			}	 
		}	
	}
}	

if($allowed)
{
	$ced_rnx_next_return = true;
	$ced_rnx_cancel_enable = get_option('ced_rnx_cancel_enable', false);
	if($ced_rnx_cancel_enable != 'yes')
	{
		$allowed = false;
	}		
	if($allowed)
	{
		$order = wc_get_order($order_id);
			//Check enable return
		$ced_rnx_cancel_order_product_enable = get_option('ced_rnx_cancel_order_product_enable', false);

		if(isset($ced_rnx_cancel_order_product_enable) && !empty($ced_rnx_cancel_order_product_enable))
		{
			if($ced_rnx_cancel_order_product_enable == 'yes')
			{
				$allowed = true;
			}
			else
			{
				$allowed = false;
				$reason = __('Cancel request is disabled.','woocommerce-refund-and-exchange' );
				$reason = apply_filters('ced_rnx_cancel_order_amount', $reason);
			}
		}
		else
		{
			$allowed = false;
			$reason =  __('Cancel request is disabled.','woocommerce-refund-and-exchange' );
			$reason = apply_filters('ced_rnx_cancel_order_amount', $reason);
		}

		$order=new WC_Order($order);
		$items = $order->get_items();
		if( WC()->version < "3.0.0" )
		{
			$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
		}
		else
		{
			$order_date = date_i18n('F j, Y', strtotime( $order->get_date_created()  ) );
		}
	}
}
get_header( 'shop' );

/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );

if($allowed)
{
	$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
	
	
	$ced_main_wrapper_class = get_option('ced_rnx_return_exchange_class');
	$ced_child_wrapper_class = get_option('ced_rnx_return_exchange_child_class');
	$ced_return_css = get_option('ced_rnx_return_custom_css');
	?>
	<style>	<?php echo $ced_return_css;?>	</style>
	<div class="woocommerce woocommerce-account <?php echo $ced_main_wrapper_class;?>">
		<div class="<?php echo $ced_child_wrapper_class;?>" id="ced_rnx_return_request_form_wrapper">
			<div id="ced_rnx_return_request_container">
				<h1>
					<?php 
					$return_product_form = __("Order's Product Cancel Request Form",'woocommerce-refund-and-exchange' );
					echo apply_filters('ced_rnx_cancel_product_form', $return_product_form);
					?>
				</h1>
				<p>
					<?php 
					$select_product_text = __('Select Product to Cancel','woocommerce-refund-and-exchange' );
					echo apply_filters('ced_rnx_select_cancel_text', $select_product_text);
					?>
				</p>
			</div>
			<ul class="woocommerce-error" id="ced-return-alert">
			</ul>
			<p class="form-row form-row form-row-wide">
				<?php do_action('ced_rnx_cancel_before_order_item_table'); ?>
			</p>
			<div class="ced_rnx_product_table_wrapper" >
				<table class="shop_table order_details ced_rnx_product_table">
					<thead>
						<tr>
							<th class="product-check"><input type="checkbox" name="ced_rnx_cancel_product_all" class="ced_rnx_cancel_product_all" value="<?php echo $order_id; ?>"><?php _e( 'Check All','woocommerce-refund-and-exchange')?></th>
							<th class="product-name"><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
							<th class="product-qty"><?php _e( 'Quantity', 'woocommerce-refund-and-exchange' ); ?></th>

						</tr>
					</thead>
					<tbody>
						<?php
						
						foreach( $order->get_items() as $item_id => $item ) 
						{

							if($item['qty'] > 0)
							{	
								if(isset($item['variation_id']) && $item['variation_id'] > 0)
								{
									$variation_id = $item['variation_id'];
									$product_id = $item['product_id'];
								}
								else
								{
									$product_id = $item['product_id'];
								}
								$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
								$thumbnail     = wp_get_attachment_image($product->get_image_id(),'thumbnail');
								
								?>
								<tr class="ced_rnx_cancel_column" data-productid="<?php echo $product_id?>" data-variationid="<?php echo $item['variation_id']?>" data-itemid="<?php echo $item_id?>">
									<td class="product-select">
										
										<input type="checkbox" class="ced_rnx_cancel_product" value="<?php echo $item_id; ?>">  	
									</td>
									<td class="product-name">
										<?php
										$is_visible        = $product && $product->is_visible();
										$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
										
										if(isset($thumbnail) && !empty($thumbnail))
										{	
											echo  '<div class="ced_rnx_prod_img">'.wp_kses_post( $thumbnail ).'</div>';
										}
										else
										{
											?>
											<div class="ced_rnx_prod_img"><img alt="Placeholder" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo home_url();?>/wp-content/plugins/woocommerce/assets/images/placeholder.png"></div>
											<?php 
										}	
										
										
										?>
										<div class="ced_rnx_product_title">
											<?php 
											echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'], $item, $is_visible );
											echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );

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
											<p>
												<b><?php _e( 'Price', 'woocommerce-refund-and-exchange' ); ?> :</b> <?php 
												echo wc_price(wc_get_price_to_display($product)); 
												?>	
											</p>
										</div>
									</td>
									<td class="product-quantity">
										<?php echo sprintf( '<input type="number" max="%s" min="1" value="%s" class="ced_rnx_cancel_product_qty form-control" name="ced_rnx_cancel_product_qty">', $item['qty'], 1 );?>
									</td>
								</tr>
								<?php  
							}
						}
						?>
					</tbody>
				</table>
				<p class="form-row form-row form-row-wide">
				<div class="ced_rnx_cancel_request_messege">
					<strong>
					<?php 
						$ced_rnx_cancel_note = __('NOTE :- According to your Product and Product\'s quantity selection we will remove product from order and if you will checked check all box then your complete  order  will be  cancelled.','woocommerce-refund-and-exchange');
						echo apply_filters('ced_rnx_cancel_note', $ced_rnx_cancel_note);

					 ?>
					</strong>
				</div>
				</p>
				<p class="form-row form-row form-row-wide">
				<?php do_action('ced_rnx_cancel_after_note'); ?>
				</p>
				<?php
					$Cancel_button_text = __('Cancel Product(s)', 'woocommerce-refund-and-exchange' );
					$Cancel_button_text =  apply_filters('ced_rnx_cancel_button_text', $Cancel_button_text);
				?>
				<p class="form-row form-row form-row-wide">
					<input type="submit" name="ced_rnx_cancel_product_submit" value="<?php echo $Cancel_button_text;?>" class="button btn ced_rnx_cancel_product_submit">
					<div class="ced_rnx_return_notification"><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/loading.gif" width="40px"></div>
				</p>
			</div>
			<br>
			<p class="form-row form-row form-row-wide">
				<?php do_action('ced_rnx_cancel_after_submit'); ?>
			</p>
			<hr/>
			<div class="ced_rnx_note_tag_wrapper">
				<div class="ced-rnx_customer_detail">
					<?php 
					wc_get_template( 'order/order-details-customer.php', array( 'order' =>  $order ) ); 
					?>
				</div>				
			</div>
		</div>	
		<?php 
	}

	else 
	{
		$return_request_not_send = __('Order\'s Product Cancel Request can\'t be send. ', 'woocommerce-refund-and-exchange' );
		echo apply_filters('ced_rnx_cancel_request_not_send', $return_request_not_send);
		echo $reason;
	}

/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
$ced_rnx_show_sidebar_on_form = get_option('ced_rnx_show_sidebar_on_form','no');	
if($ced_rnx_show_sidebar_on_form == 'yes')
{
	do_action( 'woocommerce_sidebar' );
}

get_footer( 'shop' );
?>