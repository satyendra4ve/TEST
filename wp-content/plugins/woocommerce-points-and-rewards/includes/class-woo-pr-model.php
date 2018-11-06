<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Plugin Model Class
 *
 * Handles generic functionailties
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
class Woo_Pr_Model {

    //class constructor
    public function __construct() {
        
    }

    /**
     * Escape Tags & Slashes
     *
     * Handles escapping the slashes and tags
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_escape_attr($data) {

        return esc_attr(stripslashes($data));
    }

    /**
     * Returns the points label, singular or plural form, based on $points
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_get_points_label( $points ) {

        $singular = !empty(get_option('woo_pr_lables_points')) ? get_option('woo_pr_lables_points') : '';
        $plural = !empty(get_option('woo_pr_lables_points_monetary_value')) ? get_option('woo_pr_lables_points_monetary_value') : '';

        if (1 == $points) {
            return $singular;
        } else {
            return $plural;
        }
    }

    /**
     * Get points
     *
     * @access public
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_get_points($args = array()) {

        $queryargs = array('post_type' => WOO_POINTS_LOG_POST_TYPE, 'post_status' => 'publish');

        $queryargs = wp_parse_args($args, $queryargs);

        //if search is called then retrive searching data
        if (isset($args['search'])) {
            $queryargs['s'] = $args['search'];
        }
        // filter related month
        if (isset($args['monthyear']) && !empty($args['monthyear'])) {
            $queryargs['m'] = $args['monthyear'];
        }
        if (isset($args['author'])) {
            $queryargs['author'] = $args['author'];
        }
        if (isset($args['event'])) {
            $queryargs['meta_query'] = array(
                array(
                    'key' => '_woo_log_events',
                    'value' => $args['event'],
                    'compare' => '=',
                )
            );
        }

        //show how many per page records
        if (isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
            $queryargs['posts_per_page'] = $args['posts_per_page'];
        } else {
            $queryargs['posts_per_page'] = -1;
        }

        //show per page records
        if (isset($args['paged']) && !empty($args['paged'])) {
            $queryargs['paged'] = $args['paged'];
        }

        //fire query in to table for retriving data
        $result = new WP_Query($queryargs);

        if (isset($args['getcount']) && $args['getcount'] == '1') {
            $postslist = $result->post_count;
        } else {
        	//retrived data is in object format so assign that data to array for listing
            $postslist = $this->woo_pr_object_to_array($result->posts);

        	// if get list for deal sales list then return data with data and total array
            if (isset($args['woo_pr_list']) && $args['woo_pr_list']) {

                $data_res = array();

                $data_res['data'] = $postslist;

            	//To get total count of post using "found_posts" and for users "total_users" parameter
                $data_res['total'] = isset($result->found_posts) ? $result->found_posts : '';

                return $data_res;
            }
        }

        return $postslist;
    }

    /**
     * Calculate Points using cartdata
     * 
     * Handles to calculate points using cartdata
     * and return
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_user_checkout_points( $cartdata, $user_id = 0, $order_id = 0 ) {

        global $current_user;
        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');
        $total_points   = $totalcat_earned_point = $result = 0;
        $earning_product_points = $earning_cat_points = $earning_price_points = 0;
        // Set user id
        if( empty($user_id) || $user_id==0 ){
            $user_id = $current_user->ID;
        }

        // If cartdata not empty
        if (!empty($cartdata)) {
            $total_pros     = count( $cartdata );

            foreach ($cartdata as $cart_item) {

                $product_id     = $cart_item['product_id'];
                $quantity       = $cart_item['quantity'];
                $product 		= wc_get_product( $product_id );
                $product_post   = get_post( $product_id );

                if( $product ){

                    // Get product type
                    $pro_type		= $product->get_type();

                    // Don't show message if login user is the owner of product
                    if( $product_post->post_author == $user_id ) {

                        $total_pros--;

                    } else {

                        $earning_product_points = $this->woo_get_product_earn_point($product_id, $quantity);
                        $earning_cat_points 	= $this->woo_get_category_earn_point($product_id, $quantity);

                        if( !empty( $pro_type ) && $pro_type == 'woo_pr_points' ) {

                        	$total_points += $this->woo_pr_get_product_buy_points($product_id, $quantity, $order_id);
                        } else {

        	                if ( $earning_product_points !== '' ) {

        	                    $total_points += $earning_product_points;
        	                }
        	                // Get points from product categories
    	                    else if ( $earning_cat_points !== '' ) {

    	                        $total_points += $earning_cat_points;
    	                    }
                        
        	                // Get points from product price
        	                else {

        	                    if( $product ) {
                                    $product_price = $cart_item['line_total'];

                                    //Check if taxable product and setup display price as inclusive.
                                    if ( $product->is_taxable() && WC()->cart->display_prices_including_tax() ) {
                                        $product_price = wc_get_price_including_tax( $product );
                                    }
                                    
                                    if( !empty($order_id) ){

                                        $order_currency = get_post_meta( $order_id, '_order_currency', true );
                                        if( !empty($order_currency) ){

                                            $product_price = woo_pr_wcm_currency_convert_original( $product_price, $order_currency );
                                        } else {

                                            $product_price = woo_pr_wcm_currency_convert_original( $product_price );
                                        }
                                    } else {

                                        $product_price = woo_pr_wcm_currency_convert_original( $product_price );
                                    }                                    

                                    //Allow third party to modify this price
                                    $product_price = apply_filters('woo_pr_get_cart_product_price', $product_price, $product    );

                                    $earning_price_points   = $this->woo_pr_calculate_earn_points_from_price( $product_price );
                                    $total_points          += $earning_price_points;

        	                    }
        	                }
                        }
                    }
                }
            } //end foreach loop
        }

        if( !empty( $total_pros ) ) {

            // Apply decimal if enabled
            if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
                return $total_points = round( $total_points, $woo_pr_number_decimal );
            }
            //return total points user will get
            return intval( $total_points );
        } else {

            return 'user_product';
        }
    }

    public function woo_get_category_earn_point( $product_id, $quantity = 1 ) {

        $totalcat_earned_point = '';

        // get points of download are set in category level
        $category_earn_points = $this->woo_pr_get_category_earn_points($product_id); 
        if( $category_earn_points !== '' ){

            $totalcat_earned_point = $category_earn_points * $quantity;
        }
        return $totalcat_earned_point;
    }

    public function woo_get_product_earn_point( $product_id, $quantity = 1 ) {

        $totalproduct_earned_point = '';

        // get points for download level from meta box
        $product_earn_points = $this->woo_pr_get_product_earn_points($product_id);

        if( $product_earn_points !== '' ) {

        	$totalproduct_earned_point = $product_earn_points * $quantity;
        }

        return $totalproduct_earned_point;
    }

    /**
	 * Get Points for Buying Points
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_product_buy_points( $product_id, $quantity = 1, $order_id = 0 ) {

        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');
    	$totalproduct_earned_point = 0;
        $product = wc_get_product( $product_id ); // Get product

        if( $product ){

            // Get product price
            $pro_price = $product->get_price();

            // get points for download level from meta box
            if( !empty( $pro_price ) ){

                // Convert price in original currency
                $pro_price = woo_pr_wcm_currency_convert_original( $pro_price );

                // Get earning points from settings page
    	        $points = !empty(get_option('woo_pr_buy_points')) ? get_option('woo_pr_buy_points') : 1;
    	
    	        // Get earning ration from settings page
    	        $rate 	= !empty(get_option('woo_pr_buy_points_monetary_value')) ? get_option('woo_pr_buy_points_monetary_value') : 1;

                // Apply decimal if enabled
                if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
                    $totalproduct_earned_point = $quantity * round($pro_price * ( $points / $rate ), $woo_pr_number_decimal);
                } else {
                    $totalproduct_earned_point = $quantity * round($pro_price * ( $points / $rate ));
                }
            }
        }

        return $totalproduct_earned_point;
    }

    /**
     * Replace First Array to Second Array in message
     * 
     * Handles to replace one array to another array 
     * in particular message
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_replace_array($searcharr, $replacearr, $message) {

        $message = str_replace($searcharr, $replacearr, $message);
        return $message;
    }

    /**
     * Calculate Points via Disocunted Amount
     * 
     * Handles to calculate points
     * via discounted amount
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_calculate_earn_points_from_price($amount) {

        // Get earning points from settings page
        $points = !empty(get_option('woo_pr_ratio_settings_points')) ? get_option('woo_pr_ratio_settings_points') : '';

        // Get earning ration from settings page
        $rate = !empty(get_option('woo_pr_ratio_settings_points_monetary_value')) ? get_option('woo_pr_ratio_settings_points_monetary_value') : '';

        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');

        if (empty($points) || empty($rate)) {
            return 0;
        }

        // Apply decimal if enabled
        if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
            return round($amount * ( $points / $rate ), $woo_pr_number_decimal );
        }

        return round($amount * ( $points / $rate ));
    }

    /**
     * Calculate Points for Discount
     * 
     * Handles to calculate discount for points
     * 
     * @package Easy Digital Downloads - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_calculate_points($discount_amount) {

        // Get earning points and rate from settings page
        $points = !empty(get_option('woo_pr_redeem_points')) ? get_option('woo_pr_redeem_points') : '';
        $rate = !empty(get_option('woo_pr_redeem_points_monetary_value')) ? get_option('woo_pr_redeem_points_monetary_value') : '';

        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');

        if (empty($points) || empty($rate)) {
            return 0;
        }

        // Apply decimal if enabled
        if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
            return round( ($discount_amount * ( $points / $rate )), $woo_pr_number_decimal );
        }

        return floor($discount_amount * ( $points / $rate ));
    }

    /**
     * Calculate for Discount Amount
     * 
     * Handles to calculate discount for amount from discount points
     * 
     * @package Easy Digital Downloads - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_calculate_discount_amount($discount_points) {

        $points = !empty(get_option('woo_pr_redeem_points')) ? get_option('woo_pr_redeem_points') : '';
        // Get earning points from settings page
        $rate = !empty(get_option('woo_pr_redeem_points_monetary_value')) ? get_option('woo_pr_redeem_points_monetary_value') : '';
        if (empty($points) || empty($rate)) {
            return 0;
        }

        return ($discount_points * ( $rate / $points ));
    }

    /**
     * Calculate Maximum Possible discount available
     *
     * Handles to calculate maximum possible discount
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_discount_for_redeeming_points_from_cart( $cart ) {

        global $current_user;
        $cartsubtotal   = $cart->subtotal;
        $cartdata       = $cart->get_cart();

        $userid = $current_user->ID;
        $available_user_points = $this->woo_pr_get_userpoints_value();
        //get user points from user account
        $user_points = get_user_meta($userid, WOO_PR_META_PREFIX.'userpoints', true);

        //user points
        $available_user_disc = !empty($user_points) ? $user_points : '0';

        //check user has points or not
        if (empty($available_user_disc) || $available_user_disc <= 0) {
            return 0;
        } else {
            $available_user_disc = $this->woo_pr_calculate_discount_amount( $available_user_disc );
        }
        $discount_applied = 0;

        if (!empty($cartdata)) {
            foreach ($cartdata as $cart_item) {
                //max discount
                $max_discount = $this->woo_pr_get_max_points_discount_for_product($cart_item['product_id']);

                if (is_numeric($max_discount)) {

                    // adjust the max discount by the quantity being ordered
                    $max_discount *= $cart_item['quantity'];

                } else {

                    //when maximum discount is not set for product then allow maximum total cost of product as a discount
                    $max_discount = woo_pr_wcm_currency_convert_original( $cart_item['line_total'] );
                }
                $discount_applied += $max_discount;
            }
        }

        if ($discount_applied >= $cartsubtotal) {
            $discount_applied = max(0, min($discount_applied, $cartsubtotal));
        }

        // limit the discount available by the global maximum discount if set
        $cart_max_discount = !empty( get_option('woo_pr_cart_max_discount') ) ? get_option('woo_pr_cart_max_discount') : '';

        if ( !empty($cart_max_discount) && ( $cart_max_discount < $discount_applied) ){
            $discount_applied = $cart_max_discount;
        }

        // if the discount available is greater than the max discount, apply the max discount
        $discount_applied = ( $available_user_disc <= $discount_applied ) ? $available_user_disc : $discount_applied;

        return $this->woo_pr_calculate_points( $discount_applied );
    }

    /**
     * Calculate Maximum Possible discount available
     *
     * Handles to calculate maximum possible discount
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_discount_for_redeeming_points() {

        global $woocommerce, $current_user;
        
        return $this->woo_pr_get_discount_for_redeeming_points_from_cart( $woocommerce->cart );
    }

    /**
     * Calculate Maximum Possible discount available after order
     *
     * Handles to calculate maximum possible discount
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_discount_for_redeeming_points_order($order_id) {

        global $woocommerce, $current_user;

        $order                  = new WC_Order($order_id);
        $user_id                = $order->get_user_id();
        $cartsubtotal           = $order->get_subtotal();
        $items                  = $order->get_items();
        $userid                 = $current_user->ID;
        $available_user_points  = $this->woo_pr_get_userpoints_value();
        //get user points from user account
        $user_points            = get_user_meta($userid, WOO_PR_META_PREFIX.'userpoints', true);
        //user points
        $available_user_disc    = !empty($user_points) ? $user_points : '0';


        //check user has points or not
        if (empty($available_user_disc) || $available_user_disc <= 0) {
            return 0;
        }
        $discount_applied = 0;

        if (!empty($items)) {
            foreach ($items as $cart_item) {
                //max discount
                $max_discount = $this->woo_pr_get_max_points_discount_for_product($cart_item['product_id']);
                if (is_numeric($max_discount)) {

                    // adjust the max discount by the quantity being ordered
                    $max_discount *= $cart_item['quantity'];

                } else {

                    //when maximum discount is not set for product then allow maximum total cost of product as a discount
                    $max_discount_amount    = $cart_item['line_total'];
                    $max_discount           = $this->woo_pr_calculate_points( $max_discount_amount );
                }
                // if the discount available is greater than the max discount, apply the max discount
                $discount = ( $available_user_disc <= $max_discount ) ? $available_user_disc : $max_discount;

                $discount_applied += $discount;
            }
        }
        if ($discount_applied <= $cartsubtotal) {
            $discount_applied = max(0, min($discount_applied, $cartsubtotal));
        }

        $discount_applied = ( $available_user_disc <= $discount_applied ) ? $available_user_disc : $discount_applied;

        return $discount_applied;
    }

    /**
     * Calculate Max Discount Points for download
     * 
     * Handles to return max discounted point for particular downloads
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_max_points_discount_for_product($productid) {

        $max_discount = $max_product_discount = $max_category_discount = '';

        //get product level max points
        $max_product_discount = $this->woo_pr_max_discount($productid);
        $max_category_discount = $this->woo_pr_get_category_max_discount($productid);

        if ( $max_product_discount !== '' ) {
            return $max_product_discount;
        }

        if ($max_category_discount != '') {
            return $max_category_discount;
        }

        // get global maximum disocunt grom settings page
        if ($max_product_discount !== '' && $max_category_discount !== '') {
            $max_discount = !empty(get_option('woo_pr_per_product_max_discount')) ? get_option('woo_pr_per_product_max_discount') : '';
        }
        if ( $max_discount != '') {
            return woo_pr_wcm_currency_convert( $max_discount );
        }

        return '';
    }

    /**
     * Get Category Max Dicount
     * 
     * Handles to return maximum discount for category
     * 
     * @package Easy Digital Downloads - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_category_max_discount($productid) {

        $prefix         = WOO_PR_META_PREFIX;
        $maxdiscount    = '';
        $terms          = get_the_terms($productid, 'product_cat');

        if( !empty($terms) ){
            foreach ($terms as $term) {
                $cat_id         = $term->term_id;
                $cat_discount   = get_woocommerce_term_meta($cat_id, $prefix."rewards_max_point_disc");
                
                if( $cat_discount !== '' && $cat_discount > $maxdiscount ){
                    $maxdiscount    = $cat_discount;
                }
            }
        }

        $maxdiscount = $maxdiscount !== '' ? $maxdiscount : '';

        return $maxdiscount;
    }

    /**
     * Max Discount for Product
     * 
     * Handles to return max discount for download
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_max_discount($productid) {

        $prefix = WOO_PR_META_PREFIX;

        $product_max_point  = get_post_meta($productid, $prefix.'rewards_max_point_disc', true);
        $max_discount       = $product_max_point !== '' ? $product_max_point : '';

        return $max_discount;
    }

    /**
     * Calculate Discount From User Points
     * 
     * Handles to calculate value from user points
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_userpoints_value() {

        //get user total points
        $userpoints = woo_pr_get_user_points();

        // Get redemption points from settings page
        $points = intval(get_option('woo_pr_redeem_points'));

        // Get redemption ration from settings page
        $rate = intval(get_option('woo_pr_redeem_points_monetary_value'));

        if (empty($points) || empty($rate)) {
            return 0;
        }

        return ( $userpoints * ( $rate / $points ) );
    }

    /**
     * Get Product Earning Points 
     * 
     * Handles to return earning points for product
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_earning_points($productid) {

        global $current_user;

        // get product data
        $product_data   = get_post($productid);
        $product        = wc_get_product( $productid );
        $pro_type       = $product->get_type();

        // Don't show message if the user is vendor of download except only admin
        if ($product_data->post_author == $current_user->ID )
            return;

        $earningpointsbyuser = 0;

        //if not is checkout page
        if( !empty( $pro_type ) && $pro_type == 'woo_pr_points' ) { //check product type is points 

            $woo_price = $this->woo_pr_get_product_buy_points($productid);
            //calculate the earn points from price
            $earningpointsbyuser = $this->woo_pr_calculate_earn_points_from_price($woo_price);

        } else {

            if ( !empty( $pro_type ) && $pro_type == 'variable' ) { //check product type is varible
                
                $woo_price_min = $product->get_variation_price('min');
                $woo_price_max = $product->get_variation_price('max');
                $woo_price[0] = woo_pr_wcm_currency_convert_original( $woo_price_min );
                $woo_price[1] = woo_pr_wcm_currency_convert_original( $woo_price_max );
                
            } else {
                //get download price
                $woo_price = $product->get_price();
                $woo_price = woo_pr_wcm_currency_convert_original( $woo_price );
                
            } //end else

            //get download points for download level from meta box
            $downloadearnpoints = $this->woo_pr_get_product_earn_points($productid);

            if (is_numeric($downloadearnpoints)) {
                return $downloadearnpoints;
            }

            //check if points of download are set in category level
            $downloadearnpoints = $this->woo_pr_get_category_earn_points($productid);

            if (is_numeric($downloadearnpoints)) {
                return $downloadearnpoints;
            }

            if (is_array($woo_price)) { // if product is variable then woo_pr_price contains array of lowest and highest price
                $earning_points_by_user = array();
                foreach ($woo_price as $key => $data) {

                    $earning_points_by_user[$key] = $this->woo_pr_calculate_earn_points_from_price($data);
                }
                return $earning_points_by_user;
            } else { // if product is simple product
                //calculate the earn points from price
                $earningpointsbyuser = $this->woo_pr_calculate_earn_points_from_price($woo_price);
            }
        }

        // get download points based on global setting 
        return $earningpointsbyuser;
    }

    /**
     * Get Category Max Discount
     * 
     * Handles to get category max discount
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_category_earn_points($productid) {
        
        $prefix         = WOO_PR_META_PREFIX;
        $cat_points     = '';
        $all_cat_points = array();
        $productterms   = wp_get_post_terms($productid, 'product_cat');

        if (!empty($productterms)) {
            foreach ($productterms as $term) {
                $product_cat_id     = $term->term_id;
                $product_term_point = get_woocommerce_term_meta( $product_cat_id, $prefix."rewards_earn_point");
                $product_term_point = $product_term_point !== '' ? $product_term_point : '';

                if( $product_term_point !== '' ){
                    $all_cat_points[]   = $product_term_point;
                }
            }
        }

        //when download being assigned to multiple categoriew with different earned points, return biggest value of points
        // Get max point from all category points of product
        if ( !empty($all_cat_points) ) {
            $cat_points = max($all_cat_points);
        }

        return $cat_points;
    }

    /**
     * Get Download Points from Meta Box
     * 
     * Handles to get download points from Meta Box
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_get_product_earn_points($productid) {

        $prefix = WOO_PR_META_PREFIX;

        // Get earn points from metabox
        $earnedpoints = get_post_meta($productid, $prefix.'rewards_earn_point', true);

        return $earnedpoints;
    }

    /**
     * Convert Object To Array
     *
     * Converting Object Type Data To Array Type
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * 
     */
    public function woo_pr_object_to_array($result) {
        $array = array();
        foreach ($result as $key => $value) {
            if (is_object($value)) {
                $array[$key] = $this->woo_pr_object_to_array($value);
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * Stripslashes 
     * 
     * It will strip slashes from the content
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_escape_slashes_deep($data = array(), $flag = false) {

        if ($flag != true) {
            $data = $this->woo_pr_nohtml_kses($data);
        }
        $data = stripslashes_deep($data);
        return $data;
    }

    /**
     * Return Time
     * 
     * Handles to return formated time
     * from specific timestamp
     *  
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_log_time($timestamp, $type = '') {

        // calculate the diffrence 
        $timediff = time() - $timestamp;
        $returndate = '';

        if ($timediff < 3600) {

            if ($timediff < 60) { //if ($timediff < 120) { 
            //$returndate = "less than a minute ago.";
                $returndate = $timediff . __(' seconds ago', 'woopoints');
            } else if ($timediff > 60 && $timediff < 120) {
                $returndate = ' ' . __('about a minute ago', 'woopoints');
            } else {
                $returndate = intval($timediff / 60) . ' ' . __('minutes ago', 'woopoints');
            }
        } else if ($timediff < 7200) {

            $returndate = ' ' . __('1 hour ago', 'woopoints');
        } else if ($timediff < 86400) {

            $returndate = intval($timediff / 3600) . ' ' . __('hours ago', 'woopoints');
        } else if ($timediff < 172800) {

            $returndate = ' ' . __('1 day ago', 'woopoints');
        } else if ($timediff < 604800) {

            $returndate = intval($timediff / 86400) . ' ' . __('days ago', 'woopoints');
        } else if ($timediff < 1209600) {

            $returndate = ' ' . __('1 week ago', 'woopoints');
        } else if ($timediff < 3024000) {

            $returndate = intval($timediff / 604900) . ' ' . __('weeks ago', 'woopoints');
        } else {

            $returndate = @date('d.m.Y', $timestamp);

            if ($type == "fulldate") {

                $returndate = @date('d.m.Y H:i', $timestamp);
            } else if ($type == "time") {

                $returndate = @date('H:i', $timestamp);
            }
        }
        return $returndate;
    }

    /**
     * Get Events from Slug
     * 
     * Handles to get points log event from slug
     *  
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_get_events($event = '') {

        //points plural label
        $plurallable = !empty(get_option('woo_pr_lables_points_monetary_value')) ? get_option('woo_pr_lables_points_monetary_value') : '';
        $value = '';
        switch ($event) {

            case 'earned_purchase' :
                $value = sprintf(__('%s earned for purchase', 'woopoints'), ucfirst($plurallable));
                break;

            case 'earned_sell' :
                $value = sprintf(__('%s earned for sell', 'woopoints'), ucfirst($plurallable));
                break;

            case 'redeemed_purchase' :
                $value = sprintf(__('%s redeemed towards purchase', 'woopoints'), ucfirst($plurallable));
                break;

            case 'signup' :
                $value = sprintf(__('%s earned for account signup', 'woopoints'), ucfirst($plurallable));
                break;

            case 'reset_points' :
                $value = sprintf(__('%s reset', 'woopoints'), ucfirst($plurallable));
                break;

            case 'earned_product_review' :
                $value = sprintf(__('%s earned by review a product', 'woopoints'), ucfirst($plurallable));
                break;

            case 'refunded_purchase_debited' :
                $value = sprintf(__('Earned %s debited towards purchase refund', 'woopoints'), ucfirst($plurallable));
                break;

            case 'refunded_sell_debited' :
                $value = sprintf(__('Seller Earned %s debited towards purchase refund', 'woopoints'), ucfirst($plurallable));
                break;

            case 'refunded_purchase_credited' :
                $value = sprintf(__('Redeemed %s credited back towards purchase refund', 'woopoints'), ucfirst($plurallable));
                break;

            default:
                break;
        }
        return apply_filters('woo_pr_get_events', $value, $event);
    }

    /**
     * Get Events order id link
     * 
     * Handles to get points log event order id link for backend
     *  
     * @package WooCommerce - Points and Rewards
     * @since 1.0.2
     */
    public function woo_pr_get_event_order_link($item, $order_id='') {

        $order_link = '';
        $order = wc_get_order($order_id);
        if( !empty($order_id) && !empty($order) ){
            $order_link = sprintf(__('%sOrder ID: %s', 'woopoints'), '<br><strong>', '</strong><a href="' . esc_url(admin_url('post.php?post=' . absint($order_id) . '&action=edit')) . '">' . $order_id . '</a>');
        }
        return apply_filters('woo_pr_get_event_order_link', $order_link, $item );
    }

    /**
     * Get Events order id link
     * 
     * Handles to get points log event order id link for frontend
     *  
     * @package WooCommerce - Points and Rewards
     * @since 1.0.2
     */
    public function woo_pr_get_event_user_order_link( $order_id='', $logspointid='' ) {

        global $current_user;

        $order_link = '';
        $order = wc_get_order($order_id);
        if( !empty($order_id) && !empty($order) ){

            $customer_id = $order->get_user_id();
            // Show order link if is customer
            if(  $customer_id == $current_user->ID ) {
                $order_link = sprintf(__('%sOrder ID: %s', 'woopoints'), '<br>', '<a href="' . esc_url( $order->get_view_order_url() ) . '">#' . $order_id . '</a>');
            }
        }

        return apply_filters('woo_pr_get_event_user_order_link', $order_link, $logspointid );
    }

    /**
     * Strip Html Tags 
     * 
     * It will sanitize text input (strip html tags, and escape characters)
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_nohtml_kses($data = array()) {


        if (is_array($data)) {

            $data = array_map(array($this, 'woo_pr_nohtml_kses'), $data);
        } elseif (is_string($data)) {

            $data = wp_filter_nohtml_kses($data);
        }

        return $data;
    }

    /**
     * Guest User Message for Points History Message
     * 
     * Handles to return guest user points
     * history message
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     **/
    public function woo_pr_points_guest_points_history_message( ) {
        
        //message 
        $message = '';
        $guestmessage = get_option('woo_pr_guest_user_history_message');
        
        //check guest user points history message is not empty
        if( !empty( $guestmessage ) ) {

            //points lable
            $points_label       = $this->woo_pr_get_points_label( 1 );
            
            $points_replace     = array( "{points_label}" );
            $replace_message    = array( $points_label );
            $message            = $this->woo_pr_replace_array( $points_replace, $replace_message, $guestmessage );
            
        }//check guest message is not empty & points not empty
        
        //return message
        return $message;
    }

    /**
     * Owner User Message
     * 
     * Handles return message when owner buying own product
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     **/
    public function woo_pr_owner_product_message( $page_name = 'product' ) {

        // Set message
        $message = __( 'You won\'t generate any points for buying your own product.', 'woopoints' );
        if( $page_name == 'product' ){

            // wrap with info div
            "<div class='woopr-product-message'>".$message."</div>";

        } else {
            // wrap with info div
            $message = '<div class="woocommerce-info woo-pr-own-product-message">' . $message . '</div>';
        }

        //return message
        return apply_filters( 'woo_pr_owner_product_message', $message );
    }

}

?>