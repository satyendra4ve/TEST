<?php
/**
 * This file is for license panel. Include this file if license is not validated.   
 * If license is validated then show you setting page.
 * Otherwise show the same file.
 * 
 */ 
global $wp_version;
global $current_user; 

?>
<div style="padding: 10px">
	<h3><?php _e('Woocommerce Refund & Exchange With RMA','woocommerce-refund-and-exchange');?></h3>
	<hr/>
	<div style="text-align: justify; float: left; width: 85%; font-size: 16px; line-height: 25px; padding-right: 4%;">
		<?php 
		_e('This is the License Activation Panel. After purchasing extention from Codecanyon you will get the purchase code of this plugin. Please verify your purchase below so that you can use feature of this extention.','woocommerce-refund-and-exchange');
		?>

	</div>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th class="titledesc" scope="row">
					<label><?php _e('Enter Purchase Code','woocommerce-refund-and-exchange');?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<input type="text" id="ced_rnx_license_key" class="input-text regular-input" placeholder="Enter your Purchase code here...">
						<input type="button" value="Validate" class="button-primary" id="ced_rnx_license_save">
						<img class="loading_image" src="<?php echo CED_REFUND_N_EXCHANGE_URL;?>assets/images/loader.gif" style="height: 28px;vertical-align: middle;display:none;">
						<b class="licennse_notification"></b>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
</div>