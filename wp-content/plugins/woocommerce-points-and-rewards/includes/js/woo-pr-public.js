jQuery( function( $ ) {

	//Trigger when checkout data updated
	$( document.body ).on( "updated_checkout", function( e, data ) {

		//Check if message exists
		var ele_message = $( '.woocommerce-info.woo-pr-earn-points-message' );
		if( ele_message.length > 0 ) {
			var total_points = $( '#woo_pr_total_points_will_earn' ).val();
			if( total_points ) {
				var new_message = ele_message.html().replace(/-?[0-9]*\.?[0-9]+/, total_points);
				ele_message.html( new_message );
			}
		}
	});
});

//function for ajax pagination
function woo_pr_ajax_pagination(pid){
	var data = {
					action: 'woo_pr_next_page',
					paging: pid
				};

			jQuery('.woo-pr-sales-loader').show();
			jQuery('.woo-pr-paging').hide();

			jQuery.post(WooPointsPublic.ajaxurl, data, function(response) {
				var newresponse = jQuery( response ).filter( '.woo-pr-user-log' ).html();
				jQuery('.woo-pr-sales-loader').hide();
				jQuery('.woo-pr-user-log').html( newresponse );
			});
	return false;
}

