<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

	$gen_settings=get_option('phoen_rewpts_custom_btn_styling');
	
	$apply_btn_title    = (isset($gen_settings['apply_btn_title']))?( $gen_settings['apply_btn_title'] ):'APPLY POINTS';
	$remove_btn_title    = (isset($gen_settings['remove_btn_title']))?( $gen_settings['remove_btn_title'] ):'REMOVE POINTS';
	
	$apply_topmargin    = (isset($gen_settings['apply_topmargin']))?( $gen_settings['apply_topmargin'] ):'8';
	
	$apply_rightmargin    = (isset($gen_settings['apply_rightmargin']))?( $gen_settings['apply_rightmargin'] ):'10';
	
	$apply_bottommargin    = (isset($gen_settings['apply_bottommargin']))?( $gen_settings['apply_bottommargin'] ):'8';
	
	$apply_leftmargin    = (isset($gen_settings['apply_leftmargin']))?( $gen_settings['apply_leftmargin'] ):'10';
					
	$apply_btn_bg_col    = (isset($gen_settings['apply_btn_bg_col']))?( $gen_settings['apply_btn_bg_col'] ):'';
	
	$apply_btn_txt_col    = (isset($gen_settings['apply_btn_txt_col']))?( $gen_settings['apply_btn_txt_col'] ):'#000000';
	$apply_btn_txt_hov_col=  (isset($gen_settings['apply_btn_txt_hov_col']))?( $gen_settings['apply_btn_txt_hov_col'] ):'';
	$apply_btn_hov_col    = (isset($gen_settings['apply_btn_hov_col']))?( $gen_settings['apply_btn_hov_col'] ):'';
	
	$apply_btn_border_style    = (isset($gen_settings['apply_btn_border_style']))?( $gen_settings['apply_btn_border_style'] ):'none';
	
	$apply_btn_border    = (isset($gen_settings['apply_btn_border']))?( $gen_settings['apply_btn_border'] ):'0';
	
	$apply_btn_bor_col    = (isset($gen_settings['apply_btn_bor_col']))?( $gen_settings['apply_btn_bor_col'] ):'';
	
	$apply_btn_rad    = (isset($gen_settings['apply_btn_rad']))?( $gen_settings['apply_btn_rad'] ):'0';
	
	$div_rad    = (isset($gen_settings['div_rad']))?( $gen_settings['div_rad'] ):'0';
	
	
			
	$div_bg_col    = (isset($gen_settings['div_bg_col']))?( $gen_settings['div_bg_col'] ):'#fff';
	
	$div_border_style    = (isset($gen_settings['div_border_style']))?( $gen_settings['div_border_style'] ):'solid';
	
	$div_border    = (isset($gen_settings['div_border']))?( $gen_settings['div_border'] ):'1';
	
	$div_bor_col    = (isset($gen_settings['div_bor_col']))?( $gen_settings['div_bor_col'] ):'#ccc';
			
		
			?>

<style>
.phoen_rewpts_pts_link_div {					
	display: inline-block;	
}
.phoen_rewpts_redeem_message_on_cart {
	display: inline-block;
	font-size: 14px;
	line-height: 32px;	
}
.phoen_rewpts_reward_message_on_cart {
	display: inline-block;
}

.phoen_rewpts_pts_link_div_main {
    background: <?php echo $div_bg_col; ?> none repeat scroll 0 0;
    border: <?php echo $div_border; ?>px <?php echo $div_border_style; ?> <?php echo $div_bor_col; ?>;
    display: block;
    margin: 15px 0;
    overflow: auto;
    padding: 10px;
	border-radius:<?php echo $div_rad;?>px;
}

.phoen_rewpts_pts_link_div_main .phoen_rewpts_pts_link_div {
    float: right;
}

.phoen_rewpts_pts_link_div_main .phoen_rewpts_pts_link_div .button {
	padding: <?php echo $apply_topmargin;?>px <?php echo $apply_rightmargin;?>px <?php echo $apply_bottommargin;?>px <?php echo $apply_leftmargin;?>px; 
    font-weight: 400;
	background: <?php echo $apply_btn_bg_col;?>;
	border: <?php echo $apply_btn_border; ?>px <?php echo $apply_btn_border_style; ?> <?php echo $apply_btn_bor_col; ?>;
	color: <?php echo $apply_btn_txt_col; ?>;
	border-radius:<?php echo $apply_btn_rad;?>px;
}

.phoen_rewpts_pts_link_div_main .phoen_rewpts_pts_link_div .button:hover {
	background: <?php echo $apply_btn_hov_col;?>;
	color: <?php echo $apply_btn_txt_hov_col; ?>;
}

</style>