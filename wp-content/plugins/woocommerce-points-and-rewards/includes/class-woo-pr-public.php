<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Public Pages Class
 *
 * Handles all the different features and functions
 * for the front end pages.
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
class Woo_Pr_Public {

    var $model, $logs;

    public function __construct() {

        global $woo_pr_model, $woo_pr_log;

        $this->logs = $woo_pr_log;
        $this->model = $woo_pr_model;
        if (!session_id())
            session_start();
    }

    /**
     * Show Message for cart/redeemed product point
     * 
     * Handles to show message on cart
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_cart_checkout_message_content() {

        global $woocommerce;

        $woo_pr_msg = get_option('woo_pr_earn_points_cart_message');
        $cart_data  = $woocommerce->cart->get_cart();

        $totalpoints_earned = $this->model->woo_pr_get_user_checkout_points($cart_data);
        $points_label       = $this->model->woo_pr_get_points_label($totalpoints_earned);

        if( $totalpoints_earned === 'user_product' ) {

            // Owner User Message
            echo $this->model->woo_pr_owner_product_message( 'cart' );
        } else {
            // Replace code into message
            $points_replace = array( "{points}", "{points_label}" );
            $replace_message = array( $totalpoints_earned, $points_label);
            $message = $this->model->woo_pr_replace_array($points_replace, $replace_message, $woo_pr_msg);

            if (!empty($message) && !empty($totalpoints_earned)) {

                // wrap with info div
                $message = '<div class="woocommerce-info woo-pr-earn-points-message">' . $message . '</div>';

                echo apply_filters( 'woo_pr_earn_points_message', $message, $totalpoints_earned );
            }
        }
    }

    /**
     * Show Message for cart/redeemed product point
     * 
     * Handles to show message on cart
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_guest_cart_checkout_message_content() {

        global $woocommerce;

        if( !is_user_logged_in() ) {
            
            // Get message options
            $guest_checkout_msg     = get_option('woo_pr_guest_checkout_page_message');
            $guest_checkout_buy_msg = get_option('woo_pr_guest_checkout_page_buy_message');
            $signup_points          = get_option('woo_pr_earn_for_account_signup');

            $cart_data  = $woocommerce->cart->get_cart();

            $totalpoints_earned = $this->model->woo_pr_get_user_checkout_points($cart_data);
            $points_label       = $this->model->woo_pr_get_points_label($totalpoints_earned);

            // Replace code into message
            $points_replace     = array("{points}", "{points_label}", "{signup_points}");
            $replace_message    = array( $totalpoints_earned, $points_label, $signup_points );
            $signup_message     = $this->model->woo_pr_replace_array($points_replace, $replace_message, $guest_checkout_msg);

            //check user is not logged in and total earned points is not empty
            if (!empty($signup_message) && !empty($totalpoints_earned) ) {

                // wrap with info div
                $signup_message = '<div class="woocommerce-info woo-pr-earn-points-message">' . $signup_message . '</div>';

                echo apply_filters( 'woo_pr_earn_points_guest_checkout_message', $signup_message, $totalpoints_earned, $signup_points );
            }

            // If cart_data not empty
            if (!empty($cart_data)) {
                $total_buy_points = 0;
                foreach ($cart_data as $cart_item) {

                    $product_id     = $cart_item['product_id'];
                    $quantity       = $cart_item['quantity'];
                    $_product       = wc_get_product( $product_id );
                    $pro_type       = $_product->get_type();

                    if( !empty( $pro_type ) && $pro_type == 'woo_pr_points' ) {

                        $total_buy_points += $this->model->woo_pr_get_product_buy_points($product_id, $quantity);
                    }
                }

                // Replace code into message
                $points_replace     = array( "{points}", "{points_label}" );
                $replace_message    = array( $total_buy_points, $points_label );
                $guest_buy_msg      = $this->model->woo_pr_replace_array( $points_replace, $replace_message, $guest_checkout_buy_msg);

                if( !empty($guest_buy_msg) &&  !empty($total_buy_points) ){

                    // wrap with info div
                    $guest_buy_msg = '<div class="woocommerce-info woo-pr-buy-points-message">' . $guest_buy_msg . '</div>';

                    echo apply_filters( 'woo_pr_buy_points_guest_checkout_message', $guest_buy_msg, $total_buy_points );

                }
            }
        }
    }

    /**
     * Redeem Points Markup
     * 
     * Handles to show redeem points markup
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_redeem_point_markup() {

        global $current_user, $woocommerce, $wp;

        $gotdiscount = false;
        $woo_fees = $woocommerce->cart->get_fees();

        //points plural label
        $plurallable = !empty(get_option('woo_pr_lables_points_monetary_value')) ? get_option('woo_pr_lables_points_monetary_value') : __( 'Points', 'woopoints' );
        $woo_pr_fee_name = $plurallable.__( ' Discount', 'woopoints' );
        $woo_pr_fee_name = str_replace( ' ', '-', strtolower( $woo_pr_fee_name ) );

        foreach ($woo_fees as $woo_fee_key => $woo_fee_val) {

            if (strpos($woo_fee_key, $woo_pr_fee_name) !== false) {

                $gotdiscount = true;
                break;
            }
        }

        $current_uri = home_url($wp->request);

        // get message from settings
        $redemptionmessage = get_option('woo_pr_redeem_points_cart_message');

        //  calculate discount towards points
        $available_discount_value = $this->model->woo_pr_get_discount_for_redeeming_points();

        $points = intval(get_option('woo_pr_redeem_points'));

        // Get redemption ration from settings page
        $rate = intval(get_option('woo_pr_redeem_points_monetary_value'));

        if (empty($points) || empty($rate)) {
            return 0;
        }

        $available_discount = $available_discount_value * ( $rate / $points );

        if (!empty($available_discount) && !empty($redemptionmessage)) {

            //get discounte price from points
            $discountedpoints   = $this->model->woo_pr_calculate_points($available_discount);

            // Conver the amount
            $available_discount = woo_pr_wcm_currency_convert( $available_discount );

            //get points label to show to user
            $points_label       = $this->model->woo_pr_get_points_label($discountedpoints);

            $points_replace     = array("{points}", "{points_label}", "{points_value}");
            $replace_message    = array( $discountedpoints, $points_label, get_woocommerce_currency_symbol() . round( $available_discount, 2 ) );
            $message = $this->model->woo_pr_replace_array($points_replace, $replace_message, $redemptionmessage);

            // add 'Apply Discount' button
            if (!empty($message) && $gotdiscount == false) {
                ob_start();
                ?>
                <div class="woo-points-redeem-points-wrap">
                    <form method="POST" action="" >

                        <input type="submit" id="woo_pr_apply_discount" name="woo_pr_apply_discount" class="button woo-points-apply-discount-button" value="<?php _e('Apply Discount', 'woopoints'); ?>" />
                        <input type="hidden" name="add_discount_value" value="<?php echo $available_discount; ?>">    

                        <div class="woo-points-redeem-message"><p><?php echo $message; ?></p></div><!--.woo-pr-points-checkout-message-->
                    </form>
                </div>
                <?php
                $message_content = ob_get_clean();
            }
        }

        if (!empty($gotdiscount)) {

            $removfeesurl = add_query_arg(array('woo_pr_remove_discount' => 'remove'), $current_uri);
            ob_start();
            ?>
            <fieldset class="woo-pr-points-checkout-message">

                <a href="<?php echo $removfeesurl; ?>" class="button woo-point-remove-discount-link woo-pr-points-float-right"><?php _e('Remove', 'woopoints'); ?></a>
                <div class="woo-pr-points-remove-disocunt-message"><?php printf(__('Remove %s Discount', 'woopoints'), $points_label); ?></div><!--.woo-pr-points-checkout-message-->
            </fieldset><!--.woo-pr-points-redeem-points-wrap--> 
            <?php
            $message_content = ob_get_clean();
        }

        if( !empty($available_discount) && !empty($message_content) ){

            // wrap with info div
            $message_content = '<div class="woocommerce-info woo-pr-redeem-earn-points">' . $message_content . '</div>';

            echo apply_filters( 'woo_pr_redeem_points_message', $message_content, $available_discount );
        }
    }

    /**
     * Manage points on order processing or completed
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_order_processing_completed_update_points($order_id) {

        $prefix     = WOO_PR_META_PREFIX;
        $order      = new WC_Order($order_id);
        $user_id    = $order->get_user_id();
        $cart_data  = $order->get_items();
        $totalpoints_earned = 0;

        // Get points redeemed meta
        $old_points_order_earned = get_post_meta( $order_id, $prefix.'points_order_earned', true );
        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');

        if( !empty( $cart_data ) && empty($old_points_order_earned) ) {

	        // Get total earned points from this order
	        $totalpoints_earned = $this->model->woo_pr_get_user_checkout_points( $cart_data, $user_id, $order_id );
            $order_currency = get_post_meta( $order_id, '_order_currency', true );

            if( $totalpoints_earned  != 'user_product' ){
    	        //points plural label
    	        $pointslabel = $this->model->woo_pr_get_points_label( $totalpoints_earned );
    	
    	        //record data logs for redeem for purchase
    	        $post_data = array(
    	            'post_title'   => sprintf(__('%s earned for purchase', 'woopoints'), $pointslabel ),
    	            'post_content' => sprintf(__('%s earned for purchase', 'woopoints'), $pointslabel ),
    	            'post_author'  => $user_id,
                    'post_parent'  => $order_id,
    	        );
    	        //log meta array
    	        $log_meta = array(
                    'order_id'  => $order_id,
    	            'userpoint' => $totalpoints_earned,
    	            'events' => 'earned_purchase',
    	            'operation' => 'add'//add or minus
    	        );
    	
    	        //insert entry in log
    	        $this->logs->woo_pr_insert_logs($post_data, $log_meta);
    	
    	        // Add points from user points log
    	        woo_pr_add_points_to_user($totalpoints_earned, $user_id);
    	
    	        $order->add_order_note(sprintf(__('%1$d %2$s earned for purchase.', 'woopoints'), $totalpoints_earned, $pointslabel));
            }

	        // Get selling points from settings page
	        $seller_points 	= !empty(get_option('woo_pr_selling_points')) ? get_option('woo_pr_selling_points') : '';
	
	        // Get selling ratio from settings page
	        $seller_rate 	= !empty(get_option('woo_pr_selling_points_monetary_value')) ? get_option('woo_pr_selling_points_monetary_value') : '';

	        // Add points for product sale
	        foreach ( $cart_data as $item_key => $item_data ) {

	        	// If product is variable product take variation id else product id
				$data_id = ( !empty( $item_data['variation_id'] ) ) ? $item_data['variation_id'] : $item_data['product_id'];

				$post 		= get_post( $item_data['product_id'] );
				$_product 	= wc_get_product( $data_id );

                // If user is not product author then add selling points.
                if( !empty($post) && ($user_id != $post->post_author) ){
                    $_pro_price = $_product->get_price();

                    $_pro_price = woo_pr_wcm_currency_convert_original( $_pro_price );

    				// Calculate seller points
    				$seller_earned_points = ( $seller_points * ( $_pro_price * $item_data->get_quantity() ) ) / $seller_rate;

                    // Apply decimal if enabled
                    if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
                        $seller_earned_points = round( $seller_earned_points, $woo_pr_number_decimal );
                    } else {
                        $seller_earned_points = round( $seller_earned_points );
                    }

    				//points plural label
    	        	$pointslabel = $this->model->woo_pr_get_points_label( $seller_earned_points );
    			
    				$post_data = array(
    								'post_title'	=> sprintf( __('%s earned for selling the downloads.','woopoints'), $pointslabel ),
    								'post_content'	=> sprintf( __('Get %s for selling the downloads.','woopoints'), $pointslabel ),
    								'post_author'	=> $post->post_author,
                                    'post_parent'   => $order_id,
    							);
    				$log_meta = array(
                                        'order_id'      => $order_id,
    									'userpoint'		=>	$seller_earned_points,
    									'events'		=>	'earned_sell',
    									'operation'		=>	'add'//add or minus
    								);
    							
    				//insert entry in log	
    				$this->logs->woo_pr_insert_logs( $post_data, $log_meta );
    			
    				//update user points
    				woo_pr_add_points_to_user( $seller_earned_points, $post->post_author );
                }
	        }
        }
        // Update points redeemed meta
        update_post_meta( $order_id, $prefix.'points_order_earned', $totalpoints_earned );
    }

    /**
     * Calculate Discount towards Points
     * 
     * Handles to calculate the discount towards points
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_redeem_points_add_remove_discount() {

        global $woocommerce;
        $cartdata = $woocommerce->cart->get_cart();

        //points plural label
        $plurallable = !empty(get_option('woo_pr_lables_points_monetary_value')) ? get_option('woo_pr_lables_points_monetary_value') : __( 'Points', 'woopoints' );
        $woo_pr_fee_name = $plurallable.__( ' Discount', 'woopoints' );
        $woo_pr_fee_name_low = str_replace( ' ', '-', strtolower( $woo_pr_fee_name ) );
        
        if (isset($_GET['woo_pr_remove_discount']) && !empty($_GET['woo_pr_remove_discount']) && $_GET['woo_pr_remove_discount'] == 'remove') {

            $woo_fees = $woocommerce->cart->get_fees();
            if (!empty($woo_fees)) {

                foreach ($woo_fees as $woo_fee_key => $woo_fee_val) {

                    if (strpos($woo_fee_key, $woo_pr_fee_name_low ) !== false) {

                        //remove fees towards fees
                        $woocommerce->cart->remove_fee($woo_pr_fee_key);
                    }
                }
            }
            $redirecturl = remove_query_arg('woo_pr_remove_discount', get_permalink());
            unset($_SESSION["woo_pr_add_discount_value"]);

            //redirect to current page
            wp_redirect($redirecturl);
            exit;
        }

        // If apply discount submit or discout in session
        if ( (isset($_POST['woo_pr_apply_discount']) && !empty($_POST['woo_pr_apply_discount']) )
        || ( isset($_SESSION['woo_pr_add_discount_value']) && !empty($_SESSION['woo_pr_add_discount_value']) ) ) {
            
            if( isset($_REQUEST['add_discount_value']) && !empty($_REQUEST['add_discount_value']) ){
                $_SESSION['woo_pr_add_discount_value'] = $_REQUEST['add_discount_value'];
            }

            // check cartdata not empty
            if (!empty($cartdata)) {

                // Get max discount points from cart
                $cart_max_discount_points = $this->model->woo_pr_get_discount_for_redeeming_points_from_cart( $woocommerce->cart );

                if( !empty($cart_max_discount_points) && $cart_max_discount_points > 0 ){
                    
                    // Calculate discount amount from discount points
                    $cart_max_discount_amount = $this->model->woo_pr_calculate_discount_amount( $cart_max_discount_points );

                    // Conver the amount
                    $cart_max_discount_amount = woo_pr_wcm_currency_convert( $cart_max_discount_amount );

                    $cart_max_discount_amount *= -1;

                    // Add points discount in cart
                    $woocommerce->cart->add_fee( $woo_pr_fee_name, $cart_max_discount_amount, true, 'standard');
                }
            }
        }

    }

    /**
     * Add points to customer for creating an account
     *
     * @since 1.0
     * @Package WooCommerce - Points and Rewards
     */
    public function woo_pr_create_log_account_signup($user_id) {

        $points = get_option('woo_pr_earn_for_account_signup');

        if (!empty($points)) {
            woo_pr_add_points_to_user($points, $user_id);
        }
        $pointslable = $this->model->woo_pr_get_points_label($points);

        $post_data = array(
            'post_title' => sprintf(__('%s for Signup', 'woopoints'), $pointslable),
            'post_content' => sprintf(__('Get %s for signing up new account', 'woopoints'), $pointslable),
            'post_author' => $user_id
        );
        $log_meta = array(
            'userpoint' => $points,
            'events' => 'signup',
            'operation' => 'add' //add or minus
        );

        $this->logs->woo_pr_insert_logs($post_data, $log_meta);
    }

