<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Admin Class
 *
 * Manage Admin Panel Class
 *
 * @package WooCommerce - Points and Rewards
 * @since 1.0.0
 */
class Woo_Pr_Admin {

    public $model, $scripts, $logs, $public;

    //class constructor
    function __construct() {

        global $woo_pr_model, $woo_pr_scripts, $woo_pr_log, $woo_pr_public;

        $this->scripts = $woo_pr_scripts;
        $this->model = $woo_pr_model;
        $this->logs = $woo_pr_log;
        $this->public = $woo_pr_public;
    }

    /**
     * Downloads Category fields HTML
     * 
     * Handles to add category fields HTML
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_product_category_add_fields_html() {

        $points_earned_description = __('This can be a fixed number of points earned for the purchase of any product that belongs to this category. This setting modifies the global Points Conversion Rate, but can be overridden by a product. Use 0 to assign no earn points for products belonging to this category, and empty to use the global setting. If a product belongs to multiple categories which define different point levels, the highest available point count will be used when awarding points for placing order.', 'woopoints');
        $max_discount_description = sprintf(__('Enter a fixed maximum discount amount  which restricts  the amount of points that can be redeemed for a discount. For example, if you want to restrict the discount on this category to a maximum of %s5, enter 5. This setting overrides the global default, but can be overridden by a product. Use 0 to disable point discounts for this category, and blank to use the global setting. If a product belongs to multiple categories which define different point discounts, the lowest point count will be used when allowing points discount for placing order.', 'woopoints'), get_woocommerce_currency_symbol());
        ?>

        <div class="form-field">
            <label for="woo_pr_rewards_earn_point"><?php _e('Points Earned', 'woopoints'); ?></label>
            <input type="number" class="woo-points-earned-cat-field" name="woo_pr_rewards_earn_point" id="woo_pr_rewards_earn_point"/>
            <p><?php echo $points_earned_description; ?></p>
        </div><!--.form-field-->
        <div class="form-field">
            <label for="woo_pr_rewards_max_point_disc"><?php _e('Maximum Points Discount', 'woopoints'); ?></label>
            <input type="number" class="woo-pr-points-dis-cat-field" name="woo_pr_rewards_max_point_disc" value="jjnfj" id="woo_pr_rewards_max_point_disc"/>
            <?php echo get_woocommerce_currency_symbol(); ?>
            <p><?php echo $max_discount_description; ?></p>
        </div><!--.form-field-->
            <script type="text/javascript">

                jQuery( document ).ajaxComplete( function( event, request, options ) {
                    if ( request && 4 === request.readyState && 200 === request.status
                        && options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

                        var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
                        if ( ! res || res.errors ) {
                            return;
                        }
                        // Clear Display type field on submit
                        jQuery( '#addtag #woo_pr_rewards_earn_point' ).val( '' );
                        jQuery( '#addtag #woo_pr_rewards_max_point_disc' ).val( '' );
                        return;
                    }
                } );

            </script>
        <?php
    }

    /**
     * Downloads Category Edit fields HTML
     * 
     * Handles to edit category fields HTML
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_product_category_edit_fields_html($term) {
        
        $prefix = WOO_PR_META_PREFIX;

        $points_earned_description = __('This can be a fixed number of points earned for the purchase of any product that belongs to this category. This setting modifies the global Points Conversion Rate, but can be overridden by a product. Use 0 to assign no earn points for products belonging to this category, and empty to use the global setting. If a product belongs to multiple categories which define different point levels, the highest available point count will be used when awarding points for placing order.', 'woopoints');
        $max_discount_description = sprintf(__('Enter a fixed maximum discount amount  which restricts  the amount of points that can be redeemed for a discount. For example, if you want to restrict the discount on this category to a maximum of %s5, enter 5. This setting overrides the global default, but can be overridden by a product. Use 0 to disable point discounts for this category, and blank to use the global setting. If a product belongs to multiple categories which define different point discounts, the lowest point count will be used when allowing points discount for placing order.', 'woopoints'), get_woocommerce_currency_symbol());

        $term_id = $term->term_id;

        //get earn point and maximum pont discount data. 
        $earnedpoints = get_woocommerce_term_meta($term_id, $prefix."rewards_earn_point");
        $maxdiscount = get_woocommerce_term_meta($term_id, $prefix."rewards_max_point_disc");
        $earnedpoints = $earnedpoints !== '' ? $this->model->woo_pr_escape_attr($earnedpoints) : '';
        $maxdiscount = $maxdiscount !== '' ? $this->model->woo_pr_escape_attr($maxdiscount) : '';
        ?>
        <tr class="form-field">
            <th valign="top" scope="row"><label for="woo_pr_rewards_earn_point"><?php _e('Points Earned', 'woopoints'); ?></label></th>
            <td>
                <input type="number" class="woo-points-earned-cat-field" name="woo_pr_rewards_earn_point" id="woo_pr_rewards_earn_point" value="<?php echo $earnedpoints; ?>"/>
                <p class="description"><?php echo $points_earned_description; ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th valign="top" scope="row"><label for="woo_pr_rewards_max_point_disc"><?php _e('Maximum Points Discount', 'woopoints'); ?></label></th>
            <td>
                <input type="number" class="woo-pr-points-dis-cat-field" name="woo_pr_rewards_max_point_disc" id="woo_pr_rewards_max_point_disc"  value="<?php echo $maxdiscount; ?>"/>
                       <?php echo get_woocommerce_currency_symbol(); ?>
                <p class="description"><?php echo $max_discount_description; ?></p>
            </td>
        </tr>

        <?php
    }

    /**
     * Add a 'Points Earned' column header to the product category list table
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * @param array $columns associative array of column id to title
     * @return array
     */
    public function woo_pr_add_product_category_list_table_points_column_header($columns) {

        $new_columns = array();

        foreach ($columns as $column_key => $column_title) {

            $new_columns[$column_key] = $column_title;

            // add column header immediately after 'Slug'
            if ('slug' == $column_key) {
                $new_columns['points_earned'] = __('Points Earned', 'woopoints');
                $new_columns['max_points_discount'] = __('Maximum Points discount', 'woopoints');
            }
        }

        return $new_columns;
    }

