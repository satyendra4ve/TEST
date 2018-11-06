<?php

$plugin_dir_url = plugin_dir_url( __FILE__ );

?>
<style>
					
						.upgrade{background:#f4f4f9;padding: 50px 0; width:100%; clear: both;}
						.upgrade .upgrade-box{ background-color: #808a97;
							color: #fff;
							margin: 0 auto;
						   min-height: 110px;
							position: relative;
							width: 60%;}

						.upgrade .upgrade-box p{ font-size: 15px;
							 padding: 19px 20px;
							text-align: center;}

						.upgrade .upgrade-box a{background: none repeat scroll 0 0 #6cab3d;
							border-color: #ff643f;
							color: #fff;
							display: inline-block;
							font-size: 17px;
							left: 50%;
							margin-left: -150px;
							outline: medium none;
							padding: 11px 6px;
							position: absolute;
							text-align: center;
							text-decoration: none;
							top: 36%;
							width: 277px;}

						.upgrade .upgrade-box a:hover{background: none repeat scroll 0 0 #72b93c;}
                       
					   /**premium box**/    
						.premium-box{ width:100%; height:auto; background:#fff; float:left; }
						.premium-features{}
						.premium-heading{color:#484747;font-size: 40px; padding-top:35px;text-align:center;text-transform:uppercase;}
						.premium-features li{ width:100%; float:left;  padding: 80px 0; margin: 0; }
						.premium-features li .detail{ width:50%; }
						.premium-features li .img-box{ width:50%;box-sizing:border-box; }
						

						.premium-features li:nth-child(odd) { background:#f4f4f9; }
						.premium-features li:nth-child(odd) .detail{float:right; text-align:left; }
						.premium-features li:nth-child(odd) .detail .inner-detail{}
						.premium-features li:nth-child(odd) .detail p{ }
						.premium-features li:nth-child(odd) .img-box{ float:left; text-align:right; padding-right:30px;}

						.premium-features li:nth-child(even){  }
						.premium-features li:nth-child(even) .detail{ float:left; text-align:right;}
						.premium-features li:nth-child(even) .detail .inner-detail{ margin-right: 46px;}
						.premium-features li:nth-child(even) .detail p{ float:right;} 
						.premium-features li:nth-child(even) .img-box{ float:right;}

						.premium-features .detail{}
						.premium-features .detail h2{ color: #484747;  font-size: 24px; font-weight: 700; padding: 0; line-height:1.1;}
						.premium-features .detail p{  color: #484747;  font-size: 13px;  max-width: 327px;}
					 
					 /**images**/
					 
					 .pd_prm_option1 { background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option1.png") no-repeat; width:100%; max-width:496px; height:98px; display:inline-block; background-size:100% auto;}
					  
					 .prm_option2{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option2.png") no-repeat; width:100%;max-width:409px; height:82px; display:inline-block;  background-size:100% auto; }
					
                     .prm_option3{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option3.png") no-repeat; width:100%;max-width:452px;   height:118px; display:inline-block;background-size:100% auto;}

					 .prm_option4{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option4.png") no-repeat; width:100%;max-width:264px;  height:73px; display:inline-block;  background-size:100% auto;}					
					 .prm_option5{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option5.png") no-repeat; width:100%;max-width:658px; height:265px; display:inline-block; background-size:100% auto;}	
					 .prm_option6{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option6.png") no-repeat; width:100%; max-width:514px; height: 138px; display:inline-block; background-size:100% auto;}  					
					 .prm_option7{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option7.png") no-repeat; width:100%; max-width:670px; height: 864px; display:inline-block; background-size:100% auto;}  					
					 .prm_option8{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option8.png") no-repeat; width:100%; max-width:743px; height: 955px; display:inline-block; background-size:100% auto;}  					
					 .prm_option9{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option9.png") no-repeat; width:100%; max-width:689px; height: 346px; display:inline-block; background-size:100% auto;}  					
					 .prm_option10{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option10.png") no-repeat; width:100%; max-width:600px; height: 279px; display:inline-block; background-size:100% auto;}  					
				     .prm_option11{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option11.png") no-repeat; width:100%; max-width:395px; height: 462px; display:inline-block; background-size:100% auto;}
					 .prm_option12{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option12.png") no-repeat; width:100%; max-width:405px; height: 450px; display:inline-block; background-size:100% auto;}
					 .prm_option13{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option13.png") no-repeat; width:100%; max-width:323px; height: 70px; display:inline-block; background-size:100% auto;}					 
					 .prm_option14{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option14.png") no-repeat; width:100%; max-width:421px; height: 68px; display:inline-block; background-size:100% auto;}
					 .prm_option14_front{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option14-front.png") no-repeat; width:100%; max-width:270px; height: 133px; display:inline-block; background-size:100% auto;}
					 .prm_option15{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option15.png") no-repeat; width:100%; max-width:647px; height: 231px; display:inline-block; background-size:100% auto;}					 
					 .prm_option15_front{background:url("<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/prm_option15-front.png") no-repeat; width:100%; max-width:790px; height: 654px; display:inline-block; background-size:100% auto;}
                     
   
.premium-box .premium-box-head {
    background: #eae8e7 none repeat scroll 0 0;
    height: 500px;
    text-align: center;
    width: 100%;
}
.premium-box .premium-box-head .pho-upgrade-btn {
    display: block;
    text-align: center;
}

.premium-box .premium-box-head .pho-upgrade-btn a {
    display: inline-block;
    margin-top: 75px;
}


.main-heading {
    background: #fff none repeat scroll 0 0;
    margin-bottom: -70px;
    text-align: center;
}
.main-heading img {
    margin-top: -200px;
}

.premium-box-container {
    margin: 0 auto;
}
.premium-box-container .description:nth-child(2n+1) {
    background: #fff none repeat scroll 0 0;
}
.premium-box-container .description {
    display: block;
    padding: 35px 0;
    text-align: center;
}

.premium-box-container .pho-desc-head::after {
    background: rgba(0, 0, 0, 0) url("imges/head-arrow.png") no-repeat scroll 0 0;
    content: "";
    height: 98px;
    position: absolute;
    right: -30px;
    top: -6px;
    width: 69px;
}
.premium-box-container .pho-desc-head {
    margin: 0 auto;
    position: relative;
    width: 768px;
}
.premium-box-container .description {
    text-align: center;
}

.pho-plugin-content p {
    color: #212121;
    font-size: 18px;
    line-height: 32px;
}

.premium-box-container .pho-desc-head h2 {
    color: #02c277;
    font-size: 28px;
    font-weight: bolder;
    margin: 0;
    text-transform: capitalize;
    line-height: 30px;
}

.premium-box-container .description:nth-child(2n+1) .pho-img-bg {
     background: #f1f1f1 url("<?php echo $plugin_dir_url; ?>../assets/images/image-frame-odd.png") no-repeat scroll 100% top;
}
.description .pho-plugin-content .pho-img-bg {
    border-radius: 5px 5px 0 0;
    height: auto;
    margin: 0 auto;
    padding: 70px 0 40px;
    width: 750px;
}

.premium-box-container .description:nth-child(2n) .pho-img-bg {
    background: #f1f1f1 url("<?php echo $plugin_dir_url; ?>../assets/images/image-frame-even.png") no-repeat scroll 100% top;
}

.premium-box-container .description:nth-child(2n) {
    background: #eae8e7 none repeat scroll 0 0;
}
 
 
.premium-box-container .pho-desc-head::after {
    background: rgba(0, 0, 0, 0) url("<?php echo $plugin_dir_url; ?>../assets/images/head-arrow.png") no-repeat scroll 0 0;
    content: "";
    height: 98px;
    position: absolute;
    right: -30px;
    top: 0;
    width: 69px;
} 

.pho-plugin-content {
    margin: 0 auto;
    overflow: hidden;
    width: 768px;
} 

.pho-upgrade-btn {
    display: block;
    text-align: center;
}


.pho-upgrade-btn a {
    display: inline-block;
    margin-top: 75px;
}
           
img {
	height: auto;
	max-width: 100%;
}

a:focus {
	box-shadow: none;
}
		   
				</style>
		<div class="premium-box">	
            <div class="premium-box-head">
					<div class="pho-upgrade-btn">
	
						<a href="https://www.phoeniixx.com/product/reward-points-for-woocommerce/" target="_blank"><img src="<?php echo $plugin_dir_url; ?>../assets/images/premium-btn.png" /></a>
						<a target="blank" href="http://reward.phoeniixxdemo.com/wp-login.php?redirect_to=http%3A%2F%2Freward.phoeniixxdemo.com%2Fwp-admin%2F&reauth=1"><img src="<?php echo $plugin_dir_url; ?>../assets/images/button2.png" /></a>
						</div></div>
											
	<ul class="premium-features">
		<div class="main-heading"><h1><img src="<?php echo $plugin_dir_url; ?>../assets/images/premium-head.png" /></h1></div>
                            
       <div class="premium-box-container">
			
			
			<div class="description">
				<div class="pho-desc-head"><h2>Product Based Points</h2></div>
				<div class="pho-plugin-content">
							<p>
									  With this feature you can set the reward points for the specific product only.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/product_based_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			
			<div class="description">
				<div class="pho-desc-head"><h2>Account Sign up Point</h2></div>
				<div class="pho-plugin-content">
							<p>
									  With this option you could set the sign up points. 
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/account_sign_up_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>First Order Points</h2></div>
				<div class="pho-plugin-content">
							<p>
									  With this option customer get the reward points when they shop for the first time. 
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/First_order_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			
			<div class="description">
				<div class="pho-desc-head"><h2>First Review Points</h2></div>
				<div class="pho-plugin-content">
							<p>
									  This feature allows you to set the points when the customer gives first review on the product.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/first_review_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>Payment Gateway Points</h2></div>
				<div class="pho-plugin-content">
							<p>
									  This is the option in which you can set the reward points for the payment gateway (Only for the paypal)
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/payment_gateway_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>Expiry Date</h2></div>
				<div class="pho-plugin-content">
							<p>
									  Option to set the points expiry date. 
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/points-expiry-date.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>Bonus Points</h2></div>
				<div class="pho-plugin-content">
							<p>
									  Can give the bonus points to your customers. 
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/Bonus-points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>Points Earning Date</h2></div>
				<div class="pho-plugin-content">
							<p>
									  There will be an option to set the reward points earning date. 
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/earning-points-date.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>User Role Based</h2></div>
				<div class="pho-plugin-content">
							<p>
									  By using this option you can set the points based on a user role.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/User-role-based.png" />
							</div>
						</div>
			</div><!-- description end -->
			
						
			<div class="description">
				<div class="pho-desc-head"><h2>Reward Point Detail</h2></div>
				<div class="pho-plugin-content">
							<p>
									  With this feature admin can view all the details of the reward points earned by the customer.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/reward_points_details.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			<div class="description">
				<div class="pho-desc-head"><h2>Manage Customer points</h2></div>
				<div class="pho-plugin-content">
							<p>
									 Admin can manage the customer points from the backend settings.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/manage_customer_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			
			<div class="description">
				<div class="pho-desc-head"><h2>View customer points</h2></div>
				<div class="pho-plugin-content">
							<p>
									  With this option admin can view the details of all the points earned by the customer , points spent by them.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/view_customer_points.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			
			<div class="description">
				<div class="pho-desc-head"><h2>Customer can view points in their account</h2></div>
				<div class="pho-plugin-content">
							<p>
									  By using the shortcode the customer can also view their reward points in their account.
									</p>
							<div class="pho-img-bg">
							<img src="<?php echo $plugin_dir_url; ?>../assets/images/customer_can_view.png" />
							</div>
						</div>
			</div><!-- description end -->
			
			
			
        
        
          
       
        <div class="pho-upgrade-btn">
        	<a href="https://www.phoeniixx.com/product/reward-points-for-woocommerce/" target="_blank"><img src="<?php echo $plugin_dir_url; ?>../assets/images/premium-btn.png" /></a>
			<a target="blank" href="http://reward.phoeniixxdemo.com/wp-login.php?redirect_to=http%3A%2F%2Freward.phoeniixxdemo.com%2Fwp-admin%2F&reauth=1"><img src="<?php echo $plugin_dir_url; ?>../assets/images/button2.png" /></a>
        </div>
        
        
        
       </div><!-- premium-box-container -->
							
						 
						</ul>
						
						
						
				   </div>