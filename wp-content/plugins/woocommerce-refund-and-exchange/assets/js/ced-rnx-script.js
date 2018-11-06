function ced_rnx_currency_seprator(price)
{
	price = price.toFixed(2);
	price = price.replace('.',global_rnx.price_decimal_separator);
	// price = price.replace(',',global_rnx.price_thousand_separator);
	return price;
}
function ced_rnx_total()
{
	var total = 0;	
	var checkall = true;	
	jQuery("#ced_rnx_total_refund_amount").parent('td').siblings('th').html(global_rnx.ced_rnx_price_deduct_message);
	jQuery(".ced_rnx_return_column").each(function(){
		if(jQuery(this).find("td:eq(0)").children('.ced_rnx_return_product').is(':checked')){
			var product_price = jQuery(this).find("td:eq(0)").children('.ced_rnx_return_product').val();
			var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_return_product_qty').val();
			var product_total = product_price * product_qty;
			var this_obj = this;
			jQuery('.ced_rnx_return_notification_checkbox').show();
			jQuery(this).find("td:eq(3)").children('.ced_rnx_formatted_price').html(ced_rnx_currency_seprator(product_total));
			var order_id = jQuery('#ced_rnx_return_request_form').attr('data-orderid');
			jQuery.ajax({
				url 	: global_rnx.ajaxurl,
				type 	: "POST",
				cache 	: false,
				async 	: false,
				data 	: { 
					action:'ced_rnx_calculate_price_deduct_on_return',
					product_total:product_total, 
					product_qty:product_qty, 
					order_id:order_id 
				},
				success: function(response) 
				{
					product_total = response;
					product_total = parseFloat(product_total);
					jQuery(this_obj).find("td:eq(3)").children('.ced_rnx_formatted_price').html(ced_rnx_currency_seprator(product_total));
					jQuery('.ced_rnx_return_notification_checkbox').hide();
					jQuery("#ced_rnx_total_refund_amount").parent('td').siblings('th').html(global_rnx.ced_rnx_price_deduct_message);
				}
			});
			total += product_total;
		}
		else
		{
			checkall = false;
		}	
	});
	
	if(checkall)
	{
		jQuery('.ced_rnx_return_product_all').attr('checked', true);
	}	
	else
	{
		jQuery('.ced_rnx_return_product_all').attr('checked', false);
	}	
	jQuery("#ced_rnx_total_refund_amount .ced_rnx_formatted_price").html(ced_rnx_currency_seprator(total));
	return total;
}

