<?php
add_shortcode('opencabs_widget', 'opencabs_widget_shortcode');

$GLOBALS['webwidget']['base_url'] = 'https://widgets.opencabs.com';
//$GLOBALS['webwidget']['base_url'] = 'https://widgets.dev.opencabs.com';
//$GLOBALS['webwidget']['base_url'] = 'https://webwidgets.local';

function opencabs_widget_shortcode($atts, $content = '')
{
    extract(shortcode_atts(array(
        'id' => false
    ), $atts));

    if (!isset($id) || !$id)
        return '';

    $snippet = get_option('uhs_snippet-' . $id);

    return $snippet;
}
function get_widget_url_params($widget_type, $clean){
    $options = get_option('opencabs_options');
    if ($widget_type=='booker' || $widget_type=='quote'){
        return 'widget=' . $widget_type . 
        '&id=oc_' . $widget_type . '_widget' .
        ($clean['format'] == 1 ? '&theme=classic' : '') .
        '&publisherId=' . $options['opencabs_field_publisherId'] .
        '&publisherName=' . $options['opencabs_field_publisher_name'] .
        '&load_latest_quote=1' .
        '&publisherContactEmail=' . $options['opencabs_field_username'] .
        '&publisherBookingsEmail=' . $options['opencabs_field_username'] .
//            '&external_css_url=http://beta.ldntaxi.co.uk/wp-content/uploads/2016/06/oc_booker.css'.
        '&terms_and_conditions_url=' . $options['opencabs_field_t_and_c'] .
        '&language=' . $clean['language'] .
        '&google_language=' . $clean['language'] .
        '&center_lat=' . ((intval($clean['google_south']) + intval($clean['google_north'])) / 2) .
        '&center_lng=' . ((intval($clean['google_east']) + intval($clean['google_west'])) / 2) .
        '&google_region=' . $clean['language'] .
        '&google_north=' . $clean['google_north'] .
        '&google_east=' . $clean['google_east'] .
        '&google_south=' . $clean['google_south'] .
        '&google_west=' . $clean['google_west'] .
        '&country_code=' . $clean['language'] .
        '&region=' . $clean['language'] .
        '&maximum_google_range_km=300';
    }
}
function opencabs_html_snippet_add_booking($is_new)
{
    $options = get_option('opencabs_options');

    $snippet_list = get_option('uhs_snippet_list');
    if (!is_array($snippet_list))
        $snippet_list = array();

    $errors = array();
    $clean = array();

    if (!$is_new) {
        $snippet_id = $_GET['edit'];
    }

    if (!empty($_POST) && wp_verify_nonce($_POST['uhs_nonce'], 'uhs_nonce')) {

        foreach ($_POST as $k => $v)
            $clean[$k] = stripslashes($v);

        $default_options = get_option('opencabs_options');
        if (!isset($clean['language']) || $clean['language'] == "") {
            $clean['language'] = $default_options['opencabs_field_default_language'];
        }

        $clean['language'] = !empty($clean['language']) ? $clean['language'] : 'en';

        if (!isset($clean['google_north']) || $clean['google_north'] == "") {
            $clean['google_north'] = $default_options['google_north'];
        }

        if (!isset($clean['google_south']) || $clean['google_south'] == "") {
            $clean['google_south'] = $default_options['google_south'];
        }

        if (!isset($clean['google_east']) || $clean['google_east'] == "") {
            $clean['google_east'] = $default_options['google_east'];
        }

        if (!isset($clean['google_west']) || $clean['google_west'] == "") {
            $clean['google_west'] = $default_options['google_west'];
        }

        $clean['snippet_code'] = '<script type="text/javascript" src="' . $GLOBALS['webwidget']['base_url'] . '/js/dist/widget-api/all.js"></script>';
        $clean['snippet_code'] .= '<script type="text/javascript" src="' . $GLOBALS['webwidget']['base_url'] . '/js/dist/widget-api/widget.php?' .
            get_widget_url_params('booker', $clean) .
            '"></script>';
        $clean['snippet_code'] .= "<div id='oc_booker_widget'>
            Loading <a href='https://opencabs.com'>Opencabs</a> Widget ...
            </div>";

        if ($is_new) {
            if (empty($clean['snippet_id']))
                $errors[] = 'Please enter a unique name.';
            elseif (in_array(strtolower($clean['snippet_id']), $snippet_list))
                $errors[] = 'You have entered a snippet name that already exists. Names are NOT case-sensitive.';

            if (count($errors) <= 0) {
                // save snippet
                $snippet_id = strtolower($clean['snippet_id']);
                $snippet_list[] = $snippet_id;
                update_option('uhs_snippet_list', $snippet_list);
                update_option('uhs_snippet-' . $snippet_id, $clean['snippet_code']);
                update_option('uhs_snippet[' . $snippet_id . '][format]', $clean['format']);
                update_option('uhs_snippet[' . $snippet_id . '][language]', $clean['language']);
                update_option('uhs_snippet[' . $snippet_id . '][google_north]', $clean['google_north']);
                update_option('uhs_snippet[' . $snippet_id . '][google_south]', $clean['google_south']);
                update_option('uhs_snippet[' . $snippet_id . '][google_east]', $clean['google_east']);
                update_option('uhs_snippet[' . $snippet_id . '][google_west]', $clean['google_west']);
                $clean = array();
                wp_redirect(admin_url('admin.php?page=opencabs_booking_widget', 'http'));
            }
        }
        else {

            if (count($errors) <= 0) {
                update_option('uhs_snippet-' . $snippet_id, $clean['snippet_code']);
                update_option('uhs_snippet[' . $snippet_id .'][format]', $clean['format']);
                update_option('uhs_snippet[' . $snippet_id .'][language]', $clean['language']);
                update_option('uhs_snippet[' . $snippet_id .'][google_north]', $clean['google_north']);
                update_option('uhs_snippet[' . $snippet_id .'][google_south]', $clean['google_south']);
                update_option('uhs_snippet[' . $snippet_id .'][google_east]', $clean['google_east']);
                update_option('uhs_snippet[' . $snippet_id .'][google_west]', $clean['google_west']);

                wp_redirect(admin_url( 'admin.php?page=opencabs_booking_widget', 'http'));
            }
        }
    }

    if (!$is_new) {
        $snippet = get_option('uhs_snippet-' . $snippet_id);
        $clean = array(
            'snippet_code' => $snippet
        );

        $clean['format'] = get_option('uhs_snippet[' . $snippet_id .'][format]');
        $clean['language'] = get_option('uhs_snippet[' . $snippet_id .'][language]');
        $clean['google_north'] = get_option('uhs_snippet[' . $snippet_id .'][google_north]');
        $clean['google_south'] = get_option('uhs_snippet[' . $snippet_id .'][google_south]');
        $clean['google_east'] = get_option('uhs_snippet[' . $snippet_id .'][google_east]');
        $clean['google_west'] = get_option('uhs_snippet[' . $snippet_id .'][google_west]');
    }

    ?>
    <div class="wrap">
        <p><a href="?page=opencabs&amp;tab=1">&laquo; Back to the list</a></p>

        <form method="post" action="" style="margin: 1em 0;padding: 1px 1em;background: #fff;border: 1px solid #ccc;">

            <?php if (count($errors) > 0) : ?>
                <div class="message error"><?php echo wpautop(implode("\n", $errors)); ?></div>
            <?php endif; ?>

            <?php wp_nonce_field('uhs_nonce', 'uhs_nonce'); ?>
            <div class="container-fluid">
                <?php if ($is_new) { ?>
                    <div class="row m-t-xs">
                        <label class="col-lg-2" for="snippet_id">Name:</label>
                        <div class="col-lg-10">
                            <input type="text" name="snippet_id" id="snippet_id" size="40" value="<?php
                            if (isset($clean['snippet_id']))
                                echo esc_attr($clean['snippet_id']);
                            ?>"/>
                        </div>
                    </div>
                <?php } ?>

                <div class="row m-t-xs">
                    <label class="col-lg-2" for="format">Format: </label>
                    <div class="col-lg-2"><input type="radio" name="format" size="40" value="0" <?php echo (!isset($clean['format']) || $clean['format'] == 0) ? "checked" : "";?>/> Default (940 px)</div>
                    <div class="col-lg-2">
                        <input type="radio" name="format" size="40" value="1" <?php echo (isset($clean['format']) && $clean['format'] == 1) ? "checked" : "";?>/> Small (320 px)
                    </div>
                </div>

                <div class="row m-t-xs">
                    <label class="col-lg-2" for="language">Language: </label>
                    <div class="col-lg-2">
                        <select id="language" name="language">
                            <option value="" <?php echo (!isset($clean['language']) || $clean['language'] == "") ? "selected" : "";?>>(use value from general settings)</option>
                            <option value="en" <?php echo (isset($clean['language']) && $clean['language'] == "en") ? "selected" : "";?>>English</option>
                            <option value="fr" <?php echo (isset($clean['language']) && $clean['language'] == "fr") ? "selected" : "";?>>French</option>
                            <option value="it" <?php echo (isset($clean['language']) && $clean['language'] == "it") ? "selected" : "";?>>Italian</option>
                            <option value="sv" <?php echo (isset($clean['language']) && $clean['language'] == "sv") ? "selected" : "";?>>Sweden</option>
                        </select>
                    </div>
                </div>


                <div class="row m-t-xs">
                    <label class="col-lg-2">Search address bounding box: </label>
                    <div class="col-lg-10">
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="map" style="height: 250px;"></div>
                            </div>
                            <?php
                                googleMapsScripts($clean);
                            ?>
                        </div>
                        <div class="row">
                        <div class="col-lg-2">
                            <span>North point</span> <br/>
                            <input type="text" id="google_north" readonly name="google_north" value="<?php echo $clean['google_north']?>"/>
                        </div>
                        <div class="col-lg-2">
                            <span>South point</span> <br/>
                            <input type="text" id="google_south" readonly name="google_south" value="<?php echo $clean['google_south']?>"/>
                        </div>
                        <div class="col-lg-2">
                            <span>East point</span> <br/>
                            <input type="text" id="google_east" readonly name="google_east" value="<?php echo $clean['google_east']?>"/>
                        </div>
                        <div class="col-lg-2">
                            <span>West point</span> <br/>
                            <input type="text" id="google_west" readonly name="google_west" value="<?php echo $clean['google_west']?>"/>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                            <i>(leave blank to use default from the General settings)</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<!--            <p><label for="snippet_code">Snippet Code:</label></p>-->
<!--            <textarea dir="ltr" dirname="ltr" id="snippet_code" name="snippet_code" rows="10"-->
<!--                      style="font-family:Monaco,'Courier New',Courier,monospace;font-size:12px;width:80%;color:#555;">--><?php
//                if (isset($clean['snippet_code']))
//                    echo esc_attr($clean['snippet_code']);
//                ?><!--</textarea>-->

            <p><input type="submit" class="button-primary" value="Save &raquo;"/>
        </form>
    </div>
    <?php
}