    /**
     * Add the 'Points Earned' column content to the product category list table
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0
     * @param array $columns column content
     * @param string $column column ID
     * @param int $term_id the product category term ID
     * @return array
     */
    public function woo_pr_add_product_category_list_table_points_column($columns, $column, $term_id) {

        $prefix = WOO_PR_META_PREFIX;

        $points_earned = get_woocommerce_term_meta($term_id, $prefix.'rewards_earn_point');

        $max_point_descount = get_woocommerce_term_meta($term_id, $prefix.'rewards_max_point_disc');
        if ('points_earned' == $column) {
            echo ( '' !== $points_earned ) ? esc_html($points_earned) : '&mdash;';
        }
        if ('max_points_discount' == $column) {
            echo ( '' !== $max_point_descount ) ? esc_html($max_point_descount) : '&mdash;';
        }
        return $columns;
    }

    /**
     * Save extra taxonomy fields callback function.
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * 
     * @param type $term_id
     */
    function woo_pr_save_taxonomy_product_category_meta($term_id) {

        $prefix = WOO_PR_META_PREFIX;

        $woo_pr_rewards_earn_point = $max_number_amount = '';

        $earn_amount 	= filter_input(INPUT_POST, 'woo_pr_rewards_earn_point');
        $max_amount 	= filter_input(INPUT_POST, 'woo_pr_rewards_max_point_disc');

        $earn_number_amount = preg_replace('/[^0-9\.]/', '', $earn_amount);
        $max_number_amount 	= preg_replace('/[^0-9\.]/', '', $max_amount);

        if ( $earn_number_amount !== '' ) {

            $woo_pr_rewards_earn_point = round( $earn_number_amount );
        }

        if ( $max_number_amount !== '' ) {

            $woo_pr_rewards_max_point_disc = round( $max_number_amount );
        }

        update_term_meta($term_id, $prefix.'rewards_earn_point', $woo_pr_rewards_earn_point);
        update_term_meta($term_id, $prefix.'rewards_max_point_disc', $woo_pr_rewards_max_point_disc);
    }

