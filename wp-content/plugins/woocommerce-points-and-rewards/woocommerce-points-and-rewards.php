<?php
/**
 * Plugin Name: WooCommerce - Points and Rewards
 * Plugin URI: http://wpweb.co.in/
 * Description: With Points and Rewards Extension, you can reward customers for purchases and other actions with points which can be redeemed for discounts.
 * Version: 1.0.3
 * Author: WPWeb
 * Author URI: http://wpweb.co.in
 * Text Domain: woopoints
 * Domain Path: languages
 * 
 * WC tested up to: 3.4.1
 *
 * @package WooCommerce - Points and Rewards
 * @category Core
 * @author WPWeb
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions 
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
if (!defined('WOO_PR_DIR')) {
    define('WOO_PR_DIR', dirname(__FILE__));      // Plugin dir
}
if (!defined('WOO_PR_VERSION')) {
    define('WOO_PR_VERSION', '1.0.3');      // Plugin Version
}
if (!defined('WOO_PR_URL')) {
    define('WOO_PR_URL', plugin_dir_url(__FILE__));   // Plugin url
}
if (!defined('WOO_PR_INC_DIR')) {
    define('WOO_PR_INC_DIR', WOO_PR_DIR . '/includes');   // Plugin include dir
}
if (!defined('WOO_PR_INC_URL')) {
    define('WOO_PR_INC_URL', WOO_PR_URL . 'includes');    // Plugin include url
}
if (!defined('WOO_PR_ADMIN_DIR')) {
    define('WOO_PR_ADMIN_DIR', WOO_PR_INC_DIR . '/admin');  // Plugin admin dir
}
if (!defined('WOO_PR_PREFIX')) {
    define('WOO_PR_PREFIX', 'woo_pr'); // Plugin Prefix
}
if (!defined('WOO_PR_VAR_PREFIX')) {
    define('WOO_PR_VAR_PREFIX', 'woo_pr'); // Variable Prefix
}
if( !defined( 'WOO_PR_META_PREFIX' ) ) {
    define( 'WOO_PR_META_PREFIX', '_woo_pr_' ); // meta box prefix
}
if (!defined('WOO_POINTS_IMG_URL')) {
    define('WOO_POINTS_IMG_URL', WOO_PR_URL . 'includes/images'); // plugin image url
}
if (!defined('WOO_POINTS_BASENAME')) {
    define('WOO_POINTS_BASENAME', basename(WOO_PR_DIR)); //points and rewards basename
}
if (!defined('WOO_POINTS_LOG_POST_TYPE')) {
    define('WOO_POINTS_LOG_POST_TYPE', 'woopointslog'); //post type for points log
}
if (!defined('WOO_POINTS_PLUGIN_KEY')) {
	define('WOO_POINTS_PLUGIN_KEY', 'woopar');
}
// Required Wpweb updater functions file
if ( ! function_exists( 'wpweb_updater_install' ) ) {
	require_once( 'includes/wpweb-upd-functions.php' );
}

/**
 * Admin notices
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
*/
function woo_pr_activation_admin_notices() {
    
    if ( ! class_exists( 'Woocommerce' ) ) {
        
        echo '<div class="error">';
        echo "<p><strong>" . __( 'Woocommerce needs to be activated to be able to use the Points and Rewards.', 'woopoints' ) . "</strong></p>";
        echo '</div>';
    }
}

