jQuery(document).ready(function(){

	jQuery('#ced_rnx_customer_wallet_shortcode').attr('readonly' , 'readonly');
	jQuery(".ced_rnx_add-return-product-fee").click(function(){
		var html = '<div class="ced_rnx_add_fee">';
		html += '<input type="text" class="ced_return_fee_txt" name="ced_return_fee_txt[]" value="" placeholder="Fee Name">';
		html += '<input type="text" class="ced_return_fee_value wc_input_price" value="" placeholder="0" name="">';
		html += '<input type="button" class="button ced_rnx_remove-return-product-fee" value="Remove">';
		html += '</div>';
		jQuery('#ced_rnx_add_fee').append(html);
	});
	
	jQuery(".ced_rnx_save-return-product-fee").click(function(){
		jQuery(".ced_rnx_return_loader").show();
		var added_fee = {};
		var count = 0;
		var orderid = jQuery(this).data('orderid');
		var date = jQuery(this).data('date');
		jQuery(".ced_rnx_add_fee").each(function(){
			var fee = {};
			var fee_txt = jQuery(this).children().val();
			var fee_val = jQuery(this).children().next().val();
			fee['text'] = fee_txt;
			fee['val']  = fee_val;
			added_fee[count] = fee;
			count++;
		});
		
		var data = {
					action:'ced_return_fee_add',
					fees:added_fee,
					orderid : orderid,
					date : date,
					security_check	:	global_rnx.ced_rnx_nonce	
				   };
		
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_return_loader").hide();
				location.reload();
			}
		});
		
	});
	
	jQuery("#ced_rnx_accept_return").click(function(){
		
		jQuery("#ced_rnx_return_package").hide();
		jQuery(".ced_rnx_return_loader").show();
		var orderid = jQuery(this).data('orderid');
		var date = jQuery(this).data('date');
		var data = {
				action:'ced_return_req_approve',
				orderid:orderid,
				date:date,
				security_check	:	global_rnx.ced_rnx_nonce	
			   };
		
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_return_loader").hide();
				jQuery(".refund-actions .cancel-action").hide();
			
				window.location.reload();
				
			}
		});
	});	
	
	jQuery("#ced_rnx_left_amount").click(function(){
		jQuery(this).attr('disabled','disabled');
		var result = false;
		if(jQuery('.ced_rnx_extra_reason i').html() == "Left Amount After Exchange")
		 	result = true;

		if(result)
		{
			jQuery("#ced_rnx_return_package").hide();
			jQuery(".ced_rnx_return_loader").show();
			var orderid = jQuery(this).data('orderid');
			var refund_amount = jQuery(".ced_rnx_left_amount_for_refund").val();
			result=false;
			var data = {
					action:'ced_exchange_req_approve_refund',
					orderid:orderid,
					amount:refund_amount,
					security_check	:global_rnx.ced_rnx_nonce	
				   };
			jQuery('.ced_rnx_reason').children('p').eq(3).html("<strong style='color:red'>New Order Created Successfully.</strong> ");
			jQuery.ajax({
				url: global_rnx.ajaxurl, 
				type: "POST",  
				data: data,
				dataType :'json',	
				success: function(response) 
				{
					jQuery(this).removeAttr('disabled');
					if(response.result)
					{
						jQuery("#post").prepend('<div class="updated notice notice-success is-dismissible" id="message"><p>Amount is added to Customer wallet.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
						jQuery('html, body').animate({
					        scrollTop: jQuery("body").offset().top
					    }, 2000, "linear", function(){
					    	window.setTimeout(function() {
								window.location.reload();
							}, 1000);
					    });
					}
					else
					{
						jQuery(".ced_rnx_return_loader").hide();
					
						//jQuery(".refund-actions .cancel-action").hide();
						
						jQuery('html, body').animate({
					        scrollTop: jQuery("#order_shipping_line_items").offset().top
					    }, 1500);
						
						jQuery( 'div.wc-order-refund-items' ).slideDown();
						jQuery( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' ).slideUp();
						jQuery( 'div.wc-order-totals-items' ).slideUp();
						// jQuery( '#woocommerce-order-items' ).find( 'div.refund' ).show();
						jQuery( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).hide();
						var refund_amount = jQuery(".ced_rnx_extra_reason .ced_rnx_formatted_price").html();
						var refund_reason = jQuery(".ced_rnx_reason i").html();
						
						jQuery("#refund_amount").val(refund_amount);
						jQuery("#refund_reason").val(refund_reason);
						var total = accounting.unformat( refund_amount, woocommerce_admin.mon_decimal_point );

						jQuery( 'button .wc-order-refund-amount .amount' ).text( accounting.formatMoney( total, {
							symbol:    woocommerce_admin_meta_boxes.currency_format_symbol,
							decimal:   woocommerce_admin_meta_boxes.currency_format_decimal_sep,
							thousand:  woocommerce_admin_meta_boxes.currency_format_thousand_sep,
							precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
							format:    woocommerce_admin_meta_boxes.currency_format
						} ) );
					}
				}
			});
		}
		else
		{
			var order_id = jQuery(this).data('orderid');
			var refund_amount = jQuery(".ced_rnx_total_amount_for_refund").val();
			var data = {
				action:'ced_rnx_refund_price',
				order_id:order_id,
				refund_amount : refund_amount,
				security_check	:global_rnx.ced_rnx_nonce	
			   };

		  	jQuery.ajax({
				url: global_rnx.ajaxurl, 
				type: "POST",  
				data: data,
				dataType :'json',	
				success: function(response) 
				{
					jQuery(this).removeAttr('disabled');
					if((global_rnx.wallet == "1" && response.refund_method == 'manual_method') || global_rnx.wallet != "1")
					{
						jQuery('html, body').animate({
					        scrollTop: jQuery("#order_shipping_line_items").offset().top
					    }, 2000);
						
						jQuery( 'div.wc-order-refund-items' ).slideDown();
						jQuery( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' ).slideUp();
						jQuery( 'div.wc-order-totals-items' ).slideUp();
						jQuery( '#woocommerce-order-items' ).find( 'div.refund' ).show();
						jQuery( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).hide();
						var refund_amount = jQuery("#ced_rnx_refund_amount").val();
						var refund_reason = jQuery("#ced_rnx_refund_reason").val();
						jQuery("#refund_amount").val(refund_amount);
						jQuery("#refund_reason").val(refund_reason);
						
						var total = accounting.unformat( refund_amount, woocommerce_admin.mon_decimal_point );
		
						jQuery( 'button .wc-order-refund-amount .amount' ).text( accounting.formatMoney( total, {
							symbol:    woocommerce_admin_meta_boxes.currency_format_symbol,
							decimal:   woocommerce_admin_meta_boxes.currency_format_decimal_sep,
							thousand:  woocommerce_admin_meta_boxes.currency_format_thousand_sep,
							precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
							format:    woocommerce_admin_meta_boxes.currency_format
						} ) );
					}
					else
					{
						jQuery("#post").prepend('<div class="updated notice notice-success is-dismissible" id="message"><p>Amount is Successfully added to Customer wallet.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
						jQuery('html, body').animate({
					        scrollTop: jQuery("body").offset().top
					    }, 2000, "linear", function(){
					    	window.setTimeout(function() {
								window.location.reload();
							}, 1000);
					    });
					}

				}
			});
		}
	});	

	jQuery("#ced_rnx_cancel_exchange").click(function(){
		jQuery(".ced_rnx_exchange_loader").show();
		var orderid = jQuery(this).data('orderid');
		var date = jQuery(this).data('date');
		var data = {
				action:'ced_exchange_req_cancel',
				orderid:orderid,
				date:date,
				security_check	:	global_rnx.ced_rnx_nonce	
			   };
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_exchange_loader").hide();
				location.reload();
			}
		});
	});
	
	
	
	jQuery("#ced_rnx_cancel_return").click(function(){
		jQuery(".ced_rnx_return_loader").show();
		var orderid = jQuery(this).data('orderid');
		var date = jQuery(this).data('date');
		var data = {
				action:'ced_return_req_cancel',
				orderid:orderid,
				date:date,
				security_check	:	global_rnx.ced_rnx_nonce	
			   };
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_return_loader").hide();
				location.reload();
			}
		});
	});	
	
	jQuery("#ced_rnx_add_fee").on('click','.ced_rnx_remove-return-product-fee', function(){
		jQuery(this).parent().remove();
	});
	
	jQuery(".ced_rnx_add-exchange-product-fee").click(function(){
		var html = '<div class="ced_rnx_exchange_add_fee">';
		html += '<input type="text" class="ced_exchange_fee_txt" name="ced_exchange_fee_txt[]" value="" placeholder="Fee Name">';
		html += '<input type="text" class="ced_exchange_fee_value wc_input_price" value="" placeholder="0" name="">';
		html += '<input type="button" class="button ced_rnx_remove-exchange-product-fee" value="Remove">';
		html += '</div>';
		jQuery('#ced_rnx_exchange_add_fee').append(html);
	});
	
	jQuery(".ced_rnx_save-exchange-product-fee").click(function(){
		jQuery(".ced_rnx_exchange_loader").show();
		var added_fee = {};
		var count = 0;
		var orderid = jQuery(this).data('orderid');
		var date = jQuery(this).data('date');
		jQuery(".ced_rnx_exchange_add_fee").each(function(){
			var fee = {};
			var fee_txt = jQuery(this).children().val();
			var fee_val = jQuery(this).children().next().val();
			fee['text'] = fee_txt;
			fee['val'] = fee_val;
			added_fee[count] = fee;
			count++;
		});
		var data = {
					action:'ced_exchange_fee_add',
					fees:added_fee,
					orderid : orderid,
					date : date,
					security_check	:	global_rnx.ced_rnx_nonce	
				   };
		
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_exchange_loader").hide();
				location.reload();
			}
		});
		
	});
	
	jQuery("#ced_rnx_exchange_add_fee").on('click','.ced_rnx_remove-exchange-product-fee', function(){
		jQuery(this).parent().remove();
	});
	
	jQuery("#ced_rnx_accept_exchange").click(function(){
		jQuery(".ced_rnx_exchange_loader").show();
		var orderid = jQuery(this).data('orderid');
		var date = jQuery(this).data('date');
		var data = {
				action:'ced_exchange_req_approve',
				orderid:orderid,
				date:date,
				security_check	:	global_rnx.ced_rnx_nonce	
			   };
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				jQuery(".ced_rnx_exchange_loader").hide();
				// if(response['response'] == "successfull")
				    location.reload();
			}
		});
	});
	
	jQuery("#ced_rnx_return_predefined_reason_add").click(function(){
		var html = '';
		html += '<input type="text" name="ced_rnx_return_predefined_reason[]" value="" class="input-text">';
		jQuery("#ced_rnx_return_predefined_reason_wrapper").append(html);
	});
	jQuery("#ced_rnx_exchange_predefined_reason_add").click(function(){
		var html = '';
		html += '<input type="text" name="ced_rnx_exchange_predefined_reason[]" value="" class="input-text">';
		jQuery("#ced_rnx_exchange_predefined_reason_wrapper").append(html);
	});
	jQuery('#ced_rnx_accordion h2').on('click',function(){
		if(jQuery(this).next('.ced_rnx_content_sec').is(":visible"))
		{ 
			jQuery(this).removeClass('ced_rnx_slide_active');
		}     
		else
		{
			jQuery(this).addClass('ced_rnx_slide_active');
		}
		jQuery(this).next('.ced_rnx_content_sec').slideToggle('slow');
		
	});
	jQuery("#rnx_mail_setting").click(function(){
		if(jQuery("#rnx_mail_setting_wrapper").is(":visible"))
		{ 
			jQuery(this).removeClass('ced_rnx_slide_active');
		}     
		else
		{
			jQuery(this).addClass('ced_rnx_slide_active');
		}
		jQuery("#rnx_mail_setting_wrapper").slideToggle('slow');
	});
	jQuery("#rnx_return_reason").click(function(){
		if(jQuery("#rnx_return_reason_wrapper").is(":visible"))
		{ 
			jQuery(this).removeClass('ced_rnx_slide_active');
		}     
		else
		{
			jQuery(this).addClass('ced_rnx_slide_active');
		}
		jQuery("#rnx_return_reason_wrapper").slideToggle('slow');
	});
	jQuery("#rnx_exchange_reason").click(function(){
		if(jQuery("#rnx_exchange_reason_wrapper").is(":visible"))
		{ 
			jQuery(this).removeClass('ced_rnx_slide_active');
		}     
		else
		{
			jQuery(this).addClass('ced_rnx_slide_active');
		}
		jQuery("#rnx_exchange_reason_wrapper").slideToggle('slow');
	});
	jQuery("#rnx_refund_rules").click(function(){
		if(jQuery("#rnx_refund_rules_wrapper").is(":visible"))
		{ 
			jQuery(this).removeClass('ced_rnx_slide_active');
		}     
		else
		{
			jQuery(this).addClass('ced_rnx_slide_active');
		}
		jQuery("#rnx_refund_rules_wrapper").slideToggle('slow');
	});
	jQuery('#ced_rnx_enable_price_policy').on( 'change' , function(){
		if ( jQuery( '#ced_rnx_enable_price_policy' ).is( ':checked' ) ) {
			jQuery('#ced_rnx_price_based_policy').show();
		}else{
			jQuery('#ced_rnx_price_based_policy').hide();
		}
	});
	if ( jQuery( '#ced_rnx_enable_price_policy' ).is( ':checked' ) ) {
		jQuery('#ced_rnx_price_based_policy').show();
	}else{
		jQuery('#ced_rnx_price_based_policy').hide();
	}

	jQuery('#ced_rnx_enable_time_policy').on( 'change' , function(){
		if ( jQuery( '#ced_rnx_enable_time_policy' ).is( ':checked' ) ) {
			jQuery('#ced_rnx_time_based_policy').show();
		}else{
			jQuery('#ced_rnx_time_based_policy').hide();
		}
	});
	if ( jQuery( '#ced_rnx_enable_time_policy' ).is( ':checked' ) ) {
		jQuery('#ced_rnx_time_based_policy').show();
	}else{
		jQuery('#ced_rnx_time_based_policy').hide();
	}

	jQuery(document).on('click', '.ced_rnx_add_price_row', function(){
		var row = '<tr valign="top" class="price_row"><th class="titledesc" scope="row"><input type="number" name="ced_rnx_number_of_days[]" class="ced_rnx_number_of_days" min="0"></input></th><th class="titledesc" scope="row"><input type="text" name="ced_rnx_price_reduced[]" class="ced_rnx_price_reduced" placeholder="Enter % Price to be reduced"></input></th><th class="titledesc" scope="row"><input type="button" class="ced_rnx_add_price_row button" value="Add"></input><input type="button" class="ced_rnx_remove_price_row button" value="Remove"></input></th></tr>';
		jQuery(row).insertAfter( jQuery('[class^="price_row"]').last() );
	});

	jQuery(document).on('click' , '.ced_rnx_remove_price_row' , function(){
		jQuery(this).closest('tr.price_row').remove();
	});
	if ( jQuery( '.ced_rnx_date_time_picker' ).length > 0 ) 
	{
		jQuery( '.ced_rnx_date_time_picker' ).timepicker({
			showPeriod: true,
		    showLeadingZero: true
	    });
	}

	jQuery(document).on( 'click', '.ced_rnx_add_customer_wallet' , function(){

		var id = jQuery(this).data('id');
		jQuery('#regenerate_coupon_code_image-'+id).css('display' , 'inline-block');
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: { action : 'ced_rnx_generate_user_wallet_code' , id : id , security_check	:	global_rnx.ced_rnx_nonce },
			success: function(respond)   
			{
				var l = respond+'<br><b>( '+global_rnx.ced_rnx_currency_symbol+'0.00 )</b>';
				jQuery('#regenerate_coupon_code_image-'+id).css('display' , 'none');
				jQuery('div#user'+id).html(l);
			}
		});
	} );

	jQuery(document).on('click' , '#ced_rnx_change_customer_wallet_amount' , function(){
		jQuery('.regenerate_coupon_code_image').css('display' , 'inline-block');
		var user_id = jQuery(this).data('id');
		var coupon_code = jQuery(this).data('couponcode');
		var amount = jQuery('#ced_rnx_customer_wallet_price').val();
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: { action : 'ced_rnx_change_customer_wallet_amount' , coupon_code : coupon_code , amount : amount, security_check	:	global_rnx.ced_rnx_nonce },
			success: function(respond)   
			{
				jQuery('.regenerate_coupon_code_image').css('display' , 'none');
			}
		});
	});
	jQuery(document).on('click' ,'.ced_rnx_catalog_name_text',function(){
		jQuery(this).parent().siblings('.ced_rnx_catalog_toggle').slideToggle();
	});
	jQuery(document).on('click','.ced_rnx_catalog_add',function()
	{	
		var $this = jQuery(this).parents('.ced_rnx_catalog_dropdwn');
		var counter = jQuery('.ced_rnx_catalog_dropdwn').last().attr( 'data-counter' );
		counter++;
		var html='';
		var ced_product_id ;
		var pro_count=jQuery('#ced_products_count').val();
		var products={};
		for(var i=0;i<pro_count;i++){
			ced_product_id=jQuery('.ced_product_id'+i).val();
			products[ced_product_id]=jQuery('.ced_products'+i).val();
		}
		html+='<div class="ced_rnx_catalog_dropdwn" data-counter = '+counter+' ><div class="ced_rnx_catalog_wrapper" ><div class="ced_rnx_catalog_name_text"><strong>'+global_rnx.defuat_catalog_name+'</strong></div><a class="ced_rnx_catalog_delete" data-counter="'+counter+'" href="javascript:; "><strong>-</strong></a><a class="ced_rnx_catalog_add" data-counter="'+counter+'" href="javascript:;"><strong>+</strong></a></div><div class="ced_rnx_catalog_toggle" ><table><tr><th><label><strong>'+global_rnx.catalog_name+'</strong></label></th><td><input type="text" name="ced_rnx_catalog_name'+counter+'" class="ced_rnx_catalog_name" placeholder="'+global_rnx.placeholder_catalog_name+'"  ></td></tr><tr><th ><label><strong>'+global_rnx.select_catalog_product+'</strong></label></th><td><select name="ced_rnx_products'+counter+'[]" class="ced_rnx_products" multiple>';
		for(var key in products){
			
			html+='<option value="'+key+'" >'+ products[key] +'</option>';
		}
		html +='</select></td></tr><tr><th><label><strong>'+global_rnx.maximum_catalog_refund_days+'</strong></label></th><td><input type="number" min="0" placeholder="'+global_rnx.placeholder_refund+'"   name="ced_rnx_catalog_refund_days'+counter+'" class="ced_rnx_catalog_refund_days"><span>'+global_rnx.catalog_disable+'</span></td></tr><tr><th><label><strong>'+global_rnx.maximum_catalog_exchange_days+'</strong></label></th><td><input type="number" min="0" placeholder="'+global_rnx.placeholder_exchange+'"   name="ced_rnx_catalog_exchange_days'+counter+'" class="ced_rnx_catalog_exchange_days"><span>'+global_rnx.catalog_disable+'</span></td></tr></table></div></div>';
		jQuery('.ced_rnx_catalog_wrapper_section').append(html);
		jQuery(document.body).find( 'select.ced_rnx_products' ).select2();
	});

	jQuery(document).on('click','.ced_rnx_catalog_delete',function(){
		var catalog_index=jQuery(this).parents('.ced_rnx_catalog_dropdwn').attr('data-counter');
		var current_url      = window.location.href;
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: { action : 'ced_rnx_catalog_delete' ,catalog_db_index:catalog_index,},
			success: function(response)   
			{
				jQuery( '.ced_rnx_catalog_dropdwn' ).each(function(){
					if( jQuery(this).attr('data-counter') == catalog_index) 
					{
						console.log( jQuery(this).attr('data-counter') );
						jQuery(this).remove();
					}
				});
				if( jQuery(document).find('.ced_rnx_catalog_dropdwn').length <= 0 )
				{
					window.location.href = current_url;
				}
			}
		});
		
	});
	jQuery(document).on('click','.ced_rnx_catalog_add',function(){
		var catalog_count = jQuery(document).find('.ced_rnx_catalog_dropdwn').last().attr( 'data-counter' );
	jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: { action : 'ced_rnx_catalog_count' ,catalog_count:catalog_count	},
		});
	});
	jQuery(".button-primary").on('click',this,function(){
		var alerthtml='';
		jQuery('.ced_rnx_catalog_name').each(function(){
			var name=jQuery(this).val();
			if(name=='')
			{
				alerthtml += 'Please Enter Catalog Name.<br>';
				jQuery(".ced_rnx_error_notice").show();
			}
			else
			{
				alerthtml='';
			}
		});
		if(alerthtml != '')
		{
			jQuery(".ced_rnx_error_notice").show();
			jQuery(".ced_rnx_error_notice").html(alerthtml);
			jQuery('html, body').animate({
		        scrollTop: jQuery(".nav-tab-wrapper").offset().top
		    }, 800);
			return false;
		}
		else
		{
			jQuery(".ced_rnx_error_notice").hide();
		}	
	});
	jQuery(".ced_rnx_error_notice").hide();
	if(jQuery('.ced_rnx_products').length>0)
	{
		jQuery(document).find('.ced_rnx_products').select2();
	}
	// jQuery('.wc-order-data-row').show();
	jQuery( document ).on( 'click', '.mwb_sidebar_hide,.mwb_sidebar_heading', function(){
        if( jQuery( document ).find( '.mwb_sidebar_hidden' ).length > 0 )
        {
            jQuery( '.mwb_salebooster_adds' ).removeClass('mwb_sidebar_hidden');
            jQuery( '.mwb_table' ).removeClass('mwb_table_full_width');
            jQuery( '.mwb_sidebar_hide' ).css( 'transform', 'rotate(360deg)' );
            jQuery( '.mwb_sidebar_heading' ).html( 'Hide Sidebar' );
        }
        else{
            jQuery( '.mwb_salebooster_adds' ).addClass('mwb_sidebar_hidden');
            jQuery( '.mwb_table' ).addClass('mwb_table_full_width');
            jQuery( '.mwb_sidebar_hide' ).css( 'transform', 'rotate(180deg)' );
            jQuery( '.mwb_sidebar_heading' ).html( 'Show Sidebar' );
        }
    });

    jQuery(document).on('click','#ced_rnx_stock_back',function(){
    	jQuery(this).attr('disabled','disabled');
    	var order_id = jQuery(this).data('orderid');
    	var type = jQuery(this).data('type');
    	var data = { 
				action   : 'ced_rnx_manage_stock' ,
				order_id : order_id ,
				type     : type,
				security_check : global_rnx.ced_rnx_nonce
				};
    	jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",             
			data: data,
			dataType :'json',
			success: function(response)   
			{
				jQuery(this).removeAttr('disabled');
				if(response.result)
				{
					jQuery("#post").prepend('<div class="updated notice notice-success is-dismissible" id="message"><p>'+response.msg+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
					jQuery('html, body').animate({
				        scrollTop: jQuery("body").offset().top
				    }, 2000, "linear", function(){
				    	window.setTimeout(function() {
								window.location.reload();
							}, 1000);
				    });
				}
				else
				{
					jQuery("#post").prepend('<div id="messege" class="notice notice-error is-dismissible" id="message"><p>'+response.msg+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
					jQuery('html, body').animate({
				        scrollTop: jQuery("body").offset().top
				    }, 2000, "linear", function(){
				    });
				}
			}
		});
    });

	/////////////// License Activation ////////////////

	jQuery('#ced_rnx_license_save').on('click',function(){
		jQuery('.licennse_notification').html('');
		var ced_rnx_license_key = jQuery('#ced_rnx_license_key').val();
		if( ced_rnx_license_key == '' )
		{
			jQuery('#ced_rnx_license_key').css('border','1px solid red');
			return false;
		}
		else
		{
			jQuery('#ced_rnx_license_key').css('border','none');
		}
		jQuery('.loading_image').show();
		jQuery.ajax({
			url: global_rnx.ajaxurl, 
			type: "POST",  
			dataType: 'json',
			data:{
	 		'action':'ced_rnx_register_license',
	 		'security_check'	:	global_rnx.ced_rnx_nonce,
	 		'license_key':ced_rnx_license_key
			},success: function(response) 
			{
				if( response.msg == '' )
				{
					response.msg = 'Something Went Wrong! Please try again';
				}
				jQuery('.loading_image').hide();
				console.log(response);
				if(response.status == true )
				{
					jQuery('.licennse_notification').css('color','green');
					jQuery('.licennse_notification').html(response.msg);
					window.location.href = response.url;
				}
				else
				{	
					jQuery('.licennse_notification').css('color','red');
					jQuery('.licennse_notification').html(response.msg);
				}
			}
	 	});
	});

	////////////////End License authentication///////////////
});