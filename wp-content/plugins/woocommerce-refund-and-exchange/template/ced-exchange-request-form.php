<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
$allowed = true;


//Product Exchange request form

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
		$reason = apply_filters('ced_rnx_exchange_choose_order', $reason);
	}
	else
	{
		$order_customer_id = get_post_meta($order_id, '_customer_user', true);
		if($current_user_id > 0)    // check order associated to customer account or not for registered user
		{
			if(!(current_user_can('administrator')))
			{
				if($order_customer_id != $current_user_id)
				{
					$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
					$myaccount_page_url = get_permalink( $myaccount_page );
					$allowed = false;
					$reason = __("This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>",'woocommerce-refund-and-exchange' );
					$reason = apply_filters('ced_rnx_exchange_choose_order', $reason);
				}
			}
		}
		else						// check order associated to customer account or not for guest user
		{
			if(isset($_SESSION['ced_rnx_email']))
			{
				$user_email = $_SESSION['ced_rnx_email'];
				$order_email = get_post_meta($order_id, '_billing_email', true);
				if(!(current_user_can('administrator')))
				{
					if($user_email != $order_email)
					{
						$allowed = false;
						$ced_rnx_pages= get_option('ced_rnx_pages');
						$page_id = $ced_rnx_pages['pages']['ced_request_from'];
						$myaccount_page_url = get_permalink( $page_id );
						$reason = __("This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>",'woocommerce-refund-and-exchange' );
						$reason = apply_filters('ced_rnx_exchange_choose_order', $reason);
					}
				}
			}
			else 
			{
				$allowed = false;
			}	
			
		}
	}
	
	if($allowed)
	{	
		$ced_rnx_next_return = true;
		$ced_rnx_enable = get_option('ced_rnx_return_exchange_enable', false);
		if($ced_rnx_enable == 'yes')
		{
			$ced_rnx_made = get_post_meta($order_id, "ced_rnx_request_made", true);
			if(isset($ced_rnx_made) && !empty($ced_rnx_made))
			{
				$ced_rnx_next_return = false;
			}
		}
		if($ced_rnx_next_return)
		{
			$allowed = true;
		}
		else
		{
			$allowed = false;
		}
		
		if($allowed)
		{
			$order = wc_get_order($order_id);
		
			//Check enable exchange
			$exchange_enable = get_option('ced_rnx_exchange_enable', false);
			if(isset($exchange_enable) && !empty($exchange_enable))
			{
				if($exchange_enable == 'yes')
				{
					$allowed = true;
				}
				else
				{
					$allowed = false;
					$reason = __('Exchange request is disabled.','woocommerce-refund-and-exchange' );
					$reason = apply_filters('ced_rnx_exchange_order_amount', $reason);
				}
			}
			else
			{
				$allowed = false;
				$reason = __('Exchange request is disabled.','woocommerce-refund-and-exchange' );
				$reason = apply_filters('ced_rnx_exchange_order_amount', $reason);
			}
			
			
			
			if($allowed)
			{
				if(isset($_SESSION['exchange_requset']))
				{
					$exchange_details = $_SESSION['exchange_requset'];
				}	
				else
				{
					$exchange_details = get_post_meta($order_id, 'ced_rnx_exchange_product', true);
				}	
				
				$_SESSION['exchange_requset'] = $exchange_details;
				
				//Get pending exchange request
				
				if(isset($exchange_details) && !empty($exchange_details))
				{
					foreach($exchange_details as $date=>$exchange_detail)
					{
						if($exchange_detail['status'] == 'pending')
						{
							if(isset($exchange_detail['subject']))
							{
								$subject = $exchange_details[$date]['subject'];
							}
							if(isset($exchange_detail['reason']))
							{
								$reason = $exchange_details[$date]['reason'];
							}
							if(isset($exchange_detail['from']))
							{
								$exchange_products = $exchange_detail['from'];
							}
							else
							{
								$exchange_products = array();
							}
							if(isset($exchange_detail['to']))
							{
								$exchange_to_products = $exchange_detail['to'];
							}
							else
							{
								$exchange_to_products = array();
							}
						}
					}
				}
				$order = new WC_Order( $order );
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
				if( WC()->version < "3.0.0" )
				{
					$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
			    }
			    else
			    {
			    	$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
			    }
			    $today_date = date_i18n( 'F j, Y' );
	    		$order_date = strtotime($order_date);
	    		$today_date = strtotime($today_date);
				$days = $today_date - $order_date;
				$day_diff = floor($days/(60*60*24));
				$day_allowed = get_option('ced_rnx_exchange_days', true); //Check allowed days
				if(isset($ced_rnx_catalog_exchange_days)&& $ced_rnx_catalog_exchange_days != 0)
				{
					if($ced_rnx_catalog_exchange_days >= $day_diff)
					{
						$allowed = true;
					}
					else
					{
						$allowed = false;
						$reason = __('Days exceed.', 'woocommerce-refund-and-exchange' );
						$reason = apply_filters('ced_rnx_exchange_day_exceed', $reason);
					}
				}
				else
				{
					if($day_allowed >= $day_diff && $day_allowed != 0)
					{
						$allowed = true;
					}
					else
					{
						$allowed = false;
						$reason = __('Days exceed.', 'woocommerce-refund-and-exchange' );
						$reason = apply_filters('ced_rnx_exchange_day_exceed', $reason);
					}
				}
				if($allowed)
				{
					$order = wc_get_order( $order_id );
					$order_total = $order->get_total();
					$exchange_min_amount = get_option('ced_rnx_exchange_minimum_amount', false);
						
					//Check minimum amount
						
					if(isset($exchange_min_amount) && !empty($exchange_min_amount))
					{
						if($exchange_min_amount <= $order_total)
						{
							$allowed = true;
						}
						else
						{
							$allowed = false;
							$reason = __('For Exchange request Order amount must be greater of equal to ', 'woocommerce-refund-and-exchange' ).$exchange_min_amount.'.';
							$reason = apply_filters('ced_rnx_exchange_order_amount', $reason);
						}
					}
					if($allowed)
					{
						$statuses = get_option('ced_rnx_exchange_order_status', array());
						$order_status ="wc-".$order->get_status();
						if(!in_array($order_status, $statuses))
						{
							$allowed = false;
							$reason =  __('Exchange request is disabled.','woocommerce-refund-and-exchange' );
							$reason = apply_filters('ced_rnx_return_order_amount', $reason);
						}
					}
				}
			}	
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
	$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
	$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
	
	$ced_main_wrapper_class = get_option('ced_rnx_return_exchange_class');
	$ced_child_wrapper_class = get_option('ced_rnx_return_exchange_child_class');
	$ced_exchange_css = get_option('ced_rnx_exchange_custom_css');
	?>
	<style>	<?php echo $ced_exchange_css;?>	</style>
	<div class="woocommerce woocommerce-account <?php echo $ced_main_wrapper_class;?>">
		<div class="<?php echo $ced_child_wrapper_class;?>" id="ced_rnx_exchange_request_form_wrapper">
			<div id="ced_rnx_exchange_request_container">
				<h1>
				<?php 
					$exchange_product_form = __( 'Product Exchange Request Form', 'woocommerce-refund-and-exchange' );
					echo apply_filters('ced_rnx_exchange_product_form', $exchange_product_form);
				?>
				</h1>
				<p>
				<?php 
					$select_product_text = __( 'Select Product to Exchange', 'woocommerce-refund-and-exchange' );
					echo apply_filters('ced_rnx_select_exchange_text', $select_product_text);
				?>
				</p>
			</div>
			<ul class="woocommerce-error" id="ced-exchange-alert">
			</ul>
			<input type="hidden" id="ced_rnx_exchange_request_order" value="<?php echo $order_id;?>">
			<div class="ced_rnx_product_table_wrapper">
				<table class="shop_table order_details ced_rnx_product_table">
					<thead>
						<tr>
							<th class="product-check"><input type="checkbox" name="ced_rnx_exchange_product_all" class="ced_rnx_exchange_product_all"> <?php _e( 'Check All', 'woocommerce-refund-and-exchange' ); ?></th>
							<th class="product-name"><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
							<th class="product-qty"><?php _e( 'Quantity', 'woocommerce-refund-and-exchange' ); ?></th>
							<th class="product-total"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$ced_rnx_sale = get_option('ced_rnx_exchange_sale_enable', false);
							$ced_rnx_ex_cats = get_option('ced_rnx_exchange_ex_cats', array());
						
							$sale_enable = false;
							if($ced_rnx_sale == 'yes')
							{
								$sale_enable = true;
							}
							
							$ced_rnx_in_tax = get_option('ced_rnx_exchange_tax_enable', false);
							$in_tax = false;
							if($ced_rnx_in_tax == 'yes')
							{
								$in_tax = true;
							}
							foreach( $order->get_items() as $item_id => $item ) 
							{
									if($item['qty'] > 0)
									{
										if(isset($item['variation_id']) && $item['variation_id'] >0)
									{
										$variation_id = $item['variation_id'];
										$product_id = $item['product_id'];
									}
									else
									{
										$product_id = $item['product_id'];
									}
									$ced_rnx_catalog_detail=get_option('catalog',array());
									$day_allowed = get_option('ced_rnx_exchange_days', true);
									if(isset($ced_rnx_catalog_detail) && !empty($ced_rnx_catalog_detail))
									{
										$ced_rnx_catalog_exchange=array();
										foreach ($ced_rnx_catalog_detail as $key => $value) 
										{
											if(is_array($ced_rnx_catalog_detail[$key]['products']) && !empty($ced_rnx_catalog_detail[$key]['products']))
											{
												if(in_array( $product_id, $ced_rnx_catalog_detail[$key]['products']))
												{
													$ced_rnx_pro=$product_id;
													$ced_rnx_catalog_exchange[]=$ced_rnx_catalog_detail[$key]['exchange'];
													if( WC()->version < "3.0.0" )
													{
														$order_date = date_i18n( 'F j, Y', strtotime( $order->order_date  ) );
													}
													else
													{
														
														$order_date = date_i18n( 'F j, Y', strtotime( $order->get_date_created()  ) );
													}
													$today_date = date_i18n( 'F j, Y' );
										    		$order_date = strtotime($order_date);
										    		$today_date = strtotime($today_date);
													$days = $today_date - $order_date;
													$day_diff = floor($days/(60*60*24));
												}
											}
										}
										if(is_array($ced_rnx_catalog_exchange) && !empty($ced_rnx_catalog_exchange))
										{
											$ced_rnx_catalog_exchange_day=min($ced_rnx_catalog_exchange);
										}
									}
									if(isset($product_id)&&isset($ced_rnx_pro) &&$product_id==$ced_rnx_pro)
									{
										if($ced_rnx_catalog_exchange_day >= $day_diff && $ced_rnx_catalog_exchange_day != 0)
										{
											$show = true;
										}
										else
										{
											$show = false;
										}
									}
									else
									{
										if($day_allowed >= $day_diff && $day_allowed != 0)
										{
											$show = true;
										}
										else
										{
											$show = false;
										}
									}
									$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
									
									$thumbnail     = wp_get_attachment_image($product->get_image_id(),'thumbnail');
									
									
									$pro_categories = get_the_terms( $product_id, 'product_cat' );
									$productdata = wc_get_product($product_id);
									
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
									
									$ced_product_total = $order->get_line_subtotal( $item, $in_tax );
									$ced_product_qty = $item['qty'];
									if($ced_product_qty > 0)
									{
										$ced_per_product_price = $ced_product_total / $ced_product_qty;
									}
									$purchase_note = get_post_meta( $product_id, '_purchase_note', true );
			
									$checked = "";
									$qty = 1;
									if(isset($exchange_products) && !empty($exchange_products))
									{
										foreach($exchange_products as $exchange_product)
										{
											if($item['product_id'] == $exchange_product['product_id'] && $item['variation_id'] == $exchange_product['variation_id'])
											{
												$checked = 'checked="checked"';
												$qty = $exchange_product['qty'];
												break;
											}
										}
									}
									
									
									?>
									<tr class="ced_rnx_exchange_column" data-productid="<?php echo $product_id?>" data-variationid="<?php echo $item['variation_id']?>" data-itemid="<?php echo $item_id?>">
										<td class="product-select">
											<?php 
											if($show)
											{
												$mwb_ord_cpn = $order->get_used_coupons();
												$mwb_wlt_status = false;
												if( !empty( $mwb_ord_cpn ) )
												{
													foreach ($mwb_ord_cpn as $k_cpn => $v_cpn) 
													{
														$mwb_cpn_obj = new WC_Coupon( $v_cpn );
														$mwb_cpn_id = $mwb_cpn_obj->get_id();
														$mwb_wlt = get_post_meta( $mwb_cpn_id, 'rnxwallet',true );
														if( $mwb_wlt )
														{
															$mwb_wlt_status = true;
														}
													}
												}
												if( $mwb_wlt_status )
												{
													$mwb_actual_price = $ced_per_product_price;
												}
												else
												{
													$mwb_actual_price = $order->get_item_total( $item, $in_tax );
												}
												?>
												<input type="checkbox" <?php echo $checked?> class="ced_rnx_exchange_product" value="<?php echo $mwb_actual_price;?>">
											<?php 
											}
											else
											{?>
												<img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/exchange-disable.png" width="20px">

											<?php $mwb_actual_price = $order->get_item_total( $item, $in_tax );
											}
											?>
										</td>
										<td class="product-name">
										<?php
											$is_visible        = $product && $product->is_visible();
											$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
								
											echo  '<div class="ced_rnx_prod_img">'.wp_kses_post( $thumbnail ).'</div>';
											?>
											<div class="ced_rnx_product_title">
											<?php
											echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'], $item, $is_visible );
											echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );
											?><input type="hidden" class="quanty" value="<?php echo $item['qty']; ?>"> <?php
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
											<b><?php _e( 'Price', 'woocommerce-refund-and-exchange' ); ?> : </b><?php 
											 echo ced_rnx_format_price( $mwb_actual_price ); ?>
											<?php 
												if($in_tax == true)
												{	
												?>
													<small class="tax_label"><?php _e('(incl. tax)','woocommerce-refund-and-exchange'); ?></small>
												<?php 
												}	
											?>
										</p>
										</div>
										</td>
										<td class="product-quantity">
											<?php echo sprintf( '<input type="number" max="%s" min="1" value="%s" class="ced_rnx_exchange_product_qty form-control" name="ced_rnx_exchange_product_qty">', $item['qty'], $qty );?>
										</td>
										<td class="product-total">
											<?php
											echo ced_rnx_format_price( $mwb_actual_price ); ?>
											<?php 
												if($in_tax == true)
												{	
												?>
													<small class="tax_label"><?php _e('(incl. tax)','woocommerce-refund-and-exchange'); ?></small>
												<?php 
												}	
											?>
								  			<input type="hidden" id="quanty" value="<?php echo $item['qty']; ?>"> 
										</td>
									</tr>
									<?php 
								}
							}?>
						<tr>
							<th scope="row" colspan="3"><?php _e('Total Amount', 'woocommerce-refund-and-exchange') ?></th>
							<td class="ced_rnx_total_amount_wrap"><span id="ced_rnx_total_exchange_amount"><?php echo ced_rnx_format_price(0);?></span><?php 
								if($in_tax == true)
								{	
								?>
									<small class="tax_label"><?php _e('(incl. tax)','woocommerce-refund-and-exchange'); ?></small>
								<?php 
								}
							?>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="ced_rnx_return_notification_checkbox"><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/loading.gif" width="40px"></div>
			</div>
			<p class="form-row form-row form-row-wide ced_rnx_exchange_note">
				<?php do_action('ced_rnx_exchange_after_order_item_table'); ?>
			</p>
			<p class="form-row form-row form-row-wide ced_rnx_exchange_note">
				<i><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/return-disable.png" width="20px"> : <?php _e('It means product can\'t be exchanged.', 'woocommerce-refund-and-exchange');?></i>
			</p>
			<p id="ced_rnx_variation_list"></p>
			<p class="form-row form-row form-row-wide">
				<?php 
				$choose_product_button ='<input type="button" class="button btn ced_rnx_exhange_shop" name="ced_rnx_exhange_shop"  id="ced_rnx_exhange_shop" value="'. __('CHOOSE PRODUCTS', 'woocommerce-refund-and-exchange').'" class="input-text">';
				?>
				<a class="ced_rnx_exhange_shop" href="javascript:void(0);">
					
					<?php echo apply_filters('ced_rnx_exchange_choose_product_button', $choose_product_button); ?>
					<span class="ced_rnx_exchange_notification_choose_product "><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/loading.gif" width="20px"></span>
				</a>
				
			</p>
			<?php 
			
			$total_price = 0;
			if(isset($exchange_to_products) && !empty($exchange_to_products))
			{
			?>
			<table class="shop_table order_details  ced_rnx_exchanged_products ced_rnx_product_table">
				<thead>
					<tr>
						<th><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Quantity', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Price', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Remove', 'woocommerce-refund-and-exchange' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach($exchange_to_products as $key=>$exchange_to_product)
					{
						$props = array();
						$variation_attributes = array();
						$ced_woo_tax_enable_setting = get_option('woocommerce_calc_taxes');
						$ced_woo_tax_display_shop_setting = get_option('woocommerce_tax_display_shop');
						$ced_rnx_tax_test = false;
						if(isset($exchange_to_product['variation_id']))
						{
							if($exchange_to_product['variation_id'])
							{
								$variation_product = wc_get_product($exchange_to_product['variation_id']);
								$variation_attributes = $variation_product->get_variation_attributes();
								$variation_attributes = isset($exchange_to_product['variations']) ? $exchange_to_product['variations'] : $variation_product->get_variation_attributes();
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
											
										if($variationID && $variationID == $exchange_to_product['variation_id']){
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
								$ced_rnx_thumbnail     = wp_get_attachment_image_src($variation_product->get_image_id(),'thumbnail');
								$ced_rnx_thumbnail = $ced_rnx_thumbnail[0];
								
								if($ced_woo_tax_enable_setting == 'yes')
								{	
									$ced_rnx_tax_test = true;
									$ced_rnx_exchange_to_product_price =wc_get_price_including_tax($variation_product);
								}
								else
								{
									$ced_rnx_exchange_to_product_price = $exchange_to_product['price'];
								}
								$product=wc_get_product($exchange_to_product['variation_id']);

							}	
						}
						else
						{
							$product=wc_get_product($exchange_to_product['id']);
							if($ced_woo_tax_enable_setting == 'yes')
							{
								$ced_rnx_tax_test = true;

							// print_r(wc_get_product(51));die;
								$ced_rnx_exchange_to_product_price =wc_get_price_including_tax($product);
							}
							else
							{
								$ced_rnx_exchange_to_product_price = $exchange_to_product['price'];
							}

							$ced_rnx_thumbnail = wp_get_attachment_image_src($product->get_image_id(),'thumbnail');
							$ced_rnx_thumbnail = $ced_rnx_thumbnail[0];
						
						}

						
						if(isset($exchange_to_product['p_id']))
						{
							if($exchange_to_product['p_id'])
							{
								$grouped_product = new WC_Product_Grouped($exchange_to_product['p_id']);
								$grouped_product_title = $grouped_product->get_title();
								$props = wc_get_product_attachment_props( get_post_thumbnail_id($exchange_to_product['p_id']), $grouped_product );
							}
						}

						
						$pro_price = $exchange_to_product['qty']*$ced_rnx_exchange_to_product_price;
						$total_price += $pro_price;

						// if(empty($ced_rnx_thumbnail))
						// {
							
						// }
						?>
						<tr>
							<td><?php 
							if(isset($ced_rnx_thumbnail) && !is_null($ced_rnx_thumbnail))
							{
								?>
									<div class="ced_rnx_prod_img"><img width="100" height="100" title="<?php echo $props['title']?>" alt="<?php ?>" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo $ced_rnx_thumbnail; ?>"></div>
								<?php 
							}	
							else
							{
								?>
									<div class="ced_rnx_prod_img"><img width="100" height="100" title="<?php echo $props['title']?>" alt="<?php ?>" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo home_url('/wp-content/plugins/woocommerce/assets/images/placeholder.png');?>"></div>
								<?php 
							}	

							?>
								<div class="ced_rnx_product_title">
								<?php 
								if(isset($exchange_to_product['p_id']))
								{
									echo $grouped_product_title.' -> ';
								}
								echo $product->get_title(); 
								if(isset($variation_attributes) && !empty($variation_attributes)) 
								{
									echo wc_get_formatted_variation( $variation_attributes );
								}	
								?>
							</div></td>
							<td ><?php echo $exchange_to_product['qty']; ?></td>
							<td ><?php echo ced_rnx_format_price($ced_rnx_exchange_to_product_price);
								if($ced_rnx_tax_test == true)
								{	
								?>
									<small class="tax_label"><?php _e('(incl. tax)','woocommerce-refund-and-exchange'); ?></small>
								<?php 
								}	
							 ?></div></td>
							<td ><?php echo ced_rnx_format_price($pro_price);
								if($ced_rnx_tax_test == true)
								{	
								?>
									<small class="tax_label"><?php _e('(incl. tax)','woocommerce-refund-and-exchange'); ?></small>
								<?php 
								}
							?></td>
							<td data-key="<?php echo $key;?>" class="ced_rnx_exchnaged_product_remove"><a class="remove" href="javascript:void(0)">×</a></td>
						</tr>
					<?php 
					}
					?>
				</tbody>
				<tfoot class="exchange_product_table_footer">
					<th colspan="3"><?php _e('Total Amount', 'woocommerce-refund-and-exchange' );?></th>
					<th colspan="2" id="ced_rnx_exchanged_total_show"> <?php echo ced_rnx_format_price($total_price);
					if($ced_rnx_tax_test == true)
								{	
								?>
									<small class="tax_label"><?php _e('(incl. tax)','woocommerce-refund-and-exchange'); ?></small>
								<?php 
								}
					?></th>
				</tfoot>
			</table>	
			<?php 
			}
			?>
			<div class="ced_rnx_note_tag_wrapper">
			<input type="text" value="<?php echo $total_price;?>" id="ced_rnx_exchanged_total" style="display:none;">
			<p class="form-row form-row form-row-wide" id="ced_rnx_exchange_extra_amount">
				<label>
					<b>
						<?php
							$reason_exchange_amount = __('<i>Extra Amount Need to Pay</i>', 'woocommerce-refund-and-exchange');;
							echo apply_filters('ced_rnx_exchange_extra_amount', $reason_exchange_amount);
						?> : 
						<span class="ced_rnx_exchange_extra_amount">
							<?php echo ced_rnx_format_price(0);?>
						</span>
					</b>
				</label>
			</p>
			<p class="form-row form-row form-row-wide">
				<?php do_action('ced_rnx_exchange_before_exchange_subject'); ?>
			</p>
			<p class="form-row form-row form-row-wide">
				<label>
					<b>
						<?php 
							$reason_exchange_request = __('Subject of Exchange Request', 'woocommerce-refund-and-exchange');
							echo apply_filters('ced_rnx_exchange_request_subject', $reason_exchange_request);
						?>
					</b>
				</label>
				<?php 
				$predefined_exchange_reason = get_option('ced_rnx_exchange_predefined_reason', false);
				if(isset($predefined_exchange_reason))
				{	
				?>
					<div class="ced_rnx_subject_dropdown">
						<select name="ced_rnx_exchange_request_subject" id="ced_rnx_exchange_request_subject">
							<?php 
							foreach($predefined_exchange_reason as $predefine_reason)
							{
								?>
								<option value="<?php echo $predefine_reason?>"><?php echo $predefine_reason?></option>
								<?php 
							}
							?>
							<option value=""><?php _e('Other', 'woocommerce-refund-and-exchange');?></option>
						</select>
					</div>
				<?php 
				}
				?>
			</p>
			<p class="form-row form-row form-row-wide">	
				<input type="text" name="ced_rnx_exchange_request_subject" id="ced_rnx_exchange_request_subject_text" class="input-text ced_rnx_exchange_request_subject" placeholder="<?php _e('Write your reason subject','woocommerce-refund-and-exchange');?>">
			</p>
			<?php 
			$predefined_exchange_desc = get_option('ced_rnx_exchange_request_description', false);
			if(isset($predefined_exchange_desc))
			{	
				if($predefined_exchange_desc == 'yes')
				{
					?>
					<p class="form-row form-row form-row-wide">
						<label>
							<b>
							<?php 
								$reason_exchange_request = __('Reason of Exchange Request', 'woocommerce-refund-and-exchange');
								echo apply_filters('ced_rnx_exchange_request_reason', $reason_exchange_request);
							?>
							</b>
						</label>
						<?php $placeholder = get_option( 'ced_rnx_exchange_placeholder_text' , 'Reason for Exchange Request' );
						if ($placeholder == '') {
						 	$placeholder = __('Reason for the Exchange Request','woocommerce-refund-and-exchange');
						 } ?>
						<textarea name="ced_rnx_exchange_request_reason" cols="40" style="height: 222px;" class="ced_rnx_exchange_request_reason form-control" placeholder="<?php echo $placeholder; ?>"><?php echo $reason;?></textarea>
					</p>
					
					<?php 
				}
				else
				{
					?>
					<input type="hidden" name="ced_rnx_exchange_request_reason" class="ced_rnx_exchange_request_reason form-control" value="<?php _e('No Reason Enter','woocommerce-refund-and-exchange')?>">
					<?php 				
				}	
			}
			else
			{
				?>
				<input type="hidden" name="ced_rnx_exchange_request_reason" class="ced_rnx_exchange_request_reason form-control" value="<?php _e('No Reason Enter','woocommerce-refund-and-exchange')?>">
				<?php 				
			}
			?>
			
			<p class="form-row form-row form-row-wide">
				<input type="submit" class="button btn ced_rnx_exchange_request_submit" name="ced_rnx_exchange_request_submit" value="<?php _e('Submit Request','woocommerce-refund-and-exchange')?>" class="input-text">
				<div class="ced_rnx_exchange_notification"><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/loading.gif" width="40px"></div>
			</p>
			<br/>
			<p class="form-row form-row form-row-wide">
				<?php do_action('ced_rnx_exchange_after_submit_button'); ?>
			</p>
			<div class="ced-rnx_customer_detail">
				<?php wc_get_template( 'order/order-details-customer.php', array( 'order' =>  $order ) ); ?>
			</div>
		</div>
	</div>
<?php 
}
else
{
	 $exchange_request_not_send = __('Exchange Request can\'t be send. ', 'woocommerce-refund-and-exchange');
	 echo apply_filters('ced_rnx_exchange_request_not_send', $exchange_request_not_send);
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