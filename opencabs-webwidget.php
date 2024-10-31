<?php
/*
Plugin Name:  OpenCabs - Taxi and private hire bookings
Plugin URI:   http://opencabs.com
Description:  OpenCabs - Taxi and private hire bookings
Version:      1.2
Author:       Ievgen Karelin
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

require( dirname( __FILE__ ) . '/opencabs-webwidget-snippets.php' );

//$GLOBALS['webwidget']['api_key'] = 'abc';
//$GLOBALS['webwidget']['backend_url'] = 'http://api.dev.opencabs.com';
$GLOBALS['webwidget']['api_key'] = 'vKFHyLbdVPenDZkxZ9kmam3T4FuNCe2gXVwrrtBQBUQN83weaNJNAJTxex8hTNc8';
$GLOBALS['webwidget']['backend_url'] = 'http://api.opencabs.com';

function opencabs_add_style_script(){
    $screen = get_current_screen();

    if (in_array($screen->id, ['toplevel_page_opencabs', 'opencabs-options_page_opencabs_booking_widget', 'opencabs-options_page_opencabs_quote_widget', 'opencabs-options_page_opencabs_signhup_widget'])) {
        wp_register_script('bootstrap_script', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js');
        wp_enqueue_script('bootstrap_script');

        // Register stylesheets
        wp_register_style('opencabs_style', plugin_dir_url( __FILE__ ) . 'css/ubicabs.css');
        wp_enqueue_style('opencabs_style');

        wp_register_style('bootstrap_style', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css');
        wp_enqueue_style('bootstrap_style');

        wp_register_style('bootstrap_theme_style', plugin_dir_url( __FILE__ ) . 'css/bootstrap-theme.min.css');
        wp_enqueue_style('bootstrap_theme_style');
    }
}
add_action('admin_enqueue_scripts', 'opencabs_add_style_script');

/**
 * top level menu
 */
