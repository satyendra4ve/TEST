<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="phoen_rewpts_order_report_table_div"><?php _e('REWARD POINTS DETAIL','phoen-rewpts'); ?></div>

<table class="wp-list-table widefat fixed striped customers" id="phoen_customer_attr_table">
				
	<thead>
		
		<tr class="phoen_rewpts_user_reward_point_tr">
			
			<th class=" column-customer_name " scope="col"><span><?php _e('EMAIL ID','phoen-rewpts'); ?></span>
				
			</th>

			<th class=" column-email" scope="col"><span><?php _e('COMPLETED ORDER ','phoen-rewpts'); ?></span>
				
			</th>

			<th class=" column-orders" scope="col"><span><?php _e('AMOUNT SPENT','phoen-rewpts'); ?></span>
				
			</th>

			<th class=" column-spent" scope="col"><span><?php _e('REWARD POINTS','phoen-rewpts'); ?></span>
				
			</th>
			
			<th class=" column-spent" scope="col"><span><?php _e('AMOUNT IN WALLET','phoen-rewpts'); ?></span>
				
			</th>

		</tr>
		
	</thead>	
	
	<tbody>	
			<?php 
			global $woocommerce;
			
			$curr=get_woocommerce_currency_symbol();
			
			$argsm    = array('posts_per_page' => -1, 'post_type' => 'shop_order','post_status'=>array_keys(wc_get_order_statuses()));
			
			$products_order = get_posts( $argsm ); 
			
			$user_detail=get_users();
						$data_count = 0;
			for($a=0;$a<count($user_detail);$a++) 	{
				
				$total_point_reward=0;
				
				$order_count=0;
				
				$amount_spent=0;
				
				$id=$user_detail[$a]->ID;
				
				$check_order_count=phoen_order_count($id); 
				
				if($check_order_count>0)
				{
				
					?>
					<tr class="phoen_attr_data_tr_cust">
					
						<td class="customer_name " ><?php echo $user_detail[$a]->user_email; ?></td>
					
						<?php 	
					
						$gen_val = get_option('phoe_rewpts_value');
						
						$reward_point=isset($gen_val['reward_point'])?$gen_val['reward_point']:'';
						
						$reedem_point=isset($gen_val['reedem_point'])?$gen_val['reedem_point']:'';
						
						$reward_money=isset($gen_val['reward_money'])?$gen_val['reward_money']:'';
						
						$reedem_money=isset($gen_val['reedem_money'])?$gen_val['reedem_money']:'';
						
						$reward_value=$reward_point/$reward_money;
						
						$reedem_value=$reedem_point/$reedem_money;
						
						for($i=0;$i<count($products_order);$i++)  	{

							
						
							$products_detail=get_post_meta($products_order[$i]->ID); 
							
							$gen_settings=get_post_meta( $products_order[$i]->ID, 'phoe_rewpts_order_status', true );
							
							if(($products_detail['_customer_user'][0]==$user_detail[$a]->ID)&&(is_array($gen_settings)))
							{
								
												
								$ptsperprice=isset($gen_settings['points_per_price'])?$gen_settings['points_per_price']:'';
								$used_reward_point=0;
								$used_reward_point=isset($gen_settings['used_reward_point'])?$gen_settings['used_reward_point']:'0';
								
								$get_reward_point=isset($gen_settings['get_reward_point'])?$gen_settings['get_reward_point']:'';
								
								$order_bill=$products_detail['_order_total'][0];
								
								$point_reward=0;
								$tpoint_reward=0;
								if($products_order[$i]->post_status=="wc-completed")
								{
									$data_count++;
									
									$point_reward= $order_bill*$ptsperprice;
									
									
								}
								
							
								 if($products_order[$i]->post_status=="wc-refunded")
								{
									$point_rewardt= ltrim($used_reward_point,'-');
									$point_reward=($order_bill*$reedem_point)+$point_rewardt;
								} 
								
								$tpoint_reward+=$used_reward_point+$point_reward;
							
								$total_point_reward+=$tpoint_reward;
								
								$amount_spent+=$order_bill;
								
								$order_count++;
								
							}
							
						} 			?>
						
						<td class="customer_name " ><?php echo $order_count; ?></td>
						
						<td class=" column-email" ><?php echo $curr.$amount_spent; ?></td>
						
						<td class=" column-orders" ><?php echo round($total_point_reward); ?></td>
						
						<td class=" column-spent" ><?php echo $curr.round($total_point_reward/$reedem_value,2); ?></td>
					
					</tr>
					
					<?php 	
				
				}
			}
			
				?>
	</tbody>
	
	<tfoot>
					
		<tr class="phoen_rewpts_user_reward_point_tr">
		
			<th class=" column-customer_name " scope="col"><span><?php _e('EMAIL ID','phoen-rewpts'); ?></span>
				
			</th>

			<th class=" column-email" scope="col"><span><?php _e('COMPLETED ORDER ','phoen-rewpts'); ?></span>
				
			</th>

			<th class=" column-orders" scope="col"><span><?php _e('AMOUNT SPENT','phoen-rewpts'); ?></span>
				
			</th>

			<th class=" column-spent" scope="col"><span><?php _e('REWARD POINTS','phoen-rewpts'); ?></span>
				
			</th>
			
			<th class=" column-spent" scope="col"><span><?php _e('AMOUNT IN WALLET','phoen-rewpts'); ?></span>
				
			</th>

		</tr>
		
	</tfoot>	
</table>
<div class="paging-container" id="phoen_attr_data_cust_pagination"> </div>
<script>
	
	jQuery(function () {
			
			load = function() {
				window.tp = new Pagination('#phoen_attr_data_cust_pagination', {
					itemsCount: <?php echo ($data_count != '')?$data_count:0;?>,
					onPageSizeChange: function (ps) {
						console.log('changed to ' + ps);
					},
					onPageChange: function (paging) {
						//custom paging logic here
						console.log(paging);
						var start = paging.pageSize * (paging.currentPage - 1),
							end = start + paging.pageSize,
							$rows = jQuery('#phoen_customer_attr_table').find('.phoen_attr_data_tr_cust');

						$rows.hide();

						for (var i = start; i < end; i++) {
							$rows.eq(i).show();
						}
					}
				});
			}

		load();
	});
	
	</script>

<?php 

function phoen_order_count($id) {
	
	global $woocommerce;
			
	$curr=get_woocommerce_currency_symbol();
	
	$argsm    = array('posts_per_page' => -1, 'post_type' => 'shop_order','post_status'=>array_keys(wc_get_order_statuses()));
	
	$products_order = get_posts( $argsm ); 
	
	$user_detail=get_user_by('id',$id);
		
	$order_count=0;
		
	$customer_orders = get_posts( array(
		'numberposts' => -1,
		'meta_key'    => '_customer_user',
		'meta_value'  => $id,
		'post_type'   => wc_get_order_types(),
		'post_status' => array_keys( wc_get_order_statuses() )
	) );
		
	for($i=0;$i<count($customer_orders);$i++)  	{	
	
		$products_detail=get_post_meta($customer_orders[$i]->ID); 
		$gen_settings=get_post_meta( $customer_orders[$i]->ID, 'phoe_rewpts_order_status', true );
		if(($customer_orders[$i]->post_status=="wc-completed")||($customer_orders[$i]->post_status=="wc-refunded")&&(is_array($gen_settings)))
		{
							
		$order_count++;
		}
					

	}

	return $order_count;
}

?>