<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Misc Functions
 * 
 * All misc functions handles to 
 * different functions 
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 *
 */

/**
 * Get Current User / Passed User ID Points
 * 
 * Handles to get total points of current user / passed user id
 * and return
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 * */
function woo_pr_get_user_points($userid = '') {

    global $current_user;

    //check userid is empty then use current user id
    if (empty($userid))
        $userid = $current_user->ID;

    //get user points from user account
    $user_points = get_user_meta($userid, WOO_PR_META_PREFIX.'userpoints', true);

    //user points
    $user_points = !empty($user_points) ? $user_points : '0';

    return $user_points;
}

/**
 * Add Points to user account
 * 
 * Handles to add points to user account
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 * */
function woo_pr_add_points_to_user($points = 0, $userid = '') {

    global $current_user;

    //check userid is empty then use current user id
    if (empty($userid))
        $userid = $current_user->ID;

    //check points should not empty
    if (!empty($points)) {

        //get user current points
        $user_points = woo_pr_get_user_points($userid);

        //update users points for signup
        update_user_meta($userid, WOO_PR_META_PREFIX.'userpoints', ( $user_points + $points));
    } // end if to check points should not empty
}

/**
 * Minus / Decrease Points from user account
 * 
 * Handles to minus / decrease points from user account
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 * */
function woo_pr_minus_points_from_user($points = 0, $userid = '') {

    global $current_user;

    //check userid is empty then use current user id
    if (empty($userid))
        $userid = $current_user->ID;

    //check points should not empty
    if (!empty($points)) {

        //get user current points
        $user_points = woo_pr_get_user_points($userid);

        //update users points for signup
        update_user_meta($userid, WOO_PR_META_PREFIX.'userpoints', ( $user_points - $points));
    } // end if to check points should not empty
}

if ( ! function_exists( 'woocommerce_woo_pr_points_add_to_cart' ) ) {

	/**
	 * Output the simple product add to cart area.
	 */
	function woocommerce_woo_pr_points_add_to_cart() {
		wc_get_template( 'single-product/add-to-cart/points.php' );
	}
}

/**
 * Get 
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.3
 * */
function woo_pr_wcm_currency_convert_original( $amount, $current_currency = '') {

    if( class_exists('WOOMULTI_CURRENCY') ){
        
        $wcm_currency_setting = new WOOMULTI_CURRENCY_Data();
        /*Check currency*/
        $selected_currencies = $wcm_currency_setting->get_list_currencies();
        $default_currency = $wcm_currency_setting->get_default_currency();

        if( empty($current_currency) || !isset($selected_currencies[$current_currency]) ){
            $current_currency    = $wcm_currency_setting->get_current_currency();
        }

        if ( ! $current_currency ) {

            return $amount;
        }
        if ( $current_currency == $default_currency ) {

            return $amount;
        }

        if ( $amount ) {

            if ( $current_currency && isset( $selected_currencies[$current_currency] ) ) {
                $amount = $amount / $selected_currencies[$current_currency]['rate'];
            }

        }
    }

    return $amount;
}

/**
 * 
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.3
 * */
function woo_pr_wcm_currency_convert( $amount, $current_currency = '') {

    if( class_exists('WOOMULTI_CURRENCY') ){
        
        $wcm_currency_setting = new WOOMULTI_CURRENCY_Data();
        /*Check currency*/
        $selected_currencies = $wcm_currency_setting->get_list_currencies();

        if( empty($current_currency) || !isset($selected_currencies[$current_currency]) ){
            $current_currency    = $wcm_currency_setting->get_current_currency();
        }

        if ( ! $current_currency ) {

            return $amount;
        }

        if ( $amount ) {

            if ( $current_currency && isset( $selected_currencies[$current_currency] ) ) {
                $amount = $amount * $selected_currencies[$current_currency]['rate'];
            }

        }
    }

    return $amount;
}