    /**
     * Handle an order that is cancelled or refunded by:
     *
     * 1) Removing any points earned for the order
     *
     * 2) Crediting points redeemed for a discount back to the customer's account if the order that they redeemed the points
     * for a discount on is cancelled or refunded
     *
     * @Package WooCommerce - Points and Rewards
     * @since 1.0
     * @param int $order_id the WC_Order ID
     */
    public function woo_pr_handle_cancelled_refunded_order($order_id) {

        $prefix = WOO_PR_META_PREFIX;

        $order = wc_get_order($order_id);
        $order_id = $order->get_id();
        $order_user_id = $order->get_user_id();

        // bail for guest user
        if (!$order_user_id) {
            return;
        }

        // Get settings to revert points when purchase is refunded
        $woo_pr_revert_points_refund = !empty(get_option('woo_pr_revert_points_refund_enabled')) ? get_option('woo_pr_revert_points_refund_enabled') : '';

        // If payment id is not empty and payment status needs to revert points
        if (!empty($woo_pr_revert_points_refund) && $woo_pr_revert_points_refund == 'yes' && !empty($order_id)) {

            // Get earned points and redeemed points
            $points_earned = get_post_meta( $order_id, $prefix.'points_order_earned', true);
            $points_redeemed = get_post_meta( $order_id, $prefix.'redeem_order', true);
            $check_sell_debited = get_post_meta( $order_id, $prefix.'sell_debited', true);

            // If points earned is not empty
            if (!empty($points_earned)) {

                //points label
                $pointslable = $this->model->woo_pr_get_points_label($points_earned);

                //record data logs for redeem for purchase
                $post_data = array(
                    'post_title'    => sprintf(__(' %s debited for refunded Payment %d', 'woopoints'), $pointslable, $points_earned),
                    'post_content'  => sprintf(__('%s debited for refunded Payment %d', 'woopoints'), $pointslable, $points_earned),
                    'post_author'   => $order_user_id,
                    'post_parent'   => $order_id,
                );
                //log meta array
                $log_meta = array(
                    'order_id'  => $order_id,
                    'userpoint' => $points_earned,
                    'events' => 'refunded_purchase_debited',
                    'operation' => 'minus'//add or minus
                );

                //insert entry in log
                $this->logs->woo_pr_insert_logs($post_data, $log_meta);

                // Deduct points from user points log
                woo_pr_minus_points_from_user($points_earned, $order_user_id);

                $order->add_order_note(sprintf(__('%1$d %2$s debited discount towards to customer.', 'woopoints'), $points_earned, $pointslable));

                // Delete points earned meta
                delete_post_meta($order_id, $prefix.'points_order_earned');
            }

            // If points redeemed is not empty
            if (!empty($points_redeemed)) {

                //points label
                $pointslable = $this->model->woo_pr_get_points_label($points_redeemed);

                //record data logs for redeem for purchase
                $post_data = array(
                    'post_title'    => sprintf(__(' %s credited for refunded Payment %d', 'woopoints'), $pointslable, $order_id),
                    'post_content'  => sprintf(__('%s credited for refunded Payment %d', 'woopoints'), $pointslable, $order_id),
                    'post_author'   => $order_user_id,
                    'post_parent'   => $order_id,
                );
                //log meta array
                $log_meta = array(
                    'order_id'  => $order_id,
                    'userpoint' => $points_redeemed,
                    'events' => 'refunded_purchase_credited',
                    'operation' => 'add'//add or minus
                );

                //insert entry in log
                $this->logs->woo_pr_insert_logs($post_data, $log_meta);

                // Add points from user points log
                woo_pr_add_points_to_user($points_redeemed, $order_user_id);

                // Add order note for Points and Rewards
                $order->add_order_note(sprintf(__('%1$d %2$s credited back to customer.', 'woopoints'), $points_redeemed, $pointslable));

                // Delete points redeemed meta
                delete_post_meta($order_id, $prefix.'redeem_order');
            }

            // Refunded Sell Debited
            $order_points_logs_args = array(
                // 'author'        => $order_user_id,
                'post_parent'   => $order_id,
                'meta_query'    => array(
                                    array(
                                        'key'     => '_woo_log_events',
                                        'value'   => 'earned_sell',
                                    ),
                                )
            );
            //get order logs data
            $order_points_logs = $this->model->woo_pr_get_points( $order_points_logs_args );

            if( !empty( $order_points_logs ) ) { //check user log in not empty
            
                foreach ( $order_points_logs as $key => $value ){
                    
                    $logspointid    = $value['ID'];
                    $post_author    = $value['post_author'];
                    $event          = get_post_meta( $logspointid, '_woo_log_events', true );
                    $event_data     = $this->model->woo_pr_get_events( $event );
                    $sellpoints     = get_post_meta( $logspointid, '_woo_log_userpoint', true );
                    $sellpoints     = str_replace("+", "", $sellpoints );
                    $sellpoints     = str_replace("-", "", $sellpoints );
                    
                    //check event is earned sell and points earned is not empty
                    if( !empty($sellpoints) && ($event == 'earned_sell') ) {

                        //points label
                        $pointslable = $this->model->woo_pr_get_points_label($sellpoints);

                        //record data logs for redeem for purchase
                        $post_data = array(
                            'post_title'    => sprintf(__(' %s debited for refunded Payment %d', 'woopoints'), $pointslable, $sellpoints),
                            'post_content'  => sprintf(__('%s debited for refunded Payment %d', 'woopoints'), $pointslable, $sellpoints),
                            'post_author'   => $post_author,
                            'post_parent'   => $order_id,
                        );
                        //log meta array
                        $log_meta = array(
                            'order_id'  => $order_id,
                            'userpoint' => $sellpoints,
                            'events' => 'refunded_sell_debited',
                            'operation' => 'minus'//add or minus
                        );

                        //insert entry in log
                        $this->logs->woo_pr_insert_logs($post_data, $log_meta);

                        // Deduct points from user points log
                        woo_pr_minus_points_from_user($sellpoints, $post_author);
                    }
                } //end foreach loop

                // Update sell debited
                update_post_meta( $order_id, $prefix.'sell_debited', 'yes' );
            }// End Refunded Sell Debited
        }
    }
    
