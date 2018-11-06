<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Show Return Product detail on Order Page on admin Side

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
$return_datas = get_post_meta($order_id, 'ced_rnx_return_product', true);
$line_items  = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );

if(isset($return_datas) && !empty($return_datas))
{
	foreach($return_datas as $key=>$return_data)
	{
		$date=date_create($key);
		$date_format = get_option('date_format');
		$date=date_format($date,$date_format);
		?>
		<p><?php _e( 'Following product refund request made on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $date?>.</b></p>
		<div>
		<div id="ced_rnx_return_wrapper">
			<table>
				<thead>
					<tr>
						<th><?php _e( 'Item', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Name', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Cost', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Qty', 'woocommerce-refund-and-exchange' ); ?></th>
						<th><?php _e( 'Total', 'woocommerce-refund-and-exchange' ); ?></th>
						<?php if(ced_rnx_wc_vendor_addon_enable()) : ?>
						<th><?php _e( 'Approved By Vendor', 'woocommerce-refund-and-exchange' ); ?></th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody>
				<?php 
				$total = 0;
				$return_products = $return_data['products'];
				foreach ( $line_items as $item_id => $item ) 
				{
					foreach($return_products as $return_product)
					{
						if($item_id == $return_product['item_id'])
						{
							$_product  = $order->get_product_from_item( $item );
							$item_meta =wc_get_order_item_meta( $item_id,$key );
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
								<td><?php echo ced_rnx_format_price($return_product['price']);?></td>
								<td><?php echo $return_product['qty'];?></td>
								<td><?php echo ced_rnx_format_price($return_product['price']*$return_product['qty']);?></td>
								<?php if(ced_rnx_wc_vendor_addon_enable()) : ?>
								<td><?php
									$mwb_rnx_wcv_approved = isset($return_product['approved'])? $return_product['approved'] : 0 ;
									if($mwb_rnx_wcv_approved)
									{
										_e('Yes','woocommerce-refund-and-exchange');
									}
									else
									{
										_e('No','woocommerce-refund-and-exchange');
									}
								 ?></td>
								<?php endif; ?>
							</tr>
							<?php 
							$total += $return_product['price']*$return_product['qty'];
						}
					}		
				}
				?>
					<tr>
						<th colspan="4"><?php _e('Total Amount', 'woocommerce-refund-and-exchange');?></th>
						<th><?php echo ced_rnx_format_price($total);?></th>
					</tr>
				</tbody>
			</table>	
		</div>
		<div class="ced_rnx_extra_reason ced_rnx_extra_reason_for_refund">
		<?php 
		
		$fee_enable = get_option('ced_rnx_return_shipcost_enable', false);
		if($fee_enable == 'yes')
		{
			?>
			<p><?php _e('Fees amount is deducted from Refund amount', 'woocommerce-refund-and-exchange');?></p>
			<?php 
			$disable = "";
			if($return_data['status'] != 'pending')
			{
				$disable = 'readonly';
			}
			else 
			{
			?>
			<div id="ced_rnx_add_fee">
			<?php 
			}
			$added_fees = get_post_meta($order_id, 'ced_rnx_return_added_fee', true);
			
			if(isset($added_fees) && !empty($added_fees))
			{
				if(is_array($added_fees))
				{
					foreach($added_fees as $da=>$added_fee)
					{
						if($da == $key)
						{
							if(is_array($added_fee))
							{
								foreach($added_fee as $fee)
								{
									$return_data['amount'] -= $fee['val']; 
									if($return_data['status'] == 'pending')
									{
									?>
									<div class="ced_rnx_add_fee">
									
									<?php 
									}
									?>
										
										<input type="text" placeholder="<?php _e('Fee Name','woocommerce-refund-and-exchange')?>" <?php echo $disable?> value="<?php echo $fee['text'];?>" name="ced_return_fee_txt[]" class="ced_return_fee_txt">
										<input type="text" name="" placeholder="0" <?php echo $disable?> value="<?php echo $fee['val'];?>" class="ced_return_fee_value wc_input_price">
										<?php 
										if($return_data['status'] == 'pending')
										{	?>
										<input type="button" value="<?php _e('Remove','woocommerce-refund-and-exchange')?>" class="button ced_rnx_remove-return-product-fee">
										<?php 
										}
									if($return_data['status'] == 'pending')
									{
									?>
									</div>
									<?php 	
									}
								}
							}
							break;
						}
					}	
				}
			}	
			if($return_data['status'] == 'pending')
			{	
				?>
				</div>
				<button class="button ced_rnx_add-return-product-fee" type="button"><?php _e('Add Fee', 'woocommerce-refund-and-exchange');?></button>
				<button class="button button-primary ced_rnx_save-return-product-fee" type="button" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>"><?php _e('Save', 'woocommerce-refund-and-exchange');?></button>
				<?php 
			}

		}
		
		if($return_data['status'] == 'pending')
		{
			?>
			<input type="hidden" value="<?php echo $return_data['amount']?>" id="ced_rnx_refund_amount">
			<input type="hidden" value="<?php echo $return_data['subject']?>" id="ced_rnx_refund_reason">
			<?php
		}
		?>
		<p><strong>
		
		<?php _e('Refund Amount', 'woocommerce-refund-and-exchange');?> :</strong> <?php echo ced_rnx_format_price($return_data['amount'])?> <input type="hidden" name="ced_rnx_total_amount_for_refund" class="ced_rnx_total_amount_for_refund" value="<?php echo $return_data['amount'] ; ?>"></p>
		<div class="ced_rnx_reason">	
			<p><strong><?php _e('Subject', 'woocommerce-refund-and-exchange');?> :</strong><i> <?php echo $return_data['subject']?></i></p></p>
			<p><b><?php _e('Reason', 'woocommerce-refund-and-exchange');?> :</b></p>
			<p><?php echo $return_data['reason']?></p>
			<?php 
			$req_attachments = get_post_meta($order_id, 'ced_rnx_return_attachment', true);
			
			if(isset($req_attachments) && !empty($req_attachments))
			{	
				?>
				<p><b><?php _e('Attachment', 'woocommerce-refund-and-exchange');?> :</b></p>
				<?php
				if(is_array($req_attachments))
				{
					foreach($req_attachments as $da=>$attachments)
					{
						if($da == $key)
						{
							$count = 1;
							foreach($attachments['files'] as $attachment)
							{
								if($attachment != $order_id.'-')
								{
									?>
									<a href="<?php echo home_url()?>/wp-content/attachment/<?php echo $attachment?>" target="_blank"><?php _e('Attachment','woocommerce-refund-and-exchange');?>-<?php echo $count;?></a>
									<?php 
									$count++;
								}
							}	
							break;
						}
					}		
				}	
			}
			if($return_data['status'] == 'pending')
			{	
				?>
				<p id="ced_rnx_return_package">
				<input type="button" value="<?php _e('Accept Request','woocommerce-refund-and-exchange');?>" class="button" id="ced_rnx_accept_return" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>">
				<input type="button" value="<?php _e('Cancel Request','woocommerce-refund-and-exchange');?>" class="button" id="ced_rnx_cancel_return" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>">
				</p>
				<?php 
			}
			?>
		</div>
		<div class="ced_rnx_return_loader">
			<img src="<?php echo home_url();?>/wp-admin/images/spinner-2x.gif">
		</div>
		</div>	
		</div>
		<p>
		<?php 
		if($return_data['status'] == 'complete')
		{
			?>
			<input type="hidden" value="<?php echo ced_rnx_currency_seprator($return_data['amount']) ?>" id="ced_rnx_refund_amount">
			<input type="hidden" value="<?php echo $return_data['subject']?>" id="ced_rnx_refund_reason">
			<?php
			$refundable_amount = 0;
			$refundable_amount = get_post_meta($order_id,'refundable_amount',true);
			$approve_date=date_create($return_data['approve_date']);
			$date_format = get_option('date_format');
			$approve_date=date_format($approve_date,$date_format);

			if($refundable_amount > 0)
			{
				_e( 'Following product refund request is approved on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b><input type="button" name="ced_rnx_left_amount" class="button button-primary" data-orderid="<?php echo $order_id; ?>" id="ced_rnx_left_amount" Value="Refund Amount" > <?php
			}
			else{
				_e( 'Following product refund request is approved on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b>
			<?php
			}
			$ced_rnx_manage_stock_for_return = get_post_meta($order_id,'ced_rnx_manage_stock_for_return',true);
			if($ced_rnx_manage_stock_for_return == '')
			{
				$ced_rnx_manage_stock_for_return = 'yes';
			}
			$manage_stock = get_option('ced_rnx_return_request_manage_stock');
			if($manage_stock == "yes" && $ced_rnx_manage_stock_for_return == 'yes')
			{
				?> <div id="ced_rnx_stock_button_wrapper"><?php _e( 'When Product Bacck in stock then for stock management click on ', 'woocommerce-refund-and-exchange' ); ?> <input type="button" name="ced_rnx_stock_back" class="button button-primary" id="ced_rnx_stock_back" data-type="ced_rnx_return" data-orderid="<?php echo $order_id; ?>" Value="Manage Stock" ></div> <?php
			}
		}
		if($return_data['status'] == 'cancel')
		{
			$approve_date=date_create($return_data['cancel_date']);
			$approve_date=date_format($approve_date,"F d, Y");
				
			_e( 'Following product refund request is cancelled on', 'woocommerce-refund-and-exchange' ); ?> <b><?php echo $approve_date?>.</b>
		<?php
		}
		?>
		</p>
		<hr/>
		<?php 
	}
} 
else 
{	
	$ced_rnx_pages= get_option('ced_rnx_pages');
	$page_id = $ced_rnx_pages['pages']['ced_return_from'];
	$return_url = get_permalink($page_id);
	$order_id = $order->get_id();
	$ced_rnx_return_url = add_query_arg('order_id',$order_id,$return_url);
?>
<p><?php _e('No request from customer', 'woocommerce-refund-and-exchange');?></p>
<a target="_blank" href="<?php echo $ced_rnx_return_url; ?>" class="button-primary button"><b><?php _e('Initiate Refund Request','woocommerce-refund-and-exchange'); ?></b></a>
<?php 
}
?>