    /**
     * Add Metabox
     * 
     * Add metabox for points and rewards
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_product_metabox() {
        $post_types = array('product');     //limit meta box to certain post types
        add_meta_box(
                'woo_pr_and_rewards'
                , __('Product Points and Rewards Configuration', 'woopoints')
                , array($this, 'woo_pr_product_metabox_content')
                , $post_types
                , 'advanced'
                , 'high'
        );
    }

    /**
     * Metabox Callback function.
     * 
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_product_metabox_content($product) {

        $productid = $product->ID;
        $prefix = WOO_PR_META_PREFIX;
        $woo_pr_enable_reviews = get_option('woo_pr_enable_reviews');

        //get earn point and maximum pont discount data. 
        $earnedpoints   = get_post_meta($productid, $prefix."rewards_earn_point", true);
        $maxdiscount    = get_post_meta($productid, $prefix."rewards_max_point_disc", true);
        $review_points  = get_post_meta($productid, $prefix."review_points", true);
        $earnedpoints   = (!empty($earnedpoints) || ($earnedpoints==0)) ? $this->model->woo_pr_escape_attr($earnedpoints) : '';
        $maxdiscount    = (!empty($maxdiscount) || ($maxdiscount==0)) ? $this->model->woo_pr_escape_attr($maxdiscount) : '';

        //create nonce for metabox
        wp_nonce_field(WOO_POINTS_BASENAME, 'at_woo_pr_points_and_rewards_meta_nonce');
        ?>
        <div id="woo_pr_simple_point">
            <div id="woo_pr_points_rewads_fields">
                <table>
                    <tr>
                        <td width="20%">
                            <label for="woo_pr_rewards_earn_point"><?php _e('Points Earned:', 'woopoints'); ?></label>
                        </td>
                        <td>
                            <input type="number" class="woo-pr-price-field" value="<?php echo $earnedpoints; ?>" id="woo_pr_rewards_earn_point" name="woo_pr_rewards_earn_point"/>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="description"><?php _e('This can be a fixed number of points earned for purchasing this product. This setting modifies the global Points Conversion Rate and overrides any category value. Use 0 to assign no points for this product, and empty to use the global/category settings.', 'woopoints'); ?></span><br/><br/></td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="woo_pr_rewards_max_point_disc"><?php _e('Maximum Points Discount:', 'woopoints'); ?></label>
                        </td>
                        <td>
                            <input type="number" class="woo-price-field" value="<?php echo $maxdiscount; ?>" id="woo_pr_rewards_max_point_disc" name="woo_pr_rewards_max_point_disc"/>
                            <?php echo get_woocommerce_currency_symbol(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="description"><?php printf(__('Enter a fixed maximum discount amount which restricts the amount of points that can be redeemed for a discount. For example, if you want to restrict the discount on this product to a maximum of %s5, enter 5. This setting overrides the global and category settings. Use 0 to disable point discounts for this product, and blank to use the global/category defaults.', 'woopoints'), get_woocommerce_currency_symbol()); ?></span><br/><br/></td>
                    </tr>
                    <?php // check if review points in enable
                    if( !empty( $woo_pr_enable_reviews ) && ($woo_pr_enable_reviews=='yes') ){ ?>
                        <tr>
                            <td width="20%" style="vertical-align:top;">
                                <label for="woo_pr_rating_point_disc"><?php _e('Points earned for Review:', 'woopoints'); ?></label>
                            </td>
                            <td>
                                <?php
                                for ( $star_num = 5; $star_num >= 1; $star_num-- ) {

                                    $val = isset( $review_points[$star_num] ) ? $review_points[$star_num] : '';

                                    echo '<div class="woo_pr_sub_field_item">';

                                    //Display Star description
                                    for ( $i = 1; $i <= 5; $i++ ) {
                                        $star_filled = ( $star_num >= $i ) ? 'dashicons-star-filled' : 'dashicons-star-empty';
                                        echo '<span class="dashicons '. $star_filled .'"></span>';
                                    }

                                    echo '<input type="number" min="0" class="small-text" id="woo_pr_review_points" name="woo_pr_review_points['. $star_num .']" value="' . esc_attr( $val ) . '"/>';
                                    echo __( ' Point(s)', 'woopoints' );

                                    echo '</div>';
                                }?>
                                <span class="description"><?php echo __('Enter the number of points earned when a customer add a review on this product.', 'woopoints'); ?></span><br/><br/>
                            </td>
                        </tr>

                    <?php
                    } 

                    /**
                     * Fires Points & Rewards metabox settings after.
                     *
                     * add custom setting after metabox
                     * 
                     * @package WooCommerce - Points and Rewards
                     * @since 1.0.0
                     */
                    do_action( 'woo_pr_product_metabox_fields_after', $product );?>

