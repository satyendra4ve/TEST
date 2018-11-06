<?php
/**
* File: settings.php
* Author: ArticNet LLC.
**/

if(!class_exists('WooCommerce_Dropshippers_Settings'))
{
	class WooCommerce_Dropshippers_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct
		
		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init()
		{
			// register your plugin's settings
			register_setting('WooCommerce_Dropshippers', 'woocommerce_dropshippers_options');

			// add a settings section
			add_settings_section(
				'WooCommerce_Dropshippers-section', 
				__('WooCommerce Dropshipper Settings','woocommerce-dropshippers'), 
				array(&$this, 'settings_section_WooCommerce_Dropshippers'), 
				'WooCommerce_Dropshippers'
			);


			// GENERAL TAB
			add_settings_field(
				'woocommerce_dropshippers_admin_email',
				__("The dropshippers' notifications will be sent to this email address. If left empty, the default Wordpress admin email will be used instead.",'woocommerce-dropshippers'),
				array(&$this, 'settings_field_admin_email'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_admin_email_cc',
				__("In addition to the Administrator, the notifications from dropshippers will also be sent in CC to this email address. If empty, no additional email will be sent.",'woocommerce-dropshippers'),
				array(&$this, 'settings_field_admin_email_cc'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_dropshipper_email_bcc',
				__("Send a BCC mail copy to this mail address for all the mails sent to the dropshippers",'woocommerce-dropshippers'),
				array(&$this, 'settings_field_dropshipper_email_bcc'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_can_put_order_to_completed',
				__('Put the order state to "Completed" when all the dropshippers have shipped their products','woocommerce-dropshippers'),
				array(&$this, 'settings_field_can_put_order_to_completed'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_mark_as_shipped',
				__('Allow Dropshippers to mark their orders as shipped by clicking a link on the email, without logging in','woocommerce-dropshippers'),
				array(&$this, 'settings_field_mark_as_shipped'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_can_add_droshipping_fee',
				__('Allow dropshipper shipping fee','woocommerce-dropshippers'),
				array(&$this, 'settings_field_can_add_droshipping_fee'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);

			
			// SHOW/HIDE FIELDS TAB
			add_settings_field(
				'woocommerce_dropshippers_show_prices',
				__('Show prices in dropshipper email','woocommerce-dropshippers'),
				array(&$this, 'settings_field_show_prices'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_text_string',
				__('Show full prices to dropshippers','woocommerce-dropshippers'),
				array(&$this, 'settings_field_input_text'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_can_see_email',
				__('Show customer email to dropshippers','woocommerce-dropshippers'),
				array(&$this, 'settings_field_can_see_email'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_can_see_phone',
				__('Show customer phone number to dropshippers','woocommerce-dropshippers'),
				array(&$this, 'settings_field_can_see_phone'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_can_see_email_shipping',
				__('Show the shipping method on the dropshippers\' email ','woocommerce-dropshippers'),
				array(&$this, 'settings_field_can_see_email_shipping'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_can_see_customer_order_notes',
				__('Allow dropshipper to see Customer Order Notes','woocommerce-dropshippers'),
				array(&$this, 'settings_field_can_see_customer_order_notes'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);


			// PACKING SLIP TAB
			add_settings_field(
				'woocommerce_dropshippers_billing_address',
				__('The billing address that will be shown to dropshippers','woocommerce-dropshippers'),
				array(&$this, 'settings_field_billing_address'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_company_logo',
				__('Shop logo to appear in the Dropshipper packing slip','woocommerce-dropshippers'),
				array(&$this, 'settings_field_company_logo'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_send_pdf',
				__('Send PDF Packing Slip to dropshippers','woocommerce-dropshippers'),
				array(&$this, 'settings_field_send_pdf'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_slip_footer',
				__('Footer text to appear in the Dropshipper packing slip','woocommerce-dropshippers'),
				array(&$this, 'settings_field_slip_footer'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);
			add_settings_field(
				'woocommerce_dropshippers_slip_images',
				__('Show product images in web-based Dropshipper packing slip','woocommerce-dropshippers'),
				array(&$this, 'settings_field_slip_images'),
				'WooCommerce_Dropshippers',
				'WooCommerce_Dropshippers-section'
			);


			// Possibly do additional admin_init tasks
		} // END public static function activate
		
		public function settings_section_WooCommerce_Dropshippers()
		{
			// Think of this as help text for the section.
			//echo 'These settings do things for the WooCommerce Dropshippers.';
		}
		
		public function settings_field_show_prices()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<select id="woocommerce_dropshippers_show_prices" name="woocommerce_dropshippers_options[show_prices]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. ( isset($options['show_prices']) && ($options['show_prices']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_input_text()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<select id="woocommerce_dropshippers_text_string" name="woocommerce_dropshippers_options[text_string]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['text_string']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_billing_address()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<textarea style="width: 300px; height: 150px;" id="woocommerce_dropshippers_billing_address" name="woocommerce_dropshippers_options[billing_address]">' . (isset($options['billing_address'])?$options['billing_address']:'') .'</textarea>'."\n";
		} // END
		public function settings_field_mark_as_shipped()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(empty($options['mark_as_shipped']) ){
				$options['mark_as_shipped'] = 'No';
			}
			echo '<select id="woocommerce_dropshippers_mark_as_shipped" name="woocommerce_dropshippers_options[mark_as_shipped]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['mark_as_shipped']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_can_see_email()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<select id="woocommerce_dropshippers_can_see_email" name="woocommerce_dropshippers_options[can_see_email]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_see_email']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_can_see_phone()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<select id="woocommerce_dropshippers_can_see_phone" name="woocommerce_dropshippers_options[can_see_phone]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_see_phone']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_send_pdf()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(empty($options['send_pdf']) ){
				$options['send_pdf'] = 'Yes';
			}
			echo '<select id="woocommerce_dropshippers_send_pdf" name="woocommerce_dropshippers_options[send_pdf]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['send_pdf']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_company_logo()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<input type="text" id="woocommerce_dropshippers_company_logo" name="woocommerce_dropshippers_options[company_logo]" value="'.(isset($options['company_logo'])?$options['company_logo']:'').'" />'."\n";
			echo '<button id="woocommerce_dropshippers_company_logo_button">'. __('Upload','woocommerce-dropshippers') .'</button>'."\n";
		} // END
		public function settings_field_slip_footer()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(! isset($options['slip_footer'])){ $options['slip_footer'] = ''; }
			echo '<textarea style="width: 300px; height: 150px;" id="woocommerce_dropshippers_slip_footer" name="woocommerce_dropshippers_options[slip_footer]">' . (isset($options['slip_footer'])?$options['slip_footer']:'') .'</textarea>'."\n";
		} // END
		public function settings_field_slip_images()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(! isset($options['slip_images'])){ $options['slip_images'] = 'No'; }
			echo '<select id="woocommerce_dropshippers_slip_images" name="woocommerce_dropshippers_options[slip_images]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['slip_images']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_admin_email()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<input type="email" id="woocommerce_dropshippers_admin_email" name="woocommerce_dropshippers_options[admin_email]" value="'.(isset($options['admin_email'])?$options['admin_email']:'').'" />'."\n";
		} // END
		public function settings_field_admin_email_cc()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<input type="email" id="woocommerce_dropshippers_admin_email_cc" name="woocommerce_dropshippers_options[admin_email_cc]" value="'.(isset($options['admin_email_cc'])?$options['admin_email_cc']:'').'" />'."\n";
		} // END
		public function settings_field_dropshipper_email_bcc()
		{
			$options = get_option('woocommerce_dropshippers_options');
			echo '<input type="email" id="woocommerce_dropshippers_dropshipper_email_bcc" name="woocommerce_dropshippers_options[dropshipper_email_bcc]" value="'.(isset($options['dropshipper_email_bcc'])?$options['dropshipper_email_bcc']:'').'" />'."\n";
		} // END
		public function settings_field_can_see_email_shipping()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(! isset($options['can_see_email_shipping'])){ $options['can_see_email_shipping'] = 'Yes'; }
			echo '<select id="woocommerce_dropshippers_can_see_email_shipping" name="woocommerce_dropshippers_options[can_see_email_shipping]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_see_email_shipping']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_can_put_order_to_completed()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(! isset($options['can_put_order_to_completed'])){ $options['can_put_order_to_completed'] = 'No'; }
			echo '<select id="woocommerce_dropshippers_can_put_order_to_completed" name="woocommerce_dropshippers_options[can_put_order_to_completed]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_put_order_to_completed']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END
		public function settings_field_can_add_droshipping_fee()
		{
			if ( in_array( 'woocommerce-dropshippers-multidrop/woocommerce-dropshippers-multidrop.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				//	¯\(°_o)/¯
			}
			else{
				$options = get_option('woocommerce_dropshippers_options');
				if(! isset($options['can_add_droshipping_fee'])){ $options['can_add_droshipping_fee'] = 'No'; }
				echo '<select id="woocommerce_dropshippers_can_add_droshipping_fee" name="woocommerce_dropshippers_options[can_add_droshipping_fee]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_add_droshipping_fee']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
			}
		} // END
		public function settings_field_can_see_customer_order_notes()
		{
			$options = get_option('woocommerce_dropshippers_options');
			if(! isset($options['can_see_customer_order_notes'])){ $options['can_see_customer_order_notes'] = 'No'; }
			echo '<select id="woocommerce_dropshippers_can_see_customer_order_notes" name="woocommerce_dropshippers_options[can_see_customer_order_notes]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_see_customer_order_notes']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
		} // END

		/**
		 * add a menu
		 */		
		public function add_menu()
		{
			// Add a page to manage this plugin's settings
			if(current_user_can('manage_options')){
				add_submenu_page(
					'WooCommerce_Dropshippers',
					__('WooCommerce Dropshipper Settings','woocommerce-dropshippers'),
					__('Settings','woocommerce-dropshippers'),
					'manage_options',
					'WooCommerce_Dropshippers',
					array(&$this, 'plugin_settings_page')
				);
				add_submenu_page(
					'WooCommerce_Dropshippers',
					__('WooCommerce Dropshipper Bulk Assign','woocommerce-dropshippers'),
					__('Bulk Assign','woocommerce-dropshippers'),
					'manage_woocommerce',
					'dropshippers_bulk_assign',
					array(&$this, 'bulk_assign_page')
				);
				add_submenu_page(
					'WooCommerce_Dropshippers',
					__('WooCommerce Dropshipper Bulk Price','woocommerce-dropshippers'),
					__('Bulk Price','woocommerce-dropshippers'),
					'manage_woocommerce',
					'dropshippers_bulk_price',
					array(&$this, 'bulk_price_page')
				);
			}
			else{
				remove_submenu_page( 'WooCommerce_Dropshippers', 'WooCommerce_Dropshippers' );
				add_submenu_page(
					'WooCommerce_Dropshippers',
					__('WooCommerce Dropshipper Bulk Assign','woocommerce-dropshippers'),
					__('Bulk Assign','woocommerce-dropshippers'),
					'manage_woocommerce',
					'WooCommerce_Dropshippers',
					array(&$this, 'bulk_assign_page')
				);
			}
		} // END public function add_menu()
	
		/**
		 * Menu Callback
		 */		
		public function plugin_settings_page(){
			if(!current_user_can('manage_options')){
				wp_die(__('You do not have sufficient permissions to access this pagez.'));
			}
			?>
			<style type="text/css">
				.settings-tabs nav.articnet-nav-tab-wrapper{
					border-bottom: 1px solid #ccc;
					margin: 1.5em 0 1em;
				}
				.settings-tabs nav.articnet-nav-tab-wrapper a{
					box-shadow: none;
					-webkit-border-top-left-radius: 6px;
					-webkit-border-top-right-radius: 6px;
					-moz-border-radius-topleft: 6px;
					-moz-border-radius-topright: 6px;
					border-top-left-radius: 6px;
					border-top-right-radius: 6px;
				}
				.dropshipper-setting-tab-show-hide, .dropshipper-setting-tab-packing-slip {
					display: none;
				}
			</style>
			<div class="dropshippers-header" style="margin:0; padding:0; width:100%; height:100px; background: url('<?php echo plugins_url( 'images/headerbg.png', __FILE__ ) ?>'); background-repeat: repeat-x;">
				<img src="<?php echo plugins_url( 'images/woocommerce-dropshippers-header.png', __FILE__ ) ?>" style="margin:0; padding:0; width:auto; height:100px;">
			</div>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2><?php echo __('WooCommerce Dropshipper Settings','woocommerce-dropshippers'); ?></h2>
				<div class="settings-tabs">
					<nav class="nav-tab-wrapper articnet-nav-tab-wrapper">
						<a class="nav-tab nav-tab-active" href="#" onclick="return false;" open-tab="dropshipper-setting-tab-general"><?php echo __('General','woocommerce-dropshippers'); ?></a>
						<a class="nav-tab " href="#" onclick="return false;" open-tab="dropshipper-setting-tab-show-hide"><?php echo __('Show/Hide Fields','woocommerce-dropshippers'); ?></a>
						<a class="nav-tab " href="#" onclick="return false;" open-tab="dropshipper-setting-tab-packing-slip"><?php echo __('Packing Slip','woocommerce-dropshippers'); ?></a>
					</nav>
				</div>
				<form method="post" action="options.php" class="woocommerce-dropshippers-settings-form">
					<?php
						settings_fields( 'WooCommerce_Dropshippers' );
						do_settings_sections( 'WooCommerce_Dropshippers' );
					?>
					<?php submit_button(__("Save Settings",'woocommerce-dropshippers')); ?>
				</form>
			</div>
			<script type="text/javascript">
				var woocommerce_dropshippers_throbber_url = '<?php echo admin_url('images/loading.gif') ?>';
				jQuery('.woocommerce-dropshippers-settings-form .form-table tr').each(function(j,k){
					if(j>=0 && j<=5){
						jQuery(k).addClass('dropshipper-setting').addClass('dropshipper-setting-tab-general');
						<?php if ( in_array( 'woocommerce-dropshippers-multidrop/woocommerce-dropshippers-multidrop.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) : ?>
						if(j==5){
							jQuery(k).empty();
						}
						<?php endif; ?>
					}
					else if(j>=6 && j<=11){
						jQuery(k).addClass('dropshipper-setting').addClass('dropshipper-setting-tab-show-hide');
					}
					else if(j>=12 && j<=16){
						jQuery(k).addClass('dropshipper-setting').addClass('dropshipper-setting-tab-packing-slip');
					}
				});

				jQuery('.settings-tabs a').click(function(){
					var tab = jQuery(this).attr('open-tab');
					jQuery('.settings-tabs a').removeClass('nav-tab-active');
					jQuery(this).addClass('nav-tab-active');

					jQuery('.dropshipper-setting').hide();
					jQuery('.'+tab).show();
				});
			</script>
			<?php
		} // END public function plugin_settings_page()


		/**
		 * bulk assign page Callback
		 */	 
		public function bulk_assign_page(){
			if(!current_user_can('manage_woocommerce')){
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
			?>
			<style type="text/css">
			#dropspinner{
				visibility: visible;
				float: none;
				display: none;
			}
			</style>
			<div class="dropshippers-header" style="margin:0; padding:0; width:100%; height:100px; background: url('<?php echo plugins_url( 'images/headerbg.png', __FILE__ ) ?>'); background-repeat: repeat-x;">
				<img src="<?php echo plugins_url( 'images/woocommerce-dropshippers-header.png', __FILE__ ) ?>" style="margin:0; padding:0; width:auto; height:100px;">
			</div>
			<div class="wrap">
				<?php screen_icon(); ?>
				<div id="bulk-updated" class="updated" style="display:none"><p><?php echo __('Dropshipper updated', 'woocommerce-dropshippers'); ?></p></div>
				<div id="bulk-error" class="error" style="display:none"><p><?php echo __('An error as occurred', 'woocommerce-dropshippers'); ?></p></div>
				<h2><?php echo __('Dropshippers Bulk Assign','woocommerce-dropshippers'); ?></h2>
				<hr>
				<h3><?php echo __('Dropshipper','woocommerce-dropshippers'); ?></h3>
				<select id="dropshippers-users" name="dropshippers-users">
				<?php
					$dropshipperz = get_users('role=dropshipper');
					foreach ($dropshipperz as $drop_usr) {
						echo '<option value="'.htmlspecialchars($drop_usr->user_login).'">'.htmlspecialchars($drop_usr->user_login).'</option>';
					}
				?>
				</select>
				<br>
				<h3><?php echo __('Product Category','woocommerce-dropshippers'); ?></h3>
				<select id="dropshippers-categories">
				<?php
					$args = array(
						'hide_empty' => false
					);
					$product_categories = get_terms( 'product_cat', $args );
					foreach ($product_categories as $cat) {
						echo '<option value="'.$cat->slug.'">'.htmlspecialchars($cat->name).'</option>';
					}
				?>
				</select>
				<br><br>

				<button id="dropassign" onclick="dropshippers_assign(); return false;" class="button-primary"><?php echo __('Assign','woocommerce-dropshippers'); ?></button> <div id="dropspinner" class="spinner"></div>

				<script type="text/javascript">
					function dropshippers_assign(){
						var user = jQuery('#dropshippers-users').val();
						var cat = jQuery('#dropshippers-categories').val();
						js_dropshippers_bulk_assign(user, cat);
					}
				</script>
			</div>
			<?php
		} // END public function bulk_assign_page()

		/**
		 * bulk price page Callback
		 */	 
		public function bulk_price_page(){
			if(!current_user_can('manage_woocommerce')){
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
			?>
			<style type="text/css">
			#dropspinner{
				visibility: visible;
				float: none;
				display: none;
			}
			</style>
			<div class="dropshippers-header" style="margin:0; padding:0; width:100%; height:100px; background: url('<?php echo plugins_url( 'images/headerbg.png', __FILE__ ) ?>'); background-repeat: repeat-x;">
				<img src="<?php echo plugins_url( 'images/woocommerce-dropshippers-header.png', __FILE__ ) ?>" style="margin:0; padding:0; width:auto; height:100px;">
			</div>
			<div class="wrap">
				<?php screen_icon(); ?>
				<div id="bulk-updated" class="updated" style="display:none"><p><?php echo __('Dropshipper updated', 'woocommerce-dropshippers'); ?></p></div>
				<div id="bulk-error" class="error" style="display:none"><p><?php echo __('An error as occurred', 'woocommerce-dropshippers'); ?></p></div>
				<h2><?php echo __('Dropshippers Bulk Price','woocommerce-dropshippers'); ?></h2>
				<hr>
				<h3><?php echo __('Dropshipper','woocommerce-dropshippers'); ?></h3>
				<select id="dropshippers-users" name="dropshippers-users">
				<?php
					$dropshipperz = get_users('role=dropshipper');
					foreach ($dropshipperz as $drop_usr) {
						echo '<option value="'.htmlspecialchars($drop_usr->user_login).'">'.htmlspecialchars($drop_usr->user_login).'</option>';
					}
				?>
				</select>
				<br>
				<h3><?php echo __('Dropshipper Price Operation','woocommerce-dropshippers'); ?></h3>
				<p class="description"><?php echo __('By choosing <strong>+</strong>, the Dropshipper Price will be calculated subtracting the Value field from the Product regular price.', 'woocommerce-dropshippers'); ?></p>
				<p class="description"><?php echo __('By choosing <strong>%</strong>, the Dropshipper Price will be the percentage of the Product regular price set into the Value field.', 'woocommerce-dropshippers'); ?></p>
				<select id="dropshippers-operation">
					<option value="+">+</option>
					<option value="%">%</option>
				</select>
				<br>
				<h3><?php echo __('Value','woocommerce-dropshippers'); ?></h3>
				<input type="number" id="dropshippers-value" value="0" min="0" />
				<br><br>

				<button id="dropassign" onclick="dropshippers_calculate(); return false;" class="button-primary">Calculate</button> <div id="dropspinner" class="spinner"></div>

				<script type="text/javascript">
					function dropshippers_calculate(){
						var user = jQuery('#dropshippers-users').val();
						var op = jQuery('#dropshippers-operation').val();
						var value = jQuery('#dropshippers-value').val();
						if(value < 0){
							alert('Value can\'t be negative!');
						}
						else{
							js_dropshippers_bulk_price(user, op, value);
						}
						
					}
				</script>
			</div>
			<?php
		} // END public function bulk_assign_page()


	} // END class WooCommerce_Dropshippers_Settings
} // END if(!class_exists('WooCommerce_Dropshippers_Settings'))

add_action( 'wp_ajax_woocommerce_dropshippers_get_attachment_path', 'woocommerce_dropshippers_get_attachment_path_callback' );
function woocommerce_dropshippers_get_attachment_path_callback() {
	$attachment_id = intval( $_POST['att_id'] );
	$fullsize_path = get_attached_file( $attachment_id ); // Full path
	echo $fullsize_path;
	wp_die(); // this is required to terminate immediately and return a proper response
}