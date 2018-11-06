<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Show Exchange Product detail on Order Page on admin Side

if ( ! is_int( $thepostid ) ) {
	$thepostid = $post->ID;
}
if ( ! is_object( $theorder ) ) {
	$theorder = wc_get_order( $thepostid );
}

$order = $theorder;
if( WC()->version < "3.0.0" )
{
	$order_id=$order->id;
}
else
{
	$order_id=$order->get_id();
}
$exchange_details = get_post_meta($order_id, 'ced_rnx_exchange_product', true);
$line_items  = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
//Get Pending exchange request

if(isset($exchange_details) && !empty($exchange_details))
{
	foreach($exchange_details as $date=>$exchange_detail)
	{
		if(isset($exchange_details[$date]['subject']) && $exchange_details[$date]['reason'])
		{
				$approve_date=date_create($date);
				$date_format = get_option('date_format');
				$approve_date=date_format($approve_date,$date_format);
				
				
				$pending_date = '';
				if($exchange_detail['status'] == 'pending')
				{
					$pending_date = $date;
				}
				$subject = $exchange_details[$date]['subject'];
				$reason = $exchange_details[$date]['reason'];
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
					
				if(isset($exchange_detail['fee']))
				{
					$exchange_fees = $exchange_detail['fee'];
				}
				else
				{
					$exchange_fees = array();
				}
					
				$exchange_status = $exchange_detail['status'];
				$exchange_reason = $exchange_detail['reason'];
				$exchange_subject = $exchange_detail['subject'];
		
				_e( 'Following product exchange request is made on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b>
				
				<div>
					<div id="ced_rnx_exchange_wrapper">
					<p><b><?php _e('Exchanged Product', 'woocommerce-refund-and-exchange' ); ?></b></p>
					<table>
						<thead>
							<tr>
								<th><?php _e( 'Item', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Name', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Cost', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Qty', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php 
						if(isset($exchange_products) && !empty($exchange_products))
						{
							$selected_total_price = 0;
							foreach ( $line_items as $item_id => $item )
							{
								foreach($exchange_products as $key=>$exchanged_product)
								{
									if($item_id == $exchanged_product['item_id'])
									{
										$_product  = $order->get_product_from_item( $item );
										$item_meta = wc_get_order_item_meta( $item_id,$key );
										$thumbnail     = $_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $_product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
										?>
										<tr>
											<td class="thumb">
											<?php
												echo '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
											?>
											</td>
											<td class="name">
											<?php
												echo esc_html( $item['name'] );
												if ( $_product && $_product->get_sku() ) {
													echo '<div class="wc-order-item-sku"><strong>' . __( 'SKU:', 'woocommerce-refund-and-exchange' ) . '</strong> ' . esc_html( $_product->get_sku() ) . '</div>';
												}
												if ( ! empty( $item['variation_id'] ) ) {
													echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'woocommerce-refund-and-exchange' ) . '</strong> ';
													if ( ! empty( $item['variation_id'] ) && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
														echo esc_html( $item['variation_id'] );
													} elseif ( ! empty( $item['variation_id'] ) ) {
														echo esc_html( $item['variation_id'] ) . ' (' . __( 'No longer exists', 'woocommerce-refund-and-exchange' ) . ')';
													}
													echo '</div>';
												}
												if( WC()->version < "3.1.0" )
												{
													$item_meta      = new WC_Order_Item_Meta( $item, $_product );
													$item_meta->display();
												}
												else
												{
													$item_meta      = new WC_Order_Item_Product( $item, $_product );
													wc_display_item_meta($item_meta);
												}
												?>
											</td>
											<td><?php echo ced_rnx_format_price($exchanged_product['price']);?></td>
											<td><?php echo $exchanged_product['qty'];?></td>
											<td><?php echo ced_rnx_format_price($exchanged_product['price']*$exchanged_product['qty']);?></td>
										</tr>
										<?php 
										$selected_total_price += $exchanged_product['price']*$exchanged_product['qty'];
									}
								}
							}		
						}
							?>
							<tr>
								<th colspan="4"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php echo ced_rnx_format_price($selected_total_price); ?></th>
							</tr>
						</tbody>
					</table>	
				</div>
				<div id="ced_rnx_exchange_wrapper">
					<p><b><?php _e('Requested Product', 'woocommerce-refund-and-exchange' ); ?></b></p>
					<table>
						<thead>
							<tr>
								<th><?php _e( 'Item', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Name', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Cost', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Qty', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php 
						$ced_woo_tax_enable_setting = get_option('woocommerce_calc_taxes');
						$ced_woo_tax_display_shop_setting = get_option('woocommerce_tax_display_shop');
						$ced_rnx_tax_test = false;


						if(isset($exchange_to_products) && !empty($exchange_to_products))
						{
							$total_price = 0;
							foreach($exchange_to_products as $key=>$exchange_to_product)
							{
								$variation_attributes = array();
								
								//Variable Product
								if(isset($exchange_to_product['variation_id']))
								{
									if($exchange_to_product['variation_id'])
									{
										$variation_product = wc_get_product($exchange_to_product['variation_id']);
										$variation_attributes = $variation_product->get_variation_attributes();
										$variation_labels = array();
										foreach ($variation_attributes as $label => $value){
											if(is_null($value) || $value == ''){
												$variation_labels[] = $label;
											}
										}
										
										if(isset($exchange_to_product['variations']) && !empty($exchange_to_product['variations']))
										{
											$variation_attributes = $exchange_to_product['variations'];
										}
										if($ced_woo_tax_enable_setting == 'yes')
										{	
											$ced_rnx_tax_test = true;
											if(isset($exchange_to_product['price'])){
												$exchange_to_product_price = $exchange_to_product['price'];
											}else{
												$exchange_to_product_price = wc_get_price_including_tax($variation_product);
											}
										}
										else
										{
											$exchange_to_product_price = $exchange_to_product['price'];
										}
									} $product = wc_get_product($exchange_to_product['variation_id']);	
								}
								else
								{
									$product = wc_get_product($exchange_to_product['id']);
								
									if($ced_woo_tax_enable_setting == 'yes')
									{	
										$ced_rnx_tax_test = true;
										if(isset($exchange_to_product['price'])){
											$exchange_to_product_price = $exchange_to_product['price'];
										}else{
											$exchange_to_product_price = wc_get_price_including_tax($product);
										}
									}
									else
									{
										$exchange_to_product_price = $exchange_to_product['price'];
									}
								
								}
								//Grouped Product
								if(isset($exchange_to_product['p_id']))
								{
									if($exchange_to_product['p_id'])
									{
										$grouped_product = new WC_Product_Grouped($exchange_to_product['p_id']);
										$grouped_product_title = $grouped_product->get_title();
									}
								}
								
								$pro_price = $exchange_to_product['qty']*$exchange_to_product_price;
								$total_price += $pro_price;
								?>
								<tr>
									<td>
										<?php 
											if(isset($exchange_to_product['p_id']))
											{
												echo $grouped_product->get_image();
											}
											elseif(isset($variation_attributes) && !empty($variation_attributes))
											{
												echo $variation_product->get_image();
											}	
											else 
											{
												echo $product->get_image();
											}	
										?>
									</td>
									<td>
										<?php 
											if(isset($exchange_to_product['p_id']))
											{
												echo $grouped_product_title.' -> ';
											}
											echo $product->get_title(); 
											if ( $_product && $_product->get_sku() ) {
													echo '<div class="wc-order-item-sku"><strong>' . __( 'SKU:', 'woocommerce-refund-and-exchange' ) . '</strong> ' . esc_html( $product->get_sku() ) . '</div>';
												}
											if(isset($variation_attributes) && !empty($variation_attributes))
											{
												// echo wc_get_formatted_variation( $variation_product->get_variation_attributes() );
												echo wc_get_formatted_variation( $variation_attributes );
											}
										?>
									</td>
									<td><?php echo ced_rnx_format_price($exchange_to_product_price); ?></td>
									<td><?php echo $exchange_to_product['qty']; ?></td>
									<td><?php echo ced_rnx_format_price($pro_price);?></td>
								</tr>
								<?php 
								}
							}
							?>
							<tr>
								<th colspan="4"><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
								<th><?php echo ced_rnx_format_price($total_price); ?></th>
							</tr>
						</tbody>
					</table>	
				</div>
				<div class="ced_rnx_extra_reason ced_rnx_extra_reason_for_exchange">
				<?php 
					$fee_enable = get_option('ced_rnx_exchange_shipcost_enable', false);
					if($fee_enable == 'yes')
					{
						?>
						<p><?php _e('Fees amount is added to Paid amount', 'woocommerce-refund-and-exchange');?></p>
						<?php 
						$readonly = "";
						if($exchange_status == 'complete')
						{
							$readonly = 'readonly="readonly"';
						}	
						else 
						{
							?>
							<div id="ced_rnx_exchange_add_fee">
							<?php 	
						}	
						
						if(isset($exchange_fees) && !empty($exchange_fees))
						{
							if(is_array($exchange_fees))
							{
								foreach($exchange_fees as $fee)
								{
									$total_price += $fee['val']; 
									if($exchange_status == 'pending')
									{
									?>
									<div class="ced_rnx_exchange_add_fee">
									<?php 
									}
									?>
										<input type="text" placeholder="<?php _e('Fee Name','woocommerce-refund-and-exchange');?>" value="<?php echo $fee['text'];?>" name="ced_exchange_fee_txt[]" class="ced_exchange_fee_txt" <?php echo $readonly;?>>
										<input type="text" name="" placeholder="0" value="<?php echo $fee['val'];?>" class="ced_exchange_fee_value wc_input_price" <?php echo $readonly;?>>
									<?php 
									if($exchange_status == 'pending')
									{
									?>
										<input type="button" value="<?php _e('Remove','woocommerce-refund-and-exchange');?>" class="button ced_rnx_remove-exchange-product-fee">
									</div>
									<?php 
									}	
								}	
							}
						}	
						if($exchange_status == 'pending')
						{
							?>
							</div>
							<button class="button ced_rnx_add-exchange-product-fee" type="button"><?php _e('Add fee', 'woocommerce-refund-and-exchange');?></button>
							<button class="button button-primary ced_rnx_save-exchange-product-fee" type="button" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $date;?>"><?php _e('Save', 'woocommerce-refund-and-exchange');?></button>
							<?php 
						}
					}
					$mwb_cpn_used = get_post_meta( $order_id ,'mwb_rnx_status_exchanged', true );
					if( $mwb_cpn_used )
					{
						$mwb_dis_tot = $mwb_cpn_used;
					}
					else
					{
						$mwb_cpn_dis = $order->get_discount_total();
						$mwb_cpn_tax = $order->get_discount_tax();
						$mwb_dis_tot = $mwb_cpn_dis + $mwb_cpn_tax;
					}
					$mwb_dis_tot = 0;
					if( $total_price - ( $selected_total_price + $mwb_dis_tot ) > 0)
					{?>
						<p><strong><?php _e('Extra Amount Paid', 'woocommerce-refund-and-exchange');?> : <?php echo ced_rnx_format_price( $total_price-( $selected_total_price + $mwb_dis_tot ) );?></strong></p>
					<?php
					}
					else
					{
						if( $mwb_dis_tot > $total_price )
						{
							$total_price = 0;
						}
						else
						{
							$total_price = $total_price - $mwb_dis_tot;
						}
						?>
						<p><strong><i><?php _e('Left Amount After Exchange', 'woocommerce-refund-and-exchange');?></i> : <?php echo ced_rnx_format_price( $selected_total_price-$total_price );?></strong>
						<input type="hidden" name="ced_rnx_left_amount_for_refund" class="ced_rnx_left_amount_for_refund" value="<?php echo($selected_total_price-$total_price) ; ?>">
						</p>
						<?php
					}
					?>
					<div class="ced_rnx_reason">	
						<p><strong><?php _e('Subject', 'woocommerce-refund-and-exchange');?> :</strong><i> <?php echo $exchange_subject;?></i></p>
						<p><b><?php _e('Reason', 'woocommerce-refund-and-exchange');?> :</b></p>
						<p><?php echo $exchange_reason;?></p>
					<?php 
					if($exchange_status == 'pending')
					{
					?>	
						<p>
							<input type="button" value="Accept Request" class="button" id="ced_rnx_accept_exchange" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $date;?>">
							<input type="button" value="Cancel Request" class="button" id="ced_rnx_cancel_exchange" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $date;?>">
						</p>
					<?php 
					}
					?>	
					</div>
					<div class="ced_rnx_exchange_loader">
						<img src="<?php echo home_url();?>/wp-admin/images/spinner-2x.gif">
					</div>
				</div>	
			</div>
			<p>
			<?php 
			if($exchange_detail['status'] == 'complete')
			{
				$left_amount = get_post_meta($order_id,"ced_rnx_left_amount",true);
				if(isset($left_amount) && $left_amount != null && $left_amount > 0)
				{

					?><p><strong><?php _e( 'Refunddable Amount of this order is ', 'woocommerce-refund-and-exchange' );
						echo $left_amount.". ";
					?></strong><input type="button" name="ced_rnx_left_amount" class="button button-primary" id="ced_rnx_left_amount" data-orderid="<?php echo $order_id; ?>" Value="<?php _e('Refund Amount','woocommerce-refund-and-exchange'); ?>" ></p>
					<input type="hidden" name="left_amount" id="left_amount" value="<?php echo $left_amount; ?>"><?php	

				}
				$approve_date=date_create($exchange_detail['approve']);
				$date_format = get_option('date_format');
				$approve_date=date_format($approve_date,$date_format);
				
				_e( 'Above product exchange request is approved on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b>
			<?php
				$exhanged_order_id = get_post_meta($order_id, "date-$date", true);
				?></p><p><?php _e( 'A new order is generated for your exchange request.', 'woocommerce-refund-and-exchange' );?>
				<a href="<?php echo home_url("wp-admin/post.php?post=$exhanged_order_id&action=edit")?>">Order #<?php echo $exhanged_order_id;?></a>
				<?php 
				$ced_rnx_manage_stock_for_exchange = get_post_meta($order_id,'ced_rnx_manage_stock_for_exchange',true);
				if($ced_rnx_manage_stock_for_exchange == '')
				{
					$ced_rnx_manage_stock_for_exchange = 'yes';
				}
				$manage_stock = get_option('ced_rnx_exchange_request_manage_stock');
				if($manage_stock == "yes" && $ced_rnx_manage_stock_for_exchange == 'yes')
				{
					?> <div><?php _e( 'When Product Back in stock then for stock management click on ', 'woocommerce-refund-and-exchange' ); ?> <input type="button" name="ced_rnx_stock_back" class="button button-primary" id="ced_rnx_stock_back" data-type="ced_rnx_exchange" data-orderid="<?php echo $order_id; ?>" Value="Manage Stock" ></div> <?php
				}
			}
			if($exchange_detail['status'] == 'cancel')
			{
				$approve_date=date_create($exchange_detail['cancel_date']);
				$approve_date=date_format($approve_date,"F d, Y");
				?></p><p><?php
				_e( 'Above product exchange request is cancelled on ', 'woocommerce-refund-and-exchange' ); ?><b><?php echo $approve_date?>.</b>
			<?php
			}
			?>
			</p>
			<hr/>
			<?php
		}
	}
}
else 
{	
	$ced_rnx_pages= get_option('ced_rnx_pages');
	$page_id = $ced_rnx_pages['pages']['ced_exchange_from'];
	$exchange_url = get_permalink($page_id);
	$order_id = $order->get_id();
	$ced_rnx_exchange_url = add_query_arg('order_id',$order_id,$exchange_url);
?>
<p><?php _e('No request from customer', 'woocommerce-refund-and-exchange');?></p>
<a target="_blank" href="<?php echo $ced_rnx_exchange_url; ?>" class="button-primary button"><b><?php _e('Initiate Exchange Request','woocommerce-refund-and-exchange'); ?></b></a>
<?php 
}
?>