                </table>
            </div> <!--#woo_pr_points_rewads_fields-->
        </div><!--#woo_pr_simple_point-->
        <?php
    }

    /**
     * Save our extra meta box fields
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_product_meta_fields_save($postid) {

        global $post_type, $post;

        $prefix = WOO_PR_META_PREFIX;
        $woo_pr_enable_reviews = get_option('woo_pr_enable_reviews');

        //check post type is product
        if( $post_type == 'product' && ( !array_key_exists( 'product-type', $_POST ) 
        	|| $_POST['product-type'] != 'woo_pr_points' ) ){

            $woo_pr_rewards_earn_point = $woo_pr_rewards_max_point_disc = $woo_pr_earned = $woo_pr_max_discount = '';
            $woo_pr_review_points = array();

            if( array_key_exists( 'woo_pr_rewards_earn_point', $_POST ) && $_POST['woo_pr_rewards_earn_point'] !== '' ) {

            	$woo_pr_earned = trim($_POST['woo_pr_rewards_earn_point']);
            }

            $woo_pr_earned = (!empty($woo_pr_earned) ) ? $this->model->woo_pr_escape_attr($woo_pr_earned) : $woo_pr_earned;

            //update maximum discount points
            if( array_key_exists( 'woo_pr_rewards_max_point_disc', $_POST ) && $_POST['woo_pr_rewards_max_point_disc'] !== '' ) {

            	$woo_pr_max_discount = trim($_POST['woo_pr_rewards_max_point_disc']);
            	$woo_pr_max_discount    = (!empty($woo_pr_max_discount) ) ? $this->model->woo_pr_escape_attr($woo_pr_max_discount) : $woo_pr_max_discount;
            }

            $earn_number_amount     = preg_replace('/[^0-9\.]/', '', $woo_pr_earned);
            $max_number_amount      = preg_replace('/[^0-9\.]/', '', $woo_pr_max_discount);

            if ( $earn_number_amount !== '' ) {
                $woo_pr_rewards_earn_point = round( $earn_number_amount );
            }
            if ( $max_number_amount !== '' ) {
                $woo_pr_rewards_max_point_disc  = round( $max_number_amount );
            }

            //update review points
            if( array_key_exists( 'woo_pr_review_points', $_POST ) ) {

                $woo_pr_review_points = $_POST['woo_pr_review_points'];
                foreach ($woo_pr_review_points as $key => $value) {
                    $woo_pr_review_points[$key] = trim($value);
                }
            }

            update_post_meta($postid, $prefix.'rewards_earn_point', $woo_pr_rewards_earn_point);
            update_post_meta($postid, $prefix.'rewards_max_point_disc', $woo_pr_rewards_max_point_disc);
            // check if review points is enable
            if( !empty( $woo_pr_enable_reviews ) && ($woo_pr_enable_reviews=='yes') ) {
                update_post_meta($postid, $prefix.'review_points', $woo_pr_review_points);
            }
        }
    }

    /**
     * Add Custom column label for users screen
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_add_points_column($columns) {
        $columns['_woo_userpoints'] = __('Points', 'woopoints');
        return $columns;
    }

    /**
     * Add custom column content for users screen
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_show_points_column_content($value, $column_name, $user_id) {

        switch ($column_name) {
            case '_woo_userpoints' :

                $points = get_user_meta($user_id, WOO_PR_META_PREFIX.'userpoints', true);
                // Get decimal points option
                $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
                $woo_pr_number_decimal = get_option('woo_pr_number_decimal');

                if ('_woo_userpoints' == $column_name) {
                    $ubalance = !empty($points) ? $points : '0';
                }
                // Apply decimal if enabled
                if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
                    $ubalance = round( $ubalance, $woo_pr_number_decimal );
                } else {
                    $ubalance = round( $ubalance );
                }

                $balance = '<div id="woo_pr_points_user_' . $user_id . '_balance">' . $ubalance . '</div>';

                // Row actions
                $row = array();
                $row['history'] = '<a href="' . admin_url('admin.php?page=woo-points-log&userid=' . $user_id) . '">' . __('History', 'woopoints') . '</a>';
                if (current_user_can('edit_users')) { // Check edit user capability
                    $row['adjust'] = '<a href="javascript:void(0)" id="woo_pr_points_user_' . $user_id . '_adjust" class="woo-pr-points-editor-popup" data-userid="' . $user_id . '" data-current="' . $ubalance . '">' . __('Adjust', 'woopoints') . '</a>';
                }

                $balance .= $this->woo_pr_row_actions($row);
                return $balance;
                break;
        }
    }

    /**
     * Add product type points
     * 
     * @package WooCommerce - Points and Rewards
     * @param array $types associative array of product type
     * @since 1.0.0
     */