function booking_widget_html_snippet_settings()
{
    if (isset($_GET['edit']) && $_GET['edit'])
        return opencabs_html_snippet_add_booking(false);

    if (isset($_GET['add']) && $_GET['add'])
        return opencabs_html_snippet_add_booking(true);

    $errors = array();
    $clean = array();

    if (isset($_GET['uhs_del']) && $_GET['uhs_del'] && wp_verify_nonce($_GET['uhs_nonce'], 'uhs_delete')) {
        delete_option('uhs_snippet-' . $_GET['uhs_del']);
        $snippet_list = get_option('uhs_snippet_list');
        if (is_array($snippet_list) && in_array($_GET['uhs_del'], $snippet_list)) {
            $snippet_list = array_diff($snippet_list, array($_GET['uhs_del']));
            update_option('uhs_snippet_list', $snippet_list);
            $success = 'Snippet with ID &quot;' . esc_html($_GET['uhs_del']) . '&quot; successfully deleted.';
        }
    }


    $snippet_list = get_option('uhs_snippet_list');
    if (!is_array($snippet_list))
        $snippet_list = array();

    ?>
    <div class="wrap">
        <?php if (count($snippet_list) > 0) : ?>


            <form method="get" action="">
                <p class="alignright">
                    <input type="hidden" name="page" value="opencabs_booking_widget"/>
                    <input type="hidden" name="add" value="1"/>
                    <input type="submit" class="button-primary" value="Add new &raquo;"/>
                </p>
            </form>

            <table class="widefat fixed">
                <thead>
                <tr>
                    <th>Tracking Name</th>
                    <th>Snipper Short Code</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($snippet_list as $snippet_id) : ?>
                    <tr>
                        <td>
                            <?php echo esc_html($snippet_id); ?>
                        </td>
                        <td>
                            <code>[opencabs_widget id="<?php echo esc_html($snippet_id); ?>"]</code>
                        </td>
                        <td>
                            <a href="?page=opencabs&amp;tab=1&amp;edit=<?php echo rawurlencode($snippet_id); ?>">Edit
                                Snippet</a> |
                            <span class="trash"><a
                                        onclick="return confirm('Are you sure you want to delete this snippet?');"
                                        href="?page=opencabs&amp;tab=1&amp;uhs_nonce=<?php echo esc_attr(wp_create_nonce('uhs_delete')); ?>&amp;uhs_del=<?php echo rawurlencode($snippet_id); ?>">Delete Snippet</a></span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        <?php else : ?>
            <h2>Your Booking Snippets Library is Empty</h2>
            <p>You have no snippets, please <a href="?page=opencabs&amp;tab=1&amp;add=1">please add one</a>.</p>
        <?php endif; ?>
    </div>
    <?php
}