function opencabs_options_page() {
    // add top level menu page
    add_menu_page(
        'OpenCabs',
        'OpenCabs',
        'manage_options',
        'opencabs',
        function() { opencabs_options_page_html(0); },
        'data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDQ0Ny42NDUgNDQ3LjY0NSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDQ3LjY0NSA0NDcuNjQ1OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPHBhdGggZD0iTTQ0Ny42MzksMjQ0LjQwMmMwLTguODA1LTEuOTg4LTE3LjIxNS01LjU3OC0yNC45MDljLTAuMzctMS45NTYtMC43OTMtMy45MDktMS4zMjItNS44OWwtMzguODg0LTk2LjM2NWwtMC4yNjMtMC44NjcgICBjLTEzLjYwNS00MC41MDktMzIuOTYzLTc4LjAwMS04Mi4wNDktNzguMDAxSDEzMS44NjhjLTUwLjI5NiwwLTY4LjA2OSwzOC40MjEtODEuOTcyLDc3Ljc3NmwtNDAuNjczLDk2LjYgICBDMy4zNDMsMjIyLjE2NywwLDIzMi45NDQsMCwyNDQuNDAydjI5Ljk4NmMwLDQuNjM2LDAuNTQ4LDkuMTcxLDEuNTksMTMuNTM5QzAuNTc3LDI5MC41NjYsMCwyOTMuNDEsMCwyOTYuNDA4djg5LjE4NSAgIGMwLDEzLjA3OCwxMC42MDIsMjMuNjgyLDIzLjY4LDIzLjY4Mmg0OS4xNGMxMy4wNzEsMCwyMy42NzMtMTAuNjA0LDIzLjY3My0yMy42ODJ2LTQ0LjU5OWgyNTcuNDZ2NDQuNTk5ICAgYzAsMTMuMDc4LDEwLjYwNCwyMy42ODIsMjMuNjgzLDIzLjY4Mmg0Ni4zMjZjMTMuMDgzLDAsMjMuNjgzLTEwLjYwNCwyMy42ODMtMjMuNjgydi04OS4xOTVjMC0yLjk4Ny0wLjU4My01Ljg0NC0xLjU4OC04LjQ3NCAgIGMxLjAzOC00LjM3NSwxLjU4OC04LjkwNSwxLjU4OC0xMy41NHYtMjkuOTgxSDQ0Ny42Mzl6IE03OC43NTQsMTI1LjgyMWMxNS40ODMtNDMuNjgzLDI3LjkzNC01Ny4wMTgsNTMuMTE0LTU3LjAxOGgxODcuNjY0ICAgYzI0Ljk5NSwwLDM4LjkxMywxNC44NzMsNTMuMDU2LDU2LjgzbDI4LjM3NSw1Ny41MDJjLTkuMjY1LTMuNDMxLTE5LjQ2MS01LjMzNS0zMC4xNzMtNS4zMzVINzYuODQ5ICAgYy05LjY0NSwwLTE4Ljg2MiwxLjU1MS0yNy4zNjYsNC4zNThMNzguNzU0LDEyNS44MjF6IE0xMDMuMTI5LDI4NS43NzZINTEuMjgxYy05LjMzNSwwLTE2LjkwNi03LjU3OC0xNi45MDYtMTYuOTEyICAgYzAtOS4zMzcsNy41NzEtMTYuOTEsMTYuOTA2LTE2LjkxaDUxLjg0OGM5LjMzOSwwLDE2LjkxLDcuNTczLDE2LjkxLDE2LjkxQzEyMC4wMzksMjc4LjE5OCwxMTIuNDYzLDI4NS43NzYsMTAzLjEyOSwyODUuNzc2eiAgICBNMjg2LjI4NCwyODIuMzg5aC0xMjAuNmMtNS45MTMsMC0xMC43MDQtNC43OTQtMTAuNzA0LTEwLjcwNGMwLTUuOTIxLDQuNzkxLTEwLjcxMywxMC43MDQtMTAuNzEzaDEyMC42ICAgYzUuOTIsMCwxMC43MSw0Ljc5MiwxMC43MSwxMC43MTNDMjk2Ljk5NCwyNzcuNTk1LDI5Mi4yMDQsMjgyLjM4OSwyODYuMjg0LDI4Mi4zODl6IE0zOTUuMDUxLDI4NS43NzZoLTUxLjg0NiAgIGMtOS4zNDMsMC0xNi45MS03LjU3OC0xNi45MS0xNi45MTJjMC05LjMzNyw3LjU3My0xNi45MSwxNi45MS0xNi45MWg1MS44NDZjOS4zNDMsMCwxNi45MTYsNy41NzMsMTYuOTE2LDE2LjkxICAgQzQxMS45NjcsMjc4LjE5OCw0MDQuMzk0LDI4NS43NzYsMzk1LjA1MSwyODUuNzc2eiIgZmlsbD0iI2EwYTVhYSIvPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo='
    );

    add_submenu_page('opencabs', 'General', 'General', 'manage_options', 'opencabs', function() {});

    $options = get_option('opencabs_options');

    $is_logged_in = false;

    if ($options['opencabs_field_username'] != '' && $options['opencabs_field_token'] != '') {
        $is_logged_in = true;
    }

    if ($is_logged_in) {
        add_submenu_page('opencabs', 'Booking widget', 'Booking widget', 'manage_options', 'opencabs_booking_widget', function () {
            opencabs_options_page_html(1);
        });
        add_submenu_page('opencabs', 'Quote widget', 'Quote widget', 'manage_options', 'opencabs_quote_widget', function () {
            opencabs_options_page_html(2);
        });
        add_submenu_page('opencabs', 'Signup/Login widget', 'Signup/Login widget', 'manage_options', 'opencabs_signhup_widget', function () {
            opencabs_options_page_html(3);
        });
    }
}

/**
 * custom option and settings
 */
