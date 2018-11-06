<?php 
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Notification setting page for Refund and Exchange Product on admin side

$tab = "basic";
if(isset($_GET['tab']))
{
	$tab = $_GET['tab'];
}
$refund_active = "";
$exchange_active = "";
$basic_active = "";
$return_ship_label_setting_active = "";
$ced_rnx_addon_section = "";
$ced_rnx_help_section = "";
$ced_rnx_license_section = "";

if($tab == "refund")
{
	$refund_active = "nav-tab-active";
}	
elseif($tab == "exchange")
{
	$exchange_active = "nav-tab-active";
}
elseif($tab == "return_ship_label_setting") {
	$return_ship_label_setting_active = "nav-tab-active";	
}
elseif ($tab == "ced_rnx_help_section") {
	$ced_rnx_help_section = "nav-tab-active";
}
elseif ($tab == "ced_rnx_addon_section") {
	$ced_rnx_addon_section = "nav-tab-active";
}
elseif ($tab == "ced_rnx_license_section") {
	$ced_rnx_license_section = 	"nav-tab-active";	
}	
else if( $tab != "rnx_dokan_tab")
{
	$basic_active = "nav-tab-active";
}	

if(isset($_POST['ced_rnx_noti_save_basic']))
{
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php _e('Settings saved.','woocommerce-refund-and-exchange'); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e('Dismiss this notices.','woocommerce-refund-and-exchange'); ?></span>
		</button>
	</div><?php
	unset($_POST['ced_rnx_noti_save_basic']);
	$post = $_POST;
	foreach($post as $k=>$val)
	{
		if(is_array($val))
		{
			foreach($val as $a=>$b)
			{
				
				if(empty($b) & $b != 0)
				{
					unset($val[$a]);
				}	
			}	
		}	
		update_option($k, $val);
	}
	if (!isset($post['ced_rnx_enable_time_policy'])) {
		update_option( 'ced_rnx_enable_time_policy' , 'no' );
	}
	if (!isset($post['ced_rnx_enable_price_policy'])) {
		update_option( 'ced_rnx_enable_price_policy' , 'no' );
	}
	if (!isset($post['ced_rnx_show_refund_policy_on_product_page'])) {
		update_option( 'ced_rnx_show_refund_policy_on_product_page' , 'no' );
	}


}	
$rnx_price_acc_to_days = get_option( 'ced_rnx_price_reduced' , array() );
if(isset($_POST['ced_rnx_noti_save_return']))
{
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php _e('Settings saved.','woocommerce-refund-and-exchange'); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e('Dismiss this notices.','woocommerce-refund-and-exchange'); ?></span>
		</button>
	</div><?php
	unset($_POST['ced_rnx_noti_save_return']);
	$post = $_POST;
	foreach($post as $k=>$val)
	{
		update_option($k, $val);
	}
	if(!isset($post['ced_rnx_notification_return_cancel_template']))
	{
		update_option( 'ced_rnx_notification_return_cancel_template' , 'no' );
	}
	if(!isset($post['ced_rnx_notification_return_approve_wallet_template']))
	{
		update_option( 'ced_rnx_notification_return_approve_wallet_template' , 'no' );
	}
	if(!isset($post['ced_rnx_notification_return_approve_template']))
	{
		update_option( 'ced_rnx_notification_return_approve_template' , 'no' );
	}
	if(!isset($post['ced_rnx_notification_return_template']))
	{
		update_option( 'ced_rnx_notification_return_template' , 'no' );
	}
	if(!isset($post['ced_rnx_notification_auto_accept_return_template']))
	{
		update_option( 'ced_rnx_notification_auto_accept_return_template' , 'no' );
	}
}
if(isset($_POST['ced_rnx_noti_save_exchange']))
{
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php _e('Settings saved.','woocommerce-refund-and-exchange'); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e('Dismiss this notices.','woocommerce-refund-and-exchange'); ?></span>
		</button>
	</div><?php
	unset($_POST['ced_rnx_noti_save_exchange']);
	$post = $_POST;
	foreach($post as $k=>$val)
	{
		update_option($k, $val);
	}
	if(!isset($post['ced_notification_exchange_template']))
	{
		update_option( 'ced_notification_exchange_template' , 'no' );
	}
	if(!isset($post['ced_rnx_notification_exchange_approve_template']))
	{
		update_option( 'ced_rnx_notification_exchange_approve_template' , 'no' );
	}
	if(!isset($post['ced_rnx_notification_exchange_cancel_template']))
	{
		update_option( 'ced_rnx_notification_exchange_cancel_template' , 'no' );
	}
}
if(isset($_POST['ced_rnx_noti_save_return_slip']))
{
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php _e('Settings saved.','woocommerce-refund-and-exchange'); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e('Dismiss this notices.','woocommerce-refund-and-exchange'); ?></span>
		</button>
	</div><?php
	unset($_POST['ced_rnx_noti_save_return_slip']);
	$post = $_POST;
	foreach($post as $k=>$val)
	{
		update_option($k, $val);
	}
	if(!isset($post['ced_rnx_enable_return_ship_label']))
	{
		update_option( 'ced_rnx_enable_return_ship_label' , 'no' );
	}
}

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
{ ?>
	<div class="wrap ced_rnx_notification">
		<h2><?php _e('Notification Setting', 'woocommerce-refund-and-exchange' ); ?></h2>
		
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<a class="nav-tab <?php echo $basic_active;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=basic"><?php _e('Basic', 'woocommerce-refund-and-exchange' ); ?></a>
			<a class="nav-tab <?php echo $refund_active;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=refund"><?php _e('Refund', 'woocommerce-refund-and-exchange' ); ?></a>
			<a class="nav-tab <?php echo $exchange_active;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=exchange"><?php _e('Exchange', 'woocommerce-refund-and-exchange' ); ?></a>
			<a class="nav-tab <?php echo $return_ship_label_setting_active;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=return_ship_label_setting"><?php _e('Return Ship Label', 'woocommerce-refund-and-exchange' ); ?></a>
			<?php do_action('ced_rnx_notification_setting_tab'); ?>
			<?php do_action('ced_rnx_notification_setting_tab_before_addon_section'); ?>
			<a class="nav-tab <?php echo $ced_rnx_addon_section;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=ced_rnx_addon_section"><?php _e('RNX Add-Ons', 'woocommerce-refund-and-exchange' ); ?></a>
			<?php if( $ced_rnx_license_hash != $ced_rnx_hash)
			{ ?>
				<a class="nav-tab <?php echo $ced_rnx_license_section;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=ced_rnx_license_section"><?php _e('License Verification', 'woocommerce-refund-and-exchange' ); ?></a>
			<?php } ?>
			<a class="nav-tab <?php echo $ced_rnx_help_section;?>" href="<?php echo admin_url()?>admin.php?page=ced-rnx-notification&amp;tab=ced_rnx_help_section"><?php _e('Help', 'woocommerce-refund-and-exchange' ); ?></a>
		</nav>
		<a href="<?php echo home_url('/wp-admin/admin.php?page=wc-settings&tab=ced_rnx_setting')?>"><input type="button" value="<?php _e('GO TO SETTING', 'woocommerce-refund-and-exchange');?>" class="ced-rnx-save-button button button-primary" style="float:right;"></a></div>
		<div class="">
		<?php 
		do_action('ced_rnx_custom_setting_section_for_dokan');
	//Basic Tab of Notification setting

		if($tab == "basic")
		{
			$predefined_return_reason = get_option('ced_rnx_return_predefined_reason', false);
			$predefined_exchange_reason = get_option('ced_rnx_exchange_predefined_reason', false);
			?>
			<div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?> ">
				<form enctype="multipart/form-data" action="" id="mainform" method="post">
					<h2 id="rnx_mail_setting" class="ced_rnx_basic_setting ced_rnx_slide_active"><?php _e('Mail Setting', 'woocommerce-refund-and-exchange' ); ?></h2>
					<div id="rnx_mail_setting_wrapper">
						<table class="form-table ced_rnx_notification_section">
							<tbody>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e('From Name', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td class="forminp forminp-text">
										<?php 

										$admin_name = get_option('blogname');
										$fname = get_option('ced_rnx_notification_from_name', false);
										if(empty($fname))
										{
											$fname = $admin_name;
										}
										?>
										<input type="text" placeholder="" class="input-text" value="<?php echo $fname;?>" style="" id="ced_rnx_notification_from_name" name="ced_rnx_notification_from_name">
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_mail"><?php _e('From Email', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td class="forminp forminp-email">
										<?php 
										$admin_email = get_option('admin_email');
										$email = get_option('ced_rnx_notification_from_mail', false);
										if(empty($email))
										{
											$email = $admin_email;
										}
										?>
										<input type="email" placeholder="" class="input-text" value="<?php echo $email;?>" id="ced_rnx_notification_from_mail" name="ced_rnx_notification_from_mail">
									</td>
								</tr>

								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Mail Header', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td class="forminp forminp-textarea">
										<?php 
										$content = stripslashes(get_option('ced_rnx_notification_mail_header', false));
										$editor_id = 'ced_rnx_notification_mail_header';
										$settings = array(
											'media_buttons'    => true,
											'drag_drop_upload' => true,
											'dfw'              => true,
											'teeny'            => true,
											'editor_height'    => 200,
											'editor_class'	   => '',
											'textarea_name'    => "ced_rnx_notification_mail_header"
											);
										wp_editor( $content, $editor_id, $settings );
										?>
									</td>
								</tr>

								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Mail Footer', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td class="forminp forminp-textarea">
										<?php 
										$content = stripslashes(get_option('ced_rnx_notification_mail_footer', false));
										$editor_id = 'ced_rnx_notification_mail_footer';
										$settings = array(
											'media_buttons'    => true,
											'drag_drop_upload' => true,
											'dfw'              => true,
											'teeny'            => true,
											'editor_height'    => 200,
											'editor_class'	   => '',
											'textarea_name'    => "ced_rnx_notification_mail_footer"
											);
										wp_editor( $content, $editor_id, $settings );
										?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>	
					<h2 id="rnx_return_reason" class="ced_rnx_basic_setting"><?php _e('Predefined Refund Reason', 'woocommerce-refund-and-exchange' ); ?></h2>
					<div id="rnx_return_reason_wrapper" class="ced_rnx_basic_wrapper">
						<table class="form-table ced_rnx_notification_section">
							<tbody>
								<tr valign="top">
									<td class="titledesc" scope="row" colspan="2">
										<div id="ced_rnx_return_predefined_reason_wrapper">
											<?php 
											if(isset($predefined_return_reason) && !empty($predefined_return_reason))
											{
												foreach($predefined_return_reason as $predefine_reason)
												{
													if(!empty($predefine_reason))
													{	
														?>
														<input type="text" class="input-text" value="<?php echo $predefine_reason;?>" class="ced_rnx_return_predefined_reason" name="ced_rnx_return_predefined_reason[]">
														<?php 
													}
													else
													{
														?>
														<input type="text" class="input-text" class="ced_rnx_return_predefined_reason" name="ced_rnx_return_predefined_reason[]">
														<?php 
													}
												}	
											}
											else
											{		
												?>
												<input type="text" class="input-text" class="ced_rnx_return_predefined_reason" name="ced_rnx_return_predefined_reason[]">
												<?php 
											}
											?>
										</div>
										<input type="button" value="<?php _e('ADD MORE', 'woocommerce-refund-and-exchange' ); ?>" class="button" id="ced_rnx_return_predefined_reason_add">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<h2 id="rnx_exchange_reason" class="ced_rnx_basic_setting"><?php _e('Predefined Exchange Reason', 'woocommerce-refund-and-exchange' ); ?></h2>
					<div id="rnx_exchange_reason_wrapper" class="ced_rnx_basic_wrapper">
						<table class="form-table ced_rnx_notification_section">
							<tbody>
								<tr valign="top">
									<td class="titledesc" scope="row" colspan="2">
										<div id="ced_rnx_exchange_predefined_reason_wrapper">
											<?php 
											if(isset($predefined_exchange_reason) && !empty($predefined_exchange_reason))
											{
												foreach($predefined_exchange_reason as $predefine_reason)
												{
													if(!empty($predefine_reason))
													{	
														?>
														<input type="text" class="input-text" value="<?php echo $predefine_reason;?>" class="ced_rnx_exchange_predefined_reason" name="ced_rnx_exchange_predefined_reason[]">
														<?php 
													}
													else
													{
														?>
														<input type="text" class="input-text" class="ced_rnx_exchange_predefined_reason" name="ced_rnx_exchange_predefined_reason[]">
														<?php 
													}
												}	
											}
											else
											{		
												?>
												<input type="text" class="input-text" class="ced_rnx_exchange_predefined_reason" name="ced_rnx_exchange_predefined_reason[]">
												<?php 
											}
											?>
										</div>
										<input type="button" value="<?php _e('ADD MORE', 'woocommerce-refund-and-exchange' ); ?>" class="button" id="ced_rnx_exchange_predefined_reason_add">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<h2 id="rnx_refund_rules" class="ced_rnx_basic_setting"><?php _e('Refund Policy', 'woocommerce-refund-and-exchange' ); ?></h2>
					<div id="rnx_refund_rules_wrapper" class="ced_rnx_basic_wrapper">
						<?php do_action( 'ced_rnx_add_return_policy_settings_before' ); ?>
						<h2><?php /*_e( 'Price Based Policy', 'woocommerce-refund-and-exchange' );*/ ?></h2>
						<?php $ced_rnx_enable_price_policy = get_option( 'ced_rnx_enable_price_policy', 'no' );
						$ced_rnx_show_refund_policy_on_product_page = get_option( 'ced_rnx_show_refund_policy_on_product_page', 'no' );
						?>
						<table class="form-table ced_rnx_notification_section">
							<tbody>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e('Enable Price Based Policy', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td>
										<span class="ced_rnx_checkbox_wrrapper">
											<input type="checkbox" id="ced_rnx_enable_price_policy" name="ced_rnx_enable_price_policy" <?php if ($ced_rnx_enable_price_policy == 'on') {
												?>checked="checked"<?php
											} ?>></input><label for="ced_rnx_enable_price_policy"></label>
											<label for="ced_rnx_enable_price_policy"></label>
										</span>
										<span class="description"><?php _e( 'Enable to add price based Refund policy rules', 'woocommerce-refund-and-exchange' ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_show_refund_policy_on_product_page"><?php _e('Show Refund Policy On Product Page', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td>
										<span class="ced_rnx_checkbox_wrrapper">
											<input type="checkbox" id="ced_rnx_show_refund_policy_on_product_page" name="ced_rnx_show_refund_policy_on_product_page" <?php if ($ced_rnx_show_refund_policy_on_product_page == 'on') {
												?>checked="checked"<?php
											} ?>></input>
											<label for="ced_rnx_show_refund_policy_on_product_page"></label>
										</span>
										<span class="description"><?php _e( 'Enable to add Refund policy tab in product page. Also you can use <strong>[ced_rnx_refund_policy]</strong> shortcode for show Refund Policy Table.', 'woocommerce-refund-and-exchange' ); ?></span>
									</td>
								</tr>
							</tbody>
						</table>
						<table class="form-table ced_rnx_notification_section" id="ced_rnx_price_based_policy">
							<tbody>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e('Number of days to Refund', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e( 'Precentage Price Reduced', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e( 'Action', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
								</tr>
								<?php 
								$rnx_number_of_days = get_option( 'ced_rnx_number_of_days' , array() );
								$rnx_price_acc_to_days = get_option( 'ced_rnx_price_reduced' , array() );
								if ( is_array($rnx_number_of_days) && !empty( $rnx_number_of_days ) ) {
									foreach ($rnx_number_of_days as $key => $value) {
										?>
										<tr valign="top" class="price_row">
											<th class="titledesc" scope="row">
												<input type="number" name="ced_rnx_number_of_days[]" class="ced_rnx_number_of_days" value="<?php echo $value; ?>" min="0"></input>
											</th>
											<th class="titledesc" scope="row">
												<input type="text" name="ced_rnx_price_reduced[]" class="ced_rnx_price_reduced" value="<?php echo $rnx_price_acc_to_days[$key] ; ?>" placeholder="<?php _e('Enter % Price to be reduced' , 'woocommerce-refund-and-exchange'); ?>"></input>
											</th>
											<th class="titledesc" scope="row">
												<input type="button" class="ced_rnx_add_price_row button" value="<?php _e( 'Add','woocommerce-refund-and-exchange' ) ?>"></input>
												<?php if( $key > 0 ){
													?>
													<input type="button" class="ced_rnx_remove_price_row button" value="<?php _e( 'Remove','woocommerce-refund-and-exchange' ) ?>"></input>
													<?php
												} ?>
											</th>
										</tr>
										<?php
									} 
								}else{
									?>
									<tr valign="top" class="price_row">
										<th class="titledesc" scope="row">
											<input type="number" name="ced_rnx_number_of_days[]" class="ced_rnx_number_of_days" min="0"></input>
										</th>
										<th class="titledesc" scope="row">
											<input type="text" name="ced_rnx_price_reduced[]" class="ced_rnx_price_reduced" placeholder="<?php _e('Enter % Price to be reduced' , 'woocommerce-refund-and-exchange'); ?>"></input>
										</th>
										<th class="titledesc" scope="row">
											<input type="button" class="ced_rnx_add_price_row button" value="<?php _e( 'Add','woocommerce-refund-and-exchange' ) ?>"></input>
										</th>
									</tr>
									<?php
								}?>
								<?php $text = get_option( 'ced_rnx_price_deduct_message', '' ); ?>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e('Price Deduction Message', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<th class="titledesc" scope="row">
										<input type="text" name="ced_rnx_price_deduct_message" id="ced_rnx_price_deduct_message" value="<?php echo $text; ?>" placeholder="Enter message to be shown"></input>
									</th>
								</tr>
							</tbody>
						</table>
						<?php $ced_rnx_enable_time_policy = get_option( 'ced_rnx_enable_time_policy', 'no' ); ?>
						<table class="form-table ced_rnx_notification_section">
							<tbody>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e('Enable Time Based Policy', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td>
										<span class="ced_rnx_checkbox_wrrapper">
											<input type="checkbox" id="ced_rnx_enable_time_policy" name="ced_rnx_enable_time_policy" <?php if ($ced_rnx_enable_time_policy == 'on') {
												?>checked="checked"<?php
											} ?>></input>
											<label for="ced_rnx_enable_time_policy"></label>
										</span>
										<span class="description"><?php _e( 'Enable to add time based Refund policy rules', 'woocommerce-refund-and-exchange' ); ?></span>
									</td>
								</tr>
							</tbody>
						</table>
						<?php $ced_rnx_from_time = get_option( 'ced_rnx_return_from_time', '' ); ?>
						<?php $ced_rnx_to_time = get_option( 'ced_rnx_return_to_time', '' ); ?>
						<table class="form-table ced_rnx_notification_section" id="ced_rnx_time_based_policy">
							<tbody>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_notification_from_name"><?php _e('Allow Refund Request Between', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<th class="titledesc" scope="row">
										<input type="text" value="<?php echo $ced_rnx_from_time ; ?>" class="ced_rnx_date_time_picker" id="ced_rnx_return_from_time" placeholder="hh:mm AM" name="ced_rnx_return_from_time"></input>
									</th>
									<th class="titledesc" scope="row">
										<input type="text" value="<?php echo $ced_rnx_to_time ; ?>" class="ced_rnx_date_time_picker" id="ced_rnx_return_to_time" placeholder="hh:mm PM" name="ced_rnx_return_to_time"></input>
									</th>
								</tr>
							</tbody>
						</table>
						<?php do_action( 'ced_rnx_add_return_policy_settings_after' ); ?>
					</div>	
					<p class="submit">
						<input type="submit" value="<?php _e('Save changes', 'woocommerce-refund-and-exchange' ); ?>" class="button-primary woocommerce-save-button ced-rnx-save-button" name="ced_rnx_noti_save_basic"> 
					</p>
				</form>
			</div>
			<?php 
		}

	//Refund Tab of Notification setting

		if($tab == "refund")
		{
			?>
			<div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>">
				<form enctype="multipart/form-data" action="" id="mainform" method="post">

					<div id="ced_rnx_accordion">
						<div class="ced_rnx_accord_sec_wrap">
							<h2><?php _e('Short-Codes ', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec ced_rnx_notification_section">
								<p><h3><?php _e("These are order shortcode that you can use in EMAIL MESSESGES. It will be changed with order's dynamic values.", 'woocommerce-refund-and-exchange');?></h3></p>
								<p><?php echo sprintf(__('%s Note :%s Use %s [order] %s for Order Number, %s [siteurl] %s for home page url and %s [username] %s for user name.','woocommerce-refund-and-exchange'),'<b>','</b>','<b>','</b>','<b>','</b>','<b>','</b>');?></p>
								<p><?php echo '<strong> [_order_total] , [formatted_shipping_address] , [formatted_billing_address] , [_billing_company] , [_billing_email] , [_billing_phone] , [_billing_country] , [_billing_address_1] , [_billing_address_2] , [_billing_state] , [_billing_postcode] , [_shipping_first_name] , [_shipping_last_name] , [_shipping_company] , [_shipping_country] , [_shipping_address_1] , [_shipping_address_2] , [_shipping_city] , [_shipping_state] , [_shipping_postcode] , [_payment_method_tittle] , [_order_shipping] , [_refundable_amount]</strong>'?></p>

							</div>
						</div>
					</div>
					<div id="ced_rnx_accordion">
						<div class="ced_rnx_accord_sec_wrap">
							<h2 class="ced_rnx_slide_active"><?php _e('Merchant Setting', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec ced_rnx_notification_sec_active">
								<table class="form-table ced_rnx_notification_section">
									<tbody>

										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_merchant_return_subject"><?php _e('Merchant Refund Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-text">
												<?php 
												$merchant_subject = get_option('ced_rnx_notification_merchant_return_subject', false);
												?>
												<input type="text" placeholder="" class="input-text" value="<?php echo $merchant_subject;?>" style="" id="ced_rnx_notification_merchant_return_subject" name="ced_rnx_notification_merchant_return_subject">
											</td>
										</tr>

									</tbody>
								</table>
							</div>
						</div>

						<div class="ced_rnx_accord_sec_wrap">
							<h2><?php _e('Auto Accept Refund Request', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec">
								<table class="form-table ced_rnx_notification_section">
									<tbody>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_auto_accept_return_subject"><?php _e('Auto Accept Refund Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-text">
												<?php 
												$return_accept_subject = get_option('ced_rnx_notification_auto_accept_return_subject', false);
												?>
												<input type="text" placeholder="" class="input-text" value="<?php echo $return_accept_subject;?>" style="" id="ced_rnx_notification_auto_accept_return_subject" name="ced_rnx_notification_auto_accept_return_subject">
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php $ced_rnx_notification_auto_accept_return_template =get_option('ced_rnx_notification_auto_accept_return_template', 'no'); ?>
												<span class="ced_rnx_checkbox_wrrapper">
													<input type="checkbox" id="ced_rnx_notification_auto_accept_return_template" name="ced_rnx_notification_auto_accept_return_template" <?php if ($ced_rnx_notification_auto_accept_return_template == 'on') {
														?>checked="checked"<?php
													}  ?>><label for="ced_rnx_notification_auto_accept_return_template"></label>
												</span>
												<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Auto Accept Refund Request Message', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php 
												$content = stripslashes(get_option('ced_rnx_notification_auto_accept_return_rcv', false));
												$editor_id = 'ced_rnx_notification_auto_accept_return_rcv';
												$settings = array(
													'media_buttons'    => false,
													'drag_drop_upload' => true,
													'dfw'              => true,
													'teeny'            => true,
													'editor_height'    => 200,
													'editor_class'	   => '',
													'textarea_name'    => "ced_rnx_notification_auto_accept_return_rcv"
													);
												wp_editor( $content, $editor_id, $settings );
												?>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<div class="ced_rnx_accord_sec_wrap">
							<h2><?php _e('Refund Request', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec">
								<table class="form-table ced_rnx_notification_section">
									<tbody>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_return_subject"><?php _e('Refund Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-text">
												<?php 
												$return_cancel_subject = get_option('ced_rnx_notification_return_subject', false);
												?>
												<input type="text" placeholder="" class="input-text" value="<?php echo $return_cancel_subject;?>" style="" id="ced_rnx_notification_return_subject" name="ced_rnx_notification_return_subject">
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php $ced_rnx_notification_return_template =get_option('ced_rnx_notification_return_template', 'no'); ?>
												<span class="ced_rnx_checkbox_wrrapper">
													<input type="checkbox" id="ced_rnx_notification_return_template" name="ced_rnx_notification_return_template" <?php if ($ced_rnx_notification_return_template == 'on') {
														?>checked="checked"<?php
													}  ?>><label for="ced_rnx_notification_return_template"></label>
												</span>
												<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_return_rcv"><?php _e('Recieved Refund Request Message', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php 
												$content = stripslashes(get_option('ced_rnx_notification_return_rcv', false));
												$editor_id = 'ced_rnx_notification_return_rcv';
												$settings = array(
													'media_buttons'    => false,
													'drag_drop_upload' => true,
													'dfw'              => true,
													'teeny'            => true,
													'editor_height'    => 200,
													'editor_class'	   => '',
													'textarea_name'    => "ced_rnx_notification_return_rcv"
													);
												wp_editor( $content, $editor_id, $settings );
												?>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="ced_rnx_accord_sec_wrap">
							<h2><?php _e('Refund Approved', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec">
								<table class="form-table ced_rnx_notification_section">
									<tbody>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_return_approve_subject"><?php _e('Approved Refund Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-text">
												<?php 
												$return_subject = get_option('ced_rnx_notification_return_approve_subject', false);
												?>
												<input type="text" placeholder="" class="input-text" value="<?php echo $return_subject;?>" style="" id="ced_rnx_notification_return_approve_subject" name="ced_rnx_notification_return_approve_subject">
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php $ced_rnx_notification_return_approve_template =get_option('ced_rnx_notification_return_approve_template', 'no'); ?>
												<span class="ced_rnx_checkbox_wrrapper">
													<input type="checkbox" id="ced_rnx_notification_return_approve_template" name="ced_rnx_notification_return_approve_template" <?php if ($ced_rnx_notification_return_approve_template == 'on') {
														?>checked="checked"<?php
													}  ?>><label for="ced_rnx_notification_return_approve_template"></label>
												</span>
												<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_return_approve"><?php _e('Approved Refund Request Message', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php 
												$content = stripslashes(get_option('ced_rnx_notification_return_approve', false));
												$editor_id = 'ced_rnx_notification_return_approve';
												$settings = array(
													'media_buttons'    => false,
													'drag_drop_upload' => true,
													'dfw'              => true,
													'teeny'            => true,
													'editor_height'    => 200,
													'editor_class'	   => '',
													'textarea_name'    => "ced_rnx_notification_return_approve"
													);
												wp_editor( $content, $editor_id, $settings );
												?>
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php $ced_rnx_notification_return_approve_wallet_template =get_option('ced_rnx_notification_return_approve_wallet_template', 'no'); ?>
												<span class="ced_rnx_checkbox_wrrapper">
													<input type="checkbox" id="ced_rnx_notification_return_approve_wallet_template" name="ced_rnx_notification_return_approve_wallet_template" <?php if ($ced_rnx_notification_return_approve_wallet_template == 'on') {
														?>checked="checked"<?php
													}  ?>><label for="ced_rnx_notification_return_approve_wallet_template"></label>
												</span>
												<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
											</td>
										</tr>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_return_approve_wallet"><?php _e('Approved Refund Request Message (Wallet Feature enabled)', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-textarea">
												<?php 
												$content = stripslashes(get_option('ced_rnx_notification_return_approve_wallet', false));
												$editor_id = 'ced_rnx_notification_return_approve_wallet';
												$settings = array(
													'media_buttons'    => false,
													'drag_drop_upload' => true,
													'dfw'              => true,
													'teeny'            => true,
													'editor_height'    => 200,
													'editor_class'	   => '',
													'textarea_name'    => "ced_rnx_notification_return_approve_wallet"
													);
												wp_editor( $content, $editor_id, $settings );
												?>
											</td>
										</tr>

									</tbody>
								</table>
							</div>
						</div>
						<div class="ced_rnx_accord_sec_wrap">
							<h2><?php _e('Refund Cancel', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec ">
								<table class="form-table ced_rnx_notification_section ">
									<tbody>
										<tr valign="top">
											<th class="titledesc" scope="row">
												<label for="ced_rnx_notification_return_cancel_subject"><?php _e('Cancelled Refund Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
											</th>
											<td class="forminp forminp-text">
												<?php 
												$return_subject = get_option('ced_rnx_notification_return_cancel_subject', false);
												?>
												<input type="text" placeholder="" class="input-text" value="<?php echo $return_subject?>" style="" id="ced_rnx_notification_return_cancel_subject" name="ced_rnx_notification_return_cancel_subject">
											</td>
										</tr>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-textarea">
											<?php $ced_rnx_notification_return_cancel_template =get_option('ced_rnx_notification_return_cancel_template', 'no'); ?>
											<span class="ced_rnx_checkbox_wrrapper">
												<input type="checkbox" id="ced_rnx_notification_return_approve_wallet_template" name="ced_rnx_notification_return_cancel_template" <?php if ($ced_rnx_notification_return_cancel_template == 'on') {
													?>checked="checked"<?php
												}  ?>><label for="ced_rnx_notification_return_approve_wallet_template"></label>
											</span>
											<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_rnx_notification_return_cancel"><?php _e('Cancelled Refund Request Message', 'woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-textarea">
											<?php 
											$content = stripslashes(get_option('ced_rnx_notification_return_cancel', false));
											$editor_id = 'ced_rnx_notification_return_cancel';
											$settings = array(
												'media_buttons'    => false,
												'drag_drop_upload' => true,
												'dfw'              => true,
												'teeny'            => true,
												'editor_height'    => 200,
												'editor_class'	   => '',
												'textarea_name'    => "ced_rnx_notification_return_cancel"
												);
											wp_editor( $content, $editor_id, $settings );
											?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>		
				<p class="submit">
					<input type="submit" value="<?php _e('Save
					Settings', 'woocommerce-refund-and-exchange' ); ?>" class="ced-rnx-save-button button-primary woocommerce-save-button" name="ced_rnx_noti_save_return"> 
				</p>
			</form>
		</div>
		<?php 
	}

	//Exchange Tab of Notification setting

	if($tab == "exchange")
	{
		?>
		<div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>">
			<form enctype="multipart/form-data" action="" id="mainform" method="post">
				<div id="ced_rnx_accordion">
					<div class="ced_rnx_accord_sec_wrap">
						<h2><?php _e('Short-Codes ', 'woocommerce-refund-and-exchange' ); ?></h2>
						<div class="ced_rnx_content_sec ced_rnx_notification_section">
							<p><?php _e('These are order shortcode that you can use in EMAIL MESSESGE', 'woocommerce-refund-and-exchange');?></p>
							<p><?php echo sprintf(__('%s Note :%s Use %s [order] %s for Order Number, %s [siteurl] %s for home page url and %s [username] %s for user name.','woocommerce-refund-and-exchange'),'<b>','</b>','<b>','</b>','<b>','</b>','<b>','</b>');?></p>
							<p><?php echo '<strong> [_order_total] , [formatted_shipping_address] , [formatted_billing_address] , [_billing_company] , [_billing_email] , [_billing_phone] , [_billing_country] , [_billing_address_1] , [_billing_address_2] , [_billing_state] , [_billing_postcode] , [_shipping_first_name] , [_shipping_last_name] , [_shipping_company] , [_shipping_country] , [_shipping_address_1] , [_shipping_address_2] , [_shipping_city] , [_shipping_state] , [_shipping_postcode] , [_payment_method_tittle] , [_order_shipping] , [_refundable_amount]</strong>'?></p>

						</div>
					</div>
				</div>
				<div id="ced_rnx_accordion">
					<div class="ced_rnx_accord_sec_wrap">
						<h2 class="ced_rnx_slide_active"><?php _e('Merchant Setting', 'woocommerce-refund-and-exchange' ); ?></h2>
						<div class="ced_rnx_content_sec ced_rnx_notification_sec_active">
							<table class="form-table ced_rnx_notification_section">
								<tbody>

									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_rnx_notification_merchant_exchange_subject"><?php _e('Merchant Exchange Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-text">
											<?php 
											$merchant_subject = get_option('ced_rnx_notification_merchant_exchange_subject', false);
											?>
											<input type="text" placeholder="" class="input-text" value="<?php echo $merchant_subject;?>" style="" id="ced_rnx_notification_merchant_exchange_subject" name="ced_rnx_notification_merchant_exchange_subject">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>	
					<!-- Exchange request received -->

					<div class="ced_rnx_accord_sec_wrap">
						<h2><?php _e('Exchange Request', 'woocommerce-refund-and-exchange' ); ?></h2>
						<div class="ced_rnx_content_sec ">
							<table class="form-table ced_rnx_notification_section">
								<tbody>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_notification_exchange_subject"><?php _e('Exchange Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-text">
											<?php 
											$exchange_subject = get_option('ced_notification_exchange_subject', false);
											?>
											<input type="text" placeholder=""class="input-text" value="<?php echo $exchange_subject;?>" style="" id="ced_notification_exchange_subject" name="ced_notification_exchange_subject">
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-textarea">
											<?php $ced_notification_exchange_template =get_option('ced_notification_exchange_template', 'no'); ?>
											<span class="ced_rnx_checkbox_wrrapper">
												<input type="checkbox" id="ced_notification_exchange_template" name="ced_notification_exchange_template" <?php if ($ced_notification_exchange_template == 'on') {
													?>checked="checked"<?php
												}  ?>><label for="ced_notification_exchange_template"></label>
											</span>
											<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_notification_exchange_rcv"><?php _e('Recieved Exchange Request Message', 'woocommerce-refund-and-exchange' ); ?></label> 
										</th>
										<td class="forminp forminp-textarea">
											<?php 
											$content = stripslashes(get_option('ced_notification_exchange_rcv', false));
											$editor_id = 'ced_notification_exchange_rcv';
											$settings = array(
												'media_buttons'    => false,
												'drag_drop_upload' => true,
												'dfw'              => true,
												'teeny'            => true,
												'editor_height'    => 200,
												'editor_class'	   => '',
												'textarea_name'    => "ced_notification_exchange_rcv"
												);
											wp_editor( $content, $editor_id, $settings );
											?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>	
					<!-- Exchange request accepted -->

					<div class="ced_rnx_accord_sec_wrap">
						<h2><?php _e('Exchange Approved', 'woocommerce-refund-and-exchange' ); ?></h2>
						<div class="ced_rnx_content_sec ">
							<table class="form-table ced_rnx_notification_section">
								<tbody>	
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_notification_exchange_subject"><?php _e('Approve Exchange Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-text">
											<?php 
											$exchange_subject = get_option('ced_rnx_notification_exchange_approve_subject', false);
											?>
											<input type="text" placeholder=""class="input-text" value="<?php echo $exchange_subject;?>" style="" id="ced_rnx_notification_exchange_approve_subject" name="ced_rnx_notification_exchange_approve_subject">
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-textarea">
											<?php $ced_rnx_notification_exchange_approve_template =get_option('ced_rnx_notification_exchange_approve_template', 'no'); ?>
											<span class="ced_rnx_checkbox_wrrapper">
												<input type="checkbox" id="ced_rnx_notification_exchange_approve_template" name="ced_rnx_notification_exchange_approve_template" <?php if ($ced_rnx_notification_exchange_approve_template == 'on') {
													?>checked="checked"<?php
												}  ?>><label for="ced_rnx_notification_exchange_approve_template"></label>
											</span>
											<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_notification_exchange_rcv"><?php _e('Recieved Exchange Request Message', 'woocommerce-refund-and-exchange' ); ?></label> 
										</th>
										<td class="forminp forminp-textarea">
											<?php 
											$content = stripslashes(get_option('ced_rnx_notification_exchange_approve', false));
											$editor_id = 'ced_rnx_notification_exchange_approve';
											$settings = array(
												'media_buttons'    => false,
												'drag_drop_upload' => true,
												'dfw'              => true,
												'teeny'            => true,
												'editor_height'    => 200,
												'editor_class'	   => '',
												'textarea_name'    => "ced_rnx_notification_exchange_approve"
												);
											wp_editor( $content, $editor_id, $settings );
											?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!-- Exchange request cancel -->

					<div class="ced_rnx_accord_sec_wrap">
						<h2><?php _e('Exchange Cancel', 'woocommerce-refund-and-exchange' ); ?></h2>
						<div class="ced_rnx_content_sec ">
							<table class="form-table ced_rnx_notification_section">
								<tbody>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_notification_exchange_subject"><?php _e('Cancel Exchange Request Subject', 'woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-text">
											<?php 
											$exchange_subject = get_option('ced_rnx_notification_exchange_cancel_subject', false);
											?>
											<input type="text" placeholder=""class="input-text" value="<?php echo $exchange_subject;?>" style="" id="ced_rnx_notification_exchange_cancel_subject" name="ced_rnx_notification_exchange_cancel_subject">
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_rnx_notification_auto_accept_return_rcv"><?php _e('Enable for custom email template','woocommerce-refund-and-exchange' ); ?></label>
										</th>
										<td class="forminp forminp-textarea">
											<?php $ced_rnx_notification_exchange_cancel_template =get_option('ced_rnx_notification_exchange_cancel_template', 'no'); ?>
											<span class="ced_rnx_checkbox_wrrapper">
												<input type="checkbox" id="ced_rnx_notification_exchange_cancel_template" name="ced_rnx_notification_exchange_cancel_template" <?php if ($ced_rnx_notification_exchange_cancel_template == 'on') {
													?>checked="checked"<?php
												}  ?>><label for="ced_rnx_notification_exchange_cancel_template"></label>
											</span>
											<?php _e('Enable, if you want to put custom email template in editor & Put your email template under text tab of editor. ','woocommerce-refund-and-exchange' ); ?>
										</td>
									</tr>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="ced_notification_exchange_rcv"><?php _e('Recieved Exchange Request Message', 'woocommerce-refund-and-exchange' ); ?></label> 
										</th>
										<td class="forminp forminp-textarea">

											<?php

											$content = stripslashes(get_option('ced_rnx_notification_exchange_cancel', false));
											$editor_id = 'ced_rnx_notification_exchange_cancel';
											$settings = array(
												'media_buttons'    => false,
												'drag_drop_upload' => true,
												'dfw'              => true,
												'teeny'            => true,
												'editor_height'    => 200,
												'editor_class'	   => '',
												'textarea_name'    => "ced_rnx_notification_exchange_cancel"
												);
											wp_editor( $content, $editor_id, $settings );
											?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<p class="submit">
					<input type="submit" value="<?php _e('Save changes', 'woocommerce-refund-and-exchange' ); ?>" class="ced-rnx-save-button button-primary woocommerce-save-button" name="ced_rnx_noti_save_exchange"> 
				</p>
			</form>
		</div>
		<?php 
	}

	if($tab == 'return_ship_label_setting')
	{
		?>
		<div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>">
			<form enctype="multipart/form-data" action="" id="mainform" method="post">
				<div id="ced_rnx_accordion">
					<div class="ced_rnx_accord_sec_wrap">
						<h2 id="rnx_mail_setting" class="ced_rnx_basic_setting ced_rnx_slide_active"><?php _e('Return Slip Setting', 'woocommerce-refund-and-exchange' ); ?></h2>
						<div id="rnx_mail_setting_wrapper">
							<table class="form-table ced_rnx_notification_section">
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_enable_return_ship_label"><?php _e('Enable Shiping Label', 'woocommerce-refund-and-exchange' ); ?></label> 
									</th>
									<td class="forminp forminp-textarea">
										<?php $ced_rnx_enable_return_ship_label =get_option('ced_rnx_enable_return_ship_label', 'no'); 
										?>
										<span class="ced_rnx_checkbox_wrrapper">
											<input type="checkbox" id="ced_rnx_enable_return_ship_label" name="ced_rnx_enable_return_ship_label"<?php if ($ced_rnx_enable_return_ship_label == 'on') {
												?>checked="checked"<?php
											} ?>></input><label for="ced_rnx_enable_return_ship_label"></label>
										</span>
										<span class="description"><?php _e( 'Enable this to send a return Slip Label to customer for sending return Product Back.', 'woocommerce-refund-and-exchange' ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_return_slip_mail_subject"><?php _e('Return Slip Mail Subject', 'woocommerce-refund-and-exchange' ); ?></label>
									</th>
									<td class="forminp forminp-text">
										<?php 
										$ced_rnx_return_slip_mail_subject = get_option('ced_rnx_return_slip_mail_subject', false);
										?>
										<input type="text" placeholder=""class="input-text" value="<?php echo $ced_rnx_return_slip_mail_subject;?>" style="" id="ced_rnx_return_slip_mail_subject" name="ced_rnx_return_slip_mail_subject">
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="ced_rnx_return_ship_template"><?php _e('Return Slip template', 'woocommerce-refund-and-exchange' ); ?></label> 
									</th>
									<td class="forminp forminp-textarea">
										<p><?php _e(' Use ', 'woocommerce-refund-and-exchange'); ?><b>[Tracking_Id]</b><?php _e(' Shortcode in Place of Tracking Id. Here tracking id represent order Id.', 'woocommerce-refund-and-exchange') ?>
											<br><?php _e(' Use ', 'woocommerce-refund-and-exchange'); ?><b>[Order_shipping_address]</b><?php _e(' Shortcode in place of return label "from" column.  ', 'woocommerce-refund-and-exchange') ?><br><?php _e(' Use ', 'woocommerce-refund-and-exchange'); ?><b>[siteurl]</b><?php _e(' Shortcode in place of your site url.', 'woocommerce-refund-and-exchange') ?><br><?php _e(' Use ', 'woocommerce-refund-and-exchange'); ?><b>[username]</b><?php _e(' Shortcode in place of Customer name.', 'woocommerce-refund-and-exchange') ?></p>
											<?php 
											$content = stripslashes(get_option('ced_rnx_return_ship_template', false));
											$editor_id = 'ced_rnx_return_ship_template';
											$settings = array(
												'media_buttons'    => false,
												'drag_drop_upload' => true,
												'dfw'              => true,
												'teeny'            => true,
												'editor_height'    => 200,
												'editor_class'	   => '',
												'textarea_name'    => "ced_rnx_return_ship_template"
												);
											wp_editor( $content, $editor_id, $settings );
											?>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div id="ced_rnx_accordion">
						<div class="ced_rnx_accord_sec_wrap">
							<h2><?php _e('Terms & condition Setting', 'woocommerce-refund-and-exchange' ); ?></h2>
							<div class="ced_rnx_content_sec ">
								<table class="form-table ced_rnx_notification_section">
									<tr>
										<td><br>
											<?php _e('<b>1-</b> You need to create a terms & Condition page and put your all terms and conditions as content of page. <a href="'. home_url().'/wp-admin/post-new.php?post_type=page">Click Here</a> to create terms & conditions Page.<br><br>
											<b>2-</b> After page setup you need to go with WooCommerce->Settings->checkout and select terms and conditions page under Terms and conditions setting. <a href="'. home_url().'/wp-admin/admin.php?page=wc-settings&tab=checkout">Click Here</a> to start terms & conditions Policy.','woocommerce-refund-and-exchange'); ?>
										</td>
									</tr>
								</table>

							</div>
						</div>
					</div>
					<p class="submit">
						<input type="submit" value="<?php _e('Save changes', 'woocommerce-refund-and-exchange' ); ?>" class="ced-rnx-save-button button-primary woocommerce-save-button" name="ced_rnx_noti_save_return_slip"> 
					</p>
				</form>
			</div>		
			<?php
		}

		do_action('ced_rnx_custom_setting_section');

		if($tab == 'ced_rnx_help_section')
		{
			?><div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>"><?php
			include_once CED_REFUND_N_EXCHANGE_DIRPATH.'template/ced-rnx-help-template.php';
			?></div><?php
		}
		if($tab == 'ced_rnx_addon_section')
		{
			?><div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>"><?php
			include_once CED_REFUND_N_EXCHANGE_DIRPATH.'template/ced-rnx-addon-template.php';
			?></div><?php
		}
		if($tab == 'ced_rnx_license_section')
		{
			?><div class="mwb_table <?php if($ced_rnx_hide_sidebar_forever == 'yes'){ echo 'ced_rnx_sidebar_hide'; }?>"><?php
			include_once CED_REFUND_N_EXCHANGE_DIRPATH.'template/ced-rnx-license-template.php';
			?></div><?php
		}
		?>
	</div>
	<?php
}else{
	include_once CED_REFUND_N_EXCHANGE_DIRPATH.'template/ced-rnx-license-template.php';
}