function opencabs_html_snippet_add_quote($is_new)
{
    $options = get_option('opencabs_options');

    $snippet_list = get_option('uhs_quote_snippet_list');
    if (!is_array($snippet_list))
        $snippet_list = array();

    $errors = array();
    $clean = array();

    if (!$is_new) {
        $snippet_id = $_GET['edit'];
    }

    if (!empty($_POST) && wp_verify_nonce($_POST['uhs_nonce'], 'uhs_nonce')) {

        foreach ($_POST as $k => $v)
            $clean[$k] = stripslashes($v);

        $default_options = get_option('opencabs_options');
        if (!isset($clean['language']) || $clean['language'] == "") {
            $clean['language'] = $default_options['opencabs_field_default_language'];
        }

        if (!isset($clean['google_north']) || $clean['google_north'] == "") {
            $clean['google_north'] = $default_options['google_north'];
        }

        if (!isset($clean['google_south']) || $clean['google_south'] == "") {
            $clean['google_south'] = $default_options['google_south'];
        }

        if (!isset($clean['google_east']) || $clean['google_east'] == "") {
            $clean['google_east'] = $default_options['google_east'];
        }

        if (!isset($clean['google_west']) || $clean['google_west'] == "") {
            $clean['google_west'] = $default_options['google_west'];
        }
//        https://webwidgets.local/js/dist/widget-api/widget.php?widget=quote&id=widget&url_params_map=bcp:black_cab_pricing,width:width,theme:theme,language:language,o:o,osn:osn,os:os,oc:oc,op:op,olat:olat,olng:olng
        $clean['snippet_code'] = '<script type="text/javascript" src="' . $GLOBALS['webwidget']['base_url'] . '/js/dist/widget-api/all.js"></script>';
        $clean['snippet_code'] .= '<script type="text/javascript" src="' . $GLOBALS['webwidget']['base_url'] . '/js/dist/widget-api/widget.php?' .
            get_widget_url_params('quote', $clean) .            
            '&redirect=' . $clean['snippet_redirect_url'] .            
            '"></script>';
        $clean['snippet_code'] .= "<div id='oc_quote_widget'>
            Loading <a href='https://opencabs.com'>Opencabs</a> Widget ...
            </div>";

        if ($is_new) {
            if (empty($clean['snippet_id']))
                $errors[] = 'Please enter a unique name.';
            elseif (in_array(strtolower($clean['snippet_id']), $snippet_list))
                $errors[] = 'You have entered a snippet name that already exists. Names are NOT case-sensitive.';
            if (empty($clean['snippet_redirect_url'])) {
                $errors[] = 'Redirect URL is requiered.';
            }

            if (count($errors) <= 0) {
                // save snippet
                $snippet_id = strtolower($clean['snippet_id']);
                $snippet_list[] = $snippet_id;
                update_option('uhs_quote_snippet_list', $snippet_list);
                update_option('uhs_snippet-' . $snippet_id, $clean['snippet_code']);
                update_option('uhs_snippet[' . $snippet_id . '][snippet_redirect_url]', $clean['snippet_redirect_url']);
                update_option('uhs_snippet[' . $snippet_id . '][language]', $clean['language']);
                update_option('uhs_snippet[' . $snippet_id . '][google_north]', $clean['google_north']);
                update_option('uhs_snippet[' . $snippet_id . '][google_south]', $clean['google_south']);
                update_option('uhs_snippet[' . $snippet_id . '][google_east]', $clean['google_east']);
                update_option('uhs_snippet[' . $snippet_id . '][google_west]', $clean['google_west']);
                $clean = array();
                wp_redirect(admin_url('admin.php?page=opencabs_quote_widget', 'http'));
            }
        }
        else {

            if (count($errors) <= 0) {
                update_option('uhs_snippet-' . $snippet_id, $clean['snippet_code']);
                update_option('uhs_snippet[' . $snippet_id .'][snippet_redirect_url]', $clean['snippet_redirect_url']);
                update_option('uhs_snippet[' . $snippet_id .'][language]', $clean['language']);
                update_option('uhs_snippet[' . $snippet_id .'][google_north]', $clean['google_north']);
                update_option('uhs_snippet[' . $snippet_id .'][google_south]', $clean['google_south']);
                update_option('uhs_snippet[' . $snippet_id .'][google_east]', $clean['google_east']);
                update_option('uhs_snippet[' . $snippet_id .'][google_west]', $clean['google_west']);

                wp_redirect(admin_url( 'admin.php?page=opencabs_quote_widget', 'http'));
            }
        }
    }

    if (!$is_new) {
        $snippet = get_option('uhs_snippet-' . $snippet_id);
        $clean = array(
            'snippet_code' => $snippet
        );

        $clean['snippet_redirect_url'] = get_option('uhs_snippet[' . $snippet_id .'][snippet_redirect_url]');
        $clean['language'] = get_option('uhs_snippet[' . $snippet_id .'][language]');
        $clean['google_north'] = get_option('uhs_snippet[' . $snippet_id .'][google_north]');
        $clean['google_south'] = get_option('uhs_snippet[' . $snippet_id .'][google_south]');
        $clean['google_east'] = get_option('uhs_snippet[' . $snippet_id .'][google_east]');
        $clean['google_west'] = get_option('uhs_snippet[' . $snippet_id .'][google_west]');
    }

    ?>
    <div class="wrap">
        <p><a href="?page=opencabs&amp;tab=2">&laquo; Back to the list</a></p>

        <form method="post" action="" style="margin: 1em 0;padding: 1px 1em;background: #fff;border: 1px solid #ccc;">

            <?php if (count($errors) > 0) : ?>
                <div class="message error"><?php echo wpautop(implode("\n", $errors)); ?></div>
            <?php endif; ?>

            <?php wp_nonce_field('uhs_nonce', 'uhs_nonce'); ?>
            <div class="container-fluid">
                <?php if ($is_new) { ?>
                    <div class="row m-t-xs">
                        <label class="col-lg-2" for="snippet_id">Name:</label>
                        <div class="col-lg-10">
                            <input type="text" name="snippet_id" id="snippet_id" size="40" value="<?php
                            if (isset($clean['snippet_id']))
                                echo esc_attr($clean['snippet_id']);
                            ?>"/>
                        </div>
                    </div>
                <?php } ?>

                <div class="row m-t-xs">
                    <label class="col-lg-2" for="language">Language: </label>
                    <div class="col-lg-2">
                        <select id="language" name="language">
                            <option value="" <?php echo (!isset($clean['language']) || $clean['language'] == "") ? "selected" : "";?>>(use value from general settings)</option>
                            <option value="en" <?php echo (isset($clean['language']) && $clean['language'] == "en") ? "selected" : "";?>>English</option>
                            <option value="fr" <?php echo (isset($clean['language']) && $clean['language'] == "fr") ? "selected" : "";?>>French</option>
                            <option value="it" <?php echo (isset($clean['language']) && $clean['language'] == "it") ? "selected" : "";?>>Italian</option>
                            <option value="sv" <?php echo (isset($clean['language']) && $clean['language'] == "sv") ? "selected" : "";?>>Sweden</option>
                        </select>
                    </div>
                </div>


                <div class="row m-t-xs">
                    <label class="col-lg-2">Search address bounding box: </label>
                    <div class="col-lg-10">
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="map" style="height: 250px;"></div>
                            </div>
                            <?php
                            googleMapsScripts($clean);
                            ?>
                        </div>
                        <div class="row">
                            <div class="col-lg-2">
                                <span>North point</span> <br/>
                                <input type="text" id="google_north" readonly name="google_north" value="<?php echo $clean['google_north']?>"/>
                            </div>
                            <div class="col-lg-2">
                                <span>South point</span> <br/>
                                <input type="text" id="google_south" readonly name="google_south" value="<?php echo $clean['google_south']?>"/>
                            </div>
                            <div class="col-lg-2">
                                <span>East point</span> <br/>
                                <input type="text" id="google_east" readonly name="google_east" value="<?php echo $clean['google_east']?>"/>
                            </div>
                            <div class="col-lg-2">
                                <span>West point</span> <br/>
                                <input type="text" id="google_west" readonly name="google_west" value="<?php echo $clean['google_west']?>"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <i>(leave blank to use default from the General settings)</i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row m-t-xs">
                    <label class="col-lg-2" for="snippet_id">Redirect URL:</label>
                    <div class="col-lg-10">
                        <input type="text" name="snippet_redirect_url" id="snippet_redirect_url" size="40" value="<?php
                        if (isset($clean['snippet_redirect_url']))
                            echo esc_attr($clean['snippet_redirect_url']);
                        ?>"/>
                    </div>
                </div>
            </div>

            <!--            <p><label for="snippet_code">Snippet Code:</label></p>-->
            <!--            <textarea dir="ltr" dirname="ltr" id="snippet_code" name="snippet_code" rows="10"-->
            <!--                      style="font-family:Monaco,'Courier New',Courier,monospace;font-size:12px;width:80%;color:#555;">--><?php
            //                if (isset($clean['snippet_code']))
            //                    echo esc_attr($clean['snippet_code']);
            //                ?><!--</textarea>-->

            <p><input type="submit" class="button-primary" value="Save &raquo;"/>
        </form>
    </div>
    <?php
}