function opencabs_settings_init() {
    // register a new setting for "opencabs" page
    register_setting( 'opencabs', 'opencabs_options' );

    $options = get_option('opencabs_options');
    $is_logged_in = false;

    if ($options['opencabs_field_username'] != '' && $options['opencabs_field_token'] != '') {
        $is_logged_in = true;
    }

    add_settings_section(
            'opencabs_section_general',
            __('Settings', 'opencabs'),
            'opencabs_section_general_cb',
            'opencabs'
    );

    add_settings_field(
        'opencabs_field_username',
        __( 'Username', 'opencabs' ),
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_username',
            'class' => $is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_password',
        __( 'Password', 'opencabs' ),
        'opencabs_field_password_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_password',
            'class' => $is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_token',
        null,
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_token',
            'class' => 'hidden'
        ]
    );

    add_settings_field(
        'opencabs_field_nodeId',
        null,
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_nodeId',
            'class' => 'hidden'
        ]
    );

    add_settings_field(
        'opencabs_field_publisherId',
        null,
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_publisherId',
            'class' => 'hidden'
        ]
    );

    add_settings_field(
        'opencabs_field_publisher_name',
        null,
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_publisher_name',
            'class' => 'hidden'
        ]
    );

    add_settings_field(
        'opencabs_field_credentials',
        __( 'OpenCabs credentials', 'opencabs' ),
        'opencabs_text_label_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
                'id' => 'opencabs_field_username',
                'class' => !$is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_application_id',
        __( 'Application Id', 'opencabs' ),
        'opencabs_text_label_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_application_id',
            'class' => !$is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_default_language',
        __( 'Default Language', 'opencabs' ),
        'opencabs_select_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_default_language',
            'options' => [
                'English (en)' => 'en',
                'French (fr)' => 'fr',
                'Italian (it)' => 'it',
                'Swedish (sw)' => 'sv'
            ],
            'class' => !$is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_t_and_c',
        __( 'Terms and Conditions URL', 'opencabs' ),
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_t_and_c',
            'class' => !$is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_bounding_box',
        __( 'Search address inside', 'opencabs' ),
        'opencabs_bounding_box_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'google_north' => 'google_north',
            'google_south' => 'google_south',
            'google_east' => 'google_east',
            'google_west' => 'google_west',
            'class' => !$is_logged_in ? 'hidden' : ''
        ]
    );

    add_settings_field(
        'opencabs_field_custom_css',
        __( 'Custom CSS file path', 'opencabs' ),
        'opencabs_text_input_field_cb',
        'opencabs',
        'opencabs_section_general',
        [
            'id' => 'opencabs_field_custom_css',
            'class' => !$is_logged_in ? 'hidden' : ''
        ]
    );
}

/**
 * register our opencabs_settings_init to the admin_init action hook
 */
add_action( 'admin_init', 'opencabs_settings_init' );

/**
 * custom option and settings:
 * callback functions
 */

function opencabs_section_general_cb( $args ) {
    ?>

    <?php
}

function opencabs_text_input_field_cb($args) {

    // First, we read the options collection
    $options = get_option('opencabs_options');

    // Next, we update the name attribute to access this element's ID in the context of the display options array
    // We also access the show_header element of the options collection in the call to the checked() helper function
    $html = '<input type="text" id="' . $args['id'] . '" name="opencabs_options[' . $args['id'] . ']" value="' . $options[$args['id']] . '"/>';

    echo $html;

}

function opencabs_field_password_cb($args) {

    // First, we read the options collection
    $options = get_option('opencabs_options');

    // Next, we update the name attribute to access this element's ID in the context of the display options array
    // We also access the show_header element of the options collection in the call to the checked() helper function
    $html = '<input type="text" id="opencabs_field_password" name="opencabs_options[opencabs_field_password]" value="' . $options['opencabs_field_password'] . '"/>';

    echo $html;
}

function opencabs_text_label_field_cb($args) {

    // First, we read the options collection
    $options = get_option('opencabs_options');

    // Here, we'll take the first argument of the array and add it to a label next to the checkbox
    $html = '<label> '  . $options[$args['id']] . '</label>';

    echo $html;

}

