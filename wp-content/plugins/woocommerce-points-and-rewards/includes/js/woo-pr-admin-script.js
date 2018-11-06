jQuery(document).ready( function($) {

	hide_points();

	$( document ).on( "click", ".woo-pr-points-editor-popup", function() {

		var username = $(this).parents('._woo_userpoints').siblings( 'td.column-username' ).find( 'strong>a' ).text();
                var user_id = $(this).attr('data-userid');
		var balance = $(this).attr('data-current');
		
                
		$( '#woo_pr_points_user_id' ).html(user_id);
		$( '#woo_pr_points_user_name' ).html(username);
		$( '#woo_pr_points_user_current_balance' ).html(balance);
		
		$( '#woo_pr_points_update_users_balance_amount' ).val('');
		$( '#woo_pr_points_update_users_balance_entry' ).val('');
		
		$( '.woo-pr-points-popup-overlay' ).fadeIn();
        $( '.woo-pr-points-popup-content' ).fadeIn();
	});
	
	//close popup window 
	$( document ).on( "click", ".woo-pr-points-close-button, .woo-pr-points-popup-overlay", function() {
		
		$( '.woo-pr-points-popup-overlay' ).fadeOut();
        $( '.woo-pr-points-popup-content' ).fadeOut();
        
	});
	
	//update user balance
	$( document ).on( "click", "#woo_pr_points_update_users_balance_submit", function() {
		
		var userid = $( 'span#woo_pr_points_user_id' ).text();
		var points = $( '#woo_pr_points_update_users_balance_amount' ).val();
		var log = $( '#woo_pr_points_update_users_balance_entry' ).val();
		
		$( '#woo_pr_points_update_users_balance_amount' ).removeClass('woo-pr-points-validate-error');
		
		if( points != '' ) {
			 
			$('#woo_pr_points_update_users_balance_submit').val( WOO_PR_Points_Admin.processing_balance );
			var data = {
							action	: 'woo_pr_adjust_user_points',
							userid	: userid,
							points	: points,
							log		: log
						};
			//call ajax to adjust points
			jQuery.post( ajaxurl, data, function( response ) {
				//alert( response );
				if( response != 'error' ) {
					$( '#woo_pr_points_user_current_balance' ).html( response );
					$( '#woo_pr_points_user_' + userid + '_balance' ).html( response );
					$( '#woo_pr_points_user_' + userid + '_adjust' ).attr( 'data-current', response );
				}
				$('#woo_pr_points_update_users_balance_amount').val('');
				$('#woo_pr_points_update_users_balance_entry').val('');
				$('#woo_pr_points_update_users_balance_submit').val( WOO_PR_Points_Admin.update_balance );
        		
			});
		} else {
			$( '#woo_pr_points_update_users_balance_amount' ).addClass('woo-pr-points-validate-error');
		}
	});
	
	$('.woo-pr-points-dropdown-wrapper select').css('width', '250px').chosen();
	$('select#woo_pr_points_userid').ajaxChosen({
	    method: 		'GET',
	    url: 			ajaxurl,
	    dataType: 		'json',
	    afterTypeDelay: 100,
	    minTermLength: 	1,
	    data: {
		    	action: 		'woo_pr_points_search_users',
		    	select_default: ''
	    }
	}, function (data) {

		var terms = {};

	    jQuery.each(data, function (i, val) {
	        terms[i] = val;
	    });

	    return terms;
	});
	
	//confirmation for applying discount buttons
	$( document ).on( "click", ".woo-pr-points-apply-disocunts-prev-orders", function() {
		
		var confirmdiscount = confirm( WOO_PR_Points_Admin.prev_order_apply_confirm_message );
		 
		if( confirmdiscount ) {
			return true;
		} else {
			return false;
		}
	});
	
	$( document ).on( "change", "#woo_pr_product_type", function(){

		hide_points();
	});
	
	function hide_points() {

		var product_type = $('#woo_pr_product_type').find(":selected").val();

		if( product_type == 'points' ) {
			$('#woo_pr_points_and_rewards').hide();
		} else {
			$('#woo_pr_points_and_rewards').show();
		}
	}
	
	// Hide/show the review points setting
	review_points_setting();

	$( document ).on( "change", "#woo_pr_enable_reviews", function(){

		review_points_setting();
	});

	function review_points_setting() {

		var enable_reviews = $('#woo_pr_enable_reviews');
		if( enable_reviews.prop('checked') == false ) {
			$('.woo_pr_review_points').parents('td').parents('tr').hide();
		} else {
			$('.woo_pr_review_points').parents('td').parents('tr').show();
		}
	}
	
	// Hide/show the Decimal points setting
	decimal_points_setting();

	$( document ).on( "change", "#woo_pr_enable_decimal_points", function(){

		decimal_points_setting();
	});

	function decimal_points_setting() {

		var enable_reviews = $('#woo_pr_enable_decimal_points');
		if( enable_reviews.prop('checked') == false ) {
			$('#woo_pr_number_decimal').parents('td').parents('tr').hide();
		} else {
			$('#woo_pr_number_decimal').parents('td').parents('tr').show();
		}
	}
});