function quote_widget_html_snippet_settings()
{
    if (isset($_GET['edit']) && $_GET['edit'])
        return opencabs_html_snippet_add_quote(false);

    if (isset($_GET['add']) && $_GET['add'])
        return opencabs_html_snippet_add_quote(true);

    $errors = array();
    $clean = array();

    if (isset($_GET['uhs_del']) && $_GET['uhs_del'] && wp_verify_nonce($_GET['uhs_nonce'], 'uhs_delete')) {
        delete_option('uhs_snippet-' . $_GET['uhs_del']);
        $snippet_list = get_option('uhs_quote_snippet_list');
        if (is_array($snippet_list) && in_array($_GET['uhs_del'], $snippet_list)) {
            $snippet_list = array_diff($snippet_list, array($_GET['uhs_del']));
            update_option('uhs_quote_snippet_list', $snippet_list);
            $success = 'Snippet with ID &quot;' . esc_html($_GET['uhs_del']) . '&quot; successfully deleted.';
        }
    }


    $snippet_list = get_option('uhs_quote_snippet_list');
    if (!is_array($snippet_list))
        $snippet_list = array();

    ?>
    <div class="wrap">
        <?php if (count($snippet_list) > 0) : ?>


            <form method="get" action="">
                <p class="alignright">
                    <input type="hidden" name="page" value="opencabs_quote_widget"/>
                    <input type="hidden" name="add" value="1"/>
                    <input type="submit" class="button-primary" value="Add new &raquo;"/>
                </p>
            </form>

            <table class="widefat fixed">
                <thead>
                <tr>
                    <th>Tracking Name</th>
                    <th>Snipper Short Code</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($snippet_list as $snippet_id) : ?>
                    <tr>
                        <td>
                            <?php echo esc_html($snippet_id); ?>
                        </td>
                        <td>
                            <code>[opencabs_widget id="<?php echo esc_html($snippet_id); ?>"]</code>
                        </td>
                        <td>
                            <a href="?page=opencabs&amp;tab=2&amp;edit=<?php echo rawurlencode($snippet_id); ?>">Edit
                                Snippet</a> |
                            <span class="trash"><a
                                        onclick="return confirm('Are you sure you want to delete this snippet?');"
                                        href="?page=opencabs&amp;tab=2&amp;uhs_nonce=<?php echo esc_attr(wp_create_nonce('uhs_delete')); ?>&amp;uhs_del=<?php echo rawurlencode($snippet_id); ?>">Delete Snippet</a></span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        <?php else : ?>
            <h2>Your Quote Snippets Library is Empty</h2>
            <p>You have no snippets, please <a href="?page=opencabs&amp;tab=2&amp;add=1">please add one</a>.</p>
        <?php endif; ?>
    </div>
    <?php
}