/**
 * Check Woocommerce Plugin
 *
 * Handles to check Woocommerce plugin
 * if not activated then deactivate our plugin
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
function woo_pr_woocommerce_check_activation() {
    
    if ( ! class_exists( 'Woocommerce' ) ) {
        // is this plugin active?
        if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
            // deactivate the plugin
            deactivate_plugins( plugin_basename( __FILE__ ) );
            // unset activation notice
            unset( $_GET[ 'activate' ] );
            // display notice
            add_action( 'admin_notices', 'woo_pr_activation_admin_notices' );
        }
    }
}
//Check Woocommerce plugin is Activated or not
add_action( 'admin_init', 'woo_pr_woocommerce_check_activation' );

/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
function woo_pr_load_text_domain() {

    // Set filter for plugin's languages directory
    $woo_pr_lang_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
    $woo_pr_lang_dir = apply_filters('woo_pr_languages_directory', $woo_pr_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'woopoints');
    $mofile = sprintf('%1$s-%2$s.mo', 'woopoints', $locale);

    // Setup paths to current locale file
    $mofile_local = $woo_pr_lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/' . WOO_POINTS_BASENAME . '/' . $mofile;

    if (file_exists($mofile_global)) { // Look in global /wp-content/languages/woocommerce-points-and-rewards folder
        load_textdomain('woopoints', $mofile_global);
    } elseif (file_exists($mofile_local)) { // Look in local /wp-content/plugins/woocommerce-points-and-rewards/languages/ folder
        load_textdomain('woopoints', $mofile_local);
    } else { // Load the default language files
        load_plugin_textdomain('woopoints', false, $woo_pr_lang_dir);
    }
}

/**
 * Add plugin action links
 *
 * Adds a Settings, Support and Docs link to the plugin list.
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
function woo_pr_add_plugin_links( $links ) {
    $plugin_links = array(
		'<a href="admin.php?page=wc-settings&tab=woopr-settings">' . __( 'Settings', 'woopoints' ) . '</a>',
		'<a href="http://support.wpweb.co.in/">' . __( 'Support', 'woopoints' ) . '</a>',
		'<a href="http://wpweb.co.in/documents/woocommerce-points-and-rewards/">' . __( 'Docs', 'woopoints' ) . '</a>'
    );

    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_pr_add_plugin_links' );

// loads the admin functions file
require_once ( WOO_PR_INC_DIR . '/admin/woo-pr-admin-function.php' );
// Registring Post type functionality
require_once( WOO_PR_INC_DIR . '/woo-pr-post-type.php' );

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
register_activation_hook(__FILE__, 'woo_pr_install');

function woo_pr_install() {
    
    //register post type
    woo_pr_register_post_types();
    
    //IMP Call of Function
    //Need to call when custom post type is being used in plugin
    flush_rewrite_rules();

    //get option for when plugin is activating first time
    $woo_pr_set_option = get_option( 'woo_pr_set_option' );
    
    if( empty( $woo_pr_set_option ) ) { //check plugin version option
        
        //update default options
        woo_pr_default_settings();
        
        //update plugin version to option
        update_option( 'woo_pr_set_option', '1.0' );
        update_option( 'woo_pr_plugin_version', WOO_PR_VERSION );
    }
}


/**
 * Default Settings
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
function woo_pr_default_settings() {

    //Items need to set 
    $options = array(
        'woo_pr_ratio_settings_points_monetary_value'   => '1',
        'woo_pr_ratio_settings_points'                  => '1',
        'woo_pr_redeem_points_monetary_value'           => '1',
        'woo_pr_redeem_points'                          => '100',
        'woo_pr_buy_points_monetary_value'              => '1',
        'woo_pr_buy_points'                             => '100',
        'woo_pr_lables_points'                          => __( 'Point', 'woopoints' ),
        'woo_pr_lables_points_monetary_value'           => __( 'Points', 'woopoints' ),
        'woo_pr_selling_points_monetary_value'          => '1',
        'woo_pr_selling_points'                         => '1',
        'woo_pr_cart_max_discount'                      => '',
        'woo_pr_per_product_max_discount'               => '',
        'woo_pr_single_product_message'                 => sprintf(__('Purchase this product now and earn %s!', 'woopoints'), '<strong>{points}</strong> {points_label}'),
        'woo_pr_earn_points_cart_message'               => sprintf(__('Complete your order and earn %s for a discount on a future purchase', 'woopoints'), '<strong>{points}</strong> {points_label}'),
        'woo_pr_redeem_points_cart_message'             => sprintf(__('Use %s for a %s discount on this order!', 'woopoints'), '<strong>{points}</strong> {points_label}', '<strong>{points_value}</strong>'),
        'woo_pr_guest_checkout_page_message'            => sprintf(__('You need to register an account in order to earn %s', 'woopoints'), ' <strong>{points}</strong> {points_label}'),
        'woo_pr_guest_checkout_page_buy_message'        =>  sprintf(__('You need to register an account in order to fund %s into your account.', 'woopoints'), ' <strong>{points}</strong> {points_label}'),
        'woo_pr_guest_user_history_message'             => sprintf(__('Sorry, You have not earned any %s yet.', 'woopoints'), '<strong>{points_label}</strong>'),
        'woo_pr_earn_for_account_signup'                => '500',
        'woo_pr_revert_points_refund_enabled'           => 'no',
        'woo_pr_delete_options'                         => 'no',
        'woo_pr_enable_decimal_points'                  => 'no',
        'woo_pr_number_decimal'                         => 2,
        
    );
    foreach ($options as $key => $value) {
        update_option( $key, $value );
    }
}

/**
 * Deactivation Hook
 *
 * Register plugin deactivation hook.
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
register_deactivation_hook(__FILE__, 'woo_pr_uninstall');

function woo_pr_uninstall() {
    global $wpdb;

    // Get prefix
    $prefix = WOO_PR_META_PREFIX;

    // Getting delete option
    $woo_pr_delete_options = get_option('woo_pr_delete_options');

    // If option is set
    if (isset($woo_pr_delete_options) && !empty($woo_pr_delete_options) && $woo_pr_delete_options == 'yes') {

        //delete custom main post data
        $queryargs = array( 'post_type' => WOO_POINTS_LOG_POST_TYPE, 'post_status' => 'any' , 'numberposts' => '-1' );
        $queryargsdata = get_posts( $queryargs );
        
        //delete all points log posts
        foreach ($queryargsdata as $post) {
            wp_delete_post($post->ID,true);
        }
        
        //get all user which meta key $prefix.'userpoints' not equal to empty
        $all_user = get_users( array( 'meta_key' => $prefix.'userpoints', 'meta_value' => '', 'meta_compare' => '!=' ) );
        
        foreach ( $all_user as $key => $value ){
            delete_user_meta( $value->ID, $prefix.'userpoints' );
        }

        //Items need to delete
        $options = array(
            'woo_pr_ratio_settings_points_monetary_value',
            'woo_pr_ratio_settings_points',
            'woo_pr_redeem_points_monetary_value',
            'woo_pr_redeem_points',
            'woo_pr_buy_points_monetary_value',
            'woo_pr_buy_points',
            'woo_pr_selling_points_monetary_value',
            'woo_pr_selling_points',
            'woo_pr_cart_max_discount',
            'woo_pr_per_product_max_discount',
            'woo_pr_lables_points_monetary_value',
            'woo_pr_lables_points',
            'woo_pr_single_product_message',
            'woo_pr_earn_points_cart_message',
            'woo_pr_redeem_points_cart_message',
            'woo_pr_guest_checkout_page_message',
            'woo_pr_guest_checkout_page_buy_message',
            'woo_pr_guest_user_history_message',
            'woo_pr_earn_for_account_signup',
            'woo_pr_apply_points_to_previous_orders',
            'woo_pr_revert_points_refund_enabled',
            'woo_pr_delete_options',
            'woo_pr_set_option',
            'woo_pr_plugin_version',
            'woo_pr_enable_reviews',
            'woo_pr_review_points',
            'woo_pr_enable_decimal_points',
            'woo_pr_number_decimal'
        );

        // Delete all options
        foreach ($options as $option) {
            delete_option($option);
        }
    } // End of if
}

//add action to load plugin
add_action('plugins_loaded', 'woo_pr_plugin_loaded');

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
function woo_pr_plugin_loaded() {

    //check Woocommerce is activated or not
    if (class_exists('Woocommerce')) {

        // load first plugin text domain
        woo_pr_load_text_domain();

        // Global variables
        global $woo_pr_scripts, $woo_pr_model, $woo_pr_admin, $woo_pr_log, $woo_pr_public, $woo_pr_polylang;

        // loads the Misc Functions file
        require_once ( WOO_PR_DIR . '/includes/woo-pr-misc-functions.php' );
        // loads the Pagination Class file
        require_once ( WOO_PR_DIR . '/includes/class-woo-pr-pagination-public.php' );
        // Script class handles most of script functionalities of plugin
        include_once( WOO_PR_INC_DIR . '/class-woo-pr-scripts.php' );
        $woo_pr_scripts = new Woo_Pr_Scripts();
        $woo_pr_scripts->add_hooks();

        // Model class handles most of model functionalities of plugin
        include_once( WOO_PR_INC_DIR . '/class-woo-pr-model.php' );
        $woo_pr_model = new Woo_Pr_Model();

        //Insert logs for points functionality.
        require_once( WOO_PR_DIR . '/includes/class-woo-pr-points-log.php');
        $woo_pr_log = new Woo_Pr_Logging();

        //Public Class for public functionlities
        require_once( WOO_PR_DIR . '/includes/class-woo-pr-public.php' );
        $woo_pr_public = new Woo_Pr_Public();
        $woo_pr_public->add_hooks();

        include_once( WOO_PR_ADMIN_DIR . '/class-woo-pr-admin.php' );
        $woo_pr_admin = new Woo_Pr_Admin();
        $woo_pr_admin->add_hooks();

        // Registering our custom product class
        require( WOO_PR_INC_DIR . '/class-woo-pr-product-type-points.php' );

        // check Polylang & Polylang for WooCommerce plugin is activated
        if( defined( 'POLYLANG_VERSION' ) && defined( 'PLLWC_VERSION' ) ) {
            require_once( WOO_PR_DIR . '/includes/compatibility/class-polylang.php' );
            $woo_pr_polylang = new Woo_Pr_Polylang();
            $woo_pr_polylang->add_hooks();            
        }
    }
}

if( class_exists( 'Wpweb_Upd_Admin' ) ) { //Check WPWEB Updater is activated
	
	// Plugin updates
	wpweb_queue_update( plugin_basename( __FILE__ ), WOO_POINTS_PLUGIN_KEY );
	
	/**
	 * Include Auto Updating Files
	 * 
	 * @package WooCommerce - Points and Rewards
	 * @since 1.0.0
	 */
	require_once( WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating
	
	$WpwebWooPARUpdateChecker = new WpwebPluginUpdateChecker (
		'http://wpweb.co.in/Updates/WOOPR/license-info.php',
		__FILE__,
		WOO_POINTS_PLUGIN_KEY
	);
	
	/**
	 * Auto Update
	 * 
	 * Get the license key and add it to the update checker.
	 * 
	 * @package WooCommerce - Points and Rewards
	 * @since 1.0.0
	 */
	function woo_par_add_secret_key( $query ) {
		
		$plugin_key	= WOO_POINTS_PLUGIN_KEY;
		
		$query['lickey'] = wpweb_get_plugin_purchase_code( $plugin_key );
		return $query;
	}
	
	$WpwebWooPARUpdateChecker->addQueryArgFilter( 'woo_par_add_secret_key' );
} // end check WPWeb Updater is activated