function opencabs_select_field_cb($args) {

    // First, we read the options collection
    $options = get_option('opencabs_options');

    $html = '<select id="' . $args['id'] . '"name="opencabs_options[' . $args['id'] . ']">';

    foreach ($args['options'] as $k => $v) {
        $html .= '<option value="' . $v .'" ' . (isset( $options[ $args['id'] ] ) ? ( selected( $options[ $args['id'] ], $v, false ) ) : ( '' )) . '>' . $k . '</option>';
    }

    $html .= '</select>';

    echo $html;
}

function opencabs_bounding_box_field_cb($args) {

    // First, we read the options collection
    $options = get_option('opencabs_options');

    $html = '<div id="map" style="height: 250px;"></div>';
    $html .= '<div class="clearfix">';

    $html .= '<div style="float: left;">';
    $html .= '<p>Min latitude *</p>';
    $html .= '<input type="text" id="' . $args['google_south'] . '" name="opencabs_options[' . $args['google_south'] . ']" readonly value="' . $options[$args['google_south']] . ' " required />';
    $html .= '</div>';

    $html .= '<div style="float: left;">';
    $html .= '<p>Max latitude *</p>';
    $html .= '<input type="text" id="' . $args['google_north'] . '" name="opencabs_options[' . $args['google_north'] . ']" readonly value="' . $options[$args['google_north']] . '" required />';
    $html .= '</div>';

    $html .= '<div style="float: left;">';
    $html .= '<p>Min longitude *</p>';
    $html .= '<input type="text" id="' . $args['google_west'] . '" name="opencabs_options[' . $args['google_west'] . ']" readonly value="' . $options[$args['google_west']] . '" required />';
    $html .= '</div>';

    $html .= '<div style="float: left;">';
    $html .= '<p>Max longitude *</p>';
    $html .= '<input type="text" id="' . $args['google_east'] . '" name="opencabs_options[' . $args['google_east'] . ']" readonly value="' . $options[$args['google_east']] . '" required />';
    $html .= '</div>';

    

    $html .= '</div>';

    echo $html;
}

add_action('admin_post_opencabs_login', 'opencabs_login_action' );
function opencabs_login_action() {
    $options = get_option('opencabs_options');

    $options['opencabs_field_username'] = $_POST['opencabs_options']['opencabs_field_username'];
    $options['opencabs_field_password'] = $_POST['opencabs_options']['opencabs_field_password'];

    update_option( 'opencabs_options', $options);

    //array ( 'action' => 'opencabs_login', 'opencabs_options' => array ( 'opencabs_field_username' => 'test', 'opencabs_field_password' => 'pass', 'opencabs_field_default_language' => 'en', 'opencabs_field_t_and_c' => '', 'google_north' => '0.15', 'google_south' => '3', 'google_east' => '0', 'google_west' => '5', 'opencabs_field_custom_css' => '', ), 'submit' => 'Login', )

    $headers = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'ApiKey' => $GLOBALS['webwidget']['api_key'],
        'Username' => $_POST['opencabs_options']['opencabs_field_username'],
        'Password' => $_POST['opencabs_options']['opencabs_field_password']
    );

    $args = array(
        'body' => array(),
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'cookies' => array()
    );

    $response = wp_remote_post( $GLOBALS['webwidget']['backend_url'] . '/auth/authUser/', $args );
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    $msg = '';

    if (wp_remote_retrieve_response_code($response) == 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        $options['opencabs_field_token'] = $data->accessToken;
        update_option('opencabs_options', $options);

        foreach($data->contexts as $context) {
            if ($context->type == 'publisher') {
                $options['opencabs_field_nodeId'] = $context->nodeId;
                $options['opencabs_field_publisherId'] = $context->id;
                $options['opencabs_field_publisher_name'] = $context->name;
                $options['opencabs_field_credentials'] = $options['opencabs_field_username'];
                update_option('opencabs_options', $options);
                break;
            }
        }
    }
    else {
        $msg = urlencode($data->text);
    }

    $url = add_query_arg( 'error_msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
    wp_safe_redirect( $url );
}

