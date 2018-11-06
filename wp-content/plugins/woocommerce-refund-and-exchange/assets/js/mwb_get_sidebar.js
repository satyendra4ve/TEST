jQuery( document ).ready( function(){

	jQuery( '.mwb_salebooster_wrapper' ).siblings( 'table, h2' ).wrapAll( '<div class="mwb_table"></div>' );
	jQuery( '<div class="clear"></div>' ).insertAfter( jQuery(document).find( '.mwb_table' ) );


	jQuery.ajax({
		'url' 	: 'https://demo.makewebbetter.com/api/get_feed.php',
		data 	: {
			'plugin_id'		: 'mwb_rnx',
			'file_url' 		: 'https://demo.makewebbetter.com/api/feed.json',
		},
		type: "POST",
		dataType :'json',
		success : function(response)
		{
			// response = jQuery.parse JSON(response);
			if(ced_rnx_side.hide_forever != 'yes')
			{
				var html = '';
				html += '<div id="mwb_sidebar_action_panel">\
							<span class="mwb_sidebar_heading">Hide Sidebar</span>\
							<span class="mwb_sidebar_action mwb_sidebar_hide dashicons dashicons-arrow-right"></span>\
							<a href="javascript:;" class="mwb_sidebar_close">X</a>\
						</div> \
						<div class="mwb_salebooster_wrapper">\
							<div class="mwb_salebooster_adds">\
						<div class="mwb_plugin_sale_head">\
							<span><img src="'+ced_rnx_side.ced_rnx_URL+'/assets/images/logo.png" alt=""></span>\
							<h2>OUR FEATURE PRODUCTS</h2>\
						</div>';
				jQuery.each( response , function( key, value ){
					html += '<div class="mwb_plugins_list">\
								<div class="mwb_booster_content">\
									<div class="mwb_booster_content_head">';
										if( value.new_plugin == 'yes' ){
											html += '<img src="'+ced_rnx_side.ced_rnx_URL+'assets/images/new.png">';
										}
										html += '<h2 class="mwb_heading_1">'+value.plugin_name+'</h2>\
									</div>\
								</div>\
								<div class="mwb_bosster_content_para_wrap">\
									<div class="mwb_booster_content_para">\
										<span>'+value.question+'</span>\
									</div>\
									<div class="mwb_booster_content_para">\
										<span>'+value.answer+'</span>\
									</div>\
								</div>';

						html += '<div class="mwb_plugin_booster_amount">';
							if( value.price != '' ){
								html += '<h1>'+value.price+'</h1>';
							}
							html += '<div class="mwb_purchase_booster_pluginlink">\
										<a href="'+value.landing_page+'">'+ced_rnx_side.button_text+'</a>\
									</div>\
								</div>\
							</div>';
					
				} );
				html += '</div></div>';
				jQuery( html ).insertBefore( jQuery( document ).find( '.mwb_table' ) );
				var mwb_table_heigh = jQuery('.mwb_table').height();
				if( mwb_table_heigh < 346 ){
					jQuery('.mwb_salebooster_adds').css('height','346px');
				}
				else{
					jQuery('.mwb_salebooster_adds').css('height',mwb_table_heigh+'px');
				}
			}
			if( document.getElementById('mwb_sidebar_action_panel') !== null ){
				jQuery( '.mwb_table' ).removeClass('mwb_table_full_width');
			}
			else{
				jQuery( '.mwb_table' ).addClass('mwb_table_full_width');
			}
		}	

	});
	if(ced_rnx_side.hide_forever != 'yes')
	{
		if(window.location.href.match('tab=ced_rnx_setting&section=catalog_setting') == 'tab=ced_rnx_setting&section=catalog_setting' )
	    {
			jQuery('.mwb_table').hide();
			jQuery('.clear').hide();
			jQuery('#wpcontent ').append('<div class="clear"></div>');
		}
	}
} );

jQuery( document ).on( 'click', '.mwb_sidebar_hide .mwb_sidebar_heading', function(){
		if( jQuery( document ).find( '.mwb_sidebar_hidden' ).length > 0 )
		{
			jQuery( '.mwb_salebooster_adds' ).removeClass('mwb_sidebar_hidden');
			jQuery( '.mwb_table' ).removeClass('mwb_table_full_width');
			jQuery( '.mwb_sidebar_hide' ).css( 'transform', 'rotate(360deg)' );
			jQuery( '.mwb_sidebar_heading' ).html( ced_rnx_side.Hide_sidebar );
		}
		else{
			jQuery( '.mwb_salebooster_adds' ).addClass('mwb_sidebar_hidden');
			jQuery( '.mwb_table' ).addClass('mwb_table_full_width');
			jQuery( '.mwb_sidebar_hide' ).css( 'transform', 'rotate(180deg)' );
			jQuery( '.mwb_sidebar_heading' ).html( ced_rnx_side.Show_sidebar );
		}
	} );
jQuery( document ).on( 'click', '.mwb_sidebar_close', function(){
	var data = {
				action:'ced_rnx_hide_sidebar_forever'
			};
	jQuery.ajax({
  			url: ced_rnx_side.ajaxurl, 
  			type: "POST",  
  			data: data,
  			dataType :'json',
  			success: function(response) 
  			{	
  				if(response.result == 'success')
  				{
  					location.reload();
  				}
  			}
  		});
});