var total = 0;	
var extra_amount = 0;
function ced_rnx_exchange_total()
{
	total = 0;	
	var checkall = true;	
	jQuery(".ced_rnx_exchange_column").each(function(){
		if(jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').is(':checked')){
			var product_price = jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').val();
			var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_exchange_product_qty').val();
			var product_total = product_price * product_qty;
			jQuery(this).find("td:eq(3)").children('.ced_rnx_formatted_price').html(ced_rnx_currency_seprator(product_total));
			total += product_total;
		}
		else
		{
			checkall = false;
		}	
	});
	
	if(checkall)
	{
		jQuery('.ced_rnx_exchange_product_all').attr('checked', true);
	}	
	else
	{
		jQuery('.ced_rnx_exchange_product_all').attr('checked', false);
	}	
	var selected_product = {};
	var count = 0;
	var orderid = jQuery("#ced_rnx_exchange_request_order").val();
	jQuery(".ced_rnx_exchange_column").each(function(){
		if(jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').is(':checked')){
			var product_info = {};
			var variation_id = jQuery(this).data("variationid");
			var product_id = jQuery(this).data("productid");
			var item_id = jQuery(this).data("itemid");
			var product_price = jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').val();
			var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_exchange_product_qty').val();
			product_info['product_id'] = product_id;
			product_info['variation_id'] = variation_id;
			product_info['item_id'] = item_id;
			product_info['price'] = product_price;
			product_info['qty'] = product_qty;
			selected_product[count] = product_info;
			count++;
			// console.log('session');
		}
	});
	var data = {	
		action	:'ced_rnx_exchange_products',
		products: selected_product,
		orderid : orderid,
		security_check	:	global_rnx.ced_rnx_nonce	
	};
	var mwb_check_coupon;
	jQuery('.ced_rnx_return_notification_checkbox').show();
	jQuery.ajax({
		url: global_rnx.ajaxurl, 
		type: "POST",  
		data: data,
		async: false,
		dataType :'json',	
		success: function(response) 
		{
			jQuery('.ced_rnx_return_notification_checkbox').hide();
			jQuery(".ced_rnx_return_notification").html(response.msg);
			mwb_check_coupon = 0;
		}
	});
	
	jQuery("#ced_rnx_total_exchange_amount .ced_rnx_formatted_price").html(ced_rnx_currency_seprator(total));
	
	var exchanged_amount = jQuery("#ced_rnx_exchanged_total").val();
	extra_amount = 0;
	if(exchanged_amount >= ( total + mwb_check_coupon ) )
	{
		extra_amount = exchanged_amount - ( total + mwb_check_coupon );
		jQuery('#ced_rnx_exchange_extra_amount i').html(global_rnx.extra_amount_msg);
	}
	else
	{
		if( mwb_check_coupon > exchanged_amount )
		{
			exchanged_amount = 0;
		}
		else
		{
			exchanged_amount = exchanged_amount - mwb_check_coupon;
		}
		extra_amount =  total  -  exchanged_amount;
		jQuery('#ced_rnx_exchange_extra_amount i').html(global_rnx.left_amount_msg);
	}
	jQuery(".ced_rnx_exchange_extra_amount .ced_rnx_formatted_price").html(ced_rnx_currency_seprator(extra_amount));
	return total;
}

