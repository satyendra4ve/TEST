<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Points List Page
 * 
 * The html markup for the Points list
 * 
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class woo_pr_log extends WP_List_Table {

    public $model, $woo_pr_points_model, $per_page;

    function __construct() {

        global $woo_pr_model;

        $this->model = $woo_pr_model;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'pointlog',
            'plural' => 'pointslog',
            'ajax' => false
        ));

        $this->per_page = apply_filters('woo_pr_posts_per_page', 20); // Per page
    }

    /**
     * Displaying Points
     * 
     * Does prepare the data for displaying the Points in the table.
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_display_points() {

        global $all_logs_user_ids;

        $all_logs_user_ids = $resultdata = $item = array();

        // Taking parameter
        $orderby    = isset($_GET['orderby']) ? urldecode($_GET['orderby']) : 'ID';
        $order      = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $search     = isset($_GET['s']) ? sanitize_text_field(trim($_GET['s'])) : null;
        $id_search  = isset($_GET['userid']) ? $_GET['userid'] : '';
        $event      = isset($_GET['woo_event_type']) ? $_GET['woo_event_type'] : '';
        $paged      = isset($_GET['paged']) ? $_GET['paged'] : null;
        $monthyear  = isset($_REQUEST['m']) ? $_REQUEST['m'] : '';

        $points_arr = array(
            'posts_per_page' => $this->per_page,
            'page'           => $paged,
            'orderby'        => $orderby,
            'order'          => $order,
            'offset'         => ( $this->get_pagenum() - 1 ) * $this->per_page,
            'woo_pr_list'    => true
        );


        if (isset($search) && !empty($search)) {
            //in case of search make parameter for retriving search data
            $points_arr['s'] = $search;
        }

        if (isset($monthyear) && !empty($monthyear)) {
            //in case of month search make parameter for retriving search data
            $points_arr['monthyear'] = $monthyear;
        }

        if (isset($id_search) && !empty($id_search)) {
            $points_arr['author'] = $id_search;
        }

        if (isset($_GET['woo_event_type']) && !empty($_GET['woo_event_type'])) {
            $points_arr['event'] = $_GET['woo_event_type'];
        }

        //call function to retrive data from table
        $data = $this->model->woo_pr_get_points($points_arr);

        if (!empty($data['data'])) {

            foreach ($data['data'] as $key => $value) {

                $customerid = $value['post_author'];
                $userdata   = get_user_by('id', $customerid);

                if ($userdata) {

                    $user_id            = $userdata->ID;
                    $item['user_id']    = $user_id;
                    $item['useremail']  = isset($userdata->user_email) ? $userdata->user_email : '';
                    $item['user_name']  = $userdata->display_name;
                } else {
                    $item['user_id']    = '';
                    $item['useremail']  = '';
                    $item['user_name']  = '';
                }

                $resultdata[$key]['customer']       = $this->column_user($item);
                $resultdata[$key]['points_id']      = $value['ID'];
                $resultdata[$key]['order_id']       = get_post_meta($value['ID'], '_woo_log_order_id', true);
                $resultdata[$key]['points']         = get_post_meta($value['ID'], '_woo_log_userpoint', true);
                $resultdata[$key]['event']          = get_post_meta($value['ID'], '_woo_log_events', true);
                $resultdata[$key]['date']           = $value['post_date_gmt'];
                $resultdata[$key]['post_content']   = $value['post_content'];
            }
        }

        $result_arr['data'] = !empty($resultdata) ? $resultdata : array();
        $result_arr['total'] = isset($data['total']) ? $data['total'] : ''; // Total no of data

        //call function to retrive data from table
        $all_users_log_arr = array(
            'posts_per_page' => -1,
            'woo_pr_list'    => true,
        );
        $all_users_log_data = $this->model->woo_pr_get_points($all_users_log_arr);

        // Get users id from post data
        if (!empty($all_users_log_data['data'])) {

            foreach ($all_users_log_data['data'] as $key => $value) {

                $customerid = $value['post_author'];
                $userdata = get_user_by('id', $customerid);
                if ($userdata) {

                    $user_id = $userdata->ID;
                    if( !in_array($user_id, $all_logs_user_ids) ){
                        $all_logs_user_ids[] = $user_id;
                    }
                }
            }
        }

        return $result_arr;
    }

    /**
     * User Column Data
     * 
     * Handles to show user column
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    function column_user($item) {

        $display_name = $item['user_name'];

        $user_id = $item['user_id'];
        $user = isset($user_id) && !empty($user_id) ? $user_id : $item['useremail'];

        $userlink = add_query_arg(array('page' => 'woo-points-log', 'userid' => $user), admin_url('admin.php'));
        return '<a href="' . $userlink . '">' . $display_name . '</a><br/>' . $item['useremail'];
    }

    /**
     * Manage column data
     * 
     * Default Column for listing table
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'customer':
            case 'points':
                return $item[$column_name];
            case 'date':
                return $this->model->woo_pr_log_time(strtotime($item['date']));
            case 'event' :
                if ($item[$column_name] == 'manual') {
                    $event_description = $item['post_content'];
                } else {
                    $event_description = $this->model->woo_pr_get_events($item[$column_name]);
                }
                $event_description .= $this->model->woo_pr_get_event_order_link($item, $item['order_id']);
                return $event_description;
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Add Filter for Sorting
     * 
     * Handles to add filter for sorting
     * in listing
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    function extra_tablenav($which) {

        global $all_logs_user_ids;

        if ($which == 'top') {

            echo '<div class="alignleft actions woo-pr-points-dropdown-wrapper">';

            $all_events = apply_filters( 'woo_pr_all_events_options', array(
                'earned_purchase'           => __('Order Placed', 'woopoints'),
                'earned_sell'               => __('Downloads Sell', 'woopoints'),
                'redeemed_purchase'         => __('Order Redeem', 'woopoints'),
                'refunded_purchase_debited' => __('Debit for Refund Order', 'woopoints'),
                'refunded_purchase_credited'=> __('Credit for Refund Order', 'woopoints'),
                'refunded_sell_debited'     => __('Debit Sell for Refund Order', 'woopoints'),
                'signup'                    => __('Account Signup', 'woopoints'),
                'manual'                    => __('Manual', 'woopoints'),
                'reset_points'              => __('Reset Points', 'woopoints'),
                'earned_product_review'     => __('Product Review', 'woopoints')
            ) );
            $checked = '';
            ?>

            <select id="woo_pr_userid" name="userid">
                <option value=""><?php _e('Show all customer', 'woopoints'); ?></option><?php
                foreach ($all_logs_user_ids as $user_id_key => $user_id_value) {

                    $user_data = get_user_by('id', $user_id_value);
                    $selected = selected(isset($_GET['userid']) ? $_GET['userid'] : '', $user_id_value, false);
                    echo '<option value="' . $user_data->ID . '" ' . $selected . '>' . $user_data->display_name . ' (#' . $user_data->ID . ' &ndash; ' . sanitize_email($user_data->user_email) . ')' . '</option>';
                }
                ?>
            </select>
            <select id="woo_event_type" name="woo_event_type">
                <option value=""><?php _e('Show All Event Types', 'woopoints'); ?></option><?php
                foreach ($all_events as $event_key => $event_value) {
                    $selected = selected(isset($_GET['woo_event_type']) ? $_GET['woo_event_type'] : '', $event_key, false);
                    echo '<option value="' . $event_key . '" ' . $selected . '>' . $event_value . '</option>';
                }
                ?>
            </select><?php
            $this->months_dropdown(WOO_POINTS_LOG_POST_TYPE);
            submit_button(__('Filter', 'woopoints'), 'button', false, false, array('id' => 'post-query-submit'));
            echo '</div>';
        }
    }

    /**
     * Display Columns
     * 
     * Handles which columns to show in table
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function get_columns() {

        $columns = array(
            'customer' 	=> __('Customer', 'woopoints'),
            'points' 	=> __('Points', 'woopoints'),
            'event' 	=> __('Event', 'woopoints'),
            'date' 		=> __('Date', 'woopoints')
        );

        return $columns;
    }

    /**
     * Sortable Columns
     * 
     * Handles soratable columns of the table
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function get_sortable_columns() {

        $sortable_columns = array(
            'customer' 	=> array('customer', true),
            'points' 	=> array('points', true),
            'event' 	=> array('event', true),
            'date' 		=> array('date', true),
        );

        return $sortable_columns;
    }

    function no_items() {

        //message to show when no records in database table
        _e('No points log found.', 'woopoints');
    }

    function prepare_items() {

        // Get how many records per page to show
        $per_page = $this->per_page;

        // Get All, Hidden, Sortable columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // Get final column header
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Get Data of particular page
        $data_res = $this->woo_pr_display_points();
        $data = $data_res['data'];

        // Get current page number
        $current_page = $this->get_pagenum();

        // Get total count
        $total_items = $data_res['total'];

        // Get page items
        $this->items = $data;

        // We also have to register our pagination options & calculations.
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

}

//Create an instance of our package class...
$PointsListTable = new woo_pr_log();

//Fetch, prepare, sort, and filter our data...
$PointsListTable->prepare_items();
?>
<div class="wrap">
    <h2><?php _e('Points Log', 'woopoints'); ?></h2>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="Points-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <input type="hidden" name="post_type" />

        <!-- Search Title -->
        <?php $PointsListTable->search_box(__('Search', 'woopoints'), 'woo_pr_search'); ?>

        <!-- Now we can render the completed list table -->
        <?php $PointsListTable->display() ?>
    </form>

</div><!--.wrap-->