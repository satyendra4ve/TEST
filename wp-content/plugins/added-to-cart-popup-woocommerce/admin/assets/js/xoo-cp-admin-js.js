jQuery(document).ready(function($){
	
	//Color Picker
	$(function(){
		$('.color-field').wpColorPicker();
	})

	//Tabs Change
	$('.xoo-tabs li').on('click',function(){
		var tab_class = $(this).attr('class').split(' ')[0];
		$('li').removeClass('active-tab');
		$('.settings-tab').removeClass('settings-tab-active');
		$(this).addClass('active-tab');
		var class_c = $('[tab-class='+tab_class+']').attr('class');
		$('[tab-class='+tab_class+']').attr('class',class_c+' settings-tab-active');
	})

	//Product details
	$('#xoo-cp-gl-pden').on('change',function(){
		if($(this).is(':checked')){
			$('#xoo-cp-gl-ibtne , #xoo-cp-gl-qtyen').parents('tr').show();
		}
		else{
			$('#xoo-cp-gl-ibtne , #xoo-cp-gl-qtyen').parents('tr').hide();
		}
	}).trigger('change');


	//Sidebar JS
	$(function(){
		var show_class 	= 'xoo-sidebar-show';
		var sidebar 	= $('.xoo-sidebar');
		var togglebar 	= $('.xoo-sidebar-toggle');

		//Show / hide sidebar
		if(localStorage.xoo_admin_sidebar_display){
			if(localStorage.xoo_admin_sidebar_display == 'shown'){
				sidebar.removeClass(show_class);
			}
			else{
				sidebar.addClass(show_class);
			}
			on_sidebar_toggle();
		}

		togglebar.on('click',function(){
			sidebar.toggleClass(show_class);
			on_sidebar_toggle();
		})

		function on_sidebar_toggle(){
			if(sidebar.hasClass(show_class)){
				togglebar.text('Show');
				var display = "hidden";
			}else{
				togglebar.text('Hide');
				var display = "shown";
			}
			localStorage.setItem("xoo_admin_sidebar_display",display);
		}
	});


})