add_action('admin_post_opencabs_logout', 'opencabs_logout_action' );
function opencabs_logout_action() {
    delete_option('opencabs_options');
    $url = add_query_arg( 'error_msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
    wp_safe_redirect( $url );
}

function check_auth_token() {
    $options = get_option('opencabs_options');
    $headers = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'ApiKey' => $GLOBALS['webwidget']['api_key'],
        'Token' => $options['opencabs_field_token'],
        'TokenType' => 'access_token'
    );

    $args = array(
        'body' => array(),
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'cookies' => array()
    );

    $response = wp_remote_post( $GLOBALS['webwidget']['backend_url'] . '/auth/authUser/', $args );

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (wp_remote_retrieve_response_code($response) != 200) {
        $options['opencabs_field_token'] = '';
        update_option('opencabs_options', $options);
    }

}

function get_application_id() {
    $options = get_option('opencabs_options');
    $headers = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'ApiKey' => $GLOBALS['webwidget']['api_key'],
        'Token' => $options['opencabs_field_token'],
        'TokenType' => 'access_token'
    );

    $args = array(
        'body' => array(),
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'cookies' => array()
    );

    $response = wp_remote_get( $GLOBALS['webwidget']['backend_url'] . '/applicationInstances/checkExistsForUrl?url=' . get_site_url(), $args );

    if (wp_remote_retrieve_response_code($response) != 200) {
        return '';
    }
    else {
        return update_application_id_from_response_and_return($response);
    }
}

function create_application_id() {
    $options = get_option('opencabs_options');
    $headers = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'ApiKey' => $GLOBALS['webwidget']['api_key'],
        'Token' => $options['opencabs_field_token'],
        'TokenType' => 'access_token'
    );

    $args = array(
        'body' => array(),
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'cookies' => array()
    );

    $response = wp_remote_post($GLOBALS['webwidget']['backend_url'] . '/applicationInstances/createForUrl?url=' . get_site_url(), $args );

    if (wp_remote_retrieve_response_code($response) != 200) {
        return '';
    }
    else {
        return update_application_id_from_response_and_return($response);
    }
}

function update_application_id_from_response_and_return($response) {
    $options = get_option('opencabs_options');

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    $options['opencabs_field_application_id'] = $data->id;
    update_option('opencabs_options', $options);

    return $data->id;
}

function googleMapsScripts($coordinates) {
    ?>
    <script>
      // This example requires the Drawing library. Include the libraries=drawing
      // parameter when you first load the API. For example:
      // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=drawing">

      var shapes = [];

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 51.5285, lng: 0.2416},
          zoom: 8
        });

        var drawingManager = new google.maps.drawing.DrawingManager({
          drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
          drawingControl: true,
          drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: ['rectangle']
          },
          rectangleOptions: {
            editable: true,
            draggable: true
          }
        });
        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, 'rectanglecomplete', function(event) {
          console.log(event);
          if (drawingManager.getDrawingMode() != null) {
            for (var i=0; i < shapes.length; i++) {
              shapes[i].setMap(null);
            }
            shapes = [];
          }

          shapes.push(event);

          jQuery('#google_north').val(event.getBounds().getNorthEast().lat());
          jQuery('#google_east').val(event.getBounds().getNorthEast().lng());
          jQuery('#google_south').val(event.getBounds().getSouthWest().lat());
          jQuery('#google_west').val(event.getBounds().getSouthWest().lng());
          
        });

        <?php
              if (is_numeric($coordinates['google_north']) && is_numeric($coordinates['google_south']) && is_numeric($coordinates['google_west']) && is_numeric($coordinates['google_east'])) {
                  ?>                    
                    var rect = new google.maps.Rectangle({                      
                      bounds: {
                            north: <?php print $coordinates['google_north'] ?>,
                            south: <?php print $coordinates['google_south'] ?>,
                            east: <?php print $coordinates['google_east'] ?>,
                            west: <?php print $coordinates['google_west'] ?>
                        }
                    });

                    rect.setMap(map);
                    shapes.push(rect);                    
                    map.fitBounds(rect.getBounds());
                  <?php
              }
        ?>
      }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCVLZBOTIDNh0IfBgE9vdI2Y6elx0G4OjU&libraries=drawing&callback=initMap"
            async defer></script>
    <?php
}