var files = {};
jQuery(document).ready(function(){
	if (global_rnx.exchange_session == 1) 
	{
		jQuery(document).on('change','.variations .value select',function(){
			var add_to_cart_btn_class = jQuery( '.single_add_to_cart_button' ).attr('class');
			if(add_to_cart_btn_class.match('wc-variation-is-unavailable') == 'wc-variation-is-unavailable')	
			{
				jQuery('.ced_rnx_add_to_exchanged_detail_variable').attr('disabled','disabled');
				jQuery('.ced_rnx_add_to_exchanged_detail_variable').hide();
			}
			else
			{
				jQuery('.ced_rnx_add_to_exchanged_detail_variable').removeAttr('disabled');
				jQuery('.ced_rnx_add_to_exchanged_detail_variable').show()
			}

		});
		if(global_rnx.ced_rnx_add_to_cart_enable != 'yes')
		{
			jQuery( '.single_add_to_cart_button' ).hide();
		}
	}
	
	jQuery( '.ced_rnx_cancel_order' ).each( function(){
		// console.log(jQuery( this ).attr( 'href' ).split( 'http://' )[1]);
		jQuery( this ).attr( 'data-order_id', jQuery( this ).attr( 'href' ).split( 'http://' )[1] );
		jQuery( this ).attr( 'href', 'javascript:void(0);' ); 
	});

	jQuery(document).on('click' , '.ced_rnx_cancel_order' , function(){
		jQuery( this ).prop("disabled",true);
		var order_id = jQuery(this).attr('data-order_id');
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: { action : 'ced_rnx_cancel_customer_order' , order_id : order_id , security_check	:	global_rnx.ced_rnx_nonce },
			success: function(respond)   
			{
				window.location.href = respond;
			}
		});
	});
	
	/***************************************************** Return Request code start ********************************************************/
	ced_rnx_total();
	
	//Check all
	jQuery(document).on('change' , '.ced_rnx_return_product_all',function(){
		if(jQuery(this).is(':checked')){
			jQuery(".ced_rnx_return_product").each(function(){
				jQuery(this).attr('checked', true);
			});
		}
		else{
			jQuery(".ced_rnx_return_product").each(function(){
				jQuery(this).attr('checked', false);
			});
		}	
		ced_rnx_total();
	});
	
	//Check one by one
	jQuery(document).on('change','.ced_rnx_return_product',function(){
		ced_rnx_total();
	});
	
	//Update qty
	jQuery(".ced_rnx_return_product_qty").change(function(){
		ced_rnx_total();
	});
	
	//Add more files to attachment
	jQuery(".ced_rnx_return_request_morefiles").click(function(){
		var html = '<br/><input type="file" class="input-text ced_rnx_return_request_files" name="ced_rnx_return_request_files[]">';
		jQuery("#ced_rnx_return_request_files").append(html);
	});
	
	jQuery('#ced_rnx_coupon_regenertor').click(function(){
		var id = jQuery(this).data('id');
		jQuery('.regenerate_coupon_code_image').css('display' , 'inline-block');
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: { action : 'ced_rnx_coupon_regenertor' , id : id , security_check	:	global_rnx.ced_rnx_nonce },
			success: function(respond)   
			{
				var response = jQuery.parseJSON( respond );
				var wallet_regenraton = '';
				wallet_regenraton = '<b>'+global_rnx.wallet_msg+':<br>'+response.coupon_code_text+': '+response.coupon_code+'<br>'+response.wallet_amount_text+': <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'+response.currency_symbol+'</span>'+response.coupon_price+'</span> </b>';
				jQuery('.ced_rnx_wallet').html(wallet_regenraton);
				jQuery('.regenerate_coupon_code_image').css('display' , 'none');
			}
		});
	});
	//Pick all attached files
	jQuery("#ced_rnx_return_request_files").on('change',".ced_rnx_return_request_files",function(e){
		files = {};
		var file_type = e.target.files;
		if(typeof file_type[0]['type'] != 'undefined')
		{
			var type = file_type[0]['type'];
		}	
		if(type == 'image/png' || type == 'image/jpg' || type == 'image/jpeg')
		{
		}	
		else
		{
			jQuery(this).val("");
		}	
		
		var count = 0;
		jQuery(".ced_rnx_return_request_files").each(function(){
			var filename = jQuery(this).val();
			files[count] = e.target.files;
			count++;
		});
		
	});
	
	//Submit Retun Request form
	jQuery("#ced_rnx_return_request_form").on('submit', function(e){
		e.preventDefault();	
		var orderid = jQuery(this).data('orderid');
		var refund_amount = ced_rnx_total();
		var alerthtml = '';
		var selected_product = {};
		var count = 0;
		
		if(refund_amount == 0)
		{
			alerthtml += '<li>'+global_rnx.select_product_msg+'</li>';
		}
		var rr_subject = jQuery("#ced_rnx_return_request_subject").val();

		if(rr_subject == '' || rr_subject == null)
		{
			rr_subject = jQuery("#ced_rnx_return_request_subject_text").val();
			if(rr_subject == '' || rr_subject == null)
			{
				alerthtml += '<li>'+global_rnx.return_subject_msg+'</li>';
			}
		}
		jQuery(".ced_rnx_return_column").each(function(){
			if(jQuery(this).find("td:eq(0)").children('.ced_rnx_return_product').is(':checked')){
				var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_return_product_qty').val();
				var product_q = jQuery(this).find("td:eq(3)").children('#quanty').val();
				if(product_qty>product_q)
				{
					alerthtml += '<li>'+global_rnx.correct_quantity+'</li>';

				}
			}
		});
		var rr_reason = jQuery(".ced_rnx_return_request_reason").val();
		
		if(rr_reason == '' || rr_reason == null)
		{
			alerthtml += '<li>'+global_rnx.return_reason_msg+'</li>';
		}
		else
		{
			r_reason = rr_reason.trim();
			if(r_reason == '' || r_reason == null)
			{
				alerthtml += '<li>'+global_rnx.return_reason_msg+'</li>';
			}
		}	
		
		if(alerthtml != '')
		{
			jQuery("#ced-return-alert").show();
			jQuery("#ced-return-alert").html(alerthtml);
			jQuery('html, body').animate({
				scrollTop: jQuery("#ced_rnx_return_request_container").offset().top
			}, 800);
			return false;
		}
		else
		{
			jQuery("#ced-return-alert").hide();
			jQuery("#ced-return-alert").html(alerthtml);
		}	

		jQuery(".ced_rnx_return_column").each(function(){
			if(jQuery(this).find("td:eq(0)").children('.ced_rnx_return_product').is(':checked')){
				var product_info = {};
				var variation_id = jQuery(this).data("variationid");
				var product_id = jQuery(this).data("productid");
				var item_id = jQuery(this).data("itemid");
				var product_price = jQuery(this).find("td:eq(0)").children('.ced_rnx_return_product').val();
				var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_return_product_qty').val();
				product_info['product_id'] = product_id;
				product_info['variation_id'] = variation_id;
				product_info['item_id'] = item_id;
				product_info['price'] = product_price;
				product_info['qty'] = product_qty;
				selected_product[count] = product_info;
				count++;
			}
		});


		var ced_rnx_refund_method = jQuery('input[name=ced_rnx_refund_method]:checked').val();

		var data = {	
			action	:'ced_rnx_return_product_info',
			products: selected_product,
			amount	: refund_amount,
			subject	: rr_subject,
			reason	: rr_reason,
			orderid : orderid,
			refund_method : ced_rnx_refund_method,
			security_check	:	global_rnx.ced_rnx_nonce	
		}
		
		jQuery(".ced_rnx_return_notification").show();
		
		//Upload attached files

		var formData = new FormData(this);
		formData.append('action', 'ced_rnx_return_upload_files');
		jQuery("body").css("cursor", "progress");
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: formData, 
			contentType: false,       
			cache: false,             
			processData:false,       
			success: function(respond)   
			{
				//Send return request
				
				jQuery.ajax({
					url: global_rnx.ajaxurl, 
					type: "POST",  
					data: data,
					dataType :'json',	
					success: function(response) 
					{
						jQuery(".ced_rnx_return_notification").hide();
						
						jQuery("#ced-return-alert").html(response.msg);
						jQuery("#ced-return-alert").removeClass('woocommerce-error');
						jQuery("#ced-return-alert").addClass("woocommerce-message");
						jQuery("#ced-return-alert").css("color", "#8FAE1B");
						jQuery("#ced-return-alert").show();
						jQuery('html, body').animate({
							scrollTop: jQuery("#ced_rnx_return_request_container").offset().top
						}, 800);
						
						if(typeof response.auto_accept != 'undefined')
						{
							if(global_rnx.auto_accept == 'yes' && response.auto_accept == true)
							{
								var fullDate = new Date()
								var twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);
								var date = fullDate.getDate() + "-" + twoDigitMonth + "-" + fullDate.getFullYear();
								var auto_accept_data = {
									action:'ced_return_req_approve',
									orderid:orderid,
									date:date,
									autoaccept:true,
									security_check	:	global_rnx.ced_rnx_nonce	
								}
								
								jQuery.ajax({
									url: global_rnx.ajaxurl, 
									type: "POST",  
									data: auto_accept_data,
									dataType :'json',	
									success: function(response) 
									{
										window.setTimeout(function() {
											window.location.href = global_rnx.myaccount_url;
										}, 1000);
									}
								});
							}
							else
							{
								window.setTimeout(function() {
									window.location.href = global_rnx.myaccount_url;
								}, 1000);
							}	
						}
						else
						{
							window.setTimeout(function() {
								window.location.href = global_rnx.myaccount_url;
							}, 1000);
						}	
					}
				});
			}
		});
	});

