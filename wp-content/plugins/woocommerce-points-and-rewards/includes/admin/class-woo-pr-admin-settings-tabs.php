<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Woo_pr_Settings')) :

    /**
     * Setting page Class
     * 
     * Handles Settings page functionality of plugin
     * 
     * @package WooCommerce - Points and Rewards
     * @since 1.0.0
     */
    class Woo_pr_Settings extends WC_Settings_Page {

        /**
         * Constructor
         * 
         * Handles to add hooks for adding settings
         * 
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function __construct() {

            global $woo_pr_model; // Declare global variables

            $this->id = 'woopr-settings'; // Get id
            $this->label = __('Points and Rewards', 'woopoints'); // Get tab label
            $this->model = $woo_pr_model; // Declare variable $this->model
            // Add filter for adding tab
            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 20);

            // Add action to show output
            add_action('woocommerce_settings_' . $this->id, array($this, 'woo_pr_output'));

            // Add action for saving data
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'woo_pr_save'));

            // Add a ratio fields woocommerce_admin_fields() field type
            add_action('woocommerce_admin_field_pr_ratio', array($this, 'render_pr_ratio_field'));

            // Add a apply points woocommerce_admin_fields() field type
            add_action('woocommerce_admin_field_woopr_apply_points', array($this, 'render_woopr_apply_points_section'));

            // Add a woopr textarea woocommerce_admin_fields() field type
            add_action('woocommerce_admin_field_woopr_textarea', array($this, 'render_woopr_textarea_section'));

            // Add a woopr rating points woocommerce_admin_fields() field type
            add_action('woocommerce_admin_field_woopr_ratingpoints', array($this, 'render_woopr_ratingpoints_section'));
        }

        /**
         * Handles to add sections for Settings tab
         * 
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function get_sections() {

            // Create array
            $sections = array(
                'woo_pr_genral_setting_points' => __('Points Settings', 'woopoints'),
            );

            return apply_filters('woo_pr_setting_sections', $sections);
        }

        /**
         * Handles to output data
         * $sections
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function woo_pr_output() {

            // Get global variable
            global $current_section;

            // Get settings for current section
            $settings = $this->get_settings($current_section);

            WC_Admin_Settings::output_fields($settings);
        }

        /**
         * Handles to save data
         * 
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function woo_pr_save($option) {

            global $current_section;

            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::save_fields($settings);
        }

        /**
         * Handles to get setting
         * 
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function get_settings($current_section = '') {

            $settings = apply_filters('woo_products_point_settings', array(
                //Setting title
                array(
                    'id'    => 'pr_points_general_settings',
                    'name'  => __('Points Settings', 'woopoints'),
                    'type'  => 'title'
                ),
                //Ratio field for earn points
                array(
                    'id'        => 'woo_pr_ratio_settings_points_monetary_value',
                    'default'   => '1',
                    'type'      => 'hidden',
                ),
                //point field for earn points
                array(
                    'title'     => __('Earn Points Conversion Rate:', 'woopoints'),
                    'desc'      => __('Set the number of points awarded based on the product price.', 'woopoints'),
                    'id'        => 'woo_pr_ratio_settings_points',
                    'default'   => '1',
                    'type'      => 'pr_ratio'
                ),
                //Ratio field for redeem points
                array(
                    'id'        => 'woo_pr_redeem_points_monetary_value',
                    'default'   => '1',
                    'type'      => 'hidden',
                ),
                //Point field for redeem points
                array(
                    'name'      => __('Redeem Points Conversion Rate:', 'woopoints'),
                    'desc'      => __('Set the value of points redeemed for a discount.', 'woopoints'),
                    'id'        => 'woo_pr_redeem_points',
                    'default'   => '100',
                    'type'      => 'pr_ratio'
                ),
                //Ratio field for buy points
                array(
                    'id'        => 'woo_pr_buy_points_monetary_value',
                    'default'   => '1',
                    'type'      => 'hidden',
                ),
                //Point field for buy points
                array(
                    'name'      => __('Buy Points Conversion Rate:', 'woopoints'),
                    'desc'      => __('Set the value for buy points.', 'woopoints'),
                    'id'        => 'woo_pr_buy_points',
                    'default'   => '100',
                    'type'      => 'pr_ratio'
                ),
                //Ratio field for selling points
                array(
                    'id'        => 'woo_pr_selling_points_monetary_value',
                    'default'   => '1',
                    'type'      => 'hidden',
                ),
                //Point field for selling points
                array(
                    'name'      => __('Selling Points Conversion Rate:', 'woopoints'),
                    'desc'      => __('Set the value for selling points.', 'woopoints'),
                    'id'        => 'woo_pr_selling_points',
                    'default'   => '1',
                    'type'      => 'pr_ratio'
                ),
                //Maximum cart discount field
                array(
                    'name'      => __('Maximum Cart Discount:', 'woopoints'),
                    'desc'      => get_woocommerce_currency_symbol().'<p class="description">' . __('Set the maximum discount allowed for the cart when redeeming points. Leave blank to disable.', 'woopoints') . '</p>',
                    'id'        => 'woo_pr_cart_max_discount',
                    'default'   => '',
                    'css'       => 'width: 280px; height: 24px;',
                    'type'      => 'text',
                ),
                //Maximum per product discount
                array(
                    'name'      => __('Maximum Per-Product Discount:', 'woopoints'),
                    'desc'      => get_woocommerce_currency_symbol(). '<p class="description">' . __('Set the maximum per-product discount allowed for the cart when redeeming points. Leave blank to disable.', 'woopoints') . '</p>',
                    'id'        => 'woo_pr_per_product_max_discount',
                    'default'   => '',
                    'css'       => 'width: 280px; height: 24px;',
                    'type'      => 'text',
                ),
                // Singular lable field
                array(
                    'id'        => 'woo_pr_lables_points_monetary_value',
                    'default'   => 'Points',
                    'type'      => 'hidden',
                ),
                //pluaral lable field
                array(
                    'name'      => __('Points Label:', 'woopoints'),
                    'desc'      => __('The label used to refer the points on the frontend, singular and plural.', 'woopoints'),
                    'desc'      => __('The label used to refer the points on the frontend, singular and plural.', 'woopoints'),
                    'id'        => 'woo_pr_lables_points',
                    'default'   => 'Point',
                    'type'      => 'pr_ratio'
                ),
                //Enable Decimal in Points
                array(
                    'name'      => __('Enable Decimal in Points:', 'woopoints'),
                    'desc'      => '<p class="description">' . __('Enable the decimal points when points are awarded to customer.', 'woopoints') . '</p>',
                    'id'        => 'woo_pr_enable_decimal_points',
                    'default'   => '',
                    'css'       => 'width: 280px; height: 24px;',
                    'type'      => 'checkbox',
                ),
                //Number of Decimals
                array(
                    'name'      => __('Number of Decimals:', 'woopoints'),
                    'desc'      => '<p class="description">' . __('This sets the number of decimal points.', 'woopoints') . '</p>',
                    'id'        => 'woo_pr_number_decimal',
                    'default'   => '2',
                    'css'       => 'width: 50px',
                    'type'      => 'number',
                    'custom_attributes' => array(
						'min'  => 0,
						'step' => 1,
					),
                ),
                array(
                    'type'  => 'sectionend', 
                    'id'    => 'pr_points_general_settings'
                ),

                //Setting title
                array(
                    'id'    => 'pr_points_messages_settings',
                    'name'  => __('Product / Cart / Checkout Messages', 'woopoints'),
                    'type'  => 'title'
                ),
                //Single product page message field
                array(
                    'name'      => __('Single Product Page Message:', 'woopoints'),
                    'desc'      => __('Add an optional message to the single product page below the price. Customize the message using {points} and {points_label}. Limited HTML is allowed. Leave blank to disable.', 'woopoints'),
                    'id'        => 'woo_pr_single_product_message',
                    'css'       => 'width: 99%; height: 100px;',
                    'default'   => sprintf(__('Purchase this product now and earn %s!', 'woopoints'), '{points} {points_label}'),
                    'type'      => 'woopr_textarea',
                ),
                // earn points cart/checkout page message
                array(
                    'name'      => __('Earn Points Cart / Checkout Page Message:', 'woopoints'),
                    'desc'      => __('Displayed on the cart and checkout page when points are earned. Customize the message using {points} and {points_label}. Limited HTML is allowed.', 'woopoints'),
                    'id'        => 'woo_pr_earn_points_cart_message',
                    'css'       => 'width: 99%; height: 100px;',
                    'default'   => sprintf(__('Complete your order and earn %s for a discount on a future purchase', 'woopoints'), '{points} {points_label}'),
                    'type'      => 'woopr_textarea',
                ),
                // redeem points cart/checkout page message
                array(
                    'name'      => __('Redeem Points Cart / Checkout Page Message:', 'woopoints'),
                    'desc'      => __('Displayed on the cart and checkout page when points are available for redemption. Customize the message using {points}, {points_value}, and {points_label}. Limited HTML is allowed.', 'woopoints'),
                    'id'        => 'woo_pr_redeem_points_cart_message',
                    'css'       => 'width: 99%; height: 100px;',
                    'default'   => sprintf(__('Use %s for a %s discount on this order!', 'woopoints'), '{points} {points_label}', '{points_value}'),
                    'type'      => 'woopr_textarea',
                ),
                //Guest checkout page message field
                array(
                    'id'        => 'woo_pr_guest_checkout_page_message',
                    'desc'      => __('Displayed on the cart and checkout page for guest users to indicate to create an account for earn the points. Customize the message using {points}, {points_label} and {signup_points}. Limited HTML is allowed. Leave blank to disable.', 'woopoints'),
                    'name'      => __('Guest User Cart / Checkout Page Message:', 'woopoints'),
                    'css'       => 'width: 99%; height: 100px;',
                    'default'   => sprintf(__('You need to register an account in order to earn %s', 'woopoints'), ' {points} {points_label}'),
                    'type'      => 'woopr_textarea'
                ),
                //Guest checkout page buy message field
                array(
                    'id'        => 'woo_pr_guest_checkout_page_buy_message',
                    'desc'      => __('Displayed on the cart and checkout page for guest users to indicate to create an account to get points into their account. Customize the message using {points} and {points_label}. Limited HTML is allowed. Leave blank to disable.', 'woopoints'),
                    'name'      => __('Guest User Cart / Checkout Page Buy Message:', 'woopoints'),
                    'css'       => 'width: 99%; height: 100px;',
                    'default'   => sprintf(__('You need to register an account in order to fund %s into your account.', 'woopoints'), ' {points} {points_label}'),
                    'type'      => 'woopr_textarea'
                ),
                // user history message field
                array(
                    'id'        => 'woo_pr_guest_user_history_message',
                    'desc'      => __('Displayed points history message for guest users to indicate to login into an account to view points of their account. Customize the message using {points_label}. Limited HTML is allowed. Leave blank to disable.', 'woopoints'),
                    'name'      => __('Guest User Points History Message:', 'woopoints'),
                    'css'       => 'width: 99%; height: 100px;',
                    'default'   => sprintf(__('Sorry, You have not earned any %s yet.', 'woopoints'), '{points_label}'),
                    'type'      => 'woopr_textarea'
                ),
                array(
                    'type'  => 'sectionend', 
                    'id'    => 'pr_points_messages_settings'
                ),

                //Setting title
                array(
                    'id'    => 'pr_points_earn_action_settings',
                    'name'  => __('Points Earned For Actions', 'woopoints'),
                    'type'  => 'title'
                ),
                //Points earn for signup field
                array(
                    'name'      => __('Points earned for account signup:', 'woopoints'),
                    'desc'      => '<br>' . __('Enter the amount of points earned when a customer signs up for a new account.', 'woopoints'),
                    'type'      => 'text',
                    'id'        => 'woo_pr_earn_for_account_signup',
                    'default'   => '500'
                ),
                //Button for all previous order
                array(
                    'title'         => __('Apply Points to Previous Orders:', 'woopoints'),
                    'desc'          => __('This will apply points to all previous orders (processing and completed) and cannot be reversed.', 'woopoints'),
                    'button_text'   => __('Apply Points', 'woopoints'),
                    'type'          => 'woopr_apply_points',
                    'id'            => 'woo_pr_apply_points_to_previous_orders',
                    'class'         => 'wc-points-rewards-apply-button',
                ),
                array(
                    'type'  => 'sectionend', 
                    'id'    => 'pr_points_earn_action_settings'
                ),

                //Setting title
                array(
                    'id'    => 'pr_points_misc_settings',
                    'name'  => __('Misc Settings', 'woopoints'),
                    'type'  => 'title'
                ),
                //Checkbox for delete all data form database
                array(
                    'title'     => __('Delete Options', 'woopoints'),
                    'id'        => 'woo_pr_delete_options',
                    'default'   => 'no',
                    'desc'      => '<p>'.__('If you don\'t want to use the points and rewards plugin on your site anymore, you can check the delete options box. This makes sure, that all the settings and tables are being deleted from the database when you deactivate the plugin.', 'woopoints').'</p>',
                    'type'      => 'checkbox'
                ),
                //Checkbox for refund order with points refunds
                array(
                    'title'     => __('Enable points removal for refunded orders', 'woopoints'),
                    'desc'      => __('Specify whether you want to refund earned and redeemed points when order gets refunded.   ', 'woopoints'),
                    'id'        => 'woo_pr_revert_points_refund_enabled',
                    'default'   => 'no',
                    'type'      => 'checkbox'
                ),
                array(
                    'type'  => 'sectionend', 
                    'id'    => 'pr_points_misc_settings'
                ),
                //Checkbox for product review points
                //Setting title
                array(
                    'id'    => 'pr_points_review_settings',
                    'name'  => __('Review Settings', 'woopoints'),
                    'type'  => 'title'
                ),
                array(
                    'id'        => 'woo_pr_enable_reviews',
                    'title'     => __('Enable Review Points', 'woopoints'),
                    'desc'      => __('Check this box if you want to assign points to customers when they add a review on any product.', 'woopoints'),
                    'default'   => 'no',
                    'type'      => 'checkbox'
                ),
                array(
                    'id'        => 'woo_pr_review_points',
                    'class'     => 'woo_pr_review_points',
                    'title'     => __('Points earned for Review', 'woopoints'),
                    'desc'      => __('Enter the number of points earned when a customer add a review on any product.', 'woopoints'),
                    'type'      => 'woopr_ratingpoints',
                    'css'       => 'width: 80px;',
                ),
                array(
                    'type'  => 'sectionend', 
                    'id'    => 'pr_points_review_settings'
                ),

            ));

            return apply_filters('woocommerce_get_settings_woopr_settings', $settings, $current_section);
        }

        /**
         * Render the 'Woopr Textarea' section
         *
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function render_woopr_textarea_section( $field ) {

            $option_value      = get_option( $field['id'], $field['default'] );
            $description       = ( isset($field['desc']) && !empty($field['desc']) ) ? '<p style="margin-top:0">' . wp_kses_post( $field['desc'] ) . '</p>' : '';
            $tooltip_html      = ( isset($field['desc_tip']) && !empty($field['desc_tip']) ) ? wc_help_tip( $field['desc_tip'] ) : '' ;
            $custom_attributes = array();

            // Custom attribute handling.
            if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
                foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
                    <?php echo $tooltip_html; ?>
                </th>
                <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
                    
                    <textarea
                        name="<?php echo esc_attr( $field['id'] ); ?>"
                        id="<?php echo esc_attr( $field['id'] ); ?>"
                        style="<?php echo esc_attr( $field['css'] ); ?>"
                        class="<?php echo esc_attr( $field['class'] ); ?>"
                        placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                        <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                        ><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
                    <?php echo $description; ?>
                </td>
            </tr>
            <?php
        }

        /**
         * Render the 'Woopr Rating Points' section
         *
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function render_woopr_ratingpoints_section( $field ) {

            $option_value      = get_option( $field['id'], $field['default'] );

            $description       = ( isset($field['desc']) && !empty($field['desc']) ) ? '<p style="margin-top:0">' . wp_kses_post( $field['desc'] ) . '</p>' : '';
            $tooltip_html      = ( isset($field['desc_tip']) && !empty($field['desc_tip']) ) ? wc_help_tip( $field['desc_tip'] ) : '' ;
            $custom_attributes = array();

            // Custom attribute handling.
            if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
                foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
                    <?php echo $tooltip_html; ?>
                </th>
                <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
                    <?php
                    for ( $star_num = 5; $star_num >= 1; $star_num-- ) {

                        $val = isset( $option_value[$star_num] ) ? $option_value[$star_num] : '';

                        echo '<div class="woo_pr_sub_field_item"><fieldset>';

                        //Display Star description
                        for ( $i = 1; $i <= 5; $i++ ) {
                            $star_filled = ( $star_num >= $i ) ? 'dashicons-star-filled' : 'dashicons-star-empty';
                            echo '<span class="dashicons '. $star_filled .'"></span>';
                        }

                        echo '&nbsp;&nbsp;<input 
                        name="'. esc_attr( $field['id'] ).'['.$star_num.']" 
                        id="'.esc_attr( $field['id'] ).'-star-'.$star_num.'" 
                        style="'.esc_attr( $field['css'] ).'"
                        class="'.esc_attr( $field['class'] ).'"
                        placeholder="'.esc_attr( $field['placeholder'] ).'"
                        type="number" min="0" 
                        value="'.esc_attr( $val ).'" />&nbsp;&nbsp;';
                        echo "<span>". __( ' Point(s)', 'woopoints' ) ."</span>";
                        echo '</fieldset></div>';
                    }
                    ?>
                    <?php echo $description; ?>
                </td>
            </tr>
            <?php
        }

        /**
         * Render the Earn Points/Redeem Points conversion ratio section
         *
         * @package WooCommerce - Points and Rewards
         * @param array $field associative array of field parameters
         * @since 1.0.0
         */
        public function render_pr_ratio_field($field) {

            // If field title is not empty and field id is not empty
            if (isset($field['title']) && isset($field['id'])) :

                $points = get_option($field['id'], $field['default']);
                $monetary_value = get_option($field['id'] . '_monetary_value');
                ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for=""><?php echo wp_kses_post($field['title']); ?></label>
                        <?= ( isset($field['desc_tip']) && !empty($field['desc_tip']) ) ? wc_help_tip( $field['desc_tip'] ) : '' ; ?>
                    </th>
                    <td class="forminp forminp-text">
                        <?php if ($field['id'] != 'woo_pr_lables_points') { ?>

                            <fieldset>
                                <input name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" type="number" style="max-width: 80px;" min="0" value="<?php echo esc_attr($points); ?>" />
                                <span>&nbsp;<?php _e('Points', 'woopoints'); ?>&nbsp;&#61;&nbsp;&nbsp;<?php echo get_woocommerce_currency_symbol(); ?></span>
                                <input name="<?php echo esc_attr($field['id'] . '_monetary_value'); ?>" id="<?php echo esc_attr($field['id'] . '_monetary_value'); ?>" type="number" min="0" style="max-width: 80px;" value="<?php echo esc_attr($monetary_value); ?>" />
                                <br>
                                <label for="<?php echo $field['id']; ?>"><?php echo wp_kses_post($field['desc']); ?></label>
                            </fieldset>

                        <?php } if ($field['id'] == 'woo_pr_lables_points') { ?>

                            <fieldset>
                                <input name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" type="text" style="max-width: 80px;" value="<?php echo esc_attr($points); ?>" />
                                <input name="<?php echo esc_attr($field['id'] . '_monetary_value'); ?>" id="<?php echo esc_attr($field['id'] . '_monetary_value'); ?>" type="text" style="max-width: 80px;" value="<?php echo esc_attr($monetary_value); ?>" />
                                <br>
                                <label for=""><?php echo wp_kses_post($field['desc']); ?></label>
                            </fieldset>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            endif;
        }

        /**
         * Render the 'Apply Points to all previous orders' section
         *
         * @package WooCommerce - Points and Rewards
         * @since 1.0.0
         */
        public function render_woopr_apply_points_section($field) {
            if (isset($field['title']) && isset($field['button_text']) && isset($field['id'])) :
                ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="apply_points"><?php echo wp_kses_post($field['title']); ?></label>
                        <!-- <span class="woocommerce-help-tip"></span> -->
                    </th>
                    <td class="forminp forminp-text">
                        <fieldset>
                            <a href="<?php echo esc_url(add_query_arg(array('points_action' => 'apply_points'))); ?>" class="button" id="<?php echo $field['id']; ?>"><?php echo esc_html($field['button_text']); ?></a>
                        </fieldset>
                    </td>

                </tr>
                <?php
           endif;
        }

    }

    endif;

return new Woo_pr_Settings();