//    function wdm_add_custom_product_type($types) {
//        $types['points_product'] = __('Points');
//        return $types;
//    }

    /**
     * Generate row actions div
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_row_actions($actions, $always_visible = false) {
        $action_count = count($actions);
        $i = 0;

        if (!$action_count)
            return '';

        $out = '<div class="' . ( $always_visible ? 'row-actions-visible' : 'row-actions' ) . '">';
        foreach ($actions as $action => $link) {
            ++$i;
            ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
            $out .= "<span class='$action'>$link$sep</span>";
        }
        $out .= '</div>';

        return $out;
    }

    /**
     * Pop Up On Editor
     *
     * Includes the pop up on the user listing page
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_points_user_balance_popup() {

        include_once( WOO_PR_ADMIN_DIR . '/forms/woo-pr-user-balance-popup.php' );
    }

    /**
     * AJAX Call for adjust user points
     *
     * Handles to adjust user points using ajax
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_adjust_user_points() {

        if (isset($_POST['userid']) && !empty($_POST['userid']) && isset($_POST['points']) && !empty($_POST['points'])) { // Check user id and points are not empty
            $user_id = $_POST['userid'];
            $current_points = $_POST['points'];

            // Get decimal points option
            $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
            $woo_pr_number_decimal = get_option('woo_pr_number_decimal');

            //check number contains minus sign or not
            if (strpos($current_points, '-') !== false) {
                $operation = 'minus';
                $current_points = str_replace('-', '', $current_points);

                // Update user points to user account
                woo_pr_minus_points_from_user($current_points, $user_id);
            } else {
                $operation = 'add';
                $current_points = str_replace('+', '', $current_points);
                // Update user points to user account
                woo_pr_add_points_to_user($current_points, $user_id);
            }

            // Get user points from user meta
            $ubalance = woo_pr_get_user_points($user_id);
            // Apply decimal if enabled
            if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
                $ubalance = round( $ubalance, $woo_pr_number_decimal );
            } else {
                $ubalance = round( $ubalance );
            }

            if (isset($_POST['log']) && !empty($_POST['log']) && trim($_POST['log'], ' ') != '') { // Check log is not empty
                $post_data = array(
                    'post_title' => $_POST['log'],
                    'post_content' => $_POST['log'],
                    'post_author' => $user_id
                );

                $log_meta = array(
                    'userpoint' => abs($current_points),
                    'events' => 'manual',
                    'operation' => $operation //add or minus
                );


                $this->logs->woo_pr_insert_logs($post_data, $log_meta);
            }

            echo $ubalance;
        } else {
            echo 'error';
        }
        die();
    }

    /**
     * Add Reset points options in bulk action for users screen
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_add_reset_points_to_bulk_actions($actions) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                jQuery('<option>').val('reset_points').text('<?php _e('Reset Points', 'woopoints') ?>').appendTo("select[name='action']");
                jQuery('<option>').val('reset_points').text('<?php _e('Reset Points', 'woopoints') ?>').appendTo("select[name='action2']");
            });
        </script>
        <?php
    }

    /**
     * Reset points to zero of selected users for users screen
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_reset_points() {
        
        $prefix = WOO_PR_META_PREFIX;
        // get the action
        $wp_list_table = _get_list_table('WP_Users_List_Table');
        $action = $wp_list_table->current_action();

        switch ($action) {
            // Perform the action
            case 'reset_points':

                if (!empty($_GET['users'])) {

                    foreach ($_GET['users'] as $key => $user_id) {
                        if (!empty($user_id)) {

                            $user_points = get_user_meta($user_id, $prefix.'userpoints', true);

                            update_user_meta($user_id, $prefix.'userpoints', 0);

                            //points label
                            $pointslable = $this->model->woo_pr_get_points_label($user_points);

                            $post_data = array(
                                'post_title' => sprintf(__('%s for Reset points', 'woopoints'), $pointslable),
                                'post_content' => sprintf(__('%s Points Reset', 'woopoints'), $pointslable),
                                'post_author' => $user_id
                            );
                            $log_meta = array(
                                'userpoint' => $user_points,
                                'events' => 'reset_points',
                                'operation' => 'minus' //add or minus
                            );

                            $this->logs->woo_pr_insert_logs($post_data, $log_meta);
                        }
                    }
                }

                // Redirect back to users
                $referrer = wp_get_referer();
                wp_redirect(add_query_arg('reset_points_message', true, $referrer));
                exit;

                break;
            default:
                break;
        }
    }

    /**
     * Add submenu un WooCommerce section.
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_users_points_log_page() {
        add_submenu_page('woocommerce', __('Points Log', 'woopoints'), __('Points Log', 'woopoints'), 'manage_options', 'woo-points-log', array($this, 'woo_pr_users_points_log'));
    }

    /**
     * Callback function of submenu Points log.
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_users_points_log() {
        include_once( WOO_PR_ADMIN_DIR . '/forms/class-woo-pr-users-points-list.php' );
    }

    /**
     * Add custom fields.
     *
     * Handles to add custom fields in user profile page
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     * */
    public function woo_pr_add_custom_user_profile_fields($user) {

        //get user points
        $userpoints = woo_pr_get_user_points($user->ID);
        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');
        // Apply decimal if enabled
        if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
            $userpoints = round( $userpoints, $woo_pr_number_decimal );
        } else {
            $userpoints = round( $userpoints );
        }
        ?>
        <table class="form-table woo-points-user-profile-balance">
            <tr>
                <th>
                    <label for="woo_userpoints"><?php _e('Current points balance ', 'woopoints'); ?></label>
                </th>
                <td>
                    <h2><?php echo $userpoints; ?></h2>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Adjust the Tool Bar
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_tool_bar($wp_admin_bar) {

        global $current_user;
        // Get decimal points option
        $enable_decimal_points = get_option('woo_pr_enable_decimal_points');
        $woo_pr_number_decimal = get_option('woo_pr_number_decimal');

        $wp_admin_bar->add_group(array(
            'parent' => 'my-account',
            'id' => 'woo-points-actions',
        ));

        //get total users points
        $tot_points = woo_pr_get_user_points();

        // Apply decimal if enabled
        if( $enable_decimal_points=='yes' && !empty($woo_pr_number_decimal) ){
            $tot_points = round( $tot_points, $woo_pr_number_decimal );
        } else {
            $tot_points = round( $tot_points );
        }

        $wp_admin_bar->add_menu(array(
            'parent' => 'woo-points-actions',
            'id' => 'user-balance',
            'title' => __('My Balance:', 'woopoints') . ' ' . $tot_points,
            'href' => admin_url('profile.php')
        ));
    }

    /**
     * Show general and invetory setting into points product type.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_variable_bulk_admin_custom_js() {

        if ('product' != get_post_type()) :
            return;
        endif;
        ?>
        <script type='text/javascript'>
        	jQuery(document).ready(function($){

        		var product_type = $( 'select#product-type' ).val();
        		if(product_type == 'woo_pr_points') {
        			$('ul.product_data_tabs li.general_options').show();
        			$('div#general_product_data div.options_group.show_if_downloadable').hide();
        			$('div#woo_pr_and_rewards').parent().hide();
        		}
        		$( document ).on( 'change', 'select#product-type', function(){

        			var product_type = $( 'select#product-type' ).val();
        			$('div#woo_pr_and_rewards').parent().show();
        			if(product_type == 'woo_pr_points') {
	        			$('ul.product_data_tabs li.general_options').show();
	        			$('div#general_product_data div.options_group.show_if_downloadable').hide();
	        			$('div#woo_pr_and_rewards').parent().hide();
	        		}
        		} );
        	});
        	jQuery('ul.product_data_tabs li.inventory_options').addClass('show_if_woo_pr_points');
        	jQuery('div#general_product_data div.options_group.pricing').addClass('show_if_woo_pr_points');
        	jQuery('div#inventory_product_data p._manage_stock_field, div#inventory_product_data div.stock_fields').addClass('show_if_woo_pr_points');
        </script>
        <?php
    }

    /**
     * Add custom product type points.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_add_custom_product_type($types) {

        $types['woo_pr_points'] = __('Points product', 'woopoints');
        return $types;
    }

    /**
     * Add custom product type points into WooCommerce class.
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function woo_pr_woocommerce_product_class($classname, $product_type) {

        if ($product_type == 'points') { // notice the checking here.
            $classname = 'WC_Product_Points';
        }

        return $classname;
    }

    /**
	 * Apply Points to Previous Orders
	 *
	 * Handles to apply points to previous orders
	 *
	 * @package WooCommerce - Points and Rewards
	 * @since 1.0.0
	 */
	public function woo_pr_apply_points_for_previous_orders() {

		// Check if action is set
		if( !empty( $_GET['points_action'] ) && $_GET['points_action'] == 'apply_points'
			&& !empty( $_GET['page'] ) && $_GET['page'] == 'wc-settings'
			&& !empty( $_GET['tab'] ) && $_GET['tab'] == 'woopr-settings' ) {

			$prefix = WOO_PR_META_PREFIX;

			// perform the action in manageable chunks
			$success_count  = 0;
			$old_order_args = array(
									'fields'		=> 'ids',
									'post_type'		=> 'shop_order',
									'post_status'	=> array( 'wc-completed', 'wc-processing' ),
									'posts_per_page'=> '-1',
									'meta_query' 	=> array(
															array(
																'key'     => $prefix.'points_order_earned',
																'compare' => 'NOT EXISTS'
															),
									)
								);

			// Get all order ids for which our meta is not set
			$order_query 	= new WP_Query( $old_order_args );
			$order_ids		= !empty( $order_query->posts ) ? $order_query->posts : array();

			// otherwise go through the results and set the order numbers
			if ( !empty( $order_ids ) && is_array( $order_ids ) ) {

				foreach( $order_ids as $order_id ) {

                    $this->public->woo_pr_order_processing_completed_update_points( $order_id );

					$success_count++;
				} //end foreach loop
			} //end if check retrive payment ids are array

			$redirectargs = array(
									'page'				=>	'wc-settings',
									'tab'				=>	'woopr-settings',
									'message'			=>	'woopr-orders-updated',
									'success-count' 	=>	$success_count,
									'points_action' 	=>	false
								);

			$redirect_url = add_query_arg( $redirectargs, admin_url( 'admin.php' ) );
			wp_redirect( $redirect_url );
			exit;
		} //end if check if there is fulfilling condition proper for applying discount for previous orders
	}

	/**
	 * Show success message
	 *
	 * Handles to show success message when points
	 * are updated for previous orders
	 *
	 * @package WooCommerce - Points and Rewards
	 * @since 1.0.0
	 */
	public function woo_pr_admin_settings_order_updated_notice(){

		// Check if action is set
		if( !empty( $_GET['message'] ) && $_GET['message'] == 'woopr-orders-updated'
			&& !empty( $_GET['page'] ) && $_GET['page'] == 'wc-settings'
			&& !empty( $_GET['tab'] ) && $_GET['tab'] == 'woopr-settings' ) {

			?>
	        <div class="updated">
	            <p>
	                <?php                     
	                echo sprintf( __( '%d order(s) updated.','woopoints' ), $_GET['success-count'] );
	                ?>
	            </p>
	        </div><?php
		}
	}

    /**
     * Show WooCommerce Order Points
     *
     * Handles to show earned points and redeemed points
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    public function woo_pr_admin_order_data_after_order_details( $order ){

        $prefix = WOO_PR_META_PREFIX;

        $order_id = $order->get_id();
        // Get earned points and redeemed points
        $points_earned   = get_post_meta( $order_id, $prefix.'points_order_earned', true);
        $points_redeemed = get_post_meta( $order_id, $prefix.'redeem_order', true);

        if( !empty($points_earned) || !empty($points_redeemed) ){
            ?>
            <div class='clear'></div>
            <h3><?= __( 'Points', 'woopoints' ); ?></h3>
            <p class="form-field form-field-wide wp-pr-user-order-points">
                <strong class="earned-label"><?= __( 'Earned:', 'woopoints' ); ?></strong> <span><?= !empty($points_earned) ? $points_earned : __( 'N/A', 'woopoints' ); ?></span>
            </p>
            <p class="form-field form-field-wide wp-pr-user-order-points">
                <strong class="redeemed-label"><?= __( 'Redeemed:', 'woopoints' ); ?></strong> <span><?= !empty($points_redeemed) ? $points_redeemed : __( 'N/A', 'woopoints' ); ?></span>
            </p>
            <?php
        }
    }

    /**
     * Adding Hooks
     *
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    function add_hooks() {

        // Add filter for adding plugin settings
        add_filter('woocommerce_get_settings_pages', 'woo_pr_admin_settings_tab');

        // Add action to product category fields
        add_action('product_cat_add_form_fields', array($this, 'woo_pr_product_category_add_fields_html'), 20, 1);
        add_action('product_cat_edit_form_fields', array($this, 'woo_pr_product_category_edit_fields_html'), 20, 1);

        // Add action to save product category fields
        add_action('edited_product_cat', array($this, 'woo_pr_save_taxonomy_product_category_meta'), 10, 1);
        add_action('create_product_cat', array($this, 'woo_pr_save_taxonomy_product_category_meta'), 10, 1);

        add_action('admin_footer', array($this, 'woo_pr_variable_bulk_admin_custom_js'), 15);
        // add a product type
        add_filter('product_type_selector', array($this, 'woo_pr_add_custom_product_type'));
        add_filter('woocommerce_product_class', array($this, 'woo_pr_woocommerce_product_class'), 10, 2);

        // add 'Points Earned' column header to the product category list table
        add_filter('manage_edit-product_cat_columns', array($this, 'woo_pr_add_product_category_list_table_points_column_header'));

        // add 'Points Earned' column content to the product category list table
        add_filter('manage_product_cat_custom_column', array($this, 'woo_pr_add_product_category_list_table_points_column'), 10, 3);

        // Add action to add metabox for product
        add_action('add_meta_boxes', array($this, 'woo_pr_product_metabox'));

        // Add action to product meta fields
        add_action('save_post', array($this, 'woo_pr_product_meta_fields_save'));

        // Add Cusom column Content
        add_action('manage_users_custom_column', array($this, 'woo_pr_show_points_column_content'), 10, 3);

        // Add Cusom column title
        add_filter('manage_users_columns', array($this, 'woo_pr_add_points_column'));

        // mark up for popup
        add_action('admin_footer-users.php', array($this, 'woo_pr_points_user_balance_popup'));

        //AJAX Call for adjust user points
        add_action('wp_ajax_woo_pr_adjust_user_points', array($this, 'woo_pr_adjust_user_points'));
        add_action('wp_ajax_nopriv_woo_pr_adjust_user_points', array($this, 'woo_pr_adjust_user_points'));

        // Add actions to add reset points in bulk actions
        add_action('admin_footer-users.php', array($this, 'woo_pr_add_reset_points_to_bulk_actions'));

        // Add actions to reset points when reset points bulk actions performed
        add_action('load-users.php', array($this, 'woo_pr_reset_points'));

        add_action('admin_menu', array($this, 'woo_pr_users_points_log_page'), 99);

        // Add Custom field to user profile
        add_action('profile_personal_options', array($this, 'woo_pr_add_custom_user_profile_fields'));

        // Add menu in admin bar
        add_action('admin_bar_menu', array($this, 'woo_pr_tool_bar'));

        // add action to apply points to previous orders
		add_action('admin_init', array($this, 'woo_pr_apply_points_for_previous_orders'));

		// Admin notice for settings moved
        add_action('admin_notices', array($this, 'woo_pr_admin_settings_order_updated_notice'));

        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'woo_pr_admin_order_data_after_order_details' ) );
    }
}
?>