function opencabs_html_snippet_add_login($is_new)
{
    $options = get_option('opencabs_options');

    $snippet_list = get_option('uhs_login_snippet_list');
    if (!is_array($snippet_list))
        $snippet_list = array();

    $errors = array();
    $clean = array();

    if (!$is_new) {
        $snippet_id = $_GET['edit'];
    }

    if (!empty($_POST) && wp_verify_nonce($_POST['uhs_nonce'], 'uhs_nonce')) {

        foreach ($_POST as $k => $v)
            $clean[$k] = stripslashes($v);

        $default_options = get_option('opencabs_options');
        if (!isset($clean['language']) || $clean['language'] == "") {
            $clean['language'] = $default_options['opencabs_field_default_language'];
        }

        $clean['snippet_code'] = '<script type="text/javascript" src="' . $GLOBALS['webwidget']['base_url'] . '/js/dist/widget-api/all.js"></script>';
        $clean['snippet_code'] .= '<script type="text/javascript" src="' . $GLOBALS['webwidget']['base_url'] . '/js/dist/widget-api/widget.php?' .
            'widget=signup' .
            '&id=oc_login_widget' .
            '&language=' . $clean['language'] .
            '&redirect=' . $clean['snippet_redirect_url'] .
            '"></script>';
        $clean['snippet_code'] .= "<div id='oc_login_widget'>
            Loading <a href='https://opencabs.com'>Opencabs</a> Widget ...
            </div>";

        if ($is_new) {
            if (empty($clean['snippet_id']))
                $errors[] = 'Please enter a unique name.';
            elseif (in_array(strtolower($clean['snippet_id']), $snippet_list))
                $errors[] = 'You have entered a snippet name that already exists. Names are NOT case-sensitive.';
            if (empty($clean['snippet_redirect_url'])) {
                $errors[] = 'Redirect URL is requiered.';
            }

            if (count($errors) <= 0) {
                // save snippet
                $snippet_id = strtolower($clean['snippet_id']);
                $snippet_list[] = $snippet_id;
                update_option('uhs_login_snippet_list', $snippet_list);
                update_option('uhs_snippet-' . $snippet_id, $clean['snippet_code']);
                update_option('uhs_snippet[' . $snippet_id . '][snippet_redirect_url]', $clean['snippet_redirect_url']);
                update_option('uhs_snippet[' . $snippet_id . '][language]', $clean['language']);
                $clean = array();
                wp_redirect(admin_url('admin.php?page=opencabs_signhup_widget', 'http'));
            }
        }
        else {

            if (count($errors) <= 0) {
                update_option('uhs_snippet-' . $snippet_id, $clean['snippet_code']);
                update_option('uhs_snippet[' . $snippet_id .'][snippet_redirect_url]', $clean['snippet_redirect_url']);
                update_option('uhs_snippet[' . $snippet_id .'][language]', $clean['language']);

                wp_redirect(admin_url( 'admin.php?page=opencabs_signhup_widget', 'http'));
            }
        }
    }

    if (!$is_new) {
        $snippet = get_option('uhs_snippet-' . $snippet_id);
        $clean = array(
            'snippet_code' => $snippet
        );

        $clean['snippet_redirect_url'] = get_option('uhs_snippet[' . $snippet_id .'][snippet_redirect_url]');
        $clean['language'] = get_option('uhs_snippet[' . $snippet_id .'][language]');
    }

    ?>
    <div class="wrap">
        <p><a href="?page=opencabs&amp;tab=3">&laquo; Back to the list</a></p>

        <form method="post" action="" style="margin: 1em 0;padding: 1px 1em;background: #fff;border: 1px solid #ccc;">

            <?php if (count($errors) > 0) : ?>
                <div class="message error"><?php echo wpautop(implode("\n", $errors)); ?></div>
            <?php endif; ?>

            <?php wp_nonce_field('uhs_nonce', 'uhs_nonce'); ?>
            <div class="container-fluid">
                <?php if ($is_new) { ?>
                    <div class="row m-t-xs">
                        <label class="col-lg-2" for="snippet_id">Name:</label>
                        <div class="col-lg-10">
                            <input type="text" name="snippet_id" id="snippet_id" size="40" value="<?php
                            if (isset($clean['snippet_id']))
                                echo esc_attr($clean['snippet_id']);
                            ?>"/>
                        </div>
                    </div>
                <?php } ?>

                <div class="row m-t-xs">
                    <label class="col-lg-2" for="language">Language: </label>
                    <div class="col-lg-2">
                        <select id="language" name="language">
                            <option value="" <?php echo (!isset($clean['language']) || $clean['language'] == "") ? "selected" : "";?>>(use value from general settings)</option>
                            <option value="en" <?php echo (isset($clean['language']) && $clean['language'] == "en") ? "selected" : "";?>>English</option>
                            <option value="fr" <?php echo (isset($clean['language']) && $clean['language'] == "fr") ? "selected" : "";?>>French</option>
                            <option value="it" <?php echo (isset($clean['language']) && $clean['language'] == "it") ? "selected" : "";?>>Italian</option>
                            <option value="sv" <?php echo (isset($clean['language']) && $clean['language'] == "sv") ? "selected" : "";?>>Sweden</option>
                        </select>
                    </div>
                </div>

                <div class="row m-t-xs">
                    <label class="col-lg-2" for="snippet_id">Redirect URL:</label>
                    <div class="col-lg-10">
                        <input type="text" name="snippet_redirect_url" id="snippet_redirect_url" size="40" value="<?php
                        if (isset($clean['snippet_redirect_url']))
                            echo esc_attr($clean['snippet_redirect_url']);
                        ?>"/>
                    </div>
                </div>
            </div>

            <!--            <p><label for="snippet_code">Snippet Code:</label></p>-->
            <!--            <textarea dir="ltr" dirname="ltr" id="snippet_code" name="snippet_code" rows="10"-->
            <!--                      style="font-family:Monaco,'Courier New',Courier,monospace;font-size:12px;width:80%;color:#555;">--><?php
            //                if (isset($clean['snippet_code']))
            //                    echo esc_attr($clean['snippet_code']);
            //                ?><!--</textarea>-->

            <p><input type="submit" class="button-primary" value="Save &raquo;"/>
        </form>
    </div>
    <?php
}

