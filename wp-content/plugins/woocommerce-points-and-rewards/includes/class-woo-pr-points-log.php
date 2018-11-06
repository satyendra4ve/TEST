<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Logging Class
 *
 * Handles all the different functionalities of logs
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
class Woo_Pr_Logging {

    var $model, $logs;

    public function __construct() {
        global $woo_pr_model, $woo_pr_points_scripts;
        $this->model = $woo_pr_model;
    }
    
    /**
     * Stores a log entry
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    function woo_pr_insert_logs($log_data = array(), $log_meta = array()) {

        global $current_user;

        $log_id = 0;

        $logspoints = abs($log_meta['userpoint']);
        //if user should enter user points more than zero
        if (!empty($logspoints)) {

            $defaults = array(
                'post_type' => WOO_POINTS_LOG_POST_TYPE,
                'post_status' => 'publish',
                'post_parent' => 0,
                'post_title' => '',
                'post_content' => ''
            );

            $args = wp_parse_args($log_data, $defaults);

            //check there is operation type is set or not
            if (isset($log_meta['operation']) && $log_meta['operation'] == 'minus') {
                $log_meta['userpoint'] = '-' . $log_meta['userpoint'];
            } else {
                $log_meta['userpoint'] = '+' . $log_meta['userpoint'];
            }

            // Store the log entry
            $log_id = wp_insert_post($args);

            // Set log meta, if any
            if ($log_id && !empty($log_meta)) {
                foreach ((array) $log_meta as $key => $meta) {
                    update_post_meta($log_id, '_woo_log_' . sanitize_key($key), $meta);
                }
            }
            // Call action after insert log
            do_action( 'woo_pr_after_insert_logs', $log_id );
        }

        return $log_id;
    }

    /**
     * Update and existing log item
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    function woo_pr_update_logs($log_data = array(), $log_meta = array()) {

        $defaults = array(
            'post_type' => WOO_POINTS_LOG_POST_TYPE,
            'post_status' => 'publish',
            'post_parent' => 0
        );

        $args = wp_parse_args($log_data, $defaults);

        // Store the log entry
        $log_id = wp_update_post($args);

        if ($log_id && !empty($log_meta)) {
            foreach ((array) $log_meta as $key => $meta) {
                if (!empty($meta))
                    update_post_meta($log_id, '_woo_log_' . sanitize_key($key), $meta);
            }
        }
    }
    /**
     * Show Listing for User Points
     * 
     * Handles to return / echo users
     * points log listing at front side
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     **/
    public function woo_pr_user_log_list(){
        
        global $current_user;
        $prefix = WOO_PR_META_PREFIX;

        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');
        
        //enqueue script to work with public script
        wp_enqueue_script( 'woo-pr-public-script' );
        
        $html = '';
        $perpage = 10;
        
        $argscount = array(
                            'author'    =>  $current_user->ID,
                            'getcount'  =>  '1'
                        );

        //get user logs count value
        $userpointslogcount = $this->model->woo_pr_get_points( $argscount );
        
        $paging = new Woo_Pr_Pagination_Public();
        $paging->items( $userpointslogcount ); 
        $paging->limit( $perpage ); // limit entries per page
        
        //check paging is set or not
        if( isset( $_POST['paging'] ) ) {
            $paging->currentPage( $_POST['paging'] ); // gets and validates the current page
        }
        
        $paging->calculate(); // calculates what to show
        $paging->parameterName( 'paging' );
        
        // setting the limit to start
        $limit_start = ( $paging->page - 1 ) * $paging->limit;
        
        if( isset( $_POST['paging'] ) ) { 
            //ajax call pagination
            $queryargs = array(
                                'posts_per_page'    =>  $perpage,
                                'paged'             =>  $_POST['paging'],
                                'author'            =>  $current_user->ID
                            );
            
        } else {
            //on page load 
            $queryargs = array(
                                'posts_per_page'    =>  $perpage,
                                'paged'             =>  '1',
                                'author'            =>  $current_user->ID
                            );
        }
        //get user logs data
        $userpointslog = $this->model->woo_pr_get_points( $queryargs );
        
        //get user points
        $tot_points = woo_pr_get_user_points( $current_user->ID ); 

        // Apply decimal if enabled
        if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
            $tot_points = round( $tot_points, $woo_pr_number_decimal );
        } else {
            $tot_points = round( $tot_points );
        }
        
            $html .= '<div class="woo-pr-user-log">';
            
            //get points plural label
            $plural_label = get_option('woo_pr_lables_points');
            $pointslabel = isset( $plural_label ) && !empty( $plural_label )
                            ? strtoupper( $plural_label ) : __( 'POINTS', 'woopoints' );
            
            $html .= '  <h4>'.sprintf( __( 'You have %s %s', 'woopoints' ), $tot_points, $pointslabel ).'</h4>';
            
            $html .= '  <div class="woo-pr-user-points"><table border="1" class="woo-pr-details">
                                <tr>
                                    <th width="50%">'.__( 'EVENT','woopoints' ).'</th>
                                    <th width="25%">'.__( 'DATE','woopoints' ).'</th>
                                    <th width="15%">'.$pointslabel.'</th>
                                </tr>';
        
                if( !empty( $userpointslogcount ) ) { //check user log in not empty
                    
                    foreach ( $userpointslog as $key => $value ){
                        
                        $logspointid = $value['ID'];
                        $event          = get_post_meta( $logspointid, '_woo_log_events', true );
                        $order_id       = get_post_meta( $logspointid, '_woo_log_order_id', true );
                        $event_data     = $this->model->woo_pr_get_events( $event );
                        $event_data     .= $this->model->woo_pr_get_event_user_order_link( $order_id, $logspointid );
                        $date           = $this->model->woo_pr_log_time( strtotime( $value['post_date_gmt'] ) );
                        $points         = get_post_meta( $logspointid, '_woo_log_userpoint', true );
                        
                        //check event is manual or not
                        if( ($event == 'manual') ) {
                            $event_data = isset( $value['post_content'] ) && !empty( $value['post_content'] ) ? $value['post_content'] : '';
                        }

                        $html .= '<tr>
                                    <td>'.$event_data.'</td>
                                    <td>'.$date.'</td>
                                    <td>'.$points.'</td>
                                </tr>';
                        
                    } //end foreach loop
                    
                } else {
                    $html .=        '<tr><td colspan="3">'.__( 'No points log found.', 'woopoints' ).'</td></tr>';
                }
                        
        $html .=        '</table></div>';
        $html .= '      <div class="woo-pr-paging">
                            <div id="woo-pr-tablenav-pages" class="woo-pr-tablenav-pages">'.
                                 $paging->getOutput() .'
                            </div>
                        </div><!--woo-pr-paging-->
                        <div class="woo-pr-sales-loader">
                            <img src="'.WOO_PR_INC_URL.'/images/loader.gif"/>
                        </div>';
        $html .= '</div><!--woo-pr-user-log-->';
        
        if( isset( $_POST['paging'] ) ) { //check paging is set in $_POST or not
            echo $html;
        } else {
            return $html;
        }
        
    }

}