/***************************************************** Return Request Code End ********************************************************/

/***************************************************** ExchaNge Request code start ********************************************************/

	//ced_rnx_exchange_total();
	
	//Check all
	jQuery(".ced_rnx_exchange_product_all").click(function(){
		if(jQuery(this).is(':checked')){
			jQuery(".ced_rnx_exchange_product").each(function(){
				jQuery(this).attr('checked', true);
			});
		}
		else{
			jQuery(".ced_rnx_exchange_product").each(function(){
				jQuery(this).attr('checked', false);
			});
		}
		ced_rnx_exchange_total();
	});
	
	//Check One by One
	jQuery(".ced_rnx_exchange_product").click(function(){
		ced_rnx_exchange_total();
	});
	
	//Update product qty
	jQuery(".ced_rnx_exchange_product_qty").change(function(){
		ced_rnx_exchange_total();
	});
	
	/***************************************************** ExchaNge Request code End ********************************************************/
	
	/************************************************** Add Product to exchange *****************************************************/
	
	ced_rnx_exchange_total();
	
	jQuery(document).on('click' , '.ced_rnx_ajax_add_to_exchange' , function(){

		var current = jQuery(this);
		jQuery(this).addClass('loading');
		var product_id = jQuery(this).data('product_id');
		var product_sku = jQuery(this).data('product_sku');
		var quantity = jQuery(this).data('quantity');
		var price = jQuery(this).data('price');
		var product_info = {};
		product_info['id'] = product_id;
		product_info['qty'] = quantity;
		product_info['sku'] = product_sku;
		product_info['price'] = price;
		
		var data = {	
			action	:'ced_rnx_add_to_exchange',
			products: product_info,
			security_check	:	global_rnx.ced_rnx_nonce	
		}
		
		//Add Exchange Product
		
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{

				current.removeClass('loading');
				current.addClass('added');
				current.parent().html('<a data-price="'+price+'" data-quantity="'+quantity+'" data-product_id="'+product_id+'" data-product_sku="'+product_sku+'" class="button ced_rnx_ajax_add_to_exchange" tabindex="0">'+global_rnx.exchange_text+'</a><a href="'+response.url+'">'+response.message+'</a>');
			}
		});
	});
	
	jQuery(".ced_rnx_exchnaged_product_remove").click(function(){
		var current = jQuery(this);
		var orderid = jQuery("#ced_rnx_exchange_request_order").val();
		var id = jQuery(this).data("key");
		var data = {	
			action	:'ced_rnx_exchnaged_product_remove',
			id: id,
			orderid : orderid,
			security_check	:	global_rnx.ced_rnx_nonce	
		}
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				current.parent().remove();
				var rowCount = jQuery('.ced_rnx_exchanged_products >tbody >tr').length;
				if(rowCount <= 0)
				{
					jQuery('.ced_rnx_exchanged_products').remove();
				}
				jQuery("#ced_rnx_exchanged_total").val(response.total_price);
				jQuery("#ced_rnx_exchanged_total_show .ced_rnx_formatted_price").html(response.total_price.toFixed(2));
				ced_rnx_exchange_total();
			}
		});
	});
	
	jQuery(document).on('click', '.ced_rnx_add_to_exchanged_detail' , function(){

		var current = jQuery(this);
		jQuery(this).addClass('loading');
		var product_id = jQuery(this).data('product_id');
		var product_sku = jQuery(this).data('product_sku');
		var quantity = jQuery(".qty").val();
		var price = jQuery(this).data('price');
		var variations = {};
		jQuery(".variations select").each(function(){
			var name = jQuery(this).data("attribute_name");
			var val = jQuery(this).val();
			variations[name] = val;
		});
		
		var grouped = {};
		jQuery(".group_table tr").each(function(){
			quantity = jQuery(this).find("td:eq(0)").children().children().val();
			id = jQuery(this).find("td:eq(0)").children().children().attr('name');
			id = id.match(/\d+/);
			id = id[0];
			grouped[id] = quantity;
			
		});
		
		var product_info = {};
		product_info['id'] = product_id;
		product_info['qty'] = quantity;
		product_info['sku'] = product_sku;
		product_info['price'] = price;
		product_info['variations'] = variations;
		product_info['grouped'] = grouped;
		
		var data = {	
			action	:'ced_rnx_add_to_exchange',
			products: product_info,
			security_check	:	global_rnx.ced_rnx_nonce	
		}
		
		//Add Exchange Product
		
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{

				current.removeClass('loading');
				current.addClass('added');
				current.parent().html('<button data-price="'+price+'" data-quantity="'+quantity+'" data-product_id="'+product_id+'" data-product_sku="'+product_sku+'" class="ced_rnx_add_to_exchanged_detail button alt added" tabindex="0">'+global_rnx.exchange_text+'</button><a href="'+response.url+'">'+response.message+'</a>');
			}
		});
	});
	
	jQuery(".ced_rnx_exchange_request_submit").click(function(){
		var orderid = jQuery("#ced_rnx_exchange_request_order").val();
		var total = ced_rnx_exchange_total();
		var alerthtml = '';
		var selected_product = {};
		var count = 0;
		var exchange_amount = jQuery("#ced_rnx_exchanged_total").val();
		extra_amount = exchange_amount - total;
		var mwb_rnx_selected = false;
		jQuery(".ced_rnx_exchange_column").each(function(){

			if(jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').is(':checked'))
			{
				mwb_rnx_selected = true;
			}	
		});
		
		if(mwb_rnx_selected == false)
		{
			alerthtml += '<li>'+global_rnx.select_product_msg_exchange +'</li>';
		}
		else
		{
			if(exchange_amount  == 0)
			{
				alerthtml += '<li>'+global_rnx.before_submit_exchange +'</li>';
			}	
		}	
		
		var rr_subject = jQuery("#ced_rnx_exchange_request_subject").val();
		
		if(rr_subject == '' || rr_subject == null)
		{
			rr_subject = jQuery("#ced_rnx_exchange_request_subject_text").val();
			if(rr_subject == '' || rr_subject == null)
			{
				alerthtml += '<li>'+global_rnx.exchange_subject_msg +'</li>';
			}	
		}
		jQuery(".ced_rnx_exchange_column").each(function(){
			if(jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').is(':checked')){
				var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_exchange_product_qty').val();
				var product_q = jQuery(this).find("td:eq(3)").children('#quanty').val();
				if(product_qty>product_q)
				{
					alerthtml += '<li>'+global_rnx.correct_quantity+'</li>';

				}
			}
		});
		
		var rr_reason = jQuery(".ced_rnx_exchange_request_reason").val();
		
		if(rr_reason == '' || rr_reason == null)
		{
			alerthtml += '<li>'+global_rnx.exchange_reason_msg+'</li>';
		}
		else
		{
			r_reason = rr_reason.trim();
			if(r_reason == '' || r_reason == null)
			{
				alerthtml += '<li>'+global_rnx.exchange_reason_msg+'</li>';
			}
		}	
		
		if(alerthtml != '')
		{
			jQuery("#ced-exchange-alert").show();
			jQuery("#ced-exchange-alert").html(alerthtml);
			jQuery('html, body').animate({
				scrollTop: jQuery("#ced_rnx_exchange_request_container").offset().top
			}, 800);
			return false;
		}
		else
		{
			jQuery("#ced-exchange-alert").hide();
			jQuery("#ced-exchange-alert").html(alerthtml);
		}	
		
		jQuery(".ced_rnx_exchange_notification").show();
		var data = {	
			action	:'ced_rnx_submit_exchange_request',
			orderid: orderid,
			reason: rr_reason,
			subject: rr_subject,
			security_check	:global_rnx.ced_rnx_nonce	
		}
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_exchange_notification").hide();
				jQuery("#ced-exchange-alert").html(response.msg);
				jQuery("#ced-exchange-alert").removeClass('woocommerce-error');
				jQuery("#ced-exchange-alert").addClass("woocommerce-message");
				jQuery("#ced-exchange-alert").css("color", "#8FAE1B");
				jQuery("#ced-exchange-alert").show();
				jQuery('html, body').animate({
					scrollTop: jQuery("#ced_rnx_exchange_request_container").offset().top
				}, 800);
				
				window.setTimeout(function() {
					window.location.href = global_rnx.myaccount_url;
				}, 1000);
			}
		});
	});
	
	if ( jQuery( document ).find("#ced_rnx_return_request_subject").length > 0 ) {
		jQuery( document ).find("#ced_rnx_return_request_subject").select2();
	}
	if ( jQuery( document ).find("#ced_rnx_exchange_request_subject").length > 0 ) {
		jQuery( document ).find("#ced_rnx_exchange_request_subject").select2();
	}

	jQuery("#ced_rnx_exhange_shop").click(function(e){
		
		var check = false;
		var selected_product = {};
		var count = 0;
		var orderid = jQuery("#ced_rnx_exchange_request_order").val();
		jQuery(".ced_rnx_exchange_column").each(function(){
			if(jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').is(':checked')){
				check = true;
				var product_info = {};
				var variation_id = jQuery(this).data("variationid");
				var product_id = jQuery(this).data("productid");
				var item_id = jQuery(this).data("itemid");
				var product_price = jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').val();
				var product_qty = jQuery(this).find("td:eq(2)").children('.ced_rnx_exchange_product_qty').val();
				product_info['product_id'] = product_id;
				product_info['variation_id'] = variation_id;
				product_info['item_id'] = item_id;
				product_info['price'] = product_price;
				product_info['qty'] = product_qty;
				selected_product[count] = product_info;
				count++;
			}
		});
		if (check == true) 
		{
			var data = {	
				action	:'ced_set_exchange_session',
				products: selected_product,
				orderid : orderid,
				security_check	:	global_rnx.ced_rnx_nonce	
			}
			jQuery('.ced_rnx_exchange_notification_choose_product').show();
			jQuery.ajax({
				url: global_rnx.ajaxurl, 
				type: "POST",  
				data: data,
				dataType :'json',	
				success: function(response) 
				{
					if(global_rnx.ced_rnx_exchange_variation_enable == true)
						{	jQuery('.ced_rnx_exchange_notification_choose_product ').hide();
					jQuery('#ced_rnx_variation_list').html('');
					jQuery('#ced_rnx_variation_list').html('<h4><Strong>'+global_rnx.ced_rnx_exchnage_with_same_product_text+'<Strong></h4>');
					jQuery(".ced_rnx_exchange_column").each(function(){
						if(jQuery(this).find("td:eq(0)").children('.ced_rnx_exchange_product').is(':checked')){
							var product_name = jQuery(this).find("td:eq(1)").children('.ced_rnx_product_title').children('a').html();
							var product_url = jQuery(this).find("td:eq(1)").children('.ced_rnx_product_title').children('a').attr('href');
							var clone = jQuery(this).find("td:eq(1)").clone().appendTo("#ced_rnx_variation_list");
							product_name = product_name.split('-');
							product_name = product_name[0];
							product_url = product_url.split('?');
							product_url = product_url[0];
							clone.find('.ced_rnx_product_title a').html(product_name);
							clone.wrap('<a href="'+product_url+'"></a><br>');
								// jQuery('.ced_rnx_exchange_note').append('<a href="'+product_url+'"><strong>'+product_name+'<strong></a><br>');
							}
						});
				}
				else
				{
					jQuery('.ced_rnx_exchange_notification_choose_product').show();
					window.location.href = global_rnx.shop_url;
				}
			}
		});
		}
		if(check == false)
		{
			e.preventDefault();
			var alerthtml = '<li>'+global_rnx.select_product_msg_exchange+'</li>';
			jQuery("#ced-exchange-alert").show();
			jQuery("#ced-exchange-alert").html(alerthtml);
			jQuery('html, body').animate({
				scrollTop: jQuery("#ced_rnx_exchange_request_container").offset().top
			}, 800);
			return false;
		}	
	});
	
	jQuery("#ced_rnx_return_request_subject").change(function(){
		var reason = jQuery(this).val();
		if(reason == null || reason == ''){
			jQuery("#ced_rnx_return_request_subject_text").show();
		}else{
			jQuery("#ced_rnx_return_request_subject_text").hide();
		}
	});
	
	var reason = jQuery("#ced_rnx_return_request_subject").val();
	
	if(reason == null || reason == ''){
		jQuery("#ced_rnx_return_request_subject_text").show();
	}else{
		jQuery("#ced_rnx_return_request_subject_text").hide();
	}
	
	jQuery("#ced_rnx_exchange_request_subject").change(function(){
		var reason = jQuery(this).val();
		if(reason == null || reason == ''){
			jQuery("#ced_rnx_exchange_request_subject_text").show();
		}else{
			jQuery("#ced_rnx_exchange_request_subject_text").hide();
		}
	});
	
	var reason = jQuery("#ced_rnx_exchange_request_subject").val();
	if(reason == null || reason == ''){
		jQuery("#ced_rnx_exchange_request_subject_text").show();
	}else{
		jQuery("#ced_rnx_exchange_request_subject_text").hide();
	}
	
	jQuery(document).on('click','.ced_rnx_add_to_exchanged_detail_variable',function(){
		var variation_id = jQuery('[name="variation_id"]').val();
		if(variation_id == null || variation_id <= 0)
		{
			alert('Please choose variation');
			return false;
		}
		var current = jQuery(this);
		jQuery(this).addClass('loading');
		var product_id = jQuery(this).data('product_id');
		var quantity = jQuery(".qty").val();
		var variations = {};
		jQuery(".variations select").each(function(){
			var name = jQuery(this).data("attribute_name");
			var val = jQuery(this).val();
			variations[name] = val;
		});
		
		var grouped = {};
		jQuery(".group_table tr").each(function(){
			quantity = jQuery(this).find("td:eq(0)").children().children().val();
			id = jQuery(this).find("td:eq(0)").children().children().attr('name');
			id = id.match(/\d+/);
			id = id[0];
			grouped[id] = quantity;
			
		});
		
		var product_info = {};
		product_info['id'] = product_id;
		product_info['variation_id'] = variation_id;
		product_info['qty'] = quantity;
		product_info['variations'] = variations;
		product_info['grouped'] = grouped;
		var data = {	
			action	:'ced_rnx_add_to_exchange',
			products: product_info,
			security_check	:	global_rnx.ced_rnx_nonce	
		}
		
		//Add Exchange Product
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				current.removeClass('loading');
				current.addClass('added');
				current.parent().html('<button class="ced_rnx_add_to_exchanged_detail_variable button alt" data-product_id="'+product_id+'"> '+global_rnx.exchange_text+' </button><a href="'+response.url+'">'+response.message+'</a>');
			}
		});
	});
	jQuery('.ced_rnx_guest_form').on('submit', function(e){
		var order_id = jQuery('#order_id').val();
		var order_email = jQuery('#order_email').val();
		if(order_id == '' || order_email == '')
		{
			
		}
	});

	jQuery('.ced_rnx_cancel_product_submit').on('click',function(){
		var order_id = jQuery('.ced_rnx_cancel_product_all').val();
		
		if(jQuery('.ced_rnx_cancel_product_all').is(':checked')){
			if(confirm("Are you really want to cancel whole order ?"))
			{	
				jQuery('.ced_rnx_return_notification').show();
				jQuery.ajax({
					url: global_rnx.ajaxurl, 
					type: "POST",             
					data: { action : 'ced_rnx_cancel_customer_order' , order_id : order_id , security_check	:	global_rnx.ced_rnx_nonce },
					success: function(respond)   
					{
						jQuery('.ced_rnx_return_notification').show();
						window.location.href = respond;// window.location.href = respond;
					}
				});
			}
		}
		else{
			if(confirm("Are you really want to cancel product(s) ?"))
			{
				jQuery('.ced_rnx_return_notification').show();
				var item_ids = [];
				var index = 0;
				var quantity = 0;
				var item_id = 0;
				jQuery('.ced_rnx_cancel_product').each(function(){
					if(jQuery(this).is(':checked'))
					{
						quantity = jQuery(this).closest('tr').find('.ced_rnx_cancel_product_qty').val();
						item_id = jQuery(this).val();
						item_ids.push([item_id, quantity]);
					}
				});
				// console.log(item_ids);
				jQuery.ajax({
					url: global_rnx.ajaxurl, 
					type: "POST",             
					data:{ 	action : 'ced_rnx_cancel_customer_order_products' , 
							order_id : order_id ,
							item_ids : item_ids , 
							security_check	:	global_rnx.ced_rnx_nonce 
						},
					success: function(respond)   
					{
						jQuery('.ced_rnx_return_notification').hide();
						window.location.href = respond;
					}
				});
				
			}
		}

	});
});