/**
 * register our opencabs_options_page to the admin_menu action hook
 */
add_action( 'admin_menu', 'opencabs_options_page' );

/**
 * top level menu:
 * callback functions
 */
function opencabs_options_page_html($tab_index = 0) {
    $options = get_option('opencabs_options');

    check_auth_token();

    $is_logged_in = false;

    if ($options['opencabs_field_username'] != '' && $options['opencabs_field_token'] != '') {
        $is_logged_in = true;
    }

    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // add error/update messages

    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'opencabs_messages', 'opencabs_message', __( 'Settings Saved', 'opencabs' ), 'updated' );
    }

    if( isset( $_GET[ 'tab' ] ) ) {
        $tab_index = $_GET['tab'];
    }

    // show error/update messages
    settings_errors( 'opencabs_messages' );
    ?>

    <div class="wrap opencabs-settings">
        <h1><?php echo __( 'OpenCabs', 'opencabs' ); ?></h1>

        <h2 class="nav-tab-wrapper">
            <a href="?page=opencabs&tab=0" class="nav-tab <?php echo $tab_index == 0 ? 'nav-tab-active' : ''; ?>">General</a>
            <?php if ($is_logged_in) {?>
                <a href="?page=opencabs&tab=1" class="nav-tab <?php echo $tab_index == 1 ? 'nav-tab-active' : ''; ?>">Booking widget</a>
                <a href="?page=opencabs&tab=2" class="nav-tab <?php echo $tab_index == 2 ? 'nav-tab-active' : ''; ?>">Quote widget</a>
                <a href="?page=opencabs&tab=3" class="nav-tab <?php echo $tab_index == 3 ? 'nav-tab-active' : ''; ?>">Signup/Login widget</a>
            <?php } ?>
        </h2>

        <?php if ($tab_index == 0) { ?>
            <?php
            if ($is_logged_in) {
                if (get_application_id() == '') {
                    create_application_id();
                }

                ?>
                <form action="options.php" method="post" novalidate>
                    <?php
                    // output security fields for the registered setting "opencabs"
                    settings_fields( 'opencabs' );
                    // output setting sections and their fields
                    // (sections are registered for "opencabs", each field is registered to a specific section)
                    do_settings_sections( 'opencabs' );
                    // output save settings button
                    submit_button('Save Settings');
                    ?>
                </form>
                <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
                    <?php
                        $redirect = urlencode( $_SERVER['REQUEST_URI'] );
                    ?>
                    <input type="hidden" name="action" value="opencabs_logout">
                    <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <div class="logout-button">
                    <?php
                        submit_button('Logout');
                    ?>
                    </div>
                </form>
                <?php

                googleMapsScripts($options);
            } else {
                    $redirect = urlencode( $_SERVER['REQUEST_URI'] );

                    if ($_GET['error_msg']) {
                        ?> <div class="message error"><?php print urldecode($_GET['error_msg']); ?></div> <?php
                    }
                ?>
                <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" novalidate>
                    <input type="hidden" name="action" value="opencabs_login">
                    <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <div class="updated notice"><p>Please login to complete the settings. If you do not have an account yet, you can register for FREE here: http://business.opencabs.com</p></div>
                    <?php
                    // output security fields for the registered setting "opencabs"
//                    settings_fields( 'opencabs' );
                    // output setting sections and their fields
                    // (sections are registered for "opencabs", each field is registered to a specific section)
                    do_settings_sections( 'opencabs' );
                    // output save settings button
                    submit_button('Login');
                    ?>
                </form>
                <?php }?>
        <?php }
        else if ($tab_index == 1) {
            booking_widget_html_snippet_settings();
        }
        else if ($tab_index == 2) {
            quote_widget_html_snippet_settings();
        }
        else if ($tab_index == 3) {
            login_widget_html_snippet_settings();
        }
        ?>
    </div>
    <?php
}