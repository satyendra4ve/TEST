<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Post Type Functions
 *
 * Handles all custom post types
 * functions
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */

/**
 * Register Post Type
 *
 * Handles to registers the Points Logs 
 * post type
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
function woo_pr_register_post_types() {

	// register WooCommerce Points Logs post type
	$points_log_labels = array(
		'name'					=> __( 'Points Logs', 'woopoints' ),
		'singular_name'			=> __( 'Points Log', 'woopoints' ),
		'add_new'				=> _x( 'Add New', WOO_POINTS_LOG_POST_TYPE, 'woopoints' ),
		'add_new_item'			=> sprintf( __( 'Add New %s' , 'woopoints' ), __( 'Points Log' , 'woopoints' ) ),
		'edit_item'				=> sprintf( __( 'Edit %s' , 'woopoints' ), __( 'Points Log' , 'woopoints' ) ),
		'new_item'				=> sprintf( __( 'New %s' , 'woopoints' ), __( 'Points Log' , 'woopoints' ) ),
		'all_items'				=> sprintf( __( '%s' , 'woopoints' ), __( 'Points Logs' , 'woopoints' ) ),
		'view_item'				=> sprintf( __( 'View %s' , 'woopoints' ), __( 'Points Log' , 'woopoints' ) ),
		'search_items'			=> sprintf( __( 'Search %a' , 'woopoints' ), __( 'Points Logs' , 'woopoints' ) ),
		'not_found'				=> sprintf( __( 'No %s Found' , 'woopoints' ), __( 'Points Logs' , 'woopoints' ) ),
		'not_found_in_trash'	=> sprintf( __( 'No %s Found In Trash' , 'woopoints' ), __( 'Points Logs' , 'woopoints' ) ),
		'parent_item_colon'		=> '',
		'menu_name' 			=> __( 'Points Logs' , 'woopoints' )
	);

	$points_log_args = array(
		'labels'				=> $points_log_labels,
		'public' 				=> false,
	    'exclude_from_search'	=> true,
	    'query_var' 			=> false,
	    'rewrite' 				=> false,
	    'capability_type' 		=> WOO_POINTS_LOG_POST_TYPE,
	    'hierarchical' 			=> false,
	    'supports' 				=> array( 'title' )
	);
	
	// finally register post type
	register_post_type( WOO_POINTS_LOG_POST_TYPE, $points_log_args );
}
add_action( 'init', 'woo_pr_register_post_types' );