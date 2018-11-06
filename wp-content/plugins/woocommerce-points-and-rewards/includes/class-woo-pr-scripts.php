<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
class Woo_Pr_Scripts {

    //class constructor
    function __construct() {
        
    }

    /**
     * Enqueue Admin Styles
     * 
     * Handles to enqueue styles for admin side
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_admin_styles( $hook_suffix ) {

        global $post, $wp_version;

        $post_id            = is_object($post) ? $post->ID : '';
        $needed_hook_suffix = array( 
            'user-edit.php', 
            'user-new.php',
            'users.php',
            'post.php', 
            'post-new.php',
            'term.php',
            'edit-tags.php',
            'woocommerce_page_woo-points-log',
            'woocommerce_page_wc-settings',
        );

        //Check pages when you needed 
        if( in_array( $hook_suffix, $needed_hook_suffix ) ) {

            wp_register_style('jquery-chosen-min-css', WOO_PR_URL . 'includes/css/chosen.min.css', array(), WOO_PR_VERSION);
            wp_enqueue_style('jquery-chosen-min-css');

            wp_register_style('woo-points-admin-styles', WOO_PR_URL . 'includes/css/woo-pr-admin-style.css', array(), WOO_PR_VERSION);
            wp_enqueue_style('woo-points-admin-styles');
        }
    }

    /**
     * Enqueue Scripts on Admin Side
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_admin_scripts( $hook_suffix ) {

        $needed_hook_suffix = array( 
            'user-edit.php', 
            'user-new.php',
            'users.php',
            'post.php', 
            'post-new.php',
            'woocommerce_page_woo-points-log',
            'woocommerce_page_wc-settings',
        );

        //Check pages when you needed 
        if( in_array( $hook_suffix, $needed_hook_suffix ) ) {

            //Register & Enqueue Script
            wp_register_script('jquery-chosen', WOO_PR_URL . 'includes/js/chosen.jquery.min.js', array('jquery'), WOO_PR_VERSION, true);
            wp_enqueue_script('jquery-chosen');

            wp_register_script('woo-pr-ajax-chosen-scripts', WOO_PR_URL . 'includes/js/ajax-chosen.jquery.js', array('jquery'), WOO_PR_VERSION, true);
            wp_enqueue_script('woo-pr-ajax-chosen-scripts');

            wp_register_script('woo-pr-admin-scripts', WOO_PR_URL . 'includes/js/woo-pr-admin-script.js', array('jquery', 'jquery-chosen', 'jquery-ui-sortable'), WOO_PR_VERSION, true);
            wp_enqueue_script('woo-pr-admin-scripts');

            wp_localize_script('woo-pr-admin-scripts', 'WOO_PR_Points_Admin', array(
                'update_balance' => __('Update Balance', 'woopoints'),
                'processing_balance' => __('Processing...', 'woopoints'),
                'prev_order_apply_confirm_message' => __('Are you sure you want to apply points to all previous orders that have not already had points generated? This cannot be reversed! Note that this can take some time in shops with a large number of orders, if an error occurs, simply Apply Points again to continue the process.', 'woopoints')
            ));
        }
    }
    
    /**
     * Enqueue Public Scripts
     * 
     * Handles to enqueue scripts for public side
     * 
     * @package Easy Digital Downloads - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_public_scripts() {
        
        global $post;
        
        wp_register_script( 'woo-pr-public-script', WOO_PR_INC_URL . '/js/woo-pr-public.js', array( 'jquery' ), null );
        wp_localize_script( 'woo-pr-public-script', 'WooPointsPublic', array( 
                                                                            'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) )
                                                                        ) );
        wp_enqueue_script( 'woo-pr-public-script' );
    }

    /**
     * Enqueue Styles
     * 
     * Loads the stylesheets for public side.
     * 
     * @package Easy Digital Downloads - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_public_styles() {

        wp_register_style( 'woo-pr-public-style', WOO_PR_INC_URL . '/css/woo-pr-style-public.css', array(), WOO_PR_VERSION );
        wp_enqueue_style( 'woo-pr-public-style' );
    }

    /**
     * Adding Hooks
     *
     * Adding hooks for the styles and scripts.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function add_hooks() {

        //add styles for back end
        add_action('admin_enqueue_scripts', array($this, 'woo_pr_admin_styles'));

        //add admin scripts
        add_action('admin_enqueue_scripts', array($this, 'woo_pr_admin_scripts'));

        //add script to front side for Points and Rewards
        add_action( 'wp_enqueue_scripts', array( $this, 'woo_pr_public_scripts' ) );

        //add styles for front end
        add_action( 'wp_enqueue_scripts', array( $this, 'woo_pr_public_styles' ) );
    }

}

?>