function login_widget_html_snippet_settings()
{
    if (isset($_GET['edit']) && $_GET['edit'])
        return opencabs_html_snippet_add_login(false);

    if (isset($_GET['add']) && $_GET['add'])
        return opencabs_html_snippet_add_login(true);

    $errors = array();
    $clean = array();

    if (isset($_GET['uhs_del']) && $_GET['uhs_del'] && wp_verify_nonce($_GET['uhs_nonce'], 'uhs_delete')) {
        delete_option('uhs_snippet-' . $_GET['uhs_del']);
        $snippet_list = get_option('uhs_login_snippet_list');
        if (is_array($snippet_list) && in_array($_GET['uhs_del'], $snippet_list)) {
            $snippet_list = array_diff($snippet_list, array($_GET['uhs_del']));
            update_option('uhs_login_snippet_list', $snippet_list);
            $success = 'Snippet with ID &quot;' . esc_html($_GET['uhs_del']) . '&quot; successfully deleted.';
        }
    }


    $snippet_list = get_option('uhs_login_snippet_list');
    if (!is_array($snippet_list))
        $snippet_list = array();

    ?>
    <div class="wrap">
        <?php if (count($snippet_list) > 0) : ?>


            <form method="get" action="">
                <p class="alignright">
                    <input type="hidden" name="page" value="opencabs_signhup_widget"/>
                    <input type="hidden" name="add" value="1"/>
                    <input type="submit" class="button-primary" value="Add new &raquo;"/>
                </p>
            </form>

            <table class="widefat fixed">
                <thead>
                <tr>
                    <th>Tracking Name</th>
                    <th>Snipper Short Code</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($snippet_list as $snippet_id) : ?>
                    <tr>
                        <td>
                            <?php echo esc_html($snippet_id); ?>
                        </td>
                        <td>
                            <code>[opencabs_widget id="<?php echo esc_html($snippet_id); ?>"]</code>
                        </td>
                        <td>
                            <a href="?page=opencabs&amp;tab=3&amp;edit=<?php echo rawurlencode($snippet_id); ?>">Edit
                                Snippet</a> |
                            <span class="trash"><a
                                        onclick="return confirm('Are you sure you want to delete this snippet?');"
                                        href="?page=opencabs&amp;tab=3&amp;uhs_nonce=<?php echo esc_attr(wp_create_nonce('uhs_delete')); ?>&amp;uhs_del=<?php echo rawurlencode($snippet_id); ?>">Delete Snippet</a></span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        <?php else : ?>
            <h2>Your Login/Signup Snippets Library is Empty</h2>
            <p>You have no snippets, please <a href="?page=opencabs&amp;tab=3&amp;add=1">please add one</a>.</p>
        <?php endif; ?>
    </div>
    <?php
}

?>