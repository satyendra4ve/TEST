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
		$reason = apply_filters('ced_rnx_return_choose_order', $reason);
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
					$reason = apply_filters('ced_rnx_return_choose_order', $reason);
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
						$reason = apply_filters('ced_rnx_return_choose_order', $reason);
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
			//Check enable return
			$return_enable = get_option('ced_rnx_return_enable', false);
			
			if(isset($return_enable) && !empty($return_enable))
			{
				if($return_enable == 'yes')
				{
					$allowed = true;
				}
				else
				{
					$allowed = false;
					$reason = __('Refund request is disabled.','woocommerce-refund-and-exchange' );
					$reason = apply_filters('ced_rnx_return_order_amount', $reason);
				}
			}
			else
			{
				$allowed = false;
				$reason =  __('Refund request is disabled.','woocommerce-refund-and-exchange' );
				$reason = apply_filters('ced_rnx_return_order_amount', $reason);
			}
			
			$products = get_post_meta($order_id, 'ced_rnx_return_product', true);
			
			//Get pending return request
			if(isset($products) && !empty($products))
			{
				foreach($products as $date=>$product)
				{
					if($product['status'] == 'pending')
					{
						$subject = $products[$date]['subject'];
						$reason = $products[$date]['reason'];
						$product_data = $product['products'];
					}
				}	
			}
			$order=new WC_Order($order);
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
			$day_allowed = get_option('ced_rnx_return_days', false);  //Check allowed days
			if(isset($ced_rnx_catalog_refund_days)&& $ced_rnx_catalog_refund_days != 0)
			{
				if($ced_rnx_catalog_refund_days >= $day_diff)
				{
					$allowed = true;
				}
				else
				{
					$allowed = false;
					$reason =  __('Days exceed.','woocommerce-refund-and-exchange' );
					$reason = apply_filters('ced_rnx_return_day_exceed', $reason);
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
					$reason =  __('Days exceed.','woocommerce-refund-and-exchange' );
					$reason = apply_filters('ced_rnx_return_day_exceed', $reason);
				}
			}
			if($allowed)
			{
				$order = wc_get_order( $order_id );
				$order_total = $order->get_total();
				
				//Check minimum amount
				
				$return_min_amount = get_option('ced_rnx_return_minimum_amount', false);
				if(isset($return_min_amount) && !empty($return_min_amount))
				{
					if($return_min_amount <= $order_total)
					{
						$allowed = true;
					}
					else
					{
						$allowed = false;
						$reason = __('For Refund request Order amount must be greater of equal to ','woocommerce-refund-and-exchange' ).$return_min_amount.'.';
						$reason = apply_filters('ced_rnx_return_order_amount', $reason);
					}
				}
				if($allowed)
				{
					$statuses = get_option('ced_rnx_return_order_status', array());
					$order_status ="wc-".$order->get_status();
					if(!in_array($order_status, $statuses))
					{
						$allowed = false;
						$reason =  __('Update Refund request is disabled.','woocommerce-refund-and-exchange' );
						$reason = apply_filters('ced_rnx_return_order_amount', $reason);
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
	$ced_return_css = get_option('ced_rnx_return_custom_css');
	?>
	<style>	<?php echo $ced_return_css;?>	</style>
	<div class="woocommerce woocommerce-account <?php echo $ced_main_wrapper_class;?>">
		<div class="<?php echo $ced_child_wrapper_class;?>" id="ced_rnx_return_request_form_wrapper">
			<div id="ced_rnx_return_request_container">
				<h1>
				<?php 
					$return_product_form = __('Order Refund Request Form','woocommerce-refund-and-exchange' );
					echo apply_filters('ced_rnx_return_product_form', $return_product_form);
				?>
				</h1>
				<p>
				<?php 
					$select_product_text = __('Select Product to Refund','woocommerce-refund-and-exchange' );
					echo apply_filters('ced_rnx_select_return_text', $select_product_text);
				?>
				</p>
			</div>
			<ul class="woocommerce-error" id="ced-return-alert">
			</ul>
			<div class="ced_rnx_product_table_wrapper" >
				<table class="shop_table order_details ced_rnx_product_table">
					<thead>
						<tr>
							<th class="product-check"><input type="checkbox" name="ced_rnx_return_product_all" class="ced_rnx_return_product_all"><?php _e( 'Check All','woocommerce-refund-and-exchange')?></th>
							<th class="product-name"><?php _e( 'Product', 'woocommerce-refund-and-exchange' ); ?></th>
							<th class="product-qty"><?php _e( 'Quantity', 'woocommerce-refund-and-exchange' ); ?></th>
							<th class="product-total"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$ced_rnx_sale = get_option('ced_rnx_return_sale_enable', false);
						$ced_rnx_ex_cats = get_option('ced_rnx_return_ex_cats', array());
						$ced_rnx_in_tax = get_option('ced_rnx_return_tax_enable', false);
						$in_tax = false;
						if($ced_rnx_in_tax == 'yes')
						{
							$in_tax = true;	
						}	
							
						$sale_enable = false;
						if($ced_rnx_sale == 'yes')
						{
							$sale_enable = true;
						}
						$products=new WC_Product($products);
						if( WC()->version < "3.0.0" )
						{
							$product_id=$products->id;
						}
						else
						{
							$product_id=$products->get_id();
						}
						
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

									$ced_rnx_catalog_detail=get_option('catalog',array());
									if(isset($ced_rnx_catalog_detail) && !empty($ced_rnx_catalog_detail))
									{
										$ced_rnx_catalog_refund=array();
										foreach ($ced_rnx_catalog_detail as $key => $value) 
										{
											if(is_array($ced_rnx_catalog_detail[$key]['products']) && !empty($ced_rnx_catalog_detail[$key]['products']))
											{
												if(in_array( $product_id, $ced_rnx_catalog_detail[$key]['products']))
												{
													$ced_rnx_pro=$product_id;
													$ced_rnx_catalog_refund[]=$ced_rnx_catalog_detail[$key]['refund'];
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
										if(is_array($ced_rnx_catalog_refund) && !empty($ced_rnx_catalog_refund))
										{
											$ced_rnx_catalog_refund_day=min($ced_rnx_catalog_refund);
										}
									}
									if(isset($product_id)&&isset($ced_rnx_pro) &&$product_id==$ced_rnx_pro)
									{
										if($ced_rnx_catalog_refund_day >= $day_diff && $ced_rnx_catalog_refund_day != 0)
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
								
								 $fs_coupons = $order->get_used_coupons();
								
								
								$ced_product_total = $order->get_line_subtotal( $item, $in_tax );
								$ced_product_qty = $item['qty'];
								$ced_per_product_price = 0;
								if($ced_product_qty > 0)
								{
									$ced_per_product_price = $ced_product_total / $ced_product_qty;
								}
								$purchase_note = get_post_meta( $product_id, '_purchase_note', true );
								$checked = '';
								$set_qty = 1;
								if(isset($product_data) && !empty($product_data))
								{
									foreach($product_data as $key=>$data)
									{
										if($item['product_id'] == $data['product_id'] && $item['variation_id'] == $data['variation_id'])
										{
											$checked = 'checked="checked"';
											$set_qty = $data['qty'];
											break;
										}
									}
								}
								?>
								<tr class="ced_rnx_return_column" data-productid="<?php echo $product_id?>" data-variationid="<?php echo $item['variation_id']?>" data-itemid="<?php echo $item_id?>">
									<td class="product-select">
								<?php if($show)
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
													break;
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
										<input type="checkbox" <?php echo $checked?> class="ced_rnx_return_product" value="<?php echo $mwb_actual_price;?>">
								<?php }
									  else
									  {?>
									  	<img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/return-disable.png" width="20px">
								<?php $mwb_actual_price = $order->get_item_total( $item, $in_tax );
								}?>	  	
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
											<img alt="Placeholder" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo home_url();?>/wp-content/plugins/woocommerce/assets/images/placeholder.png">
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
											 echo ced_rnx_format_price( $mwb_actual_price ); 
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
										<?php echo sprintf( '<input type="number" max="%s" min="1" value="%s" class="ced_rnx_return_product_qty form-control" name="ced_rnx_return_product_qty">', $item['qty'], $set_qty );?>
									</td>
									<td class="product-total">
										<?php echo ced_rnx_format_price( $mwb_actual_price ); 
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
								<?php if ( $show_purchase_note && $purchase_note ) : ?>
								<tr class="product-purchase-note">
									<td colspan="3"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
								</tr>
								<?php endif; 
							}
						}
							?>
							<tr>
								<th scope="row" colspan="3"><?php _e('Total Refund Amount', 'woocommerce-refund-and-exchange') ?></th>
								<td class="ced_rnx_total_amount_wrap"><span id="ced_rnx_total_refund_amount"><?php echo ced_rnx_format_price(0);?></span>
									<?php 
									if($in_tax == true)
									{	
									?>
										<small class="tax_label"><?php _e( '(incl. tax)', 'woocommerce-refund-and-exchange' ); ?></small>
									<?php 
									}	
									?>
								</td>
							</tr>
					</tbody>
				</table>
				<div class="ced_rnx_return_notification_checkbox"><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/loading.gif" width="40px"></div>
			</div>
			<hr/>
			<div class="ced_rnx_note_tag_wrapper">
			<p class="form-row form-row form-row-wide ced_rnx_return_note">
				<?php do_action('ced_rnx_retun_after_order_item_list_table'); ?>
			</p>
			<div class="ced_rnx_note_tag_wrapper">
			<p class="form-row form-row form-row-wide ced_rnx_return_note">
				<i><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/return-disable.png" width="20px"> : <?php _e('It means product can\'t be returned.', 'woocommerce-refund-and-exchange');?></i>
			</p>
			<p class="form-row form-row form-row-wide">
				<label>
					<b>
						<?php 
							$subject_return_request = __('Subject of Refund Request :', 'woocommerce-refund-and-exchange' );
							echo apply_filters('ced_rnx_return_request_subject', $subject_return_request);
						?>
					</b>
				</label>
				
				<?php 
				$predefined_return_reason = get_option('ced_rnx_return_predefined_reason', false);
				if(isset($predefined_return_reason))
				{	
				?>
				<div class="ced_rnx_subject_dropdown">
					<select name="ced_rnx_return_request_subject" id="ced_rnx_return_request_subject">
						<?php 
						foreach($predefined_return_reason as $predefine_reason)
						{
							?>
							<option value="<?php echo $predefine_reason?>"><?php echo $predefine_reason?></option>
							<?php 
						}
						?>
						<option value=""><?php _e( 'Other', 'woocommerce-refund-and-exchange' )?></option>
					</select>
				</div>
				<?php 
				}
				?>
			</p>
			<p class="form-row form-row form-row-wide">
				<input type="text" name="ced_rnx_return_request_subject" class="input-text ced_rnx_return_request_subject" id="ced_rnx_return_request_subject_text" placeholder="<?php _e('Write your reason subject','woocommerce-refund-and-exchange');?>">
			</p>
			
			<?php 
			$predefined_return_desc = get_option('ced_rnx_return_request_description', false);
			if(isset($predefined_return_desc))
			{	
				if($predefined_return_desc == 'yes')
				{
					?>
					<p class="form-row form-row form-row-wide">
						<label>
							<b>
								<?php 
									$reason_return_request = __('Reason of Refund Request', 'woocommerce-refund-and-exchange' );
									echo apply_filters('ced_rnx_return_request_reason', $reason_return_request);
								?>
							</b>
						</label>
						<br/>
						<?php $placeholder = get_option( 'ced_rnx_return_placeholder_text' , 'Reason for Return Request' ); 
						if ($placeholder == '') {
						 	$placeholder =__('Reason for the Refund Request','woocommerce-refund-and-exchange');
						 }
						 ?>
						<textarea name="ced_rnx_return_request_reason" cols="40" style="height: 222px;" class="ced_rnx_return_request_reason form-control" placeholder="<?php echo $placeholder; ?>"><?php echo $reason;?></textarea>
					</p>
					<?php 
				}
				else
				{
					?>
					<input type="hidden" name="ced_rnx_return_request_reason" class="ced_rnx_return_request_reason form-control" value="<?php _e('No Reason Enter', 'woocommerce-refund-and-exchange' )?>">
					<?php 				
				}	
			}
			else 
			{
				?>
				<input type="hidden" name="ced_rnx_return_request_reason" class="ced_rnx_return_request_reason form-control" value="<?php _e('No Reason Enter', 'woocommerce-refund-and-exchange' )?>">
				<?php 				
			}
			$ced_rnx_return_wallet_enable = get_option('ced_rnx_return_wallet_enable',false);
			$ced_rnx_select_refund_method_enable = get_option('ced_rnx_select_refund_method_enable',true);
			if(isset($ced_rnx_return_wallet_enable) && $ced_rnx_return_wallet_enable == 'yes' && is_user_logged_in())
			{
				if(isset($ced_rnx_select_refund_method_enable) && $ced_rnx_select_refund_method_enable == 'yes')
				{
					?><br><label>
						<b>
							<?php _e('Select Amount Refund Method :', 'woocommerce-refund-and-exchange' );
							?>
						</b>
					</label>
					<br>
					<div>
					<input type="radio" id="ced_rnx_refund_method" name="ced_rnx_refund_method" value="wallet_method" checked><span class="ced_rnx_refund_method_input_test"> <?php _e('Refund In Customer Wallet', 'woocommerce-refund-and-exchange' ); ?></span>
					</div>
					<div class=ced_rnx_refund_method-wrap>
  					<input type="radio" name="ced_rnx_refund_method" value="manual_method"><span class="ced_rnx_refund_method_input_test"> <?php _e('Refund Through Manual Method', 'woocommerce-refund-and-exchange' ); ?></span></div>
					<?php
				}
			}
			?>
			<p> <?php do_action('ced_rnx_return_after_refund_method'); ?></p>
			<form action="" method="post" id="ced_rnx_return_request_form" data-orderid="<?php echo $order_id;?>" enctype="multipart/form-data">
				<?php 
				$return_attachment = get_option('ced_rnx_return_attach_enable', false);
				if(isset($return_attachment) && !empty($return_attachment))
				{	
					if($return_attachment == 'yes')
					{
						?>
						<label><b><?php _e('Attach Files', 'woocommerce-refund-and-exchange');?></b></label>
						<p class="form-row form-row form-row-wide">
							<span id="ced_rnx_return_request_files">
							<input type="hidden" name="ced_rnx_return_request_order" value="<?php echo $order_id;?>">
							<input type="hidden" name="action" value="<?php _e('ced_rnx_refund_upload_files', 'woocommerce-refund-and-exchange');?>">
							<input type="file" name="ced_rnx_return_request_files[]" class="input-text ced_rnx_return_request_files"></span>
							<input type="button" value="<?php _e('Add More', 'woocommerce-refund-and-exchange');?>" class="btn button ced_rnx_return_request_morefiles">
							<i><?php _e('Only .png, .jpeg extension file is approved.', 'woocommerce-refund-and-exchange' )?></i>
						</p>
						<?php 
					}
				}?>
				<p class="form-row form-row form-row-wide">
					<input type="submit" name="ced_rnx_return_request_submit" value="<?php _e('Submit Request', 'woocommerce-refund-and-exchange');?>" class="button btn">
					<div class="ced_rnx_return_notification"><img src="<?php echo CED_REFUND_N_EXCHANGE_URL?>/assets/images/loading.gif" width="40px"></div>
				</p>
			</form>
			<br/>
			<p class="form-row form-row form-row-wide">
				<?php do_action('ced_rnx_return_after_submit_button'); ?>
			</p>
			</div>
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
	$return_request_not_send = __('Refund Request can\'t be send. ', 'woocommerce-refund-and-exchange' );
	echo apply_filters('ced_rnx_return_request_not_send', $return_request_not_send);
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