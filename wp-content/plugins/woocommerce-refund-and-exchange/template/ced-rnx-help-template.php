<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(isset($_POST['ced_rnx_send_suggestion']))
{
	$ced_rnx_mail_subject = isset($_POST['ced_rnx_mail_subject']) ? $_POST['ced_rnx_mail_subject'] : 'WooCommerce Refund & Exchange Client Needed Help';
	$ced_rnx_mail_content = isset($_POST['ced_rnx_mail_content']) ? $_POST['ced_rnx_mail_subject']  : 'This is default messege please contact me fast so i will feel free from this stress.';

	$ced_rnx_admin_email = get_option('admin_email');

	$status = wp_mail('support@makewebbetter.com',$ced_rnx_mail_subject,$ced_rnx_mail_content);
	if($status)
	{
		$messege = __('Your request is submitted successfully. Our team will respond as soon as possible.','woocommerce-refund-and-exchange');
		$class = "ced_rnx_mail_success_messege";
	}
	else
	{
		$messege = __('Your request is not submitted. Please try again.','woocommerce-refund-and-exchange');
		$class = "ced_rnx_mail_unsuccess_messege";
	}
}

?>
<div class="ced_rnx_help_wrapper ced_rnx_help_second_wrapper">
	<ul class="ced_rnx_help-link-wrap">
		<li>
			<a class="ced_rnx_help-link" href="http://docs.makewebbetter.com/woocommerce-refund-exchange/" target="_blank"><?php _e('Documentation','woocommerce-refund-and-exchange'); ?></a>
		</li>
		<li>
			<a class="ced_rnx_help-link" href="https://makewebbetter.freshdesk.com/support/solutions/folders/26000053405" target="_blank"><?php _e('FAQ','woocommerce-refund-and-exchange'); ?></a>
		</li>
		
	</ul>
	<?php if(isset($messege)){
		?><div class="<?php echo $class ; ?>"> <?php echo $messege; ?></div><?php 
	} ?>
	<form method="POST" action="">
		<div class="ced-rnx-suggestion">
			<h2><?php _e('Suggestion or Query','woocommerce-refund-and-exchange'); ?></h2>
			<div class="ced_rnx_help_input-wrap">
				<label><?php _e('Enter Suggestion or Query title here','woocommerce-refund-and-exchange'); ?></label>
				<div class="ced_rnx_help_input">
					<input class="ced_rnx_help_form-control text-field" type="text" name='ced_rnx_mail_subject'>
				</div>
			</div>
			<div class="ced_rnx_help_input-wrap">
				<label><?php _e('Enter Suggestion or Query detail here','woocommerce-refund-and-exchange'); ?></label>
				<div class="ced_rnx_help_input">
					<textarea  class="ced_rnx_help_form-control" name="ced_rnx_mail_content"></textarea>
				</div>
			</div>
			<div class="ced_rnx_hekp-sned-suggetion-button-wrap">
				<input type="submit" name="ced_rnx_send_suggestion" value="<?php _e('Send Suggestion','woocommerce-refund-and-exchange'); ?>" class="button-primary ced_rnx_hekp-sned-suggetion-button">
			</div>
		</div>
	</form>
</div>