    /**
     * Show Message for puchase points
     * 
     * Handles to show message for purchasing on 
     * download view page
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_points_message_before_add_to_cart_button( ) {
        
        global $post, $current_user;
        $postid = $post->ID;

        $woo_pr_single_product_message = get_option( 'woo_pr_single_product_message' );
        
        //get earning points for downloads
        $earningpoints = $this->model->woo_pr_get_earning_points( $postid );

        // Don't show message if login user is the owner of product
        if(  $post->post_author == $current_user->ID ) {

            // Owner User Message
            $message = $this->model->woo_pr_owner_product_message( 'product' );
            echo "<p class='woopr-product-message'>".$message."</p>";

        } else if( !empty( $earningpoints ) ) { //check earning points should not empty
            
            // Formatting point amount
            if(is_array($earningpoints)) { //if product is variable then earningpoints contains array of lowest and highest price
                
                $earning_points = '';
                foreach ($earningpoints as $key => $value) {
                    
                    $earning_points .= $value . ' - ';
                }
                
                $earningpoints = trim($earning_points,' - ');
                
            } else {
                $earningpoints = $earningpoints;
            }
            
            //points label
            $points_label = $this->model->woo_pr_get_points_label( $earningpoints );
            
            $points_replace     = array( "{points}","{points_label}" );
            $replace_message    = array( $earningpoints , $points_label );
            $message            = $this->model->woo_pr_replace_array( $points_replace, $replace_message, $woo_pr_single_product_message );
            
            echo "<p class='woopr-product-message'>".$message."</p>";

        } //end if to check earning points should not empty
        
    }

    /**
     * Redeem used points
     * 
     * Handles to Points redeemed towards purchase
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_woocommerce_checkout_process( $order_id, $data ){

        global $current_user;
        $prefix = WOO_PR_META_PREFIX;
        $order = wc_get_order($order_id);
        $point_discount_amount = 0;

        //points plural label
        $plurallable = !empty(get_option('woo_pr_lables_points_monetary_value')) ? get_option('woo_pr_lables_points_monetary_value') : __( 'Points', 'woopoints' );
        $woo_pr_fee_name = $plurallable.__( ' Discount', 'woopoints' );

        // Iterating through order fee items ONLY
        foreach( $order->get_items('fee') as $item_id => $item_fee ){

            // The fee name
            $fee_name = $item_fee->get_name();
            if( strpos($fee_name, $woo_pr_fee_name) !== false ){

                // The fee total amount
                $point_discount_amount += woo_pr_wcm_currency_convert_original( $item_fee->get_total() );
            }

         }

        if( isset($_SESSION['woo_pr_add_discount_value']) && !empty($_SESSION['woo_pr_add_discount_value']) ){

            // remove redeemed points from session 
            unset($_SESSION["woo_pr_add_discount_value"]);
        }
        if ( !empty( $point_discount_amount ) && $point_discount_amount < 0 ) {

            $user_id        = $current_user->ID;
            $current_points = $this->model->woo_pr_calculate_points( $point_discount_amount );
            $points_label   = $this->model->woo_pr_get_points_label( $current_points );
            $log_title      = $points_label.__( ' redeemed towards purchase', 'woopoints' );

            //check number contains minus sign or not
            if (strpos($current_points, '-') !== false) {
                $current_points = str_replace('-', '', $current_points);
            } 

            // Update user points to user account
            woo_pr_minus_points_from_user($current_points, $user_id);

            // Update points redeemed
            update_post_meta( $order_id, $prefix.'redeem_order', $current_points);

            $post_data = array(
                'post_title'    => $log_title,
                'post_content'  => $log_title,
                'post_author'   => $user_id
            );

            $log_meta = array(
                'order_id'  => $order_id,
                'userpoint' => abs($current_points),
                'events'    => 'redeemed_purchase',
                'operation' => 'minus' //add or minus
            );

            $this->logs->woo_pr_insert_logs($post_data, $log_meta);

            // Add order note for Points and Rewards
            $order->add_order_note(sprintf(__('%1$d %2$s debited discount towards to customer.', 'woopoints'), $current_points, $plurallable));
        }

    }
    
    /**
     * Show All Points and Rewards Buttons
     * 
     * Handles to show all Points and Rewards buttons on the viewing page
     * whereever user put shortcode
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     **/
    public function woo_pr_points_history( $content ) {
        
        //check user is logged in or not
        if( is_user_logged_in() ) {
            //show user logs list
            $content .= $this->logs->woo_pr_user_log_list();
        } else {
            
            //points lable
            $content = $this->model->woo_pr_points_guest_points_history_message();
        }
        return $content;
    }

