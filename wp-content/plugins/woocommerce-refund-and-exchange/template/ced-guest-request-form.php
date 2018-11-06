<?php
$current_user_id = get_current_user_id();
if($current_user_id > 0)
{
	$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
	$myaccount_page_url = get_permalink( $myaccount_page );
	wp_redirect($myaccount_page_url);
	exit;
}	

get_header( 'shop' );

/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
*/
do_action( 'woocommerce_before_main_content' );

/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */

$ced_main_wrapper_class = get_option('ced_rnx_return_exchange_class');
$ced_child_wrapper_class = get_option('ced_rnx_return_exchange_child_class');
?>
<div class="woocommerce woocommerce-account <?php echo $ced_main_wrapper_class;?>">
	<div class="<?php echo $ced_child_wrapper_class;?>">
		<div id="ced_rnx_guest_request_form_wrapper">
			<h2><?php 
			$page_head = get_option( 'ced_rnx_return_exchange_page_heading_text' , 'Refund/Exchange Request Form' );
			if ($page_head == '') {
				$page_head =  __('Refund/Exchange Request Form','woocommerce-refund-and-exchange' );
			}
			$return_product_form = $page_head;
			echo apply_filters('ced_rnx_return_product_form', $return_product_form);
			?>
			</h2>
			<?php 
			if(isset($_SESSION['ced_rnx_notification']) && !empty($_SESSION['ced_rnx_notification']))
			{
				?>
				<ul class="woocommerce-error">
						<li><strong><?php _e('ERROR','woocommerce-refund-and-exchange'); ?></strong>: <?php echo $_SESSION['ced_rnx_notification'];?></li>
				</ul>
				<?php 
				unset($_SESSION['ced_rnx_notification']);
			}
			?>
			<form class="login ced_rnx_guest_form" method="post">
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="username"><?php _e('Enter Order Id','woocommerce-refund-and-exchange' );?><span class="required"> *</span></label>
					<input type="text" id="order_id" name="order_id" class="woocommerce-Input woocommerce-Input--text input-text">
				</p>
				
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="username"><?php _e('Enter Order Email','woocommerce-refund-and-exchange' );?><span class="required"> *</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="order_email" id="order_email" value="">
				</p>
				
				<p class="form-row">
					<input type="submit" value="<?php _e('Submit','woocommerce-refund-and-exchange'); ?>" name="ced_rnx_order_id_submit" class="woocommerce-Button button">
				</p>
			</form>
		</div>
	</div>
</div>
<?php 
do_action( 'woocommerce_after_main_content' );

/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
*/
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
?>