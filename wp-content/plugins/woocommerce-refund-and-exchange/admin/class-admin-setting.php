<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin class for managing admin interfaces.
 *
 * @class    CED_rnx_admin_interface
 *
 * @version  1.0.0
 * @package  return-and-exchange/admin
 * @category Class
 * @author   makewebbetter <webmaster@makewebbetter.com>
 */
	
if( !class_exists( 'CED_rnx_admin_interface' ) ){

	class CED_rnx_admin_interface{
		
		/**
		 * This is construct of class
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		
		public function __construct(){
			$this->id = 'ced_rnx_setting';
			add_action('admin_menu', array( $this, 'ced_rnx_notification_menu') );
			
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
				add_filter( 'woocommerce_settings_tabs_array', array($this,'ced_rnx_add_settings_tab'), 50 );
				add_action( 'woocommerce_settings_tabs_' . $this->id, array($this,'ced_rnx_settings_tab') );
				add_action( 'woocommerce_sections_' . $this->id, array( $this, 'ced_rnx_output_sections' ) );
				add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
				add_filter( 'manage_users_columns', array($this,'ced_rnx_add_coupon_column' ));
				add_filter( 'manage_users_custom_column', array($this,'ced_rnx_add_coupon_column_row'), 10, 3 );
				add_action( 'edit_user_profile', array( $this , 'ced_rnx_add_customer_wallet_price_field' ) );
				add_action( 'show_user_profile', array( $this , 'ced_rnx_add_customer_wallet_price_field' ) );
				add_action( 'admin_init', array( $this , 'ced_rnx_save_catalog_settengs' ) );
			}
		}
		/**
		 * This function is used for saving catalog settings.
		 * 
		 * @name ced_rnx_save_catalog_settengs
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_save_catalog_settengs()
		{
			if(isset($_GET['section']))
			{	
				if($_GET['section']=='catalog_setting')
				{
					if(isset($_POST['save']))
					{	
						$count=get_option('catalog_count');
						$Catalog=array();
						if(isset($count) && $count!=null && $count>0)
						{
							for($i=1;$i<=$count;$i++)
							{
								if( !empty($_POST["ced_rnx_products$i"]) && trim($_POST["ced_rnx_catalog_name$i"])!=null && ($_POST["ced_rnx_catalog_refund_days$i"]!=null || $_POST["ced_rnx_catalog_exchange_days$i"]!=null))
								{
									$Catalog['Catalog'.$i]['name']=$_POST["ced_rnx_catalog_name$i"];
									$Catalog['Catalog'.$i]['products']=$_POST["ced_rnx_products$i"];
									$Catalog['Catalog'.$i]['refund']=$_POST["ced_rnx_catalog_refund_days$i"];
									$Catalog['Catalog'.$i]['exchange']=$_POST["ced_rnx_catalog_exchange_days$i"];
								}
							}
						}
						else{
							if( !empty($_POST["ced_rnx_products1"]) && trim($_POST["ced_rnx_catalog_name1"])!=null && ($_POST["ced_rnx_catalog_refund_days1"]!=null || $_POST["ced_rnx_catalog_exchange_days1"]!=null))
							{
								$Catalog['Catalog1']['name']=$_POST["ced_rnx_catalog_name1"];
								$Catalog['Catalog1']['products']=$_POST["ced_rnx_products1"];
								$Catalog['Catalog1']['refund']=$_POST["ced_rnx_catalog_refund_days1"];
								$Catalog['Catalog1']['exchange']=$_POST["ced_rnx_catalog_exchange_days1"];
							}
						}	
						update_option('catalog',$Catalog,'yes');
					}
				}
			}
		}
		
		/**
		 * Add wallet Coupon column on user list page.
		 * 
		 * @name ced_rnx_add_coupon_column
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_add_coupon_column( $column )
		{
			$column['ced_rnx_coupon_column'] = __('User Wallet' , 'woocommerce-refund-and-exchange');
			return $column;
		}

		/**
		 * Add Wallet Coupon amount change field on user edit page .
		 * 
		 * @name ced_rnx_add_customer_wallet_price_field
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_add_customer_wallet_price_field( $user ){
			
			$coupon_code = get_post_meta( $user->ID, 'ced_rnx_refund_wallet_coupon' , TRUE );
			$the_coupon = new WC_Coupon( $coupon_code );
			if( WC()->version < "3.0.0" )
			{
				$coupon_id=$the_coupon->id;
			}
			else
			{
				$coupon_id=$the_coupon->get_id();	
			}
			if(isset($coupon_id) && $coupon_id!= '')
			{
				$customer_coupon_id = $coupon_id;
				$amount = get_post_meta( $customer_coupon_id, 'coupon_amount', true );
				?>
			  	<h3><?php _e("Add Free amount to Customer Wallet", "woocommerce-refund-and-exchange"); ?></h3>
			 	<table class="form-table">
			    	<tr>
			      		<th><label for="ced_rnx_customer_wallet_price"><?php echo $coupon_code; ?></label></th>
			      		<td>
			        		<input type="text" id="ced_rnx_customer_wallet_price" class="regular-text" 
			            value="<?php echo $amount ?>" /><br />
			        		<span class="description"><?php _e("Provide Coupon Amount (Enter only number and decimal amount)" , 'woocommerce-refund-and-exchange'); ?></span>
			    		</td>
			    		<td>
			    			<input type="button" class="button button-primary ced_rnx_change_customer_wallet_amount" id="ced_rnx_change_customer_wallet_amount" data-id = "<?php echo $user->ID ?>" data-couponcode = "<?php echo $coupon_code ?>" value="<?php _e('Change Coupon Amount' , 'woocommerce-refund-and-exchange') ?>"></input>
			    			<img class="regenerate_coupon_code_image" src = '<?php echo CED_REFUND_N_EXCHANGE_URL."assets/images/loading.gif" ?>' width="20px" style="display:none;">
			    		</td>
			   		</tr>
			  </table>
				<?php
			}
		}

		/**
		 * Add user wallet data to the custom column created.
		 * 
		 * @name ced_rnx_add_coupon_column_row
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_add_coupon_column_row( $val, $column_name, $user_id ) {
			switch ($column_name)
			{
				case 'ced_rnx_coupon_column' :
					$coupon_code = get_post_meta( $user_id, 'ced_rnx_refund_wallet_coupon' , TRUE );
					$the_coupon = new WC_Coupon( $coupon_code );
					if( WC()->version < "3.0.0" )
					{
						$coupon_id=$the_coupon->id;
					}
					else
					{
						$coupon_id=$the_coupon->get_id();
					}
					if(isset($coupon_id) && $coupon_id != '')
					{
						$coupon_amount = get_post_meta( $coupon_id, 'coupon_amount', true );
						$coupon_amount = wc_price($coupon_amount);
						$val = $coupon_code.'<br><b>( '.$coupon_amount.' )</b>';
						return $val;
					}else{
						$val = '<div id="user'.$user_id.'"><input type="button" class="button button-primary ced_rnx_add_customer_wallet" data-id = "'.$user_id.'" id="ced_rnx_add_customer_wallet-'.$user_id.'" value="Create Wallet"><img id="regenerate_coupon_code_image-'.$user_id.'" src = '.CED_REFUND_N_EXCHANGE_URL.'assets/images/loading.gif width="20px" style="display:none;"></div>';
					}
					break;
				default:
			}
			return $val;
		}
		/**
		 * Add notification submenu in woocommerce 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_notification_menu()
		{
			add_submenu_page( 'woocommerce', __('RAE Configuration','woocommerce-refund-and-exchange'), __('RAE Configuration','woocommerce-refund-and-exchange'), 'manage_options', 'ced-rnx-notification', array( $this, 'ced_rnx_notification_callback' ));
		}
		
		/**
		 * Add notification submenu in woocommerce
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_notification_callback()
		{
			include_once CED_REFUND_N_EXCHANGE_DIRPATH.'admin/ced-rnx-notification.php';
		}
		
		/**
		 * Add new tab to woocommerce setting
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public static function ced_rnx_add_settings_tab( $settings_tabs ) {
			$settings_tabs['ced_rnx_setting'] = __( 'RAE Setting', 'woocommerce-refund-and-exchange' );
			return $settings_tabs;
		}
		
		/**
		 * Save section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function ced_rnx_settings_tab() 
		{
			global $current_section;

			$ced_rnx_hide_sidebar_forever = get_option('ced_rnx_hide_sidebar_forever','no'); ?>
			<div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>"> 
			<?php 
			woocommerce_admin_fields( self::ced_rnx_get_settings($current_section) );
			?>
			</div>
			<!-- <div class="mwb_table">  -->
			<?php 
			if(isset($_GET['section']))
			{
				if($_GET['section']=='catalog_setting')
				{ 
					?><div class="ced_rnx_error_notice">
					</div>
       				<?php
			
					$ced_rnx_catalog=get_option('catalog',array());
					$products= array();
					$args = array( 'post_type' => 'product','posts_per_page' => -1);
					$loop = new WP_Query( $args );
					while ( $loop->have_posts() ) : $loop->the_post(); 
					global $product; 
					$products[$product->get_id()]=$product->get_title();
					endwhile; 
					wp_reset_query();
					$counter=0;
					?><div class="ced_rnx_catalog_wrapper_section">
						<?php
						if(isset($products) && !empty($products) ) 
						{
							$ced_count=0;
							 foreach ($products as $key => $value) 
							 {
							?><input type="hidden" class="ced_product_id<?php echo $ced_count ?>" value="<?php echo $key ?>" >
							<input type="hidden" id="ced_products_count" value="<?php echo count($products); ?>"	>
							<input type="hidden" class="ced_products<?php echo $ced_count ?>" value="<?php echo $value ?>" >
							<?php $ced_count++; 
							 }
						}
					if(isset($ced_rnx_catalog) && !empty($ced_rnx_catalog) ) 
					{	
						foreach ($ced_rnx_catalog as $key => $value) 
						{ 
							$counter++;
						?>
							<div class="ced_rnx_catalog_dropdwn" data-counter = <?php echo $counter ?> >
								<div class="ced_rnx_catalog_wrapper" >
										<div class="ced_rnx_catalog_name_text"><strong><?php echo $value['name'] ?></strong></div>
										<a class="ced_rnx_catalog_delete" data-counter="<?php echo $counter; ?>" href="javascript:; "><strong><?php _e('-','woocommerce-refund-and-exchange' ); ?></strong></a>
										<a class="ced_rnx_catalog_add" data-counter="<?php echo $counter; ?>" href="javascript:;"><strong><?php _e('+','woocommerce-refund-and-exchange' ); ?></strong></a>
									</div>
									<div class="ced_rnx_catalog_toggle" >
										<table id="ced_rnx_catalog_table">
											<tr>	
												<th><label><strong><?php _e('Catalog Name:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
												<td><input type="text" name="ced_rnx_catalog_name<?php echo $counter ?>" class="ced_rnx_catalog_name" placeholder="<?php _e('Enter Catalog Name','woocommerce-refund-and-exchange' ); ?>" value="<?php echo $value['name'] ?>" ></td>
											</tr>
											<tr>	
												<th ><label><strong><?php _e('Select Catalog Products:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
												<td><select  name="ced_rnx_products<?php echo $counter ?>[]" class="ced_rnx_products" multiple id="product">
													<?php  
													$args = array( 'post_type' => 'product', 'posts_per_page' => -1);
													$loop = new WP_Query( $args );
													while ( $loop->have_posts() ) : $loop->the_post(); 
													global $product; 
													?>
													<option value="<?php echo $id=get_the_ID(); ?>" <?php if(is_array($value['products'])){ if(in_array($id,$value['products'])){ echo 'selected'; } } ?>><?php echo get_the_title(); ?></option>
													<?php
													endwhile; 
													wp_reset_query(); 
													?>
												</select></td>
											</tr><tr>	
												<th><label><strong><?php _e('Maximum Refund Days:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
												<td><input type="number" min="0" placeholder="<?php _e('Enter Refund Days','woocommerce-refund-and-exchange' ); ?>"  value="<?php echo $value['refund'] ?>" name="ced_rnx_catalog_refund_days<?php echo $counter ?>" class="ced_rnx_catalog_refund_days"><span><?php _e('[If value is 0 then catalog will not work.]', 'woocommerce-refund-and-exchange' ); ?></span></td>
											</tr><tr>
												<th><label><strong><?php _e('Maximum Exchange Days:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
												<td><input type="number" min="0" placeholder="<?php _e('Enter Exchange Days','woocommerce-refund-and-exchange' ); ?>" value="<?php echo $value['exchange'] ?>" name="ced_rnx_catalog_exchange_days<?php echo $counter ?>" class="ced_rnx_catalog_exchange_days"><span><?php _e('[If value is 0 then catalog will not work.]', 'woocommerce-refund-and-exchange' ); ?></span></td>
											</tr>
										</table>
									</div>
								</div>		
							<?php
						}
					}
					else
					{ ?>
						<div class="ced_rnx_catalog_dropdwn" data-counter = 1 >
							<div class="ced_rnx_catalog_wrapper" >
								<div class="ced_rnx_catalog_name_text"><strong><?php _e('Default Catalog', 'woocommerce-refund-and-exchange' ); ?></strong></div>
								<a class="ced_rnx_catalog_delete" data-counter="1" href="javascript:; "><strong><?php _e('-','woocommerce-refund-and-exchange' ); ?></strong></a>
								<a class="ced_rnx_catalog_add" data-counter="1" href="javascript:;"><strong><?php _e('+','woocommerce-refund-and-exchange' ); ?></strong></a>
							</div>
							<div class="ced_rnx_catalog_toggle" >
								<table id="ced_rnx_catalog_table">
									<tr>	
										<th><label><strong><?php _e('Catalog Name:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
										<td><input type="text" name="ced_rnx_catalog_name1" class="ced_rnx_catalog_name" placeholder="<?php _e('Enter Catalog Name','woocommerce-refund-and-exchange' ); ?>"  ></td>
									</tr>
									<tr>	
										<th ><label><strong><?php _e('Select Catalog Products:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
										<td><select name="ced_rnx_products1[]" id="ced_rnx_products" class="ced_rnx_products" multiple>
											<?php  
											$args = array( 'post_type' => 'product', 'posts_per_page' => -1);
											$loop = new WP_Query( $args );
											while ( $loop->have_posts() ) : $loop->the_post(); 
											global $product; ?>
											<option value="<?php echo get_the_ID(); ?>" ><?php echo get_the_title(); ?></option>
											<?php
											endwhile; 
											wp_reset_query(); 
											?>
										</select></td>
									</tr><tr>	
										<th><label><strong><?php _e('Maximum Refund Days:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
										<td><input type="number" min="0" placeholder="<?php _e('Enter Refund Days','woocommerce-refund-and-exchange' ); ?>"   name="ced_rnx_catalog_refund_days1" class="ced_rnx_catalog_refund_days"><span><?php _e('[If value is 0 then catalog will not work.]', 'woocommerce-refund-and-exchange' ); ?></span></td>
									</tr><tr>
										<th><label><strong><?php _e('Maximum Exchange Days:', 'woocommerce-refund-and-exchange' ); ?></strong></label></th>
										<td><input type="number" min="0" placeholder="<?php _e('Enter Exchange Days','woocommerce-refund-and-exchange' ); ?>"   name="ced_rnx_catalog_exchange_days1" class="ced_rnx_catalog_exchange_days"><span><?php _e('[If value is 0 then catalog will not work.]', 'woocommerce-refund-and-exchange' ); ?></span></td>
									</tr>
								</table>
							</div>
						</div>
					<?php 
					}
					?>
					</div><?php
					}
				}
				 ?>
				<?php  
			}
		
		/**
		 * Output of section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_output_sections() {
				
			global $current_section;
			$sections = $this->ced_rnx_get_sections();
			if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
				return;
			}
		
			echo '<ul class="subsubsub">';
		
			$array_keys = array_keys( $sections );
		
			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}
		
			echo '<li> | <a href="'.home_url().'/wp-admin/admin.php?page=ced-rnx-notification">'.__('Mail Configuration','woocommerce-refund-and-exchange').'</a></li>';			
			echo '</ul><br class="clear ced_rnx_clear"/>';
		}
		
		/**
		 * Create section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_get_sections() {
		
			$sections = array(
					''             	=>  __( 'Refund Products', 'woocommerce-refund-and-exchange' ),
					'exchange'     	=>  __( 'Exchange Products', 'woocommerce-refund-and-exchange' ),
					'other'     	=>  __( 'Common Setting', 'woocommerce-refund-and-exchange' ),
					'cancel'	   	=>  __( 'Cancel Order', 'woocommerce-refund-and-exchange' ),
					'wallet'	   	=>  __( 'Wallet Settings', 'woocommerce-refund-and-exchange' ),	
					'text_setting'  =>  __( 'Text Settings' , 'woocommerce-refund-and-exchange' ),
					'catalog_setting'=> __('Catalog Settings', 'woocommerce-refund-and-exchange'),
			);
		
			return apply_filters( 'ced_rnx_get_sections_' . $this->id, $sections );
		}
		
		/**
		 * Section setting
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		 function ced_rnx_get_settings($current_section) {
	    	
		 	/* get woocommerce categories */
		 		
		 	$all_cat = get_terms('product_cat',array('hide_empty'=>0));
		 	$cat_name = array();
		 	if($all_cat){
		 		foreach ($all_cat as $cat){
		 	
		 			$cat_name[$cat->term_id] = $cat->name;
		 	
		 		}
		 	}
		 	
		 	$statuses = wc_get_order_statuses();
		 	$status=$statuses;
		 	
		 	if ( 'exchange' == $current_section ) 
	    	{
	    		$settings = array(
	    							array(
										'title' => __( 'Exchange Products Setting', 'woocommerce-refund-and-exchange' ),
										'type' 	=> 'title',
									),
									array(
				    					'title'         => __( 'Enable', 'woocommerce-refund-and-exchange' ),
				    					'desc'          => __( 'Enable Exchange Request', 'woocommerce-refund-and-exchange' ),
				    					'default'       => 'no',
				    					'type'          => 'checkbox',
					    				'id' 		=> 'ced_rnx_exchange_enable'
									),
					    			array(
				    					'title'         => __( 'Enable Exchange Request With Same Product or its Variations', 'woocommerce-refund-and-exchange' ),
				    					'desc'          => __( 'Enable Exchange Request only for Exchange with same Product or its Variations.', 'woocommerce-refund-and-exchange' ),
				    					'default'       => 'no',
				    					'type'          => 'checkbox',
					    				'id' 		=> 'ced_rnx_exchange_variation_enable'
									),
				    				array(
			    						'title'         => __( 'Sale Items', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Enable Exchange Request for Sale Items', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_exchange_sale_enable'
				    				),
	    				
				    				array(
			    						'title'         => __( 'Include Tax', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Include Tax with Product Exchange Request.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_exchange_tax_enable'
				    				),
	    				 
				    				array(
			    						'title'         => __( 'Add Shipping Fee', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Add Shipping fee to Exchange amount.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_exchange_shipcost_enable'
				    				),
				    				array(
			    						'title'         => __( 'Enable Exchange Note on Product Page', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Enable to show the note on product page.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_exchange_note_enable'
				    				),
				    				 
				    				array(
			    						'title'         => __( 'Exchange Note on Product Page', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'This note is shown on product detail page.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'This Product is not exchangable',
			    						'type'          => 'textarea',
			    						'desc_tip' =>  true,
			    						'id' 		=> 'ced_rnx_exchange_note_message'
				    				),
				    				array(
				    					'title'         => __( 'Maximum Number of Days', 'woocommerce-refund-and-exchange' ),
				    					'desc'          => __( 'If days exceeds from the day of order delivered then Exchange Request will not be send. If value is 0 or blank then Exchange button will not visible at order detail page.', 'woocommerce-refund-and-exchange' ),
				    					'type'          => 'number',
				    					'custom_attributes'   => array('min'=>'0'),
				    					'id' 		=> 'ced_rnx_exchange_days'
					    			),
				    				array(
			    						'title'         => __( 'Minimum Order Amount', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Minimum Order amount must be greater or equal to this amount. Keep blank to enable exchange for all Order.', 'woocommerce-refund-and-exchange' ),
			    						'type'          => 'number',
			    						'custom_attributes'   => array('min'=>'0'),
			    						'desc_tip' =>  true,
			    						'id' 		=> 'ced_rnx_exchange_minimum_amount'
				    				),
				    				array(
			    						'title'    => __( 'Exclude Categories', 'woocommerce-refund-and-exchange' ),
			    						'desc'     => __( 'Select those categories for which products you don\'t want to exchange.', 'woocommerce-refund-and-exchange' ),
			    						'class'    => 'wc-enhanced-select',
			    						'css'      => 'min-width:300px;',
			    						'default'  => '',
			    						'type'     => 'multiselect',
			    						'options'  => $cat_name,
			    						'desc_tip' =>  true,
			    						'id' 		=> 'ced_rnx_exchange_ex_cats'
				    				),
				    				array(
			    						'title'         => __( 'Show Add To Cart button on time of Exchange', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Enable to show Add To Cart button on time exchange session is enable.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_add_to_cart_enable'
				    				),
				    				array(
			    						'title'         => __( 'Enable Exchange Reason Description', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Enable this for user to send the detail description of exchange request.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_exchange_request_description'
				    				),
				    				array(
			    						'title'         => __( 'Enable Manage Stock', 'woocommerce-refund-and-exchange' ),
			    						'desc'          => __( 'Enable this to increase product stock when exhange request is accepted.', 'woocommerce-refund-and-exchange' ),
			    						'default'       => 'no',
			    						'type'          => 'checkbox',
			    						'id' 		=> 'ced_rnx_exchange_request_manage_stock'
				    				),
				    				array(
			    						'title'    => __( 'Select the order status in which the order can be exchanged', 'woocommerce-refund-and-exchange' ),
			    						'desc'     => __( 'Select Order status on which you want exchange request user can submit.', 'woocommerce-refund-and-exchange' ),
			    						'class'    => 'wc-enhanced-select',
			    						'css'      => 'min-width:300px;',
			    						'default'  => '',
			    						'type'     => 'multiselect',
			    						'options'  => $statuses,
			    						'desc_tip' =>  true,
			    						'id' 		=> 'ced_rnx_exchange_order_status'
				    				),
	    							array(
										'type' 	=> 'sectionend',
									)
								);
	    		return apply_filters( 'ced_rnx_get_settings_return' . $this->id, $settings );
	    	}
	    	else
	    	{
	    		if ( 'other' == $current_section ) 
	    		{
	    			$settings = array(
				    					array(
				    							'title' => __( 'Common Setting', 'woocommerce-refund-and-exchange' ),
				    							'type' 	=> 'title',
				    					),
		    							array(
					    					'title'         => __( 'Enable', 'woocommerce-refund-and-exchange' ),
					    					'desc'          => sprintf(__( 'Enable Single Refund/Exchange Request per order. %s ( %s If any one Refund/Exchange request is done with an order then Refund/Exchange request is disable for that order. %s )', 'woocommerce-refund-and-exchange' ), '<br/>', '<i>', '</i>'),
					    					'default'       => 'no',
					    					'type'          => 'checkbox',
		    								'id' 		=> 'ced_rnx_return_exchange_enable'
										),

										array(
				    						'title'         => __( 'Enable Refund & Exchange for exchange approved order', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable refund & exchange feature for exchange approved order.When exchange approved order goes in selected order status then order is available for refund & exchange feature.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_exchange_approved_enable'
					    				),
					    				array(
				    						'title'         => __( 'Show Sidebar in Refund & Exchange Request Form', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable this if you want to show sidebar on refund and exchange request form..', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_show_sidebar_on_form'
					    				),
				    					array(
			    							'title'         => __( 'Main Wrapper Class of Theme', 'woocommerce-refund-and-exchange' ),
			    							'desc'          => sprintf(__( 'Write the main wrapper class of your theme if some design issue arises.','woocommerce-refund-and-exchange'  ), '<br/>', '<i>', '</i>'),
			    							'type'          => 'text',
			    							'id' 		=> 'ced_rnx_return_exchange_class'
				    					),
				    					array(
			    							'title'         => __( 'Child Wrapper Class of Theme', 'woocommerce-refund-and-exchange' ),
			    							'desc'          => sprintf(__( 'Write the child wrapper class of your theme if some design issue arises.','woocommerce-refund-and-exchange' ), '<br/>', '<i>', '</i>'),
			    							'type'          => 'text',
			    							'id' 		=> 'ced_rnx_return_exchange_child_class'
				    					),
				    					array(
			    							'title'         => __( 'Refund form Custom CSS', 'woocommerce-refund-and-exchange' ),
			    							'desc'          => sprintf(__( 'Write the custom css for Refund form.' ,'woocommerce-refund-and-exchange' ), '<br/>', '<i>', '</i>'),
			    							'type'          => 'textarea',
			    							'id' 		=> 'ced_rnx_return_custom_css'
				    					),
				    					array(
			    							'title'         => __( 'Exchange form Custom CSS', 'woocommerce-refund-and-exchange' ),
			    							'desc'          => sprintf(__( 'Write the custom css for exchange form.','woocommerce-refund-and-exchange'  ), '<br/>', '<i>', '</i>'),
			    							'type'          => 'textarea',
			    							'id' 		=> 'ced_rnx_exchange_custom_css'
				    					),
				    					array(
		    								'title' 		=> __( 'Shortcode for Wallet', 'woocommerce-refund-and-exchange' ),
		    								'desc'    		=> __( 'Copy and  Paste this Shortcode on any page for the customer wallet to be displayed.', 'woocommerce-refund-and-exchange' ),
		    								'desc_tip'		=> true,
		    								'type'			=> 'textarea',
		    								'default'       => '[ced_rnx_customer_wallet]',
		    								'id'			=> 'ced_rnx_customer_wallet_shortcode',
	    								), 
	    								array(
				    							'type' 	=> 'sectionend',
				    					)
	    						);
	    			return apply_filters( 'ced_rnx_get_settings_other' . $this->id, $settings );
	    		}
	    		else if( 'cancel' == $current_section )
	    		{
	    			$settings = array(
	    								array(
	    									'title' 		=> __( 'Cancel Order Setting' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'title',
    									),
    									array(
	    									'title' 		=> __( 'Enable' , 'woocommerce-refund-and-exchange' ),
	    									'desc'          => __( 'Enable Cancel Order', 'woocommerce-refund-and-exchange' ),
	    									'default'       => 'no',
	    									'type' 			=> 'checkbox',
	    									'id' 			=> 'ced_rnx_cancel_enable'
    									),
    									array(
	    									'title' 		=> __( "Enable Order's Product Cancel " , 'woocommerce-refund-and-exchange' ),
	    									'desc'          => __( "Enable Cancel Order's Product", 'woocommerce-refund-and-exchange' ),
	    									'default'       => 'no',
	    									'type' 			=> 'checkbox',
	    									'id' 			=> 'ced_rnx_cancel_order_product_enable'
    									),
	    								array(
				    						'title'    => __( 'Select the order status in which the order can be cancelled', 'woocommerce-refund-and-exchange' ),
					    					'desc'     => __( 'Select Order status on which you want to cancel the order by the customer.', 'woocommerce-refund-and-exchange' ),
					    					'class'    => 'wc-enhanced-select',
					    					'css'      => 'min-width:300px;',
					    					'default'  => '',
					    					'type'     => 'multiselect',
					    					'options'  => $statuses,
					    					'desc_tip' =>  true,
					    					'id' 		=> 'ced_rnx_cancel_order_status'
					    				),
					    				array(
					    						'type' 	=> 'sectionend',
					    				),
	    							);
	    			return apply_filters( 'ced_rnx_get_settings_cancel' . $this->id, $settings );
	    		}
	    		else if( 'text_setting' == $current_section )
	    		{
	    			$settings = array(
	    								array(
	    									'title' 		=> __( 'Modify Text on Frontend' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'title',
    									),
    									array(
	    									'title' 		=> __( 'Guest Refund/Exchange Form Text ' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'default'		=> 'Refund/Exchange Request Form',
	    									'id'			=> 'ced_rnx_return_exchange_page_heading_text',
	    									'desc'			=> __( 'Change heading for guest Refund exchange request page' , 'woocommerce-refund-and-exchange' ),
    									),
    									array(
	    									'title' 		=> __( 'Exchange Button Text' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_exchange_button_text',
	    									'default'		=> 'Exchange',
	    									'desc'			=> __( 'Change exchange button text on frontend' , 'woocommerce-refund-and-exchange' ),
    									),
    									array(
	    									'title' 		=> __( 'Refund Button Text' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_return_button_text',
	    									'default'		=> 'Refund',
	    									'desc'			=> __( 'Change Refund button text on frontend' , 'woocommerce-refund-and-exchange' ),
    									),
    									array(
	    									'title' 		=> __( 'Placeholder text for Refund reason field' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_return_placeholder_text',
	    									'default'		=> 'Reason for Refund',
	    									'desc'			=> __( 'Add Placeholder text for Refund reason' , 'woocommerce-refund-and-exchange' ),
    									),
    									array(
	    									'title' 		=> __( 'Placeholder text for Exchange reason field' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_exchange_placeholder_text',
	    									'default'		=> 'Reason for Exchange',
	    									'desc'			=> __( 'Add Placeholder text for Exchange reason' , 'woocommerce-refund-and-exchange' ),
    									),
    									array(
	    									'title' 		=> __( 'Exchange with same product form text' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_exchnage_with_same_product_text',
	    									'default'		=> 'Click on product(s) to exchange with selected product(s) or its variation(s). ',
	    									'desc'			=> __( "Add text to display on Exchange form to Exchanging with same product(s) and it's variation(s). ", "woocommerce-refund-and-exchange"),
    									),
    									array(
	    									'title' 		=> __( 'Order Cancel Button Text' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_order_cancel_text',
	    									'default'		=> __('Cancel Order','woocommerce-refund-and-exchange'),
	    									'desc'			=> __( 'Change Cancel Order button text on frontend' , 'woocommerce-refund-and-exchange' ),
    									),
    									array(
	    									'title' 		=> __( 'Cancel Product Button Text' , 'woocommerce-refund-and-exchange' ),
	    									'type'  		=> 'text',
	    									'id'			=> 'ced_rnx_product_cancel_text',
	    									'default'		=> __('Cancel Product','woocommerce-refund-and-exchange'),
	    									'desc'			=> __( 'Change Cancel Product button text on frontend' , 'woocommerce-refund-and-exchange' ),
    									),
					    				array(
					    						'type' 	=> 'sectionend',
					    				),
	    							);
	    			return apply_filters( 'ced_rnx_get_text_change_settings' . $this->id, $settings );
	    		}
	    		else if( 'catalog_setting' == $current_section )
	    		{
	    			$settings = array(
	    				array(
	    					'class'	=> 'ced_rnx_catalog_submit',
	    					'type' => 'sectionend',
	    					),);
	    								
	    			return apply_filters( 'ced_rnx_get_catalog_settings' . $this->id, $settings );
	    			
	    		
	    		}
	    		else if( 'wallet' == $current_section )
	    		{
	    			$settings = array(
	    					array(
								'title' 		=> __( 'Wallet settings' , 'woocommerce-refund-and-exchange' ),
								'type'  		=> 'title',
							),
	    					array(
	    						'title'         => __( 'Enable Wallet', 'woocommerce-refund-and-exchange' ),
	    						'desc'          => __( 'Enable this for add the refund amount to customer wallet', 'woocommerce-refund-and-exchange' ),
	    						'default'       => 'no',
	    						'type'          => 'checkbox',
	    						'id' 		=> 'ced_rnx_return_wallet_enable'
		    				),
		    				array(
	    						'title'         => __( 'Enable to Select Refund Method to Customer', 'woocommerce-refund-and-exchange' ),
	    						'desc'          => __( 'Enable this to select the refund method to customer.(If wallet is enable then it will work) ', 'woocommerce-refund-and-exchange' ),
	    						'default'       => 'no',
	    						'type'          => 'checkbox',
	    						'id' 		=> 'ced_rnx_select_refund_method_enable'
		    				),
		    				array(
	    						'title'         => __( 'Cancel Order Amount to Wallet', 'woocommerce-refund-and-exchange' ),
	    						'desc'          => __( 'Enable this for add the Order amount with coupon discount to customer wallet for those order which is paid and having status Processing and Completed and going to be cancelled due to some reason.', 'woocommerce-refund-and-exchange' ),
	    						'default'       => 'no',
	    						'type'          => 'checkbox',
	    						'id' 		=> 'ced_rnx_return_wallet_cancelled'
		    				),
		    				
		    				array(
	    						'title'         => __( 'Wallet Coupon Prefix', 'woocommerce-refund-and-exchange' ),
	    						'desc'          => __( ' Prefix for using wallet amount using coupon', 'woocommerce-refund-and-exchange' ),
	    						'default'       => '',
	    						'type'          => 'text',
	    						'id' 		=> 'ced_rnx_return_coupon_prefeix'
		    				),
		    				array(
		    						'type' 	=> 'sectionend',
		    				),
	    				);
	    								
	    			return apply_filters( 'ced_rnx_get_wallet_settings' . $this->id, $settings );
	    			
	    		
	    		}
	    		else 
		    	{	
		    		$settings = array(
		    				
										array(
											'title' => __( 'Refund Products Setting', 'woocommerce-refund-and-exchange' ),
											'type' 	=> 'title',
										),
										
						    			array(
					    					'title'         => __( 'Enable', 'woocommerce-refund-and-exchange' ),
					    					'desc'          => __( 'Enable Refund Request', 'woocommerce-refund-and-exchange' ),
					    					'default'       => 'no',
					    					'type'          => 'checkbox',
						    				'id' 		=> 'ced_rnx_return_enable'
										),
						    				
					    				array(
					    					'title'         => __( 'Sale Items', 'woocommerce-refund-and-exchange' ),
					    					'desc'          => __( 'Enable Refund Request for Sale Items', 'woocommerce-refund-and-exchange' ),
					    					'default'       => 'no',
					    					'type'          => 'checkbox',
					    					'id' 		=> 'ced_rnx_return_sale_enable'
					    				),
		    				
					    				array(
				    						'title'         => __( 'Include Tax', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Include Tax with Product Refund Request.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_return_tax_enable'
					    				),
						    				
					    				array(
					    					'title'         => __( 'Exclude Shipping Fee', 'woocommerce-refund-and-exchange' ),
					    					'desc'          => __( 'Exclude Shipping Cost from Refunded amount.', 'woocommerce-refund-and-exchange' ),
					    					'default'       => 'no',
					    					'type'          => 'checkbox',
					    					'id' 		=> 'ced_rnx_return_shipcost_enable'
					    				),
					    				
		    							array(
				    						'title'         => __( 'Enable Refund Note on Product Page', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable to show the note on product page.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_return_note_enable'
					    				),
		    				
					    				array(
				    						'title'         => __( 'Enable Auto Accept Product Refund Request', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable to Auto Accept Product Refund Request.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_return_autoaccept_enable'
					    				),
		    				
					    				array(
					    					
				    						'title'         => __( 'Minimum Number of Days for Auto Accept', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'If Refund Request submitted within selected number of days then Refund Request is auto approved. If value is 0 or blank then automatic accept request function is not work.', 'woocommerce-refund-and-exchange' ),
				    						'type'          => 'number',
				    						'custom_attributes'   => array('min'=>'0'),
				    						'id' 		=> 'ced_rnx_auto_return_days'
					    				),
		    				
					    				array(
				    						'title'         => __( 'Refund Note on Product Page', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'This note is shown on product detail page.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'This Product is not refundable',
				    						'type'          => 'textarea',
				    						'desc_tip' =>  true,
				    						'id' 		=> 'ced_rnx_return_note_message'
					    				),
						    				 
						    			array(
					    					'title'         => __( 'Maximum Number of Days', 'woocommerce-refund-and-exchange' ),
					    					'desc'          => __( 'If days exceeds from the day of order delivered then Refund Request will not be send. If value is 0 or blank then Refund button will not visible at order detail page.', 'woocommerce-refund-and-exchange' ),
					    					'type'          => 'number',
					    					'custom_attributes'   => array('min'=>'0'),
					    					'id' 		=> 'ced_rnx_return_days'
						    			),
		    				
					    				array(
				    						'title'         => __( 'Minimum Order Amount', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Minimum Order amount must be greater or equal to this amount. Keep blank to enable Refund for all Order.', 'woocommerce-refund-and-exchange' ),
				    						'type'          => 'number',
				    						'custom_attributes'   => array('min'=>'0'),
				    						'desc_tip' =>  true,
				    						'id' 		=> 'ced_rnx_return_minimum_amount'
					    				),
					
					    				array(
					    					'title'    => __( 'Exclude Categories', 'woocommerce-refund-and-exchange' ),
					    					'desc'     => __( 'Select those categories for which products you don\'t want to Refund.', 'woocommerce-refund-and-exchange' ),
					    					'class'    => 'wc-enhanced-select',
					    					'css'      => 'min-width:300px;',
					    					'default'  => '',
					    					'type'     => 'multiselect',
					    					'options'  => $cat_name,
					    					'desc_tip' =>  true,
					    					'id' 		=> 'ced_rnx_return_ex_cats'
					    				),
					    				array(
				    						'title'         => __( 'Enable Attachment on Request Form', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable this for user to send the attachment. User can attach <i>.png, .jpg, .jpeg</i> type files.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_return_attach_enable'
					    				),
					    				array(
				    						'title'         => __( 'Enable Refund Reason Description', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable this for user to send the detail description of Refund request.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_return_request_description'
					    				),
					    				array(
				    						'title'         => __( 'Enable Manage Stock', 'woocommerce-refund-and-exchange' ),
				    						'desc'          => __( 'Enable this to increase product stock when Refund request is accepted.', 'woocommerce-refund-and-exchange' ),
				    						'default'       => 'no',
				    						'type'          => 'checkbox',
				    						'id' 		=> 'ced_rnx_return_request_manage_stock'
					    				),
					    				array(
				    						'title'    => __( 'Select the orderstatus in which the order can be Refunded', 'woocommerce-refund-and-exchange' ),
					    					'desc'     => __( 'Select Order status on which you want Refund request user can submit.', 'woocommerce-refund-and-exchange' ),
					    					'class'    => 'wc-enhanced-select ',
					    					'css'      => 'min-width:300px;',
					    					'default'  => '',
					    					'type'     => 'multiselect',
					    					'options'  => $status,
					    					'desc_tip' =>  true,
					    					'id' 		=> 'ced_rnx_return_order_status'
					    				),
					    				array(
					    						'type' 	=> 'sectionend',
					    				),
		    				
		    						);
		    		
		    		return apply_filters( 'ced_rnx_get_settings_exchange' . $this->id, $settings );
		    	} 
	    	}
	    }
	    
	    /**
	     * Save setting
	     * @author makewebbetter<webmaster@makewebbetter.com>
	     * @link http://www.makewebbetter.com/
	     */
	    public function save() {
	    	global $current_section;
	    	$settings = $this->ced_rnx_get_settings( $current_section );
	    	WC_Admin_Settings::save_fields( $settings );
	    }
	}
	new CED_rnx_admin_interface();
}
?>