    public function woo_pr_woocommerce_locate_template( $template, $template_name, $template_path ){

    	$_template = $template;
    
	    if ( ! $template_path ) {
			$template_path = WC()->template_path();
		}
	    
	    $plugin_path = WOO_PR_DIR . '/includes/templates/';

	    // Look within passed path within the theme – this is priority    
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);
	    
	    // Modification: Get the template from this plugin, if it exists
	    if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
	        $template = $plugin_path . $template_name;
	    }
	
	    // Use default template
	    if ( ! $template ) {
	        $template = $_template;
	    }

	    // Return what we found
	    return $template;
    }

    /**
     * Awarded Points on User Rated on product.
     *
     * Awarded points rated on product.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.1
     */
    public function woo_pr_rate_on_product( $comment_ID, $comment ) {

        global $woo_pr_log;
        $prefix = WOO_PR_META_PREFIX;

        // get options 
        $woo_pr_enable_reviews = get_option('woo_pr_enable_reviews');
        $woo_pr_review_points = get_option('woo_pr_review_points');

        //Check if review need to do
        if( !empty( $woo_pr_enable_reviews ) && ($woo_pr_enable_reviews=='yes') && !empty( $comment->user_id )
            && isset( $comment->comment_type ) && ( $comment->comment_approved==1 ) ) {

            //Get details
            $rating        = ( isset( $_POST['rating'] ) ) ? trim( $_POST['rating'] ) : null;
            $rating        = wp_filter_nohtml_kses( $rating );
            // Get comment review points
            $comment_review_points = get_comment_meta( $comment->comment_ID, $prefix.'review_points', true );

            //Get points
            $product_review_points = get_post_meta( $comment->comment_post_ID, $prefix."review_points", true );
            $review_points = !empty( $product_review_points[$rating] ) ? $product_review_points[$rating] : '';
            if( empty( $review_points ) ) {

                //Get global points if not at product level
                $review_points = !empty( $woo_pr_review_points[$rating] ) ? $woo_pr_review_points[$rating] : '';
            }

            if( !empty( $review_points ) && empty($comment_review_points) ) {

                // Add points to post author 
                woo_pr_add_points_to_user( $review_points , $comment->user_id );

                // insert add point log
                $post_data = array(
                    'post_title'    => __( 'Points earned for review on product.', 'woopoints' ),
                    'post_content'  => __( 'Points earned for review on product.', 'woopoints' ),
                    'post_author'   =>  $comment->user_id
                );

                $log_meta = array(
                                    'userpoint'     =>  $review_points,
                                    'events'        =>  'earned_product_review',
                                    'operation'     =>  'add'//add or minus
                                );

                //insert entry in log   
                $points_log_id = $woo_pr_log->woo_pr_insert_logs( $post_data, $log_meta );
                // Set review points in comment meta
                update_comment_meta( $comment->comment_ID, $prefix.'review_points', $review_points );
            }
        }
    }

    /**
     * Review status change 
     *
     * Awarded points on product review status make approve.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.1
     */
    public function woo_pr_rate_status_change( $comment_ID, $comment_status ) {

        global $woo_pr_log;
        $prefix = WOO_PR_META_PREFIX;

        // get options 
        $woo_pr_enable_reviews = get_option('woo_pr_enable_reviews');
        $woo_pr_review_points = get_option('woo_pr_review_points');

        // Get comment
        $comment = get_comment( $comment_ID );

        //Check if review need to do
        if( !empty( $woo_pr_enable_reviews ) && ($woo_pr_enable_reviews=='yes') && !empty( $comment->user_id )
            && isset( $comment->comment_type ) && ( $comment->comment_approved==1 ) ) {

            //Get details
            $rating     = get_comment_meta( $comment->comment_ID, 'rating', true );
            $rating     = ( isset( $rating ) ) ? trim( $rating ) : null;
            $rating     = wp_filter_nohtml_kses( $rating );
            // Get comment review points
            $comment_review_points = get_comment_meta( $comment->comment_ID, $prefix.'review_points', true );

            //Get points
            $product_review_points = get_post_meta( $comment->comment_post_ID, $prefix."review_points", true );
            $review_points = !empty( $product_review_points[$rating] ) ? $product_review_points[$rating] : '';
            if( empty( $review_points ) ) {

                //Get global points if not at product level
                $review_points = !empty( $woo_pr_review_points[$rating] ) ? $woo_pr_review_points[$rating] : '';
            }

            if( !empty( $review_points ) && empty($comment_review_points) ) {

                // Add points to post author 
                woo_pr_add_points_to_user( $review_points , $comment->user_id );

                // insert add point log
                $post_data = array(
                    'post_title'    => __( 'Points earned for review on product.', 'woopoints' ),
                    'post_content'  => __( 'Points earned for review on product.', 'woopoints' ),
                    'post_author'   =>  $comment->user_id
                );

                $log_meta = array(
                                    'userpoint'     =>  $review_points,
                                    'events'        =>  'earned_product_review',
                                    'operation'     =>  'add'//add or minus
                                );

                //insert entry in log   
                $points_log_id = $woo_pr_log->woo_pr_insert_logs( $post_data, $log_meta );
                // Set review points in comment meta
                update_comment_meta( $comment->comment_ID, $prefix.'review_points', $review_points );
            }
        }
    }

    /**
     * Added total calculated points to checkout
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.2
     */
    public function woo_pr_review_order_after_cart_contents() {
        
        if( WC()->cart->display_prices_including_tax() ) {

            global $woocommerce;
            $cart_data  = $woocommerce->cart->get_cart();
            $totalpoints = $this->model->woo_pr_get_user_checkout_points( $cart_data );
            echo '<input id="woo_pr_total_points_will_earn" type="hidden" value="'. $totalpoints .'">';
        }
    }

    /**
     * Adding Hooks
     *
     * Adding proper hoocks for the public pages.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function add_hooks() {

        // Add action to create log when new user register.
        add_action('user_register', array($this, 'woo_pr_create_log_account_signup'));

        // Add action to remove points from customer
        add_action('woocommerce_checkout_update_order_meta', array($this, 'woo_pr_woocommerce_checkout_process'), 15, 2 );

        // add action when order status goes to complete or processing
        add_action('woocommerce_order_status_completed', array($this, 'woo_pr_order_processing_completed_update_points'));
        add_action('woocommerce_order_status_processing', array($this, 'woo_pr_order_processing_completed_update_points'));

        // Add action for order cancelled or refunded
        add_action('woocommerce_order_status_refunded', array($this, 'woo_pr_handle_cancelled_refunded_order'));
        add_action('woocommerce_order_status_cancelled', array($this, 'woo_pr_handle_cancelled_refunded_order'));
        add_action('woocommerce_order_status_failed', array($this, 'woo_pr_handle_cancelled_refunded_order'));

        // Add action to add/remove points discount.
        add_action('woocommerce_cart_calculate_fees', array($this, 'woo_pr_redeem_points_add_remove_discount'));

        // Add action to show message for puchase points before cart button
        add_action('woocommerce_before_add_to_cart_button', array($this, 'woo_pr_points_message_before_add_to_cart_button' ));

        // add earn points/redeem points message above cart / checkout
         add_action( 'woocommerce_before_cart', array( $this, 'woo_pr_cart_checkout_message_content' ), 15 );
         add_action( 'woocommerce_before_cart', array( $this, 'woo_pr_redeem_point_markup' ), 16 );
         add_action( 'woocommerce_before_cart', array( $this, 'woo_pr_guest_cart_checkout_message_content' ), 17 );
         add_action( 'woocommerce_before_checkout_form', array( $this, 'woo_pr_cart_checkout_message_content' ), 5 );
         add_action( 'woocommerce_before_checkout_form', array( $this, 'woo_pr_redeem_point_markup' ), 6 );
         add_action( 'woocommerce_before_checkout_form', array( $this, 'woo_pr_guest_cart_checkout_message_content' ), 7 );
        
        //add shortcode to show all Points and Rewards buttons
        add_shortcode( 'woopr_points_history', array( $this, 'woo_pr_points_history' ) );

        // Added woocommerce template filter to override templates from plugin
        add_filter('woocommerce_locate_template', array( $this, 'woo_pr_woocommerce_locate_template' ), 10, 3 );

        // Add action to add filter for add to cart button
        add_action( 'woocommerce_woo_pr_points_add_to_cart', 'woocommerce_woo_pr_points_add_to_cart' );

        //AJAX Call for paging for points log
        add_action( 'wp_ajax_woo_pr_next_page', array( $this->logs, 'woo_pr_user_log_list' ) );
        add_action( 'wp_ajax_nopriv_woo_pr_next_page', array( $this->logs, 'woo_pr_user_log_list' ) );

        //Action to rate on product
        add_action( 'wp_insert_comment', array( $this, 'woo_pr_rate_on_product' ), 10, 2 );
        add_action( 'wp_set_comment_status', array( $this, 'woo_pr_rate_status_change' ), 10, 2 );

        //Action to added total calculated points to checkout
        add_action( 'woocommerce_review_order_after_cart_contents', array( $this, 'woo_pr_review_order_after_cart_contents' ) );
    }
}
